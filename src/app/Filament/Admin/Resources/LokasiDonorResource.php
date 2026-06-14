<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LokasiDonorResource\Pages;
use App\Models\LokasiDonor;
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

class LokasiDonorResource extends Resource
{
    protected static ?string $model = LokasiDonor::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Lokasi Donor';

    protected static ?string $modelLabel = 'Lokasi Donor';

    protected static ?string $pluralModelLabel = 'Lokasi Donor';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->description(
                        'Masukkan identitas dan keterangan lokasi kegiatan donor darah.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lokasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Aula Kecamatan Setiabudi'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(
                                'Slug dibuat otomatis berdasarkan nama lokasi.'
                            )
                            ->placeholder('Dibuat otomatis'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->maxLength(2000)
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

                Forms\Components\Section::make('Koordinat Peta')
                    ->description(
                        'Latitude dan longitude digunakan untuk menampilkan lokasi pada peta.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->step('0.0000001')
                            ->placeholder('-6.2000000')
                            ->helperText(
                                'Nilai latitude berada di antara -90 sampai 90.'
                            ),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->step('0.0000001')
                            ->placeholder('106.8166667')
                            ->helperText(
                                'Nilai longitude berada di antara -180 sampai 180.'
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kontak Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kontak')
                            ->label('Nama Kontak')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nomor_kontak')
                            ->label('Nomor Kontak')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('081234567890'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('aktif')
                            ->label('Lokasi Aktif')
                            ->helperText(
                                'Lokasi aktif dapat digunakan pada jadwal donor.'
                            )
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi Lokasi')
                    ->schema([
                        TextEntry::make('nama')
                            ->label('Nama Lokasi'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable(),

                        IconEntry::make('aktif')
                            ->label('Status Aktif')
                            ->boolean(),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
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

                InfolistSection::make('Koordinat dan Kontak')
                    ->schema([
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->copyable(),

                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->copyable(),

                        TextEntry::make('nama_kontak')
                            ->label('Nama Kontak')
                            ->placeholder('-'),

                        TextEntry::make('nomor_kontak')
                            ->label('Nomor Kontak')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                InfolistSection::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('pembuat.name')
                            ->label('Dibuat Oleh'),

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
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('kota')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nama_kontak')
                    ->label('Kontak')
                    ->description(
                        fn (LokasiDonor $record): ?string => $record->nomor_kontak
                    )
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembuat.name')
                    ->label('Dibuat Oleh')
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
                Tables\Filters\TernaryFilter::make('aktif')
                    ->label('Status Lokasi')
                    ->placeholder('Semua Lokasi')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\SelectFilter::make('provinsi')
                    ->label('Provinsi')
                    ->options(
                        fn (): array => LokasiDonor::query()
                            ->whereNotNull('provinsi')
                            ->orderBy('provinsi')
                            ->pluck('provinsi', 'provinsi')
                            ->all()
                    )
                    ->searchable(),

                Tables\Filters\SelectFilter::make('kota')
                    ->label('Kota/Kabupaten')
                    ->options(
                        fn (): array => LokasiDonor::query()
                            ->whereNotNull('kota')
                            ->orderBy('kota')
                            ->pluck('kota', 'kota')
                            ->all()
                    )
                    ->searchable(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan'),

                Tables\Actions\ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\RestoreBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada lokasi donor')
            ->emptyStateDescription(
                'Tambahkan lokasi yang nantinya digunakan untuk menyelenggarakan kegiatan donor darah.'
            )
            ->emptyStateIcon('heroicon-o-map-pin');
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

    public static function getGlobalSearchResultTitle(
        Model $record
    ): string {
        return $record->nama;
    }

    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Kota/Kabupaten' => $record->kota,
            'Alamat' => $record->alamat,
            'Status' => $record->aktif
                ? 'Aktif'
                : 'Tidak Aktif',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('pembuat')
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}