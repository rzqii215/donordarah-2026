<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusPermintaanDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Models\PermintaanDarah;
use App\Services\LayananPermintaanDarah;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class PengajuanController extends PortalPemohonController
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

        $pengajuan = collect();

        $totalPengajuan = 0;
        $pengajuanAktif = 0;
        $pengajuanSelesai = 0;
        $pengajuanDibatalkan = 0;

        if ($profil !== null) {
            $queryDasar = PermintaanDarah::query()
                ->where(
                    'profil_rumah_sakit_id',
                    $profil->getKey()
                );

            $totalPengajuan = (clone $queryDasar)
                ->count();

            $pengajuanAktif = (clone $queryDasar)
                ->whereIn(
                    'status',
                    [
                        StatusPermintaanDarah::Diajukan->value,
                        StatusPermintaanDarah::Ditinjau->value,
                        StatusPermintaanDarah::MenungguStok->value,
                        StatusPermintaanDarah::Disetujui->value,
                        StatusPermintaanDarah::SiapDiambil->value,
                    ]
                )
                ->count();

            $pengajuanSelesai = (clone $queryDasar)
                ->where(
                    'status',
                    StatusPermintaanDarah::Selesai->value
                )
                ->count();

            $pengajuanDibatalkan = (clone $queryDasar)
                ->whereIn(
                    'status',
                    [
                        StatusPermintaanDarah::Ditolak->value,
                        StatusPermintaanDarah::Dibatalkan->value,
                    ]
                )
                ->count();

            $pengajuan = (clone $queryDasar)
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
                ->latest('created_at')
                ->limit(20)
                ->get();
        }

        return view(
            'pemohon-donor.pengajuan.index',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'pengajuan' => $pengajuan,
                'q' => $q,
                'statusAktif' => $statusAktif,
                'statusOptions' => StatusPermintaanDarah::cases(),
                'totalPengajuan' => $totalPengajuan,
                'pengajuanAktif' => $pengajuanAktif,
                'pengajuanSelesai' => $pengajuanSelesai,
                'pengajuanDibatalkan' => $pengajuanDibatalkan,
            ]
        );
    }

    public function create(): View
    {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        return view(
            'pemohon-donor.pengajuan.create',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'golonganOptions' => GolonganDarah::cases(),
                'rhesusOptions' => RhesusDarah::cases(),
                'urgensiOptions' => TingkatUrgensiPermintaanDarah::cases(),
            ]
        );
    }

    public function store(
        Request $request,
        LayananPermintaanDarah $layanan
    ): RedirectResponse {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        if ($profil === null) {
            return redirect()
                ->route(
                    'pemohon-donor.profil.index'
                )
                ->with(
                    'error',
                    'Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu.'
                );
        }

        $data = $request->validate([
            'referensi_pasien' => [
                'required',
                'string',
                'max:150',
            ],
            'nama_dokter' => [
                'required',
                'string',
                'max:255',
            ],
            'golongan_darah' => [
                'required',
                Rule::enum(
                    GolonganDarah::class
                ),
            ],
            'rhesus' => [
                'required',
                Rule::enum(
                    RhesusDarah::class
                ),
            ],
            'jumlah_kantong' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'tingkat_urgensi' => [
                'required',
                Rule::enum(
                    TingkatUrgensiPermintaanDarah::class
                ),
            ],
            'dibutuhkan_pada' => [
                'required',
                'date',
                'after_or_equal:now',
            ],
            'dokumen_permintaan' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:4096',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $pathDokumen = null;

        $dokumen = $request->file(
            'dokumen_permintaan'
        );

        if ($dokumen !== null) {
            $pathDokumen = $dokumen->store(
                'dokumen-pengajuan-kebutuhan-donor',
                'public'
            );
        }

        try {
            $layanan->buat(
                rumahSakit: $profil,
                data: [
                    'referensi_pasien' => $data['referensi_pasien'],

                    'nama_dokter' => $data['nama_dokter'],

                    'golongan_darah' => $data['golongan_darah'],

                    'rhesus' => $data['rhesus'],

                    'jumlah_kantong' => (int) $data['jumlah_kantong'],

                    'tingkat_urgensi' => $data['tingkat_urgensi'],

                    'dibutuhkan_pada' => $data['dibutuhkan_pada'],

                    'path_dokumen_permintaan' => $pathDokumen,

                    'catatan' => $data['catatan'] ?? null,
                ]
            );
        } catch (Throwable $throwable) {
            if (filled($pathDokumen)) {
                Storage::disk('public')
                    ->delete(
                        $pathDokumen
                    );
            }

            throw $throwable;
        }

        return redirect()
            ->route(
                'pemohon-donor.pengajuan.index'
            )
            ->with(
                'success',
                'Pengajuan kebutuhan donor berhasil dibuat.'
            );
    }

    public function buktiTerbaru(): RedirectResponse
    {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        if ($profil === null) {
            return redirect()
                ->route(
                    'pemohon-donor.profil.index'
                )
                ->with(
                    'error',
                    'Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu.'
                );
        }

        $pengajuan = PermintaanDarah::query()
            ->where(
                'profil_rumah_sakit_id',
                $profil->getKey()
            )
            ->latest('created_at')
            ->first();

        if ($pengajuan === null) {
            return redirect()
                ->route(
                    'pemohon-donor.pengajuan.index'
                )
                ->with(
                    'error',
                    'Belum ada pengajuan yang bisa diunduh buktinya.'
                );
        }

        return redirect()
            ->route(
                'pemohon-donor.pengajuan.bukti.unduh',
                $pengajuan
            );
    }
}
