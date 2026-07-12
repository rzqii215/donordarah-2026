<?php

namespace App\Livewire\Donor;

use App\Enums\StatusKantongDarah;
use App\Enums\StatusKelayakanDonor;
use App\Enums\StatusMutuKantongDarah;
use App\Enums\StatusPendaftaranDonor;
use App\Models\JadwalDonor;
use App\Models\KantongDarah;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use App\Models\PemeriksaanKesehatan;
use App\Models\ProfilPendonor;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.donor')]
#[Title('Beranda')]
class Portal extends Component
{
    public function render(): View
    {
        $user = $this->penggunaSaatIni();
        $profil = $this->profilPendonor();
        $jadwalTerdekat = $this->jadwalTerdekat();
        $riwayatTerbaru = $this->riwayatTerbaru();
        $pendaftaranTerakhir = $this->pendaftaranTerakhir();
        $pemeriksaanTerakhir = $this->pemeriksaanTerakhir();

        return view('livewire.donor.portal', [
            /*
             * Data utama dashboard baru.
             */
            'ringkasan' => $this->ringkasanBeranda(
                $user,
                $profil
            ),

            'statusKelayakan' => $this->statusKelayakan(
                $pemeriksaanTerakhir,
                $pendaftaranTerakhir
            ),

            'pendaftaranTerakhir' =>
                $pendaftaranTerakhir,

            'tahapanPendaftaran' =>
                $this->tahapanPendaftaran(
                    $pendaftaranTerakhir
                ),

            'jadwalBerikutnya' =>
                $jadwalTerdekat->first(),

            'aktivitasTerbaru' =>
                $riwayatTerbaru,

            /*
             * Data kompatibilitas untuk Blade lama.
             * Variabel ini dapat dilepas setelah tampilan baru terpasang.
             */
            'profilPendonor' => $profil,
            'jadwalTerdekat' => $jadwalTerdekat,
            'stokRingkas' => $this->stokRingkas(),
            'lokasiTerdekat' => $this->lokasiTerdekat(),
            'riwayatTerbaru' => $riwayatTerbaru,
        ]);
    }

