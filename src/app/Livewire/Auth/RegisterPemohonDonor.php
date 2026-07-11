<?php

namespace App\Livewire\Auth;

use App\Enums\PeranPengguna;
use App\Enums\StatusPengguna;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.auth')]
class RegisterPemohonDonor extends Component
{
    private const GOOGLE_SESSION_LIFETIME_SECONDS = 600;

    public string $metodePendaftaran = '';

    public string $nama_rumah_sakit = '';

    public string $email = '';

    public string $nomor_telepon = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $nomor_izin = '';

    public string $nama_penanggung_jawab = '';

    public string $jabatan_penanggung_jawab = '';

    public string $alamat = '';

    public string $provinsi = '';

    public string $kota = '';

    public string $kecamatan = '';

    public string $kode_pos = '';

    public bool $menyetujui_ketentuan = false;

    public function mount(): void
    {
        $this->isiDataGoogleRegisterKeForm();
    }

    public function pilihManual(): void
    {
        session()->forget('google_register');

        $this->metodePendaftaran = 'manual';
    }

    public function pilihGoogleTerhubung(): void
    {
        $this->metodePendaftaran = 'google';
    }

    public function resetMetodePendaftaran(): void
    {
        session()->forget('google_register');

        $this->metodePendaftaran = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->nama_penanggung_jawab = '';
    }

    public function register(): mixed
    {
        if ($this->metodePendaftaran === '') {
            $this->addError(
                'metodePendaftaran',
                'Pilih metode pendaftaran terlebih dahulu.'
            );

            return null;
        }

        $menggunakanGoogle = $this->metodePendaftaran === 'google';
        $googleRegister = [];

        if ($menggunakanGoogle) {
            $googleRegister = $this->googleRegisterData();

            if ($googleRegister === []) {
                $this->batalkanPendaftaranGoogle();

                $this->addError(
                    'metodePendaftaran',
                    'Sesi pendaftaran Google tidak valid atau sudah kedaluwarsa. Silakan hubungkan ulang akun Google.'
                );

                return null;
            }

            if (! Schema::hasColumn('users', 'google_id')) {
                $this->addError(
                    'metodePendaftaran',
                    'Pendaftaran Google belum siap digunakan. Hubungi administrator.'
                );

                return null;
            }

            $this->email = (string) $googleRegister['email'];

            if (
                blank($this->nama_penanggung_jawab)
                && filled($googleRegister['name'] ?? null)
            ) {
                $this->nama_penanggung_jawab = (string) $googleRegister['name'];
            }

            $googleIdSudahDigunakan = User::query()
                ->where(
                    'google_id',
                    (string) $googleRegister['google_id']
                )
                ->exists();

            if ($googleIdSudahDigunakan) {
                $this->batalkanPendaftaranGoogle();

                $this->addError(
                    'email',
                    'Akun Google ini sudah terdaftar. Silakan masuk menggunakan Google.'
                );

                return null;
            }
        }

        $data = $this->validate(
            $this->rules($menggunakanGoogle),
            $this->messages()
        );

        if ($menggunakanGoogle) {
            /*
             * Email wajib berasal dari callback Google yang tersimpan
             * di session server, bukan dari nilai Livewire di browser.
             */
            $data['email'] = (string) $googleRegister['email'];
        }

        $user = DB::transaction(
            function () use (
                $data,
                $googleRegister,
                $menggunakanGoogle
            ): User {
                $user = new User();

                $payloadUser = [
                    'name' => trim($data['nama_rumah_sakit']),
                    'email' => mb_strtolower(
                        trim($data['email'])
                    ),
                    'password' => Hash::make(
                        $menggunakanGoogle
                            ? Str::password(40)
                            : $data['password']
                    ),
                ];

                if (Schema::hasColumn('users', 'nomor_telepon')) {
                    $payloadUser['nomor_telepon'] = trim(
                        $data['nomor_telepon']
                    );
                }

                if (Schema::hasColumn('users', 'status')) {
                    $payloadUser['status'] = $this->statusPenggunaAktif();
                }

                if ($menggunakanGoogle) {
                    $payloadUser['google_id'] = (string) $googleRegister['google_id'];

                    if (Schema::hasColumn('users', 'google_avatar')) {
                        $googleAvatar = trim(
                            (string) ($googleRegister['avatar'] ?? '')
                        );

                        $payloadUser['google_avatar'] = $googleAvatar !== ''
                            ? $googleAvatar
                            : null;
                    }

                    if (Schema::hasColumn('users', 'email_verified_at')) {
                        $payloadUser['email_verified_at'] = now();
                    }
                }

                $user->forceFill($payloadUser);
                $user->save();

                $roleName = $this->rolePemohonDonor();

                Role::findOrCreate(
                    $roleName,
                    'web'
                );

                $user->assignRole($roleName);

                $profil = new ProfilRumahSakit();

                $this->isiKolomProfil(
                    $profil,
                    'pengguna_id',
                    $user->id
                );

                $this->isiKolomProfil(
                    $profil,
                    'kode_rumah_sakit',
                    $this->buatKodeRumahSakit()
                );

                $this->isiKolomProfil(
                    $profil,
                    'nama_rumah_sakit',
                    trim($data['nama_rumah_sakit'])
                );

                $this->isiKolomProfil(
                    $profil,
                    'nama_institusi',
                    trim($data['nama_rumah_sakit'])
                );

                $this->isiKolomProfil(
                    $profil,
                    'nomor_izin',
                    trim($data['nomor_izin'])
                );

                $this->isiKolomProfil(
                    $profil,
                    'path_dokumen_izin',
                    null
                );

                $this->isiKolomProfil(
                    $profil,
                    'nama_penanggung_jawab',
                    trim($data['nama_penanggung_jawab'])
                );

                $this->isiKolomProfil(
                    $profil,
                    'jabatan_penanggung_jawab',
                    filled($data['jabatan_penanggung_jawab'])
                        ? trim($data['jabatan_penanggung_jawab'])
                        : null
                );

                $this->isiKolomProfil(
                    $profil,
                    'nomor_telepon',
                    trim($data['nomor_telepon'])
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

                $profil->save();

                return $user;
            }
        );

        session()->forget('google_register');

        Auth::login($user);

        request()->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with(
                    'success',
                    'Pendaftaran pemohon donor berhasil. Link verifikasi telah dikirim ke email Anda.'
                );
        }

        return redirect()
            ->to('/pemohon-donor')
            ->with(
                'success',
                'Pendaftaran pemohon donor dengan Google berhasil.'
            );
    }

