<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusPermintaanDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Filament\Admin\Resources\PermintaanDarahResource\Pages;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Services\LayananAlokasiDarah;
use App\Services\LayananPermintaanDarah;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PermintaanDarahResource extends Resource
{
    protected static ?string $model = PermintaanDarah::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Manajemen Donor Darah';

    protected static ?string $navigationLabel = 'Pengajuan Kebutuhan Donor';

    protected static ?string $modelLabel = 'Pengajuan Kebutuhan Donor';

    protected static ?string $pluralModelLabel = 'Pengajuan Kebutuhan Donor';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pemohon')
                    ->description('Pilih pemohon donor yang mengajukan kebutuhan darah.')
                    ->schema([
                        Forms\Components\Select::make('profil_rumah_sakit_id')
                            ->label('Pemohon Donor')
                            ->options(function (): array {
                                return ProfilRumahSakit::query()
                                    ->orderBy('nama_rumah_sakit')
                                    ->get()
                                    ->mapWithKeys(function (ProfilRumahSakit $profil): array {
                                        return [
                                            $profil->id => $profil->nama_rumah_sakit
                                                . ' - '
                                                . $profil->kode_rumah_sakit,
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('nomor_permintaan')
                            ->label('Nomor Pengajuan')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Dibuat otomatis oleh sistem'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Kebutuhan')
                    ->description('Data kebutuhan donor yang diajukan oleh pemohon.')
                    ->schema([
                        Forms\Components\TextInput::make('referensi_pasien')
                            ->label('Referensi Pengajuan')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make('nama_dokter')
                            ->label('Penanggung Jawab Pengajuan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->options(GolonganDarah::options())
                            ->required(),

                        Forms\Components\Select::make('rhesus')
                            ->label('Rhesus')
                            ->options(RhesusDarah::options())
                            ->required(),

                        Forms\Components\TextInput::make('jumlah_kantong')
                            ->label('Jumlah Kantong')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),

                        Forms\Components\Select::make('tingkat_urgensi')
                            ->label('Tingkat Urgensi')
                            ->options(TingkatUrgensiPermintaanDarah::options())
                            ->required(),

                        Forms\Components\DateTimePicker::make('dibutuhkan_pada')
                            ->label('Dibutuhkan Pada')
                            ->seconds(false)
                            ->native(false)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Pengajuan')
                            ->options(StatusPermintaanDarah::options())
                            ->required()
                            ->default(StatusPermintaanDarah::Diajukan->value),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dokumen dan Catatan')
                    ->schema([
                        Forms\Components\FileUpload::make('path_dokumen_permintaan')
                            ->label('Dokumen Pendukung')
                            ->disk('public')
                            ->directory('dokumen-pengajuan-kebutuhan-donor')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(4096)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan / Pembatalan')
                            ->rows(4)
                            ->maxLength(5000)
                            ->visible(function (?PermintaanDarah $record): bool {
                                if ($record === null) {
                                    return false;
                                }

                                return in_array(
                                    self::statusValue($record),
                                    [
                                        StatusPermintaanDarah::Ditolak->value,
                                        StatusPermintaanDarah::Dibatalkan->value,
                                    ],
                                    true
                                );
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_permintaan')
                    ->label('Nomor Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('rumahSakit.nama_rumah_sakit')
                    ->label('Pemohon Donor')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('referensi_pasien')
                    ->label('Referensi')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nama_dokter')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('golongan_darah')
                    ->label('Gol. Darah')
                    ->formatStateUsing(function ($state, PermintaanDarah $record): string {
                        return self::formatGolonganDarah($record);
                    })
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('jumlah_kantong')
                    ->label('Kebutuhan')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state) . ' kantong')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_teralokasi')
                    ->label('Alokasi')
                    ->state(function (PermintaanDarah $record): string {
                        return number_format($record->jumlahKantongDialokasikan())
                            . ' / '
                            . number_format((int) $record->jumlah_kantong)
                            . ' kantong';
                    })
                    ->badge()
                    ->color(function (PermintaanDarah $record): string {
                        return $record->kebutuhanSudahTerpenuhi()
                            ? 'success'
                            : 'warning';
                    }),

                Tables\Columns\TextColumn::make('tingkat_urgensi')
                    ->label('Urgensi')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::labelEnum($state))
                    ->color(function ($state): string {
                        $value = $state instanceof TingkatUrgensiPermintaanDarah
                            ? $state->value
                            : (string) $state;

                        return match ($value) {
                            TingkatUrgensiPermintaanDarah::Darurat->value => 'danger',
                            TingkatUrgensiPermintaanDarah::Mendesak->value => 'warning',
                            default => 'info',
                        };
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('dibutuhkan_pada')
                    ->label('Dibutuhkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::labelStatus($state))
                    ->color(fn ($state): string => self::warnaStatus($state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('sudah_punya_distribusi')
                    ->label('Distribusi')
                    ->boolean()
                    ->state(function (PermintaanDarah $record): bool {
                        return DistribusiDarah::query()
                            ->where('permintaan_darah_id', $record->id)
                            ->exists();
                    }),

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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusPermintaanDarah::options()),

                Tables\Filters\SelectFilter::make('golongan_darah')
                    ->label('Golongan Darah')
                    ->options(GolonganDarah::options()),

                Tables\Filters\SelectFilter::make('tingkat_urgensi')
                    ->label('Urgensi')
                    ->options(TingkatUrgensiPermintaanDarah::options()),

                Tables\Filters\Filter::make('perlu_alokasi')
                    ->label('Perlu Alokasi')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->whereIn('status', [
                                StatusPermintaanDarah::Disetujui->value,
                                StatusPermintaanDarah::MenungguStok->value,
                            ])
                    ),

                Tables\Filters\Filter::make('siap_distribusi')
                    ->label('Siap Dibuatkan Distribusi')
                    ->query(
                        fn (Builder $query): Builder => $query
                            ->where('status', StatusPermintaanDarah::SiapDiambil->value)
                            ->whereNotIn(
                                'id',
                                DistribusiDarah::query()
                                    ->whereNotNull('permintaan_darah_id')
                                    ->select('permintaan_darah_id')
                            )
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn (PermintaanDarah $record): bool => $record->dapatDiubah()
                    ),

                Tables\Actions\Action::make('tinjau')
                    ->label('Tinjau')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn (PermintaanDarah $record): bool => self::statusValue($record)
                            === StatusPermintaanDarah::Diajukan->value
                    )
                    ->action(function (PermintaanDarah $record): void {
                        app(LayananPermintaanDarah::class)->tandaiDitinjau(
                            permintaan: $record,
                            petugasId: (int) Filament::auth()->id()
                        );

                        Notification::make()
                            ->title('Pengajuan berhasil ditandai sedang ditinjau.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (PermintaanDarah $record): bool {
                        return $record->dapatDisetujui();
                    })
                    ->action(function (PermintaanDarah $record): void {
                        app(LayananPermintaanDarah::class)->setujui(
                            permintaan: $record,
                            petugasId: (int) Filament::auth()->id()
                        );

                        Notification::make()
                            ->title('Pengajuan berhasil disetujui.')
                            ->body('Lanjutkan dengan Alokasi Otomatis jika stok kantong darah sudah tersedia.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('alokasi_otomatis')
                    ->label('Alokasi Otomatis')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Alokasi Otomatis Kantong Darah')
                    ->modalDescription('Sistem akan mengambil kantong darah tersedia yang sesuai golongan darah dan rhesus, lalu memprioritaskan kedaluwarsa terdekat.')
                    ->modalSubmitActionLabel('Ya, Alokasikan')
                    ->visible(
                        fn (PermintaanDarah $record): bool => self::dapatDialokasiOtomatis($record)
                    )
                    ->action(function (PermintaanDarah $record): void {
                        try {
                            $hasil = app(LayananAlokasiDarah::class)->alokasikanOtomatis(
                                permintaan: $record,
                                petugasId: (int) Filament::auth()->id()
                            );
                        } catch (ValidationException $exception) {
                            $pesan = collect($exception->errors())
                                ->flatten()
                                ->first() ?? $exception->getMessage();

                            Notification::make()
                                ->title('Alokasi otomatis gagal.')
                                ->body((string) $pesan)
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->refresh();

                        if ($hasil->count() === 0) {
                            Notification::make()
                                ->title('Belum ada kantong darah yang dapat dialokasikan.')
                                ->body('Pengajuan tetap berada pada status Menunggu Stok sampai stok sesuai tersedia.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Alokasi otomatis berhasil.')
                            ->body(
                                number_format($hasil->count())
                                . ' kantong darah berhasil dialokasikan.'
                            )
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('siap_diambil')
                    ->label('Siap Diambil')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(
                        fn (PermintaanDarah $record): bool => in_array(
                            self::statusValue($record),
                            [
                                StatusPermintaanDarah::Disetujui->value,
                                StatusPermintaanDarah::MenungguStok->value,
                            ],
                            true
                        ) && $record->kebutuhanSudahTerpenuhi()
                    )
                    ->action(function (PermintaanDarah $record): void {
                        $record
                            ->forceFill([
                                'status' => StatusPermintaanDarah::SiapDiambil->value,
                                'siap_diambil_pada' => $record->siap_diambil_pada ?? now(),
                            ])
                            ->save();

                        Notification::make()
                            ->title('Pengajuan berhasil ditandai Siap Diambil.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('buat_distribusi')
                    ->label('Buat Distribusi')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(
                        fn (PermintaanDarah $record): bool => self::dapatBuatDistribusi($record)
                    )
                    ->url(function (PermintaanDarah $record): string {
                        return DistribusiDarahResource::getUrl('create', [
                            'permintaan_darah_id' => $record->id,
                        ]);
                    }),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(4)
                            ->maxLength(5000),
                    ])
                    ->visible(function (PermintaanDarah $record): bool {
                        return $record->dapatDitolak();
                    })
                    ->action(function (PermintaanDarah $record, array $data): void {
                        app(LayananPermintaanDarah::class)->tolak(
                            permintaan: $record,
                            petugasId: (int) Filament::auth()->id(),
                            alasan: $data['alasan_penolakan']
                        );

                        Notification::make()
                            ->title('Pengajuan berhasil ditolak.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->rows(4)
                            ->maxLength(5000),
                    ])
                    ->visible(function (PermintaanDarah $record): bool {
                        return $record->dapatDibatalkan();
                    })
                    ->action(function (PermintaanDarah $record, array $data): void {
                        app(LayananPermintaanDarah::class)->batalkan(
                            permintaan: $record,
                            alasan: $data['alasan_penolakan']
                        );

                        Notification::make()
                            ->title('Pengajuan berhasil dibatalkan.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort(
                'created_at',
                'desc'
            );
    }

    public static function canEdit(Model $record): bool
    {
        return $record instanceof PermintaanDarah
            && $record->dapatDiubah();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'rumahSakit',
                'itemAktif',
                'distribusi',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermintaanDarah::route('/'),
            'create' => Pages\CreatePermintaanDarah::route('/create'),
            'view' => Pages\ViewPermintaanDarah::route('/{record}'),
            'edit' => Pages\EditPermintaanDarah::route('/{record}/edit'),
        ];
    }

    private static function formatGolonganDarah(?PermintaanDarah $permintaan): string
    {
        if ($permintaan === null) {
            return '-';
        }

        $golongan = $permintaan->golongan_darah;
        $rhesus = $permintaan->rhesus;

        $golonganLabel = is_object($golongan) && method_exists($golongan, 'label')
            ? $golongan->label()
            : (string) ($golongan instanceof \BackedEnum ? $golongan->value : $golongan);

        $rhesusLabel = is_object($rhesus) && method_exists($rhesus, 'simbol')
            ? $rhesus->simbol()
            : (string) ($rhesus instanceof \BackedEnum ? $rhesus->value : $rhesus);

        return trim($golonganLabel . $rhesusLabel);
    }

    private static function labelEnum(mixed $state): string
    {
        if (is_object($state) && method_exists($state, 'label')) {
            return $state->label();
        }

        $value = $state instanceof \BackedEnum
            ? $state->value
            : (string) $state;

        return Str::of($value)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->headline()
            ->toString();
    }

    private static function labelStatus(mixed $state): string
    {
        if ($state instanceof StatusPermintaanDarah) {
            return $state->label();
        }

        return StatusPermintaanDarah::tryFrom((string) $state)?->label()
            ?? Str::of((string) $state)
                ->replace('_', ' ')
                ->replace('-', ' ')
                ->headline()
                ->toString();
    }

    private static function warnaStatus(mixed $state): string
    {
        $value = $state instanceof StatusPermintaanDarah
            ? $state->value
            : (string) $state;

        return match ($value) {
            StatusPermintaanDarah::Draf->value => 'gray',
            StatusPermintaanDarah::Diajukan->value => 'warning',
            StatusPermintaanDarah::Ditinjau->value => 'info',
            StatusPermintaanDarah::MenungguStok->value => 'warning',
            StatusPermintaanDarah::Disetujui->value => 'success',
            StatusPermintaanDarah::SiapDiambil->value => 'info',
            StatusPermintaanDarah::Selesai->value => 'success',
            StatusPermintaanDarah::Ditolak->value => 'danger',
            StatusPermintaanDarah::Dibatalkan->value => 'danger',
            default => 'gray',
        };
    }

    private static function statusValue(PermintaanDarah $record): string
    {
        return $record->status instanceof StatusPermintaanDarah
            ? $record->status->value
            : (string) $record->status;
    }

    private static function dapatDialokasiOtomatis(PermintaanDarah $record): bool
    {
        if (
            ! in_array(
                self::statusValue($record),
                [
                    StatusPermintaanDarah::Disetujui->value,
                    StatusPermintaanDarah::MenungguStok->value,
                    StatusPermintaanDarah::SiapDiambil->value,
                ],
                true
            )
        ) {
            return false;
        }

        if ($record->distribusi()->exists()) {
            return false;
        }

        return $record->sisaKebutuhanKantong() > 0;
    }

    private static function dapatBuatDistribusi(PermintaanDarah $record): bool
    {
        if ($record->distribusi()->exists()) {
            return false;
        }

        if (method_exists($record, 'dapatDibuatkanDistribusi')) {
            return $record->dapatDibuatkanDistribusi();
        }

        return self::statusValue($record) === StatusPermintaanDarah::SiapDiambil->value
            && $record->sisaKebutuhanKantong() === 0;
    }
}