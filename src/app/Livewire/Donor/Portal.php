<?php

namespace App\Livewire\Donor;

use App\Enums\StatusKantongDarah;
use App\Enums\StatusMutuKantongDarah;
use App\Models\JadwalDonor;
use App\Models\KantongDarah;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
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
use Livewire\Component;

#[Layout('components.layouts.donor')]
class Portal extends Component
{
    public function render(): View
    {
        return view('livewire.donor.portal', [
            'profilPendonor' => $this->profilPendonor(),
            'ringkasan' => $this->ringkasanBeranda(),
            'jadwalTerdekat' => $this->jadwalTerdekat(),
            'stokRingkas' => $this->stokRingkas(),
            'lokasiTerdekat' => $this->lokasiTerdekat(),
            'riwayatTerbaru' => $this->riwayatTerbaru(),
        ]);
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

    /**
     * @return array<string, mixed>
     */
    private function ringkasanBeranda(): array
    {
        $profil = $this->profilPendonor();
        $user = $this->penggunaSaatIni();

        return [
            'nama_user' => (string) (
                $this->atribut($user, 'name')
                ?? 'Pendonor'
            ),

            'kode_pendonor' => (string) (
                $this->atribut($profil, 'kode_pendonor')
                ?? '-'
            ),

            'golongan_rhesus' => $this->golonganRhesusProfil($profil),

            'profil_lengkap' => $this->persentaseProfil($profil),

            'total_pendaftaran' => PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->count(),

            'pendaftaran_proses' => PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->whereIn('status', [
                    'pending',
                    'approved',
                    'attended',
                    'eligible',
                ])
                ->count(),

            'donor_selesai' => PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->where('status', 'completed')
                ->count(),

            'stok_tersedia' => KantongDarah::query()
                ->where('status', $this->statusKantongTersedia())
                ->where('status_mutu', $this->statusMutuLulus())
                ->count(),

            'jadwal_aktif' => $this->queryJadwalAktif()
                ->count(),

            'lokasi_aktif' => $this->queryLokasiAktif()
                ->count(),
        ];
    }

    /**
     * @return Collection<int, JadwalDonor>
     */
    private function jadwalTerdekat(): Collection
    {
        return $this->queryJadwalAktif()
            ->with('lokasi')
            ->limit(3)
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
            ->where('status', $this->statusKantongTersedia())
            ->where('status_mutu', $this->statusMutuLulus())
            ->groupBy([
                'golongan_darah',
                'rhesus',
            ])
            ->get();

        $hasil = [];

        foreach ([
            'A',
            'B',
            'AB',
            'O',
        ] as $golongan) {
            $positif = $rows
                ->filter(
                    fn (KantongDarah $row): bool =>
                        $this->normalisasiGolongan(
                            $this->atribut($row, 'golongan_darah')
                        ) === $golongan
                        && $this->normalisasiRhesus(
                            $this->atribut($row, 'rhesus')
                        ) === 'positive'
                )
                ->sum('total');

            $negatif = $rows
                ->filter(
                    fn (KantongDarah $row): bool =>
                        $this->normalisasiGolongan(
                            $this->atribut($row, 'golongan_darah')
                        ) === $golongan
                        && $this->normalisasiRhesus(
                            $this->atribut($row, 'rhesus')
                        ) === 'negative'
                )
                ->sum('total');

            $total = (int) $positif + (int) $negatif;

            $hasil[] = [
                'golongan' => $golongan,
                'positif' => (int) $positif,
                'negatif' => (int) $negatif,
                'total' => $total,
                'status' => $this->labelStatusStok($total),
                'class' => $this->classStatusStok($total),
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

    /**
     * @return Collection<int, PendaftaranDonor>
     */
    private function riwayatTerbaru(): Collection
    {
        return PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
            ])
            ->where('pendonor_id', Auth::id())
            ->latest()
            ->limit(3)
            ->get();
    }

    private function queryJadwalAktif(): Builder
    {
        $query = JadwalDonor::query();

        if ($this->kolomAdaJadwal('status')) {
            $query->whereIn('status', [
                'published',
                'dipublikasikan',
                'active',
                'aktif',
            ]);
        }

        if ($this->kolomAdaJadwal('aktif')) {
            $query->where('aktif', true);
        }

        if ($this->kolomAdaJadwal('mulai_pada')) {
            return $query
                ->where('mulai_pada', '>=', now()->startOfDay())
                ->orderBy('mulai_pada');
        }

        if ($this->kolomAdaJadwal('tanggal_mulai')) {
            return $query
                ->where('tanggal_mulai', '>=', now()->startOfDay())
                ->orderBy('tanggal_mulai');
        }

        return $query->latest();
    }

    private function queryLokasiAktif(): Builder
    {
        $query = LokasiDonor::query();

        if ($this->kolomAdaLokasi('status')) {
            $query->whereIn('status', [
                'active',
                'aktif',
                'published',
                'dipublikasikan',
            ]);
        }

        if ($this->kolomAdaLokasi('aktif')) {
            $query->where('aktif', true);
        }

        if ($this->kolomAdaLokasi('nama')) {
            return $query->orderBy('nama');
        }

        if ($this->kolomAdaLokasi('nama_lokasi')) {
            return $query->orderBy('nama_lokasi');
        }

        return $query->latest();
    }

    public function judulJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return 'Jadwal Donor';
        }

        return (string) (
            $this->atribut($jadwal, 'judul')
            ?? $this->atribut($jadwal, 'nama')
            ?? 'Jadwal Donor'
        );
    }

