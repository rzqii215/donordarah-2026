#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== Fix return type RedirectResponse di Livewire =="

python3 - <<'PY'
from pathlib import Path
import re

base = Path("src/app/Livewire")

if not base.exists():
    raise SystemExit("Folder src/app/Livewire tidak ditemukan.")

updated = []

for path in base.rglob("*.php"):
    content = path.read_text()
    original = content

    content = content.replace(
        "use Illuminate\\Http\\RedirectResponse;\n",
        ""
    )

    content = re.sub(
        r":\s*RedirectResponse\b",
        ": mixed",
        content
    )

    content = re.sub(
        r":\s*\\\\?Illuminate\\\\Http\\\\RedirectResponse\b",
        ": mixed",
        content
    )

    content = re.sub(r"\n{3,}", "\n\n", content)

    if content != original:
        path.write_text(content)
        updated.append(str(path))

print("File Livewire yang diperbaiki:")
for item in updated:
    print("-", item)

if not updated:
    print("Tidak ada file Livewire yang perlu diperbaiki.")
PY

echo ""
echo "== Cek apakah masih ada RedirectResponse di Livewire =="

if grep -R "RedirectResponse" -n src/app/Livewire 2>/dev/null; then
    echo ""
    echo "[ERROR] Masih ada RedirectResponse di Livewire. Cek file di atas."
    exit 1
else
    echo "[OK] Tidak ada RedirectResponse di Livewire."
fi

echo ""
echo "== Cek syntax semua file Livewire Auth =="

docker compose exec php php -l app/Livewire/Auth/Login.php
docker compose exec php php -l app/Livewire/Auth/RegisterDonor.php
docker compose exec php php -l app/Livewire/Auth/RegisterPemohonDonor.php

echo ""
echo "== Clear cache =="

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "Fix Livewire redirect selesai."
