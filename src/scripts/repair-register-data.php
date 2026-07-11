<?php

declare(strict_types=1);

use App\Models\ProfilPendonor;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$fixMode = in_array('--fix', $argv, true);

$stats = [
    'checked' => 0,
    'fixed' => 0,
    'warning' => 0,
];

function judul(string $text): void
{
    echo PHP_EOL;
    echo '==================================================' . PHP_EOL;
    echo $text . PHP_EOL;
    echo '==================================================' . PHP_EOL;
}

function ok(string $text): void
{
    echo '[OK] ' . $text . PHP_EOL;
}

function infoLine(string $text): void
{
    echo '[INFO] ' . $text . PHP_EOL;
}

function warnLine(string $text): void
{
    global $stats;

    $stats['warning']++;

    echo '[WARN] ' . $text . PHP_EOL;
}

function fixLine(string $text): void
{
    global $stats;

    $stats['fixed']++;

    echo '[FIXED] ' . $text . PHP_EOL;
}

function kolomAda(string $table, string $column): bool
{
    return Schema::hasTable($table)
        && Schema::hasColumn($table, $column);
}

function rolesUser(User $user): Collection
{
    if (! method_exists($user, 'getRoleNames')) {
        return collect();
    }

    return $user->getRoleNames()
        ->map(fn (string $role): string => strtolower(trim($role)))
        ->filter()
        ->values();
}

function punyaRole(User $user): bool
{
    return rolesUser($user)->isNotEmpty();
}

function userAdmin(User $user): bool
{
    return rolesUser($user)->contains(
        fn (string $role): bool => in_array(
            $role,
            [
                'super_admin',
                'super-admin',
                'admin',
                'petugas',
            ],
            true
        )
    );
}

function userPendonor(User $user): bool
{
    return rolesUser($user)->contains(
        fn (string $role): bool =>
            $role === 'donor'
            || $role === 'pendonor'
            || str_contains($role, 'pendonor')
    );
}

function userPemohonDonor(User $user): bool
{
    return rolesUser($user)->contains(
        fn (string $role): bool =>
            $role === 'rumah_sakit'
            || $role === 'rumah-sakit'
            || $role === 'pemohon_donor'
            || $role === 'pemohon-donor'
            || $role === 'pemohon'
            || str_contains($role, 'pemohon')
            || str_contains($role, 'rumah')
            || str_contains($role, 'sakit')
            || str_contains($role, 'hospital')
    );
}

function cariProfilPendonor(User $user): ?ProfilPendonor
{
    $profil = new ProfilPendonor();
    $table = $profil->getTable();

    foreach (['pengguna_id', 'user_id'] as $column) {
        if (! kolomAda($table, $column)) {
            continue;
        }

        $result = ProfilPendonor::query()
            ->where($column, $user->getKey())
            ->first();

        if ($result instanceof ProfilPendonor) {
            return $result;
        }
    }

    return null;
}

function cariProfilRumahSakit(User $user): ?ProfilRumahSakit
{
    $profil = new ProfilRumahSakit();
    $table = $profil->getTable();

    foreach (['pengguna_id', 'user_id'] as $column) {
        if (! kolomAda($table, $column)) {
            continue;
        }

        $result = ProfilRumahSakit::query()
            ->where($column, $user->getKey())
            ->first();

        if ($result instanceof ProfilRumahSakit) {
            return $result;
        }
    }

    return null;
}

function roleUtamaPendonor(): string
{
    $candidates = [
        'pendonor',
        'donor',
    ];

    foreach ($candidates as $candidate) {
        if (
            Role::query()
                ->where('name', $candidate)
                ->where('guard_name', 'web')
                ->exists()
        ) {
            return $candidate;
        }
    }

    return 'pendonor';
}

function roleUtamaPemohonDonor(): string
{
    $candidates = [
        'rumah_sakit',
        'pemohon_donor',
        'pemohon-donor',
        'pemohon',
    ];

    foreach ($candidates as $candidate) {
        if (
            Role::query()
                ->where('name', $candidate)
                ->where('guard_name', 'web')
                ->exists()
        ) {
            return $candidate;
        }
    }

    return 'rumah_sakit';
}

