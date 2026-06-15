<?php

namespace App\Livewire\Donor;

use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use App\Models\ProfilPendonor;
use App\Models\User;
use App\Services\LayananStokDarah;
use BackedEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.donor')]
class Portal extends Component
{
    use WithPagination;

    public string $section = 'beranda';

    public string $search = '';

    public string $name = '';

    public string $nomorTelepon = '';

    public string $tanggalLahir = '';

    public string $jenisKelamin = '';

    public string $alamat = '';

    public string $provinsi = '';

    public string $kota = '';

    public string $kecamatan = '';

    public string $kodePos = '';

    public string $namaKontakDarurat = '';

    public string $teleponKontakDarurat = '';

    public bool $bersediaDihubungi = false;

    /**
     * @var array<int, string>
     */
    private const DAFTAR_SECTION = [
        'beranda',
        'jadwal',
        'lokasi',
        'stok',
        'riwayat',
        'profil',
    ];

    public function mount(
        string $section = 'beranda'
    ): void {
        if (
            ! in_array(
                $section,
                self::DAFTAR_SECTION,
                true
            )
        ) {
            abort(404);
        }

        $this->section = $section;

        $pengguna = Auth::user();

        if (! $pengguna instanceof User) {
            $this->redirect(
                '/login',
                navigate: true
            );

            return;
        }

        if (! $pengguna->hasRole('donor')) {
            $tujuan = $pengguna->hasAnyRole([
                'super_admin',
                'petugas',
            ])
                ? '/admin'
                : '/';

            $this->redirect(
                $tujuan,
                navigate: true
            );

            return;
        }

        $this->isiFormProfil($pengguna);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function simpanProfil(): void
    {
        $pengguna = Auth::user();

        if (
            ! $pengguna instanceof User
            || ! $pengguna->hasRole('donor')
        ) {
            $this->redirect(
                '/',
                navigate: true
            );

            return;
        }

        $data = $this->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'nomorTelepon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'tanggalLahir' => [
                    'required',
                    'date',
                    'before:today',
                ],

                'jenisKelamin' => [
                    'required',
                    'string',
                ],

                'alamat' => [
                    'required',
                    'string',
                    'max:5000',
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

                'kodePos' => [
                    'nullable',
                    'string',
                    'max:10',
                ],

                'namaKontakDarurat' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'teleponKontakDarurat' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'bersediaDihubungi' => [
                    'boolean',
                ],
            ],
            [
                'name.required' =>
                    'Nama lengkap wajib diisi.',

                'tanggalLahir.required' =>
                    'Tanggal lahir wajib diisi.',

                'tanggalLahir.before' =>
                    'Tanggal lahir harus sebelum hari ini.',

                'jenisKelamin.required' =>
                    'Jenis kelamin wajib dipilih.',

                'alamat.required' =>
                    'Alamat lengkap wajib diisi.',

                'provinsi.required' =>
                    'Provinsi wajib diisi.',

                'kota.required' =>
                    'Kota atau kabupaten wajib diisi.',
            ]
        );

        $profil = ProfilPendonor::query()
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first();

        if ($profil === null) {
            $this->addError(
                'profil',
                'Profil Pendonor belum tersedia.'
            );

            return;
        }

        $pengguna->update([
            'name' => trim($data['name']),

            'nomor_telepon' =>
                filled($data['nomorTelepon'])
                    ? trim($data['nomorTelepon'])
                    : null,
        ]);

