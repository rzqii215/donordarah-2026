<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusPengguna;
use App\Enums\StatusPermintaanDarah;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Filament\Admin\Resources\PermintaanDarahResource\Pages;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Services\LayananPermintaanDarah;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
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

class PermintaanDarahResource extends Resource
{
    protected static ?string $model =
        PermintaanDarah::class;

    protected static ?string $navigationIcon =
        'heroicon-o-document-text';

    protected static ?string $navigationLabel =
        'Permintaan Darah';

    protected static ?string $modelLabel =
        'Permintaan Darah';

    protected static ?string $pluralModelLabel =
        'Permintaan Darah';

    protected static ?string $navigationGroup =
        'Permintaan dan Distribusi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Rumah Sakit Pemohon'
                )
                    ->schema([
                        Forms\Components\Select::make(
                            'profil_rumah_sakit_id'
                        )
                            ->label('Rumah Sakit')
                            ->relationship(
                                name: 'rumahSakit',
                                titleAttribute:
                                    'nama_rumah_sakit',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder => $query
                                        ->where(
                                            'status_verifikasi',
                                            StatusVerifikasiRumahSakit
                                                ::Disetujui
                                                ->value
                                        )
                                        ->whereHas(
                                            'pengguna',
                                            fn (
                                                Builder $pengguna
                                            ): Builder => $pengguna
                                                ->where(
                                                    'status',
                                                    StatusPengguna
                                                        ::Aktif
                                                        ->value
                                                )
                                        )
                                        ->orderBy(
                                            'nama_rumah_sakit'
                                        ),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (
                                    ProfilRumahSakit $record
                                ): string => sprintf(
                                    '%s (%s)',
                                    $record
                                        ->nama_rumah_sakit,
                                    $record
                                        ->kode_rumah_sakit,
                                )
                            )
                            ->searchable([
                                'nama_rumah_sakit',
                                'kode_rumah_sakit',
                                'nomor_izin',
                            ])
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Placeholder::make(
                            'nomor_permintaan_info'
                        )
                            ->label('Nomor Permintaan')
                            ->content(
                                fn (
                                    ?PermintaanDarah $record
                                ): string => $record
                                    ? $record
                                        ->nomor_permintaan
                                    : 'Dibuat otomatis setelah disimpan'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Informasi Pasien'
                )
                    ->description(
                        'Gunakan kode atau referensi pasien internal, bukan data medis lengkap.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make(
                            'referensi_pasien'
                        )
                            ->label('Referensi Pasien')
                            ->required()
                            ->maxLength(100)
                            ->placeholder(
                                'Contoh: PSN-2026-0001'
                            ),

                        Forms\Components\TextInput::make(
                            'nama_dokter'
                        )
                            ->label('Nama Dokter')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(
                                'Contoh: dr. Ahmad Pratama'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Kebutuhan Darah'
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
                            ->native(false),

                        Forms\Components\Select::make(
                            'rhesus'
                        )
                            ->label('Rhesus')
                            ->options(
                                RhesusDarah::options()
                            )
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make(
                            'jumlah_kantong'
                        )
                            ->label('Jumlah Kantong')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('kantong'),

                        Forms\Components\Select::make(
                            'tingkat_urgensi'
                        )
                            ->label('Tingkat Urgensi')
                            ->options(
                                TingkatUrgensiPermintaanDarah
                                    ::options()
                            )
                            ->default(
                                TingkatUrgensiPermintaanDarah
                                    ::Normal
                                    ->value
                            )
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make(
                            'dibutuhkan_pada'
                        )
                            ->label('Dibutuhkan Pada')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i'),

                        Forms\Components\FileUpload::make(
                            'path_dokumen_permintaan'
                        )
                            ->label('Dokumen Permintaan')
                            ->disk('public')
                            ->directory(
                                'dokumen-permintaan-darah'
                            )
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            ->helperText(
                                'Format PDF, JPG, atau PNG. Maksimal 5 MB.'
                            ),

                        Forms\Components\Textarea::make(
                            'catatan'
                        )
                            ->label('Catatan')
                            ->rows(4)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Status Permintaan'
                )
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_info'
                        )
                            ->label('Status')
                            ->content(
                                fn (
                                    ?PermintaanDarah $record
                                ): string => $record
                                    ? $record->status->label()
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'peninjau_info'
                        )
                            ->label('Ditinjau Oleh')
                            ->content(
                                fn (
                                    ?PermintaanDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->peninjau
                                            ?->name
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'ditinjau_pada_info'
                        )
                            ->label('Ditinjau Pada')
                            ->content(
                                fn (
                                    ?PermintaanDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->ditinjau_pada
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
                            ->rows(3),

                        Forms\Components\Textarea::make(
                            'alasan_pembatalan'
                        )
                            ->label('Alasan Pembatalan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3),
                    ])
                    ->columns(3),
            ]);
    }

    public static function infolist(
        Infolist $infolist
    ): Infolist {
        return $infolist
            ->schema([
                InfolistSection::make(
                    'Informasi Permintaan'
                )
                    ->schema([
                        TextEntry::make(
                            'nomor_permintaan'
                        )
                            ->label('Nomor Permintaan')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusPermintaanDarah|string $state
                                ): string => self
                                    ::statusEnum($state)
                                    ->label()
                            )
                            ->color(
                                fn (
                                    StatusPermintaanDarah|string $state
                                ): string => self
                                    ::statusEnum($state)
                                    ->warna()
                            ),

                        TextEntry::make(
                            'tingkat_urgensi'
                        )
                            ->label('Urgensi')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    TingkatUrgensiPermintaanDarah|string $state
                                ): string => self
                                    ::urgensiEnum($state)
                                    ->label()
                            )
                            ->color(
                                fn (
                                    TingkatUrgensiPermintaanDarah|string $state
                                ): string => self
                                    ::urgensiEnum($state)
                                    ->warna()
                            ),

                        TextEntry::make(
                            'rumahSakit.nama_rumah_sakit'
                        )
                            ->label('Rumah Sakit'),

                        TextEntry::make(
                            'rumahSakit.kode_rumah_sakit'
                        )
                            ->label('Kode Rumah Sakit'),

                        TextEntry::make(
                            'referensi_pasien'
                        )
                            ->label('Referensi Pasien'),

                        TextEntry::make('nama_dokter')
                            ->label('Nama Dokter'),
                    ])
                    ->columns(3),

                InfolistSection::make(
                    'Kebutuhan Darah'
                )
                    ->schema([
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
                                    instanceof
                                    RhesusDarah
                                        ? $state->label()
                                        : (string) $state
                            ),

                        TextEntry::make(
                            'jumlah_kantong'
                        )
                            ->label('Jumlah')
                            ->suffix(' kantong'),

                        TextEntry::make(
                            'dibutuhkan_pada'
                        )
                            ->label('Dibutuhkan Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'path_dokumen_permintaan'
                        )
                            ->label('Dokumen Permintaan')
                            ->formatStateUsing(
                                fn (
                                    ?string $state
                                ): string => filled($state)
                                    ? 'Lihat dokumen'
                                    : 'Belum diunggah'
                            )
                            ->url(
                                fn (
                                    PermintaanDarah $record
                                ): ?string => filled(
                                    $record
                                        ->path_dokumen_permintaan
                                )
                                    ? asset(
                                        'storage/'
                                        . ltrim(
                                            $record
                                                ->path_dokumen_permintaan,
                                            '/'
                                        )
                                    )
                                    : null
                            )
                            ->openUrlInNewTab(),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                InfolistSection::make(
                    'Riwayat Proses'
                )
                    ->schema([
                        TextEntry::make('peninjau.name')
                            ->label('Ditinjau Oleh')
                            ->placeholder('-'),

                        TextEntry::make(
                            'ditinjau_pada'
                        )
                            ->label('Ditinjau Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'disetujui_pada'
                        )
                            ->label('Disetujui Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'siap_diambil_pada'
                        )
                            ->label('Siap Diambil Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'selesai_pada'
                        )
                            ->label('Selesai Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'dibatalkan_pada'
                        )
                            ->label('Dibatalkan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'alasan_penolakan'
                        )
                            ->label('Alasan Penolakan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'alasan_pembatalan'
                        )
                            ->label('Alasan Pembatalan')
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
                    'nomor_permintaan'
                )
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'rumahSakit.nama_rumah_sakit'
                )
                    ->label('Rumah Sakit')
                    ->description(
                        fn (
                            PermintaanDarah $record
                        ): string => $record
                            ->rumahSakit
                            ->kode_rumah_sakit
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'referensi_pasien'
                )
                    ->label('Referensi Pasien')
                    ->searchable()
                    ->toggleable(),

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
                    'jumlah_kantong'
                )
                    ->label('Jumlah')
                    ->suffix(' kantong')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'tingkat_urgensi'
                )
                    ->label('Urgensi')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            TingkatUrgensiPermintaanDarah|string $state
                        ): string => self
                            ::urgensiEnum($state)
                            ->label()
                    )
                    ->color(
                        fn (
                            TingkatUrgensiPermintaanDarah|string $state
                        ): string => self
                            ::urgensiEnum($state)
                            ->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'status'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusPermintaanDarah|string $state
                        ): string => self
                            ::statusEnum($state)
                            ->label()
                    )
                    ->color(
                        fn (
                            StatusPermintaanDarah|string $state
                        ): string => self
                            ::statusEnum($state)
                            ->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'dibutuhkan_pada'
                )
                    ->label('Dibutuhkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'peninjau.name'
                )
                    ->label('Peninjau')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'created_at'
                )
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'status'
                )
                    ->label('Status')
                    ->options(
                        StatusPermintaanDarah::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'tingkat_urgensi'
                )
                    ->label('Urgensi')
                    ->options(
                        TingkatUrgensiPermintaanDarah
                            ::options()
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
                    'profil_rumah_sakit_id'
                )
                    ->label('Rumah Sakit')
                    ->relationship(
                        'rumahSakit',
                        'nama_rumah_sakit'
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => $record->dapatDiubah()
                    ),

                Tables\Actions\Action::make(
                    'tinjau'
                )
                    ->label('Tinjau')
                    ->icon(
                        'heroicon-o-magnifying-glass'
                    )
                    ->color('info')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => $record
                            ->dapatDitinjau()
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            PermintaanDarah $record
                        ): void {
                            app(
                                LayananPermintaanDarah::class
                            )->tandaiDitinjau(
                                permintaan: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Permintaan sedang ditinjau.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'setujui'
                )
                    ->label('Setujui')
                    ->icon(
                        'heroicon-o-check-circle'
                    )
                    ->color('success')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => $record
                            ->dapatDisetujui()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Setujui Permintaan Darah'
                    )
                    ->modalDescription(
                        'Permintaan akan dilanjutkan ke proses alokasi kantong darah.'
                    )
                    ->action(
                        function (
                            PermintaanDarah $record
                        ): void {
                            app(
                                LayananPermintaanDarah::class
                            )->setujui(
                                permintaan: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Permintaan darah berhasil disetujui.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'menunggu_stok'
                )
                    ->label('Tunggu Stok')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => in_array(
                            $record->status,
                            [
                                StatusPermintaanDarah
                                    ::Diajukan,
                                StatusPermintaanDarah
                                    ::Ditinjau,
                                StatusPermintaanDarah
                                    ::Disetujui,
                            ],
                            true
                        )
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            PermintaanDarah $record
                        ): void {
                            app(
                                LayananPermintaanDarah::class
                            )->tandaiMenungguStok(
                                permintaan: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Permintaan menunggu ketersediaan stok.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'tolak'
                )
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => $record
                            ->dapatDitolak()
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            PermintaanDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananPermintaanDarah::class
                            )->tolak(
                                permintaan: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Permintaan darah ditolak.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'batalkan'
                )
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(
                        fn (
                            PermintaanDarah $record
                        ): bool => $record
                            ->dapatDibatalkan()
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(2000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            PermintaanDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananPermintaanDarah::class
                            )->batalkan(
                                permintaan: $record,
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Permintaan darah dibatalkan.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(
                'Belum ada permintaan darah'
            )
            ->emptyStateDescription(
                'Permintaan darah dari Rumah Sakit akan tampil pada halaman ini.'
            )
            ->emptyStateIcon(
                'heroicon-o-document-text'
            );
    }

    public static function canEdit(
        Model $record
    ): bool {
        return $record instanceof PermintaanDarah
            && $record->dapatDiubah();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListPermintaanDarahs::route('/'),

            'create' =>
                Pages\CreatePermintaanDarah::route(
                    '/create'
                ),

            'view' =>
                Pages\ViewPermintaanDarah::route(
                    '/{record}'
                ),

            'edit' =>
                Pages\EditPermintaanDarah::route(
                    '/{record}/edit'
                ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->nomor_permintaan;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Rumah Sakit' =>
                $record
                    ->rumahSakit
                    ?->nama_rumah_sakit
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

            'Jumlah' =>
                $record->jumlah_kantong
                . ' kantong',

            'Status' =>
                $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'rumahSakit.pengguna',
                'peninjau',
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
}