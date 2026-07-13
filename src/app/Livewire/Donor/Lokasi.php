<?php

namespace App\Livewire\Donor;

use App\Enums\StatusJadwalDonor;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.donor')]
class Lokasi extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $pencarian = '';

    #[Url(as: 'kota', except: '')]
    public string $kota = '';

    #[Url(as: 'dengan-jadwal', except: false)]
    public bool $hanyaDenganJadwal = false;

    #[Url(as: 'urut', except: 'nama')]
    public string $urutan = 'nama';

    public ?int $lokasiTerpilihId = null;

    public int $perHalaman = 6;

    /**
     * @var array<int, string>
     */
    private const URUTAN_VALID = [
        'nama',
        'jadwal',
        'jadwal_terdekat',
        'kota',
        'nama_asc',
        'nama_desc',
        'nama_az',
        'nama_za',
        'kota_asc',
        'kota_desc',
    ];

    public function mount(): void
    {
        if (
            ! in_array(
                $this->urutan,
                self::URUTAN_VALID,
                true
            )
        ) {
            $this->urutan = 'nama';
        }
    }

    public function updatedPencarian(): void
    {
        $this->resetHalamanDanDetail();
    }

    public function updatedKota(): void
    {
        $this->resetHalamanDanDetail();
    }

    public function updatedHanyaDenganJadwal(): void
    {
        $this->resetHalamanDanDetail();
    }

    public function updatedUrutan(
        string $value
    ): void {
        if (
            ! in_array(
                $value,
                self::URUTAN_VALID,
                true
            )
        ) {
            $this->urutan = 'nama';
        }

        $this->resetHalamanDanDetail();
    }

    public function resetFilter(): void
    {
        $this->pencarian = '';
        $this->kota = '';
        $this->hanyaDenganJadwal = false;
        $this->urutan = 'nama';
        $this->lokasiTerpilihId = null;

        $this->resetPage();
    }

    public function pilihLokasi(
        int $lokasiId
    ): void {
        $lokasiAda = LokasiDonor::query()
            ->whereKey($lokasiId)
            ->where('aktif', true)
            ->exists();

        if (! $lokasiAda) {
            $this->lokasiTerpilihId = null;

            return;
        }

        $this->lokasiTerpilihId =
            $lokasiId;
    }

    public function tutupDetailLokasi(): void
    {
        $this->lokasiTerpilihId = null;
    }

    public function render(): View
    {
        $lokasiDonors =
            $this->ambilLokasiDonor();

        $kotaTersedia =
            $this->ambilKotaTersedia();

        return view(
            'livewire.donor.lokasi',
            [
                'lokasiDonors' => $lokasiDonors,

                'kotaTersedia' => $kotaTersedia,

                'daftarKota' => $kotaTersedia,

                'lokasiTerpilih' => $this->ambilLokasiTerpilih(),

                'jumlahLokasi' => $lokasiDonors->total(),

                'ringkasan' => $this->ambilRingkasan(),

                'pencarian' => $this->pencarian,

                'kota' => $this->kota,

                'hanyaDenganJadwal' => $this->hanyaDenganJadwal,

                'urutan' => $this->urutan,
            ]
        );
    }

    private function ambilLokasiDonor(): LengthAwarePaginator
    {
        $query = LokasiDonor::query()
            ->where('aktif', true)
            ->withMin([
                'jadwalDonors as jadwal_terdekat_pada' => function (
                    Builder|HasMany $query
                ): void {
                    $this->queryJadwalAktif(
                        $query
                    );
                },
            ], 'mulai_pada')
            ->withCount([
                'jadwalDonors as jumlah_jadwal_aktif' => function (
                    Builder|HasMany $query
                ): void {
                    $this->queryJadwalAktif(
                        $query
                    );
                },
            ])
            ->with([
                'jadwalDonors' => function (
                    Builder|HasMany $query
                ): void {
                    $this->queryJadwalAktif(
                        $query
                    );

                    $query->limit(3);
                },
            ]);

        $this->terapkanPencarian(
            $query
        );

        $this->terapkanKota(
            $query
        );

        $this->terapkanFilterJadwal(
            $query
        );

        $this->terapkanUrutan(
            $query
        );

        return $query
            ->paginate(
                $this->perHalaman
            )
            ->withQueryString();
    }

    /**
     * @return Collection<int, string>
     */
    private function ambilKotaTersedia(): Collection
    {
        return LokasiDonor::query()
            ->where('aktif', true)
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota')
            ->map(
                fn (mixed $kota): string => trim((string) $kota)
            )
            ->filter()
            ->values();
    }

    /**
     * @return array<string, int>
     */
    private function ambilRingkasan(): array
    {
        $queryLokasiAktif =
            LokasiDonor::query()
                ->where('aktif', true);

        $totalLokasi =
            (clone $queryLokasiAktif)
                ->count();

        $lokasiDenganJadwal =
            (clone $queryLokasiAktif)
                ->whereHas(
                    'jadwalDonors',
                    function (
                        Builder $query
                    ): void {
                        $this->queryJadwalAktif(
                            $query
                        );
                    }
                )
                ->count();

        $jumlahKota =
            (clone $queryLokasiAktif)
                ->whereNotNull('kota')
                ->where('kota', '!=', '')
                ->distinct()
                ->count('kota');

        return [
            'total_lokasi' => $totalLokasi,

            'dengan_jadwal' => $lokasiDenganJadwal,

            'jumlah_kota' => $jumlahKota,
        ];
    }

    private function ambilLokasiTerpilih(): ?LokasiDonor
    {
        if (
            $this->lokasiTerpilihId ===
            null
        ) {
            return null;
        }

        return LokasiDonor::query()
            ->where('aktif', true)
            ->withCount([
                'jadwalDonors as jumlah_jadwal_aktif' => function (
                    Builder|HasMany $query
                ): void {
                    $this->queryJadwalAktif(
                        $query
                    );
                },
            ])
            ->with([
                'jadwalDonors' => function (
                    Builder|HasMany $query
                ): void {
                    $this->queryJadwalAktif(
                        $query
                    );

                    $query->limit(10);
                },
            ])
            ->find(
                $this->lokasiTerpilihId
            );
    }

    private function queryJadwalAktif(
        Builder|HasMany $query
    ): void {
        $query
            ->where(
                'status',
                StatusJadwalDonor::Dipublikasikan
                    ->value
            )
            ->where(
                'selesai_pada',
                '>=',
                now()
            )
            ->orderBy(
                'mulai_pada'
            );
    }

    private function terapkanPencarian(
        Builder $query
    ): void {
        $pencarian = trim(
            $this->pencarian
        );

        if ($pencarian === '') {
            return;
        }

        $kataKunci =
            '%' .
            mb_strtolower(
                $pencarian
            ) .
            '%';

        $query->where(
            function (
                Builder $query
            ) use (
                $kataKunci
            ): void {
                $query
                    ->whereRaw(
                        'LOWER(nama) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(alamat) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(kota) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(provinsi) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(kecamatan, \'\')) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(deskripsi, \'\')) LIKE ?',
                        [$kataKunci]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(catatan_lokasi, \'\')) LIKE ?',
                        [$kataKunci]
                    );
            }
        );
    }

    private function terapkanKota(
        Builder $query
    ): void {
        $kota = trim(
            $this->kota
        );

        if ($kota === '') {
            return;
        }

        $query->where(
            'kota',
            $kota
        );
    }

    private function terapkanFilterJadwal(
        Builder $query
    ): void {
        if (! $this->hanyaDenganJadwal) {
            return;
        }

        $query->whereHas(
            'jadwalDonors',
            function (
                Builder $query
            ): void {
                $this->queryJadwalAktif(
                    $query
                );
            }
        );
    }

    private function terapkanUrutan(
        Builder $query
    ): void {
        if (
            in_array(
                $this->urutan,
                [
                    'nama',
                    'nama_asc',
                    'nama_az',
                ],
                true
            )
        ) {
            $query->orderBy(
                'nama'
            );

            return;
        }

        if (
            in_array(
                $this->urutan,
                [
                    'nama_desc',
                    'nama_za',
                ],
                true
            )
        ) {
            $query->orderByDesc(
                'nama'
            );

            return;
        }

        if (
            in_array(
                $this->urutan,
                [
                    'kota',
                    'kota_asc',
                ],
                true
            )
        ) {
            $query
                ->orderBy('kota')
                ->orderBy('nama');

            return;
        }

        if (
            $this->urutan ===
            'kota_desc'
        ) {
            $query
                ->orderByDesc('kota')
                ->orderBy('nama');

            return;
        }

        $query
            ->orderByRaw(
                'jadwal_terdekat_pada IS NULL'
            )
            ->orderBy(
                'jadwal_terdekat_pada'
            )
            ->orderBy(
                'nama'
            );
    }

    private function resetHalamanDanDetail(): void
    {
        $this->lokasiTerpilihId = null;

        $this->resetPage();
    }

    public function jadwalTerdekat(
        LokasiDonor $lokasi
    ): ?JadwalDonor {
        if (
            $lokasi->relationLoaded(
                'jadwalDonors'
            )
        ) {
            $jadwal = $lokasi
                ->jadwalDonors
                ->sortBy(
                    fn (
                        JadwalDonor $jadwal
                    ): int => $jadwal->mulai_pada
                        ?->timestamp
                        ?? PHP_INT_MAX
                )
                ->first();

            return $jadwal instanceof JadwalDonor
                    ? $jadwal
                    : null;
        }

        $query = $lokasi
            ->jadwalDonors();

        $this->queryJadwalAktif(
            $query
        );

        return $query->first();
    }

    public function jumlahJadwalAktif(
        LokasiDonor $lokasi
    ): int {
        $jumlah =
            $lokasi->getAttribute(
                'jumlah_jadwal_aktif'
            );

        if ($jumlah !== null) {
            return (int) $jumlah;
        }

        $query = $lokasi
            ->jadwalDonors();

        $this->queryJadwalAktif(
            $query
        );

        return $query->count();
    }

    /**
     * @return Collection<int, JadwalDonor>
     */
    public function jadwalAktif(
        LokasiDonor $lokasi
    ): Collection {
        if (
            $lokasi->relationLoaded(
                'jadwalDonors'
            )
        ) {
            return $lokasi
                ->jadwalDonors
                ->sortBy(
                    fn (
                        JadwalDonor $jadwal
                    ): int => $jadwal->mulai_pada
                        ?->timestamp
                        ?? PHP_INT_MAX
                )
                ->values();
        }

        $query = $lokasi
            ->jadwalDonors();

        $this->queryJadwalAktif(
            $query
        );

        return $query->get();
    }

    public function namaLokasi(
        LokasiDonor $lokasi
    ): string {
        return filled($lokasi->nama)
            ? trim((string) $lokasi->nama)
            : 'Lokasi Donor';
    }

    public function alamatLokasi(
        LokasiDonor $lokasi
    ): string {
        return filled($lokasi->alamat)
            ? trim((string) $lokasi->alamat)
            : '-';
    }

    public function alamatLengkap(
        LokasiDonor $lokasi
    ): string {
        $alamatUtama = filled(
            $lokasi->alamat
        )
            ? trim(
                (string) $lokasi->alamat
            )
            : null;

        $wilayah = collect([
            $lokasi->kecamatan,
            $lokasi->kota,
            $lokasi->provinsi,
            $lokasi->kode_pos,
        ])
            ->map(
                fn (mixed $value): string => trim((string) $value)
            )
            ->filter()
            ->unique()
            ->implode(', ');

        $alamatLengkap = collect([
            $alamatUtama,
            $wilayah !== ''
                ? $wilayah
                : null,
        ])
            ->filter()
            ->implode(', ');

        return $alamatLengkap !== ''
            ? $alamatLengkap
            : '-';
    }

    public function wilayahLokasi(
        LokasiDonor $lokasi
    ): string {
        $wilayah = collect([
            $lokasi->kecamatan,
            $lokasi->kota,
            $lokasi->provinsi,
        ])
            ->map(
                fn (mixed $value): string => trim((string) $value)
            )
            ->filter()
            ->unique()
            ->implode(', ');

        return $wilayah !== ''
            ? $wilayah
            : '-';
    }

    public function kontakLokasi(
        LokasiDonor $lokasi
    ): string {
        $kontak =
            $lokasi->nomor_telepon
            ?? $lokasi->nomor_kontak
            ?? null;

        return filled($kontak)
            ? trim((string) $kontak)
            : '-';
    }

    public function namaKontakLokasi(
        LokasiDonor $lokasi
    ): string {
        return filled(
            $lokasi->nama_kontak
        )
            ? trim(
                (string) $lokasi
                    ->nama_kontak
            )
            : '-';
    }

    public function teleponUrl(
        LokasiDonor $lokasi
    ): ?string {
        $telepon =
            $lokasi->nomor_telepon
            ?? $lokasi->nomor_kontak
            ?? null;

        if (blank($telepon)) {
            return null;
        }

        $nomorBersih = preg_replace(
            '/[^0-9+]/',
            '',
            (string) $telepon
        );

        if (
            ! is_string($nomorBersih)
            || $nomorBersih === ''
        ) {
            return null;
        }

        return 'tel:' . $nomorBersih;
    }

    public function whatsappUrl(
        LokasiDonor $lokasi
    ): ?string {
        $telepon =
            $lokasi->nomor_telepon
            ?? $lokasi->nomor_kontak
            ?? null;

        if (blank($telepon)) {
            return null;
        }

        $nomorBersih = preg_replace(
            '/[^0-9]/',
            '',
            (string) $telepon
        );

        if (
            ! is_string($nomorBersih)
            || $nomorBersih === ''
        ) {
            return null;
        }

        if (
            str_starts_with(
                $nomorBersih,
                '0'
            )
        ) {
            $nomorBersih =
                '62' .
                substr(
                    $nomorBersih,
                    1
                );
        }

        return 'https://wa.me/' .
            $nomorBersih;
    }

    public function catatanLokasi(
        LokasiDonor $lokasi
    ): string {
        $catatan =
            $lokasi->catatan_lokasi
            ?? $lokasi->deskripsi
            ?? null;

        return filled($catatan)
            ? trim((string) $catatan)
            : '-';
    }

    public function deskripsiLokasi(
        LokasiDonor $lokasi
    ): string {
        return $this->catatanLokasi(
            $lokasi
        );
    }

    public function koordinatLokasi(
        LokasiDonor $lokasi
    ): string {
        if (
            blank($lokasi->latitude)
            || blank($lokasi->longitude)
        ) {
            return '-';
        }

        return $lokasi->latitude .
            ', ' .
            $lokasi->longitude;
    }

    public function judulJadwal(
        JadwalDonor $jadwal
    ): string {
        return filled($jadwal->judul)
            ? trim((string) $jadwal->judul)
            : 'Kegiatan Donor Darah';
    }

    public function tanggalJadwal(
        JadwalDonor $jadwal
    ): string {
        if (
            $jadwal->mulai_pada ===
            null
        ) {
            return '-';
        }

        return $jadwal->mulai_pada
            ->translatedFormat(
                'l, d F Y'
            );
    }

    public function formatTanggalJadwal(
        JadwalDonor $jadwal
    ): string {
        return $this->tanggalJadwal(
            $jadwal
        );
    }

    public function jamJadwal(
        JadwalDonor $jadwal
    ): string {
        if (
            $jadwal->mulai_pada ===
            null
        ) {
            return '-';
        }

        $mulai = $jadwal
            ->mulai_pada
            ->format('H:i');

        $selesai = $jadwal
            ->selesai_pada
            ?->format('H:i');

        if ($selesai === null) {
            return $mulai;
        }

        return $mulai .
            '–' .
            $selesai;
    }

    public function waktuJadwal(
        JadwalDonor $jadwal
    ): string {
        $jam = $this->jamJadwal(
            $jadwal
        );

        if ($jam === '-') {
            return '-';
        }

        return $jam . ' WIB';
    }

    public function formatWaktuJadwal(
        JadwalDonor $jadwal
    ): string {
        return $this->waktuJadwal(
            $jadwal
        );
    }

    public function sisaKuotaJadwal(
        JadwalDonor $jadwal
    ): int {
        return $jadwal->sisaKuota();
    }

    public function jadwalDapatDidaftar(
        JadwalDonor $jadwal
    ): bool {
        return $jadwal
            ->pendaftaranSedangDibuka()
            && $jadwal->sisaKuota() > 0;
    }

    public function labelStatusJadwal(
        JadwalDonor $jadwal
    ): string {
        if (
            $jadwal->selesai_pada !==
                null
            && $jadwal->selesai_pada
                ->isPast()
        ) {
            return 'Selesai';
        }

        if (
            $jadwal
                ->pendaftaranSedangDibuka()
            && $jadwal->sisaKuota() > 0
        ) {
            return 'Pendaftaran Dibuka';
        }

        if ($jadwal->sisaKuota() < 1) {
            return 'Kuota Penuh';
        }

        if (
            $jadwal
                ->pendaftaran_dibuka_pada !==
                null
            && $jadwal
                ->pendaftaran_dibuka_pada
                ->isFuture()
        ) {
            return 'Segera Dibuka';
        }

        return 'Pendaftaran Ditutup';
    }

    public function statusJadwal(
        JadwalDonor $jadwal
    ): string {
        return $this->labelStatusJadwal(
            $jadwal
        );
    }

    public function mapsUrl(
        LokasiDonor $lokasi
    ): string {
        if (
            filled(
                $lokasi->url_google_maps
            )
        ) {
            return (string) $lokasi
                ->url_google_maps;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' .
            rawurlencode(
                $this->queryMaps(
                    $lokasi
                )
            );
    }

    public function googleMapsUrl(
        LokasiDonor $lokasi
    ): string {
        return $this->mapsUrl(
            $lokasi
        );
    }

    public function embedMapsUrl(
        LokasiDonor $lokasi
    ): string {
        return 'https://maps.google.com/maps?q=' .
            rawurlencode(
                $this->queryMaps(
                    $lokasi
                )
            ) .
            '&z=15&output=embed';
    }

    private function queryMaps(
        LokasiDonor $lokasi
    ): string {
        if (
            filled($lokasi->latitude)
            && filled(
                $lokasi->longitude
            )
        ) {
            return $lokasi->latitude .
                ',' .
                $lokasi->longitude;
        }

        $alamat = collect([
            $this->namaLokasi(
                $lokasi
            ),
            $this->alamatLengkap(
                $lokasi
            ),
        ])
            ->filter(
                fn (string $value): bool => $value !== '-'
            )
            ->implode(', ');

        return $alamat !== ''
            ? $alamat
            : 'Indonesia';
    }
}
