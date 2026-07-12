<?php

namespace App\Filament\Admin\Pages;

use App\Models\PengaturanTampilan as ModelPengaturanTampilan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PengaturanTampilan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Tampilan Autentikasi';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Pengaturan Tampilan Autentikasi';

    protected static string $view =
        'filament.admin.pages.pengaturan-tampilan';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $pengguna = Auth::user();

        return $pengguna instanceof User
            && $pengguna->hasRole(
                'super_admin'
            );
    }

    public function mount(): void
    {
        abort_unless(
            static::canAccess(),
            403
        );

        $pengaturan =
            ModelPengaturanTampilan::query()
                ->firstOrCreate([
                    'id' => 1,
                ]);

        $this->form->fill([
            'gambar_auth' => $pengaturan->gambar_auth,

            'gambar_auth_alt' => $pengaturan->gambar_auth_alt,
        ]);
    }

    public function form(
        Form $form
    ): Form {
        return $form
            ->schema([
                Forms\Components\Section::make(
                    'Gambar Halaman Autentikasi'
                )
                    ->description(
                        'Gambar ini tampil pada halaman login, registrasi, lupa password, dan reset password.'
                    )
                    ->schema([
                        Forms\Components\FileUpload::make(
                            'gambar_auth'
                        )
                            ->label('Foto utama')
                            ->disk('public')
                            ->directory(
                                'pengaturan/auth'
                            )
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight(
                                '320'
                            )
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->helperText(
                                'Gunakan JPG, PNG, atau WebP. Maksimal 5 MB. Rasio yang disarankan 3:2.'
                            )
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make(
                            'gambar_auth_alt'
                        )
                            ->label(
                                'Deskripsi gambar'
                            )
                            ->placeholder(
                                'Contoh: Petugas kesehatan mendampingi pendonor darah.'
                            )
                            ->maxLength(255)
                            ->helperText(
                                'Deskripsi ini membantu aksesibilitas pengguna pembaca layar.'
                            )
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function simpan(): void
    {
        abort_unless(
            static::canAccess(),
            403
        );

        $data = $this->form->getState();

        $pengaturan =
            ModelPengaturanTampilan::query()
                ->firstOrCreate([
                    'id' => 1,
                ]);

        $pengaturan->fill([
            'gambar_auth' => $data['gambar_auth']
                ?? null,

            'gambar_auth_alt' => filled(
                $data['gambar_auth_alt']
                ?? null
            )
                    ? trim(
                        (string) $data[
                            'gambar_auth_alt'
                        ]
                    )
                    : null,
        ]);

        $pengaturan->save();

        Cache::forget(
            'pengaturan-tampilan.auth'
        );

        Notification::make()
            ->title(
                'Tampilan autentikasi berhasil disimpan'
            )
            ->body(
                'Gambar terbaru langsung digunakan pada seluruh halaman autentikasi.'
            )
            ->success()
            ->send();
    }
}
