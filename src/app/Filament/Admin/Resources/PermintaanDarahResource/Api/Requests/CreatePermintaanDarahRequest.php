<?php

namespace App\Filament\Admin\Resources\PermintaanDarahResource\Api\Requests;

use App\Enums\GolonganDarah;
use App\Enums\PeranPengguna;
use App\Enums\RhesusDarah;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePermintaanDarahRequest extends FormRequest
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
            'referensi_pasien' => [
                'required',
                'string',
                'max:100',
            ],

            'nama_dokter' => [
                'required',
                'string',
                'max:255',
            ],

            'golongan_darah' => [
                'required',
                Rule::enum(
                    GolonganDarah::class
                ),
            ],

            'rhesus' => [
                'required',
                Rule::enum(
                    RhesusDarah::class
                ),
            ],

            'jumlah_kantong' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'tingkat_urgensi' => [
                'required',
                Rule::enum(
                    TingkatUrgensiPermintaanDarah::class
                ),
            ],

            'dibutuhkan_pada' => [
                'required',
                'date',
                'after_or_equal:now',
            ],

            'dokumen_permintaan' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'catatan' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'referensi_pasien.required' =>
                'Referensi pasien wajib diisi.',

            'nama_dokter.required' =>
                'Nama dokter wajib diisi.',

            'golongan_darah.enum' =>
                'Golongan darah tidak valid.',

            'rhesus.enum' =>
                'Rhesus darah tidak valid.',

            'jumlah_kantong.min' =>
                'Jumlah kantong minimal satu.',

            'tingkat_urgensi.enum' =>
                'Tingkat urgensi tidak valid.',

            'dibutuhkan_pada.after_or_equal' =>
                'Waktu kebutuhan tidak boleh berada di masa lalu.',

            'dokumen_permintaan.mimes' =>
                'Dokumen harus berformat PDF, JPG, JPEG, atau PNG.',

            'dokumen_permintaan.max' =>
                'Ukuran dokumen maksimal 5 MB.',
        ];
    }
}