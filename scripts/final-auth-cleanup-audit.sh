#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Bersihkan compiled view dan cache Laravel =="

docker compose exec php sh -lc "rm -f storage/framework/views/*.php"
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 2. Cek syntax file penting =="

FILES=(
    "routes/web.php"
    "routes/google-auth.php"
    "routes/logout-public.php"
    "routes/logout.php"
    "config/services.php"
    "bootstrap/app.php"
    "app/Http/Controllers/Auth/GoogleAuthController.php"
    "app/Http/Controllers/Auth/KeluarController.php"
    "app/Http/Middleware/PastikanAksesPortalSesuaiRole.php"
    "app/Livewire/Auth/Login.php"
    "app/Livewire/Auth/RegisterDonor.php"
    "app/Livewire/Auth/RegisterPemohonDonor.php"
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
echo "== 3. Cek recursive include lama =="

if grep -R "components.auth.logout-post-bridge\|components.shared.flash-message" -n src/resources/views 2>/dev/null; then
    echo ""
    echo "[ERROR] Masih ada include lama yang berpotensi recursive."
    exit 1
else
    echo "[OK] Tidak ada include lama recursive."
fi

echo ""
echo "== 4. Cek safe flash tidak include dirinya sendiri =="

if grep -n "components.shared.safe-flash-message" src/resources/views/components/shared/safe-flash-message.blade.php 2>/dev/null; then
    echo ""
    echo "[ERROR] safe-flash-message include dirinya sendiri."
    exit 1
else
    echo "[OK] safe-flash-message tidak recursive."
fi

echo ""
echo "== 5. Cek command terminal tidak masuk file PHP =="

if grep -R "cat > src/\|<<'PHP'\|<<'BLADE'\|^PHP$" -n src/routes src/app src/config src/bootstrap 2>/dev/null; then
    echo ""
    echo "[ERROR] Ada command terminal/heredoc marker masuk ke file source."
    exit 1
else
    echo "[OK] Tidak ada command terminal masuk file source."
fi

echo ""
echo "== 6. Cek Google button dobel di login =="

LOGIN_FILE="src/resources/views/livewire/auth/login.blade.php"

if [ -f "$LOGIN_FILE" ]; then
    GOOGLE_INCLUDE_COUNT="$(grep -c "components.auth.google-auth-actions" "$LOGIN_FILE" || true)"
    GOOGLE_TEXT_COUNT="$(grep -c "Masuk dengan Google" "$LOGIN_FILE" || true)"

    echo "google-auth-actions include count : $GOOGLE_INCLUDE_COUNT"
    echo "Masuk dengan Google text count    : $GOOGLE_TEXT_COUNT"

    if [ "$GOOGLE_INCLUDE_COUNT" -gt 0 ]; then
        echo "[ERROR] Masih ada google-auth-actions lama di login."
        exit 1
    fi

    if [ "$GOOGLE_TEXT_COUNT" -gt 1 ]; then
        echo "[ERROR] Tombol/text Masuk dengan Google masih dobel."
        exit 1
    fi

    echo "[OK] Google login tidak dobel."
else
    echo "[SKIP] login.blade.php tidak ditemukan."
fi

echo ""
echo "== 7. Cek route Google =="

docker compose exec php php artisan route:list --name=google

echo ""
echo "== 8. Cek route logout =="

docker compose exec php php artisan route:list --path=logout

echo ""
echo "== 9. Cek require route di web.php =="

grep -n "google-auth.php" src/routes/web.php || {
    echo "[ERROR] google-auth.php belum direquire di routes/web.php"
    exit 1
}

grep -n "logout-public.php" src/routes/web.php || {
    echo "[ERROR] logout-public.php belum direquire di routes/web.php"
    exit 1
}

echo ""
echo "== 10. Cek config OAuth =="

docker compose exec php php artisan tinker --execute='dump([
    "app_env" => config("app.env"),
    "app_url" => config("app.url"),
    "google_client_id_ada" => filled(config("services.google.client_id")),
    "google_secret_ada" => filled(config("services.google.client_secret")),
    "google_redirect" => config("services.google.redirect"),
    "session_domain" => config("session.domain"),
    "session_secure" => config("session.secure"),
]);'

echo ""
echo "== 11. Git status =="

git status --short

echo ""
echo "Final audit selesai."
