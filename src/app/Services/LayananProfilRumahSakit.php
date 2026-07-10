<?php

namespace App\Services;

use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananProfilRumahSakit
{
    /**
     * Memperbarui profil Pemohon Donor milik pengguna yang sedang login.
     *
     * Catatan:
     * Nama class, model, relasi, dan tabel masih memakai struktur teknis lama
     * agar database dan kode yang sudah berjalan tetap aman.
     *
     * @param array<string, mixed> $data
     */
    public function perbaruiMilikPengguna(
        User $pengguna,
        array $data
    ): ProfilRumahSakit {
        return DB::transaction(function () use (
            $pengguna,
            $data
        ): ProfilRumahSakit {
            $akun = User::query()
                ->lockForUpdate()
                ->findOrFail($pengguna->id);

            $profil = ProfilRumahSakit::query()
                ->where(
                    'pengguna_id',
                    $akun->id
                )
                ->lockForUpdate()
                ->first();

            if ($profil === null) {
                throw ValidationException::withMessages([
                    'profil' =>
                        'Profil Pemohon Donor belum tersedia.',
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
                    'nama_penanggung_jawab',
                    'jabatan_penanggung_jawab',
                    'alamat',
                    'provinsi',
                    'kota',
                    'kecamatan',
                    'kode_pos',
                    'latitude',
                    'longitude',
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
                ->load([
                    'pengguna',
                    'verifikator',
                ]);
        });
    }
}