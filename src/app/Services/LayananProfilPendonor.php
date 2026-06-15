<?php

namespace App\Services;

use App\Models\ProfilPendonor;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananProfilPendonor
{
    /**
     * @param array<string, mixed> $data
     */
    public function perbaruiMilikPengguna(
        User $pengguna,
        array $data
    ): ProfilPendonor {
        return DB::transaction(function () use (
            $pengguna,
            $data
        ): ProfilPendonor {
            $akun = User::query()
                ->lockForUpdate()
                ->findOrFail($pengguna->id);

            $profil = ProfilPendonor::query()
                ->where('pengguna_id', $akun->id)
                ->lockForUpdate()
                ->first();

            if ($profil === null) {
                throw ValidationException::withMessages([
                    'profil' =>
                        'Profil Pendonor belum tersedia.',
                ]);
            }

            $dataAkun = Arr::only(
                $data,
                [
                    'name',
                    'nomor_telepon',
                ]
            );

            $dataProfil = Arr::only(
                $data,
                [
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'alamat',
                    'provinsi',
                    'kota',
                    'kecamatan',
                    'kode_pos',
                    'nama_kontak_darurat',
                    'telepon_kontak_darurat',
                    'bersedia_dihubungi',
                ]
            );

            if ($dataAkun !== []) {
                $akun->fill($dataAkun);
                $akun->save();
            }

            if ($dataProfil !== []) {
                $profil->fill($dataProfil);
                $profil->save();
            }

            return $profil
                ->refresh()
                ->load('pengguna');
        });
    }
}