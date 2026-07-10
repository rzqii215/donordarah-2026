<?php

namespace App\Livewire\Donor;

use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use App\Services\LayananPendaftaranDonor;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.donor')]
class Jadwal extends Component
{
    public string $pencarian = '';

    public ?int $jadwalTerpilihId = null;

    public function pilihJadwal(int $jadwalId): void
    {
        $this->jadwalTerpilihId = $jadwalId;
    }

    public function tutupDetailJadwal(): void
    {
        $this->jadwalTerpilihId = null;
    }

    public function daftarJadwal(int $jadwalId): mixed
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect('/login');
        }

        if (! $this->userPunyaProfilPendonor($user)) {
            $this->addError(
                'jadwal',
                'Lengkapi profil pendonor terlebih dahulu sebelum mendaftar donor.'
            );

            return null;
        }

        $jadwal = JadwalDonor::query()
            ->with('lokasi')
            ->findOrFail($jadwalId);

        if (! $this->jadwalDapatDidaftar($jadwal)) {
            $this->addError(
                'jadwal',
                'Jadwal donor ini belum dapat didaftarkan atau pendaftaran sudah ditutup.'
            );

            return null;
        }

        try {
            app(LayananPendaftaranDonor::class)->daftar(
                jadwalDonorId: $jadwal->id,
                pendonorId: (int) $user->id,
                data: [
                    'jawaban_skrining' => $this->jawabanSkriningDefault(),
                    'catatan' => null,
                ],
            );

            session()->flash(
                'success',
                'Pendaftaran donor berhasil dibuat. Silakan pantau status pendaftaran pada menu Riwayat.'
            );

            return redirect()->route('donor.riwayat');
        } catch (ValidationException $exception) {
            $pesan = collect($exception->errors())
                ->flatten()
                ->first();

            $this->addError(
                'jadwal',
                $pesan ?: 'Pendaftaran donor belum dapat diproses.'
            );

            return null;
        }
    }

    public function render(): View
    {
        return view('livewire.donor.jadwal', [
            'jadwalDonors' => $this->ambilJadwalDonor(),
            'jadwalTerpilih' => $this->ambilJadwalTerpilih(),
            'pendaftaranJadwals' => $this->ambilPendaftaranJadwals(),
        ]);
    }

    /**
     * @return Collection<int, JadwalDonor>
     */
    private function ambilJadwalDonor(): Collection
    {
        $query = JadwalDonor::query()
            ->with('lokasi');

        $this->filterStatusDipublikasikanJikaAda($query);

        if (filled($this->pencarian)) {
            $this->filterPencarian($query);
        }

        $this->urutkanJadwal($query);

        return $query
            ->limit(50)
            ->get();
    }

    private function ambilJadwalTerpilih(): ?JadwalDonor
    {
        if ($this->jadwalTerpilihId === null) {
            return null;
        }

        return JadwalDonor::query()
            ->with('lokasi')
            ->find($this->jadwalTerpilihId);
    }

    /**
     * @return Collection<int|string, PendaftaranDonor>
     */
    private function ambilPendaftaranJadwals(): Collection
    {
        $userId = Auth::id();

        if ($userId === null) {
            return collect();
        }

        return PendaftaranDonor::query()
            ->where('pendonor_id', $userId)
            ->latest()
            ->get()
            ->keyBy('jadwal_donor_id');
    }

    private function filterStatusDipublikasikanJikaAda(
        Builder $query
    ): void {
        if (! $this->kolomAdaJadwal('status')) {
            return;
        }

        $query->whereIn('status', [
            'published',
            'dipublikasikan',
            'active',
            'aktif',
        ]);
    }

    private function filterPencarian(Builder $query): void
    {
        $keyword = '%' . Str::lower(trim($this->pencarian)) . '%';

        $jadwalColumns = collect([
            'kode_jadwal',
            'judul',
            'deskripsi',
            'catatan',
        ])
            ->filter(
                fn (string $column): bool => $this->kolomAdaJadwal($column)
            )
            ->values();

        $lokasiColumns = collect([
            'nama',
            'nama_lokasi',
            'alamat',
            'alamat_lengkap',
            'kota',
            'kabupaten',
            'provinsi',
            'nomor_telepon',
            'catatan_lokasi',
        ])
            ->filter(
                fn (string $column): bool => $this->kolomAdaLokasi($column)
            )
            ->values();

        $query->where(function (Builder $query) use (
            $jadwalColumns,
            $lokasiColumns,
            $keyword
        ): void {
            foreach ($jadwalColumns as $column) {
                $query->orWhereRaw(
                    'LOWER(' . $column . ') LIKE ?',
                    [$keyword]
                );
            }

            if ($lokasiColumns->isNotEmpty()) {
                $query->orWhereHas(
                    'lokasi',
                    function (Builder $query) use (
                        $lokasiColumns,
                        $keyword
                    ): void {
                        foreach ($lokasiColumns as $column) {
                            $query->orWhereRaw(
                                'LOWER(' . $column . ') LIKE ?',
                                [$keyword]
                            );
                        }
                    }
                );
            }
        });
    }

    private function urutkanJadwal(Builder $query): void
    {
        if ($this->kolomAdaJadwal('mulai_pada')) {
            $query->orderBy('mulai_pada');

            return;
        }

        if ($this->kolomAdaJadwal('tanggal_mulai')) {
            $query->orderBy('tanggal_mulai');

            return;
        }

        $query->latest();
    }

    public function jadwalDapatDidaftar(JadwalDonor $jadwal): bool
    {
        if (
            PendaftaranDonor::query()
                ->where('pendonor_id', Auth::id())
                ->where('jadwal_donor_id', $jadwal->id)
                ->exists()
        ) {
            return false;
        }

        if (! $this->statusJadwalAktif($jadwal)) {
            return false;
        }

        $pendaftaranDibukaPada = $this->tanggalCarbon(
            $jadwal->pendaftaran_dibuka_pada ?? null
        );

        $pendaftaranDitutupPada = $this->tanggalCarbon(
            $jadwal->pendaftaran_ditutup_pada ?? null
        );

        if (
            $pendaftaranDibukaPada !== null
            && now()->lt($pendaftaranDibukaPada)
        ) {
            return false;
        }

        if (
            $pendaftaranDitutupPada !== null
            && now()->gt($pendaftaranDitutupPada)
        ) {
            return false;
        }

        return true;
    }

    public function labelStatusPendaftaran(
        mixed $status
    ): string {
        if (
            is_object($status)
            && method_exists($status, 'label')
        ) {
            return $status->label();
        }

        $value = $this->nilaiEnum($status);

        return match ($value) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'attended' => 'Hadir',
            'eligible' => 'Layak Donor',
            'ineligible' => 'Tidak Layak',
            'completed' => 'Donor Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => $value !== '' ? $value : '-',
        };
    }

    public function statusBadgeClass(mixed $status): string
    {
        $value = $this->nilaiEnum($status);

        return match ($value) {
            'approved',
            'attended',
            'eligible',
            'completed' => 'is-success',

            'rejected',
            'cancelled',
            'ineligible' => 'is-danger',

            default => 'is-warning',
        };
    }

    public function judulJadwal(JadwalDonor $jadwal): string
    {
        return (string) (
            $jadwal->judul
            ?? $jadwal->nama
            ?? 'Jadwal Donor'
        );
    }

    public function deskripsiJadwal(JadwalDonor $jadwal): string
    {
        return (string) (
            $jadwal->deskripsi
            ?? $jadwal->catatan
            ?? 'Kegiatan donor darah yang dapat diikuti oleh pendonor terdaftar.'
        );
    }

    public function tanggalJadwal(JadwalDonor $jadwal): string
    {
        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
                ?? $jadwal->tanggal_mulai
                ?? null
        );

        return $mulai
            ? $mulai->translatedFormat('d F Y')
            : 'Tanggal belum ditentukan';
    }

    public function jamJadwal(JadwalDonor $jadwal): string
    {
        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
                ?? $jadwal->tanggal_mulai
                ?? null
        );

        $selesai = $this->tanggalCarbon(
            $jadwal->selesai_pada
                ?? $jadwal->tanggal_selesai
                ?? null
        );

        if ($mulai === null) {
            return '-';
        }

        if ($selesai === null) {
            return $mulai->format('H:i');
        }

        return $mulai->format('H:i') . ' — ' . $selesai->format('H:i');
    }

    public function periodePendaftaran(JadwalDonor $jadwal): string
    {
        $mulai = $this->tanggalCarbon(
            $jadwal->pendaftaran_dibuka_pada ?? null
        );

        $selesai = $this->tanggalCarbon(
            $jadwal->pendaftaran_ditutup_pada ?? null
        );

        if ($mulai === null && $selesai === null) {
            return 'Mengikuti kebijakan petugas';
        }

        if ($mulai !== null && $selesai !== null) {
            return $mulai->translatedFormat('d M Y H:i')
                . ' — '
                . $selesai->translatedFormat('d M Y H:i');
        }

        if ($mulai !== null) {
            return 'Dibuka mulai '
                . $mulai->translatedFormat('d M Y H:i');
        }

        return 'Ditutup pada '
            . $selesai?->translatedFormat('d M Y H:i');
    }

    public function kuotaJadwal(JadwalDonor $jadwal): string
    {
        $kuota = $jadwal->kuota
            ?? $jadwal->kuota_peserta
            ?? $jadwal->kapasitas
            ?? null;

        if (blank($kuota)) {
            return 'Kuota menyesuaikan lokasi';
        }

        return $kuota . ' peserta';
    }

    public function namaLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'Lokasi belum ditentukan';
        }

        return (string) (
            $lokasi->nama
            ?? $lokasi->nama_lokasi
            ?? 'Lokasi Donor'
        );
    }

    public function alamatLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        return (string) (
            $lokasi->alamat
            ?? $lokasi->alamat_lengkap
            ?? '-'
        );
    }

    public function wilayahLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        $wilayah = collect([
            $lokasi->kota
                ?? $lokasi->kabupaten
                ?? null,

            $lokasi->provinsi
                ?? null,
        ])
            ->filter()
            ->implode(', ');

        return $wilayah !== '' ? $wilayah : '-';
    }

    public function kontakLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        return (string) (
            $lokasi->nomor_telepon
            ?? $lokasi->telepon
            ?? $lokasi->kontak
            ?? '-'
        );
    }

    public function catatanLokasi(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return '-';
        }

        return (string) (
            $lokasi->catatan_lokasi
            ?? '-'
        );
    }

    public function mapsUrl(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'https://www.google.com/maps';
        }

        if (
            $this->kolomAdaLokasi('url_google_maps')
            && filled($lokasi->url_google_maps)
        ) {
            return (string) $lokasi->url_google_maps;
        }

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode($this->queryMaps($lokasi));
    }

    public function embedMapsUrl(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'https://maps.google.com/maps?q=Indonesia&z=5&output=embed';
        }

        return 'https://maps.google.com/maps?q='
            . rawurlencode($this->queryMaps($lokasi))
            . '&z=15&output=embed';
    }

    private function queryMaps(LokasiDonor $lokasi): string
    {
        $latitude = $lokasi->latitude ?? null;
        $longitude = $lokasi->longitude ?? null;

        if (filled($latitude) && filled($longitude)) {
            return $latitude . ',' . $longitude;
        }

        return collect([
            $this->namaLokasi($lokasi),
            $this->alamatLokasi($lokasi),
            $this->wilayahLokasi($lokasi),
        ])
            ->filter(fn (string $value): bool => $value !== '-')
            ->implode(', ');
    }

    private function statusJadwalAktif(JadwalDonor $jadwal): bool
    {
        if (! $this->kolomAdaJadwal('status')) {
            return true;
        }

        $status = Str::lower(
            $this->nilaiEnum($jadwal->status)
        );

        return in_array(
            $status,
            [
                'published',
                'dipublikasikan',
                'active',
                'aktif',
            ],
            true
        );
    }

    private function userPunyaProfilPendonor(mixed $user): bool
    {
        if (! method_exists($user, 'profilPendonor')) {
            return false;
        }

        return $user->profilPendonor()->exists();
    }

    /**
     * @return array<string, bool>
     */
    private function jawabanSkriningDefault(): array
    {
        return [
            'sehat_hari_ini' => true,
            'sedang_minum_obat' => false,
            'operasi_terakhir' => false,
            'cukup_tidur' => true,
            'sudah_makan' => true,
        ];
    }

    private function tanggalCarbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function nilaiEnum(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    private function tabelJadwal(): string
    {
        return (new JadwalDonor())->getTable();
    }

    private function tabelLokasi(): string
    {
        return (new LokasiDonor())->getTable();
    }

    private function kolomAdaJadwal(string $column): bool
    {
        return Schema::hasColumn(
            $this->tabelJadwal(),
            $column
        );
    }

    private function kolomAdaLokasi(string $column): bool
    {
        return Schema::hasColumn(
            $this->tabelLokasi(),
            $column
        );
    }
}