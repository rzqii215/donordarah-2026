<?php

namespace App\Filament\Admin\Resources\KantongDarahResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateKantongDarahRequest extends FormRequest
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
			'kode_kantong' => 'required',
			'pendaftaran_donor_id' => 'required',
			'golongan_darah' => 'required',
			'rhesus' => 'required',
			'jenis_komponen' => 'required',
			'volume_ml' => 'required',
			'diambil_pada' => 'required',
			'kedaluwarsa_pada' => 'required',
			'status_mutu' => 'required',
			'status' => 'required',
			'lokasi_penyimpanan' => 'required',
			'diverifikasi_oleh' => 'required',
			'diverifikasi_pada' => 'required',
			'alasan_penolakan' => 'required|string',
			'didistribusikan_pada' => 'required',
			'catatan' => 'required|string',
			'deleted_at' => 'required'
		];
    }
}
