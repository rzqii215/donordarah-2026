<?php

namespace App\Support\Api;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait MemastikanRumahSakitTerautentikasi
{
    protected function penggunaRumahSakit(
        Request $request
    ): User {
        $pengguna = $request->user();

        if (! $pengguna instanceof User) {
            throw new AuthenticationException(
                'Token autentikasi tidak valid atau belum diberikan.'
            );
        }

        if (
            ! $pengguna->hasRole(
                PeranPengguna::PemohonDonor->value
            )
        ) {
            throw new AuthorizationException(
                'Endpoint ini hanya dapat diakses oleh Pemohon Donor.'
            );
        }

        if (
            $pengguna->status !==
            StatusPengguna::Aktif
        ) {
            throw new AuthorizationException(
                'Akun Pemohon Donor belum aktif atau sedang dibatasi.'
            );
        }

        return $pengguna;
    }

    protected function profilRumahSakit(
        Request $request,
        bool $harusTerverifikasi = true
    ): ProfilRumahSakit {
        $pengguna = $this->penggunaRumahSakit(
            $request
        );

        $profil = ProfilRumahSakit::query()
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first();

        if ($profil === null) {
            throw ValidationException::withMessages([
                'profil' =>
                    'Profil Pemohon Donor belum tersedia.',
            ]);
        }

        if (
            $harusTerverifikasi
            && $profil->status_verifikasi !==
                StatusVerifikasiRumahSakit::Disetujui
        ) {
            throw new AuthorizationException(
                'Profil Pemohon Donor belum disetujui.'
            );
        }

        return $profil;
    }
}