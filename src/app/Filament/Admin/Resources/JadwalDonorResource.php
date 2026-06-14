<?php

namespace App\Filament\Admin\Resources;

use App\Enums\StatusJadwalDonor;
use App\Filament\Admin\Resources\JadwalDonorResource\Pages;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\ImageEntry;
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
use Illuminate\Support\Facades\DB;
use Throwable;

class JadwalDonorResource extends Resource
{
    protected static ?string $model = JadwalDonor::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Donor';

    protected static ?string $modelLabel = 'Jadwal Donor';

    protected static ?string $pluralModelLabel = 'Jadwal Donor';

    protected static ?string $navigationGroup = 'Manajemen Donor';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('kode_jadwal')
                            ->label('Kode Jadwal')
                            ->required()
                            ->maxLength(30)
                            ->unique(
                                table: 'jadwal_donor',
                                column: 'kode_jadwal',
                                ignoreRecord: true,
                            )
                            ->placeholder('SCH-202606-000001'),

                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Donor Darah Bersama'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(
                                'Slug dibuat otomatis dari judul kegiatan.'
                            )
                            ->placeholder('Dibuat otomatis'),

                        Forms\Components\Select::make('lokasi_donor_id')
                            ->label('Lokasi Donor')
                            ->relationship(
                                name: 'lokasi',
                                titleAttribute: 'nama',
                                modifyQueryUsing: fn (
                                    Builder $query
                                ): Builder => $query
                                    ->where('aktif', true)
                                    ->orderBy('nama'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (LokasiDonor $record): string => sprintf(
                                    '%s — %s',
                                    $record->nama,
                                    $record->kota,
                                )
                            )
                            ->searchable([
                                'nama',
                                'kota',
                                'alamat',
                            ])
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(3000)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('path_banner')
                            ->label('Banner Kegiatan')
                            ->disk('public')
                            ->directory('jadwal-donor')
                            ->image()
                            ->imageEditor()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            ->helperText(
                                'Format JPG, PNG, atau WEBP. Maksimal 5 MB.'
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu Kegiatan')
                    ->description(
                        'Urutan waktu harus: pendaftaran dibuka, pendaftaran ditutup, kegiatan dimulai, lalu kegiatan selesai.'
                    )
                    ->schema([
                        Forms\Components\DateTimePicker::make('mulai_pada')
                            ->label('Kegiatan Dimulai')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->live(),

                        Forms\Components\DateTimePicker::make('selesai_pada')
                            ->label('Kegiatan Selesai')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->live()
                            ->rules([
                                fn (Get $get): Closure => function (
                                    string $attribute,
                                    mixed $value,
                                    Closure $fail
                                ) use ($get): void {
                                    $mulai = self::parseTanggal(
                                        $get('mulai_pada')
                                    );

                                    $selesai = self::parseTanggal($value);

                                    if (
                                        $mulai === null
                                        || $selesai === null
                                    ) {
                                        return;
                                    }

                                    if (
                                        $selesai->lessThanOrEqualTo($mulai)
                                    ) {
                                        $fail(
                                            'Kegiatan selesai harus setelah kegiatan dimulai.'
                                        );
                                    }
                                },
                            ]),

                        Forms\Components\DateTimePicker::make(
                            'pendaftaran_dibuka_pada'
                        )
                            ->label('Pendaftaran Dibuka')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->live(),

                        Forms\Components\DateTimePicker::make(
                            'pendaftaran_ditutup_pada'
                        )
                            ->label('Pendaftaran Ditutup')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->live()
                            ->rules([
                                fn (Get $get): Closure => function (
                                    string $attribute,
                                    mixed $value,
                                    Closure $fail
                                ) use ($get): void {
                                    $pendaftaranDibuka =
                                        self::parseTanggal(
                                            $get(
                                                'pendaftaran_dibuka_pada'
                                            )
                                        );

                                    $pendaftaranDitutup =
                                        self::parseTanggal($value);

                                    $kegiatanDimulai =
                                        self::parseTanggal(
                                            $get('mulai_pada')
                                        );

                                    if (
                                        $pendaftaranDitutup === null
                                    ) {
                                        return;
                                    }

                                    if (
                                        $pendaftaranDibuka !== null
                                        && $pendaftaranDitutup
                                            ->lessThanOrEqualTo(
                                                $pendaftaranDibuka
                                            )
                                    ) {
                                        $fail(
                                            'Pendaftaran ditutup harus setelah pendaftaran dibuka.'
                                        );

                                        return;
                                    }

                                    if (
                                        $kegiatanDimulai !== null
                                        && $pendaftaranDitutup
                                            ->greaterThan(
                                                $kegiatanDimulai
                                            )
                                    ) {
                                        $fail(
                                            'Pendaftaran harus ditutup sebelum atau tepat saat kegiatan dimulai.'
                                        );
                                    }
                                },
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kuota dan Status')
                    ->schema([
                        Forms\Components\TextInput::make('kuota')
                            ->label('Kuota Pendonor')
                            ->required()
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->default(50),

                        Forms\Components\Select::make('status')
                            ->label('Status Jadwal')
                            ->options(
                                StatusJadwalDonor::options()
                            )
                            ->default(
                                StatusJadwalDonor::Draf->value
                            )
                            ->required()
                            ->native(false)
                            ->disabledOn('create')
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Informasi Pembatalan'
                )
                    ->visible(
                        fn (
                            ?JadwalDonor $record
                        ): bool => $record?->status ===
                            StatusJadwalDonor::Dibatalkan
                    )
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'dibatalkan_pada_info'
                        )
                            ->label('Dibatalkan Pada')
                            ->content(
                                fn (
                                    ?JadwalDonor $record
                                ): string => $record
                                    ? (
                                        $record->dibatalkan_pada
                                            ?->format(
                                                'd M Y H:i'
                                            )
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Textarea::make(
                            'alasan_pembatalan'
                        )
                            ->label('Alasan Pembatalan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->hiddenOn('create'),
            ]);
    }

    public static function infolist(
        Infolist $infolist
    ): Infolist {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Kegiatan')
                    ->schema([
                        ImageEntry::make('path_banner')
                            ->label('Banner')
                            ->disk('public')
                            ->height(180)
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('kode_jadwal')
                            ->label('Kode Jadwal')
                            ->copyable(),

                        TextEntry::make('judul')
                            ->label('Judul Kegiatan'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusJadwalDonor|string $state
                                ): string => self::statusEnum(
                                    $state
                                )->label()
                            )
                            ->color(
                                fn (
                                    StatusJadwalDonor|string $state
                                ): string => self::statusEnum(
                                    $state
                                )->warna()
                            ),

                        TextEntry::make('lokasi.nama')
                            ->label('Lokasi'),

                        TextEntry::make('lokasi.alamat')
                            ->label('Alamat Lokasi')
                            ->columnSpanFull(),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                InfolistSection::make('Waktu dan Kuota')
                    ->schema([
                        TextEntry::make('mulai_pada')
                            ->label('Kegiatan Dimulai')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('selesai_pada')
                            ->label('Kegiatan Selesai')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'pendaftaran_dibuka_pada'
                        )
                            ->label('Pendaftaran Dibuka')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'pendaftaran_ditutup_pada'
                        )
                            ->label('Pendaftaran Ditutup')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('kuota')
                            ->label('Kuota')
                            ->suffix(' pendonor'),
                    ])
                    ->columns(2),

                InfolistSection::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('pembuat.name')
                            ->label('Dibuat Oleh'),

                        TextEntry::make(
                            'dipublikasikan_pada'
                        )
                            ->label('Dipublikasikan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('dibatalkan_pada')
                            ->label('Dibatalkan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'alasan_pembatalan'
                        )
                            ->label('Alasan Pembatalan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make(
                    'path_banner'
                )
                    ->label('Banner')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make(
                    'kode_jadwal'
                )
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make(
                    'lokasi.nama'
                )
                    ->label('Lokasi')
                    ->description(
                        fn (
                            JadwalDonor $record
                        ): string => $record->lokasi->kota
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'mulai_pada'
                )
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'selesai_pada'
                )
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('kuota')
                    ->label('Kuota')
                    ->numeric()
                    ->suffix(' orang')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusJadwalDonor|string $state
                        ): string => self::statusEnum(
                            $state
                        )->label()
                    )
                    ->color(
                        fn (
                            StatusJadwalDonor|string $state
                        ): string => self::statusEnum(
                            $state
                        )->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'pendaftaran_ditutup_pada'
                )
                    ->label('Pendaftaran Ditutup')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'pembuat.name'
                )
                    ->label('Dibuat Oleh')
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        StatusJadwalDonor::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'lokasi_donor_id'
                )
                    ->label('Lokasi')
                    ->relationship(
                        'lokasi',
                        'nama'
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make(
                    'akan_datang'
                )
                    ->label('Jadwal Akan Datang')
                    ->query(
                        fn (
                            Builder $query
                        ): Builder => $query->where(
                            'mulai_pada',
                            '>',
                            now()
                        )
                    ),

                Tables\Filters\Filter::make(
                    'pendaftaran_aktif'
                )
                    ->label('Pendaftaran Aktif')
                    ->query(
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
                            JadwalDonor $record
                        ): bool => ! in_array(
                            $record->status,
                            [
                                StatusJadwalDonor::Selesai,
                                StatusJadwalDonor::Dibatalkan,
                            ],
                            true
                        )
                    ),

                Tables\Actions\Action::make(
                    'publikasikan'
                )
                    ->label('Publikasikan')
                    ->icon('heroicon-o-megaphone')
                    ->color('success')
                    ->visible(
                        fn (
                            JadwalDonor $record
                        ): bool => $record->status ===
                            StatusJadwalDonor::Draf
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Publikasikan Jadwal Donor'
                    )
                    ->modalDescription(
                        'Jadwal akan tersedia pada frontend sesuai periode pendaftaran.'
                    )
                    ->action(
                        function (
                            JadwalDonor $record
                        ): void {
                            DB::transaction(
                                function () use (
                                    $record
                                ): void {
                                    $record->update([
                                        'status' =>
                                            StatusJadwalDonor
                                                ::Dipublikasikan,
                                        'dipublikasikan_pada' =>
                                            now(),
                                        'dibatalkan_pada' =>
                                            null,
                                        'alasan_pembatalan' =>
                                            null,
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Jadwal donor berhasil dipublikasikan.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'selesaikan'
                )
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(
                        fn (
                            JadwalDonor $record
                        ): bool => in_array(
                            $record->status,
                            [
                                StatusJadwalDonor
                                    ::Dipublikasikan,
                                StatusJadwalDonor
                                    ::Berlangsung,
                            ],
                            true
                        )
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            JadwalDonor $record
                        ): void {
                            $record->update([
                                'status' =>
                                    StatusJadwalDonor
                                        ::Selesai,
                            ]);

                            Notification::make()
                                ->title(
                                    'Jadwal donor ditandai selesai.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'batalkan'
                )
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (
                            JadwalDonor $record
                        ): bool => ! in_array(
                            $record->status,
                            [
                                StatusJadwalDonor
                                    ::Selesai,
                                StatusJadwalDonor
                                    ::Dibatalkan,
                            ],
                            true
                        )
                    )
                    ->form([
                        Forms\Components\Textarea::make(
                            'alasan_pembatalan'
                        )
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->requiresConfirmation()
                    ->action(
                        function (
                            JadwalDonor $record,
                            array $data
                        ): void {
                            DB::transaction(
                                function () use (
                                    $record,
                                    $data
                                ): void {
                                    $record->update([
                                        'status' =>
                                            StatusJadwalDonor
                                                ::Dibatalkan,
                                        'dibatalkan_pada' =>
                                            now(),
                                        'alasan_pembatalan' =>
                                            $data[
                                                'alasan_pembatalan'
                                            ],
                                    ]);
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Jadwal donor berhasil dibatalkan.'
                                )
                                ->danger()
                                ->send();
                        }
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn (
                            JadwalDonor $record
                        ): bool => $record->status ===
                            StatusJadwalDonor::Draf
                    )
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('mulai_pada', 'desc')
            ->emptyStateHeading('Belum ada jadwal donor')
            ->emptyStateDescription(
                'Tambahkan jadwal donor yang terhubung dengan lokasi aktif.'
            )
            ->emptyStateIcon(
                'heroicon-o-calendar-days'
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
                Pages\ListJadwalDonors::route('/'),

            'create' =>
                Pages\CreateJadwalDonor::route('/create'),

            'view' =>
                Pages\ViewJadwalDonor::route('/{record}'),

            'edit' =>
                Pages\EditJadwalDonor::route(
                    '/{record}/edit'
                ),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->judul;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Kode' => $record->kode_jadwal,
            'Lokasi' => $record->lokasi?->nama ?? '-',
            'Mulai' => $record->mulai_pada
                ?->format('d M Y H:i') ?? '-',
            'Status' => $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'lokasi',
                'pembuat',
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    private static function statusEnum(
        StatusJadwalDonor|string $status
    ): StatusJadwalDonor {
        return $status instanceof StatusJadwalDonor
            ? $status
            : StatusJadwalDonor::from($status);
    }

    private static function parseTanggal(
        mixed $nilai
    ): ?CarbonImmutable {
        if (blank($nilai)) {
            return null;
        }

        if ($nilai instanceof CarbonImmutable) {
            return $nilai;
        }

        if (
            $nilai instanceof \DateTimeInterface
        ) {
            return CarbonImmutable::instance($nilai);
        }

        $nilaiString = trim((string) $nilai);

        $formatTanggal = [
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd/m/Y H:i',
            'd-m-Y H:i',
            'Y-m-d\TH:i',
        ];

        foreach ($formatTanggal as $format) {
            try {
                $tanggal = CarbonImmutable::createFromFormat(
                    $format,
                    $nilaiString
                );

                if ($tanggal !== false) {
                    return $tanggal;
                }
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return CarbonImmutable::parse($nilaiString);
        } catch (Throwable) {
            return null;
        }
    }
}