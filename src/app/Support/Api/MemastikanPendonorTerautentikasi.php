<?php

namespace App\Support\Api;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

trait MemastikanPendonorTerautentikasi
{
    protected function penggunaPendonor(
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
                PeranPengguna::Pendonor->value
            )
        ) {
            throw new AuthorizationException(
                'Endpoint ini hanya dapat diakses oleh Pendonor.'
            );
        }

        $status = $pengguna->status;

        if (
            $status instanceof StatusPengguna
                ? $status !== StatusPengguna::Aktif
                : (string) $status !== StatusPengguna::Aktif->value
        ) {
            throw new AuthorizationException(
                'Akun Pendonor belum aktif atau sedang dibatasi.'
            );
        }

        return $pengguna;
    }
}