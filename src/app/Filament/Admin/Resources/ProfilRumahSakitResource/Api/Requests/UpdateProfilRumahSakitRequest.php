<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Requests;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilRumahSakitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengguna = $this->user();

        return $pengguna instanceof User
            && $pengguna->hasRole(
                PeranPengguna::RumahSakit->value
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

            'nama_penanggung_jawab' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'jabatan_penanggung_jawab' => [
                'sometimes',
                'required',
                'string',
                'max:150',
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

            'latitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.between' =>
                'Latitude harus berada antara -90 sampai 90.',

            'longitude.between' =>
                'Longitude harus berada antara -180 sampai 180.',
        ];
    }
}