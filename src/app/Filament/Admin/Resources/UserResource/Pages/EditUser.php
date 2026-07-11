<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private bool $emailBerubah = false;

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {
        $emailLama = Str::lower(
            trim(
                (string) $this->record
                    ->getAttribute('email')
            )
        );

        $emailBaru = Str::lower(
            trim(
                (string) (
                    $data['email']
                    ?? ''
                )
            )
        );

        $this->emailBerubah = $emailBaru !== ''
            && $emailBaru !== $emailLama;

        if ($emailBaru !== '') {
            $data['email'] = $emailBaru;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->emailBerubah) {
            return;
        }

        $user = $this->record;

        if (! $user instanceof User) {
            return;
        }

        $user->forceFill([
            'email_verified_at' => null,
        ]);

        $user->save();

        try {
            $user->sendEmailVerificationNotification();

            Notification::make()
                ->title('Email pengguna berhasil diubah')
                ->body(
                    'Status verifikasi dikembalikan menjadi belum terverifikasi dan link baru telah dikirim.'
                )
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Email berubah, tetapi link gagal dikirim')
                ->body(
                    'Status email sudah dikembalikan menjadi belum terverifikasi. Link dapat dikirim ulang melalui halaman daftar pengguna.'
                )
                ->warning()
                ->persistent()
                ->send();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        if ($this->emailBerubah) {
            return 'Data pengguna diperbarui dan email perlu diverifikasi ulang';
        }

        return 'Data pengguna berhasil diperbarui';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl(
            'index'
        );
    }

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Pengguna')
                ->visible(
                    fn (): bool =>
                        $this->getRecord()->getKey()
                            !== Auth::id()
                ),
        ];
    }
}