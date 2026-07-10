#!/usr/bin/env bash

set -euo pipefail

echo ""
echo "== 1. Buat component final polish auth UI =="

mkdir -p src/resources/views/components/auth

cat > src/resources/views/components/auth/auth-ui-final-polish.blade.php <<'BLADE'
@once
    <style>
        :root {
            --auth-red: #dc2626;
            --auth-red-soft: #fee2e2;
            --auth-red-hover: #b91c1c;
            --auth-ink: #0f172a;
            --auth-muted: #64748b;
            --auth-line: #e2e8f0;
            --auth-soft: #f8fafc;
            --auth-white: #ffffff;
            --auth-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
            --auth-radius: 24px;
        }

        .login-page,
        .register-donor-page,
        .register-pemohon-page {
            color: var(--auth-ink);
            text-rendering: geometricPrecision;
        }

        .login-card,
        .register-donor-section,
        .register-pemohon-section,
        .register-donor-choice article,
        .register-pemohon-choice article,
        .register-donor-method-active,
        .register-pemohon-method-active,
        .login-register-box {
            border-color: var(--auth-line) !important;
            border-radius: var(--auth-radius) !important;
            box-shadow: var(--auth-shadow) !important;
        }

        .login-heading h2,
        .register-donor-hero h1,
        .register-pemohon-hero h1,
        .register-donor-section-heading h2,
        .register-pemohon-section-heading h2,
        .register-donor-choice h2,
        .register-pemohon-choice h2 {
            color: var(--auth-ink) !important;
            letter-spacing: -0.055em;
        }

        .login-heading p,
        .login-visual-content > p,
        .register-donor-hero p,
        .register-pemohon-hero p,
        .register-donor-method-active span,
        .register-pemohon-method-active span {
            color: var(--auth-red) !important;
            font-weight: 1000 !important;
        }

        .login-heading span,
        .login-visual-content > span,
        .register-donor-hero span,
        .register-pemohon-hero span,
        .register-donor-section-heading p,
        .register-pemohon-section-heading p,
        .register-donor-choice p,
        .register-pemohon-choice p {
            color: var(--auth-muted) !important;
        }

        .login-google-button,
        .register-donor-choice a,
        .register-donor-choice button,
        .register-pemohon-choice a,
        .register-pemohon-choice button,
        .login-submit,
        .register-donor-actions button,
        .register-pemohon-actions button,
        .login-register-box a {
            transition:
                transform 160ms ease,
                box-shadow 160ms ease,
                border-color 160ms ease,
                background-color 160ms ease;
        }

        .login-google-button:hover,
        .register-donor-choice article:hover,
        .register-pemohon-choice article:hover {
            transform: translateY(-2px);
        }

        .login-submit,
        .register-donor-choice a,
        .register-donor-choice button,
        .register-pemohon-choice a,
        .register-pemohon-choice button,
        .register-donor-actions button,
        .register-pemohon-actions button,
        .login-register-box a:not(.is-outline) {
            background: var(--auth-red) !important;
        }

        .login-submit:hover,
        .register-donor-choice a:hover,
        .register-donor-choice button:hover,
        .register-pemohon-choice a:hover,
        .register-pemohon-choice button:hover,
        .register-donor-actions button:hover,
        .register-pemohon-actions button:hover,
        .login-register-box a:not(.is-outline):hover {
            background: var(--auth-red-hover) !important;
            box-shadow: 0 20px 44px rgba(220, 38, 38, 0.22) !important;
            transform: translateY(-1px);
        }

        .login-field div,
        .register-donor-field input,
        .register-donor-field select,
        .register-donor-field textarea,
        .register-pemohon-field input,
        .register-pemohon-field select,
        .register-pemohon-field textarea {
            border-color: var(--auth-line) !important;
            background: var(--auth-white) !important;
        }

        .login-field div:focus-within,
        .register-donor-field input:focus,
        .register-donor-field select:focus,
        .register-donor-field textarea:focus,
        .register-pemohon-field input:focus,
        .register-pemohon-field select:focus,
        .register-pemohon-field textarea:focus {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.44) !important;
        }

        .register-donor-field input:read-only,
        .register-pemohon-field input:read-only {
            color: var(--auth-muted) !important;
            background: var(--auth-soft) !important;
            cursor: not-allowed;
        }

        .login-google-button {
            min-height: 58px !important;
            border-radius: 18px !important;
            font-size: 14px !important;
        }

        .login-divider p {
            color: #94a3b8 !important;
            font-weight: 1000 !important;
        }

        .register-donor-choice,
        .register-pemohon-choice {
            align-items: stretch;
        }

        .register-donor-choice article,
        .register-pemohon-choice article {
            min-height: 290px;
        }

        .register-donor-choice a,
        .register-donor-choice button,
        .register-pemohon-choice a,
        .register-pemohon-choice button {
            margin-top: auto;
        }

        .register-donor-google-connected,
        .register-pemohon-google-connected {
            border-color: #bbf7d0 !important;
            background: #f0fdf4 !important;
            box-shadow: none !important;
        }

        .register-donor-check,
        .register-pemohon-check {
            border-color: var(--auth-red-soft) !important;
            background: #fff7f7 !important;
        }

        .login-alert,
        .register-donor-alert,
        .register-pemohon-alert {
            border-radius: 18px !important;
        }

        .login-note {
            color: #94a3b8 !important;
        }

        @media (max-width: 1100px) {
            .login-page {
                grid-template-columns: 1fr !important;
            }

            .login-visual {
                min-height: auto !important;
                padding-bottom: 72px !important;
            }

            .login-panel {
                padding-top: 48px !important;
            }
        }

        @media (max-width: 860px) {
            .register-donor-page,
            .register-pemohon-page {
                padding-inline: 18px !important;
            }

            .register-donor-choice,
            .register-pemohon-choice,
            .register-donor-grid,
            .register-pemohon-grid {
                grid-template-columns: 1fr !important;
            }

            .register-donor-choice article,
            .register-pemohon-choice article {
                min-height: auto;
            }
        }

        @media (max-width: 640px) {
            .login-visual,
            .login-panel {
                padding-inline: 20px !important;
            }

            .login-visual-content h1,
            .register-donor-hero h1,
            .register-pemohon-hero h1 {
                font-size: 42px !important;
                letter-spacing: -0.06em !important;
            }

            .login-heading h2 {
                font-size: 32px !important;
            }

            .login-card,
            .register-donor-section,
            .register-pemohon-section,
            .register-donor-choice article,
            .register-pemohon-choice article {
                border-radius: 20px !important;
            }

            .register-donor-actions,
            .register-pemohon-actions,
            .register-donor-method-active,
            .register-pemohon-method-active {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .register-donor-actions button,
            .register-donor-actions a,
            .register-pemohon-actions button,
            .register-pemohon-actions a,
            .register-donor-method-active button,
            .register-pemohon-method-active button {
                width: 100% !important;
            }
        }
    </style>
@endonce
BLADE

echo ""
echo "== 2. Bersihkan include polish dobel =="

python3 - <<'PY'
from pathlib import Path
import re

views_dir = Path("src/resources/views")
include = "@include('components.auth.auth-ui-final-polish')"

updated = []

for path in views_dir.rglob("*.blade.php"):
    content = path.read_text()
    original = content

    content = re.sub(
        r"\n?\s*@include\(\s*['\"]components\.auth\.auth-ui-final-polish['\"]\s*\)\s*",
        "\n",
        content
    )

    content = re.sub(r"\n{3,}", "\n\n", content)

    if content != original:
        path.write_text(content)
        updated.append(str(path))

for item in updated:
    print("-", item)

if not updated:
    print("Tidak ada include polish dobel.")
PY

echo ""
echo "== 3. Pasang polish hanya ke layout auth =="

python3 - <<'PY'
from pathlib import Path
import re

include = "@include('components.auth.auth-ui-final-polish')"

targets = [
    Path("src/resources/views/components/layouts/auth.blade.php"),
    Path("src/resources/views/layouts/auth.blade.php"),
]

updated = []

for path in targets:
    if not path.exists():
        continue

    content = path.read_text()

    if include in content:
        continue

    if re.search(r"</body>", content, flags=re.IGNORECASE):
        content = re.sub(
            r"</body>",
            f"    {include}\n</body>",
            content,
            count=1,
            flags=re.IGNORECASE
        )
    else:
        content = content.rstrip() + "\n\n" + include + "\n"

    path.write_text(content)
    updated.append(str(path))

for item in updated:
    print("-", item)

if not updated:
    print("Tidak ada layout auth yang perlu diubah.")
PY

echo ""
echo "== 4. Cek tidak recursive =="

if grep -n "components.auth.auth-ui-final-polish" src/resources/views/components/auth/auth-ui-final-polish.blade.php 2>/dev/null; then
    echo "[ERROR] auth-ui-final-polish include dirinya sendiri."
    exit 1
fi

echo "[OK] auth-ui-final-polish tidak recursive."

echo ""
echo "== 5. Cek syntax =="

docker compose exec php php -l resources/views/components/auth/auth-ui-final-polish.blade.php

echo ""
echo "== 6. Clear cache =="

docker compose exec php sh -lc "rm -f storage/framework/views/*.php"
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

docker compose restart php

echo ""
echo "Polish UI auth selesai."
