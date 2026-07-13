<?php

namespace App\Livewire\Donor;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\RhesusDarah;
use App\Enums\StatusPendaftaranDonor;
use App\Models\PendaftaranDonor;
use App\Models\ProfilPendonor;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;
use UnitEnum;

#[Layout('components.layouts.donor')]
#[Title('Profil Pendonor')]
class Profil extends Component
{
    public string $name = '';

    public string $email = '';

    public string $nomor_telepon = '';

    public string $tanggal_lahir = '';

    public string $jenis_kelamin = '';

    public string $golongan_darah = '';

    public string $rhesus = '';

    public string $alamat = '';

    public string $provinsi = '';

    public string $kota = '';

    public string $kecamatan = '';

    public string $kode_pos = '';

    public string $nama_kontak_darurat = '';

    public string $telepon_kontak_darurat = '';

    public bool $bersedia_dihubungi = true;

    public function mount(): void
    {
        $this->muatDataProfil();
    }

    public function simpanProfil(): mixed
    {
        $user = $this->penggunaSaatIni();

        if ($user === null) {
            return redirect()->guest('/login');
        }

        $data = $this->validate(
            $this->aturanValidasi($user),
            $this->pesanValidasi()
        );

        $emailBaru = mb_strtolower(
            trim($data['email'])
        );

        $emailLama = mb_strtolower(
            trim((string) $user->email)
        );

        $emailBerubah = ! hash_equals(
            $emailLama,
            $emailBaru
        );

        DB::transaction(
            function () use (
                $user,
                $data,
                $emailBaru,
                $emailBerubah
            ): void {
                $payloadUser = [
                    'name' => trim($data['name']),

                    'email' => $emailBaru,

                    'nomor_telepon' => trim(
                        $data['nomor_telepon']
                    ),
                ];

                if ($emailBerubah) {
                    $payloadUser[
                        'email_verified_at'
                    ] = null;
                }

                $user->forceFill(
                    $payloadUser
                );

                $user->save();

                $profil =
                    ProfilPendonor::query()
                        ->firstOrNew([
                            'pengguna_id' => $user->id,
                        ]);

                if (
                    blank(
                        $profil->kode_pendonor
                    )
                ) {
                    $profil->kode_pendonor =
                        $this->buatKodePendonor();
                }

                $profil->fill([
                    'tanggal_lahir' => $data['tanggal_lahir'],

                    'jenis_kelamin' => $data['jenis_kelamin'],

                    'golongan_darah' => $data['golongan_darah'],

                    'rhesus' => $data['rhesus'],

                    'alamat' => trim($data['alamat']),

                    'provinsi' => trim($data['provinsi']),

                    'kota' => trim($data['kota']),

                    'kecamatan' => filled(
                        $data['kecamatan']
                    )
                            ? trim(
                                $data['kecamatan']
                            )
                            : null,

                    'kode_pos' => filled(
                        $data['kode_pos']
                    )
                            ? trim(
                                $data['kode_pos']
                            )
                            : null,

                    'nama_kontak_darurat' => trim(
                        $data[
                            'nama_kontak_darurat'
                        ]
                    ),

                    'telepon_kontak_darurat' => trim(
                        $data[
                            'telepon_kontak_darurat'
                        ]
                    ),

                    'bersedia_dihubungi' => (bool) $data[
                            'bersedia_dihubungi'
                        ],
                ]);

                $profil->save();
            }
        );

        $this->muatDataProfil();

        if ($emailBerubah) {
            $user->refresh();

            try {
                $user
                    ->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);

                session()->flash(
                    'warning',
                    'Profil tersimpan, tetapi email verifikasi belum berhasil dikirim. Silakan gunakan tombol kirim ulang pada halaman verifikasi.'
                );
            }

            session()->flash(
                'success',
                'Profil berhasil disimpan. Karena alamat email berubah, silakan verifikasi email baru Anda.'
            );

            return redirect()->route(
                'verification.notice'
            );
        }

        session()->flash(
            'success',
            'Profil pendonor berhasil disimpan.'
        );

