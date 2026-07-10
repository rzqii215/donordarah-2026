<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusKelayakanDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PemeriksaanKesehatanResource\Pages;
use App\Models\PemeriksaanKesehatan;
use App\Models\PendaftaranDonor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanKesehatanResource extends Resource
{
    protected static ?string $model =
        PemeriksaanKesehatan::class;

    protected static ?string $navigationIcon =
        'heroicon-o-heart';

    protected static ?string $navigationLabel =
        'Pemeriksaan Kesehatan';

    protected static ?string $modelLabel =
        'Pemeriksaan Kesehatan';

    protected static ?string $pluralModelLabel =
        'Pemeriksaan Kesehatan';

    protected static ?string $navigationGroup =
        'Manajemen Donor';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Data Pendaftaran Donor'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'pendaftaran_donor_id'
                        )
                            ->label('Pendaftaran Donor')
                            ->options(
                                function (
                                    ?PemeriksaanKesehatan $record
                                ): array {
                                    $query =
                                        PendaftaranDonor::query()
                                            ->with([
                                                'pendonor',
                                                'jadwal',
                                            ]);

                                    if (
                                        $record instanceof
                                            PemeriksaanKesehatan
                                        && $record
                                            ->pendaftaran_donor_id
                                    ) {
                                        $query->whereKey(
                                            $record
                                                ->pendaftaran_donor_id
                                        );
                                    } else {
                                        $query
                                            ->where(
                                                'status',
                                                StatusPendaftaranDonor
                                                    ::Hadir
                                                    ->value
                                            )
                                            ->whereDoesntHave(
                                                'pemeriksaanKesehatan'
                                            );
                                    }

                                    return $query
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(
                                            fn (
                                                PendaftaranDonor $pendaftaran
                                            ): array => [
                                                $pendaftaran->id =>
                                                    self::labelPendaftaran(
                                                        $pendaftaran
                                                    ),
                                            ]
                                        )
                                        ->all();
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(
                                fn (
                                    ?PemeriksaanKesehatan $record
                                ): bool => $record instanceof
                                    PemeriksaanKesehatan
                            )
                            ->dehydrated(true),
                    ]),

                Forms\Components\Section::make(
                    'Pemeriksaan Fisik'
                )
                    ->schema([
                        Forms\Components\TextInput::make(
                            'berat_badan_kg'
                        )
                            ->label('Berat Badan')
                            ->numeric()
                            ->suffix('kg')
                            ->minValue(30)
                            ->maxValue(250)
                            ->required(),

                        Forms\Components\TextInput::make(
                            'tekanan_sistolik'
                        )
                            ->label('Tekanan Sistolik')
                            ->numeric()
                            ->suffix('mmHg')
                            ->minValue(70)
                            ->maxValue(250)
                            ->required(),

                        Forms\Components\TextInput::make(
                            'tekanan_diastolik'
                        )
                            ->label('Tekanan Diastolik')
                            ->numeric()
                            ->suffix('mmHg')
                            ->minValue(40)
                            ->maxValue(150)
                            ->required(),

                        Forms\Components\TextInput::make(
                            'kadar_hemoglobin'
                        )
                            ->label('Hemoglobin')
                            ->numeric()
                            ->step('0.1')
                            ->suffix('g/dL')
                            ->minValue(5)
                            ->maxValue(25),

                        Forms\Components\TextInput::make(
                            'suhu_tubuh'
                        )
                            ->label('Suhu Tubuh')
                            ->numeric()
                            ->step('0.1')
                            ->suffix('°C')
                            ->minValue(30)
                            ->maxValue(45),

                        Forms\Components\TextInput::make(
                            'denyut_nadi'
                        )
                            ->label('Denyut Nadi')
                            ->numeric()
                            ->suffix('bpm')
                            ->minValue(30)
                            ->maxValue(220),
                    ])
                    ->columns(3),

                Forms\Components\Section::make(
                    'Hasil Pemeriksaan'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->options(
                                self::opsiEnum(
                                    GolonganDarah::class
                                )
                            )
                            ->searchable(),

                        Forms\Components\Select::make(
                            'rhesus'
                        )
                            ->label('Rhesus')
                            ->options(
                                self::opsiEnum(
                                    RhesusDarah::class
                                )
                            )
                            ->searchable(),

                        Forms\Components\Select::make(
                            'status_kelayakan'
                        )
                            ->label('Status Kelayakan')
                            ->options(
                                self::opsiStatusKelayakan()
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                function (
                                    Set $set,
                                    mixed $state
                                ): void {
                                    if (
                                        ! self
                                            ::statusKelayakanTidakLayak(
                                                $state
                                            )
                                    ) {
                                        $set(
                                            'alasan_tidak_layak',
                                            null
                                        );
                                    }
                                }
                            ),

                        Forms\Components\DateTimePicker::make(
                            'diperiksa_pada'
                        )
                            ->label('Diperiksa Pada')
                            ->native(false)
                            ->seconds(false)
                            ->default(now())
                            ->required(),

                        Forms\Components\Textarea::make(
                            'alasan_tidak_layak'
                        )
                            ->label('Alasan Tidak Layak')
                            ->rows(3)
                            ->maxLength(1000)
                            ->visible(
                                fn (Get $get): bool => self
                                    ::statusKelayakanTidakLayak(
                                        $get('status_kelayakan')
                                    )
                            )
                            ->required(
                                fn (Get $get): bool => self
                                    ::statusKelayakanTidakLayak(
                                        $get('status_kelayakan')
                                    )
                            )
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make(
                            'catatan_medis'
                        )
                            ->label('Catatan Medis')
                            ->rows(4)
                            ->maxLength(2000)
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
                    'Informasi Pemeriksaan'
                )
                    ->schema([
                        TextEntry::make(
                            'pendaftaran.nomor_pendaftaran'
                        )
                            ->label('Nomor Pendaftaran')
                            ->copyable(),

                        TextEntry::make(
                            'pendaftaran.status'
                        )
                            ->label('Status Pendaftaran')
                            ->badge()
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    self::labelDariEnum($state)
                            )
                            ->color(
                                fn (mixed $state): string =>
                                    self::warnaStatusPendaftaran(
                                        $state
                                    )
                            ),

                        TextEntry::make(
                            'pendaftaran.pendonor.name'
                        )
                            ->label('Pendonor'),

                        TextEntry::make(
                            'pendaftaran.jadwal.judul'
                        )
                            ->label('Jadwal Donor'),

                        TextEntry::make(
                            'pemeriksa.name'
                        )
                            ->label('Diperiksa Oleh')
                            ->placeholder('-'),

                        TextEntry::make(
                            'diperiksa_pada'
                        )
                            ->label('Diperiksa Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                InfolistSection::make(
                    'Hasil Pemeriksaan'
                )
                    ->schema([
                        TextEntry::make(
                            'status_kelayakan'
                        )
                            ->label('Status Kelayakan')
                            ->badge()
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    self::labelStatusKelayakan(
                                        $state
                                    )
                            )
                            ->color(
                                fn (mixed $state): string =>
                                    self::warnaStatusKelayakan(
                                        $state
                                    )
                            ),

                        TextEntry::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    self::labelDariEnum($state)
                            )
                            ->placeholder('-'),

                        TextEntry::make('rhesus')
                            ->label('Rhesus')
                            ->formatStateUsing(
                                fn (mixed $state): string =>
                                    self::labelDariEnum($state)
                            )
                            ->placeholder('-'),

                        TextEntry::make(
                            'berat_badan_kg'
                        )
                            ->label('Berat Badan')
                            ->suffix(' kg'),

                        TextEntry::make(
                            'tekanan_sistolik'
                        )
                            ->label('Tekanan Sistolik')
                            ->suffix(' mmHg'),

                        TextEntry::make(
                            'tekanan_diastolik'
                        )
                            ->label('Tekanan Diastolik')
                            ->suffix(' mmHg'),

                        TextEntry::make(
                            'kadar_hemoglobin'
                        )
                            ->label('Hemoglobin')
                            ->suffix(' g/dL')
                            ->placeholder('-'),

                        TextEntry::make(
                            'suhu_tubuh'
                        )
                            ->label('Suhu Tubuh')
                            ->suffix(' °C')
                            ->placeholder('-'),

                        TextEntry::make(
                            'denyut_nadi'
                        )
                            ->label('Denyut Nadi')
                            ->suffix(' bpm')
                            ->placeholder('-'),

                        TextEntry::make(
                            'alasan_tidak_layak'
                        )
                            ->label('Alasan Tidak Layak')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'catatan_medis'
                        )
                            ->label('Catatan Medis')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(
        Table $table
    ): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(
                    'pendaftaran.nomor_pendaftaran'
                )
                    ->label('Nomor Pendaftaran')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.pendonor.name'
                )
                    ->label('Pendonor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.jadwal.judul'
                )
                    ->label('Jadwal Donor')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'status_kelayakan'
                )
                    ->label('Kelayakan')
                    ->badge()
                    ->formatStateUsing(
                        fn (mixed $state): string =>
                            self::labelStatusKelayakan($state)
                    )
                    ->color(
                        fn (mixed $state): string =>
                            self::warnaStatusKelayakan(
                                $state
                            )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.status'
                )
                    ->label('Status Pendaftaran')
                    ->badge()
                    ->formatStateUsing(
                        fn (mixed $state): string =>
                            self::labelDariEnum($state)
                    )
                    ->color(
                        fn (mixed $state): string =>
                            self::warnaStatusPendaftaran(
                                $state
                            )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'berat_badan_kg'
                )
                    ->label('BB')
                    ->suffix(' kg')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'tekanan_sistolik'
                )
                    ->label('Sistolik')
                    ->suffix(' mmHg')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'tekanan_diastolik'
                )
                    ->label('Diastolik')
                    ->suffix(' mmHg')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'pemeriksa.name'
                )
                    ->label('Petugas')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'diperiksa_pada'
                )
                    ->label('Diperiksa Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'status_kelayakan'
                )
                    ->label('Status Kelayakan')
                    ->options(
                        self::opsiStatusKelayakan()
                    ),

                Tables\Filters\Filter::make(
                    'masih_bisa_diubah'
                )
                    ->label('Masih Bisa Diubah')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->whereHas(
                                'pendaftaran',
                                fn (
                                    Builder $query
                                ): Builder => $query
                                    ->where(
                                        'status',
                                        StatusPendaftaranDonor
                                            ::Hadir
                                            ->value
                                    )
                                    ->whereDoesntHave(
                                        'kantongDarah'
                                    )
                            )
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn (
                            PemeriksaanKesehatan $record
                        ): bool => self::bolehDiubah(
                            $record
                        )
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('diperiksa_pada', 'desc')
            ->emptyStateHeading(
                'Belum ada pemeriksaan kesehatan'
            )
            ->emptyStateDescription(
                'Pemeriksaan dibuat setelah pendonor hadir pada jadwal donor.'
            )
            ->emptyStateIcon('heroicon-o-heart');
    }

    public static function canEdit(
        Model $record
    ): bool {
        return $record instanceof PemeriksaanKesehatan
            && self::bolehDiubah($record);
    }

    public static function canDelete(
        Model $record
    ): bool {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(
        Model $record
    ): bool {
        return false;
    }

    public static function canForceDeleteAny(): bool
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
            'index' =>
                Pages\ListPemeriksaanKesehatans::route('/'),

            'create' =>
                Pages\CreatePemeriksaanKesehatan::route(
                    '/create'
                ),

            'view' =>
                Pages\ViewPemeriksaanKesehatan::route(
                    '/{record}'
                ),

            'edit' =>
                Pages\EditPemeriksaanKesehatan::route(
                    '/{record}/edit'
                ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record
            ->pendaftaran
            ?->nomor_pendaftaran
            ?? 'Pemeriksaan #' . $record->getKey();
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Pendonor' =>
                $record->pendaftaran?->pendonor?->name
                ?? '-',

            'Status' =>
                self::labelStatusKelayakan(
                    $record->status_kelayakan
                ),

            'Diperiksa Pada' =>
                $record->diperiksa_pada
                    ?->format('d M Y H:i')
                ?? '-',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pendaftaran.pendonor.profilPendonor',
                'pendaftaran.jadwal.lokasi',
                'pendaftaran.kantongDarah',
                'pemeriksa',
            ]);
    }

    private static function bolehDiubah(
        PemeriksaanKesehatan $record
    ): bool {
        $pendaftaran = $record->pendaftaran;

        if ($pendaftaran === null) {
            return false;
        }

        if (
            $pendaftaran->relationLoaded('kantongDarah')
            && $pendaftaran->kantongDarah !== null
        ) {
            return false;
        }

        if (
            ! $pendaftaran->relationLoaded('kantongDarah')
            && $pendaftaran->kantongDarah()->exists()
        ) {
            return false;
        }

        $status =
            $pendaftaran->status instanceof StatusPendaftaranDonor
                ? $pendaftaran->status
                : StatusPendaftaranDonor::tryFrom(
                    (string) $pendaftaran->status
                );

        return $status === StatusPendaftaranDonor::Hadir;
    }

    private static function labelPendaftaran(
        PendaftaranDonor $pendaftaran
    ): string {
        return sprintf(
            '%s — %s — %s',
            $pendaftaran->nomor_pendaftaran,
            $pendaftaran->pendonor?->name ?? 'Pendonor',
            $pendaftaran->jadwal?->judul ?? 'Jadwal Donor'
        );
    }

    /**
     * @param class-string $enumClass
     *
     * @return array<string, string>
     */
    private static function opsiEnum(
        string $enumClass
    ): array {
        $options = [];

        foreach ($enumClass::cases() as $case) {
            $options[$case->value] =
                self::labelDariEnum($case);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function opsiStatusKelayakan(): array
    {
        return [
            StatusKelayakanDonor::Layak->value =>
                'Layak Donor',

            StatusKelayakanDonor::TidakLayak->value =>
                'Tidak Layak Donor',
        ];
    }

    private static function statusKelayakanTidakLayak(
        mixed $state
    ): bool {
        $status = self::statusKelayakanEnum($state);

        return $status === StatusKelayakanDonor::TidakLayak;
    }

    private static function statusKelayakanEnum(
        mixed $state
    ): ?StatusKelayakanDonor {
        if ($state instanceof StatusKelayakanDonor) {
            return $state;
        }

        if (blank($state)) {
            return null;
        }

        return StatusKelayakanDonor::tryFrom(
            (string) $state
        );
    }

    private static function labelStatusKelayakan(
        mixed $state
    ): string {
        $status = self::statusKelayakanEnum($state);

        return match ($status) {
            StatusKelayakanDonor::Layak =>
                'Layak Donor',

            StatusKelayakanDonor::TidakLayak =>
                'Tidak Layak Donor',

            default => '-',
        };
    }

    private static function warnaStatusKelayakan(
        mixed $state
    ): string {
        $status = self::statusKelayakanEnum($state);

        return match ($status) {
            StatusKelayakanDonor::Layak =>
                'success',

            StatusKelayakanDonor::TidakLayak =>
                'danger',

            default => 'gray',
        };
    }

    private static function warnaStatusPendaftaran(
        mixed $state
    ): string {
        $status =
            $state instanceof StatusPendaftaranDonor
                ? $state
                : StatusPendaftaranDonor::tryFrom(
                    (string) $state
                );

        if (
            $status !== null
            && method_exists($status, 'warna')
        ) {
            return $status->warna();
        }

        return 'gray';
    }

    private static function labelDariEnum(
        mixed $state
    ): string {
        if (blank($state)) {
            return '-';
        }

        if (
            is_object($state)
            && method_exists($state, 'label')
        ) {
            return $state->label();
        }

        if ($state instanceof \BackedEnum) {
            return (string) $state->value;
        }

        return (string) $state;
    }
}