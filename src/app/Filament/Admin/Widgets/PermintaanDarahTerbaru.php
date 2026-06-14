<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusPermintaanDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Filament\Admin\Resources\PermintaanDarahResource;
use App\Models\PermintaanDarah;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PermintaanDarahTerbaru extends TableWidget
{
    protected static ?string $heading =
        'Permintaan Darah Terbaru';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PermintaanDarah::query()
                    ->with('rumahSakit')
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make(
                    'nomor_permintaan'
                )
                    ->label('Nomor')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'rumahSakit.nama_rumah_sakit'
                )
                    ->label('Rumah Sakit')
                    ->wrap(),

                Tables\Columns\TextColumn::make(
                    'golongan_darah'
                )
                    ->label('Golongan')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            GolonganDarah|string $state
                        ): string => self::labelGolongan(
                            $state
                        )
                    )
                    ->description(
                        fn (
                            PermintaanDarah $record
                        ): string => self::labelRhesus(
                            $record->rhesus
                        )
                    ),

                Tables\Columns\TextColumn::make(
                    'jumlah_kantong'
                )
                    ->label('Jumlah')
                    ->suffix(' kantong'),

                Tables\Columns\TextColumn::make(
                    'tingkat_urgensi'
                )
                    ->label('Urgensi')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            TingkatUrgensiPermintaanDarah|string $state
                        ): string => self::urgensiEnum(
                            $state
                        )->label()
                    )
                    ->color(
                        fn (
                            TingkatUrgensiPermintaanDarah|string $state
                        ): string => self::urgensiEnum(
                            $state
                        )->warna()
                    ),

                Tables\Columns\TextColumn::make(
                    'status'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusPermintaanDarah|string $state
                        ): string => self::statusEnum(
                            $state
                        )->label()
                    )
                    ->color(
                        fn (
                            StatusPermintaanDarah|string $state
                        ): string => self::statusEnum(
                            $state
                        )->warna()
                    ),

                Tables\Columns\TextColumn::make(
                    'dibutuhkan_pada'
                )
                    ->label('Dibutuhkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (
                            PermintaanDarah $record
                        ): string => PermintaanDarahResource
                            ::getUrl(
                                'view',
                                [
                                    'record' => $record,
                                ]
                            )
                    ),
            ])
            ->paginated(false)
            ->emptyStateHeading(
                'Belum ada permintaan darah'
            )
            ->emptyStateIcon(
                'heroicon-o-document-text'
            );
    }

    private static function statusEnum(
        StatusPermintaanDarah|string $status
    ): StatusPermintaanDarah {
        return $status instanceof
            StatusPermintaanDarah
                ? $status
                : StatusPermintaanDarah::from(
                    $status
                );
    }

    private static function urgensiEnum(
        TingkatUrgensiPermintaanDarah|string $urgensi
    ): TingkatUrgensiPermintaanDarah {
        return $urgensi instanceof
            TingkatUrgensiPermintaanDarah
                ? $urgensi
                : TingkatUrgensiPermintaanDarah
                    ::from($urgensi);
    }

    private static function labelGolongan(
        GolonganDarah|string $golonganDarah
    ): string {
        return $golonganDarah instanceof
            GolonganDarah
                ? $golonganDarah->label()
                : GolonganDarah::from(
                    $golonganDarah
                )->label();
    }

    private static function labelRhesus(
        RhesusDarah|string $rhesus
    ): string {
        return $rhesus instanceof RhesusDarah
            ? 'Rhesus ' . $rhesus->simbol()
            : 'Rhesus '
                . RhesusDarah::from(
                    $rhesus
                )->simbol();
    }
}