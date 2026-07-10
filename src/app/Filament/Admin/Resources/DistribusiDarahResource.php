<?php

namespace App\Filament\Admin\Resources;

use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource\Pages;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Services\LayananDistribusiDarah;
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

class DistribusiDarahResource extends Resource
{
    protected static ?string $model = DistribusiDarah::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Manajemen Donor Darah';

    protected static ?string $navigationLabel = 'Distribusi Kantong Darah';

    protected static ?string $modelLabel = 'Distribusi Kantong Darah';

    protected static ?string $pluralModelLabel = 'Distribusi Kantong Darah';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Distribusi')
                    ->description('Data utama distribusi kantong darah untuk pemohon donor.')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_distribusi')
                            ->label('Nomor Distribusi')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Dibuat otomatis oleh sistem'),

                        Forms\Components\Select::make('permintaan_darah_id')
                            ->label('Pengajuan Kebutuhan Donor')
                            ->options(function (?DistribusiDarah $record = null): array {
                                return PermintaanDarah::query()
                                    ->with([
                                        'rumahSakit',
                                    ])
                                    ->where(function (Builder $query) use ($record): void {
                                        $query->where(
                                            'status',
                                            StatusPermintaanDarah::SiapDiambil->value
                                        );

                                        if ($record?->permintaan_darah_id !== null) {
                                            $query->orWhere(
                                                'id',
                                                $record->permintaan_darah_id
                                            );
                                        }
                                    })
                                    ->when(
                                        $record === null,
                                        function (Builder $query): Builder {
                                            return $query->whereNotIn(
                                                'id',
                                                DistribusiDarah::query()
                                                    ->whereNotNull('permintaan_darah_id')
                                                    ->select('permintaan_darah_id')
                                            );
                                        }
                                    )
                                    ->latest()
                                    ->get()
                                    ->mapWithKeys(function (PermintaanDarah $permintaan): array {
                                        $pemohon = $permintaan->rumahSakit?->nama_rumah_sakit
                                            ?? 'Pemohon tidak ditemukan';

                                        return [
                                            $permintaan->id => $permintaan->nomor_permintaan
                                                . ' - '
                                                . $pemohon
                                                . ' - '
                                                . self::formatGolonganDarah($permintaan)
                                                . ' - '
                                                . number_format((int) $permintaan->jumlah_kantong)
                                                . ' kantong',
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->default(function (): ?int {
                                $permintaanDarahId = request()->integer('permintaan_darah_id');

                                return $permintaanDarahId > 0
                                    ? $permintaanDarahId
                                    : null;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (?DistribusiDarah $record = null): bool => $record !== null)
                            ->dehydrated(true)
                            ->helperText('Hanya pengajuan berstatus Siap Diambil yang bisa dibuatkan distribusi.'),

                        Forms\Components\DateTimePicker::make('dijadwalkan_pada')
                            ->label('Jadwal Distribusi')
                            ->seconds(false)
                            ->native(false)
                            ->default(fn () => now()->addHour())
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Distribusi')
                            ->options(StatusDistribusiDarah::options())
                            ->default(StatusDistribusiDarah::Dijadwalkan->value)
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?DistribusiDarah $record = null): bool => $record !== null),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Penerima')
                    ->description('Data penerima akan terisi setelah distribusi diselesaikan.')
                    ->schema([
                        Forms\Components\TextInput::make('nama_penerima')
                            ->label('Nama Penerima')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('-'),

                        Forms\Components\TextInput::make('jabatan_penerima')
                            ->label('Jabatan Penerima')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('-'),

                        Forms\Components\TextInput::make('nomor_identitas_penerima')
                            ->label('Nomor Identitas Penerima')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('-'),

                        Forms\Components\DateTimePicker::make('diserahkan_pada')
                            ->label('Diserahkan Pada')
                            ->seconds(false)
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\FileUpload::make('path_bukti_serah_terima')
                            ->label('Bukti Serah Terima')
                            ->disk('public')
                            ->directory('bukti-serah-terima-distribusi')
                            ->downloadable()
                            ->openable()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (?DistribusiDarah $record = null): bool => $record !== null),

                Forms\Components\Section::make('Pembatalan')
                    ->description('Data pembatalan distribusi.')
                    ->schema([
                        Forms\Components\DateTimePicker::make('dibatalkan_pada')
                            ->label('Dibatalkan Pada')
                            ->seconds(false)
                            ->native(false)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('alasan_pembatalan')
                            ->label('Alasan Pembatalan')
                            ->rows(4)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (?DistribusiDarah $record = null): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_distribusi')
                    ->label('Nomor Distribusi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('permintaan.nomor_permintaan')
                    ->label('Nomor Pengajuan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permintaan.rumahSakit.nama_rumah_sakit')
                    ->label('Pemohon Donor')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('golongan_darah')
                    ->label('Gol. Darah')
                    ->state(function (DistribusiDarah $record): string {
                        return self::formatGolonganDarah($record->permintaan);
                    })
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('permintaan.jumlah_kantong')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state) . ' kantong')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dijadwalkan_pada')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::labelStatusDistribusi($state))
                    ->color(fn ($state): string => self::warnaStatusDistribusi($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_penerima')
                    ->label('Penerima')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('diserahkan_pada')
                    ->label('Diserahkan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

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
                    ->label('Status Distribusi')
                    ->options(StatusDistribusiDarah::options()),

                Tables\Filters\Filter::make('belum_selesai')
                    ->label('Belum Selesai')
                    ->query(function (Builder $query): Builder {
                        return $query->whereIn('status', [
                            StatusDistribusiDarah::Dijadwalkan->value,
                            StatusDistribusiDarah::SiapDiserahkan->value,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(function (DistribusiDarah $record): bool {
                        return self::statusValue($record) === StatusDistribusiDarah::Dijadwalkan->value;
                    }),

                Tables\Actions\Action::make('lihat_pengajuan')
                    ->label('Lihat Pengajuan')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(function (DistribusiDarah $record): string {
                        return PermintaanDarahResource::getUrl('view', [
                            'record' => $record->permintaan_darah_id,
                        ]);
                    }),

                Tables\Actions\Action::make('siap_diserahkan')
                    ->label('Tandai Siap')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(function (DistribusiDarah $record): bool {
                        return self::statusValue($record) === StatusDistribusiDarah::Dijadwalkan->value;
                    })
                    ->action(function (DistribusiDarah $record): void {
                        try {
                            app(LayananDistribusiDarah::class)->tandaiSiap($record);
                        } catch (ValidationException $exception) {
                            self::notifikasiGagal(
                                exception: $exception,
                                judul: 'Distribusi gagal ditandai siap.'
                            );

                            return;
                        }

                        Notification::make()
                            ->title('Distribusi berhasil ditandai siap diserahkan.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('selesaikan')
                    ->label('Serahkan')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('nama_penerima')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('jabatan_penerima')
                            ->label('Jabatan Penerima')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nomor_identitas_penerima')
                            ->label('Nomor Identitas Penerima')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('path_bukti_serah_terima')
                            ->label('Bukti Serah Terima')
                            ->disk('public')
                            ->directory('bukti-serah-terima-distribusi')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(4096)
                            ->downloadable()
                            ->openable(),
                    ])
                    ->visible(function (DistribusiDarah $record): bool {
                        return in_array(
                            self::statusValue($record),
                            [
                                StatusDistribusiDarah::Dijadwalkan->value,
                                StatusDistribusiDarah::SiapDiserahkan->value,
                            ],
                            true
                        );
                    })
                    ->action(function (DistribusiDarah $record, array $data): void {
                        try {
                            app(LayananDistribusiDarah::class)->selesaikan(
                                distribusi: $record,
                                petugasId: (int) Filament::auth()->id(),
                                data: $data
                            );
                        } catch (ValidationException $exception) {
                            self::notifikasiGagal(
                                exception: $exception,
                                judul: 'Distribusi gagal diselesaikan.'
                            );

                            return;
                        }

                        Notification::make()
                            ->title('Distribusi berhasil diselesaikan.')
                            ->body('Pengajuan selesai dan kantong darah berubah menjadi Didistribusikan.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('alasan_pembatalan')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->rows(4)
                            ->minLength(10)
                            ->maxLength(5000),
                    ])
                    ->visible(function (DistribusiDarah $record): bool {
                        return in_array(
                            self::statusValue($record),
                            [
                                StatusDistribusiDarah::Dijadwalkan->value,
                                StatusDistribusiDarah::SiapDiserahkan->value,
                            ],
                            true
                        );
                    })
                    ->action(function (DistribusiDarah $record, array $data): void {
                        try {
                            app(LayananDistribusiDarah::class)->batalkan(
                                distribusi: $record,
                                petugasId: (int) Filament::auth()->id(),
                                alasan: (string) $data['alasan_pembatalan']
                            );
                        } catch (ValidationException $exception) {
                            self::notifikasiGagal(
                                exception: $exception,
                                judul: 'Distribusi gagal dibatalkan.'
                            );

                            return;
                        }

                        Notification::make()
                            ->title('Distribusi berhasil dibatalkan.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
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
                'permintaan',
                'permintaan.rumahSakit',
                'permintaan.itemAktif',
                'permintaan.itemAktif.kantongDarah',
                'disiapkanOleh',
                'diserahkanOleh',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistribusiDarah::route('/'),
            'create' => Pages\CreateDistribusiDarah::route('/create'),
            'view' => Pages\ViewDistribusiDarah::route('/{record}'),
            'edit' => Pages\EditDistribusiDarah::route('/{record}/edit'),
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

    private static function labelStatusDistribusi(mixed $state): string
    {
        if ($state instanceof StatusDistribusiDarah) {
            return $state->label();
        }

        return StatusDistribusiDarah::tryFrom((string) $state)?->label()
            ?? Str::of((string) $state)
                ->replace('_', ' ')
                ->replace('-', ' ')
                ->headline()
                ->toString();
    }

    private static function warnaStatusDistribusi(mixed $state): string
    {
        $value = $state instanceof StatusDistribusiDarah
            ? $state->value
            : (string) $state;

        return match ($value) {
            StatusDistribusiDarah::Dijadwalkan->value => 'warning',
            StatusDistribusiDarah::SiapDiserahkan->value => 'info',
            StatusDistribusiDarah::Selesai->value => 'success',
            StatusDistribusiDarah::Dibatalkan->value => 'danger',
            default => 'gray',
        };
    }

    private static function statusValue(DistribusiDarah $record): string
    {
        return $record->status instanceof StatusDistribusiDarah
            ? $record->status->value
            : (string) $record->status;
    }

    private static function notifikasiGagal(
        ValidationException $exception,
        string $judul
    ): void {
        $pesan = collect($exception->errors())
            ->flatten()
            ->first() ?? $exception->getMessage();

        Notification::make()
            ->title($judul)
            ->body((string) $pesan)
            ->danger()
            ->send();
    }
}