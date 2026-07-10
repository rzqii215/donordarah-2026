#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Backup .env =="

if [ -f "src/.env" ]; then
    cp src/.env "src/.env.backup-forgot-password-local"
    echo "[OK] Backup dibuat: src/.env.backup-forgot-password-local"
else
    echo "[ERROR] src/.env tidak ditemukan."
    exit 1
fi

echo ""
echo "== 2. Set mail lokal ke log =="

python3 - <<'PY'
from pathlib import Path

path = Path("src/.env")
content = path.read_text()

pairs = {
    "APP_URL": "https://donordarah.test",
    "MAIL_MAILER": "log",
    "MAIL_FROM_ADDRESS": "no-reply@donordarah.local",
    "MAIL_FROM_NAME": '"Donor Darah Local"',
}

lines = content.splitlines()
keys_found = set()
new_lines = []

for line in lines:
    if "=" not in line or line.strip().startswith("#"):
        new_lines.append(line)
        continue

    key = line.split("=", 1)[0].strip()

    if key in pairs:
        new_lines.append(f"{key}={pairs[key]}")
        keys_found.add(key)
    else:
        new_lines.append(line)

missing = [key for key in pairs if key not in keys_found]

if missing:
    new_lines.append("")
    new_lines.append("# Local forgot password mail testing")
    for key in missing:
        new_lines.append(f"{key}={pairs[key]}")

path.write_text("\n".join(new_lines).rstrip() + "\n")

print("[OK] .env diset ke MAIL_MAILER=log")
PY

echo ""
echo "== 3. Pastikan route password-reset dipanggil di web.php =="

grep -qxF "require __DIR__ . '/password-reset.php';" src/routes/web.php || printf "\nrequire __DIR__ . '/password-reset.php';\n" >> src/routes/web.php

echo "[OK] routes/password-reset.php dipanggil di routes/web.php"

echo ""
echo "== 4. Cek marker terminal yang tidak boleh masuk source =="

if grep -R "^PHP$\|^BLADE$\|<<'PHP'\|<<'BLADE'\|cat > src/" -n src/routes src/app src/config src/database src/resources 2>/dev/null; then
    echo ""
    echo "[ERROR] Ada marker terminal masuk ke file source. Hapus dulu baris di atas."
    exit 1
else
    echo "[OK] Tidak ada marker heredoc/terminal di source."
fi

echo ""
echo "== 5. Cek syntax file PHP forgot password =="

docker compose exec php php -l routes/password-reset.php
docker compose exec php php -l app/Livewire/Auth/ForgotPassword.php
docker compose exec php php -l app/Livewire/Auth/ResetPassword.php
docker compose exec php php -l app/Models/User.php

if [ -f "src/app/Notifications/Auth/ResetPasswordNotification.php" ]; then
    docker compose exec php php -l app/Notifications/Auth/ResetPasswordNotification.php
fi

echo ""
echo "== 6. Jalankan migration =="

docker compose exec php php artisan migrate

echo ""
echo "== 7. Clear cache =="

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 8. Cek route password =="

docker compose exec php php artisan route:list --name=password

echo ""
echo "== 9. Cek config mail =="

docker compose exec php php artisan tinker --execute='dump([
    "app_url" => config("app.url"),
    "mail_default" => config("mail.default"),
    "from_address" => config("mail.from.address"),
    "from_name" => config("mail.from.name"),
]);'

echo ""
echo "Setup forgot password local selesai."
