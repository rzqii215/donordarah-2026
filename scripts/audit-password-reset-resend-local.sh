#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Hapus cache Laravel =="

docker compose exec php sh -lc "rm -f bootstrap/cache/*.php"
docker compose exec php sh -lc "rm -f storage/framework/views/*.php"

docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

echo ""
echo "== 2. Cek syntax file forgot password =="

FILES=(
    "routes/password-reset.php"
    "app/Livewire/Auth/ForgotPassword.php"
    "app/Livewire/Auth/ResetPassword.php"
    "app/Notifications/Auth/ResetPasswordNotification.php"
    "app/Models/User.php"
    "config/mail.php"
    "config/services.php"
)

for file in "${FILES[@]}"; do
    if [ -f "src/$file" ]; then
        docker compose exec php php -l "$file"
    else
        echo "[SKIP] File tidak ada: src/$file"
    fi
done

echo ""
echo "== 3. Cek route password reset =="

grep -qxF "require __DIR__ . '/password-reset.php';" src/routes/web.php || {
    echo "[ERROR] routes/password-reset.php belum direquire di routes/web.php"
    exit 1
}

docker compose exec php php artisan route:list --name=password

echo ""
echo "== 4. Cek tabel password reset =="

docker compose exec php php artisan migrate

docker compose exec php php artisan tinker --execute='dump([
    "password_reset_tokens_table" => \Illuminate\Support\Facades\Schema::hasTable("password_reset_tokens"),
]);'

echo ""
echo "== 5. Cek config mail Resend tanpa menampilkan API key =="

docker compose exec php php artisan tinker --execute='dump([
    "app_url" => config("app.url"),
    "mail_default" => config("mail.default"),
    "resend_key_ada" => filled(config("services.resend.key")),
    "from_address" => config("mail.from.address"),
    "from_name" => config("mail.from.name"),
]);'

echo ""
echo "== 6. Cek marker terminal yang tidak boleh masuk source =="

if grep -R "^PHP$\|^BLADE$\|<<'PHP'\|<<'BLADE'\|cat > src/" -n src/routes src/app src/config src/database src/resources 2>/dev/null; then
    echo ""
    echo "[ERROR] Ada marker terminal masuk source."
    exit 1
else
    echo "[OK] Tidak ada marker terminal/heredoc di source."
fi

echo ""
echo "== 7. Cek apakah .env atau backup .env ikut tracked Git =="

TRACKED_ENV="$(git ls-files | grep -E '^src/\.env($|\.|_)' || true)"

if [ -n "$TRACKED_ENV" ]; then
    echo "[WARNING] File env berikut masih tracked Git:"
    echo "$TRACKED_ENV"
    echo ""
    echo "Jalankan:"
    echo "git rm --cached src/.env src/.env.* 2>/dev/null || true"
else
    echo "[OK] src/.env dan backup env tidak tracked Git."
fi

echo ""
echo "== 8. Scan API key Resend di file tracked Git =="

LEAKS="$(
    git ls-files \
        | grep -vE '(^src/\.env|^vendor/|^node_modules/|^src/vendor/|^src/node_modules/|composer.lock)' \
        | xargs -r grep -nE 're_[A-Za-z0-9_-]{10,}' || true
)"

if [ -n "$LEAKS" ]; then
    echo "[ERROR] Kemungkinan API key Resend bocor di file tracked:"
    echo "$LEAKS"
    exit 1
else
    echo "[OK] Tidak ada API key Resend di file tracked Git."
fi

echo ""
echo "== 9. Git status =="

git status --short

echo ""
echo "Audit final password reset Resend local selesai."
