<?php

namespace App\Livewire\Donor;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Enums\RhesusDarah;
use App\Models\PendaftaranDonor;
use App\Models\ProfilPendonor;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.donor')]
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

    public bool $bersedia_dihubungi = true;

    public ?string $notifikasiBerhasil = null;

    public function mount(): void
    {
        $this->muatDataProfil();
    }

    public function hapusNotifikasi(): void
    {
        $this->notifikasiBerhasil = null;
    }

    public function simpanProfil(): mixed
    {
        $user = $this->penggunaSaatIni();

        if ($user === null) {
            return redirect('/login');
        }

        $data = $this->validate([
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
                Rule::unique('users', 'email')->ignore($user->id),
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
                'date',
                'before_or_equal:' . now()
                    ->subYears(17)
                    ->format('Y-m-d'),
            ],

            'jenis_kelamin' => [
                'required',
                Rule::in($this->nilaiEnumCases(JenisKelamin::cases())),
            ],

            'golongan_darah' => [
                'required',
                Rule::in($this->nilaiEnumCases(GolonganDarah::cases())),
            ],

            'rhesus' => [
                'required',
                Rule::in($this->nilaiEnumCases(RhesusDarah::cases())),
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
                'max:100',
            ],

            'kota' => [
                'required',
                'string',
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
                'max:10',
            ],

            'bersedia_dihubungi' => [
                'boolean',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 3 karakter.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan.',

            'nomor_telepon.required' => 'Nomor HP wajib diisi.',
            'nomor_telepon.regex' => 'Format nomor HP tidak valid.',

            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before_or_equal' => 'Pendonor minimal berusia 17 tahun.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'golongan_darah.required' => 'Golongan darah wajib dipilih.',
            'rhesus.required' => 'Rhesus wajib dipilih.',

            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.min' => 'Alamat minimal 10 karakter.',

            'provinsi.required' => 'Provinsi wajib diisi.',
            'kota.required' => 'Kota/Kabupaten wajib diisi.',
        ]);

        DB::transaction(function () use ($user, $data): void {
            $payloadUser = [
                'name' => trim($data['name']),
                'email' => mb_strtolower(trim($data['email'])),
            ];

            if (Schema::hasColumn('users', 'nomor_telepon')) {
                $payloadUser['nomor_telepon'] = trim($data['nomor_telepon']);
            }

            $user->forceFill($payloadUser);
            $user->save();

            $profil = ProfilPendonor::query()
                ->where('pengguna_id', $user->id)
                ->first();

            if ($profil === null) {
                $profil = new ProfilPendonor();
                $profil->setAttribute('pengguna_id', $user->id);
            }

            $kodePendonor = $profil->getAttribute('kode_pendonor');

            $this->isiKolomProfil(
                $profil,
                'kode_pendonor',
                filled($kodePendonor)
                    ? $kodePendonor
                    : $this->buatKodePendonor()
            );

            $this->isiKolomProfil(
                $profil,
                'tanggal_lahir',
                $data['tanggal_lahir']
            );

            $this->isiKolomProfil(
                $profil,
                'jenis_kelamin',
                $data['jenis_kelamin']
            );

            $this->isiKolomProfil(
                $profil,
                'golongan_darah',
                $data['golongan_darah']
            );

            $this->isiKolomProfil(
                $profil,
                'rhesus',
                $data['rhesus']
            );

            $this->isiKolomProfil(
                $profil,
                'alamat',
                trim($data['alamat'])
            );

            $this->isiKolomProfil(
                $profil,
                'provinsi',
                trim($data['provinsi'])
            );

            $this->isiKolomProfil(
                $profil,
                'kota',
                trim($data['kota'])
            );

            $this->isiKolomProfil(
                $profil,
                'kecamatan',
                filled($data['kecamatan'])
                    ? trim($data['kecamatan'])
                    : null
            );

            $this->isiKolomProfil(
                $profil,
                'kode_pos',
                filled($data['kode_pos'])
                    ? trim($data['kode_pos'])
                    : null
            );

            $this->isiKolomProfil(
                $profil,
                'bersedia_dihubungi',
                (bool) $data['bersedia_dihubungi']
            );

            $profil->save();
        });

        $this->muatDataProfil();

        $this->notifikasiBerhasil = 'Profil berhasil disimpan.';

        session()->flash(
            'success',
            $this->notifikasiBerhasil
        );

        return null;
    }

    public function render(): View
    {
        return view('livewire.donor.profil', [
            'profilPendonor' => $this->profilPendonor(),
            'kelengkapan' => $this->kelengkapanProfil(),
            'ringkasan' => $this->ringkasanProfil(),
        ]);
    }

    public function opsiJenisKelamin(): array
    {
        return collect(JenisKelamin::cases())
            ->map(fn (\UnitEnum $case): array => [
                'value' => $this->nilaiDariEnum($case),
                'label' => $this->labelJenisKelamin(
                    $this->nilaiDariEnum($case)
                ),
            ])
            ->values()
            ->all();
    }

    public function opsiGolonganDarah(): array
    {
        return collect(GolonganDarah::cases())
            ->map(fn (\UnitEnum $case): array => [
                'value' => $this->nilaiDariEnum($case),
                'label' => $this->labelGolonganDarah(
                    $this->nilaiDariEnum($case)
                ),
            ])
            ->values()
            ->all();
    }

    public function opsiRhesusDarah(): array
    {
        return collect(RhesusDarah::cases())
            ->map(fn (\UnitEnum $case): array => [
                'value' => $this->nilaiDariEnum($case),
                'label' => $this->labelRhesusDarah(
                    $this->nilaiDariEnum($case)
                ),
            ])
            ->values()
            ->all();
    }

    public function labelJenisKelamin(string $value): string
    {
        return match (strtolower($value)) {
            'male',
            'laki_laki',
            'laki-laki',
            'pria' => 'Laki-laki',

            'female',
            'perempuan',
            'wanita' => 'Perempuan',

            default => Str::headline(
                str_replace(['_', '-'], ' ', $value)
            ),
        };
    }

    public function labelGolonganDarah(string $value): string
    {
        return strtoupper($value);
    }

    public function labelRhesusDarah(string $value): string
    {
        return match (strtolower($value)) {
            'positive',
            'positif',
            '+',
            'rh+' => 'Positif (+)',

            'negative',
            'negatif',
            '-',
            'rh-' => 'Negatif (-)',

            default => Str::headline(
                str_replace(['_', '-'], ' ', $value)
            ),
        };
    }

    public function golonganRhesusTampilan(): string
    {
        if (
            blank($this->golongan_darah)
            || blank($this->rhesus)
        ) {
            return '-';
        }

        $rhesus = match (strtolower($this->rhesus)) {
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

        return strtoupper($this->golongan_darah) . $rhesus;
    }

    public function umurPendonor(): string
    {
        if (blank($this->tanggal_lahir)) {
            return '-';
        }

        try {
            return Carbon::parse($this->tanggal_lahir)->age . ' tahun';
        } catch (\Throwable) {
            return '-';
        }
    }

    public function tanggalLahirTampilan(): string
    {
        if (blank($this->tanggal_lahir)) {
            return '-';
        }

        try {
            return Carbon::parse($this->tanggal_lahir)
                ->translatedFormat('d F Y');
        } catch (\Throwable) {
            return '-';
        }
    }

    public function kelengkapanProfil(): array
    {
        $items = [
            [
                'label' => 'Nama Lengkap',
                'filled' => filled($this->name),
            ],
            [
                'label' => 'Alamat Email',
                'filled' => filled($this->email),
            ],
            [
                'label' => 'Nomor HP',
                'filled' => filled($this->nomor_telepon),
            ],
            [
                'label' => 'Tanggal Lahir',
                'filled' => filled($this->tanggal_lahir),
            ],
            [
                'label' => 'Jenis Kelamin',
                'filled' => filled($this->jenis_kelamin),
            ],
            [
                'label' => 'Golongan Darah',
                'filled' => filled($this->golongan_darah),
            ],
            [
                'label' => 'Rhesus',
                'filled' => filled($this->rhesus),
            ],
            [
                'label' => 'Alamat',
                'filled' => filled($this->alamat),
            ],
            [
                'label' => 'Provinsi',
                'filled' => filled($this->provinsi),
            ],
            [
                'label' => 'Kota/Kabupaten',
                'filled' => filled($this->kota),
            ],
        ];

        $total = count($items);

        $terisi = collect($items)
            ->filter(fn (array $item): bool => $item['filled'])
            ->count();

        $persentase = $total > 0
            ? (int) round(($terisi / $total) * 100)
            : 0;

        return [
            'total' => $total,
            'terisi' => $terisi,
            'persentase' => $persentase,
            'lengkap' => $persentase === 100,
            'belum_lengkap' => collect($items)
                ->reject(fn (array $item): bool => $item['filled'])
                ->pluck('label')
                ->values()
                ->all(),
        ];
    }

    public function ringkasanProfil(): array
    {
        $profil = $this->profilPendonor();

        return [
            'kode_pendonor' => (string) (
                $profil?->getAttribute('kode_pendonor')
                ?? '-'
            ),

            'total_pendaftaran' => PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->count(),

            'donor_selesai' => PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->where('status', 'completed')
                ->count(),

            'golongan_rhesus' => $this->golonganRhesusTampilan(),

            'umur' => $this->umurPendonor(),
        ];
    }

    private function muatDataProfil(): void
    {
        $user = $this->penggunaSaatIni();

        if ($user === null) {
            return;
        }

        $profil = $this->profilPendonor();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->nomor_telepon = (string) (
            $user->getAttribute('nomor_telepon')
            ?? ''
        );

        $this->tanggal_lahir = $this->tanggalUntukInput(
            $profil?->getAttribute('tanggal_lahir')
        );

        $this->jenis_kelamin = $this->nilaiDariEnum(
            $profil?->getAttribute('jenis_kelamin')
        );

        $this->golongan_darah = $this->nilaiDariEnum(
            $profil?->getAttribute('golongan_darah')
        );

        $this->rhesus = $this->nilaiDariEnum(
            $profil?->getAttribute('rhesus')
        );

        $this->alamat = (string) (
            $profil?->getAttribute('alamat')
            ?? ''
        );

        $this->provinsi = (string) (
            $profil?->getAttribute('provinsi')
            ?? ''
        );

        $this->kota = (string) (
            $profil?->getAttribute('kota')
            ?? ''
        );

        $this->kecamatan = (string) (
            $profil?->getAttribute('kecamatan')
            ?? ''
        );

        $this->kode_pos = (string) (
            $profil?->getAttribute('kode_pos')
            ?? ''
        );

        $this->bersedia_dihubungi = (bool) (
            $profil?->getAttribute('bersedia_dihubungi')
            ?? true
        );
    }

    private function profilPendonor(): ?ProfilPendonor
    {
        return ProfilPendonor::query()
            ->where('pengguna_id', Auth::id())
            ->first();
    }

    private function penggunaSaatIni(): ?User
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    private function isiKolomProfil(
        ProfilPendonor $profil,
        string $kolom,
        mixed $nilai
    ): void {
        if (! Schema::hasColumn($this->tabelProfilPendonor(), $kolom)) {
            return;
        }

        $profil->setAttribute($kolom, $nilai);
    }

    private function tabelProfilPendonor(): string
    {
        return (new ProfilPendonor())->getTable();
    }

    private function buatKodePendonor(): string
    {
        if (! Schema::hasColumn($this->tabelProfilPendonor(), 'kode_pendonor')) {
            return 'DNR-' . now()->format('Ymd') . '-' . strtoupper(
                Str::random(6)
            );
        }

        $tanggal = now()->format('Ymd');

        for ($i = 1; $i <= 20; $i++) {
            $nomorUrut = str_pad(
                (string) (
                    ProfilPendonor::query()
                        ->whereDate('created_at', today())
                        ->count() + $i
                ),
                4,
                '0',
                STR_PAD_LEFT
            );

            $kode = "DNR-{$tanggal}-{$nomorUrut}";

            if (
                ! ProfilPendonor::query()
                    ->where('kode_pendonor', $kode)
                    ->exists()
            ) {
                return $kode;
            }
        }

        return 'DNR-' . $tanggal . '-' . strtoupper(
            Str::random(8)
        );
    }

    private function nilaiEnumCases(array $cases): array
    {
        return collect($cases)
            ->map(fn (\UnitEnum $case): string => $this->nilaiDariEnum($case))
            ->values()
            ->all();
    }

    private function nilaiDariEnum(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        if (blank($value)) {
            return '';
        }

        return (string) $value;
    }

    private function tanggalUntukInput(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if (blank($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }
}