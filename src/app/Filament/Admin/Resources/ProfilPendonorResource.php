<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\PeranPengguna;
use App\Enums\RhesusDarah;
use App\Filament\Admin\Resources\ProfilPendonorResource\Pages;
use App\Models\ProfilPendonor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProfilPendonorResource extends Resource
{
    protected static ?string $model = ProfilPendonor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Profil Pendonor';

    protected static ?string $modelLabel = 'Profil Pendonor';

    protected static ?string $pluralModelLabel = 'Profil Pendonor';

    protected static ?string $navigationGroup = 'Manajemen Donor';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description(
                        'Pilih akun pengguna yang telah memiliki role Pendonor.'
                    )
                    ->schema([
                        Forms\Components\Select::make('pengguna_id')
                            ->label('Akun Pendonor')
                            ->relationship(
                                name: 'pengguna',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->role(PeranPengguna::Pendonor->value),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (User $record): string => sprintf(
                                    '%s (%s)',
                                    $record->name,
                                    $record->email,
                                )
                            )
                            ->searchable([
                                'name',
                                'email',
                            ])
                            ->preload()
                            ->required()
                            ->unique(
                                table: 'profil_pendonor',
                                column: 'pengguna_id',
                                ignoreRecord: true,
                            )
                            ->disabledOn('edit'),

                        Forms\Components\TextInput::make('kode_pendonor')
                            ->label('Kode Pendonor')
                            ->required()
                            ->maxLength(30)
                            ->unique(
                                table: 'profil_pendonor',
                                column: 'kode_pendonor',
                                ignoreRecord: true,
                            )
                            ->placeholder('DNR-2026-000001'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()),

                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options(JenisKelamin::options())
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->options(GolonganDarah::options())
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Select::make('rhesus')
                            ->label('Rhesus')
                            ->options(RhesusDarah::options())
                            ->native(false)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make(
                            'terakhir_donor_pada'
                        )
                            ->label('Terakhir Donor')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->maxDate(now())
                            ->nullable(),

                        Forms\Components\Toggle::make(
                            'bersedia_dihubungi'
                        )
                            ->label('Bersedia Dihubungi')
                            ->helperText(
                                'Pendonor bersedia menerima informasi dan pengingat kegiatan donor.'
                            )
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat Pendonor')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('provinsi')
                            ->label('Provinsi')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('kota')
                            ->label('Kota/Kabupaten')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('kecamatan')
                            ->label('Kecamatan')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('kode_pos')
                            ->label('Kode Pos')
                            ->maxLength(10),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kontak Darurat')
                    ->schema([
                        Forms\Components\TextInput::make(
                            'nama_kontak_darurat'
                        )
                            ->label('Nama Kontak Darurat')
                            ->maxLength(255),

                        Forms\Components\TextInput::make(
                            'telepon_kontak_darurat'
                        )
                            ->label('Nomor Telepon Kontak Darurat')
                            ->tel()
                            ->maxLength(30),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Akun')
                    ->schema([
                        TextEntry::make('pengguna.name')
                            ->label('Nama Pendonor'),

                        TextEntry::make('pengguna.email')
                            ->label('Email'),

                        TextEntry::make('pengguna.nomor_telepon')
                            ->label('Nomor Telepon')
                            ->placeholder('-'),

                        TextEntry::make('kode_pendonor')
                            ->label('Kode Pendonor')
                            ->copyable(),
                    ])
                    ->columns(2),

                InfolistSection::make('Data Pribadi')
                    ->schema([
                        TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date('d M Y'),

                        TextEntry::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(
                                fn (mixed $state): string => self::labelJenisKelamin(
                                    $state
                                )
                            ),

                        TextEntry::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(
                                fn (mixed $state): string => self::labelGolonganDarah(
                                    $state
                                )
                            ),

                        TextEntry::make('rhesus')
                            ->label('Rhesus')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(
                                fn (mixed $state): string => self::labelRhesus(
                                    $state
                                )
                            ),

                        TextEntry::make('terakhir_donor_pada')
                            ->label('Terakhir Donor')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum pernah donor'),

                        IconEntry::make('bersedia_dihubungi')
                            ->label('Bersedia Dihubungi')
                            ->boolean(),
                    ])
                    ->columns(3),

                InfolistSection::make('Alamat')
                    ->schema([
                        TextEntry::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),

                        TextEntry::make('provinsi')
                            ->label('Provinsi'),

                        TextEntry::make('kota')
                            ->label('Kota/Kabupaten'),

                        TextEntry::make('kecamatan')
                            ->label('Kecamatan')
                            ->placeholder('-'),

                        TextEntry::make('kode_pos')
                            ->label('Kode Pos')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                InfolistSection::make('Kontak Darurat')
                    ->schema([
                        TextEntry::make('nama_kontak_darurat')
                            ->label('Nama Kontak Darurat')
                            ->placeholder('-'),

                        TextEntry::make('telepon_kontak_darurat')
                            ->label('Nomor Telepon Kontak Darurat')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_pendonor')
                    ->label('Kode Pendonor')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('pengguna.name')
                    ->label('Nama Pendonor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pengguna.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(
                        fn (mixed $state): string => self::labelJenisKelamin(
                            $state
                        )
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(
                        fn (mixed $state): string => self::labelGolonganDarah(
                            $state
                        )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('rhesus')
                    ->label('Rhesus')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(
                        fn (mixed $state): string => self::labelRhesus(
                            $state
                        )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('kota')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('bersedia_dihubungi')
                    ->label('Bersedia Dihubungi')
                    ->boolean(),

                Tables\Columns\TextColumn::make('terakhir_donor_pada')
                    ->label('Terakhir Donor')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options(JenisKelamin::options()),

                Tables\Filters\SelectFilter::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->options(GolonganDarah::options()),

                Tables\Filters\SelectFilter::make('rhesus')
                    ->label('Rhesus')
                    ->options(RhesusDarah::options()),

                Tables\Filters\SelectFilter::make('kota')
                    ->label('Kota/Kabupaten')
                    ->options(
                        fn (): array => ProfilPendonor::query()
                            ->whereNotNull('kota')
                            ->orderBy('kota')
                            ->pluck('kota', 'kota')
                            ->all()
                    )
                    ->searchable(),

                Tables\Filters\TernaryFilter::make(
                    'bersedia_dihubungi'
                )
                    ->label('Bersedia Dihubungi')
                    ->placeholder('Semua Pendonor')
                    ->trueLabel('Bersedia')
                    ->falseLabel('Tidak Bersedia'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada profil Pendonor')
            ->emptyStateDescription(
                'Tambahkan profil Pendonor untuk mulai mengelola data donor darah.'
            )
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfilPendonors::route('/'),
            'create' => Pages\CreateProfilPendonor::route('/create'),
            'view' => Pages\ViewProfilPendonor::route('/{record}'),
            'edit' => Pages\EditProfilPendonor::route(
                '/{record}/edit'
            ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->pengguna?->name
            ?? $record->kode_pendonor;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Kode Pendonor' => $record->kode_pendonor,
            'Golongan Darah' => self::labelGolonganDarah(
                $record->golongan_darah
            ),
            'Rhesus' => self::labelRhesus(
                $record->rhesus
            ),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('pengguna');
    }

    private static function labelJenisKelamin(
        mixed $state
    ): string {
        if ($state instanceof JenisKelamin) {
            return $state->label();
        }

        if (blank($state)) {
            return '-';
        }

        return JenisKelamin::tryFrom((string) $state)?->label()
            ?? (string) $state;
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

        return GolonganDarah::tryFrom((string) $state)?->label()
            ?? (string) $state;
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

        return RhesusDarah::tryFrom((string) $state)?->label()
            ?? (string) $state;
    }
}