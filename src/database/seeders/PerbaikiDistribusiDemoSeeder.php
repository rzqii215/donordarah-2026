<?php

namespace Database\Seeders;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Seeder;

class PerbaikiDistribusiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $penyiapId = $this->ambilPenyiapId();

        $statusPengajuanSiapDistribusi = [
            StatusPermintaanDarah::SiapDiambil->value,
            StatusPermintaanDarah::Disetujui->value,
            'siap_diambil',
            'ready_for_pickup',
            'disetujui',
            'approved',
        ];

        $pengajuanBelumPunyaDistribusi = PermintaanDarah::query()
            ->whereIn(
                'status',
                $statusPengajuanSiapDistribusi
            )
            ->whereNotIn(
                'id',
                DistribusiDarah::query()
                    ->whereNotNull('permintaan_darah_id')
                    ->select('permintaan_darah_id')
            )
            ->latest('id')
            ->get();

        if ($pengajuanBelumPunyaDistribusi->isEmpty()) {
            $this->command?->warn(
                'Tidak ada pengajuan siap distribusi yang belum memiliki data distribusi.'
            );

            $this->command?->line(
                'Cek halaman Pengajuan. Pastikan ada minimal satu pengajuan dengan status Siap Diambil atau Disetujui.'
            );

            return;
        }

        foreach ($pengajuanBelumPunyaDistribusi as $index => $pengajuan) {
            $profil = ProfilRumahSakit::query()
                ->find($pengajuan->profil_rumah_sakit_id);

            $nomorDistribusi = $this->buatNomorDistribusi();

            $distribusi = DistribusiDarah::query()
                ->create([
                    'nomor_distribusi' => $nomorDistribusi,
                    'permintaan_darah_id' => $pengajuan->id,
                    'disiapkan_oleh' => $penyiapId,
                    'dijadwalkan_pada' => now()->addHours($index + 2),
                    'status' => StatusDistribusiDarah::Dijadwalkan->value,
                    'diserahkan_oleh' => null,
                    'nama_penerima' => $profil?->nama_penanggung_jawab
                        ?? 'Penerima Distribusi Demo',
                    'jabatan_penerima' => $profil?->jabatan_penanggung_jawab
                        ?? 'Penanggung Jawab',
                    'nomor_identitas_penerima' => null,
                    'path_bukti_serah_terima' => null,
                    'diserahkan_pada' => null,
                    'dibatalkan_pada' => null,
                    'alasan_pembatalan' => null,
                    'catatan' => 'Distribusi demo dibuat otomatis dari pengajuan yang sudah siap diambil.',
                ]);

            $this->command?->line(
                'Distribusi dibuat: '
                . $distribusi->nomor_distribusi
                . ' untuk pengajuan '
                . $pengajuan->nomor_permintaan
            );
        }

        $this->command?->info(
            'Data distribusi demo berhasil dibuat.'
        );
    }

    private function ambilPenyiapId(): int
    {
        $adminId = User::query()
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', [
                    'super_admin',
                    'admin',
                    'petugas',
                ]);
            })
            ->value('id');

        if ($adminId !== null) {
            return (int) $adminId;
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