    private function penggunaSaatIni(): User
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof User,
            401
        );

        return $user;
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

    private function pendaftaranTerakhir(): ?PendaftaranDonor
    {
        return PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
            ])
            ->where(
                'pendonor_id',
                Auth::id()
            )
            ->latest('created_at')
            ->latest('id')
            ->first();
    }

    private function pemeriksaanTerakhir(): ?PemeriksaanKesehatan
    {
        return PemeriksaanKesehatan::query()
            ->with([
                'pendaftaran.jadwal.lokasi',
            ])
            ->whereHas(
                'pendaftaran',
                function (Builder $query): void {
                    $query->where(
                        'pendonor_id',
                        Auth::id()
                    );
                }
            )
            ->orderByDesc('diperiksa_pada')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function ringkasanBeranda(
        User $user,
        ?ProfilPendonor $profil
    ): array {
        $queryPendaftaran = PendaftaranDonor::query()
            ->where(
                'pendonor_id',
                $user->getKey()
            );

        $jumlahSelesai = (clone $queryPendaftaran)
            ->where(
                'status',
                StatusPendaftaranDonor::Selesai->value
            )
            ->count();

        $jumlahProses = (clone $queryPendaftaran)
            ->whereIn(
                'status',
                [
                    StatusPendaftaranDonor::Menunggu->value,
                    StatusPendaftaranDonor::Disetujui->value,
                    StatusPendaftaranDonor::Hadir->value,
                    StatusPendaftaranDonor::Layak->value,
                ]
            )
            ->count();

        $pendaftaranSelesaiTerakhir =
            (clone $queryPendaftaran)
                ->where(
                    'status',
                    StatusPendaftaranDonor::Selesai->value
                )
                ->orderByDesc('selesai_pada')
                ->orderByDesc('id')
                ->first();

        $tanggalDonorTerakhir =
            $this->tanggalCarbon(
                $this->atribut(
                    $profil,
                    'terakhir_donor_pada'
                )
            )
            ?? $this->tanggalCarbon(
                $this->atribut(
                    $pendaftaranSelesaiTerakhir,
                    'selesai_pada'
                )
            );

        return [
            'nama_user' => (string) (
                $this->atribut($user, 'name')
                ?? 'Pendonor'
            ),

            'nama_depan' => $this->namaDepan(
                (string) (
                    $this->atribut($user, 'name')
                    ?? 'Pendonor'
                )
            ),

            'inisial' => $this->inisialNama(
                (string) (
                    $this->atribut($user, 'name')
                    ?? 'P'
                )
            ),

            'kode_pendonor' => (string) (
                $this->atribut(
                    $profil,
                    'kode_pendonor'
                )
                ?? '-'
            ),

            'golongan_rhesus' =>
                $this->golonganRhesusProfil(
                    $profil
                ),

            'profil_lengkap' =>
                $this->persentaseProfil(
                    $user,
                    $profil
                ),

            'total_pendaftaran' =>
                (clone $queryPendaftaran)
                    ->count(),

            'pendaftaran_proses' =>
                $jumlahProses,

            'donor_selesai' =>
                $jumlahSelesai,

            'tanggal_donor_terakhir' =>
                $tanggalDonorTerakhir,

            'donor_terakhir_label' =>
                $tanggalDonorTerakhir
                    ? $tanggalDonorTerakhir
                        ->translatedFormat(
                            'd F Y'
                        )
                    : 'Belum ada',

            'stok_tersedia' =>
                KantongDarah::query()
                    ->where(
                        'status',
                        $this->statusKantongTersedia()
                    )
                    ->where(
                        'status_mutu',
                        $this->statusMutuLulus()
                    )
                    ->count(),

            'jadwal_aktif' =>
                $this->queryJadwalAktif()
                    ->count(),

            'lokasi_aktif' =>
                $this->queryLokasiAktif()
                    ->count(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function statusKelayakan(
        ?PemeriksaanKesehatan $pemeriksaan,
        ?PendaftaranDonor $pendaftaran
    ): array {
        if ($pemeriksaan instanceof PemeriksaanKesehatan) {
            $status = $this->nilaiDariEnum(
                $pemeriksaan->status_kelayakan
            );

            $tanggal = $this->tanggalCarbon(
                $pemeriksaan->diperiksa_pada
            );

            if (
                $status ===
                StatusKelayakanDonor::Layak->value
            ) {
                return [
                    'status' =>
                        StatusKelayakanDonor::Layak
                            ->label(),

                    'tone' =>
                        'success',

                    'judul' =>
                        'Layak untuk donor',

                    'deskripsi' =>
                        $tanggal
                            ? 'Berdasarkan pemeriksaan kesehatan pada '
                                . $tanggal->translatedFormat(
                                    'd F Y'
                                )
                                . '.'
                            : 'Berdasarkan hasil pemeriksaan kesehatan terakhir.',

                    'alasan' =>
                        '',

                    'tanggal' =>
                        $tanggal
                            ? $tanggal->translatedFormat(
                                'd F Y, H:i'
                            )
                            : '-',
                ];
            }

            if (
                $status ===
                StatusKelayakanDonor::TidakLayak->value
            ) {
                return [
                    'status' =>
                        StatusKelayakanDonor::TidakLayak
                            ->label(),

                    'tone' =>
                        'danger',

                    'judul' =>
                        'Belum dapat donor',

                    'deskripsi' =>
                        filled(
                            $pemeriksaan->alasan_tidak_layak
                        )
                            ? (string) $pemeriksaan
                                ->alasan_tidak_layak
                            : 'Silakan mengikuti arahan petugas kesehatan sebelum mendaftar kembali.',

                    'alasan' =>
                        (string) (
                            $pemeriksaan
                                ->alasan_tidak_layak
                            ?? ''
                        ),

                    'tanggal' =>
                        $tanggal
                            ? $tanggal->translatedFormat(
                                'd F Y, H:i'
                            )
                            : '-',
                ];
            }
        }

        if ($pendaftaran instanceof PendaftaranDonor) {
            $statusPendaftaran =
                $this->nilaiDariEnum(
                    $pendaftaran->status
                );

            if (
                in_array(
                    $statusPendaftaran,
                    [
                        StatusPendaftaranDonor::Menunggu
                            ->value,

                        StatusPendaftaranDonor::Disetujui
                            ->value,

                        StatusPendaftaranDonor::Hadir
                            ->value,
                    ],
                    true
                )
            ) {
                return [
                    'status' =>
                        'Menunggu Pemeriksaan',

                    'tone' =>
                        'warning',

                    'judul' =>
                        'Proses donor sedang berjalan',

                    'deskripsi' =>
                        'Selesaikan tahapan pendaftaran dan pemeriksaan kesehatan pada jadwal yang dipilih.',

                    'alasan' =>
                        '',

                    'tanggal' =>
                        $this->tanggalPendaftaran(
                            $pendaftaran
                        ),
                ];
            }
        }

        return [
            'status' =>
                'Belum Diperiksa',

            'tone' =>
                'neutral',

            'judul' =>
                'Belum ada hasil kelayakan',

            'deskripsi' =>
                'Status kelayakan ditentukan oleh petugas melalui pemeriksaan kesehatan pada hari donor.',

            'alasan' =>
                '',

            'tanggal' =>
                '-',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function tahapanPendaftaran(
        ?PendaftaranDonor $pendaftaran
    ): array {
        if (! $pendaftaran instanceof PendaftaranDonor) {
            return [];
        }

        $status = $this->nilaiDariEnum(
            $pendaftaran->status
        );

        $posisi = match ($status) {
            StatusPendaftaranDonor::Menunggu->value =>
                1,

            StatusPendaftaranDonor::Disetujui->value =>
                2,

            StatusPendaftaranDonor::Hadir->value =>
                3,

            StatusPendaftaranDonor::Layak->value =>
                4,

            StatusPendaftaranDonor::Selesai->value =>
                5,

            StatusPendaftaranDonor::TidakLayak->value =>
                3,

            StatusPendaftaranDonor::Ditolak->value,
            StatusPendaftaranDonor::Dibatalkan->value =>
                1,

            StatusPendaftaranDonor::TidakHadir->value =>
                2,

            default =>
                0,
        };

        $gagal = in_array(
            $status,
            [
                StatusPendaftaranDonor::Ditolak->value,
                StatusPendaftaranDonor::Dibatalkan->value,
                StatusPendaftaranDonor::TidakHadir->value,
                StatusPendaftaranDonor::TidakLayak->value,
            ],
            true
        );

        $labels = [
            'Pendaftaran dikirim',
            'Verifikasi petugas',
            'Kehadiran',
            'Pemeriksaan kesehatan',
            'Donor selesai',
        ];

        $hasil = [];

        foreach ($labels as $index => $label) {
            if ($posisi >= count($labels)) {
                $state = 'done';
            } elseif ($index < $posisi) {
                $state = 'done';
            } elseif ($index === $posisi) {
                $state = $gagal
                    ? 'danger'
                    : 'current';
            } else {
                $state = 'upcoming';
            }

            $hasil[] = [
                'label' => $label,
                'state' => $state,
            ];
        }

        return $hasil;
    }

    /**
     * @return Collection<int, JadwalDonor>
     */
    private function jadwalTerdekat(): Collection
    {
        return $this->queryJadwalAktif()
            ->with([
                'lokasi',
                'pendaftaranAktif',
            ])
            ->limit(3)
            ->get();
    }

    /**
     * @return Collection<int, PendaftaranDonor>
     */
    private function riwayatTerbaru(): Collection
    {
        return PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
            ])
            ->where(
                'pendonor_id',
                Auth::id()
            )
            ->latest('created_at')
            ->latest('id')
            ->limit(4)
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stokRingkas(): array
    {
        $rows = KantongDarah::query()
            ->select([
                'golongan_darah',
                'rhesus',
                DB::raw('COUNT(*) as total'),
            ])
            ->where(
                'status',
                $this->statusKantongTersedia()
            )
            ->where(
                'status_mutu',
                $this->statusMutuLulus()
            )
            ->groupBy([
                'golongan_darah',
                'rhesus',
            ])
            ->get();

        $hasil = [];

        foreach (
            [
                'A',
                'B',
                'AB',
                'O',
            ] as $golongan
        ) {
            $positif = $rows
                ->filter(
                    fn (KantongDarah $row): bool =>
                        $this->normalisasiGolongan(
                            $this->atribut(
                                $row,
                                'golongan_darah'
                            )
                        ) === $golongan
                        && $this->normalisasiRhesus(
                            $this->atribut(
                                $row,
                                'rhesus'
                            )
                        ) === 'positive'
                )
                ->sum('total');

            $negatif = $rows
                ->filter(
                    fn (KantongDarah $row): bool =>
                        $this->normalisasiGolongan(
                            $this->atribut(
                                $row,
                                'golongan_darah'
                            )
                        ) === $golongan
                        && $this->normalisasiRhesus(
                            $this->atribut(
                                $row,
                                'rhesus'
                            )
                        ) === 'negative'
                )
                ->sum('total');

            $total =
                (int) $positif
                + (int) $negatif;

            $hasil[] = [
                'golongan' => $golongan,
                'positif' => (int) $positif,
                'negatif' => (int) $negatif,
                'total' => $total,
                'status' =>
                    $this->labelStatusStok(
                        $total
                    ),
                'class' =>
                    $this->classStatusStok(
                        $total
                    ),
            ];
        }

        return $hasil;
    }

    /**
     * @return Collection<int, LokasiDonor>
     */
    private function lokasiTerdekat(): Collection
    {
        return $this->queryLokasiAktif()
            ->limit(3)
            ->get();
    }

    private function queryJadwalAktif(): Builder
    {
        $query = JadwalDonor::query();

        if ($this->kolomAdaJadwal('status')) {
            $query->whereIn(
                'status',
                [
                    'published',
                    'dipublikasikan',
                    'active',
                    'aktif',
                ]
            );
        }

        if ($this->kolomAdaJadwal('aktif')) {
            $query->where(
                'aktif',
                true
            );
        }

        if ($this->kolomAdaJadwal('mulai_pada')) {
            return $query
                ->where(
                    'mulai_pada',
                    '>=',
                    now()
                )
                ->orderBy('mulai_pada');
        }

        if (
            $this->kolomAdaJadwal(
                'tanggal_mulai'
            )
        ) {
            return $query
                ->where(
                    'tanggal_mulai',
                    '>=',
                    now()
                )
                ->orderBy(
                    'tanggal_mulai'
                );
        }

        return $query->latest();
    }

    private function queryLokasiAktif(): Builder
    {
        $query = LokasiDonor::query();

        if ($this->kolomAdaLokasi('status')) {
            $query->whereIn(
                'status',
                [
                    'active',
                    'aktif',
                    'published',
                    'dipublikasikan',
                ]
            );
        }

        if ($this->kolomAdaLokasi('aktif')) {
            $query->where(
                'aktif',
                true
            );
        }

        if ($this->kolomAdaLokasi('nama')) {
            return $query->orderBy('nama');
        }

        if (
            $this->kolomAdaLokasi(
                'nama_lokasi'
            )
        ) {
            return $query->orderBy(
                'nama_lokasi'
            );
        }

        return $query->latest();
    }

    public function judulJadwal(
        ?JadwalDonor $jadwal
    ): string {
        if ($jadwal === null) {
            return 'Jadwal Donor';
        }

        return (string) (
            $this->atribut(
                $jadwal,
                'judul'
            )
            ?? $this->atribut(
                $jadwal,
                'nama'
            )
            ?? 'Jadwal Donor'
        );
    }

    public function tanggalJadwal(
        ?JadwalDonor $jadwal
    ): string {
        if ($jadwal === null) {
            return '-';
        }

        $tanggal = $this->tanggalCarbon(
            $this->atribut(
                $jadwal,
                'mulai_pada'
            )
            ?? $this->atribut(
                $jadwal,
                'tanggal_mulai'
            )
        );

        return $tanggal
            ? $tanggal->translatedFormat(
                'd F Y'
            )
            : '-';
    }

    public function jamJadwal(
        ?JadwalDonor $jadwal
    ): string {
        if ($jadwal === null) {
            return '-';
        }

        $mulai = $this->tanggalCarbon(
            $this->atribut(
                $jadwal,
                'mulai_pada'
            )
            ?? $this->atribut(
                $jadwal,
                'tanggal_mulai'
            )
        );

        $selesai = $this->tanggalCarbon(
            $this->atribut(
                $jadwal,
                'selesai_pada'
            )
            ?? $this->atribut(
                $jadwal,
                'tanggal_selesai'
            )
        );

        if ($mulai === null) {
            return '-';
        }

        if ($selesai === null) {
            return $mulai->format('H:i')
                . ' WIB';
        }

        return $mulai->format('H:i')
            . '–'
            . $selesai->format('H:i')
            . ' WIB';
    }

    public function namaLokasi(
        mixed $lokasi
    ): string {
        if (! $lokasi instanceof Model) {
            return 'Lokasi belum ditentukan';
        }

        return (string) (
            $this->atribut(
                $lokasi,
                'nama'
            )
            ?? $this->atribut(
                $lokasi,
                'nama_lokasi'
            )
            ?? 'Lokasi Donor'
        );
    }

    public function alamatLokasi(
        mixed $lokasi
    ): string {
        if (! $lokasi instanceof Model) {
            return '-';
        }

        return (string) (
            $this->atribut(
                $lokasi,
                'alamat'
            )
            ?? $this->atribut(
                $lokasi,
                'alamat_lengkap'
            )
            ?? '-'
        );
    }

    public function wilayahLokasi(
        mixed $lokasi
    ): string {
        if (! $lokasi instanceof Model) {
            return '-';
        }

        $wilayah = collect([
            $this->atribut(
                $lokasi,
                'kota'
            )
                ?? $this->atribut(
                    $lokasi,
                    'kabupaten'
                ),

            $this->atribut(
                $lokasi,
                'provinsi'
            ),
        ])
            ->filter()
            ->implode(', ');

        return $wilayah !== ''
            ? $wilayah
            : '-';
    }

    public function mapsUrl(
        mixed $lokasi
    ): string {
        if (! $lokasi instanceof Model) {
            return 'https://www.google.com/maps';
        }

        $urlGoogleMaps = $this->atribut(
            $lokasi,
            'url_google_maps'
        );

        if (
            filled($urlGoogleMaps)
            && filter_var(
                $urlGoogleMaps,
                FILTER_VALIDATE_URL
            ) !== false
        ) {
            return (string) $urlGoogleMaps;
        }

        $latitude = $this->atribut(
            $lokasi,
            'latitude'
        );

        $longitude = $this->atribut(
            $lokasi,
            'longitude'
        );

        if (
            filled($latitude)
            && filled($longitude)
        ) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode(
                    $latitude
                    . ','
                    . $longitude
                );
        }

        $query = collect([
            $this->namaLokasi($lokasi),
            $this->alamatLokasi($lokasi),
            $this->wilayahLokasi($lokasi),
        ])
            ->filter(
                fn (string $value): bool =>
                    $value !== '-'
            )
            ->implode(', ');

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode($query);
    }

    public function sisaKuota(
        ?JadwalDonor $jadwal
    ): int {
        if (! $jadwal instanceof JadwalDonor) {
            return 0;
        }

        return $jadwal->sisaKuota();
    }

    public function nomorPendaftaran(
        PendaftaranDonor $pendaftaran
    ): string {
        return (string) (
            $this->atribut(
                $pendaftaran,
                'nomor_pendaftaran'
            )
            ?? 'REG-'
                . str_pad(
                    (string) $pendaftaran->id,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
        );
    }

    public function tanggalPendaftaran(
        PendaftaranDonor $pendaftaran
    ): string {
        $tanggal = $this->tanggalCarbon(
            $pendaftaran->created_at
        );

        return $tanggal
            ? $tanggal->translatedFormat(
                'd F Y, H:i'
            )
            : '-';
    }

    public function labelStatusPendaftaran(
        mixed $status
    ): string {
        if (
            is_object($status)
            && method_exists(
                $status,
                'label'
            )
        ) {
            return (string) $status->label();
        }

        $value = $this->nilaiDariEnum(
            $status
        );

        return match ($value) {
            StatusPendaftaranDonor::Menunggu
                ->value =>
                StatusPendaftaranDonor::Menunggu
                    ->label(),

            StatusPendaftaranDonor::Disetujui
                ->value =>
                StatusPendaftaranDonor::Disetujui
                    ->label(),

            StatusPendaftaranDonor::Ditolak
                ->value =>
                StatusPendaftaranDonor::Ditolak
                    ->label(),

            StatusPendaftaranDonor::Hadir
                ->value =>
                StatusPendaftaranDonor::Hadir
                    ->label(),

            StatusPendaftaranDonor::Layak
                ->value =>
                StatusPendaftaranDonor::Layak
                    ->label(),

            StatusPendaftaranDonor::TidakLayak
                ->value =>
                StatusPendaftaranDonor::TidakLayak
                    ->label(),

            StatusPendaftaranDonor::Selesai
                ->value =>
                StatusPendaftaranDonor::Selesai
                    ->label(),

            StatusPendaftaranDonor::Dibatalkan
                ->value =>
                StatusPendaftaranDonor::Dibatalkan
                    ->label(),

            StatusPendaftaranDonor::TidakHadir
                ->value =>
                StatusPendaftaranDonor::TidakHadir
                    ->label(),

            default =>
                $value !== ''
                    ? Str::headline($value)
                    : '-',
        };
    }

    public function statusBadgeClass(
        mixed $status
    ): string {
        return match (
            $this->nilaiDariEnum(
                $status
            )
        ) {
            StatusPendaftaranDonor::Selesai
                ->value,

            StatusPendaftaranDonor::Layak
                ->value,

            StatusPendaftaranDonor::Hadir
                ->value,

            StatusPendaftaranDonor::Disetujui
                ->value =>
                'is-success',

            StatusPendaftaranDonor::Ditolak
                ->value,

            StatusPendaftaranDonor::Dibatalkan
                ->value,

            StatusPendaftaranDonor::TidakLayak
                ->value,

            StatusPendaftaranDonor::TidakHadir
                ->value =>
                'is-danger',

            default =>
                'is-warning',
        };
    }

    public function statusTone(
        mixed $status
    ): string {
        return match (
            $this->nilaiDariEnum(
                $status
            )
        ) {
            StatusPendaftaranDonor::Selesai
                ->value,

            StatusPendaftaranDonor::Layak
                ->value,

            StatusPendaftaranDonor::Hadir
                ->value,

            StatusPendaftaranDonor::Disetujui
                ->value =>
                'success',

            StatusPendaftaranDonor::Ditolak
                ->value,

            StatusPendaftaranDonor::Dibatalkan
                ->value,

            StatusPendaftaranDonor::TidakLayak
                ->value,

            StatusPendaftaranDonor::TidakHadir
                ->value =>
                'danger',

            default =>
                'warning',
        };
    }

    private function persentaseProfil(
        User $user,
        ?ProfilPendonor $profil
    ): int {
        $items = [
            filled(
                $this->atribut(
                    $user,
                    'name'
                )
            ),

            filled(
                $this->atribut(
                    $user,
                    'email'
                )
            ),

            filled(
                $this->atribut(
                    $user,
                    'nomor_telepon'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'tanggal_lahir'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'jenis_kelamin'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'golongan_darah'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'rhesus'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'alamat'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'provinsi'
                )
            ),

            filled(
                $this->atribut(
                    $profil,
                    'kota'
                )
            ),
        ];

        $total = count($items);

        $terisi = collect($items)
            ->filter()
            ->count();

        return $total > 0
            ? (int) round(
                ($terisi / $total) * 100
            )
            : 0;
    }

    private function golonganRhesusProfil(
        ?ProfilPendonor $profil
    ): string {
        if ($profil === null) {
            return '-';
        }

        $golongan =
            $this->normalisasiGolongan(
                $this->atribut(
                    $profil,
                    'golongan_darah'
                )
            );

        $rhesus = match (
            $this->normalisasiRhesus(
                $this->atribut(
                    $profil,
                    'rhesus'
                )
            )
        ) {
            'positive' =>
                '+',

            'negative' =>
                '-',

            default =>
                '',
        };

        if (blank($golongan)) {
            return '-';
        }

        return $golongan . $rhesus;
    }

    private function namaDepan(
        string $nama
    ): string {
        $nama = trim($nama);

        if ($nama === '') {
            return 'Pendonor';
        }

        return Str::before(
            $nama,
            ' '
        );
    }

    private function inisialNama(
        string $nama
    ): string {
        $parts = preg_split(
            '/\s+/',
            trim($nama)
        );

        if (
            ! is_array($parts)
            || $parts === []
        ) {
            return 'P';
        }

        return collect($parts)
            ->filter()
            ->take(2)
            ->map(
                fn (string $part): string =>
                    mb_strtoupper(
                        mb_substr(
                            $part,
                            0,
                            1
                        )
                    )
            )
            ->implode('');
    }

    private function labelStatusStok(
        int $total
    ): string {
        if ($total <= 0) {
            return 'Kosong';
        }

        if ($total <= 2) {
            return 'Rendah';
        }

        return 'Aman';
    }

    private function classStatusStok(
        int $total
    ): string {
        if ($total <= 0) {
            return 'is-danger';
        }

        if ($total <= 2) {
            return 'is-warning';
        }

        return 'is-success';
    }

    private function statusKantongTersedia(): string
    {
        return StatusKantongDarah::Tersedia
            ->value;
    }

    private function statusMutuLulus(): string
    {
        return StatusMutuKantongDarah::Lulus
            ->value;
    }

    private function normalisasiGolongan(
        mixed $value
    ): string {
        $value = strtoupper(
            trim(
                $this->nilaiDariEnum(
                    $value
                )
            )
        );

        return match ($value) {
            'A' => 'A',
            'B' => 'B',
            'AB' => 'AB',
            'O' => 'O',
            default => $value,
        };
    }

    private function normalisasiRhesus(
        mixed $value
    ): string {
        $value = strtolower(
            trim(
                $this->nilaiDariEnum(
                    $value
                )
            )
        );

        return match ($value) {
            '+',
            'plus',
            'positif',
            'positive',
            'rh+',
            'rhesus_positive' =>
                'positive',

            '-',
            'minus',
            'negatif',
            'negative',
            'rh-',
            'rhesus_negative' =>
                'negative',

            default =>
                $value,
        };
    }

    private function nilaiDariEnum(
        mixed $value
    ): string {
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

    private function atribut(
        ?Model $model,
        string $atribut
    ): mixed {
        if (! $model instanceof Model) {
            return null;
        }

        return $model->getAttribute(
            $atribut
        );
    }

    private function tanggalCarbon(
        mixed $value
    ): ?CarbonInterface {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance(
                $value
            );
        }

        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse(
                (string) $value
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function kolomAdaJadwal(
        string $kolom
    ): bool {
        return Schema::hasColumn(
            (new JadwalDonor())->getTable(),
            $kolom
        );
    }

    private function kolomAdaLokasi(
        string $kolom
    ): bool {
        return Schema::hasColumn(
            (new LokasiDonor())->getTable(),
            $kolom
        );
    }
}