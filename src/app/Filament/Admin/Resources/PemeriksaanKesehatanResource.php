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
                    'Data Pendonor'
                )
                    ->description(
                        'Pemeriksaan hanya dapat dilakukan setelah Pendonor tercatat hadir.'
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
                                                ::Hadir
                                                ->value
                                        )
                                        ->whereDoesntHave(
                                            'pemeriksaanKesehatan'
                                        )
                                        ->with([
                                            'pendonor',
                                            'jadwal',
                                        ])
                                        ->orderByDesc(
                                            'hadir_pada'
                                        ),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (
                                    PendaftaranDonor $record
                                ): string => sprintf(
                                    '%s — %s — %s',
                                    $record
                                        ->nomor_pendaftaran,
                                    $record
                                        ->pendonor
                                        ->name,
                                    $record
                                        ->jadwal
                                        ->judul,
                                )
                            )
                            ->searchable([
                                'nomor_pendaftaran',
                            ])
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\DateTimePicker::make(
                            'diperiksa_pada'
                        )
                            ->label('Waktu Pemeriksaan')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Pemeriksaan Fisik'
                )
                    ->schema([
                        Forms\Components\TextInput::make(
                            'berat_badan_kg'
                        )
                            ->label('Berat Badan')
                            ->required()
                            ->numeric()
                            ->minValue(20)
                            ->maxValue(300)
                            ->step('0.01')
                            ->suffix('kg'),

                        Forms\Components\TextInput::make(
                            'tekanan_sistolik'
                        )
                            ->label('Tekanan Sistolik')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(50)
                            ->maxValue(300)
                            ->suffix('mmHg'),

                        Forms\Components\TextInput::make(
                            'tekanan_diastolik'
                        )
                            ->label('Tekanan Diastolik')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(30)
                            ->maxValue(200)
                            ->suffix('mmHg'),

                        Forms\Components\TextInput::make(
                            'kadar_hemoglobin'
                        )
                            ->label('Kadar Hemoglobin')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->step('0.01')
                            ->suffix('g/dL'),

                        Forms\Components\TextInput::make(
                            'suhu_tubuh'
                        )
                            ->label('Suhu Tubuh')
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(45)
                            ->step('0.01')
                            ->suffix('°C'),

                        Forms\Components\TextInput::make(
                            'denyut_nadi'
                        )
                            ->label('Denyut Nadi')
                            ->numeric()
                            ->integer()
                            ->minValue(30)
                            ->maxValue(220)
                            ->suffix('bpm'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make(
                    'Golongan Darah'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->options(
                                GolonganDarah::options()
                            )
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Select::make(
                            'rhesus'
                        )
                            ->label('Rhesus')
                            ->options(
                                RhesusDarah::options()
                            )
                            ->native(false)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Hasil Pemeriksaan'
                )
                    ->description(
                        'Keputusan kelayakan ditentukan oleh Petugas, bukan dihitung otomatis oleh sistem.'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'status_kelayakan'
                        )
                            ->label('Status Kelayakan')
                            ->options(
                                StatusKelayakanDonor::options()
                            )
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Textarea::make(
                            'alasan_tidak_layak'
                        )
                            ->label('Alasan Tidak Layak')
                            ->visible(
                                fn (
                                    Get $get
                                ): bool => $get(
                                    'status_kelayakan'
                                ) ===
                                    StatusKelayakanDonor
                                        ::TidakLayak
                                        ->value
                            )
                            ->required(
                                fn (
                                    Get $get
                                ): bool => $get(
                                    'status_kelayakan'
                                ) ===
                                    StatusKelayakanDonor
                                        ::TidakLayak
                                        ->value
                            )
                            ->minLength(5)
                            ->maxLength(2000)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make(
                            'catatan_medis'
                        )
                            ->label('Catatan Pemeriksaan')
                            ->maxLength(3000)
                            ->rows(4)
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
                    'Data Pendonor'
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

                        TextEntry::make(
                            'pemeriksa.name'
                        )
                            ->label('Diperiksa Oleh'),

                        TextEntry::make(
                            'diperiksa_pada'
                        )
                            ->label('Diperiksa Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(3),

                InfolistSection::make(
                    'Hasil Pemeriksaan Fisik'
                )
                    ->schema([
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

                        TextEntry::make('suhu_tubuh')
                            ->label('Suhu Tubuh')
                            ->suffix(' °C')
                            ->placeholder('-'),

                        TextEntry::make('denyut_nadi')
                            ->label('Denyut Nadi')
                            ->suffix(' bpm')
                            ->placeholder('-'),

                        TextEntry::make(
                            'golongan_darah'
                        )
                            ->label('Golongan Darah')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    mixed $state
                                ): string => self
                                    ::labelGolonganDarah(
                                        $state
                                    )
                            )
                            ->placeholder('-'),

                        TextEntry::make('rhesus')
                            ->label('Rhesus')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    mixed $state
                                ): string => self
                                    ::labelRhesus(
                                        $state
                                    )
                            )
                            ->placeholder('-'),
                    ])
                    ->columns(4),

                InfolistSection::make(
                    'Keputusan Pemeriksaan'
                )
                    ->schema([
                        TextEntry::make(
                            'status_kelayakan'
                        )
                            ->label('Status Kelayakan')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusKelayakanDonor|string $state
                                ): string => self
                                    ::statusKelayakanEnum(
                                        $state
                                    )
                                    ->label()
                            )
                            ->color(
                                fn (
                                    StatusKelayakanDonor|string $state
                                ): string => self
                                    ::statusKelayakanEnum(
                                        $state
                                    )
                                    ->warna()
                            ),

                        TextEntry::make(
                            'alasan_tidak_layak'
                        )
                            ->label('Alasan Tidak Layak')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'catatan_medis'
                        )
                            ->label('Catatan Pemeriksaan')
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
                    'pendaftaran.nomor_pendaftaran'
                )
                    ->label('Nomor Pendaftaran')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.pendonor.name'
                )
                    ->label('Pendonor')
                    ->description(
                        fn (
                            PemeriksaanKesehatan $record
                        ): string => $record
                            ->pendaftaran
                            ->pendonor
                            ->profilPendonor
                            ?->kode_pendonor
                            ?? '-'
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran.jadwal.judul'
                )
                    ->label('Jadwal')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'berat_badan_kg'
                )
                    ->label('Berat')
                    ->suffix(' kg')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'tekanan_sistolik'
                )
                    ->label('Tekanan Darah')
                    ->formatStateUsing(
                        fn (
                            mixed $state,
                            PemeriksaanKesehatan $record
                        ): string => sprintf(
                            '%s/%s mmHg',
                            $record->tekanan_sistolik,
                            $record
                                ->tekanan_diastolik,
                        )
                    ),

                Tables\Columns\TextColumn::make(
                    'golongan_darah'
                )
                    ->label('Golongan')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            mixed $state
                        ): string => self
                            ::labelGolonganDarah(
                                $state
                            )
                    )
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make(
                    'status_kelayakan'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusKelayakanDonor|string $state
                        ): string => self
                            ::statusKelayakanEnum(
                                $state
                            )
                            ->label()
                    )
                    ->color(
                        fn (
                            StatusKelayakanDonor|string $state
                        ): string => self
                            ::statusKelayakanEnum(
                                $state
                            )
                            ->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'pemeriksa.name'
                )
                    ->label('Petugas')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'diperiksa_pada'
                )
                    ->label('Diperiksa')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'status_kelayakan'
                )
                    ->label('Status Kelayakan')
                    ->options(
                        StatusKelayakanDonor::options()
                    ),

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
                    'diperiksa_oleh'
                )
                    ->label('Petugas Pemeriksa')
                    ->relationship(
                        'pemeriksa',
                        'name'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
            ])
            ->bulkActions([])
            ->defaultSort(
                'diperiksa_pada',
                'desc'
            )
            ->emptyStateHeading(
                'Belum ada pemeriksaan kesehatan'
            )
            ->emptyStateDescription(
                'Catat kehadiran Pendonor sebelum membuat pemeriksaan kesehatan.'
            )
            ->emptyStateIcon(
                'heroicon-o-heart'
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
                Pages\ListPemeriksaanKesehatans
                    ::route('/'),

            'create' =>
                Pages\CreatePemeriksaanKesehatan
                    ::route('/create'),

            'view' =>
                Pages\ViewPemeriksaanKesehatan
                    ::route('/{record}'),

            'edit' =>
                Pages\EditPemeriksaanKesehatan
                    ::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record
            ->pendaftaran
            ?->pendonor
            ?->name
            ?? 'Pemeriksaan Kesehatan';
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Nomor Pendaftaran' =>
                $record
                    ->pendaftaran
                    ?->nomor_pendaftaran
                ?? '-',

            'Status' =>
                $record
                    ->status_kelayakan
                    ->label(),

            'Diperiksa Pada' =>
                $record
                    ->diperiksa_pada
                    ?->format('d M Y H:i')
                ?? '-',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pendaftaran.jadwal',
                'pendaftaran.pendonor.profilPendonor',
                'pemeriksa',
            ]);
    }

    private static function statusKelayakanEnum(
        StatusKelayakanDonor|string $status
    ): StatusKelayakanDonor {
        return $status instanceof
            StatusKelayakanDonor
                ? $status
                : StatusKelayakanDonor::from(
                    $status
                );
    }

    private static function labelGolonganDarah(
        mixed $state
    ): string {
        if ($state instanceof GolonganDarah) {
            return $state->label();
        }

        if (blank($state)) {
            return '-';
        }

        return GolonganDarah::tryFrom(
            (string) $state
        )?->label() ?? (string) $state;
    }

    private static function labelRhesus(
        mixed $state
    ): string {
        if ($state instanceof RhesusDarah) {
            return $state->label();
        }

        if (blank($state)) {
            return '-';
        }

        return RhesusDarah::tryFrom(
            (string) $state
        )?->label() ?? (string) $state;
    }
}