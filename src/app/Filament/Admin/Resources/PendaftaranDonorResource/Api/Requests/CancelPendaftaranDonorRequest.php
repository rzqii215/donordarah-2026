<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Requests;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CancelPendaftaranDonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengguna = $this->user();

        return $pengguna instanceof User
            && $pengguna->hasRole(
                PeranPengguna::Pendonor->value
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan.required' =>
                'Alasan pembatalan wajib diisi.',

            'alasan.string' =>
                'Alasan pembatalan harus berupa teks.',

            'alasan.min' =>
                'Alasan pembatalan minimal 10 karakter.',

            'alasan.max' =>
                'Alasan pembatalan maksimal 2000 karakter.',
        ];
    }
}