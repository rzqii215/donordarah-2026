<?php

namespace App\Livewire\Donor;

use App\Enums\StatusJadwalDonor;
use App\Models\JadwalDonor;
use App\Models\PendaftaranDonor;
use App\Models\ProfilPendonor;
use App\Models\User;
use App\Services\LayananPendaftaranDonor;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use UnitEnum;

#[Layout('components.layouts.donor')]
#[Title('Daftar Donor')]
class DaftarDonor extends Component
{
    public JadwalDonor $jadwal;

    public int $langkah = 2;

    public string $sehatHariIni = '';

    public string $sedangMinumObat = '';

    public string $operasiTerakhir = '';

    public string $cukupTidur = '';

    public string $sudahMakan = '';

    public string $catatan = '';

    public bool $persetujuan = false;

    public function mount(
        JadwalDonor $jadwal
    ): mixed {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->guest('/login');
        }

        $this->jadwal = $jadwal
            ->load('lokasi')
            ->loadCount('pendaftaranAktif');

        $sudahTerdaftar =
            PendaftaranDonor::withTrashed()
                ->where(
                    'jadwal_donor_id',
                    $jadwal->id
                )
                ->where(
                    'pendonor_id',
                    $user->id
                )
                ->exists();

        if ($sudahTerdaftar) {
            session()->flash(
                'error',
                'Anda sudah pernah terdaftar pada jadwal donor tersebut.'
            );

            return redirect()->route(
                'donor.riwayat'
            );
        }

        if (
            $jadwal->status !==
            StatusJadwalDonor::Dipublikasikan
        ) {
            session()->flash(
                'error',
                'Jadwal donor tidak tersedia untuk pendaftaran.'
            );

            return redirect()->route(
                'donor.jadwal'
            );
        }

        if (! $jadwal->pendaftaranSedangDibuka()) {
            session()->flash(
                'error',
                'Periode pendaftaran jadwal ini belum dibuka atau sudah ditutup.'
            );

            return redirect()->route(
                'donor.jadwal'
            );
        }

        if ($jadwal->sisaKuota() <= 0) {
            session()->flash(
                'error',
                'Kuota jadwal donor sudah penuh.'
            );

            return redirect()->route(
                'donor.jadwal'
            );
        }

