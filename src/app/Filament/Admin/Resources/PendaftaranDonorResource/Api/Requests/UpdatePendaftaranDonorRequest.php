<?php

namespace App\Filament\Admin\Resources\PendaftaranDonorResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePendaftaranDonorRequest extends FormRequest
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
			'nomor_pendaftaran' => 'required',
			'jadwal_donor_id' => 'required',
			'pendonor_id' => 'required',
			'jawaban_skrining' => 'required',
			'status' => 'required',
			'ditinjau_oleh' => 'required',
			'ditinjau_pada' => 'required',
			'alasan_penolakan' => 'required|string',
			'hadir_pada' => 'required',
			'dibatalkan_pada' => 'required',
			'alasan_pembatalan' => 'required|string',
			'selesai_pada' => 'required',
			'catatan' => 'required|string',
			'deleted_at' => 'required'
		];
    }
}
