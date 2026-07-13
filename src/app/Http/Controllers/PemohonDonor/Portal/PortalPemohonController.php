<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Enums\StatusPengguna;
use App\Http\Controllers\Controller;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

abstract class PortalPemohonController extends Controller
{
    protected function penggunaPemohon(): User
    {
        $pengguna = Auth::guard('web')->user();

        abort_unless(
            $pengguna instanceof User,
            401
        );

        if (
            $this->nilaiStatusPengguna($pengguna)
            !== StatusPengguna::Aktif->value
        ) {
            $this->keluarkanPengguna(
                'Akun Pemohon Donor belum aktif atau sedang dibatasi.'
            );
        }

        return $pengguna;
    }

    protected function profilPemohon(
        User $pengguna
    ): ?ProfilRumahSakit {
        return ProfilRumahSakit::query()
            ->where(
                'pengguna_id',
                $pengguna->getKey()
            )
            ->first();
    }

    protected function nilaiStatusPengguna(
        User $pengguna
    ): string {
        $status = $pengguna->status;

        if ($status instanceof BackedEnum) {
            return strtolower(
                trim(
                    (string) $status->value
                )
            );
        }

        if ($status instanceof UnitEnum) {
            return strtolower(
                trim(
                    (string) $status->name
                )
            );
        }

        return strtolower(
            trim(
                (string) $status
            )
        );
    }

    protected function keluarkanPengguna(
        string $pesan
    ): never {
        Auth::guard('web')->logout();

        request()
            ->session()
            ->invalidate();

        request()
            ->session()
            ->regenerateToken();

        throw new HttpResponseException(
            redirect()
                ->route('login')
                ->with(
                    'error',
                    $pesan
                )
        );
    }
}
