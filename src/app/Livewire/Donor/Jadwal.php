<?php

namespace App\Livewire\Donor;

use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.donor')]
#[Title('Jadwal Donor')]
class Jadwal extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $pencarian = '';

    #[Url(as: 'kota', except: '')]
    public string $kota = '';

    #[Url(as: 'mulai', except: '')]
    public string $tanggalMulai = '';

    #[Url(as: 'selesai', except: '')]
    public string $tanggalSelesai = '';

    #[Url(as: 'urut', except: 'terdekat')]
    public string $urutan = 'terdekat';

    #[Url(as: 'tersedia', except: false)]
    public bool $hanyaTersedia = false;

    public ?int $jadwalTerpilihId = null;

    public function updatedPencarian(): void
    {
        $this->resetPage();
    }

    public function updatedUrutan(): void
    {
        $this->pastikanUrutanValid();
        $this->resetPage();
    }

    public function updatedHanyaTersedia(): void
    {
        $this->resetPage();
    }

    public function terapkanFilter(): void
    {
        $this->validate([
            'kota' => [
                'nullable',
                'string',
                'max:150',
            ],

            'tanggalMulai' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'tanggalSelesai' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggalMulai',
            ],
        ], [
            'tanggalMulai.date_format' =>
                'Tanggal mulai tidak valid.',

            'tanggalSelesai.date_format' =>
                'Tanggal selesai tidak valid.',

            'tanggalSelesai.after_or_equal' =>
                'Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.',
        ]);

        $this->pastikanUrutanValid();
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset([
            'pencarian',
            'kota',
            'tanggalMulai',
            'tanggalSelesai',
            'hanyaTersedia',
        ]);

        $this->urutan = 'terdekat';

        $this->resetValidation();
        $this->resetPage();
    }

    public function pilihJadwal(
        int $jadwalId
    ): void {
        $this->resetErrorBag('jadwal');

        $jadwalAda = JadwalDonor::query()
            ->whereKey($jadwalId)
            ->exists();

        if (! $jadwalAda) {
            $this->addError(
                'jadwal',
                'Jadwal donor yang dipilih sudah tidak tersedia.'
            );

            return;
        }

        $this->jadwalTerpilihId = $jadwalId;
    }

    public function tutupDetailJadwal(): void
    {
        $this->jadwalTerpilihId = null;
        $this->resetErrorBag('jadwal');
    }

    public function daftarJadwal(
        int $jadwalId
    ): mixed {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->guest('/login');
        }

        if (! $this->userPunyaProfilPendonor($user)) {
            $this->addError(
                'jadwal',
                'Lengkapi profil pendonor terlebih dahulu sebelum mendaftar donor.'
            );

            return null;
        }

        $jadwal = JadwalDonor::query()
            ->withCount('pendaftaranAktif')
            ->findOrFail($jadwalId);

        if (! $this->jadwalDapatDidaftar($jadwal)) {
            $this->addError(
                'jadwal',
                'Jadwal ini belum dapat didaftarkan, sudah ditutup, atau kuotanya telah penuh.'
            );

            return null;
        }

        return redirect()->route(
            'donor.jadwal.daftar',
            [
                'jadwal' => $jadwal->id,
            ]
        );
    }

    public function render(): View
    {
        $jadwalDonors =
            $this->ambilJadwalDonor();

        $jadwalTerpilih =
            $this->ambilJadwalTerpilih();

        return view(
            'livewire.donor.jadwal',
            [
                'jadwalDonors' =>
                    $jadwalDonors,

                'jadwalTerpilih' =>
                    $jadwalTerpilih,

                'pendaftaranJadwals' =>
                    $this->ambilPendaftaranJadwals(
                        collect(
                            $jadwalDonors->items()
                        )
                    ),

                'pendaftaranTerpilih' =>
                    $this->ambilPendaftaranTerpilih(
                        $jadwalTerpilih
                    ),

                'kotaTersedia' =>
                    $this->ambilKotaTersedia(),
            ]
        );
    }

    private function ambilJadwalDonor(): LengthAwarePaginator
    {
        $query = JadwalDonor::query()
            ->with('lokasi')
            ->withCount('pendaftaranAktif')
            ->where(
                'status',
                StatusJadwalDonor
                    ::Dipublikasikan
                    ->value
            )
            ->where(
                'mulai_pada',
                '>=',
                now()->startOfDay()
            );

        $this->filterPencarian($query);
        $this->filterKota($query);
        $this->filterTanggal($query);
        $this->filterKetersediaan($query);
        $this->urutkanJadwal($query);

        return $query
            ->paginate(6)
            ->withQueryString();
    }

    private function ambilJadwalTerpilih(): ?JadwalDonor
    {
        if ($this->jadwalTerpilihId === null) {
            return null;
        }

        return JadwalDonor::query()
            ->with('lokasi')
            ->withCount('pendaftaranAktif')
            ->find($this->jadwalTerpilihId);
    }

    /**
     * @param Collection<int, JadwalDonor> $jadwals
     * @return Collection<int|string, PendaftaranDonor>
     */
    private function ambilPendaftaranJadwals(
        Collection $jadwals
    ): Collection {
        $userId = Auth::id();

        if (
            $userId === null
            || $jadwals->isEmpty()
        ) {
            return collect();
        }

        return PendaftaranDonor::query()
            ->where(
                'pendonor_id',
                $userId
            )
            ->whereIn(
                'jadwal_donor_id',
                $jadwals->pluck('id')
            )
            ->latest()
            ->get()
            ->keyBy('jadwal_donor_id');
    }

    private function ambilPendaftaranTerpilih(
        ?JadwalDonor $jadwal
    ): ?PendaftaranDonor {
        $userId = Auth::id();

        if (
            $userId === null
            || $jadwal === null
        ) {
            return null;
        }

        return PendaftaranDonor::query()
            ->where(
                'pendonor_id',
                $userId
            )
            ->where(
                'jadwal_donor_id',
                $jadwal->id
            )
            ->latest()
            ->first();
    }

    /**
     * @return Collection<int, string>
     */
    private function ambilKotaTersedia(): Collection
    {
        return LokasiDonor::query()
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->where('aktif', true)
            ->whereHas(
                'jadwalDonors',
                function (Builder $query): void {
                    $query
                        ->where(
                            'status',
                            StatusJadwalDonor
                                ::Dipublikasikan
                                ->value
                        )
                        ->where(
                            'mulai_pada',
                            '>=',
                            now()->startOfDay()
                        );
                }
            )
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota')
            ->filter()
            ->values();
    }

    private function filterPencarian(
        Builder $query
    ): void {
        $keyword = trim(
            $this->pencarian
        );

        if ($keyword === '') {
            return;
        }

        $pattern = '%' . $keyword . '%';

        $query->where(
            function (Builder $query) use (
                $pattern
            ): void {
                $query
                    ->whereLike(
                        'kode_jadwal',
                        $pattern
                    )
                    ->orWhereLike(
                        'judul',
                        $pattern
                    )
                    ->orWhereLike(
                        'deskripsi',
                        $pattern
                    )
                    ->orWhereHas(
                        'lokasi',
                        function (
                            Builder $query
                        ) use (
                            $pattern
                        ): void {
                            $query
                                ->whereLike(
                                    'nama',
                                    $pattern
                                )
                                ->orWhereLike(
                                    'alamat',
                                    $pattern
                                )
                                ->orWhereLike(
                                    'kota',
                                    $pattern
                                )
                                ->orWhereLike(
                                    'provinsi',
                                    $pattern
                                );
                        }
                    );
            }
        );
    }

    private function filterKota(
        Builder $query
    ): void {
        if (blank($this->kota)) {
            return;
        }

        $kota = trim($this->kota);

        $query->whereHas(
            'lokasi',
            fn (Builder $query): Builder =>
                $query->where(
                    'kota',
                    $kota
                )
        );
    }

    private function filterTanggal(
        Builder $query
    ): void {
        $tanggalMulai =
            $this->tanggalFilter(
                $this->tanggalMulai
            );

        $tanggalSelesai =
            $this->tanggalFilter(
                $this->tanggalSelesai
            );

        if ($tanggalMulai !== null) {
            $query->where(
                'mulai_pada',
                '>=',
                $tanggalMulai->startOfDay()
            );
        }

        if ($tanggalSelesai !== null) {
            $query->where(
                'mulai_pada',
                '<=',
                $tanggalSelesai->endOfDay()
            );
        }
    }

    private function filterKetersediaan(
        Builder $query
    ): void {
        if (! $this->hanyaTersedia) {
            return;
        }

        $sekarang = now();

        $query
            ->where(
                function (
                    Builder $query
                ) use (
                    $sekarang
                ): void {
                    $query
                        ->whereNull(
                            'pendaftaran_dibuka_pada'
                        )
                        ->orWhere(
                            'pendaftaran_dibuka_pada',
                            '<=',
                            $sekarang
                        );
                }
            )
            ->where(
                function (
                    Builder $query
                ) use (
                    $sekarang
                ): void {
                    $query
                        ->whereNull(
                            'pendaftaran_ditutup_pada'
                        )
                        ->orWhere(
                            'pendaftaran_ditutup_pada',
                            '>=',
                            $sekarang
                        );
                }
            )
            ->where(
                'mulai_pada',
                '>',
                $sekarang
            );

        $userId = Auth::id();

        if ($userId !== null) {
            $query->whereDoesntHave(
                'pendaftaranDonor',
                fn (Builder $query): Builder =>
                    $query->where(
                        'pendonor_id',
                        $userId
                    )
            );
        }

        $statusMengurangiKuota = collect(
            StatusPendaftaranDonor
                ::statusMengurangiKuota()
        )
            ->map(
                fn (mixed $status): string =>
                    $this->nilaiEnum($status)
            )
            ->filter()
            ->values()
            ->all();

        if ($statusMengurangiKuota === []) {
            return;
        }

        $jadwalTable =
            (new JadwalDonor())->getTable();

        $pendaftaranTable =
            (new PendaftaranDonor())->getTable();

        $placeholders = implode(
            ', ',
            array_fill(
                0,
                count($statusMengurangiKuota),
                '?'
            )
        );

        $query->whereRaw(
            '('
            . $jadwalTable
            . '.kuota IS NULL OR '
            . $jadwalTable
            . '.kuota > ('
            . 'SELECT COUNT(*) FROM '
            . $pendaftaranTable
            . ' WHERE '
            . $pendaftaranTable
            . '.jadwal_donor_id = '
            . $jadwalTable
            . '.id AND '
            . $pendaftaranTable
            . '.deleted_at IS NULL AND '
            . $pendaftaranTable
            . '.status IN ('
            . $placeholders
            . ')))',
            $statusMengurangiKuota
        );
    }

    private function urutkanJadwal(
        Builder $query
    ): void {
        match ($this->urutan) {
            'kuota_terbesar' =>
                $query
                    ->orderByDesc('kuota')
                    ->orderBy('mulai_pada'),

            'terbaru' =>
                $query
                    ->orderByDesc('created_at')
                    ->orderBy('mulai_pada'),

            default =>
                $query
                    ->orderBy('mulai_pada')
                    ->orderBy('id'),
        };
    }

    private function pastikanUrutanValid(): void
    {
        if (
            ! in_array(
                $this->urutan,
                [
                    'terdekat',
                    'kuota_terbesar',
                    'terbaru',
                ],
                true
            )
        ) {
            $this->urutan = 'terdekat';
        }
    }

    public function jadwalDapatDidaftar(
        JadwalDonor $jadwal,
        bool $periksaPendaftaranPengguna = true
    ): bool {
        if ($periksaPendaftaranPengguna) {
            $userId = Auth::id();

            if (
                $userId === null
                || PendaftaranDonor::query()
                    ->where(
                        'pendonor_id',
                        $userId
                    )
                    ->where(
                        'jadwal_donor_id',
                        $jadwal->id
                    )
                    ->exists()
            ) {
                return false;
            }
        }

        return data_get(
            $this->statusKetersediaanJadwal(
                $jadwal
            ),
            'key'
        ) === 'open';
    }

    /**
     * @return array{
     *     key: string,
     *     label: string
     * }
     */
    public function statusKetersediaanJadwal(
        JadwalDonor $jadwal
    ): array {
        $status = Str::lower(
            $this->nilaiEnum(
                $jadwal->status
            )
        );

        if (
            $status !==
            StatusJadwalDonor
                ::Dipublikasikan
                ->value
        ) {
            return [
                'key' => 'closed',
                'label' => 'Tidak Tersedia',
            ];
        }

        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
        );

        $selesai = $this->tanggalCarbon(
            $jadwal->selesai_pada
        );

        $pendaftaranDibuka =
            $this->tanggalCarbon(
                $jadwal
                    ->pendaftaran_dibuka_pada
            );

        $pendaftaranDitutup =
            $this->tanggalCarbon(
                $jadwal
                    ->pendaftaran_ditutup_pada
            );

        if (
            $selesai !== null
            && now()->gt($selesai)
        ) {
            return [
                'key' => 'finished',
                'label' => 'Jadwal Selesai',
            ];
        }

        if (
            $mulai !== null
            && now()->gte($mulai)
        ) {
            return [
                'key' => 'closed',
                'label' => 'Pendaftaran Ditutup',
            ];
        }

        if (
            $pendaftaranDibuka !== null
            && now()->lt(
                $pendaftaranDibuka
            )
        ) {
            return [
                'key' => 'soon',
                'label' => 'Segera Dibuka',
            ];
        }

        if (
            $pendaftaranDitutup !== null
            && now()->gt(
                $pendaftaranDitutup
            )
        ) {
            return [
                'key' => 'closed',
                'label' => 'Pendaftaran Ditutup',
            ];
        }

        if (
            $jadwal->kuota !== null
            && $this->sisaKuota(
                $jadwal
            ) <= 0
        ) {
            return [
                'key' => 'full',
                'label' => 'Kuota Penuh',
            ];
        }

        return [
            'key' => 'open',
            'label' => 'Buka Pendaftaran',
        ];
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

        $value = $this->nilaiEnum(
            $status
        );

        return match ($value) {
            'pending' =>
                'Menunggu Verifikasi',

            'approved' =>
                'Disetujui',

            'attended' =>
                'Hadir',

            'eligible' =>
                'Layak Donor',

            'ineligible' =>
                'Tidak Layak',

            'completed' =>
                'Donor Selesai',

            'rejected' =>
                'Ditolak',

            'cancelled' =>
                'Dibatalkan',

            'no_show',
            'absent',
            'not_attended' =>
                'Tidak Hadir',

            default => $value !== ''
                ? Str::headline($value)
                : '-',
        };
    }

    public function statusPendaftaranTone(
        mixed $status
    ): string {
        return match (
            $this->nilaiEnum($status)
        ) {
            'approved',
            'attended',
            'eligible',
            'completed' => 'success',

            'rejected',
            'cancelled',
            'ineligible',
            'no_show',
            'absent',
            'not_attended' => 'danger',

            default => 'warning',
        };
    }

    public function judulJadwal(
        JadwalDonor $jadwal
    ): string {
        return (string) (
            $jadwal->judul
            ?? 'Jadwal Donor'
        );
    }

    public function deskripsiJadwal(
        JadwalDonor $jadwal
    ): string {
        return (string) (
            $jadwal->deskripsi
            ?? 'Kegiatan donor darah yang dapat diikuti oleh pendonor terdaftar.'
        );
    }

    public function tanggalJadwal(
        JadwalDonor $jadwal
    ): string {
        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
        );

        return $mulai
            ? $mulai->translatedFormat(
                'l, d F Y'
            )
            : 'Tanggal belum ditentukan';
    }

    public function jamJadwal(
        JadwalDonor $jadwal
    ): string {
        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
        );

        $selesai = $this->tanggalCarbon(
            $jadwal->selesai_pada
        );

        if ($mulai === null) {
            return '-';
        }

        if ($selesai === null) {
            return $mulai->format('H:i');
        }

        return $mulai->format('H:i')
            . '–'
            . $selesai->format('H:i');
    }

    public function periodePendaftaran(
        JadwalDonor $jadwal
    ): string {
        $mulai = $this->tanggalCarbon(
            $jadwal
                ->pendaftaran_dibuka_pada
        );

        $selesai = $this->tanggalCarbon(
            $jadwal
                ->pendaftaran_ditutup_pada
        );

        if (
            $mulai === null
            && $selesai === null
        ) {
            return 'Mengikuti kebijakan petugas';
        }

        if (
            $mulai !== null
            && $selesai !== null
        ) {
            return $mulai
                ->translatedFormat(
                    'd M Y H:i'
                )
                . ' — '
                . $selesai
                    ->translatedFormat(
                        'd M Y H:i'
                    );
        }

        if ($mulai !== null) {
            return 'Dibuka mulai '
                . $mulai
                    ->translatedFormat(
                        'd M Y H:i'
                    );
        }

        return 'Ditutup pada '
            . $selesai?->translatedFormat(
                'd M Y H:i'
            );
    }

    public function kuotaTotal(
        JadwalDonor $jadwal
    ): int {
        return max(
            0,
            (int) (
                $jadwal->kuota
                ?? 0
            )
        );
    }

    public function sisaKuota(
        JadwalDonor $jadwal
    ): int {
        $kuota = $this->kuotaTotal(
            $jadwal
        );

        if ($kuota <= 0) {
            return 0;
        }

        $jumlahPendaftar =
            $jadwal->getAttribute(
                'pendaftaran_aktif_count'
            );

        if ($jumlahPendaftar === null) {
            $jumlahPendaftar =
                $jadwal
                    ->jumlahPendaftarAktif();
        }

        return max(
            0,
            $kuota
            - (int) $jumlahPendaftar
        );
    }

    public function persentaseKuotaTerisi(
        JadwalDonor $jadwal
    ): int {
        $kuota = $this->kuotaTotal(
            $jadwal
        );

        if ($kuota <= 0) {
            return 0;
        }

        $terisi = $kuota
            - $this->sisaKuota(
                $jadwal
            );

        return max(
            0,
            min(
                100,
                (int) round(
                    ($terisi / $kuota) * 100
                )
            )
        );
    }

    public function namaLokasi(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi?->nama_tampilan
            ?? 'Lokasi belum ditentukan';
    }

    public function alamatLokasi(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi?->alamat_tampilan
            ?? '-';
    }

    public function wilayahLokasi(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi?->wilayah_tampilan
            ?? '-';
    }

    public function kontakLokasi(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi?->kontak_tampilan
            ?? '-';
    }

    public function catatanLokasi(
        ?LokasiDonor $lokasi
    ): string {
        if ($lokasi === null) {
            return '-';
        }

        return filled(
            $lokasi->catatan_lokasi
        )
            ? (string) $lokasi
                ->catatan_lokasi
            : '-';
    }

    public function mapsUrl(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi
            ?->google_maps_tampilan
            ?? 'https://www.google.com/maps';
    }

    public function embedMapsUrl(
        ?LokasiDonor $lokasi
    ): string {
        if ($lokasi === null) {
            return 'https://maps.google.com/maps?q=Indonesia&z=5&output=embed';
        }

        return 'https://maps.google.com/maps?q='
            . rawurlencode(
                $this->queryMaps(
                    $lokasi
                )
            )
            . '&z=15&output=embed';
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
            return $lokasi->latitude
                . ','
                . $lokasi->longitude;
        }

        return collect([
            $this->namaLokasi(
                $lokasi
            ),

            $this->alamatLokasi(
                $lokasi
            ),

            $this->wilayahLokasi(
                $lokasi
            ),
        ])
            ->filter(
                fn (string $value): bool =>
                    $value !== '-'
            )
            ->implode(', ');
    }

    private function userPunyaProfilPendonor(
        mixed $user
    ): bool {
        if (
            ! method_exists(
                $user,
                'profilPendonor'
            )
        ) {
            return false;
        }

        return $user
            ->profilPendonor()
            ->exists();
    }

    private function tanggalCarbon(
        mixed $value
    ): ?CarbonInterface {
        if ($value instanceof CarbonInterface) {
            return $value;
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

    private function tanggalFilter(
        string $value
    ): ?Carbon {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat(
                'Y-m-d',
                $value
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function nilaiEnum(
        mixed $value
    ): string {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        return trim(
            (string) $value
        );
    }
}