<?php

namespace App\Filament\Admin\Resources\ProfilPendonorResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProfilPendonorRequest extends FormRequest
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
			'pengguna_id' => 'required',
			'kode_pendonor' => 'required',
			'tanggal_lahir' => 'required|date',
			'jenis_kelamin' => 'required',
			'golongan_darah' => 'required',
			'rhesus' => 'required',
			'alamat' => 'required|string',
			'provinsi' => 'required',
			'kota' => 'required',
			'kecamatan' => 'required',
			'kode_pos' => 'required',
			'nama_kontak_darurat' => 'required',
			'telepon_kontak_darurat' => 'required',
			'terakhir_donor_pada' => 'required',
			'bersedia_dihubungi' => 'required'
		];
    }
}
