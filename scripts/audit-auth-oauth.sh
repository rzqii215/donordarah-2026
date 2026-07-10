#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== Audit syntax PHP =="
docker compose exec php php -l app/Http/Controllers/Auth/GoogleAuthController.php
docker compose exec php php -l app/Http/Controllers/Auth/KeluarController.php
docker compose exec php php -l app/Http/Middleware/PastikanAksesPortalSesuaiRole.php
docker compose exec php php -l app/Livewire/Auth/Login.php
docker compose exec php php -l app/Livewire/Auth/RegisterDonor.php
docker compose exec php php -l app/Livewire/Auth/RegisterPemohonDonor.php
docker compose exec php php -l routes/google-auth.php
docker compose exec php php -l routes/logout-public.php
docker compose exec php php -l bootstrap/app.php
docker compose exec php php -l config/services.php

echo ""
echo "== Clear cache =="
dca optimize:clear
dca config:clear
dca route:clear
dca view:clear

echo ""
echo "== Cek Google config =="
dca tinker --execute='dump([
    "app_env" => config("app.env"),
    "app_url" => config("app.url"),
    "google_client_id_ada" => filled(config("services.google.client_id")),
    "google_secret_ada" => filled(config("services.google.client_secret")),
    "google_redirect" => config("services.google.redirect"),
    "session_domain" => config("session.domain"),
    "session_secure" => config("session.secure"),
    "session_same_site" => config("session.same_site"),
]);'

echo ""
echo "== Cek route Google =="
dca route:list --name=google

echo ""
echo "== Cek route logout =="
dca route:list --path=logout

echo ""
echo "== Cek route login/register =="
dca route:list | grep -E "login|register|google|logout" || true

echo ""
echo "== Cek middleware role terdaftar =="
grep -n "PastikanAksesPortalSesuaiRole" bootstrap/app.php || {
    echo "Middleware PastikanAksesPortalSesuaiRole belum terdaftar di bootstrap/app.php"
    exit 1
}

echo ""
echo "== Cek require route =="
grep -n "google-auth.php" routes/web.php || {
    echo "routes/google-auth.php belum direquire di routes/web.php"
    exit 1
}

grep -n "logout-public.php" routes/web.php || {
    echo "routes/logout-public.php belum direquire di routes/web.php"
    exit 1
}

echo ""
echo "== Audit selesai =="
