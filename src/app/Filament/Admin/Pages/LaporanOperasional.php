<?php

namespace App\Filament\Admin\Pages;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPendaftaranDonor;
use App\Enums\StatusPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\KantongDarah;
use App\Models\PendaftaranDonor;
use App\Models\PermintaanDarah;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Actions;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanOperasional extends Page
{
    protected static ?string $navigationIcon =
        'heroicon-o-chart-bar';

    protected static ?string $navigationLabel =
        'Laporan Operasional';

    protected static ?string $title =
        'Laporan Operasional';

    protected static ?string $navigationGroup =
        'Laporan';

    protected static ?int $navigationSort = 1;

    protected static string $view =
        'filament.admin.pages.laporan-operasional';

    public ?string $tanggalMulai = null;

    public ?string $tanggalSelesai = null;

    public function mount(): void
    {
        $this->resetFilter();
    }

    public function resetFilter(): void
    {
        $this->tanggalMulai = now()
            ->startOfMonth()
            ->toDateString();

        $this->tanggalSelesai = now()
            ->toDateString();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function ringkasan(): array
    {
        [$mulai, $selesai] = $this->rentangTanggal();

        return [
            [
                'label' => 'Pendaftaran Donor',
                'nilai' => PendaftaranDonor::query()
                    ->whereBetween(
                        'created_at',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Pendaftaran pada periode terpilih',
            ],
            [
                'label' => 'Donor Selesai',
                'nilai' => PendaftaranDonor::query()
                    ->where(
                        'status',
                        StatusPendaftaranDonor
                            ::Selesai
                            ->value
                    )
                    ->whereBetween(
                        'selesai_pada',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Pendonor yang selesai donor',
            ],
            [
                'label' => 'Kantong Dibuat',
                'nilai' => KantongDarah::query()
                    ->whereBetween(
                        'created_at',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Kantong baru pada periode terpilih',
            ],
            [
                'label' => 'Pengajuan Masuk',
                'nilai' => PermintaanDarah::query()
                    ->whereBetween(
                        'created_at',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Pengajuan dari Pemohon Donor',
            ],
            [
                'label' => 'Pengajuan Selesai',
                'nilai' => PermintaanDarah::query()
                    ->where(
                        'status',
                        StatusPermintaanDarah
                            ::Selesai
                            ->value
                    )
                    ->whereBetween(
                        'selesai_pada',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Pengajuan yang telah dipenuhi',
            ],
            [
                'label' => 'Distribusi Selesai',
                'nilai' => DistribusiDarah::query()
                    ->where(
                        'status',
                        StatusDistribusiDarah
                            ::Selesai
                            ->value
                    )
                    ->whereBetween(
                        'diserahkan_pada',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
                'keterangan' => 'Distribusi yang telah diserahkan',
            ],
        ];
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function stokDarah(): array
    {
        $data = [];

        foreach (GolonganDarah::cases() as $golonganDarah) {
            foreach (RhesusDarah::cases() as $rhesus) {
                $queryDasar = KantongDarah::query()
                    ->where(
                        'golongan_darah',
                        $golonganDarah->value
                    )
                    ->where(
                        'rhesus',
                        $rhesus->value
                    );

                $tersedia = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusKantongDarah
                            ::Tersedia
                            ->value
                    )
                    ->where(
                        'status_mutu',
                        StatusMutuKantongDarah
                            ::Lulus
                            ->value
                    )
                    ->where(
                        'kedaluwarsa_pada',
                        '>',
                        now()
                    )
                    ->count();

                $dialokasikan = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusKantongDarah
                            ::Dipesan
                            ->value
                    )
                    ->count();

                $mendekatiKedaluwarsa =
                    (clone $queryDasar)
                        ->where(
                            'status',
                            StatusKantongDarah
                                ::Tersedia
                                ->value
                        )
                        ->where(
                            'status_mutu',
                            StatusMutuKantongDarah
                                ::Lulus
                                ->value
                        )
                        ->whereBetween(
                            'kedaluwarsa_pada',
                            [
                                now(),
                                now()->addDays(7),
                            ]
                        )
                        ->count();

                $data[] = [
                    'golongan' =>
                        $golonganDarah->label(),

                    'rhesus' =>
                        $rhesus->simbol(),

                    'tersedia' =>
                        $tersedia,

                    'dialokasikan' =>
                        $dialokasikan,

                    'mendekati_kedaluwarsa' =>
                        $mendekatiKedaluwarsa,
                ];
            }
        }

        return $data;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function permintaanPerStatus(): array
    {
        [$mulai, $selesai] = $this->rentangTanggal();

        $data = [];

        foreach (
            StatusPermintaanDarah::cases()
            as $status
        ) {
            $data[] = [
                'label' => $status->label(),
                'warna' => $status->warna(),
                'jumlah' => PermintaanDarah::query()
                    ->where(
                        'status',
                        $status->value
                    )
                    ->whereBetween(
                        'created_at',
                        [
                            $mulai,
                            $selesai,
                        ]
                    )
                    ->count(),
            ];
        }

        return $data;
    }

    /**
     * @return Collection<int, DistribusiDarah>
     */
    public function distribusiTerbaru(): Collection
    {
        [$mulai, $selesai] = $this->rentangTanggal();

        return DistribusiDarah::query()
            ->with([
                'permintaan.rumahSakit',
            ])
            ->whereBetween(
                'created_at',
                [
                    $mulai,
                    $selesai,
                ]
            )
            ->latest('created_at')
            ->limit(15)
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_stok')
                ->label('Export Stok')
                ->icon(
                    'heroicon-o-arrow-down-tray'
                )
                ->color('gray')
                ->action(
                    fn (): StreamedResponse =>
                        $this->exportStok()
                ),

            Actions\Action::make(
                'export_permintaan'
            )
                ->label('Export Pengajuan')
                ->icon(
                    'heroicon-o-arrow-down-tray'
                )
                ->color('gray')
                ->action(
                    fn (): StreamedResponse =>
                        $this->exportPermintaan()
                ),

            Actions\Action::make(
                'export_distribusi'
            )
                ->label('Export Distribusi')
                ->icon(
                    'heroicon-o-arrow-down-tray'
                )
                ->color('gray')
                ->action(
                    fn (): StreamedResponse =>
                        $this->exportDistribusi()
                ),
        ];
    }

    private function exportStok(): StreamedResponse
    {
        return $this->unduhCsv(
            namaFile: sprintf(
                'laporan-stok-darah-%s.csv',
                now()->format('Ymd-His')
            ),
            header: [
                'Golongan Darah',
                'Rhesus',
                'Tersedia',
                'Dialokasikan',
                'Mendekati Kedaluwarsa',
            ],
            penghasilBaris: function (): iterable {
                foreach (
                    $this->stokDarah()
                    as $stok
                ) {
                    yield [
                        $stok['golongan'],
                        $stok['rhesus'],
                        $stok['tersedia'],
                        $stok['dialokasikan'],
                        $stok[
                            'mendekati_kedaluwarsa'
                        ],
                    ];
                }
            },
        );
    }

    private function exportPermintaan(): StreamedResponse
    {
        [$mulai, $selesai] = $this->rentangTanggal();

        return $this->unduhCsv(
            namaFile: sprintf(
                'laporan-pengajuan-kebutuhan-donor-%s.csv',
                now()->format('Ymd-His')
            ),
            header: [
                'Nomor Pengajuan',
                'Pemohon Donor',
                'Referensi Pengajuan',
                'Nama Penanggung Jawab',
                'Golongan Darah',
                'Rhesus',
                'Jumlah Kantong',
                'Urgensi',
                'Status',
                'Dibutuhkan Pada',
                'Dibuat Pada',
            ],
            penghasilBaris: function () use (
                $mulai,
                $selesai
            ): iterable {
                $records =
                    PermintaanDarah::query()
                        ->with('rumahSakit')
                        ->whereBetween(
                            'created_at',
                            [
                                $mulai,
                                $selesai,
                            ]
                        )
                        ->orderBy('id')
                        ->cursor();

                foreach ($records as $record) {
                    yield [
                        $record->nomor_permintaan,
                        $record->rumahSakit
                            ?->nama_rumah_sakit
                            ?? '-',
                        $record->referensi_pasien,
                        $record->nama_dokter,
                        $record->golongan_darah
                            ->label(),
                        $record->rhesus
                            ->simbol(),
                        $record->jumlah_kantong,
                        $record->tingkat_urgensi
                            ->label(),
                        $record->status
                            ->label(),
                        $record->dibutuhkan_pada
                            ?->format(
                                'Y-m-d H:i:s'
                            ),
                        $record->created_at
                            ?->format(
                                'Y-m-d H:i:s'
                            ),
                    ];
                }
            },
        );
    }

    private function exportDistribusi(): StreamedResponse
    {
        [$mulai, $selesai] = $this->rentangTanggal();

        return $this->unduhCsv(
            namaFile: sprintf(
                'laporan-distribusi-kantong-darah-%s.csv',
                now()->format('Ymd-His')
            ),
            header: [
                'Nomor Distribusi',
                'Nomor Pengajuan',
                'Pemohon Donor',
                'Jumlah Kantong',
                'Status',
                'Dijadwalkan Pada',
                'Diserahkan Pada',
                'Nama Penerima',
                'Jabatan Penerima',
            ],
            penghasilBaris: function () use (
                $mulai,
                $selesai
            ): iterable {
                $records =
                    DistribusiDarah::query()
                        ->with([
                            'permintaan.rumahSakit',
                        ])
                        ->whereBetween(
                            'created_at',
                            [
                                $mulai,
                                $selesai,
                            ]
                        )
                        ->orderBy('id')
                        ->cursor();

                foreach ($records as $record) {
                    yield [
                        $record->nomor_distribusi,
                        $record->permintaan
                            ?->nomor_permintaan
                            ?? '-',
                        $record->permintaan
                            ?->rumahSakit
                            ?->nama_rumah_sakit
                            ?? '-',
                        $record->permintaan
                            ?->jumlah_kantong
                            ?? 0,
                        $record->status
                            ->label(),
                        $record->dijadwalkan_pada
                            ?->format(
                                'Y-m-d H:i:s'
                            ),
                        $record->diserahkan_pada
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                            ?? '-',
                        $record->nama_penerima
                            ?? '-',
                        $record->jabatan_penerima
                            ?? '-',
                    ];
                }
            },
        );
    }

    /**
     * @param array<int, string> $header
     */
    private function unduhCsv(
        string $namaFile,
        array $header,
        Closure $penghasilBaris
    ): StreamedResponse {
        return response()->streamDownload(
            function () use (
                $header,
                $penghasilBaris
            ): void {
                $handle = fopen(
                    'php://output',
                    'wb'
                );

                if ($handle === false) {
                    return;
                }

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    $header
                );

                foreach (
                    $penghasilBaris()
                    as $baris
                ) {
                    fputcsv(
                        $handle,
                        $baris
                    );
                }

                fclose($handle);
            },
            $namaFile,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function rentangTanggal(): array
    {
        $mulai = filled($this->tanggalMulai)
            ? CarbonImmutable::parse(
                $this->tanggalMulai
            )->startOfDay()
            : CarbonImmutable::now()
                ->startOfMonth();

        $selesai = filled(
            $this->tanggalSelesai
        )
            ? CarbonImmutable::parse(
                $this->tanggalSelesai
            )->endOfDay()
            : CarbonImmutable::now()
                ->endOfDay();

        if ($mulai->greaterThan($selesai)) {
            $tanggalMulaiLama = $mulai;

            $mulai = $selesai->startOfDay();

            $selesai =
                $tanggalMulaiLama->endOfDay();
        }

        return [
            $mulai,
            $selesai,
        ];
    }
}