<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Requests;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreatePendaftaranDonorRequest extends FormRequest
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
            'jadwal_donor_id' => [
                'required',
                'integer',
                'exists:jadwal_donor,id',
            ],

            'jawaban_skrining' => [
                'nullable',
                'array',
            ],

            'catatan' => [
                'nullable',
                'string',
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
            'jadwal_donor_id.required' =>
                'Jadwal donor wajib dipilih.',

            'jadwal_donor_id.exists' =>
                'Jadwal donor tidak ditemukan.',

            'jawaban_skrining.array' =>
                'Jawaban skrining harus berupa objek atau array.',
        ];
    }
}