        $profil->update([
            'tanggal_lahir' =>
                $data['tanggalLahir'],

            'jenis_kelamin' =>
                $data['jenisKelamin'],

            'alamat' =>
                trim($data['alamat']),

            'provinsi' =>
                trim($data['provinsi']),

            'kota' =>
                trim($data['kota']),

            'kecamatan' =>
                filled($data['kecamatan'])
                    ? trim($data['kecamatan'])
                    : null,

            'kode_pos' =>
                filled($data['kodePos'])
                    ? trim($data['kodePos'])
                    : null,

            'nama_kontak_darurat' =>
                filled($data['namaKontakDarurat'])
                    ? trim($data['namaKontakDarurat'])
                    : null,

            'telepon_kontak_darurat' =>
                filled($data['teleponKontakDarurat'])
                    ? trim($data['teleponKontakDarurat'])
                    : null,

            'bersedia_dihubungi' =>
                $data['bersediaDihubungi'],
        ]);

        $this->isiFormProfil(
            $pengguna->refresh()
        );

        session()->flash(
            'success',
            'Profil berhasil diperbarui.'
        );
    }

    public function render(): View
    {
        $pengguna = Auth::user();

        if (! $pengguna instanceof User) {
            abort(401);
        }

        $data = match ($this->section) {
            'jadwal' =>
                $this->dataJadwal(),

            'lokasi' =>
                $this->dataLokasi(),

            'stok' =>
                $this->dataStok(),

            'riwayat' =>
                $this->dataRiwayat($pengguna),

            'profil' =>
                $this->dataProfil($pengguna),

            default =>
                $this->dataBeranda($pengguna),
        };

        $data['pageTitle'] =
            $this->judulHalaman();

        return view(
            'livewire.donor.portal',
            $data
        );
    }

    public function labelEnum(
        mixed $nilai
    ): string {
        if (
            is_object($nilai)
            && method_exists(
                $nilai,
                'label'
            )
        ) {
            return (string) $nilai->label();
        }

        if ($nilai instanceof BackedEnum) {
            return Str::headline(
                (string) $nilai->value
            );
        }

        return filled($nilai)
            ? Str::headline(
                (string) $nilai
            )
            : '-';
    }

    public function valueEnum(
        mixed $nilai
    ): string {
        if ($nilai instanceof BackedEnum) {
            return (string) $nilai->value;
        }

        return filled($nilai)
            ? (string) $nilai
            : '';
    }

    public function simbolRhesus(
        mixed $nilai
    ): string {
        if (
            is_object($nilai)
            && method_exists(
                $nilai,
                'simbol'
            )
        ) {
            return (string) $nilai->simbol();
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    private function dataBeranda(
        User $pengguna
    ): array {
        $jadwalTerdekat = JadwalDonor::query()
            ->where(
                'status',
                StatusJadwalDonor
                    ::Dipublikasikan
                    ->value
            )
            ->where(
                'selesai_pada',
                '>=',
                now()
            )
            ->orderBy('mulai_pada')
            ->first();

        $lokasiJadwal = $jadwalTerdekat
            ? LokasiDonor::query()->find(
                $jadwalTerdekat->getAttribute(
                    'lokasi_donor_id'
                )
            )
            : null;

        $ringkasanStok = app(
            LayananStokDarah::class
        )->ringkasanPublik();

        $stokPerGolongan =
            $this->stokPerGolongan(
                collect(
                    $ringkasanStok['data']
                    ?? []
                )
            );

        $jumlahRiwayat =
            PendaftaranDonor::query()
                ->where(
                    'pendonor_id',
                    $pengguna->id
                )
                ->count();

        $donorSelesai =
            PendaftaranDonor::query()
                ->where(
                    'pendonor_id',
                    $pengguna->id
                )
                ->where(
                    'status',
                    StatusPendaftaranDonor
                        ::Selesai
                        ->value
                )
                ->count();

        return [
            'jadwalTerdekat' =>
                $jadwalTerdekat,

            'namaLokasiJadwal' =>
                $lokasiJadwal
                    ? $this->namaLokasi(
                        $lokasiJadwal
                    )
                    : 'Lokasi belum tersedia',

            'stokPerGolongan' =>
                $stokPerGolongan,

            'jumlahLokasi' =>
                LokasiDonor::query()
                    ->count(),

            'totalStok' =>
                (int) (
                    $ringkasanStok['meta']
                    ['total_kantong_tersedia']
                    ?? 0
                ),

            'jumlahRiwayat' =>
                $jumlahRiwayat,

            'donorSelesai' =>
                $donorSelesai,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataJadwal(): array
    {
        $query = JadwalDonor::query()
            ->where(
                'status',
                StatusJadwalDonor
                    ::Dipublikasikan
                    ->value
            )
            ->where(
                'selesai_pada',
                '>=',
                now()
            );

        if (filled($this->search)) {
            $query->where(
                'judul',
                'like',
                '%' . trim($this->search) . '%'
            );
        }

        $jadwal = $query
            ->orderBy('mulai_pada')
            ->paginate(6);

        $lokasiIds = collect(
            $jadwal->items()
        )
            ->pluck('lokasi_donor_id')
            ->filter()
            ->unique()
            ->values();

        $lokasi = LokasiDonor::query()
            ->whereIn(
                'id',
                $lokasiIds
            )
            ->get()
            ->mapWithKeys(
                fn (
                    LokasiDonor $record
                ): array => [
                    $record->id =>
                        $this->namaLokasi(
                            $record
                        ),
                ]
            );

        return [
            'jadwal' =>
                $jadwal,

            'lokasiJadwal' =>
                $lokasi,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataLokasi(): array
    {
        $model = new LokasiDonor();

        $columns = Schema::getColumnListing(
            $model->getTable()
        );

        $query = LokasiDonor::query();

        if (filled($this->search)) {
            $search = trim(
                $this->search
            );

            $searchableColumns = array_values(
                array_filter([
                    $this->kolomTersedia(
                        $columns,
                        [
                            'nama',
                            'nama_lokasi',
                            'judul',
                        ]
                    ),

                    $this->kolomTersedia(
                        $columns,
                        [
                            'alamat',
                            'alamat_lengkap',
                        ]
                    ),

                    $this->kolomTersedia(
                        $columns,
                        [
                            'kota',
                            'kabupaten_kota',
                            'kabupaten',
                        ]
                    ),
                ])
            );

            if ($searchableColumns !== []) {
                $query->where(
                    function (
                        Builder $subQuery
                    ) use (
                        $searchableColumns,
                        $search
                    ): void {
                        foreach (
                            $searchableColumns
                            as $index => $column
                        ) {
                            if ($index === 0) {
                                $subQuery->where(
                                    $column,
                                    'like',
                                    '%' . $search . '%'
                                );

                                continue;
                            }

                            $subQuery->orWhere(
                                $column,
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    }
                );
            }
        }

        $lokasi = $query
            ->latest('id')
            ->paginate(9);

        $lokasiCards = collect(
            $lokasi->items()
        )->map(
            fn (
                LokasiDonor $record
            ): array => $this
                ->normalisasiLokasi(
                    $record
                )
        );

        return [
            'lokasi' =>
                $lokasi,

            'lokasiCards' =>
                $lokasiCards,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataStok(): array
    {
        $ringkasan = app(
            LayananStokDarah::class
        )->ringkasanPublik();

        return [
            'stokDarah' =>
                collect(
                    $ringkasan['data']
                    ?? []
                ),

            'metaStok' =>
                $ringkasan['meta']
                ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataRiwayat(
        User $pengguna
    ): array {
        $riwayat =
            PendaftaranDonor::query()
                ->with('jadwal')
                ->where(
                    'pendonor_id',
                    $pengguna->id
                )
                ->latest()
                ->paginate(10);

        return [
            'riwayat' =>
                $riwayat,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataProfil(
        User $pengguna
    ): array {
        return [
            'profil' =>
                ProfilPendonor::query()
                    ->where(
                        'pengguna_id',
                        $pengguna->id
                    )
                    ->first(),
        ];
    }

    private function isiFormProfil(
        User $pengguna
    ): void {
        $profil = ProfilPendonor::query()
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first();

        $this->name =
            (string) $pengguna->name;

        $this->nomorTelepon =
            (string) (
                $pengguna->nomor_telepon
                ?? ''
            );

        if ($profil === null) {
            return;
        }

        $this->tanggalLahir =
            $profil->tanggal_lahir
                ?->format('Y-m-d')
            ?? '';

        $this->jenisKelamin =
            $this->valueEnum(
                $profil->jenis_kelamin
            );

        $this->alamat =
            (string) (
                $profil->alamat
                ?? ''
            );

        $this->provinsi =
            (string) (
                $profil->provinsi
                ?? ''
            );

        $this->kota =
            (string) (
                $profil->kota
                ?? ''
            );

        $this->kecamatan =
            (string) (
                $profil->kecamatan
                ?? ''
            );

        $this->kodePos =
            (string) (
                $profil->kode_pos
                ?? ''
            );

        $this->namaKontakDarurat =
            (string) (
                $profil
                    ->nama_kontak_darurat
                ?? ''
            );

        $this->teleponKontakDarurat =
            (string) (
                $profil
                    ->telepon_kontak_darurat
                ?? ''
            );

        $this->bersediaDihubungi =
            (bool) $profil
                ->bersedia_dihubungi;
    }

    private function judulHalaman(): string
    {
        return match ($this->section) {
            'jadwal' =>
                'Jadwal Donor',

            'lokasi' =>
                'Lokasi Donor',

            'stok' =>
                'Stok Darah',

            'riwayat' =>
                'Riwayat Donor',

            'profil' =>
                'Profil Saya',

            default =>
                'Beranda Pendonor',
        };
    }

    private function namaLokasi(
        LokasiDonor $lokasi
    ): string {
        return (string) (
            $lokasi->getAttribute('nama')
            ?? $lokasi->getAttribute(
                'nama_lokasi'
            )
            ?? $lokasi->getAttribute(
                'judul'
            )
            ?? 'Lokasi Donor'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalisasiLokasi(
        LokasiDonor $lokasi
    ): array {
        return [
            'id' =>
                $lokasi->id,

            'nama' =>
                $this->namaLokasi(
                    $lokasi
                ),

            'alamat' =>
                $lokasi->getAttribute(
                    'alamat'
                )
                ?? $lokasi->getAttribute(
                    'alamat_lengkap'
                )
                ?? '-',

            'kota' =>
                $lokasi->getAttribute(
                    'kota'
                )
                ?? $lokasi->getAttribute(
                    'kabupaten_kota'
                )
                ?? $lokasi->getAttribute(
                    'kabupaten'
                )
                ?? '-',

            'provinsi' =>
                $lokasi->getAttribute(
                    'provinsi'
                )
                ?? '-',

            'telepon' =>
                $lokasi->getAttribute(
                    'nomor_telepon'
                )
                ?? $lokasi->getAttribute(
                    'telepon'
                )
                ?? '-',
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $data
     *
     * @return array<string, int>
     */
    private function stokPerGolongan(
        Collection $data
    ): array {
        $hasil = [
            'A' => 0,
            'B' => 0,
            'O' => 0,
            'AB' => 0,
        ];

        foreach ($data as $item) {
            $golongan = (string) (
                $item['golongan_darah']
                ['label']
                ?? $item['golongan_darah']
                ['value']
                ?? ''
            );

            if (
                array_key_exists(
                    $golongan,
                    $hasil
                )
            ) {
                $hasil[$golongan] +=
                    (int) (
                        $item['jumlah_kantong']
                        ?? 0
                    );
            }
        }

        return $hasil;
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $candidates
     */
    private function kolomTersedia(
        array $columns,
        array $candidates
    ): ?string {
        foreach ($candidates as $candidate) {
            if (
                in_array(
                    $candidate,
                    $columns,
                    true
                )
            ) {
                return $candidate;
            }
        }

        return null;
    }
}