        return null;
    }

    public function lanjutkanKeSkrining(): void
    {
        $dataBelumLengkap =
            $this->dataProfilBelumLengkap();

        if ($dataBelumLengkap !== []) {
            $this->addError(
                'profil',
                'Lengkapi data profil berikut terlebih dahulu: '
                . implode(', ', $dataBelumLengkap)
                . '.'
            );

            return;
        }

        $this->resetValidation();
        $this->langkah = 3;
    }

    public function lanjutkanKeKonfirmasi(): void
    {
        $this->validate(
            $this->aturanSkrining(),
            $this->pesanValidasiSkrining()
        );

        $this->resetValidation();
        $this->langkah = 4;
    }

    public function kembali(): mixed
    {
        $this->resetValidation();

        if ($this->langkah <= 2) {
            return redirect()->route(
                'donor.jadwal'
            );
        }

        $this->langkah--;

        return null;
    }

    public function kembaliKeDataPendonor(): void
    {
        $this->resetValidation();
        $this->langkah = 2;
    }

    public function kembaliKeSkrining(): void
    {
        $this->resetValidation();
        $this->langkah = 3;
    }

    public function kirimPendaftaran(): mixed
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->guest('/login');
        }

        $this->validate(
            array_merge(
                $this->aturanSkrining(),
                [
                    'catatan' => [
                        'nullable',
                        'string',
                        'max:1000',
                    ],

                    'persetujuan' => [
                        'accepted',
                    ],
                ]
            ),
            array_merge(
                $this->pesanValidasiSkrining(),
                [
                    'catatan.max' => 'Catatan maksimal 1.000 karakter.',

                    'persetujuan.accepted' => 'Anda wajib menyetujui pernyataan kebenaran data.',
                ]
            )
        );

        if ($this->dataProfilBelumLengkap() !== []) {
            $this->addError(
                'profil',
                'Data profil Anda berubah atau belum lengkap. Silakan periksa kembali profil pendonor.'
            );

            $this->langkah = 2;

            return null;
        }

        try {
            app(LayananPendaftaranDonor::class)
                ->daftar(
                    jadwalDonorId: (int) $this->jadwal->id,

                    pendonorId: (int) $user->id,

                    data: [
                        'jawaban_skrining' => [
                            'sehat_hari_ini' => $this->sehatHariIni === '1',

                            'sedang_minum_obat' => $this->sedangMinumObat === '1',

                            'operasi_terakhir' => $this->operasiTerakhir === '1',

                            'cukup_tidur' => $this->cukupTidur === '1',

                            'sudah_makan' => $this->sudahMakan === '1',

                            'persetujuan_kebenaran_data' => true,

                            'disetujui_pada' => now()->toIso8601String(),
                        ],

                        'catatan' => filled($this->catatan)
                                ? trim($this->catatan)
                                : null,
                    ],
                );

            session()->flash(
                'success',
                'Pendaftaran donor berhasil dikirim dan sedang menunggu verifikasi petugas.'
            );

            return redirect()->route(
                'donor.riwayat'
            );
        } catch (ValidationException $exception) {
            $pesan = collect(
                $exception->errors()
            )
                ->flatten()
                ->first();

            $this->addError(
                'pendaftaran',
                $pesan
                    ?: 'Pendaftaran donor belum dapat diproses.'
            );

            return null;
        }
    }

    public function render(): View
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $user->loadMissing(
                'profilPendonor'
            );
        }

        return view(
            'livewire.donor.daftar-donor',
            [
                'pengguna' => $user,

                'profilPendonor' => $user instanceof User
                        ? $user->profilPendonor
                        : null,

                'pertanyaanSkrining' => $this->pertanyaanSkrining(),

                'dataProfilBelumLengkap' => $this->dataProfilBelumLengkap(),
            ]
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function aturanSkrining(): array
    {
        return [
            'sehatHariIni' => [
                'required',
                'in:0,1',
            ],

            'sedangMinumObat' => [
                'required',
                'in:0,1',
            ],

            'operasiTerakhir' => [
                'required',
                'in:0,1',
            ],

            'cukupTidur' => [
                'required',
                'in:0,1',
            ],

            'sudahMakan' => [
                'required',
                'in:0,1',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function pesanValidasiSkrining(): array
    {
        return [
            'sehatHariIni.required' => 'Jawab pertanyaan mengenai kondisi kesehatan hari ini.',

            'sedangMinumObat.required' => 'Jawab pertanyaan mengenai konsumsi obat.',

            'operasiTerakhir.required' => 'Jawab pertanyaan mengenai riwayat operasi.',

            'cukupTidur.required' => 'Jawab pertanyaan mengenai waktu tidur.',

            'sudahMakan.required' => 'Jawab pertanyaan mengenai makan sebelum donor.',

            '*.in' => 'Jawaban skrining tidak valid.',
        ];
    }

    /**
     * @return array<int, array{
     *     property: string,
     *     pertanyaan: string,
     *     bantuan: string
     * }>
     */
    private function pertanyaanSkrining(): array
    {
        return [
            [
                'property' => 'sehatHariIni',

                'pertanyaan' => 'Apakah Anda merasa sehat hari ini?',

                'bantuan' => 'Jawab sesuai kondisi tubuh yang Anda rasakan saat ini.',
            ],
            [
                'property' => 'sedangMinumObat',

                'pertanyaan' => 'Apakah Anda sedang mengonsumsi obat tertentu?',

                'bantuan' => 'Termasuk obat dengan resep dokter maupun obat yang dikonsumsi rutin.',
            ],
            [
                'property' => 'operasiTerakhir',

                'pertanyaan' => 'Apakah Anda menjalani operasi dalam waktu dekat?',

                'bantuan' => 'Informasi ini akan ditinjau kembali oleh petugas kesehatan.',
            ],
            [
                'property' => 'cukupTidur',

                'pertanyaan' => 'Apakah Anda sudah tidur dengan cukup?',

                'bantuan' => 'Pendonor disarankan memiliki waktu istirahat yang cukup.',
            ],
            [
                'property' => 'sudahMakan',

                'pertanyaan' => 'Apakah Anda sudah makan sebelum donor?',

                'bantuan' => 'Hindari melakukan donor darah dalam keadaan perut kosong.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function dataProfilBelumLengkap(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [
                'Pengguna',
            ];
        }

        $user->loadMissing(
            'profilPendonor'
        );

        $profil = $user->profilPendonor;

        if (! $profil instanceof ProfilPendonor) {
            return [
                'Profil pendonor',
            ];
        }

        $data = [
            'Nama lengkap' => filled($user->name),

            'Email' => filled($user->email),

            'Nomor telepon' => filled($user->nomor_telepon),

            'Tanggal lahir' => filled($profil->tanggal_lahir),

            'Jenis kelamin' => filled($profil->jenis_kelamin),

            'Golongan darah' => filled($profil->golongan_darah),

            'Rhesus' => filled($profil->rhesus),

            'Alamat' => filled($profil->alamat),
        ];

        return collect($data)
            ->reject(
                fn (bool $terisi): bool => $terisi
            )
            ->keys()
            ->values()
            ->all();
    }

    public function jawabanLabel(
        string $property
    ): string {
        $value = match ($property) {
            'sehatHariIni' => $this->sehatHariIni,

            'sedangMinumObat' => $this->sedangMinumObat,

            'operasiTerakhir' => $this->operasiTerakhir,

            'cukupTidur' => $this->cukupTidur,

            'sudahMakan' => $this->sudahMakan,

            default => '',
        };

        return match ($value) {
            '1' => 'Ya',
            '0' => 'Tidak',
            default => 'Belum dijawab',
        };
    }

    public function namaLokasi(): string
    {
        return $this->jadwal
            ->lokasi
            ?->nama_tampilan
            ?? 'Lokasi belum ditentukan';
    }

    public function alamatLokasi(): string
    {
        return $this->jadwal
            ->lokasi
            ?->alamat_tampilan
            ?? '-';
    }

    public function tanggalJadwal(): string
    {
        $tanggal = $this->jadwal
            ->mulai_pada;

        if (! $tanggal instanceof CarbonInterface) {
            return 'Tanggal belum ditentukan';
        }

        return $tanggal->translatedFormat(
            'l, d F Y'
        );
    }

    public function jamJadwal(): string
    {
        $mulai = $this->jadwal
            ->mulai_pada;

        $selesai = $this->jadwal
            ->selesai_pada;

        if (! $mulai instanceof CarbonInterface) {
            return '-';
        }

        if (! $selesai instanceof CarbonInterface) {
            return $mulai->format('H:i');
        }

        return $mulai->format('H:i')
            . '–'
            . $selesai->format('H:i');
    }

    public function tanggalLahir(
        ?ProfilPendonor $profil
    ): string {
        if (
            ! $profil?->tanggal_lahir
            instanceof CarbonInterface
        ) {
            return '-';
        }

        return $profil
            ->tanggal_lahir
            ->translatedFormat('d F Y');
    }

    public function labelJenisKelamin(
        ?ProfilPendonor $profil
    ): string {
        if ($profil === null) {
            return '-';
        }

        $value = $this->nilaiEnum(
            $profil->jenis_kelamin
        );

        return match (
            Str::lower($value)
        ) {
            'male',
            'laki_laki',
            'laki-laki',
            'pria' => 'Laki-laki',

            'female',
            'perempuan',
            'wanita' => 'Perempuan',

            default => $value !== ''
                ? Str::headline($value)
                : '-',
        };
    }

    public function golonganDarah(
        ?ProfilPendonor $profil
    ): string {
        if ($profil === null) {
            return '-';
        }

        $golongan = Str::upper(
            $this->nilaiEnum(
                $profil->golongan_darah
            )
        );

        $rhesusValue = Str::lower(
            $this->nilaiEnum(
                $profil->rhesus
            )
        );

        $rhesus = match ($rhesusValue) {
            'positive',
            'positif',
            '+',
            'rh+' => '+',

            'negative',
            'negatif',
            '-',
            'rh-' => '-',

            default => '',
        };

        if ($golongan === '') {
            return '-';
        }

        return $golongan . $rhesus;
    }

    private function nilaiEnum(
        mixed $value
    ): string {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof UnitEnum) {
            return (string) $value->name;
        }

        return trim((string) $value);
    }
}
