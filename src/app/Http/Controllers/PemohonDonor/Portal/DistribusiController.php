<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Enums\StatusDistribusiDarah;
use App\Models\DistribusiDarah;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DistribusiController extends PortalPemohonController
{
    public function index(
        Request $request
    ): View {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        $q = trim(
            (string) $request->query(
                'q',
                ''
            )
        );

        $statusAktif = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $distribusi = collect();

        $totalDistribusi = 0;
        $terjadwal = 0;
        $siapDiserahkan = 0;
        $selesai = 0;
        $dibatalkan = 0;

        if ($profil !== null) {
            $queryDasar = DistribusiDarah::query()
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
                );

            $totalDistribusi = (clone $queryDasar)
                ->count();

            $terjadwal = (clone $queryDasar)
                ->where(
                    'status',
                    StatusDistribusiDarah::Dijadwalkan->value
                )
                ->count();

            $siapDiserahkan = (clone $queryDasar)
                ->where(
                    'status',
                    StatusDistribusiDarah::SiapDiserahkan->value
                )
                ->count();

            $selesai = (clone $queryDasar)
                ->where(
                    'status',
                    StatusDistribusiDarah::Selesai->value
                )
                ->count();

            $dibatalkan = (clone $queryDasar)
                ->where(
                    'status',
                    StatusDistribusiDarah::Dibatalkan->value
                )
                ->count();

            $distribusi = DistribusiDarah::query()
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
                ->when(
                    filled($q),
                    function (
                        Builder $query
                    ) use (
                        $q
                    ): void {
                        $query->where(
                            function (
                                Builder $subQuery
                            ) use (
                                $q
                            ): void {
                                $subQuery
                                    ->where(
                                        'nomor_distribusi',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhereHas(
                                        'permintaan',
                                        function (
                                            Builder $permintaanQuery
                                        ) use (
                                            $q
                                        ): void {
                                            $permintaanQuery
                                                ->where(
                                                    'nomor_permintaan',
                                                    'like',
                                                    "%{$q}%"
                                                )
                                                ->orWhere(
                                                    'referensi_pasien',
                                                    'like',
                                                    "%{$q}%"
                                                );
                                        }
                                    );
                            }
                        );
                    }
                )
                ->when(
                    filled($statusAktif),
                    function (
                        Builder $query
                    ) use (
                        $statusAktif
                    ): void {
                        $query->where(
                            'status',
                            $statusAktif
                        );
                    }
                )
                ->orderByDesc(
                    'dijadwalkan_pada'
                )
                ->limit(20)
                ->get();
        }

        return view(
            'pemohon-donor.distribusi.index',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'distribusi' => $distribusi,
                'q' => $q,
                'statusAktif' => $statusAktif,
                'statusOptions' => StatusDistribusiDarah::cases(),
                'totalDistribusi' => $totalDistribusi,
                'terjadwal' => $terjadwal,
                'siapDiserahkan' => $siapDiserahkan,
                'selesai' => $selesai,
                'dibatalkan' => $dibatalkan,
            ]
        );
    }
}
