<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use UnitEnum;

class RiwayatController extends PortalPemohonController
{
    public function __invoke(
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

        $jenisAktif = trim(
            (string) $request->query(
                'jenis',
                ''
            )
        );

        $riwayat = collect();

        $totalRiwayat = 0;
        $totalPengajuan = 0;
        $totalDistribusi = 0;

        if ($profil !== null) {
            $riwayatPengajuan =
                PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->getKey()
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
                                            'nomor_permintaan',
                                            'like',
                                            "%{$q}%"
                                        )
                                        ->orWhere(
                                            'referensi_pasien',
                                            'like',
                                            "%{$q}%"
                                        )
                                        ->orWhere(
                                            'nama_dokter',
                                            'like',
                                            "%{$q}%"
                                        );
                                }
                            );
                        }
                    )
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(
                        function (
                            PermintaanDarah $pengajuan
                        ): array {
                            return [
                                'jenis' => 'pengajuan',

                                'judul' => 'Pengajuan Kebutuhan Donor',

                                'nomor' => $pengajuan
                                    ->nomor_permintaan,

                                'keterangan' => $pengajuan
                                    ->referensi_pasien,

                                'status' => $pengajuan->status,

                                'waktu' => $pengajuan
                                    ->updated_at,

                                'deskripsi' => 'Pengajuan diperbarui dengan status '
                                    . $this->labelStatus(
                                        $pengajuan->status
                                    )
                                    . '.',
                            ];
                        }
                    );

            $riwayatDistribusi =
                DistribusiDarah::query()
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
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(
                        function (
                            DistribusiDarah $distribusi
                        ): array {
                            return [
                                'jenis' => 'distribusi',

                                'judul' => 'Distribusi Kantong Darah',

                                'nomor' => $distribusi
                                    ->nomor_distribusi,

                                'keterangan' => $distribusi
                                    ->permintaan
                                    ?->nomor_permintaan
                                    ?? '-',

                                'status' => $distribusi->status,

                                'waktu' => $distribusi
                                    ->updated_at,

                                'deskripsi' => 'Distribusi diperbarui dengan status '
                                    . $this->labelStatus(
                                        $distribusi->status
                                    )
                                    . '.',
                            ];
                        }
                    );

            $totalPengajuan =
                $riwayatPengajuan->count();

            $totalDistribusi =
                $riwayatDistribusi->count();

            $riwayat = $riwayatPengajuan
                ->merge(
                    $riwayatDistribusi
                )
                ->when(
                    $jenisAktif === 'pengajuan',
                    fn ($koleksi) => $koleksi
                        ->where(
                            'jenis',
                            'pengajuan'
                        )
                        ->values()
                )
                ->when(
                    $jenisAktif === 'distribusi',
                    fn ($koleksi) => $koleksi
                        ->where(
                            'jenis',
                            'distribusi'
                        )
                        ->values()
                )
                ->sortByDesc('waktu')
                ->values()
                ->take(50);

            $totalRiwayat =
                $riwayat->count();
        }

        return view(
            'pemohon-donor.riwayat.index',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'riwayat' => $riwayat,
                'q' => $q,
                'jenisAktif' => $jenisAktif,
                'totalRiwayat' => $totalRiwayat,
                'totalPengajuan' => $totalPengajuan,
                'totalDistribusi' => $totalDistribusi,
            ]
        );
    }

    private function labelStatus(
        mixed $status
    ): string {
        if (
            is_object($status)
            && method_exists(
                $status,
                'label'
            )
        ) {
            return (string) $status->label();
        }

        if ($status instanceof BackedEnum) {
            $nilai = (string) $status->value;
        } elseif ($status instanceof UnitEnum) {
            $nilai = (string) $status->name;
        } else {
            $nilai = (string) $status;
        }

        return Str::headline(
            str_replace(
                [
                    '_',
                    '-',
                ],
                ' ',
                $nilai
            )
        );
    }
}
