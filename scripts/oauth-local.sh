#!/usr/bin/env bash

set -euo pipefail

ENV_FILE="${1:-src/.env}"
APP_URL_VALUE="${2:-http://localhost}"
GOOGLE_REDIRECT_URI_VALUE="${APP_URL_VALUE%/}/auth/google/callback"

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

set_env("APP_ENV", "local")
set_env("APP_DEBUG", "true")
set_env("APP_URL", app_url)

set_env("GOOGLE_REDIRECT_URI", google_redirect_uri)

set_env("SESSION_DOMAIN", "")
set_env("SESSION_SECURE_COOKIE", "false")
set_env("SESSION_SAME_SITE", "lax")

env_file.write_text("\n".join(lines) + "\n")

print("OAuth local berhasil disiapkan.")
print(f"APP_URL={app_url}")
print(f"GOOGLE_REDIRECT_URI={google_redirect_uri}")
PY

echo ""
echo "Selesai. Jalankan:"
echo "dca optimize:clear"
echo "dca config:clear"
echo "dca route:clear"
echo "dca view:clear"
