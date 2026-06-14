<?php

namespace App\Filament\Admin\Resources\JadwalDonorResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateJadwalDonorRequest extends FormRequest
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
			'lokasi_donor_id' => 'required',
			'kode_jadwal' => 'required',
			'judul' => 'required',
			'slug' => 'required',
			'deskripsi' => 'required|string',
			'mulai_pada' => 'required',
			'selesai_pada' => 'required',
			'pendaftaran_dibuka_pada' => 'required',
			'pendaftaran_ditutup_pada' => 'required',
			'kuota' => 'required',
			'status' => 'required',
			'path_banner' => 'required',
			'dibuat_oleh' => 'required',
			'dipublikasikan_pada' => 'required',
			'dibatalkan_pada' => 'required',
			'alasan_pembatalan' => 'required|string',
			'deleted_at' => 'required'
		];
    }
}
