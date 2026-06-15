<?php

namespace App\Filament\Admin\Resources\ProfilRumahSakitResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProfilRumahSakitRequest extends FormRequest
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
			'kode_rumah_sakit' => 'required',
			'nama_rumah_sakit' => 'required',
			'nomor_izin' => 'required',
			'path_dokumen_izin' => 'required',
			'nama_penanggung_jawab' => 'required',
			'jabatan_penanggung_jawab' => 'required',
			'alamat' => 'required|string',
			'provinsi' => 'required',
			'kota' => 'required',
			'kecamatan' => 'required',
			'kode_pos' => 'required',
			'latitude' => 'required|numeric',
			'longitude' => 'required|numeric',
			'status_verifikasi' => 'required',
			'diverifikasi_oleh' => 'required',
			'diverifikasi_pada' => 'required',
			'alasan_penolakan' => 'required|string'
		];
    }
}
