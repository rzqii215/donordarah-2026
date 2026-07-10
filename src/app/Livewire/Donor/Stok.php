<?php

namespace App\Livewire\Donor;

use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Models\KantongDarah;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.donor')]
class Stok extends Component
{
    public function render(): View
    {
        return view('livewire.donor.stok', [
            'ringkasan' => $this->ringkasanStok(),
            'stokDarah' => $this->stokPerGolongan(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function ringkasanStok(): array
    {
        return [
            'tersedia' => KantongDarah::query()
                ->where(
                    'status',
                    StatusKantongDarah::Tersedia->value
                )
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus->value
                )
                ->count(),

            'dipesan' => KantongDarah::query()
                ->where(
                    'status',
                    StatusKantongDarah::Dipesan->value
                )
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus->value
                )
                ->count(),

            'didistribusikan' => KantongDarah::query()
                ->where(
                    'status',
                    StatusKantongDarah::Didistribusikan->value
                )
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus->value
                )
                ->count(),

            'lulus_mutu' => KantongDarah::query()
                ->where(
                    'status_mutu',
                    StatusMutuKantongDarah::Lulus->value
                )
                ->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stokPerGolongan(): array
    {
        $agregat = $this->ambilAgregatKantong();

        $hasil = [];

        foreach ($this->golonganDarah() as $golongan) {
            foreach ($this->rhesusDarah() as $rhesus) {
                $tersedia = $this->ambilTotal(
                    agregat: $agregat,
                    golongan: $golongan,
                    rhesus: $rhesus['key'],
                    status: StatusKantongDarah::Tersedia->value,
                );

                $dipesan = $this->ambilTotal(
                    agregat: $agregat,
                    golongan: $golongan,
                    rhesus: $rhesus['key'],
                    status: StatusKantongDarah::Dipesan->value,
                );

                $didistribusikan = $this->ambilTotal(
                    agregat: $agregat,
                    golongan: $golongan,
                    rhesus: $rhesus['key'],
                    status: StatusKantongDarah::Didistribusikan->value,
                );

                $hasil[] = [
                    'kode' => $golongan . $rhesus['label'],
                    'golongan' => $golongan,
                    'rhesus' => $rhesus['label'],
                    'tersedia' => $tersedia,
                    'dipesan' => $dipesan,
                    'didistribusikan' => $didistribusikan,
                    'total' => $tersedia + $dipesan + $didistribusikan,
                    'status_label' => $this->labelKetersediaan($tersedia),
                    'status_class' => $this->classKetersediaan($tersedia),
                    'persentase' => $this->persentaseStok($tersedia),
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
                DB::raw('COUNT(*) as total'),
            ])
            ->where(
                'status_mutu',
                StatusMutuKantongDarah::Lulus->value
            )
            ->whereIn('status', [
                StatusKantongDarah::Tersedia->value,
                StatusKantongDarah::Dipesan->value,
                StatusKantongDarah::Didistribusikan->value,
            ])
            ->groupBy([
                'golongan_darah',
                'rhesus',
                'status',
            ])
            ->get()
            ->map(function (KantongDarah $kantong): array {
                return [
                    'golongan' => $this->normalisasiGolongan(
                        $kantong->golongan_darah
                    ),
                    'rhesus' => $this->normalisasiRhesus(
                        $kantong->rhesus
                    ),
                    'status' => $this->nilaiEnum(
                        $kantong->status
                    ),
                    'total' => (int) $kantong->total,
                ];
            });
    }

    private function ambilTotal(
        Collection $agregat,
        string $golongan,
        string $rhesus,
        string $status
    ): int {
        return (int) $agregat
            ->filter(
                fn (array $item): bool =>
                    $item['golongan'] === $golongan
                    && $item['rhesus'] === $rhesus
                    && $item['status'] === $status
            )
            ->sum('total');
    }

    /**
     * @return array<int, string>
     */
    private function golonganDarah(): array
    {
        return [
            'A',
            'B',
            'AB',
            'O',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function rhesusDarah(): array
    {
        return [
            [
                'key' => 'positive',
                'label' => '+',
            ],
            [
                'key' => 'negative',
                'label' => '-',
            ],
        ];
    }

    private function normalisasiGolongan(mixed $value): string
    {
        $value = strtoupper(
            trim($this->nilaiEnum($value))
        );

        return match ($value) {
            'A' => 'A',
            'B' => 'B',
            'AB' => 'AB',
            'O' => 'O',
            default => $value,
        };
    }

    private function normalisasiRhesus(mixed $value): string
    {
        $value = strtolower(
            trim($this->nilaiEnum($value))
        );

        return match ($value) {
            '+',
            'plus',
            'positif',
            'positive',
            'rh+',
            'rhesus_positive' => 'positive',

            '-',
            'minus',
            'negatif',
            'negative',
            'rh-',
            'rhesus_negative' => 'negative',

            default => $value,
        };
    }

    private function nilaiEnum(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    private function labelKetersediaan(int $tersedia): string
    {
        if ($tersedia <= 0) {
            return 'Kosong';
        }

        if ($tersedia <= 2) {
            return 'Rendah';
        }

        return 'Aman';
    }

    private function classKetersediaan(int $tersedia): string
    {
        if ($tersedia <= 0) {
            return 'is-danger';
        }

        if ($tersedia <= 2) {
            return 'is-warning';
        }

        return 'is-success';
    }

    private function persentaseStok(int $tersedia): int
    {
        if ($tersedia <= 0) {
            return 0;
        }

        if ($tersedia >= 10) {
            return 100;
        }

        return $tersedia * 10;
    }
}