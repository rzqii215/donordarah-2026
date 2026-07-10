<?php

namespace Database\Seeders;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Seeder;

class BuatDistribusiDariPengajuanSiapSeeder extends Seeder
{
    public function run(): void
    {
        $penyiapId = $this->ambilPenyiapId();

        $pengajuanSiap = PermintaanDarah::query()
            ->where(
                'status',
                StatusPermintaanDarah::SiapDiambil->value
            )
            ->whereNotIn(
                'id',
                DistribusiDarah::query()
                    ->whereNotNull('permintaan_darah_id')
                    ->select('permintaan_darah_id')
            )
            ->latest('id')
            ->get();

        if ($pengajuanSiap->isEmpty()) {
            $this->command?->warn(
                'Tidak ada pengajuan status Siap Diambil yang belum memiliki distribusi.'
            );

            return;
        }

        foreach ($pengajuanSiap as $index => $pengajuan) {
            $profil = ProfilRumahSakit::query()
                ->find($pengajuan->profil_rumah_sakit_id);

            $nomorDistribusi = $this->buatNomorDistribusi();

            DistribusiDarah::query()
                ->create([
                    'nomor_distribusi' => $nomorDistribusi,
                    'permintaan_darah_id' => $pengajuan->id,
                    'disiapkan_oleh' => $penyiapId,
                    'dijadwalkan_pada' => now()->addHours($index + 2),
                    'status' => StatusDistribusiDarah::Dijadwalkan->value,
                    'diserahkan_oleh' => null,
                    'nama_penerima' => $profil?->nama_penanggung_jawab
                        ?? 'Penerima Distribusi',
                    'jabatan_penerima' => $profil?->jabatan_penanggung_jawab
                        ?? 'Penanggung Jawab',
                    'nomor_identitas_penerima' => null,
                    'path_bukti_serah_terima' => null,
                    'diserahkan_pada' => null,
                    'dibatalkan_pada' => null,
                    'alasan_pembatalan' => null,
                    'catatan' => 'Distribusi otomatis dibuat dari pengajuan yang sudah berstatus Siap Diambil.',
                ]);

            $this->command?->line(
                'Distribusi dibuat: '
                . $nomorDistribusi
                . ' untuk pengajuan '
                . $pengajuan->nomor_permintaan
            );
        }

        $this->command?->info(
            'Distribusi otomatis berhasil dibuat dari pengajuan Siap Diambil.'
        );
    }

    private function ambilPenyiapId(): int
    {
        $userId = User::query()
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', [
                    'super_admin',
                    'admin',
                    'petugas',
                ]);
            })
            ->value('id');

        if ($userId !== null) {
            return (int) $userId;
        }

        $userId = User::query()
            ->value('id');

        if ($userId === null) {
            throw new \RuntimeException(
                'Tidak ada user di database. Buat minimal satu user terlebih dahulu.'
            );
        }

        return (int) $userId;
    }

    private function buatNomorDistribusi(): string
    {
        $nomorUrut = DistribusiDarah::query()
            ->whereDate(
                'created_at',
                now()->toDateString()
            )
            ->count() + 1;

        do {
            $nomorDistribusi = 'DST-' . now()->format('Ymd') . '-' . str_pad(
                (string) $nomorUrut,
                4,
                '0',
                STR_PAD_LEFT
            );

            $sudahAda = DistribusiDarah::query()
                ->where(
                    'nomor_distribusi',
                    $nomorDistribusi
                )
                ->exists();

            $nomorUrut++;
        } while ($sudahAda);

        return $nomorDistribusi;
    }
}