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
                PeranPengguna::PemohonDonor->value
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
            'name.required' =>
                'Nama akun wajib diisi.',

            'name.max' =>
                'Nama akun maksimal 255 karakter.',

            'nomor_telepon.max' =>
                'Nomor telepon maksimal 30 karakter.',

            'nama_penanggung_jawab.required' =>
                'Nama penanggung jawab wajib diisi.',

            'nama_penanggung_jawab.max' =>
                'Nama penanggung jawab maksimal 255 karakter.',

            'jabatan_penanggung_jawab.required' =>
                'Jabatan atau peran penanggung jawab wajib diisi.',

            'jabatan_penanggung_jawab.max' =>
                'Jabatan atau peran penanggung jawab maksimal 150 karakter.',

            'alamat.required' =>
                'Alamat Pemohon Donor wajib diisi.',

            'alamat.max' =>
                'Alamat Pemohon Donor maksimal 5000 karakter.',

            'provinsi.required' =>
                'Provinsi wajib diisi.',

            'provinsi.max' =>
                'Provinsi maksimal 100 karakter.',

            'kota.required' =>
                'Kota atau kabupaten wajib diisi.',

            'kota.max' =>
                'Kota atau kabupaten maksimal 100 karakter.',

            'kecamatan.max' =>
                'Kecamatan maksimal 100 karakter.',

            'kode_pos.max' =>
                'Kode pos maksimal 10 karakter.',

            'latitude.numeric' =>
                'Latitude harus berupa angka.',

            'latitude.between' =>
                'Latitude harus berada antara -90 sampai 90.',

            'longitude.numeric' =>
                'Longitude harus berupa angka.',

            'longitude.between' =>
                'Longitude harus berada antara -180 sampai 180.',
        ];
    }
}