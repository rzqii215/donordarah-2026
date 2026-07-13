<?php

namespace App\Livewire\Donor;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Models\KantongDarah;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use UnitEnum;

#[Layout('components.layouts.donor')]
class Stok extends Component
{
    #[Url(as: 'cari', except: '')]
    public string $pencarian = '';

    #[Url(as: 'rhesus', except: 'semua')]
    public string $filterRhesus = 'semua';

    #[Url(as: 'tersedia', except: false)]
    public bool $hanyaTersedia = false;

    #[Url(as: 'urut', except: 'golongan')]
    public string $urutan = 'golongan';

    /**
     * @var array<int, string>
     */
    private const FILTER_RHESUS_VALID = [
        'semua',
        'positive',
        'negative',
    ];

    /**
     * @var array<int, string>
     */
    private const URUTAN_VALID = [
        'golongan',
        'tersedia_terbanyak',
        'tersedia_tersedikit',
        'total_terbanyak',
    ];

    public function mount(): void
    {
        $this->pastikanFilterValid();
    }

    public function updatedFilterRhesus(
        string $value
    ): void {
        if (
            ! in_array(
                $value,
                self::FILTER_RHESUS_VALID,
                true
            )
        ) {
            $this->filterRhesus = 'semua';
        }
    }

    public function updatedUrutan(
        string $value
    ): void {
        if (
            ! in_array(
                $value,
                self::URUTAN_VALID,
                true
            )
        ) {
            $this->urutan = 'golongan';
        }
    }

    public function resetFilter(): void
    {
        $this->pencarian = '';
        $this->filterRhesus = 'semua';
        $this->hanyaTersedia = false;
        $this->urutan = 'golongan';
    }

    public function render(): View
    {
        $seluruhStok =
            $this->stokPerGolongan();

        $stokDarah =
            $this->terapkanFilter(
                $seluruhStok
            );

        $peringatanStok = collect(
            $seluruhStok
        )
            ->filter(
                fn (array $stok): bool => $stok['tersedia'] <= 2
            )
            ->sortBy('tersedia')
            ->values();

        return view(
            'livewire.donor.stok',
            [
                'ringkasan' => $this->ringkasanStok(),

                'stokDarah' => $stokDarah,

                'peringatanStok' => $peringatanStok,

                'diperbaruiPada' => $this->waktuPembaruanTerakhir(),

                'pencarian' => $this->pencarian,

                'filterRhesus' => $this->filterRhesus,

                'hanyaTersedia' => $this->hanyaTersedia,

                'urutan' => $this->urutan,
            ]
        );
    }

    /**
     * @return array<string, int>
     */
    private function ringkasanStok(): array
    {
        $tersedia = KantongDarah::query()
            ->tersedia()
            ->count();

        $dipesan = KantongDarah::query()
            ->where(
                'status',
                StatusKantongDarah::Dipesan
                    ->value
            )
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus
                    ->value
            )
            ->where(
                'kedaluwarsa_pada',
                '>',
                now()
            )
            ->count();

