<?php

declare(strict_types=1);

use App\Models\ProfilPendonor;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

function tampilkanJudul(string $judul): void
{
    echo PHP_EOL;
    echo "==================================================" . PHP_EOL;
    echo $judul . PHP_EOL;
    echo "==================================================" . PHP_EOL;
}

function tampilkanOk(string $pesan): void
{
    echo "[OK] {$pesan}" . PHP_EOL;
}

function tampilkanWarn(string $pesan): void
{
    echo "[WARN] {$pesan}" . PHP_EOL;
}

function nilaiKolom(Model $model, string $kolom): mixed
{
    return $model->getAttribute($kolom);
}

function kolomAda(string $tabel, string $kolom): bool
{
    return Schema::hasTable($tabel)
        && Schema::hasColumn($tabel, $kolom);
}

function rolesUser(User $user): array
{
    if (! method_exists($user, 'getRoleNames')) {
        return [];
    }

    return $user->getRoleNames()
        ->map(fn (string $role): string => strtolower(trim($role)))
        ->filter()
        ->values()
        ->all();
}

function userAdalahPendonor(User $user): bool
{
    foreach (rolesUser($user) as $role) {
        if (
            $role === 'donor'
            || $role === 'pendonor'
            || str_contains($role, 'pendonor')
        ) {
            return true;
        }
    }

    return false;
}

function userAdalahPemohonDonor(User $user): bool
{
    foreach (rolesUser($user) as $role) {
        if (
            $role === 'rumah_sakit'
            || $role === 'rumah-sakit'
            || $role === 'pemohon_donor'
            || $role === 'pemohon-donor'
            || $role === 'pemohon'
            || str_contains($role, 'pemohon')
            || str_contains($role, 'rumah')
            || str_contains($role, 'sakit')
            || str_contains($role, 'hospital')
        ) {
            return true;
        }
    }

    return false;
}

function cariProfilPendonor(User $user): ?ProfilPendonor
{
    $profil = new ProfilPendonor();
    $tabel = $profil->getTable();

    foreach (['pengguna_id', 'user_id'] as $kolomRelasi) {
        if (! kolomAda($tabel, $kolomRelasi)) {
            continue;
        }

        $hasil = ProfilPendonor::query()
            ->where($kolomRelasi, $user->getKey())
            ->first();

        if ($hasil instanceof ProfilPendonor) {
            return $hasil;
        }
    }

    return null;
}

function cariProfilRumahSakit(User $user): ?ProfilRumahSakit
{
    $profil = new ProfilRumahSakit();
    $tabel = $profil->getTable();

    foreach (['pengguna_id', 'user_id'] as $kolomRelasi) {
        if (! kolomAda($tabel, $kolomRelasi)) {
            continue;
        }

        $hasil = ProfilRumahSakit::query()
            ->where($kolomRelasi, $user->getKey())
            ->first();

        if ($hasil instanceof ProfilRumahSakit) {
            return $hasil;
        }
    }

    return null;
}

function cekKolomWajibProfil(Model $profil, array $kolomWajib, string $label): array
{
    $tabel = $profil->getTable();
    $kosong = [];

    foreach ($kolomWajib as $kolom) {
        if (! kolomAda($tabel, $kolom)) {
            continue;
        }

        if (blank(nilaiKolom($profil, $kolom))) {
            $kosong[] = $kolom;
        }
    }

    if ($kosong === []) {
        return [];
    }

    return [
        "{$label} kolom kosong: " . implode(', ', $kosong),
    ];
}

tampilkanJudul('AUDIT USER DAN ROLE');

$users = User::query()
    ->latest('id')
    ->limit(30)
    ->get();

if ($users->isEmpty()) {
    tampilkanWarn('Belum ada data user.');
} else {
    foreach ($users as $user) {
        $roles = rolesUser($user);
        $roleText = $roles === []
            ? '-'
            : implode(', ', $roles);

        $googleIdAda = kolomAda('users', 'google_id')
            ? filled($user->getAttribute('google_id'))
            : false;

        echo "#{$user->id} | {$user->name} | {$user->email} | roles: {$roleText} | google_id: " . ($googleIdAda ? 'ada' : '-') . PHP_EOL;

        if ($roles === []) {
            tampilkanWarn("User #{$user->id} belum punya role.");
        }
    }
}

tampilkanJudul('AUDIT DUPLICATE EMAIL');

$duplikatEmail = DB::table('users')
    ->select('email', DB::raw('COUNT(*) as total'))
    ->whereNotNull('email')
    ->groupBy('email')
    ->having('total', '>', 1)
    ->get();

