<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLokasiDonorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'nama' => 'required',
			'slug' => 'required',
			'alamat' => 'required|string',
			'provinsi' => 'required',
			'kota' => 'required',
			'kecamatan' => 'required',
			'kode_pos' => 'required',
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'nama_kontak' => 'required',
			'nomor_kontak' => 'required',
			'deskripsi' => 'required|string',
			'aktif' => 'required',
			'dibuat_oleh' => 'required',
			'deleted_at' => 'required'
		];
    }
}