function pastikanRoleAda(string $roleName, bool $fixMode): void
{
    if (
        Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->exists()
    ) {
        return;
    }

    if (! $fixMode) {
        infoLine("DRY-RUN: role {$roleName} akan dibuat.");
        return;
    }

    Role::findOrCreate($roleName, 'web');

    fixLine("Role {$roleName} dibuat.");
}

function assignRoleAman(User $user, string $roleName, bool $fixMode): void
{
    if (! method_exists($user, 'assignRole')) {
        warnLine("User #{$user->id} tidak mendukung assignRole.");
        return;
    }

    if (! $fixMode) {
        infoLine("DRY-RUN: user #{$user->id} {$user->email} akan diberi role {$roleName}.");
        return;
    }

    $user->assignRole($roleName);

    fixLine("User #{$user->id} {$user->email} diberi role {$roleName}.");
}

function cekKolomWajib(Model $model, array $columns, string $label): void
{
    $table = $model->getTable();
    $emptyColumns = [];

    foreach ($columns as $column) {
        if (! kolomAda($table, $column)) {
            continue;
        }

        if (blank($model->getAttribute($column))) {
            $emptyColumns[] = $column;
        }
    }

    if ($emptyColumns !== []) {
        warnLine($label . ' masih kosong: ' . implode(', ', $emptyColumns));
    }
}

judul('MODE SCRIPT');

if ($fixMode) {
    warnLine('Mode FIX aktif. Data bisa berubah.');
} else {
    infoLine('Mode DRY-RUN aktif. Data belum diubah.');
    infoLine('Untuk memperbaiki data, jalankan: php scripts/repair-register-data.php --fix');
}

$rolePendonor = roleUtamaPendonor();
$rolePemohonDonor = roleUtamaPemohonDonor();

judul('ROLE UTAMA');

infoLine("Role pendonor yang dipakai: {$rolePendonor}");
infoLine("Role pemohon donor yang dipakai: {$rolePemohonDonor}");

pastikanRoleAda($rolePendonor, $fixMode);
pastikanRoleAda($rolePemohonDonor, $fixMode);

judul('REPAIR USER TANPA ROLE');

$users = User::query()
    ->orderBy('id')
    ->get();

foreach ($users as $user) {
    $stats['checked']++;

    $profilPendonor = cariProfilPendonor($user);
    $profilRumahSakit = cariProfilRumahSakit($user);

    if (! punyaRole($user)) {
        if ($profilPendonor instanceof ProfilPendonor) {
            assignRoleAman($user, $rolePendonor, $fixMode);
            continue;
        }

        if ($profilRumahSakit instanceof ProfilRumahSakit) {
            assignRoleAman($user, $rolePemohonDonor, $fixMode);
            continue;
        }

        warnLine("User #{$user->id} {$user->email} belum punya role dan belum punya profil.");
    }
}

judul('REPAIR EMAIL VERIFIED USER GOOGLE');

if (
    kolomAda('users', 'google_id')
    && kolomAda('users', 'email_verified_at')
) {
    $googleUsers = User::query()
        ->whereNotNull('google_id')
        ->where('google_id', '!=', '')
        ->whereNull('email_verified_at')
        ->get();

    if ($googleUsers->isEmpty()) {
        ok('Tidak ada user Google yang email_verified_at kosong.');
    } else {
        foreach ($googleUsers as $user) {
            if (! $fixMode) {
                infoLine("DRY-RUN: email_verified_at user Google #{$user->id} {$user->email} akan diisi.");
                continue;
            }

            $user->forceFill([
                'email_verified_at' => now(),
            ]);

            $user->save();

            fixLine("email_verified_at user Google #{$user->id} {$user->email} diisi.");
        }
    }
} else {
    warnLine('Kolom google_id atau email_verified_at tidak tersedia.');
}