if ($duplikatEmail->isEmpty()) {
    tampilkanOk('Tidak ada email dobel.');
} else {
    foreach ($duplikatEmail as $item) {
        tampilkanWarn("Email dobel: {$item->email} total {$item->total}");
    }
}

tampilkanJudul('AUDIT DUPLICATE GOOGLE ID');

if (! kolomAda('users', 'google_id')) {
    tampilkanWarn('Kolom users.google_id tidak ditemukan.');
} else {
    $duplikatGoogleId = DB::table('users')
        ->select('google_id', DB::raw('COUNT(*) as total'))
        ->whereNotNull('google_id')
        ->where('google_id', '!=', '')
        ->groupBy('google_id')
        ->having('total', '>', 1)
        ->get();

    if ($duplikatGoogleId->isEmpty()) {
        tampilkanOk('Tidak ada google_id dobel.');
    } else {
        foreach ($duplikatGoogleId as $item) {
            tampilkanWarn("Google ID dobel: {$item->google_id} total {$item->total}");
        }
    }
}

tampilkanJudul('AUDIT PROFIL PENDONOR');

$pendonorUsers = User::query()
    ->get()
    ->filter(fn (User $user): bool => userAdalahPendonor($user))
    ->values();

if ($pendonorUsers->isEmpty()) {
    tampilkanWarn('Belum ada user dengan role pendonor.');
} else {
    foreach ($pendonorUsers as $user) {
        $profil = cariProfilPendonor($user);

        if (! $profil instanceof ProfilPendonor) {
            tampilkanWarn("User pendonor #{$user->id} {$user->email} belum punya ProfilPendonor.");
            continue;
        }

        $warnings = cekKolomWajibProfil(
            $profil,
            [
                'tanggal_lahir',
                'jenis_kelamin',
                'golongan_darah',
                'rhesus',
                'alamat',
                'provinsi',
                'kota',
            ],
            "ProfilPendonor user #{$user->id}"
        );

        if ($warnings === []) {
            tampilkanOk("User pendonor #{$user->id} {$user->email} profil lengkap.");
        } else {
            foreach ($warnings as $warning) {
                tampilkanWarn($warning);
            }
        }
    }
}

tampilkanJudul('AUDIT PROFIL PEMOHON DONOR');

$pemohonUsers = User::query()
    ->get()
    ->filter(fn (User $user): bool => userAdalahPemohonDonor($user))
    ->values();

if ($pemohonUsers->isEmpty()) {
    tampilkanWarn('Belum ada user dengan role pemohon donor / rumah sakit.');
} else {
    foreach ($pemohonUsers as $user) {
        $profil = cariProfilRumahSakit($user);

        if (! $profil instanceof ProfilRumahSakit) {
            tampilkanWarn("User pemohon donor #{$user->id} {$user->email} belum punya ProfilRumahSakit.");
            continue;
        }

        $kolomNamaInstitusi = kolomAda($profil->getTable(), 'nama_rumah_sakit')
            ? 'nama_rumah_sakit'
            : 'nama_institusi';

        $warnings = cekKolomWajibProfil(
            $profil,
            [
                $kolomNamaInstitusi,
                'nomor_izin',
                'nama_penanggung_jawab',
                'alamat',
                'provinsi',
                'kota',
            ],
            "ProfilRumahSakit user #{$user->id}"
        );

        if ($warnings === []) {
            tampilkanOk("User pemohon donor #{$user->id} {$user->email} profil lengkap.");
        } else {
            foreach ($warnings as $warning) {
                tampilkanWarn($warning);
            }
        }
    }
}

tampilkanJudul('AUDIT EMAIL VERIFIED GOOGLE');

if (
    kolomAda('users', 'google_id')
    && kolomAda('users', 'email_verified_at')
) {
    $googleBelumVerified = User::query()
        ->whereNotNull('google_id')
        ->where('google_id', '!=', '')
        ->whereNull('email_verified_at')
        ->get();

    if ($googleBelumVerified->isEmpty()) {
        tampilkanOk('Semua user Google sudah email_verified_at.');
    } else {
        foreach ($googleBelumVerified as $user) {
            tampilkanWarn("User Google #{$user->id} {$user->email} email_verified_at masih kosong.");
        }
    }
}

tampilkanJudul('RINGKASAN');

echo "Total user               : " . User::query()->count() . PHP_EOL;
echo "Total user pendonor      : " . $pendonorUsers->count() . PHP_EOL;
echo "Total user pemohon donor : " . $pemohonUsers->count() . PHP_EOL;

echo PHP_EOL;
echo "Audit selesai." . PHP_EOL;
