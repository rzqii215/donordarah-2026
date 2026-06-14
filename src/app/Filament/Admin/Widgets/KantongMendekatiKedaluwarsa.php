<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Filament\Admin\Resources\KantongDarahResource;
use App\Models\KantongDarah;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class KantongMendekatiKedaluwarsa extends TableWidget
{
    protected static ?string $heading =
        'Kantong Mendekati Kedaluwarsa';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KantongDarah::query()
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
                    ->orderBy('kedaluwarsa_pada')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make(
                    'kode_kantong'
                )
                    ->label('Kode Kantong')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'golongan_darah'
                )
                    ->label('Golongan')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            GolonganDarah|string $state
                        ): string => $state instanceof
                            GolonganDarah
                                ? $state->label()
                                : GolonganDarah::from(
                                    $state
                                )->label()
                    ),

                Tables\Columns\TextColumn::make(
                    'rhesus'
                )
                    ->label('Rhesus')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            RhesusDarah|string $state
                        ): string => $state instanceof
                            RhesusDarah
                                ? $state->simbol()
                                : RhesusDarah::from(
                                    $state
                                )->simbol()
                    ),

                Tables\Columns\TextColumn::make(
                    'volume_ml'
                )
                    ->label('Volume')
                    ->suffix(' ml'),

                Tables\Columns\TextColumn::make(
                    'lokasi_penyimpanan'
                )
                    ->label('Penyimpanan')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make(
                    'kedaluwarsa_pada'
                )
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->description(
                        fn (
                            KantongDarah $record
                        ): string => $record
                            ->kedaluwarsa_pada
                            ->diffForHumans()
                    )
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (
                            KantongDarah $record
                        ): string => KantongDarahResource
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
                'Tidak ada kantong yang segera kedaluwarsa'
            )
            ->emptyStateDescription(
                'Tidak ditemukan stok tersedia yang kedaluwarsa dalam tujuh hari ke depan.'
            )
            ->emptyStateIcon(
                'heroicon-o-check-circle'
            );
    }
}