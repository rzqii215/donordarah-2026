<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PengaturanController extends PortalPemohonController
{
    public function index(): View
    {
        $pengguna = $this->penggunaPemohon();

        $profil = $this->profilPemohon(
            $pengguna
        );

        return view(
            'pemohon-donor.pengaturan.index',
            [
                'pengguna' => $pengguna,
                'profil' => $profil,
            ]
        );
    }

    public function updateAkun(
        Request $request
    ): RedirectResponse {
        $pengguna = $this->penggunaPemohon();

        $data = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $pengguna->getKey()
                    ),
                ],
                'nomor_telepon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
            ],
            [
                'email.required' => 'Email wajib diisi.',

                'email.email' => 'Format email tidak valid.',

                'email.unique' => 'Email sudah digunakan oleh akun lain.',

                'nomor_telepon.max' => 'Nomor telepon maksimal 30 karakter.',
            ]
        );

        $pengguna
            ->forceFill([
                'email' => mb_strtolower(
                    trim(
                        (string) $data['email']
                    )
                ),

                'nomor_telepon' => filled(
                    $data['nomor_telepon'] ?? null
                )
                    ? trim(
                        (string) $data[
                            'nomor_telepon'
                        ]
                    )
                    : null,
            ])
            ->save();

        return redirect()
            ->route(
                'pemohon-donor.pengaturan.index'
            )
            ->with(
                'success',
                'Pengaturan akun berhasil diperbarui.'
            );
    }

    public function updatePassword(
        Request $request
    ): RedirectResponse {
        $pengguna = $this->penggunaPemohon();

        $data = $request->validate(
            [
                'password_lama' => [
                    'required',
                    'string',
                ],
                'password_baru' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'password_lama.required' => 'Password lama wajib diisi.',

                'password_baru.required' => 'Password baru wajib diisi.',

                'password_baru.min' => 'Password baru minimal 8 karakter.',

                'password_baru.confirmed' => 'Konfirmasi password baru tidak sesuai.',
            ]
        );

        if (
            ! Hash::check(
                $data['password_lama'],
                $pengguna->password
            )
        ) {
            return back()
                ->withErrors([
                    'password_lama' => 'Password lama tidak sesuai.',
                ])
                ->onlyInput();
        }

        $pengguna
            ->forceFill([
                'password' => Hash::make(
                    $data['password_baru']
                ),
            ])
            ->save();

        $request
            ->session()
            ->regenerate();

        return redirect()
            ->route(
                'pemohon-donor.pengaturan.index'
            )
            ->with(
                'success',
                'Password akun berhasil diperbarui.'
            );
    }
}
