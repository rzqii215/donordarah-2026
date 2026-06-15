<?php

namespace App\Filament\Admin\Resources\DistribusiDarahResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDistribusiDarahRequest extends FormRequest
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
			'nomor_distribusi' => 'required',
			'permintaan_darah_id' => 'required',
			'disiapkan_oleh' => 'required',
			'dijadwalkan_pada' => 'required',
			'status' => 'required',
			'diserahkan_oleh' => 'required',
			'nama_penerima' => 'required',
			'jabatan_penerima' => 'required',
			'nomor_identitas_penerima' => 'required',
			'path_bukti_serah_terima' => 'required',
			'diserahkan_pada' => 'required',
			'dibatalkan_pada' => 'required',
			'alasan_pembatalan' => 'required|string',
			'catatan' => 'required|string'
		];
    }
}