    public function tanggalJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return '-';
        }

        $tanggal = $this->tanggalCarbon(
            $this->atribut($jadwal, 'mulai_pada')
                ?? $this->atribut($jadwal, 'tanggal_mulai')
        );

        return $tanggal
            ? $tanggal->translatedFormat('d F Y')
            : '-';
    }

    public function jamJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return '-';
        }

        $mulai = $this->tanggalCarbon(
            $this->atribut($jadwal, 'mulai_pada')
                ?? $this->atribut($jadwal, 'tanggal_mulai')
        );

        $selesai = $this->tanggalCarbon(
            $this->atribut($jadwal, 'selesai_pada')
                ?? $this->atribut($jadwal, 'tanggal_selesai')
        );

        if ($mulai === null) {
            return '-';
        }

        if ($selesai === null) {
            return $mulai->format('H:i');
        }

        return $mulai->format('H:i') . ' — ' . $selesai->format('H:i');
    }

    public function namaLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'Lokasi belum ditentukan';
        }

        return (string) (
            $this->atribut($lokasi, 'nama')
            ?? $this->atribut($lokasi, 'nama_lokasi')
            ?? 'Lokasi Donor'
        );
    }

    public function alamatLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        return (string) (
            $this->atribut($lokasi, 'alamat')
            ?? $this->atribut($lokasi, 'alamat_lengkap')
            ?? '-'
        );
    }

    public function wilayahLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        $wilayah = collect([
            $this->atribut($lokasi, 'kota')
                ?? $this->atribut($lokasi, 'kabupaten'),

            $this->atribut($lokasi, 'provinsi'),
        ])
            ->filter()
            ->implode(', ');

        return $wilayah !== '' ? $wilayah : '-';
    }

    public function mapsUrl(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'https://www.google.com/maps';
        }

        $urlGoogleMaps = $this->atribut($lokasi, 'url_google_maps');

        if (
            $this->kolomAdaLokasi('url_google_maps')
            && filled($urlGoogleMaps)
        ) {
            return (string) $urlGoogleMaps;
        }

        $latitude = $this->atribut($lokasi, 'latitude');
        $longitude = $this->atribut($lokasi, 'longitude');

        if (
            filled($latitude)
            && filled($longitude)
        ) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode(
                    $latitude . ',' . $longitude
                );
        }

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode(
                collect([
                    $this->namaLokasi($lokasi),
                    $this->alamatLokasi($lokasi),
                    $this->wilayahLokasi($lokasi),
                ])
                    ->filter(fn (string $value): bool => $value !== '-')
                    ->implode(', ')
            );
    }

    public function nomorPendaftaran(PendaftaranDonor $pendaftaran): string
    {
        return (string) (
            $this->atribut($pendaftaran, 'nomor_pendaftaran')
            ?? 'REG-' . str_pad(
                (string) $pendaftaran->id,
                5,
                '0',
                STR_PAD_LEFT
            )
        );
    }

    public function labelStatusPendaftaran(mixed $status): string
    {
        if (
            is_object($status)
            && method_exists($status, 'label')
        ) {
            return $status->label();
        }

        $value = $this->nilaiDariEnum($status);

        return match ($value) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'attended' => 'Hadir',
            'eligible' => 'Layak Donor',
            'ineligible' => 'Tidak Layak',
            'completed' => 'Donor Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'absent',
            'not_attended' => 'Tidak Hadir',
            default => $value !== '' ? Str::headline($value) : '-',
        };
    }

    public function statusBadgeClass(mixed $status): string
    {
        return match ($this->nilaiDariEnum($status)) {
            'completed',
            'eligible',
            'attended',
            'approved' => 'is-success',

            'rejected',
            'cancelled',
            'ineligible',
            'absent',
            'not_attended' => 'is-danger',

            default => 'is-warning',
        };
    }

    private function persentaseProfil(?ProfilPendonor $profil): int
    {
        $user = $this->penggunaSaatIni();

        $items = [
            filled($this->atribut($user, 'name')),
            filled($this->atribut($user, 'email')),
            filled($this->atribut($user, 'nomor_telepon')),
            filled($this->atribut($profil, 'tanggal_lahir')),
            filled($this->atribut($profil, 'jenis_kelamin')),
            filled($this->atribut($profil, 'golongan_darah')),
            filled($this->atribut($profil, 'rhesus')),
            filled($this->atribut($profil, 'alamat')),
            filled($this->atribut($profil, 'provinsi')),
            filled($this->atribut($profil, 'kota')),
        ];

        $total = count($items);

        $terisi = collect($items)
            ->filter()
            ->count();

        return $total > 0
            ? (int) round(($terisi / $total) * 100)
            : 0;
    }

    private function golonganRhesusProfil(?ProfilPendonor $profil): string
    {
        if ($profil === null) {
            return '-';
        }

        $golongan = $this->normalisasiGolongan(
            $this->atribut($profil, 'golongan_darah')
        );

        $rhesus = match (
            $this->normalisasiRhesus(
                $this->atribut($profil, 'rhesus')
            )
        ) {
            'positive' => '+',
            'negative' => '-',
            default => '',
        };

        if (blank($golongan)) {
            return '-';
        }

        return $golongan . $rhesus;
    }

    private function labelStatusStok(int $total): string
    {
        if ($total <= 0) {
            return 'Kosong';
        }

        if ($total <= 2) {
            return 'Rendah';
        }

        return 'Aman';
    }

    private function classStatusStok(int $total): string
    {
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
        return StatusKantongDarah::Tersedia->value;
    }

    private function statusMutuLulus(): string
    {
        return StatusMutuKantongDarah::Lulus->value;
    }

    private function normalisasiGolongan(mixed $value): string
    {
        $value = strtoupper(
            trim($this->nilaiDariEnum($value))
        );

        return match ($value) {
            'A' => 'A',
            'B' => 'B',
            'AB' => 'AB',
            'O' => 'O',
            default => $value,
        };
    }

    private function normalisasiRhesus(mixed $value): string
    {
        $value = strtolower(
            trim($this->nilaiDariEnum($value))
        );

        return match ($value) {
            '+',
            'plus',
            'positif',
            'positive',
            'rh+',
            'rhesus_positive' => 'positive',

            '-',
            'minus',
            'negatif',
            'negative',
            'rh-',
            'rhesus_negative' => 'negative',

            default => $value,
        };
    }

    private function tanggalCarbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function nilaiDariEnum(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (blank($value)) {
            return '';
        }

        if (
            is_object($value)
            && ! method_exists($value, '__toString')
        ) {
            return '';
        }

        return (string) $value;
    }

    private function atribut(
        mixed $model,
        string $key,
        mixed $default = null
    ): mixed {
        if ($model === null) {
            return $default;
        }

        if ($model instanceof Model) {
            $value = $model->getAttribute($key);

            return $value ?? $default;
        }

        if (
            is_array($model)
            && array_key_exists($key, $model)
        ) {
            return $model[$key] ?? $default;
        }

        if (
            is_object($model)
            && isset($model->{$key})
        ) {
            return $model->{$key};
        }

        return $default;
    }

    private function kolomAdaJadwal(string $column): bool
    {
        return Schema::hasColumn(
            (new JadwalDonor())->getTable(),
            $column
        );
    }

    private function kolomAdaLokasi(string $column): bool
    {
        return Schema::hasColumn(
            (new LokasiDonor())->getTable(),
            $column
        );
    }
}