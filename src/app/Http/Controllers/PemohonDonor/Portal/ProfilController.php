<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Enums\StatusVerifikasiRumahSakit;
use App\Models\ProfilRumahSakit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProfilController extends PortalPemohonController
{
    public function index(): View
    {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        return view(
            'pemohon-donor.profil.index',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
            ]
        );
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'nomor_telepon' => [
                'nullable',
                'string',
                'max:30',
            ],
            'nama_rumah_sakit' => [
                'required',
                'string',
                'max:255',
            ],
            'nomor_izin' => [
                'nullable',
                'string',
                'max:255',
            ],
            'dokumen_izin' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:4096',
            ],
            'nama_penanggung_jawab' => [
                'required',
                'string',
                'max:255',
            ],
            'jabatan_penanggung_jawab' => [
                'nullable',
                'string',
                'max:255',
            ],
            'alamat' => [
                'required',
                'string',
                'max:500',
            ],
            'provinsi' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kota' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kecamatan' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kode_pos' => [
                'nullable',
                'string',
                'max:20',
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $pathDokumenLama =
            $profil?->path_dokumen_izin;

        $pathDokumenBaru = null;

        $dokumenIzin = $request->file(
            'dokumen_izin'
        );

        if ($dokumenIzin !== null) {
            $hasilPenyimpanan = $dokumenIzin->store(
                'dokumen-izin-pemohon-donor',
                'public'
            );

            if (! is_string($hasilPenyimpanan)) {
                throw new RuntimeException(
                    'Dokumen izin gagal disimpan.'
                );
            }

            $pathDokumenBaru =
                $hasilPenyimpanan;
        }

        $statusVerifikasi =
            $profil?->status_verifikasi
            ?? StatusVerifikasiRumahSakit::Menunggu;

        $mengajukanVerifikasiUlang =
            $statusVerifikasi
            === StatusVerifikasiRumahSakit::Ditolak;

        if ($mengajukanVerifikasiUlang) {
            $statusVerifikasi =
                StatusVerifikasiRumahSakit::Menunggu;
        }

        try {
            DB::transaction(
                function () use (
                    $pengguna,
                    $profil,
                    $data,
                    $pathDokumenLama,
                    $pathDokumenBaru,
                    $statusVerifikasi,
                    $mengajukanVerifikasiUlang
                ): void {
                    $pengguna
                        ->forceFill([
                            'name' => $data['name'],

                            'nomor_telepon' => $data[
                                    'nomor_telepon'
                                ] ?? null,
                        ])
                        ->save();

                    ProfilRumahSakit::query()
                        ->updateOrCreate(
                            [
                                'pengguna_id' => $pengguna->getKey(),
                            ],
                            [
                                'kode_rumah_sakit' => $profil
                                    ?->kode_rumah_sakit
                                    ?? $this
                                        ->buatKodePemohon(),

                                'nama_rumah_sakit' => $data[
                                        'nama_rumah_sakit'
                                    ],

                                'nomor_izin' => $data[
                                        'nomor_izin'
                                    ] ?? null,

                                'path_dokumen_izin' => $pathDokumenBaru
                                    ?? $pathDokumenLama,

                                'nama_penanggung_jawab' => $data[
                                        'nama_penanggung_jawab'
                                    ],

                                'jabatan_penanggung_jawab' => $data[
                                        'jabatan_penanggung_jawab'
                                    ] ?? null,

                                'alamat' => $data['alamat'],

                                'provinsi' => $data[
                                        'provinsi'
                                    ] ?? null,

                                'kota' => $data[
                                        'kota'
                                    ] ?? null,

                                'kecamatan' => $data[
                                        'kecamatan'
                                    ] ?? null,

                                'kode_pos' => $data[
                                        'kode_pos'
                                    ] ?? null,

                                'latitude' => $data[
                                        'latitude'
                                    ] ?? null,

                                'longitude' => $data[
                                        'longitude'
                                    ] ?? null,

                                'status_verifikasi' => $statusVerifikasi,

                                'diverifikasi_oleh' => $mengajukanVerifikasiUlang
                                        ? null
                                        : $profil
                                            ?->diverifikasi_oleh,

                                'diverifikasi_pada' => $mengajukanVerifikasiUlang
                                        ? null
                                        : $profil
                                            ?->diverifikasi_pada,

                                'alasan_penolakan' => $statusVerifikasi
                                    === StatusVerifikasiRumahSakit::Menunggu
                                        ? null
                                        : $profil
                                            ?->alasan_penolakan,
                            ]
                        );
                }
            );
        } catch (Throwable $throwable) {
            if (filled($pathDokumenBaru)) {
                Storage::disk('public')
                    ->delete(
                        $pathDokumenBaru
                    );
            }

            throw $throwable;
        }

        if (
            filled($pathDokumenBaru)
            && filled($pathDokumenLama)
            && $pathDokumenBaru
                !== $pathDokumenLama
        ) {
            Storage::disk('public')
                ->delete(
                    $pathDokumenLama
                );
        }

        return redirect()
            ->route(
                'pemohon-donor.profil.index'
            )
            ->with(
                'success',
                'Profil Pemohon Donor berhasil diperbarui.'
            );
    }

    private function buatKodePemohon(): string
    {
        $nomorUrut =
            ProfilRumahSakit::query()
                ->count() + 1;

        do {
            $kode = sprintf(
                'PMH-%s-%s',
                now()->format('Y'),
                str_pad(
                    (string) $nomorUrut,
                    6,
                    '0',
                    STR_PAD_LEFT
                )
            );

            $sudahAda =
                ProfilRumahSakit::query()
                    ->where(
                        'kode_rumah_sakit',
                        $kode
                    )
                    ->exists();

            $nomorUrut++;
        } while ($sudahAda);

        return $kode;
    }
}
