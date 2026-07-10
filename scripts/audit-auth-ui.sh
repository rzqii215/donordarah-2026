#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Clear cache dan compiled Blade =="

docker compose exec php sh -lc "rm -f storage/framework/views/*.php"
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 2. Cek syntax file auth penting =="

FILES=(
    "app/Livewire/Auth/Login.php"
    "app/Livewire/Auth/RegisterDonor.php"
    "app/Livewire/Auth/RegisterPemohonDonor.php"
    "resources/views/livewire/auth/login.blade.php"
    "resources/views/livewire/auth/register-donor.blade.php"
    "resources/views/livewire/auth/register-pemohon-donor.blade.php"
    "resources/views/components/shared/safe-flash-message.blade.php"
)

for file in "${FILES[@]}"; do
    if [ -f "src/$file" ]; then
        docker compose exec php php -l "$file"
    else
        echo "[SKIP] File tidak ada: src/$file"
    fi
done

echo ""
echo "== 3. Cek Google button dobel di login =="

LOGIN_FILE="src/resources/views/livewire/auth/login.blade.php"

if [ -f "$LOGIN_FILE" ]; then
    GOOGLE_INCLUDE_COUNT="$(grep -c "components.auth.google-auth-actions" "$LOGIN_FILE" || true)"
    GOOGLE_TEXT_COUNT="$(grep -c "Masuk dengan Google" "$LOGIN_FILE" || true)"

    echo "google-auth-actions include count : $GOOGLE_INCLUDE_COUNT"
    echo "Masuk dengan Google text count    : $GOOGLE_TEXT_COUNT"

    if [ "$GOOGLE_INCLUDE_COUNT" -gt 0 ]; then
        echo "[ERROR] Masih ada include Google lama di login."
        exit 1
    fi

    if [ "$GOOGLE_TEXT_COUNT" -gt 1 ]; then
        echo "[ERROR] Tombol Masuk dengan Google masih dobel."
        exit 1
    fi

    echo "[OK] Login Google tidak dobel."
else
    echo "[ERROR] login.blade.php tidak ditemukan."
    exit 1
fi

echo ""
echo "== 4. Cek register donor =="

REGISTER_DONOR_FILE="src/resources/views/livewire/auth/register-donor.blade.php"

if [ -f "$REGISTER_DONOR_FILE" ]; then
    grep -q "Daftar dengan Google" "$REGISTER_DONOR_FILE" \
        && echo "[OK] Register donor punya tombol Google." \
        || { echo "[ERROR] Register donor belum punya tombol Google."; exit 1; }

    grep -q "Isi Form Manual" "$REGISTER_DONOR_FILE" \
        && echo "[OK] Register donor punya tombol manual." \
        || { echo "[ERROR] Register donor belum punya tombol manual."; exit 1; }

    grep -q "menggunakanGoogle" "$REGISTER_DONOR_FILE" \
        && echo "[OK] Register donor punya conditional Google/manual." \
        || { echo "[ERROR] Register donor belum punya conditional menggunakanGoogle."; exit 1; }

    if grep -q "components.auth.google-auth-actions" "$REGISTER_DONOR_FILE"; then
        echo "[ERROR] Register donor masih pakai include Google lama."
        exit 1
    fi
else
    echo "[ERROR] register-donor.blade.php tidak ditemukan."
    exit 1
fi

echo ""
echo "== 5. Cek register pemohon donor =="

REGISTER_PEMOHON_FILE="src/resources/views/livewire/auth/register-pemohon-donor.blade.php"

if [ -f "$REGISTER_PEMOHON_FILE" ]; then
    grep -q "Daftar dengan Google" "$REGISTER_PEMOHON_FILE" \
        && echo "[OK] Register pemohon punya tombol Google." \
        || { echo "[ERROR] Register pemohon belum punya tombol Google."; exit 1; }

    grep -q "Isi Form Manual" "$REGISTER_PEMOHON_FILE" \
        && echo "[OK] Register pemohon punya tombol manual." \
        || { echo "[ERROR] Register pemohon belum punya tombol manual."; exit 1; }

    grep -q "menggunakanGoogle" "$REGISTER_PEMOHON_FILE" \
        && echo "[OK] Register pemohon punya conditional Google/manual." \
        || { echo "[ERROR] Register pemohon belum punya conditional menggunakanGoogle."; exit 1; }

    if grep -q "components.auth.google-auth-actions" "$REGISTER_PEMOHON_FILE"; then
        echo "[ERROR] Register pemohon masih pakai include Google lama."
        exit 1
    fi
else
    echo "[ERROR] register-pemohon-donor.blade.php tidak ditemukan."
    exit 1
fi

echo ""
echo "== 6. Cek URL login/logout yang gabung =="

if grep -R "loginhttp\|login.*logout\|logout.*login" -n src/resources/views src/app/Livewire 2>/dev/null; then
    echo "[ERROR] Masih ada URL login/logout yang berpotensi gabung."
    exit 1
else
    echo "[OK] Tidak ada URL login/logout gabung di source."
fi

echo ""
echo "== 7. Cek include recursive lama =="

if grep -R "components.auth.logout-post-bridge\|components.shared.flash-message" -n src/resources/views 2>/dev/null; then
    echo "[ERROR] Masih ada include lama recursive."
    exit 1
else
    echo "[OK] Tidak ada include lama recursive."
fi

echo ""
echo "== 8. Cek safe flash =="

SAFE_FLASH_COUNT="$(grep -R "components.shared.safe-flash-message" -n src/resources/views 2>/dev/null | wc -l | tr -d ' ')"

echo "safe flash include count: $SAFE_FLASH_COUNT"

if grep -n "components.shared.safe-flash-message" src/resources/views/components/shared/safe-flash-message.blade.php 2>/dev/null; then
    echo "[ERROR] safe-flash-message include dirinya sendiri."
    exit 1
fi

echo "[OK] Safe flash tidak recursive."

echo ""
echo "== 9. Cek route auth =="

docker compose exec php php artisan route:list | grep -E "login|register|google|logout" || true

echo ""
echo "Audit UI auth selesai."
