<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Requests;

use App\Enums\JenisKelamin;
use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfilPendonorRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'nomor_telepon' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'tanggal_lahir' => [
                'sometimes',
                'required',
                'date',
                'before:today',
            ],

            'jenis_kelamin' => [
                'sometimes',
                'required',
                Rule::enum(JenisKelamin::class),
            ],

            'alamat' => [
                'sometimes',
                'required',
                'string',
                'max:5000',
            ],

            'provinsi' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'kota' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'kecamatan' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'kode_pos' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
            ],

            'nama_kontak_darurat' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'telepon_kontak_darurat' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'bersedia_dihubungi' => [
                'sometimes',
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_lahir.before' =>
                'Tanggal lahir harus sebelum hari ini.',

            'jenis_kelamin.enum' =>
                'Jenis kelamin tidak valid.',

            'bersedia_dihubungi.boolean' =>
                'Kesediaan dihubungi harus bernilai true atau false.',
        ];
    }
}