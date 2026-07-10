#!/usr/bin/env bash

set -euo pipefail

if [ $# -lt 1 ]; then
    echo "Gunakan:"
    echo "./scripts/oauth-production.sh https://domain-asli-kamu.com"
    exit 1
fi

APP_URL_VALUE="${1%/}"
ENV_FILE="${2:-src/.env}"
GOOGLE_REDIRECT_URI_VALUE="${APP_URL_VALUE}/auth/google/callback"

if [[ "$APP_URL_VALUE" != https://\* ]]; then
    echo "Production wajib pakai https://"
    echo "Contoh: ./scripts/oauth-production.sh https://donordarah.id"
    exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
    echo "File .env tidak ditemukan: $ENV_FILE"
    exit 1
fi

python3 - "$ENV_FILE" "$APP_URL_VALUE" "$GOOGLE_REDIRECT_URI_VALUE" <<'PY'
from pathlib import Path
import sys

env_file = Path(sys.argv[1])
app_url = sys.argv[2]
google_redirect_uri = sys.argv[3]

content = env_file.read_text()
lines = content.splitlines()

def set_env(key: str, value: str) -> None:
    global lines

    target = f"{key}="
    replaced = False

    for index, line in enumerate(lines):
        if line.startswith(target):
            lines[index] = f"{key}={value}"
            replaced = True
            break

    if not replaced:
        lines.append(f"{key}={value}")

set_env("APP_ENV", "production")
set_env("APP_DEBUG", "false")
set_env("APP_URL", app_url)

set_env("GOOGLE_REDIRECT_URI", google_redirect_uri)

set_env("SESSION_DOMAIN", "")
set_env("SESSION_SECURE_COOKIE", "true")
set_env("SESSION_SAME_SITE", "lax")

env_file.write_text("\n".join(lines) + "\n")

print("OAuth production berhasil disiapkan.")
print(f"APP_URL={app_url}")
print(f"GOOGLE_REDIRECT_URI={google_redirect_uri}")
PY

echo ""
echo "Pastikan Google Console production memiliki:"
echo "Authorized JavaScript origins:"
echo "$APP_URL_VALUE"
echo ""
echo "Authorized redirect URIs:"
echo "$GOOGLE_REDIRECT_URI_VALUE"
echo ""
echo "Setelah itu jalankan:"
echo "dca optimize:clear"
echo "dca config:clear"
echo "dca route:clear"
echo "dca view:clear"
