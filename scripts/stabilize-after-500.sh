#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Hapus include recursive dari semua Blade =="

python3 - <<'PY'
from pathlib import Path
import re

views_dir = Path("src/resources/views")

patterns = [
    r"\n?\s*@include\(\s*['\"]components\.auth\.logout-post-bridge['\"]\s*\)\s*",
    r"\n?\s*@include\(\s*['\"]components\.shared\.flash-message['\"]\s*\)\s*",
]

updated = []

for path in views_dir.rglob("*.blade.php"):
    content = path.read_text()
    original = content

    for pattern in patterns:
        content = re.sub(pattern, "\n", content)

    content = re.sub(r"\n{3,}", "\n\n", content)

    if content != original:
        path.write_text(content)
        updated.append(str(path))

for item in updated:
    print("-", item)

if not updated:
    print("Tidak ada include recursive.")
PY

echo ""
echo "== 2. Kosongkan component bridge/flash agar aman =="

mkdir -p src/resources/views/components/auth
mkdir -p src/resources/views/components/shared

cat > src/resources/views/components/auth/logout-post-bridge.blade.php <<'BLADE'
{{-- Dinonaktifkan sementara. Logout memakai route GET/POST langsung. --}}
BLADE

cat > src/resources/views/components/shared/flash-message.blade.php <<'BLADE'
{{-- Dinonaktifkan sementara untuk mencegah recursive view. --}}
BLADE

echo ""
echo "== 3. Rapikan route logout =="

cat > src/routes/logout-public.php <<'PHP'
<?php

use App\Http\Controllers\Auth\KeluarController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function (): void {
    Route::match([
        'GET',
        'POST',
    ], '/logout', KeluarController::class)
        ->name('logout');

    Route::match([
        'GET',
        'POST',
    ], '/donor/logout', KeluarController::class)
        ->name('donor.logout');

    Route::match([
        'GET',
        'POST',
    ], '/pemohon-donor/logout', KeluarController::class)
        ->name('pemohon-donor.logout');
});
PHP

cat > src/routes/logout.php <<'PHP'
<?php

/*
|--------------------------------------------------------------------------
| Logout Routes
|--------------------------------------------------------------------------
|
| File ini sengaja dikosongkan agar route logout tidak dobel.
| Route logout aktif ada di routes/logout-public.php.
|
*/
PHP

echo ""
echo "== 4. Rapikan require di routes/web.php =="

python3 - <<'PY'
from pathlib import Path
import re

path = Path("src/routes/web.php")

if not path.exists():
    raise SystemExit("src/routes/web.php tidak ditemukan.")

content = path.read_text()

content = re.sub(
    r"\n?require\s+__DIR__\s*\.\s*['\"]\/logout\.php['\"]\s*;\s*",
    "\n",
    content
)

content = re.sub(
    r"\n?require\s+__DIR__\s*\.\s*['\"]\/logout-public\.php['\"]\s*;\s*",
    "\n",
    content
)

content = content.rstrip() + "\n\nrequire __DIR__ . '/logout-public.php';\n"
content = re.sub(r"\n{3,}", "\n\n", content)

path.write_text(content)

print("routes/web.php selesai dirapikan.")
PY

echo ""
echo "== 5. Cek file PHP yang salah isi command terminal =="

if grep -R "cat > src/" -n src/routes src/app src/config 2>/dev/null; then
    echo ""
    echo "Ditemukan command terminal masuk ke file PHP. Perbaiki file di atas dulu."
    exit 1
else
    echo "Tidak ada command cat > yang masuk ke file PHP."
fi

if grep -R "<<'PHP'" -n src/routes src/app src/config 2>/dev/null; then
    echo ""
    echo "Ditemukan marker heredoc PHP masuk ke file. Perbaiki file di atas dulu."
    exit 1
else
    echo "Tidak ada marker heredoc yang masuk ke file PHP."
fi

echo ""
echo "== 6. Cek syntax =="

docker compose exec php php -l routes/logout-public.php
docker compose exec php php -l routes/logout.php
docker compose exec php php -l routes/web.php
docker compose exec php php -l app/Http/Controllers/Auth/KeluarController.php
docker compose exec php php -l app/Http/Middleware/PastikanAksesPortalSesuaiRole.php
docker compose exec php php -l resources/views/components/auth/logout-post-bridge.blade.php
docker compose exec php php -l resources/views/components/shared/flash-message.blade.php

echo ""
echo "== 7. Bersihkan compiled Blade, log, dan cache =="

docker compose exec php sh -lc "rm -f storage/framework/views/*.php"
docker compose exec php sh -lc ": > storage/logs/laravel.log"

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 8. Restart PHP container =="

docker compose restart php

echo ""
echo "== 9. Cek route logout =="

docker compose exec php php artisan route:list --path=logout

echo ""
echo "== 10. Cek include recursive tersisa =="

if grep -R "components.auth.logout-post-bridge\|components.shared.flash-message" -n src/resources/views 2>/dev/null; then
    echo ""
    echo "Masih ada include bridge/flash. Hapus dulu."
    exit 1
else
    echo "Tidak ada include recursive tersisa."
fi

echo ""
echo "Stabilisasi selesai."
