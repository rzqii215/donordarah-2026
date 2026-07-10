<?php

use App\Enums\StatusPengguna;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('pemohon-donor')
    ->name('pemohon-donor.')
    ->group(function (): void {
        $ambilPenggunaPemohon = function (): User {
            $pengguna = Auth::user();

            if (! $pengguna instanceof User) {
                abort(401);
            }

            if (
                ! method_exists($pengguna, 'hasRole')
                || ! $pengguna->hasRole('pemohon_donor')
            ) {
                abort(403);
            }

            $statusPengguna = $pengguna->status instanceof \BackedEnum
                ? $pengguna->status->value
                : (string) $pengguna->status;

            if ($statusPengguna !== StatusPengguna::Aktif->value) {
                Auth::guard('web')->logout();

                request()->session()->invalidate();
                request()->session()->regenerateToken();

                redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Akun Pemohon Donor belum aktif atau sedang dibatasi.'
                    )
                    ->send();

                exit;
            }

            return $pengguna;
        };

        $ambilProfilPemohon = function (
            User $pengguna
        ): ProfilRumahSakit {
            $profil = ProfilRumahSakit::query()
                ->where(
                    'pengguna_id',
                    $pengguna->id
                )
                ->first();

            if ($profil === null) {
                redirect()
                    ->route('pemohon-donor.profil.index')
                    ->with(
                        'error',
                        'Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu.'
                    )
                    ->send();

                exit;
            }

            return $profil;
        };

        $ambilPengajuanDariNomor = function (
            ProfilRumahSakit $profil,
            string $nomor
        ): PermintaanDarah {
            $pengajuan = PermintaanDarah::query()
                ->where(
                    'profil_rumah_sakit_id',
                    $profil->id
                )
                ->where(
                    'nomor_permintaan',
                    $nomor
                )
                ->first();

            if ($pengajuan === null) {
                abort(404);
            }

            return $pengajuan;
        };

        $ambilDistribusiDariNomor = function (
            ProfilRumahSakit $profil,
            string $nomor
        ): DistribusiDarah {
            $distribusi = DistribusiDarah::query()
                ->where(
                    'nomor_distribusi',
                    $nomor
                )
                ->whereHas(
                    'permintaan',
                    fn (Builder $query): Builder => $query
                        ->where(
                            'profil_rumah_sakit_id',
                            $profil->id
                        )
                )
                ->first();

            if ($distribusi === null) {
                abort(404);
            }

            return $distribusi;
        };

        Route::get('/riwayat/bukti/{jenis}/{nomor}', function (
            string $jenis,
            string $nomor
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon,
            $ambilPengajuanDariNomor,
            $ambilDistribusiDariNomor
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if ($jenis === 'pengajuan') {
                $pengajuan = $ambilPengajuanDariNomor(
                    profil: $profil,
                    nomor: $nomor
                );

                return redirect()->route(
                    'pemohon-donor.pengajuan.bukti',
                    $pengajuan
                );
            }

            if ($jenis === 'distribusi') {
                $distribusi = $ambilDistribusiDariNomor(
                    profil: $profil,
                    nomor: $nomor
                );

                return redirect()->route(
                    'pemohon-donor.distribusi.bukti',
                    $distribusi
                );
            }

            abort(404);
        })
            ->whereIn('jenis', [
                'pengajuan',
                'distribusi',
            ])
            ->name('riwayat.bukti');

        Route::get('/riwayat/bukti/{jenis}/{nomor}/unduh', function (
            string $jenis,
            string $nomor
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon,
            $ambilPengajuanDariNomor,
            $ambilDistribusiDariNomor
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if ($jenis === 'pengajuan') {
                $pengajuan = $ambilPengajuanDariNomor(
                    profil: $profil,
                    nomor: $nomor
                );

                return redirect()->route(
                    'pemohon-donor.pengajuan.bukti.unduh',
                    $pengajuan
                );
            }

            if ($jenis === 'distribusi') {
                $distribusi = $ambilDistribusiDariNomor(
                    profil: $profil,
                    nomor: $nomor
                );

                return redirect()->route(
                    'pemohon-donor.distribusi.bukti.unduh',
                    $distribusi
                );
            }

            abort(404);
        })
            ->whereIn('jenis', [
                'pengajuan',
                'distribusi',
            ])
            ->name('riwayat.bukti.unduh');
    });