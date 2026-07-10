<?php

namespace App\Filament\Admin\Resources;

use App\Enums\StatusItemPermintaanDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\ItemPermintaanDarahResource\Pages;
use App\Models\ItemPermintaanDarah;
use App\Models\KantongDarah;
use App\Models\PermintaanDarah;
use App\Services\LayananAlokasiDarah;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ItemPermintaanDarahResource extends Resource
{
    protected static ?string $model = ItemPermintaanDarah::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Alokasi Kantong Darah';

    protected static ?string $modelLabel = 'Alokasi Kantong Darah';

    protected static ?string $pluralModelLabel = 'Alokasi Kantong Darah';

    protected static ?string $navigationGroup = 'Pengajuan dan Distribusi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengajuan Kebutuhan Donor')
                    ->description('Pilih pengajuan yang sudah disetujui atau sedang menunggu stok.')
                    ->schema([
                        Forms\Components\Select::make('permintaan_darah_id')
                            ->label('Pengajuan Kebutuhan Donor')
                            ->options(
                                fn (): array => PermintaanDarah::query()
                                    ->whereIn(
                                        'status',
                                        [
                                            StatusPermintaanDarah::Disetujui->value,
                                            StatusPermintaanDarah::MenungguStok->value,
                                            StatusPermintaanDarah::SiapDiambil->value,
                                        ]
                                    )
                                    ->with('rumahSakit')
                                    ->orderByDesc('created_at')
                                    ->get()
                                    ->filter(
                                        fn (PermintaanDarah $record): bool => $record
                                            ->sisaKebutuhanKantong() > 0
                                    )
                                    ->mapWithKeys(
                                        fn (PermintaanDarah $record): array => [
                                            $record->id => sprintf(
                                                '%s — %s — %s%s — Sisa %d',
                                                $record->nomor_permintaan,
                                                $record->rumahSakit->nama_rumah_sakit,
                                                $record->golongan_darah->label(),
                                                $record->rhesus->simbol(),
                                                $record->sisaKebutuhanKantong(),
                                            ),
                                        ]
                                    )
                                    ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                function (Set $set): void {
                                    $set('kantong_darah_id', null);
                                }
                            ),

                        Forms\Components\Placeholder::make('kebutuhan_info')
                            ->label('Kebutuhan Donor')
                            ->content(
                                function (Get $get): string {
                                    $permintaan = PermintaanDarah::query()
                                        ->find(
                                            $get('permintaan_darah_id')
                                        );

                                    if ($permintaan === null) {
                                        return '-';
                                    }

                                    return sprintf(
                                        '%s%s — %d kantong — Sisa %d',
                                        $permintaan->golongan_darah->label(),
                                        $permintaan->rhesus->simbol(),
                                        $permintaan->jumlah_kantong,
                                        $permintaan->sisaKebutuhanKantong(),
                                    );
                                }
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kantong Darah')
                    ->description('Hanya kantong tersedia, lulus mutu, belum kedaluwarsa, dan sesuai golongan darah yang ditampilkan.')
                    ->schema([
                        Forms\Components\Select::make('kantong_darah_id')
                            ->label('Kantong Darah')
                            ->options(
                                function (Get $get): array {
                                    $permintaan = PermintaanDarah::query()
                                        ->find(
                                            $get('permintaan_darah_id')
                                        );

                                    if ($permintaan === null) {
                                        return [];
                                    }

                                    return KantongDarah::query()
                                        ->where(
                                            'status',
                                            StatusKantongDarah::Tersedia->value
                                        )
                                        ->where(
                                            'status_mutu',
                                            StatusMutuKantongDarah::Lulus->value
                                        )
                                        ->where(
                                            'golongan_darah',
                                            $permintaan->golongan_darah->value
                                        )
                                        ->where(
                                            'rhesus',
                                            $permintaan->rhesus->value
                                        )
                                        ->where(
                                            'kedaluwarsa_pada',
                                            '>',
                                            now()
                                        )
                                        ->whereDoesntHave('alokasiAktif')
                                        ->orderBy('kedaluwarsa_pada')
                                        ->get()
                                        ->mapWithKeys(
                                            fn (KantongDarah $record): array => [
                                                $record->id => sprintf(
                                                    '%s — %s%s — %d ml — Kedaluwarsa %s',
                                                    $record->kode_kantong,
                                                    $record->golongan_darah->label(),
                                                    $record->rhesus->simbol(),
                                                    $record->volume_ml,
                                                    $record->kedaluwarsa_pada->format('d M Y'),
                                                ),
                                            ]
                                        )
                                        ->all();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Alokasi')
                            ->rows(3)
                            ->maxLength(2000),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Alokasi Kantong Darah')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Alokasi')
                            ->badge()
                            ->formatStateUsing(
                                fn (StatusItemPermintaanDarah|string $state): string => self::statusEnum($state)->label()
                            )
                            ->color(
                                fn (StatusItemPermintaanDarah|string $state): string => self::statusEnum($state)->warna()
                            ),

                        TextEntry::make('permintaan.nomor_permintaan')
                            ->label('Nomor Pengajuan')
                            ->copyable(),

                        TextEntry::make('permintaan.rumahSakit.nama_rumah_sakit')
                            ->label('Pemohon Donor'),

                        TextEntry::make('kantongDarah.kode_kantong')
                            ->label('Kode Kantong')
                            ->copyable(),

                        TextEntry::make('kantongDarah.golongan_darah')
                            ->label('Golongan Darah'),

                        TextEntry::make('kantongDarah.rhesus')
                            ->label('Rhesus'),

                        TextEntry::make('kantongDarah.volume_ml')
                            ->label('Volume')
                            ->suffix(' ml'),

                        TextEntry::make('kantongDarah.kedaluwarsa_pada')
                            ->label('Kedaluwarsa')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(4),

                InfolistSection::make('Riwayat Alokasi')
                    ->schema([
                        TextEntry::make('pengalokasi.name')
                            ->label('Dialokasikan Oleh'),

                        TextEntry::make('dialokasikan_pada')
                            ->label('Dialokasikan Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('pelepas.name')
                            ->label('Dilepaskan Oleh')
                            ->placeholder('-'),

                        TextEntry::make('dilepas_pada')
                            ->label('Dilepaskan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('alasan_pelepasan')
                            ->label('Alasan Pelepasan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('didistribusikan_pada')
                            ->label('Didistribusikan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permintaan.nomor_permintaan')
                    ->label('Pengajuan')
                    ->description(
                        fn (ItemPermintaanDarah $record): string => $record
                            ->permintaan
                            ->rumahSakit
                            ->nama_rumah_sakit
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kantongDarah.kode_kantong')
                    ->label('Kode Kantong')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('kantongDarah.golongan_darah')
                    ->label('Golongan')
                    ->badge(),

                Tables\Columns\TextColumn::make('kantongDarah.rhesus')
                    ->label('Rhesus')
                    ->badge(),

                Tables\Columns\TextColumn::make('kantongDarah.volume_ml')
                    ->label('Volume')
                    ->suffix(' ml'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (StatusItemPermintaanDarah|string $state): string => self::statusEnum($state)->label()
                    )
                    ->color(
                        fn (StatusItemPermintaanDarah|string $state): string => self::statusEnum($state)->warna()
                    )
                    ->sortable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('pengalokasi.name')
                    ->label('Dialokasikan Oleh')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dialokasikan_pada')
                    ->label('Dialokasikan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kantongDarah.kedaluwarsa_pada')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dilepas_pada')
                    ->label('Dilepaskan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Alokasi')
                    ->options(
                        StatusItemPermintaanDarah::options()
                    ),

                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Aktif')
                    ->placeholder('Semua Alokasi')
                    ->trueLabel('Masih Aktif')
                    ->falseLabel('Sudah Tidak Aktif'),

                Tables\Filters\SelectFilter::make('permintaan_darah_id')
                    ->label('Pengajuan Kebutuhan Donor')
                    ->relationship(
                        'permintaan',
                        'nomor_permintaan'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\Action::make('lepaskan')
                    ->label('Lepaskan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(
                        fn (ItemPermintaanDarah $record): bool => $record
                            ->dapatDilepaskan()
                    )
                    ->form([
                        Forms\Components\Textarea::make('alasan')
                            ->label('Alasan Pelepasan')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Lepaskan Alokasi Kantong Darah')
                    ->modalDescription('Kantong akan dikembalikan ke stok tersedia jika belum kedaluwarsa.')
                    ->action(
                        function (
                            ItemPermintaanDarah $record,
                            array $data
                        ): void {
                            app(LayananAlokasiDarah::class)->lepaskan(
                                item: $record,
                                petugasId: (int) Filament::auth()->id(),
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title('Alokasi Kantong Darah berhasil dilepaskan.')
                                ->warning()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('dialokasikan_pada', 'desc')
            ->emptyStateHeading('Belum ada alokasi kantong darah')
            ->emptyStateDescription('Alokasikan stok yang tersedia ke pengajuan kebutuhan donor yang sudah disetujui.')
            ->emptyStateIcon('heroicon-o-arrows-right-left');
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemPermintaanDarahs::route('/'),
            'create' => Pages\CreateItemPermintaanDarah::route('/create'),
            'view' => Pages\ViewItemPermintaanDarah::route('/{record}'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->kantongDarah?->kode_kantong
            ?? 'Alokasi Kantong Darah';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Pengajuan' => $record->permintaan?->nomor_permintaan ?? '-',
            'Pemohon Donor' => $record->permintaan?->rumahSakit?->nama_rumah_sakit ?? '-',
            'Status' => $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'permintaan.rumahSakit',
                'kantongDarah',
                'pengalokasi',
                'pelepas',
            ]);
    }

    private static function statusEnum(
        StatusItemPermintaanDarah|string $status
    ): StatusItemPermintaanDarah {
        return $status instanceof StatusItemPermintaanDarah
            ? $status
            : StatusItemPermintaanDarah::from($status);
    }
}