        $didistribusikan =
            KantongDarah::query()
                ->where(
                    'status',
                    StatusKantongDarah::Didistribusikan
                        ->value
                )
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus
                        ->value
                )
                ->count();

        $lulusMutu = KantongDarah::query()
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus
                    ->value
            )
            ->where(
                function (
                    Builder $query
                ): void {
                    $query
                        ->where(
                            'status',
                            StatusKantongDarah::Didistribusikan
                                ->value
                        )
                        ->orWhere(
                            'kedaluwarsa_pada',
                            '>',
                            now()
                        );
                }
            )
            ->count();

        $hampirKedaluwarsa =
            KantongDarah::query()
                ->tersedia()
                ->whereBetween(
                    'kedaluwarsa_pada',
                    [
                        now(),
                        now()->addDays(7),
                    ]
                )
                ->count();

        $volumeTersedia =
            (int) KantongDarah::query()
                ->tersedia()
                ->sum('volume_ml');

        return [
            'tersedia' => $tersedia,

            'dipesan' => $dipesan,

            'didistribusikan' => $didistribusikan,

            'lulus_mutu' => $lulusMutu,

            'hampir_kedaluwarsa' => $hampirKedaluwarsa,

            'volume_tersedia_ml' => $volumeTersedia,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stokPerGolongan(): array
    {
        $agregat =
            $this->ambilAgregatKantong();

        $hasil = [];

        foreach (
            GolonganDarah::cases() as $golongan
        ) {
            foreach (
                RhesusDarah::cases() as $rhesus
            ) {
                $tersedia =
                    $this->ambilTotal(
                        agregat: $agregat,
                        golongan: $golongan->value,
                        rhesus: $rhesus->value,
                        status: StatusKantongDarah::Tersedia
                            ->value,
                    );

                $dipesan =
                    $this->ambilTotal(
                        agregat: $agregat,
                        golongan: $golongan->value,
                        rhesus: $rhesus->value,
                        status: StatusKantongDarah::Dipesan
                            ->value,
                    );

                $didistribusikan =
                    $this->ambilTotal(
                        agregat: $agregat,
                        golongan: $golongan->value,
                        rhesus: $rhesus->value,
                        status: StatusKantongDarah::Didistribusikan
                            ->value,
                    );

                $total =
                    $tersedia
                    + $dipesan
                    + $didistribusikan;

                $hasil[] = [
                    'kode' => $golongan->value .
                        $rhesus->simbol(),

                    'golongan' => $golongan->value,

                    'rhesus' => $rhesus->value,

                    'rhesus_label' => $rhesus->label(),

                    'rhesus_simbol' => $rhesus->simbol(),

                    'tersedia' => $tersedia,

                    'dipesan' => $dipesan,

                    'didistribusikan' => $didistribusikan,

                    'total' => $total,

                    'status_label' => $this->labelKetersediaan(
                        $tersedia
                    ),

                    'status_class' => $this->classKetersediaan(
                        $tersedia
                    ),

                    'persentase' => $this->persentaseStok(
                        $tersedia
                    ),
                ];
            }
        }

        return $hasil;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function ambilAgregatKantong(): Collection
    {
        return KantongDarah::query()
            ->select([
                'golongan_darah',
                'rhesus',
                'status',
                DB::raw(
                    'COUNT(*) as total'
                ),
            ])
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus
                    ->value
            )
            ->whereIn(
                'status',
                [
                    StatusKantongDarah::Tersedia
                        ->value,

                    StatusKantongDarah::Dipesan
                        ->value,

                    StatusKantongDarah::Didistribusikan
                        ->value,
                ]
            )
            ->where(
                function (
                    Builder $query
                ): void {
                    $query
                        ->where(
                            'status',
                            StatusKantongDarah::Didistribusikan
                                ->value
                        )
                        ->orWhere(
                            'kedaluwarsa_pada',
                            '>',
                            now()
                        );
                }
            )
            ->groupBy([
                'golongan_darah',
                'rhesus',
                'status',
            ])
            ->get()
            ->map(
                function (
                    KantongDarah $kantong
                ): array {
                    return [
                        'golongan' => $this->nilaiEnum(
                            $kantong
                                ->golongan_darah
                        ),

                        'rhesus' => $this->nilaiEnum(
                            $kantong->rhesus
                        ),

                        'status' => $this->nilaiEnum(
                            $kantong->status
                        ),

                        'total' => (int) $kantong
                            ->getAttribute(
                                'total'
                            ),
                    ];
                }
            );
    }

    private function ambilTotal(
        Collection $agregat,
        string $golongan,
        string $rhesus,
        string $status
    ): int {
        return (int) $agregat
            ->filter(
                fn (array $item): bool => $item['golongan'] ===
                        $golongan
                    && $item['rhesus'] ===
                        $rhesus
                    && $item['status'] ===
                        $status
            )
            ->sum('total');
    }

    /**
     * @param  array<int, array<string, mixed>>  $stokDarah
     * @return array<int, array<string, mixed>>
     */
    private function terapkanFilter(
        array $stokDarah
    ): array {
        $hasil = collect(
            $stokDarah
        );

        $pencarian = mb_strtolower(
            trim($this->pencarian)
        );

        if ($pencarian !== '') {
            $hasil = $hasil->filter(
                function (
                    array $stok
                ) use (
                    $pencarian
                ): bool {
                    $teks = mb_strtolower(
                        implode(
                            ' ',
                            [
                                $stok['kode'],
                                $stok['golongan'],
                                $stok['rhesus'],
                                $stok['rhesus_label'],
                                $stok['status_label'],
                            ]
                        )
                    );

                    return str_contains(
                        $teks,
                        $pencarian
                    );
                }
            );
        }

        if (
            $this->filterRhesus !==
            'semua'
        ) {
            $hasil = $hasil->where(
                'rhesus',
                $this->filterRhesus
            );
        }

        if ($this->hanyaTersedia) {
            $hasil = $hasil->filter(
                fn (array $stok): bool => $stok['tersedia'] > 0
            );
        }

        $hasil = match ($this->urutan) {
            'tersedia_terbanyak' => $hasil->sortByDesc(
                'tersedia'
            ),

            'tersedia_tersedikit' => $hasil->sortBy(
                'tersedia'
            ),

            'total_terbanyak' => $hasil->sortByDesc(
                'total'
            ),

            default => $hasil->sortBy(
                function (
                    array $stok
                ): string {
                    $urutanGolongan = [
                        'A' => '1',
                        'B' => '2',
                        'AB' => '3',
                        'O' => '4',
                    ];

                    $urutanRhesus =
                        $stok['rhesus'] ===
                        RhesusDarah::Positif
                            ->value
                            ? '1'
                            : '2';

                    return (
                        $urutanGolongan[
                            $stok['golongan']
                        ]
                        ?? '9'
                    ) . $urutanRhesus;
                }
            ),
        };

        return $hasil
            ->values()
            ->all();
    }

    private function labelKetersediaan(
        int $tersedia
    ): string {
        if ($tersedia <= 0) {
            return 'Kosong';
        }

        if ($tersedia <= 2) {
            return 'Kritis';
        }

        if ($tersedia <= 5) {
            return 'Rendah';
        }

        return 'Aman';
    }

    private function classKetersediaan(
        int $tersedia
    ): string {
        if ($tersedia <= 0) {
            return 'danger';
        }

        if ($tersedia <= 2) {
            return 'critical';
        }

        if ($tersedia <= 5) {
            return 'warning';
        }

        return 'success';
    }

    private function persentaseStok(
        int $tersedia
    ): int {
        if ($tersedia <= 0) {
            return 0;
        }

        return min(
            100,
            $tersedia * 10
        );
    }

    private function nilaiEnum(
        mixed $value
    ): string {
        if (
            $value instanceof BackedEnum
        ) {
            return (string) $value
                ->value;
        }

        if (
            $value instanceof UnitEnum
        ) {
            return (string) $value
                ->name;
        }

        return trim(
            (string) $value
        );
    }

    private function waktuPembaruanTerakhir(): string
    {
        $terakhirDiperbarui =
            KantongDarah::query()
                ->max('updated_at');

        if ($terakhirDiperbarui === null) {
            return 'Belum ada data';
        }

        return Carbon::parse(
            $terakhirDiperbarui
        )
            ->timezone(
                config(
                    'app.timezone',
                    'Asia/Jakarta'
                )
            )
            ->translatedFormat(
                'd F Y, H:i'
            ) . ' WIB';
    }

    private function pastikanFilterValid(): void
    {
        if (
            ! in_array(
                $this->filterRhesus,
                self::FILTER_RHESUS_VALID,
                true
            )
        ) {
            $this->filterRhesus =
                'semua';
        }

        if (
            ! in_array(
                $this->urutan,
                self::URUTAN_VALID,
                true
            )
        ) {
            $this->urutan =
                'golongan';
        }
    }
}
