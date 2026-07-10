<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Handlers;

use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Requests\CreatePermintaanDarahRequest;
use App\Filament\Admin\Resources\PermintaanDarahResource\Api\Transformers\PermintaanDarahTransformer;
use App\Services\LayananPermintaanDarah;
use App\Support\Api\MemastikanRumahSakitTerautentikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Rupadana\ApiService\Http\Handlers;
use Throwable;

class CreateHandler extends Handlers
{
    use MemastikanRumahSakitTerautentikasi;

    public static string|null $uri = '/';

    public static string|null $resource =
        PermintaanDarahResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    /**
     * Membuat pengajuan kebutuhan donor untuk Pemohon Donor yang sedang login.
     */
    public function handler(
        CreatePermintaanDarahRequest $request
    ): JsonResponse {
        $profilPemohonDonor = $this->profilRumahSakit(
            request: $request,
            harusTerverifikasi: true,
        );

        $data = $request->validated();

        $pathDokumen = null;

        if ($request->hasFile('dokumen_permintaan')) {
            $pathDokumen = $request
                ->file('dokumen_permintaan')
                ?->store(
                    'dokumen-pengajuan-kebutuhan-donor',
                    'public'
                );
        }

        try {
            $record = app(
                LayananPermintaanDarah::class
            )->buat(
                rumahSakit: $profilPemohonDonor,
                data: [
                    'referensi_pasien' =>
                        $data['referensi_pasien'],

                    'nama_dokter' =>
                        $data['nama_dokter'],

                    'golongan_darah' =>
                        $data['golongan_darah'],

                    'rhesus' =>
                        $data['rhesus'],

                    'jumlah_kantong' =>
                        (int) $data['jumlah_kantong'],

                    'tingkat_urgensi' =>
                        $data['tingkat_urgensi'],

                    'dibutuhkan_pada' =>
                        $data['dibutuhkan_pada'],

                    'path_dokumen_permintaan' =>
                        $pathDokumen,

                    'catatan' =>
                        $data['catatan'] ?? null,
                ],
            );
        } catch (Throwable $throwable) {
            if (filled($pathDokumen)) {
                Storage::disk('public')->delete(
                    $pathDokumen
                );
            }

            throw $throwable;
        }

        $record->load([
            'rumahSakit',
            'peninjau',
        ]);

        return (
            new PermintaanDarahTransformer(
                $record
            )
        )
            ->response()
            ->setStatusCode(201);
    }
}