    public function render(): View
    {
        return view('livewire.auth.register-pemohon-donor');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $menggunakanGoogle): array
    {
        $passwordRules = $menggunakanGoogle
            ? [
                'nullable',
            ]
            : [
                'required',
                'string',
                'min:8',
                'confirmed',
            ];

        return [
            'nama_rumah_sakit' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'nomor_telepon' => [
                'required',
                'string',
                'min:9',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'password' => $passwordRules,

            'nomor_izin' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],

            'nama_penanggung_jawab' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'jabatan_penanggung_jawab' => [
                'nullable',
                'string',
                'max:255',
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

            'menyetujui_ketentuan' => [
                'accepted',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'nama_rumah_sakit.required' => 'Nama institusi wajib diisi.',
            'nama_rumah_sakit.min' => 'Nama institusi minimal 3 karakter.',

            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar.',

            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',

            'nomor_izin.required' => 'Nomor izin institusi wajib diisi.',
            'nomor_izin.min' => 'Nomor izin minimal 3 karakter.',

            'nama_penanggung_jawab.required' => 'Nama penanggung jawab wajib diisi.',
            'nama_penanggung_jawab.min' => 'Nama penanggung jawab minimal 3 karakter.',

            'alamat.required' => 'Alamat institusi wajib diisi.',
            'alamat.min' => 'Alamat minimal 10 karakter.',

            'provinsi.required' => 'Provinsi wajib diisi.',
            'kota.required' => 'Kota/Kabupaten wajib diisi.',

            'menyetujui_ketentuan.accepted' => 'Persetujuan pendaftaran wajib dicentang.',
        ];
    }

    public function menggunakanGoogle(): bool
    {
        return $this->metodePendaftaran === 'google'
            && $this->googleRegisterData() !== [];
    }

    /**
     * @return array<string, mixed>
     */
    private function googleRegisterData(): array
    {
        $googleRegister = session('google_register');

        if (! is_array($googleRegister)) {
            return [];
        }

        $googleId = trim(
            (string) ($googleRegister['google_id'] ?? '')
        );

        $email = mb_strtolower(
            trim((string) ($googleRegister['email'] ?? ''))
        );

        $authenticatedAt = filter_var(
            $googleRegister['authenticated_at'] ?? null,
            FILTER_VALIDATE_INT
        );

        $expiresAt = filter_var(
            $googleRegister['expires_at'] ?? null,
            FILTER_VALIDATE_INT
        );

        $emailTerverifikasi = filter_var(
            $googleRegister['email_verified'] ?? null,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        $tujuanSesuai = ($googleRegister['tujuan'] ?? null)
            === 'pemohon-donor';

        $identitasValid = $googleId !== ''
            && filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ) !== false
            && $emailTerverifikasi === true;

        $waktuValid = $this->waktuGoogleRegisterValid(
            $authenticatedAt,
            $expiresAt
        );

        if (
            ! $tujuanSesuai
            || ! $identitasValid
            || ! $waktuValid
        ) {
            session()->forget('google_register');

            return [];
        }

        $googleRegister['google_id'] = $googleId;
        $googleRegister['email'] = $email;
        $googleRegister['name'] = trim(
            (string) ($googleRegister['name'] ?? '')
        );
        $googleRegister['avatar'] = trim(
            (string) ($googleRegister['avatar'] ?? '')
        );
        $googleRegister['email_verified'] = true;
        $googleRegister['authenticated_at'] = (int) $authenticatedAt;
        $googleRegister['expires_at'] = (int) $expiresAt;

        return $googleRegister;
    }

    private function waktuGoogleRegisterValid(
        int|false $authenticatedAt,
        int|false $expiresAt
    ): bool {
        if (
            $authenticatedAt === false
            || $expiresAt === false
        ) {
            return false;
        }

        $sekarang = now()->timestamp;

        if (
            $authenticatedAt > $sekarang
            || $expiresAt < $sekarang
            || $expiresAt < $authenticatedAt
        ) {
            return false;
        }

        return ($sekarang - $authenticatedAt)
                <= self::GOOGLE_SESSION_LIFETIME_SECONDS
            && ($expiresAt - $authenticatedAt)
                <= self::GOOGLE_SESSION_LIFETIME_SECONDS;
    }

    private function batalkanPendaftaranGoogle(): void
    {
        session()->forget('google_register');

        $this->metodePendaftaran = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    private function isiDataGoogleRegisterKeForm(): void
    {
        $googleRegister = $this->googleRegisterData();

        if ($googleRegister === []) {
            return;
        }

        $this->metodePendaftaran = 'google';
        $this->email = (string) $googleRegister['email'];
        $this->nama_penanggung_jawab = (string) (
            $googleRegister['name']
            ?? ''
        );
        $this->password = '';
        $this->password_confirmation = '';
    }

    private function isiKolomProfil(
        ProfilRumahSakit $profil,
        string $kolom,
        mixed $nilai
    ): void {
        if (! Schema::hasColumn($this->tabelProfilRumahSakit(), $kolom)) {
            return;
        }

        $profil->setAttribute(
            $kolom,
            $nilai
        );
    }

    private function tabelProfilRumahSakit(): string
    {
        return (new ProfilRumahSakit())->getTable();
    }

    private function buatKodeRumahSakit(): string
    {
        if (
            ! Schema::hasColumn(
                $this->tabelProfilRumahSakit(),
                'kode_rumah_sakit'
            )
        ) {
            return 'PMH-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));
        }

        $tanggal = now()->format('Ymd');

        for ($i = 1; $i <= 30; $i++) {
            $nomorUrut = str_pad(
                (string) (
                    ProfilRumahSakit::query()
                        ->whereDate('created_at', today())
                        ->count() + $i
                ),
                4,
                '0',
                STR_PAD_LEFT
            );

            $kode = "PMH-{$tanggal}-{$nomorUrut}";

            if (
                ! ProfilRumahSakit::query()
                    ->where('kode_rumah_sakit', $kode)
                    ->exists()
            ) {
                return $kode;
            }
        }

        return 'PMH-'
            . $tanggal
            . '-'
            . strtoupper(Str::random(8));
    }

    private function rolePemohonDonor(): string
    {
        if (class_exists(PeranPengguna::class)) {
            foreach ([
                'PemohonDonor',
                'RumahSakit',
                'Pemohon',
                'Hospital',
            ] as $caseName) {
                $constant = PeranPengguna::class . '::' . $caseName;

                if (defined($constant)) {
                    return $this->nilaiDariEnum(
                        constant($constant)
                    );
                }
            }
        }

        return 'rumah_sakit';
    }

    private function statusPenggunaAktif(): string
    {
        if (class_exists(StatusPengguna::class)) {
            foreach ([
                'Aktif',
                'Active',
            ] as $caseName) {
                $constant = StatusPengguna::class . '::' . $caseName;

                if (defined($constant)) {
                    return $this->nilaiDariEnum(
                        constant($constant)
                    );
                }
            }
        }

        return 'active';
    }

    private function nilaiDariEnum(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        if (blank($value)) {
            return '';
        }

        return (string) $value;
    }
}