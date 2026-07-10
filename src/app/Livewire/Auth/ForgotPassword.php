<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $berhasilDikirim = false;

    public function sendResetLink(): void
    {
        $this->validate([
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar.',
        ]);

        $status = Password::sendResetLink([
            'email' => mb_strtolower(trim($this->email)),
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->berhasilDikirim = true;

            session()->flash(
                'success',
                'Link reset password berhasil dikirim. Silakan cek email Anda.'
            );

            return;
        }

        $this->addError(
            'email',
            'Link reset password gagal dikirim. Silakan coba beberapa saat lagi.'
        );
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}