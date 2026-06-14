<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Filament\Admin\Resources\ProfilRumahSakitResource\Pages;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfilRumahSakitResource extends Resource
{
    protected static ?string $model = ProfilRumahSakit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Rumah Sakit';

    protected static ?string $modelLabel = 'Rumah Sakit';

    protected static ?string $pluralModelLabel = 'Rumah Sakit';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->description('Pilih akun yang telah memiliki role Rumah Sakit.')
                    ->schema([
                        Forms\Components\Select::make('pengguna_id')
                            ->label('Akun Pengguna')
                            ->relationship(
                                name: 'pengguna',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->role(PeranPengguna::RumahSakit->value),
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
                                table: 'profil_rumah_sakit',
                                column: 'pengguna_id',
                                ignoreRecord: true,
                            )
                            ->disabledOn('edit'),

                        Forms\Components\TextInput::make('kode_rumah_sakit')
                            ->label('Kode Rumah Sakit')
                            ->required()
                            ->maxLength(30)
                            ->unique(
                                table: 'profil_rumah_sakit',
                                column: 'kode_rumah_sakit',
                                ignoreRecord: true,
                            )
                            ->placeholder('HSP-2026-000001'),

                        Forms\Components\Hidden::make('status_verifikasi')
                            ->default(
                                StatusVerifikasiRumahSakit::Menunggu->value
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Identitas Rumah Sakit')
                    ->schema([
                        Forms\Components\TextInput::make('nama_rumah_sakit')
                            ->label('Nama Rumah Sakit')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nomor_izin')
                            ->label('Nomor Izin Operasional')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: 'profil_rumah_sakit',
                                column: 'nomor_izin',
                                ignoreRecord: true,
                            ),

                        Forms\Components\FileUpload::make(
                            'path_dokumen_izin'
                        )
                            ->label('Dokumen Izin Operasional')
                            ->disk('public')
                            ->directory('hospital-documents')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->helperText(
                                'Format PDF, JPG, atau PNG. Maksimal 5 MB.'
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penanggung Jawab')
                    ->schema([
                        Forms\Components\TextInput::make(
                            'nama_penanggung_jawab'
                        )
                            ->label('Nama Penanggung Jawab')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make(
                            'jabatan_penanggung_jawab'
                        )
                            ->label('Jabatan Penanggung Jawab')
                            ->maxLength(150),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat Rumah Sakit')
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

                Forms\Components\Section::make('Koordinat Lokasi')
                    ->description(
                        'Koordinat digunakan untuk integrasi peta digital.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->step('0.0000001')
                            ->placeholder('-6.2852000'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->step('0.0000001')
                            ->placeholder('106.8446000'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Verifikasi')
                    ->visible(
                        fn (Get $get): bool => filled(
                            $get('status_verifikasi')
                        )
                    )
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_verifikasi_label'
                        )
                            ->label('Status Saat Ini')
                            ->content(
                                fn (?ProfilRumahSakit $record): string => $record
                                    ? $record->status_verifikasi->label()
                                    : StatusVerifikasiRumahSakit::Menunggu
                                        ->label()
                            ),

                        Forms\Components\Placeholder::make(
                            'verifikator_label'
                        )
                            ->label('Diverifikasi Oleh')
                            ->content(
                                fn (?ProfilRumahSakit $record): string => $record
                                    ? ($record->verifikator?->name ?? '-')
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'diverifikasi_pada_label'
                        )
                            ->label('Diverifikasi Pada')
                            ->content(
                                fn (?ProfilRumahSakit $record): string => $record
                                    ? (
                                        $record->diverifikasi_pada
                                            ?->format('d M Y H:i')
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Textarea::make(
                            'alasan_penolakan'
                        )
                            ->label('Alasan Penolakan/Penangguhan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->hiddenOn('create'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Akun')
                    ->schema([
                        TextEntry::make('pengguna.name')
                            ->label('Nama Pengguna'),

                        TextEntry::make('pengguna.email')
                            ->label('Email'),

                        TextEntry::make('pengguna.nomor_telepon')
                            ->label('Nomor Telepon')
                            ->placeholder('-'),

                        TextEntry::make('kode_rumah_sakit')
                            ->label('Kode Rumah Sakit')
                            ->copyable(),

                        TextEntry::make('status_verifikasi')
                            ->label('Status Verifikasi')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusVerifikasiRumahSakit|string $state
                                ): string => $state instanceof
                                    StatusVerifikasiRumahSakit
                                        ? $state->label()
                                        : StatusVerifikasiRumahSakit::from(
                                            $state
                                        )->label()
                            )
                            ->color(
                                fn (
                                    StatusVerifikasiRumahSakit|string $state
                                ): string => match (
                                    $state instanceof
                                    StatusVerifikasiRumahSakit
                                        ? $state
                                        : StatusVerifikasiRumahSakit::from(
                                            $state
                                        )
                                ) {
                                    StatusVerifikasiRumahSakit::Menunggu
                                        => 'warning',
                                    StatusVerifikasiRumahSakit::Disetujui
                                        => 'success',
                                    StatusVerifikasiRumahSakit::Ditolak
                                        => 'danger',
                                    StatusVerifikasiRumahSakit::Ditangguhkan
                                        => 'gray',
                                }
                            ),
                    ])
                    ->columns(3),

                InfolistSection::make('Identitas Rumah Sakit')
                    ->schema([
                        TextEntry::make('nama_rumah_sakit')
                            ->label('Nama Rumah Sakit'),

                        TextEntry::make('nomor_izin')
                            ->label('Nomor Izin Operasional'),

                        TextEntry::make('nama_penanggung_jawab')
                            ->label('Nama Penanggung Jawab'),

                        TextEntry::make(
                            'jabatan_penanggung_jawab'
                        )
                            ->label('Jabatan Penanggung Jawab')
                            ->placeholder('-'),

                        TextEntry::make('path_dokumen_izin')
                            ->label('Dokumen Izin')
                            ->formatStateUsing(
                                fn (?string $state): string => filled($state)
                                    ? 'Lihat dokumen'
                                    : 'Belum diunggah'
                            )
                            ->url(
                                fn (
                                    ProfilRumahSakit $record
                                ): ?string => filled(
                                    $record->path_dokumen_izin
                                )
                                    ? asset(
                                        'storage/' . ltrim(
                                            $record->path_dokumen_izin,
                                            '/'
                                        )
                                    )
                                    : null
                            )
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2),

                InfolistSection::make('Alamat dan Lokasi')
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

                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->placeholder('-'),

                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                InfolistSection::make('Riwayat Verifikasi')
                    ->schema([
                        TextEntry::make('verifikator.name')
                            ->label('Diverifikasi Oleh')
                            ->placeholder('-'),

                        TextEntry::make('diverifikasi_pada')
                            ->label('Diverifikasi Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('alasan_penolakan')
                            ->label('Alasan Penolakan/Penangguhan')
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
                Tables\Columns\TextColumn::make('kode_rumah_sakit')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('nama_rumah_sakit')
                    ->label('Nama Rumah Sakit')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('pengguna.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nomor_izin')
                    ->label('Nomor Izin')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('kota')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusVerifikasiRumahSakit|string $state
                        ): string => $state instanceof
                            StatusVerifikasiRumahSakit
                                ? $state->label()
                                : StatusVerifikasiRumahSakit::from(
                                    $state
                                )->label()
                    )
                    ->color(
                        fn (
                            StatusVerifikasiRumahSakit|string $state
                        ): string => match (
                            $state instanceof
                            StatusVerifikasiRumahSakit
                                ? $state
                                : StatusVerifikasiRumahSakit::from($state)
                        ) {
                            StatusVerifikasiRumahSakit::Menunggu
                                => 'warning',
                            StatusVerifikasiRumahSakit::Disetujui
                                => 'success',
                            StatusVerifikasiRumahSakit::Ditolak
                                => 'danger',
                            StatusVerifikasiRumahSakit::Ditangguhkan
                                => 'gray',
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('verifikator.name')
                    ->label('Verifikator')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('diverifikasi_pada')
                    ->label('Diverifikasi Pada')
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
                Tables\Filters\SelectFilter::make(
                    'status_verifikasi'
                )
                    ->label('Status Verifikasi')
                    ->options(
                        StatusVerifikasiRumahSakit::options()
                    ),

                Tables\Filters\SelectFilter::make('kota')
                    ->label('Kota/Kabupaten')
                    ->options(
                        fn (): array => ProfilRumahSakit::query()
                            ->whereNotNull('kota')
                            ->orderBy('kota')
                            ->pluck('kota', 'kota')
                            ->all()
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn (
                            ProfilRumahSakit $record
                        ): bool => $record->status_verifikasi !==
                            StatusVerifikasiRumahSakit::Disetujui
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Rumah Sakit')
                    ->modalDescription(
                        'Rumah sakit akan diaktifkan dan dapat mengajukan permintaan darah.'
                    )
                    ->action(
                        function (ProfilRumahSakit $record): void {
                            DB::transaction(
                                function () use ($record): void {
                                    $record->update([
                                        'status_verifikasi' =>
                                            StatusVerifikasiRumahSakit::Disetujui,
                                        'diverifikasi_oleh' =>
                                            Filament::auth()->id(),
                                        'diverifikasi_pada' => now(),
                                        'alasan_penolakan' => null,
                                    ]);

                                    $record->pengguna()->update([
                                        'status' =>
                                            StatusPengguna::Aktif,
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Rumah sakit berhasil disetujui.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (
                            ProfilRumahSakit $record
                        ): bool => $record->status_verifikasi !==
                            StatusVerifikasiRumahSakit::Ditolak
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan_penolakan'
                        )
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Rumah Sakit')
                    ->action(
                        function (
                            ProfilRumahSakit $record,
                            array $data
                        ): void {
                            DB::transaction(
                                function () use (
                                    $record,
                                    $data
                                ): void {
                                    $record->update([
                                        'status_verifikasi' =>
                                            StatusVerifikasiRumahSakit::Ditolak,
                                        'diverifikasi_oleh' =>
                                            Filament::auth()->id(),
                                        'diverifikasi_pada' => now(),
                                        'alasan_penolakan' =>
                                            $data['alasan_penolakan'],
                                    ]);

                                    $record->pengguna()->update([
                                        'status' =>
                                            StatusPengguna::Ditolak,
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Pendaftaran rumah sakit ditolak.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make('tangguhkan')
                    ->label('Tangguhkan')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(
                        fn (
                            ProfilRumahSakit $record
                        ): bool => $record->status_verifikasi ===
                            StatusVerifikasiRumahSakit::Disetujui
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan_penangguhan'
                        )
                            ->label('Alasan Penangguhan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->action(
                        function (
                            ProfilRumahSakit $record,
                            array $data
                        ): void {
                            DB::transaction(
                                function () use (
                                    $record,
                                    $data
                                ): void {
                                    $record->update([
                                        'status_verifikasi' =>
                                            StatusVerifikasiRumahSakit::Ditangguhkan,
                                        'diverifikasi_oleh' =>
                                            Filament::auth()->id(),
                                        'diverifikasi_pada' => now(),
                                        'alasan_penolakan' =>
                                            $data['alasan_penangguhan'],
                                    ]);

                                    $record->pengguna()->update([
                                        'status' =>
                                            StatusPengguna::Ditangguhkan,
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Akses rumah sakit ditangguhkan.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make('kembalikan_menunggu')
                    ->label('Tinjau Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(
                        fn (
                            ProfilRumahSakit $record
                        ): bool => in_array(
                            $record->status_verifikasi,
                            [
                                StatusVerifikasiRumahSakit::Ditolak,
                                StatusVerifikasiRumahSakit::Ditangguhkan,
                            ],
                            true,
                        )
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (ProfilRumahSakit $record): void {
                            DB::transaction(
                                function () use ($record): void {
                                    $record->update([
                                        'status_verifikasi' =>
                                            StatusVerifikasiRumahSakit::Menunggu,
                                        'diverifikasi_oleh' => null,
                                        'diverifikasi_pada' => null,
                                        'alasan_penolakan' => null,
                                    ]);

                                    $record->pengguna()->update([
                                        'status' =>
                                            StatusPengguna::Menunggu,
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Rumah sakit dikembalikan ke tahap verifikasi.'
                                )
                                ->success()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(
                'Belum ada profil Rumah Sakit'
            )
            ->emptyStateDescription(
                'Tambahkan profil Rumah Sakit atau tunggu registrasi dari frontend.'
            )
            ->emptyStateIcon(
                'heroicon-o-building-office-2'
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfilRumahSakits::route('/'),
            'create' => Pages\CreateProfilRumahSakit::route(
                '/create'
            ),
            'view' => Pages\ViewProfilRumahSakit::route(
                '/{record}'
            ),
            'edit' => Pages\EditProfilRumahSakit::route(
                '/{record}/edit'
            ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->nama_rumah_sakit;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Kode' => $record->kode_rumah_sakit,
            'Kota' => $record->kota,
            'Status' => $record->status_verifikasi->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'pengguna',
                'verifikator',
            ]);
    }
}