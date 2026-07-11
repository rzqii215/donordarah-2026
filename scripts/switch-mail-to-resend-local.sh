#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== Switch mail ke Resend local testing =="

if [ ! -f "src/.env" ]; then
    echo "[ERROR] src/.env tidak ditemukan."
    exit 1
fi

cp src/.env src/.env.backup-before-resend-local

echo ""
read -rsp "Paste RESEND_KEY baru kamu, atau kosongkan kalau sudah ada di .env: " RESEND_KEY_INPUT
echo ""

export RESEND_KEY_INPUT

python3 - <<'PY'
from pathlib import Path
import os

path = Path("src/.env")
content = path.read_text()

resend_key_input = os.environ.get("RESEND_KEY_INPUT", "").strip()

pairs = {
    "APP_URL": "https://donordarah.test",
    "MAIL_MAILER": "resend",
    "MAIL_FROM_ADDRESS": "onboarding@resend.dev",
    "MAIL_FROM_NAME": '"Donor Darah Local"',
}

if resend_key_input:
    pairs["RESEND_KEY"] = resend_key_input

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
    new_lines.append("# Resend local testing")
    for key in missing:
        new_lines.append(f"{key}={pairs[key]}")

path.write_text("\n".join(new_lines).rstrip() + "\n")

print("[OK] .env sudah diset ke Resend local testing.")
PY

echo ""
echo "== Hapus cache config =="

docker compose exec php sh -lc "rm -f bootstrap/cache/*.php"
docker compose exec php sh -lc "rm -f storage/framework/views/*.php"

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

docker compose restart php

echo ""
echo "== Cek config mail =="

docker compose exec php php artisan tinker --execute='dump([
    "app_url" => config("app.url"),
    "mail_default" => config("mail.default"),
    "resend_key_ada" => filled(config("services.resend.key")),
    "from_address" => config("mail.from.address"),
    "from_name" => config("mail.from.name"),
]);'

echo ""
echo "Selesai."
