<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LokasiDonorResource\Pages;
use App\Models\LokasiDonor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LokasiDonorResource extends Resource
{
    protected static ?string $model = LokasiDonor::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Lokasi Donor';

    protected static ?string $modelLabel = 'Lokasi Donor';

    protected static ?string $pluralModelLabel = 'Lokasi Donor';

    protected static ?string $navigationGroup = 'Manajemen Donor';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->schema(self::schemaInformasiLokasi())
                    ->columns(2),

                Forms\Components\Section::make('Peta dan Koordinat')
                    ->description('Isi latitude dan longitude agar lokasi tampil lebih akurat di portal pendonor.')
                    ->schema(self::schemaPeta())
                    ->columns(2),

                Forms\Components\Section::make('Catatan')
                    ->schema(self::schemaCatatan())
                    ->columns(1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Lokasi')
                    ->schema(self::infolistInformasiLokasi())
                    ->columns(2),

                InfolistSection::make('Peta dan Kontak')
                    ->schema(self::infolistPeta())
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters(self::tableFilters())
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                Tables\Actions\Action::make('buka_google_maps')
                    ->label('Maps')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->url(
                        fn (LokasiDonor $record): string => self::urlMaps($record)
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort(
                self::kolomAda('nama')
                    ? 'nama'
                    : (
                        self::kolomAda('nama_lokasi')
                            ? 'nama_lokasi'
                            : 'created_at'
                    )
            )
            ->emptyStateHeading('Belum ada lokasi donor')
            ->emptyStateDescription('Tambahkan lokasi donor agar pendonor dapat melihat alamat dan peta lokasi kegiatan.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
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
            'index' => Pages\ListLokasiDonors::route('/'),
            'create' => Pages\CreateLokasiDonor::route('/create'),
            'view' => Pages\ViewLokasiDonor::route('/{record}'),
            'edit' => Pages\EditLokasiDonor::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return self::namaLokasi($record);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Alamat' => self::alamatLokasi($record),
            'Wilayah' => self::wilayahLokasi($record),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function schemaInformasiLokasi(): array
    {
        $schema = [];

        if (self::kolomAda('nama')) {
            $schema[] = Forms\Components\TextInput::make('nama')
                ->label('Nama Lokasi')
                ->placeholder('Contoh: Unit Donor Darah Pusat')
                ->required()
                ->maxLength(255);
        }

        if (self::kolomAda('nama_lokasi')) {
            $schema[] = Forms\Components\TextInput::make('nama_lokasi')
                ->label('Nama Lokasi')
                ->placeholder('Contoh: Unit Donor Darah Pusat')
                ->required()
                ->maxLength(255);
        }

        if (self::kolomAda('nomor_telepon')) {
            $schema[] = Forms\Components\TextInput::make('nomor_telepon')
                ->label('Nomor Telepon')
                ->placeholder('Contoh: 021xxxxxxx / 08xxxxxxxxxx')
                ->tel()
                ->maxLength(30);
        }

        if (self::kolomAda('alamat')) {
            $schema[] = Forms\Components\Textarea::make('alamat')
                ->label('Alamat Lengkap')
                ->placeholder('Tulis alamat lengkap lokasi donor')
                ->required()
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull();
        }

        if (self::kolomAda('alamat_lengkap')) {
            $schema[] = Forms\Components\Textarea::make('alamat_lengkap')
                ->label('Alamat Lengkap')
                ->placeholder('Tulis alamat lengkap lokasi donor')
                ->required()
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull();
        }

        if (self::kolomAda('provinsi')) {
            $schema[] = Forms\Components\TextInput::make('provinsi')
                ->label('Provinsi')
                ->placeholder('Contoh: DKI Jakarta')
                ->required()
                ->maxLength(100);
        }

        if (self::kolomAda('kota')) {
            $schema[] = Forms\Components\TextInput::make('kota')
                ->label('Kota/Kabupaten')
                ->placeholder('Contoh: Jakarta Selatan')
                ->required()
                ->maxLength(100);
        }

        if (self::kolomAda('kabupaten')) {
            $schema[] = Forms\Components\TextInput::make('kabupaten')
                ->label('Kota/Kabupaten')
                ->placeholder('Contoh: Jakarta Selatan')
                ->required()
                ->maxLength(100);
        }

        if (self::kolomAda('status')) {
            $schema[] = Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Aktif',
                    'aktif' => 'Aktif',
                    'inactive' => 'Tidak Aktif',
                    'nonaktif' => 'Tidak Aktif',
                ])
                ->default('active')
                ->required();
        }

        return $schema;
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function schemaPeta(): array
    {
        $schema = [];

        if (self::kolomAda('latitude')) {
            $schema[] = Forms\Components\TextInput::make('latitude')
                ->label('Latitude')
                ->placeholder('Contoh: -6.2607000')
                ->numeric()
                ->minValue(-90)
                ->maxValue(90)
                ->helperText('Bisa diambil dari Google Maps.');
        }

        if (self::kolomAda('longitude')) {
            $schema[] = Forms\Components\TextInput::make('longitude')
                ->label('Longitude')
                ->placeholder('Contoh: 106.7816000')
                ->numeric()
                ->minValue(-180)
                ->maxValue(180)
                ->helperText('Bisa diambil dari Google Maps.');
        }

        if (self::kolomAda('url_google_maps')) {
            $schema[] = Forms\Components\TextInput::make('url_google_maps')
                ->label('Link Google Maps')
                ->placeholder('https://maps.google.com/...')
                ->url()
                ->maxLength(2048)
                ->columnSpanFull();
        }

        return $schema;
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function schemaCatatan(): array
    {
        $schema = [];

        if (self::kolomAda('catatan_lokasi')) {
            $schema[] = Forms\Components\Textarea::make('catatan_lokasi')
                ->label('Catatan Lokasi')
                ->placeholder('Contoh: parkir tersedia, dekat pintu utama, lantai 2, dan lain-lain.')
                ->rows(4)
                ->maxLength(2000);
        }

        return $schema;
    }

    /**
     * @return array<int, TextEntry>
     */
    private static function infolistInformasiLokasi(): array
    {
        $schema = [];

        if (self::kolomAda('nama')) {
            $schema[] = TextEntry::make('nama')
                ->label('Nama Lokasi');
        }

        if (self::kolomAda('nama_lokasi')) {
            $schema[] = TextEntry::make('nama_lokasi')
                ->label('Nama Lokasi');
        }

        if (self::kolomAda('alamat')) {
            $schema[] = TextEntry::make('alamat')
                ->label('Alamat')
                ->placeholder('-')
                ->columnSpanFull();
        }

        if (self::kolomAda('alamat_lengkap')) {
            $schema[] = TextEntry::make('alamat_lengkap')
                ->label('Alamat')
                ->placeholder('-')
                ->columnSpanFull();
        }

        if (self::kolomAda('kota')) {
            $schema[] = TextEntry::make('kota')
                ->label('Kota/Kabupaten')
                ->placeholder('-');
        }

        if (self::kolomAda('kabupaten')) {
            $schema[] = TextEntry::make('kabupaten')
                ->label('Kota/Kabupaten')
                ->placeholder('-');
        }

        if (self::kolomAda('provinsi')) {
            $schema[] = TextEntry::make('provinsi')
                ->label('Provinsi')
                ->placeholder('-');
        }

        if (self::kolomAda('status')) {
            $schema[] = TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => self::labelStatus($state))
                ->color(fn (?string $state): string => self::warnaStatus($state));
        }

        return $schema;
    }

    /**
     * @return array<int, TextEntry>
     */
    private static function infolistPeta(): array
    {
        $schema = [];

        if (self::kolomAda('nomor_telepon')) {
            $schema[] = TextEntry::make('nomor_telepon')
                ->label('Nomor Telepon')
                ->placeholder('-');
        }

        if (self::kolomAda('latitude')) {
            $schema[] = TextEntry::make('latitude')
                ->label('Latitude')
                ->placeholder('-');
        }

        if (self::kolomAda('longitude')) {
            $schema[] = TextEntry::make('longitude')
                ->label('Longitude')
                ->placeholder('-');
        }

        if (self::kolomAda('url_google_maps')) {
            $schema[] = TextEntry::make('url_google_maps')
                ->label('Link Google Maps')
                ->url(fn (LokasiDonor $record): string => self::urlMaps($record))
                ->openUrlInNewTab()
                ->placeholder('-')
                ->columnSpanFull();
        }

        if (self::kolomAda('catatan_lokasi')) {
            $schema[] = TextEntry::make('catatan_lokasi')
                ->label('Catatan Lokasi')
                ->placeholder('-')
                ->columnSpanFull();
        }

        return $schema;
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    private static function tableColumns(): array
    {
        $columns = [];

        if (self::kolomAda('nama')) {
            $columns[] = Tables\Columns\TextColumn::make('nama')
                ->label('Nama Lokasi')
                ->searchable()
                ->sortable();
        }

        if (self::kolomAda('nama_lokasi')) {
            $columns[] = Tables\Columns\TextColumn::make('nama_lokasi')
                ->label('Nama Lokasi')
                ->searchable()
                ->sortable();
        }

        if (self::kolomAda('alamat')) {
            $columns[] = Tables\Columns\TextColumn::make('alamat')
                ->label('Alamat')
                ->limit(45)
                ->searchable();
        }

        if (self::kolomAda('alamat_lengkap')) {
            $columns[] = Tables\Columns\TextColumn::make('alamat_lengkap')
                ->label('Alamat')
                ->limit(45)
                ->searchable();
        }

        if (self::kolomAda('kota')) {
            $columns[] = Tables\Columns\TextColumn::make('kota')
                ->label('Kota')
                ->searchable()
                ->sortable();
        }

        if (self::kolomAda('kabupaten')) {
            $columns[] = Tables\Columns\TextColumn::make('kabupaten')
                ->label('Kota')
                ->searchable()
                ->sortable();
        }

        if (self::kolomAda('provinsi')) {
            $columns[] = Tables\Columns\TextColumn::make('provinsi')
                ->label('Provinsi')
                ->searchable()
                ->sortable();
        }

        if (self::kolomAda('nomor_telepon')) {
            $columns[] = Tables\Columns\TextColumn::make('nomor_telepon')
                ->label('Kontak')
                ->placeholder('-')
                ->toggleable();
        }

        if (self::kolomAda('status')) {
            $columns[] = Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => self::labelStatus($state))
                ->color(fn (?string $state): string => self::warnaStatus($state))
                ->sortable();
        }

        if (self::kolomAda('created_at')) {
            $columns[] = Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        return $columns;
    }

    /**
     * @return array<int, Tables\Filters\BaseFilter>
     */
    private static function tableFilters(): array
    {
        $filters = [];

        if (self::kolomAda('status')) {
            $filters[] = Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Aktif',
                    'aktif' => 'Aktif',
                    'inactive' => 'Tidak Aktif',
                    'nonaktif' => 'Tidak Aktif',
                ]);
        }

        return $filters;
    }

    private static function tabel(): string
    {
        return (new LokasiDonor())->getTable();
    }

    private static function kolomAda(string $column): bool
    {
        return Schema::hasColumn(self::tabel(), $column);
    }

    private static function namaLokasi(Model $record): string
    {
        return (string) (
            $record->nama
            ?? $record->nama_lokasi
            ?? 'Lokasi Donor'
        );
    }

    private static function alamatLokasi(Model $record): string
    {
        return (string) (
            $record->alamat
            ?? $record->alamat_lengkap
            ?? '-'
        );
    }

    private static function wilayahLokasi(Model $record): string
    {
        $wilayah = collect([
            $record->kota ?? $record->kabupaten ?? null,
            $record->provinsi ?? null,
        ])
            ->filter()
            ->implode(', ');

        return $wilayah !== '' ? $wilayah : '-';
    }

    private static function urlMaps(LokasiDonor $record): string
    {
        if (
            self::kolomAda('url_google_maps')
            && filled($record->url_google_maps)
        ) {
            return (string) $record->url_google_maps;
        }

        if (
            self::kolomAda('latitude')
            && self::kolomAda('longitude')
            && filled($record->latitude)
            && filled($record->longitude)
        ) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode($record->latitude . ',' . $record->longitude);
        }

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode(
                collect([
                    self::namaLokasi($record),
                    self::alamatLokasi($record),
                    self::wilayahLokasi($record),
                ])
                    ->filter(fn (string $value): bool => $value !== '-')
                    ->implode(', ')
            );
    }

    private static function labelStatus(?string $state): string
    {
        return match ($state) {
            'active', 'aktif', 'published', 'dipublikasikan' => 'Aktif',
            'inactive', 'nonaktif', 'draft' => 'Tidak Aktif',
            default => $state !== null && $state !== '' ? $state : '-',
        };
    }

    private static function warnaStatus(?string $state): string
    {
        return match ($state) {
            'active', 'aktif', 'published', 'dipublikasikan' => 'success',
            'inactive', 'nonaktif', 'draft' => 'gray',
            default => 'gray',
        };
    }
}