<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Models\KantongDarah;
use Filament\Widgets\ChartWidget;

class StokDarahPerGolongan extends ChartWidget
{
    protected static ?string $heading =
        'Stok Darah Tersedia per Golongan';

    protected static ?string $description =
        'Jumlah kantong yang lulus mutu dan belum kedaluwarsa.';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $labels = [];
        $jumlahStok = [];

        foreach (GolonganDarah::cases() as $golonganDarah) {
            foreach (RhesusDarah::cases() as $rhesus) {
                $labels[] = sprintf(
                    '%s%s',
                    $golonganDarah->label(),
                    $rhesus->simbol(),
                );

                $jumlahStok[] = KantongDarah::query()
                    ->tersedia()
                    ->where(
                        'golongan_darah',
                        $golonganDarah->value
                    )
                    ->where(
                        'rhesus',
                        $rhesus->value
                    )
                    ->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kantong',
                    'data' => $jumlahStok,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}