#!/usr/bin/env bash

set -u

OUTPUT="audit-api-pendonor.txt"

: > "$OUTPUT"

section() {
    echo "" >> "$OUTPUT"
    echo "==================================================" >> "$OUTPUT"
    echo "$1" >> "$OUTPUT"
    echo "==================================================" >> "$OUTPUT"
}

section "MODEL PROFIL PENDONOR"
sed -n '1,420p' \
    src/app/Models/ProfilPendonor.php \
    >> "$OUTPUT" 2>&1

section "MODEL PENDAFTARAN DONOR"
sed -n '1,520p' \
    src/app/Models/PendaftaranDonor.php \
    >> "$OUTPUT" 2>&1

section "SERVICE PENDAFTARAN DONOR"
if [ -f src/app/Services/LayananPendaftaranDonor.php ]; then
    sed -n '1,620p' \
        src/app/Services/LayananPendaftaranDonor.php \
        >> "$OUTPUT" 2>&1
else
    echo "LayananPendaftaranDonor.php belum tersedia" \
        >> "$OUTPUT"
fi

section "MIGRATION PROFIL PENDONOR"
sed -n '1,420p' \
    src/database/migrations/2026_06_14_133904_create_profil_pendonors_table.php \
    >> "$OUTPUT" 2>&1

section "MIGRATION PENDAFTARAN DONOR"
sed -n '1,420p' \
    src/database/migrations/2026_06_14_133943_create_pendaftaran_donors_table.php \
    >> "$OUTPUT" 2>&1

section "ENUM TERKAIT"
for file in src/app/Enums/*.php; do
    case "$file" in
        *Pendonor*|*Pendaftaran*|*Pengguna*|*Golongan*|*Rhesus*|*Kelamin*)
            echo "" >> "$OUTPUT"
            echo "===== $file =====" >> "$OUTPUT"

            sed -n '1,320p' \
                "$file" \
                >> "$OUTPUT" 2>&1
            ;;
    esac
done

section "ROLE TERDAFTAR"
docker compose exec -T php php artisan tinker \
    --execute="dump(\Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name')->all());" \
    >> "$OUTPUT" 2>&1

section "API SERVICE PROFIL PENDONOR"
sed -n '1,260p' \
    src/app/Filament/Admin/Resources/ProfilPendonorResource/Api/ProfilPendonorApiService.php \
    >> "$OUTPUT" 2>&1

section "API SERVICE PENDAFTARAN DONOR"
sed -n '1,260p' \
    src/app/Filament/Admin/Resources/PendaftaranDonorResource/Api/PendaftaranDonorApiService.php \
    >> "$OUTPUT" 2>&1

section "CREATE HANDLER PENDAFTARAN"
sed -n '1,360p' \
    src/app/Filament/Admin/Resources/PendaftaranDonorResource/Api/Handlers/CreateHandler.php \
    >> "$OUTPUT" 2>&1

section "CREATE REQUEST PENDAFTARAN"
sed -n '1,360p' \
    src/app/Filament/Admin/Resources/PendaftaranDonorResource/Api/Requests/CreatePendaftaranDonorRequest.php \
    >> "$OUTPUT" 2>&1

echo ""
echo "Audit selesai:"
echo "$OUTPUT"
