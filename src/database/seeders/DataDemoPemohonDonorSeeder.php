<?php

namespace Database\Seeders;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPengguna;
use App\Enums\StatusPermintaanDarah;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DataDemoPemohonDonorSeeder extends Seeder
{
    public function run(): void
    {
        $this->pastikanRolePemohonDonorTersedia();

        $penggunaDemo = $this->buatAkunPemohonDemo();

        $profilDemo = $this->buatProfilPemohonDemo(
            pengguna: $penggunaDemo
        );

        $profilPemohon = ProfilRumahSakit::query()
            ->whereNotNull('pengguna_id')
            ->get()
            ->push($profilDemo)
            ->unique('id')
            ->values();

        $penyiapId = $this->ambilPenyiapId(
            fallbackUserId: $penggunaDemo->id
        );

        foreach ($profilPemohon as $profil) {
            $this->buatPaketDataDemoUntukProfil(
                profil: $profil,
                penyiapId: $penyiapId
            );
        }

        $this->command?->info('Seeder data demo Pemohon Donor berhasil dijalankan.');
        $this->command?->line('Akun demo: pemohon@demo.com');
        $this->command?->line('Password : password');
    }

    private function pastikanRolePemohonDonorTersedia(): void
    {
        if (! class_exists(Role::class)) {
            return;
        }

        Role::query()->firstOrCreate([
            'name' => 'pemohon_donor',
            'guard_name' => 'web',
        ]);
    }

    private function buatAkunPemohonDemo(): User
    {
        $pengguna = User::query()
            ->firstOrNew([
                'email' => 'pemohon@demo.com',
            ]);

        $dataPengguna = [
            'name' => 'Pemohon Donor Demo',
            'email' => 'pemohon@demo.com',
            'password' => Hash::make('password'),
        ];

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $dataPengguna['email_verified_at'] = now();
        }

        if (Schema::hasColumn('users', 'status')) {
            $dataPengguna['status'] = StatusPengguna::Aktif->value;
        }

        if (Schema::hasColumn('users', 'nomor_telepon')) {
            $dataPengguna['nomor_telepon'] = '081234567890';
        }

        $pengguna->forceFill($dataPengguna);
        $pengguna->save();

        if (
            method_exists($pengguna, 'assignRole')
            && method_exists($pengguna, 'hasRole')
            && ! $pengguna->hasRole('pemohon_donor')
        ) {
            $pengguna->assignRole('pemohon_donor');
        }

        return $pengguna;
    }

    private function buatProfilPemohonDemo(
        User $pengguna
    ): ProfilRumahSakit {
        return ProfilRumahSakit::query()
            ->updateOrCreate(
                [
                    'pengguna_id' => $pengguna->id,
                ],
                [
                    'kode_rumah_sakit' => 'PMH-DEMO-000001',
                    'nama_rumah_sakit' => 'Yayasan Harapan Sehat Demo',
                    'nomor_izin' => 'IZIN-DEMO-001',
                    'path_dokumen_izin' => null,
                    'nama_penanggung_jawab' => 'Rizqi Pemohon Demo',
                    'jabatan_penanggung_jawab' => 'Koordinator Donor',
                    'alamat' => 'Jl. Sehat Bersama No. 10, Jakarta',
                    'provinsi' => 'DKI Jakarta',
                    'kota' => 'Jakarta Pusat',
                    'kecamatan' => 'Gambir',
                    'kode_pos' => '10110',
                    'latitude' => -6.175392,
                    'longitude' => 106.827153,
                    'status_verifikasi' => StatusVerifikasiRumahSakit::Disetujui->value,
                    'diverifikasi_oleh' => $this->ambilPenyiapId(
                        fallbackUserId: $pengguna->id
                    ),
                    'diverifikasi_pada' => now(),
                    'alasan_penolakan' => null,
                ]
            );
    }

    private function ambilPenyiapId(
        int $fallbackUserId
    ): int {
        $adminId = User::query()
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', [
                    'super_admin',
                    'admin',
                    'petugas',
                ]);
            })
            ->value('id');

        return $adminId ?? $fallbackUserId;
    }

    private function buatPaketDataDemoUntukProfil(
        ProfilRumahSakit $profil,
        int $penyiapId
    ): void {
        $pengajuanDiajukan = $this->buatPengajuanDemo(
            profil: $profil,
            nomor: 'REQ-DEMO-' . str_pad((string) $profil->id, 4, '0', STR_PAD_LEFT) . '-001',
            referensi: 'DEMO-PENGAJUAN-BARU',
            status: StatusPermintaanDarah::Diajukan->value,
            golonganDarah: GolonganDarah::A->value,
            rhesus: RhesusDarah::Positif->value,
            jumlahKantong: 2,
            urgensi: TingkatUrgensiPermintaanDarah::Normal->value,
            dibutuhkanPada: now()->addDay()
        );

        $pengajuanSiapDiambil = $this->buatPengajuanDemo(
            profil: $profil,
            nomor: 'REQ-DEMO-' . str_pad((string) $profil->id, 4, '0', STR_PAD_LEFT) . '-002',
            referensi: 'DEMO-SIAP-DISTRIBUSI',
            status: StatusPermintaanDarah::SiapDiambil->value,
            golonganDarah: GolonganDarah::O->value,
            rhesus: RhesusDarah::Positif->value,
            jumlahKantong: 3,
            urgensi: TingkatUrgensiPermintaanDarah::Mendesak->value,
            dibutuhkanPada: now()->addHours(6)
        );

        $pengajuanSelesai = $this->buatPengajuanDemo(
            profil: $profil,
            nomor: 'REQ-DEMO-' . str_pad((string) $profil->id, 4, '0', STR_PAD_LEFT) . '-003',
            referensi: 'DEMO-SELESAI',
            status: StatusPermintaanDarah::Selesai->value,
            golonganDarah: GolonganDarah::B->value,
            rhesus: RhesusDarah::Negatif->value,
            jumlahKantong: 1,
            urgensi: TingkatUrgensiPermintaanDarah::Darurat->value,
            dibutuhkanPada: now()->subHours(2)
        );

        $this->buatDistribusiDemo(
            pengajuan: $pengajuanSiapDiambil,
            nomor: 'DST-DEMO-' . str_pad((string) $profil->id, 4, '0', STR_PAD_LEFT) . '-001',
            penyiapId: $penyiapId,
            status: StatusDistribusiDarah::Dijadwalkan->value,
            dijadwalkanPada: now()->addHours(2),
            diserahkanPada: null
        );

        $this->buatDistribusiDemo(
            pengajuan: $pengajuanSelesai,
            nomor: 'DST-DEMO-' . str_pad((string) $profil->id, 4, '0', STR_PAD_LEFT) . '-002',
            penyiapId: $penyiapId,
            status: StatusDistribusiDarah::Selesai->value,
            dijadwalkanPada: now()->subHours(3),
            diserahkanPada: now()->subHour()
        );

        $this->command?->line(
            'Data demo dibuat untuk profil: '
            . ($profil->nama_rumah_sakit ?? 'Profil #' . $profil->id)
        );
    }

    private function buatPengajuanDemo(
        ProfilRumahSakit $profil,
        string $nomor,
        string $referensi,
        string $status,
        string $golonganDarah,
        string $rhesus,
        int $jumlahKantong,
        string $urgensi,
        mixed $dibutuhkanPada
    ): PermintaanDarah {
        return PermintaanDarah::query()
            ->updateOrCreate(
                [
                    'nomor_permintaan' => $nomor,
                ],
                [
                    'profil_rumah_sakit_id' => $profil->id,
                    'referensi_pasien' => $referensi,
                    'nama_dokter' => $profil->nama_penanggung_jawab
                        ?? 'Penanggung Jawab Demo',
                    'golongan_darah' => $golonganDarah,
                    'rhesus' => $rhesus,
                    'jumlah_kantong' => $jumlahKantong,
                    'tingkat_urgensi' => $urgensi,
                    'dibutuhkan_pada' => $dibutuhkanPada,
                    'status' => $status,
                    'path_dokumen_permintaan' => null,
                    'catatan' => 'Data pengajuan demo untuk pengujian portal Pemohon Donor.',
                ]
            );
    }

    private function buatDistribusiDemo(
        PermintaanDarah $pengajuan,
        string $nomor,
        int $penyiapId,
        string $status,
        mixed $dijadwalkanPada,
        mixed $diserahkanPada
    ): DistribusiDarah {
        $profil = ProfilRumahSakit::query()
            ->find($pengajuan->profil_rumah_sakit_id);

        return DistribusiDarah::query()
            ->updateOrCreate(
                [
                    'nomor_distribusi' => $nomor,
                ],
                [
                    'permintaan_darah_id' => $pengajuan->id,
                    'disiapkan_oleh' => $penyiapId,
                    'dijadwalkan_pada' => $dijadwalkanPada,
                    'status' => $status,
                    'diserahkan_oleh' => $diserahkanPada !== null
                        ? $penyiapId
                        : null,
                    'nama_penerima' => $profil?->nama_penanggung_jawab
                        ?? 'Penerima Demo',
                    'jabatan_penerima' => $profil?->jabatan_penanggung_jawab
                        ?? 'Penanggung Jawab',
                    'nomor_identitas_penerima' => 'DEMO-' . str_pad((string) $pengajuan->id, 6, '0', STR_PAD_LEFT),
                    'path_bukti_serah_terima' => null,
                    'diserahkan_pada' => $diserahkanPada,
                    'dibatalkan_pada' => null,
                    'alasan_pembatalan' => null,
                    'catatan' => 'Data distribusi demo untuk pengujian bukti distribusi.',
                ]
            );
    }
}