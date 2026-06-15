<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermintaanDarahRequest extends FormRequest
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
			'nomor_permintaan' => 'required',
			'profil_rumah_sakit_id' => 'required',
			'referensi_pasien' => 'required',
			'nama_dokter' => 'required',
			'golongan_darah' => 'required',
			'rhesus' => 'required',
			'jumlah_kantong' => 'required',
			'tingkat_urgensi' => 'required',
			'dibutuhkan_pada' => 'required',
			'path_dokumen_permintaan' => 'required',
			'status' => 'required',
			'ditinjau_oleh' => 'required',
			'ditinjau_pada' => 'required',
			'disetujui_pada' => 'required',
			'siap_diambil_pada' => 'required',
			'selesai_pada' => 'required',
			'dibatalkan_pada' => 'required',
			'alasan_penolakan' => 'required|string',
			'alasan_pembatalan' => 'required|string',
			'catatan' => 'required|string',
			'deleted_at' => 'required'
		];
    }
}
