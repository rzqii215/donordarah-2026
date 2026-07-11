#!/usr/bin/env bash

set -euo pipefail

echo "== Update alur registrasi dan verifikasi email =="

python3 - <<'PY'
from pathlib import Path
from shutil import copy2

files = {
    "donor": Path("src/app/Livewire/Auth/RegisterDonor.php"),
    "pemohon": Path("src/app/Livewire/Auth/RegisterPemohonDonor.php"),
}

backup_dir = Path("scripts/backups-registration-verification")
backup_dir.mkdir(parents=True, exist_ok=True)

for name, path in files.items():
    if not path.exists():
        raise SystemExit(f"[ERROR] File tidak ditemukan: {path}")

    copy2(
        path,
        backup_dir / f"{path.name}.bak"
    )

def replace_once(
    content: str,
    old: str,
    new: str,
    label: str,
) -> str:
    if old not in content:
        raise SystemExit(
            f"[ERROR] Pola tidak ditemukan: {label}"
        )

    return content.replace(
        old,
        new,
        1
    )

# ==========================================================
# Register Donor
# ==========================================================

donor_path = files["donor"]
donor = donor_path.read_text()

donor = replace_once(
    donor,
    """use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;""",
    """use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;""",
    "import Auth RegisterDonor",
)

donor = replace_once(
    donor,
    """        DB::transaction(function () use ($data, $googleRegister): void {""",
    """        $user = DB::transaction(function () use ($data, $googleRegister): User {""",
    "transaction RegisterDonor",
)

donor = replace_once(
    donor,
    """            $profil->save();
        });""",
    """            $profil->save();

            return $user;
        });""",
    "return user RegisterDonor",
)

donor = replace_once(
    donor,
    """        session()->forget('google_register');

        return redirect()
            ->to('/login')
            ->with(
                'success',
                'Pendaftaran pendonor berhasil. Silakan masuk menggunakan akun yang sudah dibuat.'
            );""",
    """        session()->forget('google_register');

        Auth::login($user);

        request()->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with(
                    'success',
                    'Pendaftaran pendonor berhasil. Link verifikasi telah dikirim ke email Anda.'
                );
        }

        return redirect()
            ->to('/donor')
            ->with(
                'success',
                'Pendaftaran pendonor dengan Google berhasil.'
            );""",
    "redirect RegisterDonor",
)

donor_path.write_text(donor)

# ==========================================================
# Register Pemohon Donor
# ==========================================================

pemohon_path = files["pemohon"]
pemohon = pemohon_path.read_text()

pemohon = replace_once(
    pemohon,
    """use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;""",
    """use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;""",
    "import Auth RegisterPemohonDonor",
)

pemohon = replace_once(
    pemohon,
    """        DB::transaction(function () use ($data, $googleRegister): void {""",
    """        $user = DB::transaction(function () use ($data, $googleRegister): User {""",
    "transaction RegisterPemohonDonor",
)

pemohon = replace_once(
    pemohon,
    """            $profil->save();
        });""",
    """            $profil->save();

            return $user;
        });""",
    "return user RegisterPemohonDonor",
)

pemohon = replace_once(
    pemohon,
    """        session()->forget('google_register');

        return redirect()
            ->to('/login')
            ->with(
                'success',
                'Pendaftaran pemohon donor berhasil. Silakan masuk menggunakan akun yang sudah dibuat.'
            );""",
    """        session()->forget('google_register');

        Auth::login($user);

        request()->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with(
                    'success',
                    'Pendaftaran pemohon donor berhasil. Link verifikasi telah dikirim ke email Anda.'
                );
        }

        return redirect()
            ->to('/pemohon-donor')
            ->with(
                'success',
                'Pendaftaran pemohon donor dengan Google berhasil.'
            );""",
    "redirect RegisterPemohonDonor",
)

pemohon_path.write_text(pemohon)

print("[OK] RegisterDonor.php diperbarui.")
print("[OK] RegisterPemohonDonor.php diperbarui.")
print(f"[OK] Backup tersedia di {backup_dir}.")
PY

echo ""
echo "== Periksa syntax =="

docker compose exec php php -l app/Livewire/Auth/RegisterDonor.php
docker compose exec php php -l app/Livewire/Auth/RegisterPemohonDonor.php

echo ""
echo "== Bersihkan cache =="

docker compose exec php php artisan optimize:clear

echo ""
echo "Update alur registrasi selesai."