        return null;
    }

    public function batalkanPerubahan(): void
    {
        $this->resetValidation();

        $this->muatDataProfil();
    }

    public function render(): View
    {
        return view(
            'livewire.donor.profil',
            [
                'profilPendonor' => $this->profilPendonor(),

                'kelengkapan' => $this->kelengkapanProfil(),

                'ringkasan' => $this->ringkasanProfil(),

                'pengguna' => $this->penggunaSaatIni(),
            ]
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function aturanValidasi(
        User $user
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            'nomor_telepon' => [
                'required',
                'string',
                'min:9',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'tanggal_lahir' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1900-01-01',
                'before_or_equal:'
                    . now()
                        ->subYears(17)
                        ->format('Y-m-d'),
            ],

            'jenis_kelamin' => [
                'required',
                Rule::enum(
                    JenisKelamin::class
                ),
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

            'alamat' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],

            'provinsi' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'kota' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'kecamatan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'kode_pos' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5,10}$/',
            ],

            'nama_kontak_darurat' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'telepon_kontak_darurat' => [
                'required',
                'string',
                'min:9',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'bersedia_dihubungi' => [
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function pesanValidasi(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',

            'name.min' => 'Nama lengkap minimal 3 karakter.',

            'email.required' => 'Alamat email wajib diisi.',

            'email.email' => 'Alamat email tidak valid.',

            'email.unique' => 'Alamat email sudah digunakan akun lain.',

            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',

            'nomor_telepon.min' => 'Nomor telepon minimal 9 karakter.',

            'nomor_telepon.regex' => 'Format nomor telepon tidak valid.',

            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',

            'tanggal_lahir.date_format' => 'Format tanggal lahir tidak valid.',

            'tanggal_lahir.after_or_equal' => 'Tanggal lahir tidak valid.',

            'tanggal_lahir.before_or_equal' => 'Pendonor minimal berusia 17 tahun.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',

            'jenis_kelamin.enum' => 'Jenis kelamin tidak valid.',

            'golongan_darah.required' => 'Golongan darah wajib dipilih.',

            'golongan_darah.enum' => 'Golongan darah tidak valid.',

            'rhesus.required' => 'Rhesus darah wajib dipilih.',

            'rhesus.enum' => 'Rhesus darah tidak valid.',

            'alamat.required' => 'Alamat wajib diisi.',

            'alamat.min' => 'Alamat minimal 10 karakter.',

            'provinsi.required' => 'Provinsi wajib diisi.',

            'kota.required' => 'Kota atau kabupaten wajib diisi.',

            'kode_pos.regex' => 'Kode pos hanya boleh berisi 5 sampai 10 angka.',

            'nama_kontak_darurat.required' => 'Nama kontak darurat wajib diisi.',

            'nama_kontak_darurat.min' => 'Nama kontak darurat minimal 3 karakter.',

            'telepon_kontak_darurat.required' => 'Nomor kontak darurat wajib diisi.',

            'telepon_kontak_darurat.regex' => 'Format nomor kontak darurat tidak valid.',
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     label: string
     * }>
     */
    public function opsiJenisKelamin(): array
    {
        return collect(
            JenisKelamin::cases()
        )
            ->map(
                fn (
                    JenisKelamin $case
                ): array => [
                    'value' => $case->value,

                    'label' => $case->label(),
                ]
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     label: string
     * }>
     */
    public function opsiGolonganDarah(): array
    {
        return collect(
            GolonganDarah::cases()
        )
            ->map(
                fn (
                    GolonganDarah $case
                ): array => [
                    'value' => $case->value,

                    'label' => $case->label(),
                ]
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     label: string
     * }>
     */
    public function opsiRhesusDarah(): array
    {
        return collect(
            RhesusDarah::cases()
        )
            ->map(
                fn (
                    RhesusDarah $case
                ): array => [
                    'value' => $case->value,

                    'label' => $case->label(),
                ]
            )
            ->values()
            ->all();
    }

    public function golonganRhesusTampilan(): string
    {
        if (
            blank($this->golongan_darah)
            || blank($this->rhesus)
        ) {
            return '-';
        }

        $rhesus = match (
            $this->rhesus
        ) {
            RhesusDarah::Positif->value => '+',

            RhesusDarah::Negatif->value => '-',

            default => '',
        };

        return Str::upper(
            $this->golongan_darah
        ) . $rhesus;
    }

    public function labelRhesusTampilan(): string
    {
        return match (
            $this->rhesus
        ) {
            RhesusDarah::Positif->value => RhesusDarah::Positif
                ->label(),

            RhesusDarah::Negatif->value => RhesusDarah::Negatif
                ->label(),

            default => '-',
        };
    }

    public function labelJenisKelaminTampilan(): string
    {
        return match (
            $this->jenis_kelamin
        ) {
            JenisKelamin::LakiLaki->value => JenisKelamin::LakiLaki
                ->label(),

            JenisKelamin::Perempuan->value => JenisKelamin::Perempuan
                ->label(),

            default => '-',
        };
    }

    public function umurPendonor(): string
    {
        if (blank($this->tanggal_lahir)) {
            return '-';
        }

        try {
            return Carbon::parse(
                $this->tanggal_lahir
            )->age . ' tahun';
        } catch (Throwable) {
            return '-';
        }
    }

    public function tanggalLahirTampilan(): string
    {
        if (blank($this->tanggal_lahir)) {
            return '-';
        }

        try {
            return Carbon::parse(
                $this->tanggal_lahir
            )->translatedFormat(
                'd F Y'
            );
        } catch (Throwable) {
            return '-';
        }
    }

    public function avatarUrl(): string
    {
        $user = $this->penggunaSaatIni();

        if ($user === null) {
            return 'https://www.gravatar.com/avatar/?d=mp&s=250';
        }

        return $user->getFilamentAvatarUrl()
            ?? 'https://www.gravatar.com/avatar/?d=mp&s=250';
    }

    /**
     * @return array{
     *     total: int,
     *     terisi: int,
     *     persentase: int,
     *     lengkap: bool,
     *     belum_lengkap: array<int, string>
     * }
     */
    public function kelengkapanProfil(): array
    {
        $items = [
            'Nama Lengkap' => filled($this->name),

            'Alamat Email' => filled($this->email),

            'Nomor Telepon' => filled($this->nomor_telepon),

            'Tanggal Lahir' => filled($this->tanggal_lahir),

            'Jenis Kelamin' => filled($this->jenis_kelamin),

            'Golongan Darah' => filled($this->golongan_darah),

            'Rhesus' => filled($this->rhesus),

            'Alamat' => filled($this->alamat),

            'Provinsi' => filled($this->provinsi),

            'Kota/Kabupaten' => filled($this->kota),

            'Nama Kontak Darurat' => filled(
                $this->nama_kontak_darurat
            ),

            'Telepon Kontak Darurat' => filled(
                $this->telepon_kontak_darurat
            ),
        ];

        $total = count($items);

        $terisi = collect($items)
            ->filter()
            ->count();

        $persentase = $total > 0
            ? (int) round(
                ($terisi / $total) * 100
            )
            : 0;

        return [
            'total' => $total,

            'terisi' => $terisi,

            'persentase' => $persentase,

            'lengkap' => $persentase === 100,

            'belum_lengkap' => collect($items)
                ->reject()
                ->keys()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ringkasanProfil(): array
    {
        $profil =
            $this->profilPendonor();

        $queryPendaftaran =
            PendaftaranDonor::query()
                ->where(
                    'pendonor_id',
                    Auth::id()
                );

        return [
            'kode_pendonor' => (string) (
                $profil?->kode_pendonor
                ?? '-'
            ),

            'total_pendaftaran' => (clone $queryPendaftaran)
                ->count(),

            'donor_selesai' => (clone $queryPendaftaran)
                ->where(
                    'status',
                    StatusPendaftaranDonor::Selesai
                        ->value
                )
                ->count(),

            'golongan_rhesus' => $this
                ->golonganRhesusTampilan(),

            'umur' => $this->umurPendonor(),

            'terakhir_donor' => $profil?->terakhir_donor_pada
                    instanceof CarbonInterface
                        ? $profil
                            ->terakhir_donor_pada
                            ->translatedFormat(
                                'd F Y'
                            )
                        : 'Belum ada',
        ];
    }

    private function muatDataProfil(): void
    {
        $user = $this->penggunaSaatIni();

        if ($user === null) {
            return;
        }

        $profil =
            $this->profilPendonor();

        $this->name =
            (string) $user->name;

        $this->email =
            (string) $user->email;

        $this->nomor_telepon =
            (string) (
                $user->nomor_telepon
                ?? ''
            );

        $this->tanggal_lahir =
            $this->tanggalUntukInput(
                $profil?->tanggal_lahir
            );

        $this->jenis_kelamin =
            $this->nilaiDariEnum(
                $profil?->jenis_kelamin
            );

        $this->golongan_darah =
            $this->nilaiDariEnum(
                $profil?->golongan_darah
            );

        $this->rhesus =
            $this->nilaiDariEnum(
                $profil?->rhesus
            );

        $this->alamat =
            (string) (
                $profil?->alamat
                ?? ''
            );

        $this->provinsi =
            (string) (
                $profil?->provinsi
                ?? ''
            );

        $this->kota =
            (string) (
                $profil?->kota
                ?? ''
            );

        $this->kecamatan =
            (string) (
                $profil?->kecamatan
                ?? ''
            );

        $this->kode_pos =
            (string) (
                $profil?->kode_pos
                ?? ''
            );

        $this->nama_kontak_darurat =
            (string) (
                $profil
                    ?->nama_kontak_darurat
                ?? ''
            );

        $this->telepon_kontak_darurat =
            (string) (
                $profil
                    ?->telepon_kontak_darurat
                ?? ''
            );

        $this->bersedia_dihubungi =
            (bool) (
                $profil
                    ?->bersedia_dihubungi
                ?? true
            );
    }

    private function profilPendonor(): ?ProfilPendonor
    {
        return ProfilPendonor::query()
            ->where(
                'pengguna_id',
                Auth::id()
            )
            ->first();
    }

    private function penggunaSaatIni(): ?User
    {
        $user = Auth::user();

        return $user instanceof User
            ? $user
            : null;
    }

    private function buatKodePendonor(): string
    {
        $tanggal = now()->format(
            'Ymd'
        );

        do {
            $kode = sprintf(
                'DNR-%s-%s',
                $tanggal,
                Str::upper(
                    Str::random(6)
                )
            );
        } while (
            ProfilPendonor::query()
                ->where(
                    'kode_pendonor',
                    $kode
                )
                ->exists()
        );

        return $kode;
    }

    private function nilaiDariEnum(
        mixed $value
    ): string {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof UnitEnum) {
            return (string) $value->name;
        }

        if (blank($value)) {
            return '';
        }

        return trim(
            (string) $value
        );
    }

    private function tanggalUntukInput(
        mixed $value
    ): string {
        if ($value instanceof CarbonInterface) {
            return $value->format(
                'Y-m-d'
            );
        }

        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse(
                $value
            )->format('Y-m-d');
        } catch (Throwable) {
            return '';
        }
    }
}
