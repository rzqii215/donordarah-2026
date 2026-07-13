<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class BerandaController extends PortalPemohonController
{
    public function __invoke(): View
    {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        $pengajuanBaru = 0;
        $diproses = 0;
        $diterima = 0;
        $distribusi = 0;

        $pengajuanTerbaru = collect();
        $riwayatAktivitas = collect();
        $jadwalDistribusi = collect();

        if ($profil !== null) {
            $queryPengajuan = PermintaanDarah::query()
                ->where(
                    'profil_rumah_sakit_id',
                    $profil->getKey()
                );

            $pengajuanBaru = (clone $queryPengajuan)
                ->where(
                    'status',
                    StatusPermintaanDarah::Diajukan->value
                )
                ->count();

            $diproses = (clone $queryPengajuan)
                ->whereIn(
                    'status',
                    [
                        StatusPermintaanDarah::Diajukan->value,
                        StatusPermintaanDarah::Ditinjau->value,
                        StatusPermintaanDarah::MenungguStok->value,
                        StatusPermintaanDarah::Disetujui->value,
                    ]
                )
                ->count();

            $diterima = (clone $queryPengajuan)
                ->where(
                    'status',
                    StatusPermintaanDarah::SiapDiambil->value
                )
                ->count();

            $distribusi = DistribusiDarah::query()
                ->whereHas(
                    'permintaan',
                    function (
                        Builder $query
                    ) use (
                        $profil
                    ): void {
                        $query->where(
                            'profil_rumah_sakit_id',
                            $profil->getKey()
                        );
                    }
                )
                ->count();

            $pengajuanTerbaru = (clone $queryPengajuan)
                ->latest('created_at')
                ->limit(3)
                ->get();

            $riwayatAktivitas = (clone $queryPengajuan)
                ->latest('updated_at')
                ->limit(3)
                ->get();

            $jadwalDistribusi = DistribusiDarah::query()
                ->with('permintaan')
                ->whereHas(
                    'permintaan',
                    function (
                        Builder $query
                    ) use (
                        $profil
                    ): void {
                        $query->where(
                            'profil_rumah_sakit_id',
                            $profil->getKey()
                        );
                    }
                )
                ->orderByDesc(
                    'dijadwalkan_pada'
                )
                ->limit(2)
                ->get();
        }

        return view(
            'pemohon-donor.beranda',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'pengajuanBaru' => $pengajuanBaru,
                'diproses' => $diproses,
                'diterima' => $diterima,
                'distribusi' => $distribusi,
                'pengajuanTerbaru' => $pengajuanTerbaru,
                'riwayatAktivitas' => $riwayatAktivitas,
                'jadwalDistribusi' => $jadwalDistribusi,
            ]
        );
    }
}
