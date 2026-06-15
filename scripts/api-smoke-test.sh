#!/usr/bin/env bash

set -Euo pipefail

BASE_URL="${BASE_URL:-https://donordarah.test}"

DONOR_EMAIL="${DONOR_EMAIL:-pendonor@donordarah.test}"
DONOR_PASSWORD="${DONOR_PASSWORD:-password}"

HOSPITAL_EMAIL="${HOSPITAL_EMAIL:-rumahsakit@donordarah.test}"
HOSPITAL_PASSWORD="${HOSPITAL_PASSWORD:-password}"

ADMIN_EMAIL="${ADMIN_EMAIL:-admin@admin.com}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-password}"

TEMP_DIRECTORY="$(mktemp -d)"

TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

LAST_TOKEN=""

DONOR_TOKEN=""
HOSPITAL_TOKEN=""
ADMIN_TOKEN=""

cleanup() {
    rm -rf "${TEMP_DIRECTORY}"
}

trap cleanup EXIT

print_line() {
    printf '%*s\n' 72 '' | tr ' ' '='
}

print_title() {
    echo ""
    print_line
    echo "$1"
    print_line
}

status_is_allowed() {
    local actual_status="$1"
    local expected_statuses="$2"
    local expected_status

    IFS=',' read -ra statuses <<< "${expected_statuses}"

    for expected_status in "${statuses[@]}"; do
        if [[ "${actual_status}" == "${expected_status}" ]]; then
            return 0
        fi
    done

    return 1
}

truncate_response() {
    local file="$1"

    if [[ ! -s "${file}" ]]; then
        echo "(response kosong)"
        return
    fi

    head -c 1200 "${file}"
    echo ""
}

request_test() {
    local name="$1"
    local method="$2"
    local path="$3"
    local expected_statuses="$4"
    local token="${5:-}"
    local request_body="${6:-}"
    local content_type="${7:-application/json}"

    local response_file
    local status_code
    local url

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    response_file="${TEMP_DIRECTORY}/response-${TOTAL_TESTS}.json"
    url="${BASE_URL}${path}"

    local curl_arguments=(
        --insecure
        --silent
        --show-error
        --output "${response_file}"
        --write-out "%{http_code}"
        --request "${method}"
        --header "Accept: application/json"
    )

    if [[ -n "${token}" ]]; then
        curl_arguments+=(
            --header "Authorization: Bearer ${token}"
        )
    fi

    if [[ -n "${request_body}" ]]; then
        curl_arguments+=(
            --header "Content-Type: ${content_type}"
            --data "${request_body}"
        )
    fi

    if ! status_code="$(
        curl "${curl_arguments[@]}" "${url}"
    )"; then
        status_code="000"
    fi

    if status_is_allowed \
        "${status_code}" \
        "${expected_statuses}"
    then
        PASSED_TESTS=$((PASSED_TESTS + 1))

        printf '[LULUS] %-48s HTTP %s\n' \
            "${name}" \
            "${status_code}"

        return
    fi

    FAILED_TESTS=$((FAILED_TESTS + 1))

    printf '[GAGAL] %-48s HTTP %s, target %s\n' \
        "${name}" \
        "${status_code}" \
        "${expected_statuses}"

    echo "URL: ${method} ${url}"
    echo "Response:"

    truncate_response "${response_file}"
}

extract_token() {
    local response="$1"

    printf '%s' "${response}" |
        docker compose exec -T php php -r '
            $payload = json_decode(
                stream_get_contents(STDIN),
                true
            );

            if (! is_array($payload)) {
                exit(0);
            }

            $findToken = function (
                mixed $value
            ) use (&$findToken): ?string {
                if (! is_array($value)) {
                    return null;
                }

                foreach (
                    [
                        "token",
                        "plainTextToken",
                        "plain_text_token",
                        "access_token",
                    ] as $key
                ) {
                    if (
                        isset($value[$key])
                        && is_string($value[$key])
                        && $value[$key] !== ""
                    ) {
                        return $value[$key];
                    }
                }

                foreach ($value as $child) {
                    if (! is_array($child)) {
                        continue;
                    }

                    $token = $findToken($child);

                    if ($token !== null) {
                        return $token;
                    }
                }

                return null;
            };

            echo $findToken($payload) ?? "";
        ' 2>/dev/null
}

