<?php

namespace App\Filament\Admin\Resources;

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusItemPermintaanDarah;
use App\Enums\StatusPermintaanDarah;
use App\Filament\Admin\Resources\DistribusiDarahResource\Pages;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Services\LayananDistribusiDarah;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistribusiDarahResource extends Resource
{
    protected static ?string $model =
        DistribusiDarah::class;

    protected static ?string $navigationIcon =
        'heroicon-o-truck';

    protected static ?string $navigationLabel =
        'Distribusi Darah';

    protected static ?string $modelLabel =
        'Distribusi Darah';

    protected static ?string $pluralModelLabel =
        'Distribusi Darah';

    protected static ?string $navigationGroup =
        'Permintaan dan Distribusi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Permintaan Darah'
                )
                    ->description(
                        'Distribusi hanya dapat dibuat untuk permintaan yang sudah siap diambil.'
                    )
                    ->schema([
                        Forms\Components\Select::make(
                            'permintaan_darah_id'
                        )
                            ->label('Permintaan Darah')
                            ->options(
                                fn (): array => PermintaanDarah::query()
                                    ->where(
                                        'status',
                                        StatusPermintaanDarah
                                            ::SiapDiambil
                                            ->value
                                    )
                                    ->whereDoesntHave(
                                        'distribusi'
                                    )
                                    ->with([
                                        'rumahSakit',
                                        'itemAktif',
                                    ])
                                    ->orderByDesc('created_at')
                                    ->get()
                                    ->filter(
                                        fn (
                                            PermintaanDarah $record
                                        ): bool => $record
                                            ->kebutuhanSudahTerpenuhi()
                                    )
                                    ->mapWithKeys(
                                        fn (
                                            PermintaanDarah $record
                                        ): array => [
                                            $record->id => sprintf(
                                                '%s — %s — %s%s — %d kantong',
                                                $record
                                                    ->nomor_permintaan,
                                                $record
                                                    ->rumahSakit
                                                    ->nama_rumah_sakit,
                                                $record
                                                    ->golongan_darah
                                                    ->label(),
                                                $record
                                                    ->rhesus
                                                    ->simbol(),
                                                $record
                                                    ->jumlah_kantong,
                                            ),
                                        ]
                                    )
                                    ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visibleOn('create'),

                        Forms\Components\Placeholder::make(
                            'permintaan_info'
                        )
                            ->label('Permintaan Darah')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? sprintf(
                                        '%s — %s',
                                        $record
                                            ->permintaan
                                            ->nomor_permintaan,
                                        $record
                                            ->permintaan
                                            ->rumahSakit
                                            ->nama_rumah_sakit,
                                    )
                                    : '-'
                            )
                            ->hiddenOn('create'),

                        Forms\Components\Placeholder::make(
                            'nomor_distribusi_info'
                        )
                            ->label('Nomor Distribusi')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? $record
                                        ->nomor_distribusi
                                    : 'Dibuat otomatis setelah disimpan'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Jadwal Penyerahan'
                )
                    ->schema([
                        Forms\Components\DateTimePicker::make(
                            'dijadwalkan_pada'
                        )
                            ->label('Dijadwalkan Pada')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minDate(now()),

                        Forms\Components\Textarea::make(
                            'catatan'
                        )
                            ->label('Catatan Distribusi')
                            ->rows(3)
                            ->maxLength(3000),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(
                    'Status Distribusi'
                )
                    ->hiddenOn('create')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_info'
                        )
                            ->label('Status')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? $record->status->label()
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'penyiap_info'
                        )
                            ->label('Disiapkan Oleh')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->penyiap
                                            ?->name
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'diserahkan_pada_info'
                        )
                            ->label('Diserahkan Pada')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->diserahkan_pada
                                            ?->format(
                                                'd M Y H:i'
                                            )
                                        ?? '-'
                                    )
                                    : '-'
                            ),

                        Forms\Components\Placeholder::make(
                            'penerima_info'
                        )
                            ->label('Penerima')
                            ->content(
                                fn (
                                    ?DistribusiDarah $record
                                ): string => $record
                                    ? (
                                        $record
                                            ->nama_penerima
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
                    ->columns(2),
            ]);
    }

    public static function infolist(
        Infolist $infolist
    ): Infolist {
        return $infolist
            ->schema([
                InfolistSection::make(
                    'Informasi Distribusi'
                )
                    ->schema([
                        TextEntry::make(
                            'nomor_distribusi'
                        )
                            ->label('Nomor Distribusi')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (
                                    StatusDistribusiDarah|string $state
                                ): string => self
                                    ::statusEnum($state)
                                    ->label()
                            )
                            ->color(
                                fn (
                                    StatusDistribusiDarah|string $state
                                ): string => self
                                    ::statusEnum($state)
                                    ->warna()
                            ),

                        TextEntry::make(
                            'dijadwalkan_pada'
                        )
                            ->label('Dijadwalkan Pada')
                            ->dateTime('d M Y H:i'),

                        TextEntry::make(
                            'penyiap.name'
                        )
                            ->label('Disiapkan Oleh'),

                        TextEntry::make(
                            'permintaan.nomor_permintaan'
                        )
                            ->label('Nomor Permintaan')
                            ->copyable(),

                        TextEntry::make(
                            'permintaan.rumahSakit.nama_rumah_sakit'
                        )
                            ->label('Rumah Sakit'),

                        TextEntry::make(
                            'permintaan.rumahSakit.alamat'
                        )
                            ->label('Alamat Rumah Sakit')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                InfolistSection::make(
                    'Kebutuhan Darah'
                )
                    ->schema([
                        TextEntry::make(
                            'permintaan.golongan_darah'
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

                        TextEntry::make(
                            'permintaan.rhesus'
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
                            ),

                        TextEntry::make(
                            'permintaan.jumlah_kantong'
                        )
                            ->label('Jumlah')
                            ->suffix(' kantong'),

                        TextEntry::make(
                            'permintaan.referensi_pasien'
                        )
                            ->label('Referensi Pasien'),
                    ])
                    ->columns(4),

                InfolistSection::make(
                    'Kantong Darah'
                )
                    ->schema([
                        RepeatableEntry::make(
                            'permintaan.itemDistribusi'
                        )
                            ->label('')
                            ->schema([
                                TextEntry::make(
                                    'kantongDarah.kode_kantong'
                                )
                                    ->label('Kode Kantong')
                                    ->copyable(),

                                TextEntry::make(
                                    'kantongDarah.golongan_darah'
                                )
                                    ->label('Golongan'),

                                TextEntry::make(
                                    'kantongDarah.rhesus'
                                )
                                    ->label('Rhesus'),

                                TextEntry::make(
                                    'kantongDarah.volume_ml'
                                )
                                    ->label('Volume')
                                    ->suffix(' ml'),

                                TextEntry::make('status')
                                    ->label('Status Item')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn (
                                            StatusItemPermintaanDarah|string $state
                                        ): string => $state
                                            instanceof
                                            StatusItemPermintaanDarah
                                                ? $state->label()
                                                : StatusItemPermintaanDarah
                                                    ::from(
                                                        $state
                                                    )
                                                    ->label()
                                    ),
                            ])
                            ->columns(5),
                    ]),

                InfolistSection::make(
                    'Serah Terima'
                )
                    ->schema([
                        TextEntry::make(
                            'penyerah.name'
                        )
                            ->label('Diserahkan Oleh')
                            ->placeholder('-'),

                        TextEntry::make(
                            'diserahkan_pada'
                        )
                            ->label('Diserahkan Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),

                        TextEntry::make(
                            'nama_penerima'
                        )
                            ->label('Nama Penerima')
                            ->placeholder('-'),

                        TextEntry::make(
                            'jabatan_penerima'
                        )
                            ->label('Jabatan Penerima')
                            ->placeholder('-'),

                        TextEntry::make(
                            'nomor_identitas_penerima'
                        )
                            ->label('Nomor Identitas')
                            ->placeholder('-'),

                        TextEntry::make(
                            'path_bukti_serah_terima'
                        )
                            ->label('Bukti Serah Terima')
                            ->formatStateUsing(
                                fn (
                                    ?string $state
                                ): string => filled($state)
                                    ? 'Lihat dokumen'
                                    : 'Belum diunggah'
                            )
                            ->url(
                                fn (
                                    DistribusiDarah $record
                                ): ?string => filled(
                                    $record
                                        ->path_bukti_serah_terima
                                )
                                    ? asset(
                                        'storage/'
                                        . ltrim(
                                            $record
                                                ->path_bukti_serah_terima,
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
                    'nomor_distribusi'
                )
                    ->label('Nomor Distribusi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'permintaan.nomor_permintaan'
                )
                    ->label('Permintaan')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make(
                    'permintaan.rumahSakit.nama_rumah_sakit'
                )
                    ->label('Rumah Sakit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'permintaan.jumlah_kantong'
                )
                    ->label('Jumlah')
                    ->suffix(' kantong'),

                Tables\Columns\TextColumn::make(
                    'status'
                )
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            StatusDistribusiDarah|string $state
                        ): string => self
                            ::statusEnum($state)
                            ->label()
                    )
                    ->color(
                        fn (
                            StatusDistribusiDarah|string $state
                        ): string => self
                            ::statusEnum($state)
                            ->warna()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'dijadwalkan_pada'
                )
                    ->label('Dijadwalkan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'diserahkan_pada'
                )
                    ->label('Diserahkan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'nama_penerima'
                )
                    ->label('Penerima')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'penyiap.name'
                )
                    ->label('Disiapkan Oleh')
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
                    'status'
                )
                    ->label('Status Distribusi')
                    ->options(
                        StatusDistribusiDarah::options()
                    ),

                Tables\Filters\SelectFilter::make(
                    'permintaan_darah_id'
                )
                    ->label('Permintaan Darah')
                    ->relationship(
                        'permintaan',
                        'nomor_permintaan'
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
                            DistribusiDarah $record
                        ): bool => $record->dapatDiubah()
                    ),

                Tables\Actions\Action::make(
                    'tandai_siap'
                )
                    ->label('Tandai Siap')
                    ->icon(
                        'heroicon-o-check-badge'
                    )
                    ->color('info')
                    ->visible(
                        fn (
                            DistribusiDarah $record
                        ): bool => $record
                            ->dapatDitandaiSiap()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Tandai Distribusi Siap'
                    )
                    ->modalDescription(
                        'Pastikan seluruh kantong darah sudah diperiksa dan siap diserahkan.'
                    )
                    ->action(
                        function (
                            DistribusiDarah $record
                        ): void {
                            app(
                                LayananDistribusiDarah::class
                            )->tandaiSiap(
                                distribusi: $record
                            );

                            Notification::make()
                                ->title(
                                    'Distribusi siap diserahkan.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'selesaikan'
                )
                    ->label('Serahkan')
                    ->icon(
                        'heroicon-o-hand-raised'
                    )
                    ->color('success')
                    ->visible(
                        fn (
                            DistribusiDarah $record
                        ): bool => $record
                            ->dapatDiselesaikan()
                    )
                    ->form([
                        Forms\Components\TextInput::make(
                            'nama_penerima'
                        )
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make(
                            'jabatan_penerima'
                        )
                            ->label('Jabatan Penerima')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\TextInput::make(
                            'nomor_identitas_penerima'
                        )
                            ->label(
                                'Nomor Identitas Penerima'
                            )
                            ->maxLength(100),

                        Forms\Components\FileUpload::make(
                            'path_bukti_serah_terima'
                        )
                            ->label('Bukti Serah Terima')
                            ->disk('public')
                            ->directory(
                                'bukti-serah-terima-darah'
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
                    ])
                    ->modalHeading(
                        'Selesaikan Distribusi Darah'
                    )
                    ->modalDescription(
                        'Setelah diselesaikan, kantong darah dan permintaan tidak dapat dikembalikan melalui proses biasa.'
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            DistribusiDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananDistribusiDarah::class
                            )->selesaikan(
                                distribusi: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                                data: $data,
                            );

                            Notification::make()
                                ->title(
                                    'Distribusi darah berhasil diselesaikan.'
                                )
                                ->success()
                                ->send();
                        }
                    ),

                Tables\Actions\Action::make(
                    'batalkan'
                )
                    ->label('Batalkan')
                    ->icon(
                        'heroicon-o-x-circle'
                    )
                    ->color('danger')
                    ->visible(
                        fn (
                            DistribusiDarah $record
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
                    ->modalHeading(
                        'Batalkan Distribusi Darah'
                    )
                    ->modalDescription(
                        'Seluruh alokasi aktif akan dilepaskan dan kantong dikembalikan ke stok.'
                    )
                    ->requiresConfirmation()
                    ->action(
                        function (
                            DistribusiDarah $record,
                            array $data
                        ): void {
                            app(
                                LayananDistribusiDarah::class
                            )->batalkan(
                                distribusi: $record,
                                petugasId: (int) Filament
                                    ::auth()
                                    ->id(),
                                alasan: $data['alasan'],
                            );

                            Notification::make()
                                ->title(
                                    'Distribusi darah dibatalkan.'
                                )
                                ->warning()
                                ->send();
                        }
                    ),
            ])
            ->bulkActions([])
            ->defaultSort(
                'dijadwalkan_pada',
                'desc'
            )
            ->emptyStateHeading(
                'Belum ada distribusi darah'
            )
            ->emptyStateDescription(
                'Distribusi dapat dibuat setelah seluruh kebutuhan permintaan darah terpenuhi.'
            )
            ->emptyStateIcon(
                'heroicon-o-truck'
            );
    }

    public static function canEdit(
        Model $record
    ): bool {
        return $record instanceof DistribusiDarah
            && $record->dapatDiubah();
    }

    public static function canDelete(
        Model $record
    ): bool {
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
                Pages\ListDistribusiDarahs
                    ::route('/'),

            'create' =>
                Pages\CreateDistribusiDarah
                    ::route('/create'),

            'view' =>
                Pages\ViewDistribusiDarah
                    ::route('/{record}'),

            'edit' =>
                Pages\EditDistribusiDarah
                    ::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->nomor_distribusi;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Permintaan' =>
                $record
                    ->permintaan
                    ?->nomor_permintaan
                ?? '-',

            'Rumah Sakit' =>
                $record
                    ->permintaan
                    ?->rumahSakit
                    ?->nama_rumah_sakit
                ?? '-',

            'Status' =>
                $record->status->label(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'permintaan.rumahSakit',
                'permintaan.itemDistribusi.kantongDarah',
                'penyiap',
                'penyerah',
            ]);
    }

    private static function statusEnum(
        StatusDistribusiDarah|string $status
    ): StatusDistribusiDarah {
        return $status instanceof
            StatusDistribusiDarah
                ? $status
                : StatusDistribusiDarah::from(
                    $status
                );
    }
}