judul('NORMALISASI EMAIL LOWERCASE');

foreach (User::query()->orderBy('id')->get() as $user) {
    $email = trim((string) $user->getAttribute('email'));
    $emailLower = mb_strtolower($email);

    if ($email === '' || $email === $emailLower) {
        continue;
    }

    $emailLowerDipakai = User::query()
        ->where('email', $emailLower)
        ->whereKeyNot($user->getKey())
        ->exists();

    if ($emailLowerDipakai) {
        warnLine("Email user #{$user->id} tidak bisa dinormalisasi karena versi lowercase sudah dipakai: {$emailLower}");
        continue;
    }

    if (! $fixMode) {
        infoLine("DRY-RUN: email user #{$user->id} akan diubah {$email} -> {$emailLower}");
        continue;
    }

    $user->forceFill([
        'email' => $emailLower,
    ]);

    $user->save();

    fixLine("Email user #{$user->id} dinormalisasi menjadi {$emailLower}.");
}

judul('AUDIT PROFIL PENDONOR');

foreach (User::query()->orderBy('id')->get() as $user) {
    if (! userPendonor($user)) {
        continue;
    }

    $profil = cariProfilPendonor($user);

    if (! $profil instanceof ProfilPendonor) {
        warnLine("User pendonor #{$user->id} {$user->email} belum punya ProfilPendonor.");
        continue;
    }

    cekKolomWajib(
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
}

judul('AUDIT PROFIL PEMOHON DONOR');

foreach (User::query()->orderBy('id')->get() as $user) {
    if (! userPemohonDonor($user)) {
        continue;
    }

    $profil = cariProfilRumahSakit($user);

    if (! $profil instanceof ProfilRumahSakit) {
        warnLine("User pemohon donor #{$user->id} {$user->email} belum punya ProfilRumahSakit.");
        continue;
    }

    $namaInstitusiColumn = kolomAda($profil->getTable(), 'nama_rumah_sakit')
        ? 'nama_rumah_sakit'
        : 'nama_institusi';

    cekKolomWajib(
        $profil,
        [
            $namaInstitusiColumn,
            'nomor_izin',
            'nama_penanggung_jawab',
            'alamat',
            'provinsi',
            'kota',
        ],
        "ProfilRumahSakit user #{$user->id}"
    );
}

judul('CEK DUPLIKAT EMAIL');

$duplicateEmails = DB::table('users')
    ->select('email', DB::raw('COUNT(*) as total'))
    ->whereNotNull('email')
    ->groupBy('email')
    ->having('total', '>', 1)
    ->get();

if ($duplicateEmails->isEmpty()) {
    ok('Tidak ada email dobel.');
} else {
    foreach ($duplicateEmails as $item) {
        warnLine("Email dobel: {$item->email} total {$item->total}");
    }
}

judul('CEK DUPLIKAT GOOGLE ID');

if (kolomAda('users', 'google_id')) {
    $duplicateGoogleIds = DB::table('users')
        ->select('google_id', DB::raw('COUNT(*) as total'))
        ->whereNotNull('google_id')
        ->where('google_id', '!=', '')
        ->groupBy('google_id')
        ->having('total', '>', 1)
        ->get();

    if ($duplicateGoogleIds->isEmpty()) {
        ok('Tidak ada google_id dobel.');
    } else {
        foreach ($duplicateGoogleIds as $item) {
            warnLine("Google ID dobel: {$item->google_id} total {$item->total}");
        }
    }
}

if ($fixMode && class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
}

judul('RINGKASAN');

echo 'Mode          : ' . ($fixMode ? 'FIX' : 'DRY-RUN') . PHP_EOL;
echo 'User dicek    : ' . $stats['checked'] . PHP_EOL;
echo 'Data diperbaiki: ' . $stats['fixed'] . PHP_EOL;
echo 'Warning       : ' . $stats['warning'] . PHP_EOL;

echo PHP_EOL;
echo 'Selesai.' . PHP_EOL;