login_user() {
    local email="$1"
    local password="$2"
    local label="$3"

    local response_file
    local status_code
    local response
    local token

    LAST_TOKEN=""

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    response_file="${TEMP_DIRECTORY}/login-${TOTAL_TESTS}.json"

    if ! status_code="$(
        curl \
            --insecure \
            --silent \
            --show-error \
            --output "${response_file}" \
            --write-out "%{http_code}" \
            --request POST \
            "${BASE_URL}/api/auth/login" \
            --header "Accept: application/json" \
            --header "Content-Type: application/json" \
            --data "$(
                printf \
                    '{"email":"%s","password":"%s"}' \
                    "${email}" \
                    "${password}"
            )"
    )"; then
        status_code="000"
    fi

    if ! status_is_allowed \
        "${status_code}" \
        "200,201"
    then
        FAILED_TESTS=$((FAILED_TESTS + 1))

        printf '[GAGAL] %-48s HTTP %s\n' \
            "Login ${label}" \
            "${status_code}"

        echo "Email: ${email}"
        echo "Response:"

        truncate_response "${response_file}"

        return
    fi

    response="$(cat "${response_file}")"

    token="$(extract_token "${response}")"

    if [[ -z "${token}" ]]; then
        FAILED_TESTS=$((FAILED_TESTS + 1))

        printf '[GAGAL] %-48s token tidak ditemukan\n' \
            "Login ${label}"

        echo "Response:"

        truncate_response "${response_file}"

        return
    fi

    LAST_TOKEN="${token}"

    PASSED_TESTS=$((PASSED_TESTS + 1))

    printf '[LULUS] %-48s HTTP %s\n' \
        "Login ${label}" \
        "${status_code}"
}

print_title "1. SCRAMBLE DAN ENDPOINT PUBLIK"

request_test \
    "Halaman dokumentasi Scramble" \
    "GET" \
    "/docs/api" \
    "200"

request_test \
    "Dokumen OpenAPI JSON" \
    "GET" \
    "/docs/api.json" \
    "200"

request_test \
    "Daftar jadwal donor publik" \
    "GET" \
    "/api/admin/jadwal-donors" \
    "200"

request_test \
    "Daftar lokasi donor publik" \
    "GET" \
    "/api/admin/lokasi-donors" \
    "200"

request_test \
    "Ringkasan stok darah publik" \
    "GET" \
    "/api/admin/kantong-darahs" \
    "200"

request_test \
    "Jadwal tidak menerima POST" \
    "POST" \
    "/api/admin/jadwal-donors" \
    "404,405" \
    "" \
    '{}'

request_test \
    "Lokasi tidak menerima DELETE" \
    "DELETE" \
    "/api/admin/lokasi-donors/1" \
    "404,405"

print_title "2. PROTEKSI ENDPOINT TANPA TOKEN"

request_test \
    "Profil pendonor tanpa token" \
    "GET" \
    "/api/admin/profil-pendonors/me" \
    "401"

request_test \
    "Pendaftaran donor tanpa token" \
    "GET" \
    "/api/admin/pendaftaran-donors" \
    "401"

request_test \
    "Profil rumah sakit tanpa token" \
    "GET" \
    "/api/admin/profil-rumah-sakits/me" \
    "401"

request_test \
    "Permintaan darah tanpa token" \
    "GET" \
    "/api/admin/permintaan-darahs" \
    "401"

request_test \
    "Distribusi darah tanpa token" \
    "GET" \
    "/api/admin/distribusi-darahs" \
    "401"

print_title "3. VALIDASI LOGIN"

request_test \
    "Login dengan kredensial salah" \
    "POST" \
    "/api/auth/login" \
    "401,422" \
    "" \
    '{
        "email": "tidak-ada@donordarah.test",
        "password": "password-salah"
    }'

print_title "4. API PENDONOR"

login_user \
    "${DONOR_EMAIL}" \
    "${DONOR_PASSWORD}" \
    "Pendonor"

DONOR_TOKEN="${LAST_TOKEN}"

if [[ -n "${DONOR_TOKEN}" ]]; then
    request_test \
        "Pendonor melihat profil sendiri" \
        "GET" \
        "/api/admin/profil-pendonors/me" \
        "200" \
        "${DONOR_TOKEN}"

    request_test \
        "Pendonor melihat riwayat pendaftaran" \
        "GET" \
        "/api/admin/pendaftaran-donors" \
        "200" \
        "${DONOR_TOKEN}"

    request_test \
        "Pendonor dilarang mengakses profil RS" \
        "GET" \
        "/api/admin/profil-rumah-sakits/me" \
        "403" \
        "${DONOR_TOKEN}"

    request_test \
        "Pendonor dilarang mengakses permintaan darah" \
        "GET" \
        "/api/admin/permintaan-darahs" \
        "403" \
        "${DONOR_TOKEN}"
