<?php

namespace App\Listeners;

use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class CatatLoginPenggunaTerakhir
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        /*
         * Login dengan kredensial benar tetap dapat
         * memicu event sebelum middleware menolak akun.
         * Karena itu hanya akun aktif yang dicatat.
         */
        if (! $this->statusPenggunaAktif($user)) {
            return;
        }

        $ipAddress = app()->bound('request')
            ? request()->ip()
            : null;

        $user->forceFill([
            'terakhir_login_pada' => now(),
            'ip_terakhir_login' => $ipAddress,
        ])->saveQuietly();
    }

    private function statusPenggunaAktif(
        User $user
    ): bool {
        return $this->nilaiStatusPengguna($user)
            === StatusPengguna::Aktif->value;
    }

    private function nilaiStatusPengguna(
        User $user
    ): string {
        $status = $user->status;

        if ($status instanceof \BackedEnum) {
            return strtolower(
                trim(
                    (string) $status->value
                )
            );
        }

        return strtolower(
            trim(
                (string) $status
            )
        );
    }
}