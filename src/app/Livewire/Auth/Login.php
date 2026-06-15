<?php

namespace App\Livewire\Auth;

use App\Enums\StatusPengguna;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->arahkanPengguna();
    }

    public function authenticate(): void
    {
        $data = $this->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                ],

                'remember' => [
                    'boolean',
                ],
            ],
            [
                'email.required' =>
                    'Alamat email wajib diisi.',

                'email.email' =>
                    'Format alamat email tidak valid.',

                'password.required' =>
                    'Kata sandi wajib diisi.',

                'password.min' =>
                    'Kata sandi minimal 8 karakter.',
            ]
        );

        $berhasil = Auth::guard('web')->attempt(
            [
                'email' =>
                    mb_strtolower(
                        trim($data['email'])
                    ),

                'password' =>
                    $data['password'],
            ],
            (bool) $data['remember']
        );

        if (! $berhasil) {
            $this->addError(
                'email',
                'Email atau kata sandi tidak sesuai.'
            );

            return;
        }

        request()->session()->regenerate();

        $pengguna = Auth::user();

        if (! $pengguna instanceof User) {
            $this->keluarkanPengguna();

            $this->addError(
                'email',
                'Data pengguna tidak ditemukan.'
            );

            return;
        }

        if (
            $pengguna->status !==
            StatusPengguna::Aktif
        ) {
            $this->keluarkanPengguna();

            $this->addError(
                'email',
                'Akun belum aktif atau sedang dibatasi.'
            );

            return;
        }

        $this->arahkanPengguna();
    }

    public function render(): View
    {
        return view(
            'livewire.auth.login'
        );
    }

    private function arahkanPengguna(): void
    {
        $pengguna = Auth::user();

        if (! $pengguna instanceof User) {
            return;
        }

        if ($pengguna->hasRole('donor')) {
            $this->redirectRoute(
                'donor.beranda',
                navigate: true
            );

            return;
        }

        if (
            $pengguna->hasAnyRole([
                'super_admin',
                'petugas',
            ])
        ) {
            $this->redirect(
                '/admin',
                navigate: false
            );

            return;
        }

        if ($pengguna->hasRole('hospital')) {
            if (Route::has('rumah-sakit.beranda')) {
                $this->redirectRoute(
                    'rumah-sakit.beranda',
                    navigate: true
                );

                return;
            }

            $this->keluarkanPengguna();

            session()->flash(
                'error',
                'Portal Rumah Sakit belum tersedia.'
            );

            return;
        }

        $this->keluarkanPengguna();

        $this->addError(
            'email',
            'Akun tidak memiliki akses ke portal.'
        );
    }

    private function keluarkanPengguna(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}