else
    echo "Pengujian endpoint Pendonor dilewati karena login gagal."
fi

print_title "5. API RUMAH SAKIT"

login_user \
    "${HOSPITAL_EMAIL}" \
    "${HOSPITAL_PASSWORD}" \
    "Rumah Sakit"

HOSPITAL_TOKEN="${LAST_TOKEN}"

if [[ -n "${HOSPITAL_TOKEN}" ]]; then
    request_test \
        "Rumah Sakit melihat profil sendiri" \
        "GET" \
        "/api/admin/profil-rumah-sakits/me" \
        "200" \
        "${HOSPITAL_TOKEN}"

    request_test \
        "Rumah Sakit melihat permintaan sendiri" \
        "GET" \
        "/api/admin/permintaan-darahs" \
        "200" \
        "${HOSPITAL_TOKEN}"

    request_test \
        "Rumah Sakit melihat distribusi sendiri" \
        "GET" \
        "/api/admin/distribusi-darahs" \
        "200" \
        "${HOSPITAL_TOKEN}"

    request_test \
        "Rumah Sakit dilarang mengakses profil donor" \
        "GET" \
        "/api/admin/profil-pendonors/me" \
        "403" \
        "${HOSPITAL_TOKEN}"

    request_test \
        "Rumah Sakit dilarang mengakses pendaftaran donor" \
        "GET" \
        "/api/admin/pendaftaran-donors" \
        "403" \
        "${HOSPITAL_TOKEN}"
else
    echo "Pengujian endpoint Rumah Sakit dilewati karena login gagal."
fi

print_title "6. API ADMIN"

login_user \
    "${ADMIN_EMAIL}" \
    "${ADMIN_PASSWORD}" \
    "Admin"

ADMIN_TOKEN="${LAST_TOKEN}"

if [[ -n "${ADMIN_TOKEN}" ]]; then
    request_test \
        "Admin tidak dianggap Pendonor" \
        "GET" \
        "/api/admin/profil-pendonors/me" \
        "403" \
        "${ADMIN_TOKEN}"

    request_test \
        "Admin tidak dianggap Rumah Sakit" \
        "GET" \
        "/api/admin/profil-rumah-sakits/me" \
        "403" \
        "${ADMIN_TOKEN}"
else
    echo "Pengujian endpoint Admin dilewati karena login gagal."
fi

print_title "7. LOGOUT DAN INVALIDASI TOKEN"

if [[ -n "${DONOR_TOKEN}" ]]; then
    request_test \
        "Logout Pendonor" \
        "POST" \
        "/api/auth/logout" \
        "200,204" \
        "${DONOR_TOKEN}"

    request_test \
        "Token Pendonor tidak berlaku setelah logout" \
        "GET" \
        "/api/admin/profil-pendonors/me" \
        "401" \
        "${DONOR_TOKEN}"
fi

if [[ -n "${HOSPITAL_TOKEN}" ]]; then
    request_test \
        "Logout Rumah Sakit" \
        "POST" \
        "/api/auth/logout" \
        "200,204" \
        "${HOSPITAL_TOKEN}"

    request_test \
        "Token Rumah Sakit tidak berlaku setelah logout" \
        "GET" \
        "/api/admin/profil-rumah-sakits/me" \
        "401" \
        "${HOSPITAL_TOKEN}"
fi

if [[ -n "${ADMIN_TOKEN}" ]]; then
    request_test \
        "Logout Admin" \
        "POST" \
        "/api/auth/logout" \
        "200,204" \
        "${ADMIN_TOKEN}"
fi

print_title "HASIL PENGUJIAN"

echo "Total pengujian : ${TOTAL_TESTS}"
echo "Lulus           : ${PASSED_TESTS}"
echo "Gagal           : ${FAILED_TESTS}"

if [[ "${FAILED_TESTS}" -gt 0 ]]; then
    echo ""
    echo "Smoke test selesai dengan kegagalan."
    exit 1
fi

echo ""
echo "Seluruh smoke test API berhasil."