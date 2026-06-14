<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\JenisKomponenDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\KantongDarahResource\Pages;
use App\Models\KantongDarah;
use App\Models\PendaftaranDonor;
use App\Services\LayananKantongDarah;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KantongDarahResource extends Resource
{
    protected static ?string $model =
        KantongDarah::class;

    protected static ?string $navigationIcon =
        'heroicon-o-beaker';

    protected static ?string $navigationLabel =
        'Kantong Darah';

    protected static ?string $modelLabel =
        'Kantong Darah';

    protected static ?string $pluralModelLabel =
        'Kantong Darah';

    protected static ?string $navigationGroup =
        'Manajemen Stok Darah';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Sumber Donor'
                )
                    ->description(
                        'Kantong darah hanya dapat dibuat dari Pendonor yang dinyatakan layak.'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'pendaftaran_donor_id'
                        )
                            ->label('Pendaftaran Donor')
                            ->relationship(
                                name: 'pendaftaran',
                                titleAttribute:
                                    'nomor_pendaftaran',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder => $query
                                        ->where(
                                            'status',
                                            StatusPendaftaranDonor
                                                ::Layak
                                                ->value
                                        )
                                        ->whereDoesntHave(
                                            'kantongDarah'
                                        )
                                        ->with([
                                            'pendonor',
                                            'pemeriksaanKesehatan',
                                        ])
                                        ->orderByDesc(
                                            'hadir_pada'
                                        ),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (
                                    PendaftaranDonor $record
                                ): string => sprintf(
                                    '%s — %s',
                                    $record
                                        ->nomor_pendaftaran,
                                    $record
                                        ->pendonor
                                        ->name,
                                )
                            )
                            ->searchable([
                                'nomor_pendaftaran',
                            ])
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                function (
                                    mixed $state,
                                    Set $set
                                ): void {
                                    if (blank($state)) {
                                        $set(
                                            'golongan_darah',
                                            null
                                        );

                                        $set(
                                            'rhesus',
                                            null
                                        );

                                        return;
                                    }

                                    $pendaftaran =
                                        PendaftaranDonor::query()
                                            ->with(
                                                'pemeriksaanKesehatan'
                                            )
                                            ->find($state);

                                    $set(
                                        'golongan_darah',
                                        $pendaftaran
                                            ?->pemeriksaanKesehatan
                                            ?->golongan_darah
                                            ?->value
                                    );

                                    $set(
                                        'rhesus',
                                        $pendaftaran
                                            ?->pemeriksaanKesehatan
                                            ?->rhesus
                                            ?->value
                                    );
                                }
                            )
                            ->visibleOn('create'),

                        Forms\Components\Placeholder::make(
                            'pendaftaran_info'
                        )
                            ->label('Pendaftaran Donor')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? sprintf(
                                        '%s — %s',
                                        $record
                                            ->pendaftaran
                                            ->nomor_pendaftaran,
                                        $record
                                            ->pendaftaran
                                            ->pendonor
                                            ->name,
                                    )
                                    : '-'
                            )
                            ->hiddenOn('create'),

                        Forms\Components\Placeholder::make(
                            'kode_kantong_info'
                        )
                            ->label('Kode Kantong')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? $record->kode_kantong
                                    : 'Dibuat otomatis setelah disimpan'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Informasi Darah'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->options(
                                GolonganDarah::options()
                            )
                            ->required()
                            ->native(false)
                            ->disabled(
                                fn (
                                    ?KantongDarah $record
                                ): bool => $record !== null
                                    && $record->status !==
                                        StatusKantongDarah
                                            ::Menunggu
                            )
                            ->dehydrated(),

                        Forms\Components\Select::make(
                            'rhesus'
                        )
                            ->label('Rhesus')
                            ->options(
                                RhesusDarah::options()
                            )
                            ->required()
                            ->native(false)
                            ->disabled(
                                fn (
                                    ?KantongDarah $record
                                ): bool => $record !== null
                                    && $record->status !==
                                        StatusKantongDarah
                                            ::Menunggu
                            )
                            ->dehydrated(),

                        Forms\Components\Select::make(
                            'jenis_komponen'
                        )
                            ->label('Jenis Komponen')
                            ->options(
                                JenisKomponenDarah::options()
                            )
                            ->default(
                                JenisKomponenDarah
                                    ::DarahUtuh
                                    ->value
                            )
                            ->required()
                            ->native(false)
                            ->disabled(
                                fn (
                                    ?KantongDarah $record
                                ): bool => $record !== null
                                    && $record->status !==
                                        StatusKantongDarah
                                            ::Menunggu
                            )
                            ->dehydrated(),

                        Forms\Components\TextInput::make(
                            'volume_ml'
                        )
                            ->label('Volume')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->suffix('ml')
                            ->disabled(
                                fn (
                                    ?KantongDarah $record
                                ): bool => $record !== null
                                    && $record->status !==
                                        StatusKantongDarah
                                            ::Menunggu
                            )
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Penyimpanan'
                )
                    ->schema([
                        Forms\Components\DateTimePicker::make(
                            'diambil_pada'
                        )
                            ->label('Waktu Pengambilan')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->disabled(
                                fn (
                                    ?KantongDarah $record
                                ): bool => $record !== null
                                    && $record->status !==
                                        StatusKantongDarah
                                            ::Menunggu
                            )
                            ->dehydrated(),

                        Forms\Components\DateTimePicker::make(
                            'kedaluwarsa_pada'
                        )
                            ->label('Waktu Kedaluwarsa')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('diambil_pada'),

                        Forms\Components\TextInput::make(
                            'lokasi_penyimpanan'
                        )
                            ->label('Lokasi Penyimpanan')
                            ->maxLength(255)
                            ->placeholder(
                                'Contoh: Lemari Pendingin A-01'
                            ),

                        Forms\Components\Textarea::make(
                            'catatan'
                        )
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Status Verifikasi'
                )
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_mutu_info'
                        )
                            ->label('Status Mutu')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? $record
                                        ->status_mutu
                                        ->label()
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'status_info'
                        )
                            ->label('Status Kantong')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? $record->status->label()
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'verifikator_info'
                        )
                            ->label('Diverifikasi Oleh')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->verifikator
                                            ?->name
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'diverifikasi_pada_info'
                        )
                            ->label('Diverifikasi Pada')
                            ->content(
                                fn (
                                    ?KantongDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->diverifikasi_pada
                                            ?->format(
                                                'd M Y H:i'
                                            )
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Textarea::make(
                            'alasan_penolakan'
                        )
                            ->label('Alasan Penolakan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(
        Infolist $infolist
    ): Infolist {
        return $infolist
            ->schema([
                InfolistSection::make(
                    'Informasi Kantong'
                )
                    ->schema([
                        TextEntry::make('kode_kantong')
                            ->label('Kode Kantong')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Status Kantong')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusKantongDarah|string $state
                                ): string => self
                                    ::statusKantongEnum(
                                        $state
                                    )
                                    ->label()
                            )
                            ->color(
                                fn (
                                    StatusKantongDarah|string $state
                                ): string => self
                                    ::statusKantongEnum(
                                        $state
                                    )
                                    ->warna()
                            ),

                        TextEntry::make('status_mutu')
                            ->label('Status Mutu')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusMutuKantongDarah|string $state
                                ): string => self
                                    ::statusMutuEnum(
                                        $state
                                    )
                                    ->label()
                            )
                            ->color(
                                fn (
                                    StatusMutuKantongDarah|string $state
                                ): string => self
                                    ::statusMutuEnum(
                                        $state
                                    )
                                    ->warna()
                            ),

                        TextEntry::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    mixed $state
                                ): string => $state
                                    instanceof
                                    GolonganDarah
                                        ? $state->label()
                                        : (string) $state
                            ),

                        TextEntry::make('rhesus')
                            ->label('Rhesus')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    mixed $state
                                ): string => $state
                                    instanceof RhesusDarah
                                        ? $state->label()
                                        : (string) $state
                            ),

                        TextEntry::make(
                            'jenis_komponen'
                        )
                            ->label('Jenis Komponen')
                            ->formatStateUsing(
                                fn (
                                    mixed $state
                                ): string => $state
                                    instanceof
                                    JenisKomponenDarah
                                        ? $state->label()
                                        : (string) $state
                            ),

                        TextEntry::make('volume_ml')
                            ->label('Volume')
                            ->suffix(' ml'),

                        TextEntry::make(
                            'lokasi_penyimpanan'
                        )
                            ->label('Lokasi Penyimpanan')
                            ->placeholder('-'),
                    ])
                    ->columns(4),

                InfolistSection::make(
                    'Sumber Donor'
                )
                    ->schema([
                        TextEntry::make(
                            'pendaftaran.nomor_pendaftaran'
                        )
                            ->label('Nomor Pendaftaran')
                            ->copyable(),

                        TextEntry::make(
                            'pendaftaran.pendonor.name'
                        )
                            ->label('Nama Pendonor'),

                        TextEntry::make(
                            'pendaftaran.pendonor.profilPendonor.kode_pendonor'
                        )
                            ->label('Kode Pendonor')
                            ->placeholder('-'),

                        TextEntry::make(
                            'pendaftaran.jadwal.judul'
                        )
                            ->label('Jadwal Donor'),
                    ])
                    ->columns(2),

                InfolistSection::make(
                    'Waktu dan Verifikasi'
                )
                    ->schema([
                        TextEntry::make('diambil_pada')
                            ->label('Diambil Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'kedaluwarsa_pada'
                        )
                            ->label('Kedaluwarsa Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'verifikator.name'
                        )
                            ->label('Diverifikasi Oleh')
                            ->placeholder('-'),

                        TextEntry::make(
                            'diverifikasi_pada'
                        )
                            ->label('Diverifikasi Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'alasan_penolakan'
                        )
                            ->label('Alasan Penolakan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(
        Table $table
    ): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(
                    'kode_kantong'
                )
                    ->label('Kode Kantong')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.pendonor.name'
                )
                    ->label('Pendonor')
                    ->description(
                        fn (
                            KantongDarah $record
                        ): string => $record
                            ->pendaftaran
                            ->nomor_pendaftaran
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'golongan_darah'
                )
                    ->label('Golongan')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            mixed $state
                        ): string => $state
                            instanceof GolonganDarah
                                ? $state->label()
                                : (string) $state
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'rhesus'
                )
                    ->label('Rhesus')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            mixed $state
                        ): string => $state
                            instanceof RhesusDarah
                                ? $state->label()
                                : (string) $state
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'volume_ml'
                )
                    ->label('Volume')
                    ->suffix(' ml')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'status_mutu'
                )
                    ->label('Status Mutu')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusMutuKantongDarah|string $state
                        ): string => self
                            ::statusMutuEnum($state)
                            ->label()
                    )
                    ->color(
                        fn (
                            StatusMutuKantongDarah|string $state
                        ): string => self
                            ::statusMutuEnum($state)
                            ->warna()
                    ),

                Tables\Columns\TextColumn::make(
                    'status'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusKantongDarah|string $state
                        ): string => self
                            ::statusKantongEnum($state)
                            ->label()
                    )
                    ->color(
                        fn (
                            StatusKantongDarah|string $state
                        ): string => self
                            ::statusKantongEnum($state)
                            ->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'kedaluwarsa_pada'
                )
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'lokasi_penyimpanan'
                )
                    ->label('Penyimpanan')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'verifikator.name'
                )
                    ->label('Verifikator')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'created_at'
                )
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'golongan_darah'
                )
                    ->label('Golongan Darah')
                    ->options(
                        GolonganDarah::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'rhesus'
                )
                    ->label('Rhesus')
                    ->options(
                        RhesusDarah::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'status_mutu'
                )
                    ->label('Status Mutu')
                    ->options(
                        StatusMutuKantongDarah::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'status'
                )
                    ->label('Status Kantong')
                    ->options(
                        StatusKantongDarah::options()
                    ),

                Tables\Filters\Filter::make(
                    'mendekati_kedaluwarsa'
                )
                    ->label('Mendekati Kedaluwarsa')
                    ->query(
                        fn (
                            Builder $query
                        ): Builder => $query
                            ->where(
                                'status',
                                StatusKantongDarah
                                    ::Tersedia
                                    ->value
                            )
                            ->whereBetween(
                                'kedaluwarsa_pada',
                                [
                                    now(),
                                    now()->addDays(7),
                                ]
                            )
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => ! in_array(
                            $record->status,
                            [
                                StatusKantongDarah
                                    ::Dipesan,
                                StatusKantongDarah
                                    ::Didistribusikan,
                            ],
                            true
                        )
                    ),

                Tables\Actions\Action::make(
                    'luluskan_mutu'
                )
                    ->label('Luluskan')
                    ->icon(
                        'heroicon-o-check-circle'
                    )
                    ->color('success')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => $record
                            ->dapatDiverifikasi()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Luluskan Pemeriksaan Mutu'
                    )
                    ->modalDescription(
                        'Kantong darah akan masuk ke stok tersedia.'
                    )
                    ->action(
                        function (
                            KantongDarah $record
                        ): void {
                            app(
                                LayananKantongDarah::class
                            )->luluskanMutu(
                                kantong: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Kantong darah berhasil masuk ke stok tersedia.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'gagalkan_mutu'
                )
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => $record
                            ->dapatDiverifikasi()
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            KantongDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananKantongDarah::class
                            )->gagalkanMutu(
                                kantong: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Kantong darah tidak lolos pemeriksaan mutu.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'tandai_rusak'
                )
                    ->label('Tandai Rusak')
                    ->icon(
                        'heroicon-o-exclamation-triangle'
                    )
                    ->color('danger')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => $record
                            ->dapatDitandaiRusak()
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Kerusakan')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            KantongDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananKantongDarah::class
                            )->tandaiRusak(
                                kantong: $record,
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Kantong darah ditandai rusak.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'tandai_kedaluwarsa'
                )
                    ->label('Kedaluwarsa')
                    ->icon(
                        'heroicon-o-clock'
                    )
                    ->color('gray')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => $record->status ===
                            StatusKantongDarah::Tersedia
                            && $record
                                ->sudahKedaluwarsa()
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            KantongDarah $record
                        ): void {
                            app(
                                LayananKantongDarah::class
                            )->tandaiKedaluwarsa(
                                kantong: $record
                            );

                            Notification::make()
                                ->title(
                                    'Kantong darah ditandai kedaluwarsa.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn (
                            KantongDarah $record
                        ): bool => $record->status ===
                            StatusKantongDarah::Menunggu
                    )
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan'),
            ])
            ->bulkActions([])
            ->defaultSort(
                'diambil_pada',
                'desc'
            )
            ->emptyStateHeading(
                'Belum ada kantong darah'
            )
            ->emptyStateDescription(
                'Kantong darah dapat dibuat setelah Pendonor dinyatakan layak.'
            )
            ->emptyStateIcon(
                'heroicon-o-beaker'
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListKantongDarahs::route('/'),

            'create' =>
                Pages\CreateKantongDarah::route(
                    '/create'
                ),

            'view' =>
                Pages\ViewKantongDarah::route(
                    '/{record}'
                ),

            'edit' =>
                Pages\EditKantongDarah::route(
                    '/{record}/edit'
                ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->kode_kantong;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Pendonor' =>
                $record
                    ->pendaftaran
                    ?->pendonor
                    ?->name
                ?? '-',

            'Golongan' =>
                sprintf(
                    '%s%s',
                    $record
                        ->golongan_darah
                        ->label(),
                    $record
                        ->rhesus
                        ->simbol(),
                ),

            'Status' =>
                $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pendaftaran.jadwal',
                'pendaftaran.pendonor.profilPendonor',
                'verifikator',
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    private static function statusKantongEnum(
        StatusKantongDarah|string $status
    ): StatusKantongDarah {
        return $status instanceof
            StatusKantongDarah
                ? $status
                : StatusKantongDarah::from(
                    $status
                );
    }

    private static function statusMutuEnum(
        StatusMutuKantongDarah|string $status
    ): StatusMutuKantongDarah {
        return $status instanceof
            StatusMutuKantongDarah
                ? $status
                : StatusMutuKantongDarah::from(
                    $status
                );
    }
}