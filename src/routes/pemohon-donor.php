<?php

use App\Http\Controllers\PemohonDonor\BuktiPemohonDonorController;
use App\Http\Controllers\PemohonDonor\Portal\BantuanController;
use App\Http\Controllers\PemohonDonor\Portal\BerandaController;
use App\Http\Controllers\PemohonDonor\Portal\DistribusiController;
use App\Http\Controllers\PemohonDonor\Portal\PengajuanController;
use App\Http\Controllers\PemohonDonor\Portal\PengaturanController;
use App\Http\Controllers\PemohonDonor\Portal\ProfilController;
use App\Http\Controllers\PemohonDonor\Portal\RiwayatController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
])
    ->prefix('pemohon-donor')
    ->name('pemohon-donor.')
    ->group(function (): void {
        Route::redirect(
            '/',
            '/pemohon-donor/beranda'
        )->name('index');

        Route::get(
            '/beranda',
            BerandaController::class
        )->name('beranda');

        Route::controller(
            PengajuanController::class
        )
            ->prefix('pengajuan')
            ->name('pengajuan.')
            ->group(function (): void {
                Route::get(
                    '/',
                    'index'
                )->name('index');

                Route::get(
                    '/buat',
                    'create'
                )->name('create');

                Route::post(
                    '/',
                    'store'
                )->name('store');

                Route::get(
                    '/bukti/unduh-terbaru',
                    'buktiTerbaru'
                )->name('bukti.terbaru');
            });

        Route::get(
            '/pengajuan/{permintaanDarah}/bukti',
            [
                BuktiPemohonDonorController::class,
                'pengajuan',
            ]
        )->name('pengajuan.bukti');

        Route::get(
            '/pengajuan/{permintaanDarah}/bukti/unduh',
            [
                BuktiPemohonDonorController::class,
                'unduhPengajuan',
            ]
        )->name('pengajuan.bukti.unduh');

        Route::get(
            '/distribusi',
            [
                DistribusiController::class,
                'index',
            ]
        )->name('distribusi.index');

        Route::get(
            '/distribusi/{distribusiDarah}/bukti',
            [
                BuktiPemohonDonorController::class,
                'distribusi',
            ]
        )->name('distribusi.bukti');

        Route::get(
            '/distribusi/{distribusiDarah}/bukti/unduh',
            [
                BuktiPemohonDonorController::class,
                'unduhDistribusi',
            ]
        )->name('distribusi.bukti.unduh');

        Route::controller(
            ProfilController::class
        )
            ->prefix('profil')
            ->name('profil.')
            ->group(function (): void {
                Route::get(
                    '/',
                    'index'
                )->name('index');

                Route::put(
                    '/',
                    'update'
                )->name('update');
            });

        Route::get(
            '/riwayat',
            RiwayatController::class
        )->name('riwayat.index');

        Route::get(
            '/bantuan',
            BantuanController::class
        )->name('bantuan.index');

        Route::controller(
            PengaturanController::class
        )
            ->prefix('pengaturan')
            ->name('pengaturan.')
            ->group(function (): void {
                Route::get(
                    '/',
                    'index'
                )->name('index');

                Route::put(
                    '/akun',
                    'updateAkun'
                )->name('akun.update');

                Route::put(
                    '/password',
                    'updatePassword'
                )->name('password.update');
            });
    });
