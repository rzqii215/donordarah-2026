<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = -2;

    public static function getNavigationBadge(): ?string
    {
        $jumlahBelumVerifikasi = User::query()
            ->whereNull('email_verified_at')
            ->count();

        return $jumlahBelumVerifikasi > 0
            ? (string) $jumlahBelumVerifikasi
            : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah pengguna yang belum memverifikasi email';
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'email',
            'roles.name',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(
        Model $record
    ): array {
        return [
            'Role' => $record->roles
                ->pluck('name')
                ->implode(', '),

            'Email' => (string) $record->email,

            'Verifikasi Email' => $record->hasVerifiedEmail()
                ? 'Terverifikasi'
                : 'Belum Terverifikasi',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->minLength(2)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->required(),

                                Forms\Components\FileUpload::make(
                                    'avatar_url'
                                )
                                    ->label('Avatar')
                                    ->image()
                                    ->optimize('webp')
                                    ->imageEditor()
                                    ->imagePreviewHeight('250')
                                    ->panelAspectRatio('7:2')
                                    ->panelLayout('integrated')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->required()
                                    ->email()
                                    ->unique(
                                        ignoreRecord: true
                                    )
                                    ->prefixIcon(
                                        'heroicon-m-envelope'
                                    )
                                    ->columnSpanFull(),

                                Forms\Components\DateTimePicker::make(
                                    'email_verified_at'
                                )
                                    ->label(
                                        'Email Terverifikasi Pada'
                                    )
                                    ->seconds(false)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visibleOn('edit')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make(
                                    'password'
                                )
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->revealable()
                                    ->confirmed()
                                    ->columnSpan(1)
                                    ->dehydrateStateUsing(
                                        fn (string $state): string =>
                                            Hash::make($state)
                                    )
                                    ->dehydrated(
                                        fn (?string $state): bool =>
                                            filled($state)
                                    )
                                    ->required(
                                        fn (string $context): bool =>
                                            $context === 'create'
                                    ),

                                Forms\Components\TextInput::make(
                                    'password_confirmation'
                                )
                                    ->label(
                                        'Konfirmasi Kata Sandi'
                                    )
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->columnSpan(1)
                                    ->required(
                                        fn (string $context): bool =>
                                            $context === 'create'
                                    ),
                            ]),
                    ]),

                Forms\Components\Section::make('Hak Akses')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->relationship(
                                'roles',
                                'name'
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(
                'created_at',
                'desc'
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->defaultImageUrl(
                        'https://www.gravatar.com/avatar/64e1b8d34f425d19e1ee2ea7236d3028?d=mp&r=g&s=250'
                    )
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->weight('medium')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make(
                    'email_verified_at'
                )
                    ->label('Verifikasi Email')
                    ->getStateUsing(
                        fn (User $record): string =>
                            $record->hasVerifiedEmail()
                                ? 'Terverifikasi'
                                : 'Belum Terverifikasi'
                    )
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            $state === 'Terverifikasi'
                                ? 'success'
                                : 'danger'
                    )
                    ->icon(
                        fn (string $state): string =>
                            $state === 'Terverifikasi'
                                ? 'heroicon-m-check-badge'
                                : 'heroicon-m-exclamation-triangle'
                    )
                    ->description(
                        fn (User $record): ?string =>
                            $record->email_verified_at?->format(
                                'd/m/Y H:i'
                            )
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make(
                    'email_verified_at'
                )
                    ->label('Verifikasi Email')
                    ->nullable()
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Sudah Terverifikasi')
                    ->falseLabel('Belum Terverifikasi'),
            ])
            ->actions([
                Tables\Actions\Action::make(
                    'kirim_verifikasi_email'
                )
                    ->label('Kirim Verifikasi')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('warning')
                    ->visible(
                        fn (User $record): bool =>
                            ! $record->hasVerifiedEmail()
                            && static::bolehMengelolaPengguna()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Kirim Link Verifikasi Email'
                    )
                    ->modalDescription(
                        fn (User $record): string =>
                            "Link verifikasi akan dikirim untuk akun {$record->email}."
                    )
                    ->modalSubmitActionLabel('Kirim Link')
                    ->action(
                        function (User $record): void {
                            try {
                                $record
                                    ->sendEmailVerificationNotification();

                                Notification::make()
                                    ->title(
                                        'Email verifikasi berhasil dikirim'
                                    )
                                    ->body(
                                        "Link verifikasi untuk {$record->email} telah diproses."
                                    )
                                    ->success()
                                    ->send();
                            } catch (Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title(
                                        'Email verifikasi gagal dikirim'
                                    )
                                    ->body(
                                        'Periksa konfigurasi mail dan log Laravel.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }
                    ),

                Tables\Actions\Action::make(
                    'verifikasi_manual'
                )
                    ->label('Verifikasi Manual')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(
                        fn (User $record): bool =>
                            ! $record->hasVerifiedEmail()
                            && static::bolehMengelolaPengguna()
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Verifikasi Email Secara Manual'
                    )
                    ->modalDescription(
                        fn (User $record): string =>
                            "Pastikan alamat {$record->email} benar sebelum diverifikasi secara manual."
                    )
                    ->modalSubmitActionLabel(
                        'Verifikasi Sekarang'
                    )
                    ->action(
                        function (User $record): void {
                            try {
                                if (! $record->hasVerifiedEmail()) {
                                    $record->markEmailAsVerified();

                                    event(
                                        new Verified($record)
                                    );
                                }

                                Notification::make()
                                    ->title(
                                        'Email berhasil diverifikasi'
                                    )
                                    ->body(
                                        "{$record->email} sekarang berstatus terverifikasi."
                                    )
                                    ->success()
                                    ->send();
                            } catch (Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title(
                                        'Verifikasi manual gagal'
                                    )
                                    ->body(
                                        'Status verifikasi belum dapat diperbarui.'
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }
                    ),

                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn (User $record): bool =>
                            static::bolehMengelolaPengguna()
                            && $record->getKey() !== Auth::id()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pengguna'),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    private static function bolehMengelolaPengguna(): bool
    {
        $pengguna = Auth::user();

        return $pengguna instanceof User
            && $pengguna->can('update_user');
    }
}