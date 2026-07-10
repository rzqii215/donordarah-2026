<?php

namespace App\Filament\Admin\Resources;

use App\Enums\PeranPengguna;
use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Filament\Admin\Resources\PendaftaranDonorResource\Pages;
use App\Models\JadwalDonor;
use App\Models\PendaftaranDonor;
use App\Models\User;
use App\Services\LayananPendaftaranDonor;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PendaftaranDonorResource extends Resource
{
    protected static ?string $model =
        PendaftaranDonor::class;

    protected static ?string $navigationIcon =
        'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel =
        'Pendaftaran Donor';

    protected static ?string $modelLabel =
        'Pendaftaran Donor';

    protected static ?string $pluralModelLabel =
        'Pendaftaran Donor';

    protected static ?string $navigationGroup =
        'Manajemen Donor';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Informasi Pendaftaran'
                )
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'nomor_pendaftaran_info'
                        )
                            ->label('Nomor Pendaftaran')
                            ->content(
                                fn (
                                    ?PendaftaranDonor $record
                                ): string => $record
                                    ? $record->nomor_pendaftaran
                                    : 'Dibuat otomatis setelah disimpan'
                            ),

                        Forms\Components\Select::make(
                            'jadwal_donor_id'
                        )
                            ->label('Jadwal Donor')
                            ->relationship(
                                name: 'jadwal',
                                titleAttribute: 'judul',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder => $query
                                        ->where(
                                            'status',
                                            StatusJadwalDonor
                                                ::Dipublikasikan
                                                ->value
                                        )
                                        ->where(
                                            'pendaftaran_dibuka_pada',
                                            '<=',
                                            now()
                                        )
                                        ->where(
                                            'pendaftaran_ditutup_pada',
                                            '>=',
                                            now()
                                        )
                                        ->orderBy('mulai_pada'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (
                                    JadwalDonor $record
                                ): string => sprintf(
                                    '%s — %s — %s',
                                    $record->kode_jadwal,
                                    $record->judul,
                                    $record->mulai_pada
                                        ->format('d M Y H:i')
                                )
                            )
                            ->searchable([
                                'kode_jadwal',
                                'judul',
                            ])
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make(
                            'pendonor_id'
                        )
                            ->label('Pendonor')
                            ->relationship(
                                name: 'pendonor',
                                titleAttribute: 'name',
                                modifyQueryUsing:
                                    fn (
                                        Builder $query
                                    ): Builder => $query
                                        ->role(
                                            PeranPengguna
                                                ::Pendonor
                                                ->value
                                        )
                                        ->whereHas(
                                            'profilPendonor'
                                        )
                                        ->orderBy('name'),
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
                            ->disabledOn('edit'),

                        Forms\Components\Textarea::make(
                            'catatan'
                        )
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Skrining Awal'
                )
                    ->description(
                        'Jawaban ini hanya merupakan skrining awal dan bukan keputusan medis.'
                    )
                    ->schema([
                        Forms\Components\Toggle::make(
                            'jawaban_skrining.sehat_hari_ini'
                        )
                            ->label('Merasa sehat hari ini')
                            ->default(true),

                        Forms\Components\Toggle::make(
                            'jawaban_skrining.sedang_minum_obat'
                        )
                            ->label('Sedang mengonsumsi obat')
                            ->default(false),

                        Forms\Components\Toggle::make(
                            'jawaban_skrining.operasi_terakhir'
                        )
                            ->label(
                                'Baru menjalani operasi atau tindakan medis'
                            )
                            ->default(false),

                        Forms\Components\Toggle::make(
                            'jawaban_skrining.cukup_tidur'
                        )
                            ->label('Tidur cukup')
                            ->default(true),

                        Forms\Components\Toggle::make(
                            'jawaban_skrining.sudah_makan'
                        )
                            ->label('Sudah makan sebelum donor')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Informasi Status'
                )
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_info'
                        )
                            ->label('Status')
                            ->content(
                                fn (
                                    ?PendaftaranDonor $record
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
                                    ?PendaftaranDonor $record
                                ): string => $record
                                    ? (
                                        $record->peninjau?->name
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
                                    ?PendaftaranDonor $record
                                ): string => $record
                                    ? (
                                        $record->ditinjau_pada
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
                    'Informasi Pendaftaran'
                )
                    ->schema([
                        TextEntry::make(
                            'nomor_pendaftaran'
                        )
                            ->label('Nomor Pendaftaran')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusPendaftaranDonor|string $state
                                ): string => self::statusEnum(
                                    $state
                                )->label()
                            )
                            ->color(
                                fn (
                                    StatusPendaftaranDonor|string $state
                                ): string => self::statusEnum(
                                    $state
                                )->warna()
                            ),

                        TextEntry::make('pendonor.name')
                            ->label('Nama Pendonor'),

                        TextEntry::make('pendonor.email')
                            ->label('Email Pendonor'),

                        TextEntry::make(
                            'pendonor.profilPendonor.kode_pendonor'
                        )
                            ->label('Kode Pendonor')
                            ->placeholder('-'),

                        TextEntry::make('jadwal.judul')
                            ->label('Jadwal Donor'),

                        TextEntry::make(
                            'jadwal.mulai_pada'
                        )
                            ->label('Waktu Kegiatan')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'jadwal.lokasi.nama'
                        )
                            ->label('Lokasi'),

                        TextEntry::make('catatan')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                InfolistSection::make('Skrining Awal')
                    ->schema([
                        IconEntry::make(
                            'jawaban_skrining.sehat_hari_ini'
                        )
                            ->label('Sehat Hari Ini')
                            ->boolean(),

                        IconEntry::make(
                            'jawaban_skrining.sedang_minum_obat'
                        )
                            ->label('Sedang Minum Obat')
                            ->boolean(),

                        IconEntry::make(
                            'jawaban_skrining.operasi_terakhir'
                        )
                            ->label('Baru Operasi')
                            ->boolean(),

                        IconEntry::make(
                            'jawaban_skrining.cukup_tidur'
                        )
                            ->label('Cukup Tidur')
                            ->boolean(),

                        IconEntry::make(
                            'jawaban_skrining.sudah_makan'
                        )
                            ->label('Sudah Makan')
                            ->boolean(),
                    ])
                    ->columns(3),

                InfolistSection::make(
                    'Riwayat Proses'
                )
                    ->schema([
                        TextEntry::make('peninjau.name')
                            ->label('Ditinjau Oleh')
                            ->placeholder('-'),

                        TextEntry::make('ditinjau_pada')
                            ->label('Ditinjau Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('hadir_pada')
                            ->label('Hadir Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('selesai_pada')
                            ->label('Selesai Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('dibatalkan_pada')
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
                    ->columns(2),
            ]);
    }

    public static function table(
        Table $table
    ): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make(
                    'nomor_pendaftaran'
                )
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'pendonor.name'
                )
                    ->label('Pendonor')
                    ->description(
                        fn (
                            PendaftaranDonor $record
                        ): string => $record
                            ->pendonor
                            ->profilPendonor
                            ?->kode_pendonor
                            ?? $record->pendonor->email
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'jadwal.judul'
                )
                    ->label('Jadwal')
                    ->description(
                        fn (
                            PendaftaranDonor $record
                        ): string => $record
                            ->jadwal
                            ->mulai_pada
                            ->format('d M Y H:i')
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'jadwal.lokasi.nama'
                )
                    ->label('Lokasi')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'status'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusPendaftaranDonor|string $state
                        ): string => self::statusEnum(
                            $state
                        )->label()
                    )
                    ->color(
                        fn (
                            StatusPendaftaranDonor|string $state
                        ): string => self::statusEnum(
                            $state
                        )->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'peninjau.name'
                )
                    ->label('Ditinjau Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'hadir_pada'
                )
                    ->label('Kehadiran')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'selesai_pada'
                )
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'created_at'
                )
                    ->label('Mendaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'status'
                )
                    ->label('Status')
                    ->options(
                        StatusPendaftaranDonor::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'jadwal_donor_id'
                )
                    ->label('Jadwal Donor')
                    ->relationship(
                        'jadwal',
                        'judul'
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make(
                    'pendonor_id'
                )
                    ->label('Pendonor')
                    ->relationship(
                        'pendonor',
                        'name'
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => self::canEdit($record)
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
                            PendaftaranDonor $record
                        ): bool => $record->dapatDisetujui()
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            PendaftaranDonor $record
                        ): void {
                            app(
                                LayananPendaftaranDonor::class
                            )->setujui(
                                pendaftaran: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Pendaftaran donor berhasil disetujui.'
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
                            PendaftaranDonor $record
                        ): bool => $record->dapatDitolak()
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            PendaftaranDonor $record,
                            array $data
                        ): void {
                            app(
                                LayananPendaftaranDonor::class
                            )->tolak(
                                pendaftaran: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Pendaftaran donor berhasil ditolak.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'catat_kehadiran'
                )
                    ->label('Catat Hadir')
                    ->icon(
                        'heroicon-o-user-plus'
                    )
                    ->color('info')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => $record
                            ->dapatDicatatHadir()
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            PendaftaranDonor $record
                        ): void {
                            app(
                                LayananPendaftaranDonor::class
                            )->catatKehadiran(
                                pendaftaran: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Kehadiran Pendonor berhasil dicatat.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'tidak_hadir'
                )
                    ->label('Tidak Hadir')
                    ->icon(
                        'heroicon-o-user-minus'
                    )
                    ->color('gray')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => $record->status ===
                            StatusPendaftaranDonor::Disetujui
                            && now()->greaterThanOrEqualTo(
                                $record
                                    ->jadwal
                                    ->mulai_pada
                            )
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            PendaftaranDonor $record
                        ): void {
                            app(
                                LayananPendaftaranDonor::class
                            )->tandaiTidakHadir(
                                pendaftaran: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                            );

                            Notification::make()
                                ->title(
                                    'Pendonor ditandai tidak hadir.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'buat_pemeriksaan_kesehatan'
                )
                    ->label('Buat Pemeriksaan')
                    ->icon(
                        'heroicon-o-heart'
                    )
                    ->color('primary')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => $record->status ===
                            StatusPendaftaranDonor::Hadir
                            && ! $record
                                ->pemeriksaanKesehatan()
                                ->exists()
                    )
                    ->url(
                        fn (
                            PendaftaranDonor $record
                        ): string => PemeriksaanKesehatanResource::getUrl(
                            'create',
                            [
                                'pendaftaran_donor_id' => $record->id,
                            ]
                        )
                    ),

                Tables\Actions\Action::make(
                    'buat_kantong_darah'
                )
                    ->label('Buat Kantong Darah')
                    ->icon(
                        'heroicon-o-beaker'
                    )
                    ->color('success')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => $record->status ===
                            StatusPendaftaranDonor::Layak
                            && ! $record
                                ->kantongDarah()
                                ->exists()
                    )
                    ->url(
                        fn (
                            PendaftaranDonor $record
                        ): string => KantongDarahResource::getUrl(
                            'create',
                            [
                                'pendaftaran_donor_id' => $record->id,
                            ]
                        )
                    ),

                Tables\Actions\Action::make(
                    'batalkan'
                )
                    ->label('Batalkan')
                    ->icon(
                        'heroicon-o-no-symbol'
                    )
                    ->color('warning')
                    ->visible(
                        fn (
                            PendaftaranDonor $record
                        ): bool => self::dapatDibatalkanLewatResource($record)
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan'
                        )
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->action(
                        function (
                            PendaftaranDonor $record,
                            array $data
                        ): void {
                            app(
                                LayananPendaftaranDonor::class
                            )->batalkan(
                                pendaftaran: $record,
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Pendaftaran donor berhasil dibatalkan.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(
                'Belum ada pendaftaran donor'
            )
            ->emptyStateDescription(
                'Pendaftaran akan muncul setelah Pendonor memilih jadwal donor yang tersedia.'
            )
            ->emptyStateIcon(
                'heroicon-o-clipboard-document-check'
            );
    }

    public static function canEdit(
        Model $record
    ): bool {
        return $record instanceof PendaftaranDonor
            && $record->status ===
                StatusPendaftaranDonor::Menunggu;
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
                Pages\ListPendaftaranDonors::route('/'),

            'create' =>
                Pages\CreatePendaftaranDonor::route(
                    '/create'
                ),

            'view' =>
                Pages\ViewPendaftaranDonor::route(
                    '/{record}'
                ),

            'edit' =>
                Pages\EditPendaftaranDonor::route(
                    '/{record}/edit'
                ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->nomor_pendaftaran;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Pendonor' =>
                $record->pendonor?->name ?? '-',

            'Jadwal' =>
                $record->jadwal?->judul ?? '-',

            'Status' =>
                $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'jadwal.lokasi',
                'pendonor.profilPendonor',
                'peninjau',
                'pemeriksaanKesehatan',
                'kantongDarah',
            ]);
    }

    private static function dapatDibatalkanLewatResource(
        PendaftaranDonor $record
    ): bool {
        if (
            in_array(
                $record->status,
                [
                    StatusPendaftaranDonor::Selesai,
                    StatusPendaftaranDonor::Layak,
                    StatusPendaftaranDonor::TidakLayak,
                ],
                true
            )
        ) {
            return false;
        }

        if ($record->kantongDarah()->exists()) {
            return false;
        }

        return $record->dapatDibatalkan();
    }

    private static function statusEnum(
        StatusPendaftaranDonor|string $status
    ): StatusPendaftaranDonor {
        return $status instanceof
            StatusPendaftaranDonor
                ? $status
                : StatusPendaftaranDonor::from(
                    $status
                );
    }
}