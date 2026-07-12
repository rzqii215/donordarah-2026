<?php

namespace App\Livewire\Donor;

use App\Enums\StatusPendaftaranDonor;
use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use App\Models\PemeriksaanKesehatan;
use App\Services\LayananPendaftaranDonor;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.donor')]
#[Title('Riwayat Donor')]
class Riwayat extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public string $pencarian = '';

    #[Url(as: 'status', except: 'semua')]
    public string $filterStatus = 'semua';

    public ?int $pendaftaranTerpilihId = null;

    public ?int $pendaftaranPembatalanId = null;

    public string $alasanPembatalan = '';

    public function updatedPencarian(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->pastikanFilterValid();
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->pencarian = '';
        $this->filterStatus = 'semua';

        $this->resetPage();
    }

    public function pilihRiwayat(
        int $pendaftaranId
    ): void {
        $pendaftaranAda =
            PendaftaranDonor::query()
                ->where(
                    'pendonor_id',
                    Auth::id()
                )
                ->whereKey($pendaftaranId)
                ->exists();

        if (! $pendaftaranAda) {
            $this->addError(
                'riwayat',
                'Riwayat pendaftaran tidak ditemukan.'
            );

            return;
        }

        $this->resetErrorBag();

        $this->pendaftaranTerpilihId =
            $pendaftaranId;
    }

    public function tutupDetailRiwayat(): void
    {
        if (
            $this->pendaftaranPembatalanId
            !== null
        ) {
            return;
        }

        $this->pendaftaranTerpilihId = null;

        $this->resetErrorBag();
    }

    public function bukaPembatalan(
        int $pendaftaranId
    ): void {
        $pendaftaran =
            $this->cariPendaftaranPengguna(
                $pendaftaranId
            );

        if ($pendaftaran === null) {
            $this->addError(
                'pembatalan',
                'Pendaftaran donor tidak ditemukan.'
            );

            return;
        }

        if (! $pendaftaran->dapatDibatalkan()) {
            $this->addError(
                'pembatalan',
                'Pendaftaran dengan status ini sudah tidak dapat dibatalkan.'
            );

            return;
        }

        $this->resetErrorBag('pembatalan');

        $this->resetErrorBag(
            'alasanPembatalan'
        );

        $this->alasanPembatalan = '';

        $this->pendaftaranPembatalanId =
            $pendaftaranId;
    }

    public function tutupPembatalan(): void
    {
        $this->pendaftaranPembatalanId = null;

        $this->alasanPembatalan = '';

        $this->resetErrorBag('pembatalan');

        $this->resetErrorBag(
            'alasanPembatalan'
        );
    }

    public function batalkanPendaftaran(): void
    {
        $this->validate([
            'alasanPembatalan' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ], [
            'alasanPembatalan.required' =>
                'Alasan pembatalan wajib diisi.',

            'alasanPembatalan.min' =>
                'Alasan pembatalan minimal 10 karakter.',

            'alasanPembatalan.max' =>
                'Alasan pembatalan maksimal 1.000 karakter.',
        ]);

        if (
            $this->pendaftaranPembatalanId
            === null
        ) {
            $this->addError(
                'pembatalan',
                'Pendaftaran yang akan dibatalkan tidak ditemukan.'
            );

            return;
        }

        $pendaftaran =
            $this->cariPendaftaranPengguna(
                $this->pendaftaranPembatalanId
            );

        if ($pendaftaran === null) {
            $this->addError(
                'pembatalan',
                'Pendaftaran donor tidak ditemukan.'
            );

            return;
        }

        try {
            app(LayananPendaftaranDonor::class)
                ->batalkan(
                    pendaftaran: $pendaftaran,
                    alasan: trim(
                        $this->alasanPembatalan
                    ),
                );

            $this->pendaftaranPembatalanId =
                null;

            $this->alasanPembatalan = '';

            session()->flash(
                'success',
                'Pendaftaran donor berhasil dibatalkan.'
            );
        } catch (ValidationException $exception) {
            $pesan = collect(
                $exception->errors()
            )
                ->flatten()
                ->first();

            $this->addError(
                'pembatalan',
                $pesan
                    ?: 'Pendaftaran donor belum dapat dibatalkan.'
            );
        }
    }

    public function render(): View
    {
        return view(
            'livewire.donor.riwayat',
            [
                'riwayatDonors' =>
                    $this->ambilRiwayatDonor(),

                'pendaftaranTerpilih' =>
                    $this->ambilPendaftaranTerpilih(),

                'pendaftaranPembatalan' =>
                    $this->ambilPendaftaranPembatalan(),

                'ringkasan' =>
                    $this->ringkasanRiwayat(),
            ]
        );
    }

    private function ambilRiwayatDonor(): LengthAwarePaginator
    {
        $query = PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
                'kantongDarah',
            ])
            ->where(
                'pendonor_id',
                Auth::id()
            );

        $this->filterPencarian($query);

        $this->filterStatus($query);

        return $query
            ->latest('created_at')
            ->paginate(8)
            ->withQueryString();
    }

    private function ambilPendaftaranTerpilih(): ?PendaftaranDonor
    {
        if (
            $this->pendaftaranTerpilihId
            === null
        ) {
            return null;
        }

        return PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
                'pemeriksaanKesehatan.pemeriksa',
                'kantongDarah',
                'peninjau',
            ])
            ->where(
                'pendonor_id',
                Auth::id()
            )
            ->whereKey(
                $this->pendaftaranTerpilihId
            )
            ->first();
    }

    private function ambilPendaftaranPembatalan(): ?PendaftaranDonor
    {
        if (
            $this->pendaftaranPembatalanId
            === null
        ) {
            return null;
        }

        return $this->cariPendaftaranPengguna(
            $this->pendaftaranPembatalanId
        );
    }

    private function cariPendaftaranPengguna(
        int $pendaftaranId
    ): ?PendaftaranDonor {
        return PendaftaranDonor::query()
            ->with('jadwal')
            ->where(
                'pendonor_id',
                Auth::id()
            )
            ->whereKey($pendaftaranId)
            ->first();
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
                        'nomor_pendaftaran',
                        $pattern
                    )
                    ->orWhereHas(
                        'jadwal',
                        function (
                            Builder $query
                        ) use (
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
        );
    }

    private function filterStatus(
        Builder $query
    ): void {
        $this->pastikanFilterValid();

        match ($this->filterStatus) {
            'proses' =>
                $query->whereIn(
                    'status',
                    [
                        StatusPendaftaranDonor
                            ::Menunggu
                            ->value,

                        StatusPendaftaranDonor
                            ::Disetujui
                            ->value,

                        StatusPendaftaranDonor
                            ::Hadir
                            ->value,

                        StatusPendaftaranDonor
                            ::Layak
                            ->value,
                    ]
                ),

            'selesai' =>
                $query->where(
                    'status',
                    StatusPendaftaranDonor
                        ::Selesai
                        ->value
                ),

            'bermasalah' =>
                $query->whereIn(
                    'status',
                    [
                        StatusPendaftaranDonor
                            ::TidakLayak
                            ->value,

                        StatusPendaftaranDonor
                            ::Ditolak
                            ->value,

                        StatusPendaftaranDonor
                            ::Dibatalkan
                            ->value,

                        StatusPendaftaranDonor
                            ::TidakHadir
                            ->value,
                    ]
                ),

            default => null,
        };
    }

    private function pastikanFilterValid(): void
    {
        if (
            ! in_array(
                $this->filterStatus,
                [
                    'semua',
                    'proses',
                    'selesai',
                    'bermasalah',
                ],
                true
            )
        ) {
            $this->filterStatus = 'semua';
        }
    }

    /**
     * @return array<string, int>
     */
    private function ringkasanRiwayat(): array
    {
        $query = PendaftaranDonor::query()
            ->where(
                'pendonor_id',
                Auth::id()
            );

        return [
            'total' =>
                (clone $query)->count(),

            'proses' =>
                (clone $query)
                    ->whereIn(
                        'status',
                        [
                            StatusPendaftaranDonor
                                ::Menunggu
                                ->value,

                            StatusPendaftaranDonor
                                ::Disetujui
                                ->value,

                            StatusPendaftaranDonor
                                ::Hadir
                                ->value,

                            StatusPendaftaranDonor
                                ::Layak
                                ->value,
                        ]
                    )
                    ->count(),

            'selesai' =>
                (clone $query)
                    ->where(
                        'status',
                        StatusPendaftaranDonor
                            ::Selesai
                            ->value
                    )
                    ->count(),

            'bermasalah' =>
                (clone $query)
                    ->whereIn(
                        'status',
                        [
                            StatusPendaftaranDonor
                                ::TidakLayak
                                ->value,

                            StatusPendaftaranDonor
                                ::Ditolak
                                ->value,

                            StatusPendaftaranDonor
                                ::Dibatalkan
                                ->value,

                            StatusPendaftaranDonor
                                ::TidakHadir
                                ->value,
                        ]
                    )
                    ->count(),
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     label: string
     * }>
     */
    public function opsiFilterStatus(): array
    {
        return [
            [
                'value' => 'semua',
                'label' => 'Semua',
            ],
            [
                'value' => 'proses',
                'label' => 'Sedang Diproses',
            ],
            [
                'value' => 'selesai',
                'label' => 'Donor Selesai',
            ],
            [
                'value' => 'bermasalah',
                'label' => 'Tidak Lanjut',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function timeline(
        PendaftaranDonor $pendaftaran
    ): array {
        $status = $this->nilaiEnum(
            $pendaftaran->status
        );

        $steps = match ($status) {
            'rejected' => [
                $this->stepTimeline(
                    'created',
                    'Pendaftaran Dikirim',
                    'Pendaftaran donor berhasil dikirim.'
                ),
                $this->stepTimeline(
                    'pending',
                    'Menunggu Verifikasi',
                    'Pendaftaran sedang ditinjau petugas.'
                ),
                $this->stepTimeline(
                    'rejected',
                    'Pendaftaran Ditolak',
                    'Pendaftaran tidak disetujui petugas.'
                ),
            ],

            'cancelled' => [
                $this->stepTimeline(
                    'created',
                    'Pendaftaran Dikirim',
                    'Pendaftaran donor berhasil dikirim.'
                ),
                $this->stepTimeline(
                    'cancelled',
                    'Pendaftaran Dibatalkan',
                    'Pendaftaran dibatalkan.'
                ),
            ],

            'ineligible' => [
                $this->stepTimeline(
                    'created',
                    'Pendaftaran Dikirim',
                    'Pendaftaran donor berhasil dikirim.'
                ),
                $this->stepTimeline(
                    'pending',
                    'Menunggu Verifikasi',
                    'Pendaftaran sedang ditinjau petugas.'
                ),
                $this->stepTimeline(
                    'approved',
                    'Pendaftaran Disetujui',
                    'Pendaftaran disetujui petugas.'
                ),
                $this->stepTimeline(
                    'attended',
                    'Hadir di Lokasi',
                    'Kehadiran pendonor telah dicatat.'
                ),
                $this->stepTimeline(
                    'ineligible',
                    'Tidak Layak Donor',
                    'Hasil pemeriksaan belum memenuhi syarat donor.'
                ),
            ],

            'no_show' => [
                $this->stepTimeline(
                    'created',
                    'Pendaftaran Dikirim',
                    'Pendaftaran donor berhasil dikirim.'
                ),
                $this->stepTimeline(
                    'pending',
                    'Menunggu Verifikasi',
                    'Pendaftaran sedang ditinjau petugas.'
                ),
                $this->stepTimeline(
                    'approved',
                    'Pendaftaran Disetujui',
                    'Pendaftaran disetujui petugas.'
                ),
                $this->stepTimeline(
                    'no_show',
                    'Tidak Hadir',
                    'Pendonor tidak hadir pada jadwal donor.'
                ),
            ],

            default => [
                $this->stepTimeline(
                    'created',
                    'Pendaftaran Dikirim',
                    'Pendaftaran donor berhasil dikirim.'
                ),
                $this->stepTimeline(
                    'pending',
                    'Menunggu Verifikasi',
                    'Pendaftaran sedang ditinjau petugas.'
                ),
                $this->stepTimeline(
                    'approved',
                    'Pendaftaran Disetujui',
                    'Pendaftaran disetujui petugas.'
                ),
                $this->stepTimeline(
                    'attended',
                    'Hadir di Lokasi',
                    'Kehadiran pendonor telah dicatat.'
                ),
                $this->stepTimeline(
                    'eligible',
                    'Layak Donor',
                    'Pemeriksaan kesehatan memenuhi syarat.'
                ),
                $this->stepTimeline(
                    'completed',
                    'Donor Selesai',
                    'Proses donor berhasil diselesaikan.'
                ),
            ],
        };

        $currentIndex = array_search(
            $status,
            array_column(
                $steps,
                'status'
            ),
            true
        );

        if ($currentIndex === false) {
            $currentIndex = 1;
        }

        return collect($steps)
            ->map(
                function (
                    array $step,
                    int $index
                ) use (
                    $pendaftaran,
                    $currentIndex
                ): array {
                    $class = 'waiting';

                    if ($index < $currentIndex) {
                        $class = 'done';
                    }

                    if ($index === $currentIndex) {
                        $class = 'active';
                    }

                    if (
                        $index === $currentIndex
                        && in_array(
                            $step['status'],
                            [
                                'rejected',
                                'cancelled',
                                'ineligible',
                                'no_show',
                            ],
                            true
                        )
                    ) {
                        $class = 'danger';
                    }

                    return [
                        'status' =>
                            $step['status'],

                        'label' =>
                            $step['label'],

                        'description' =>
                            $step['description'],

                        'class' =>
                            $class,

                        'tanggal' =>
                            $this->tanggalTahap(
                                $pendaftaran,
                                $step['status']
                            ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     status: string,
     *     label: string,
     *     description: string
     * }
     */
    private function stepTimeline(
        string $status,
        string $label,
        string $description
    ): array {
        return [
            'status' => $status,
            'label' => $label,
            'description' => $description,
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     jawaban: string,
     *     positif: bool
     * }>
     */
    public function jawabanSkrining(
        PendaftaranDonor $pendaftaran
    ): array {
        $jawaban = $pendaftaran
            ->jawaban_skrining;

        if (! is_array($jawaban)) {
            return [];
        }

        $pertanyaan = [
            'sehat_hari_ini' => [
                'label' =>
                    'Merasa sehat hari ini',

                'positifJika' =>
                    true,
            ],

            'sedang_minum_obat' => [
                'label' =>
                    'Sedang mengonsumsi obat',

                'positifJika' =>
                    false,
            ],

            'operasi_terakhir' => [
                'label' =>
                    'Menjalani operasi dalam waktu dekat',

                'positifJika' =>
                    false,
            ],

            'cukup_tidur' => [
                'label' =>
                    'Sudah tidur dengan cukup',

                'positifJika' =>
                    true,
            ],

            'sudah_makan' => [
                'label' =>
                    'Sudah makan sebelum donor',

                'positifJika' =>
                    true,
            ],
        ];

        $hasil = [];

        foreach (
            $pertanyaan
            as $key => $config
        ) {
            if (
                ! array_key_exists(
                    $key,
                    $jawaban
                )
            ) {
                continue;
            }

            $nilai = filter_var(
                $jawaban[$key],
                FILTER_VALIDATE_BOOLEAN
            );

            $hasil[] = [
                'key' => $key,

                'label' =>
                    $config['label'],

                'jawaban' =>
                    $nilai
                        ? 'Ya'
                        : 'Tidak',

                'positif' =>
                    $nilai ===
                    $config['positifJika'],
            ];
        }

        return $hasil;
    }

    public function nomorPendaftaran(
        PendaftaranDonor $pendaftaran
    ): string {
        return (string) (
            $pendaftaran
                ->nomor_pendaftaran
            ?? 'REG-'
                . str_pad(
                    (string) $pendaftaran->id,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
        );
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
                'Tidak Layak Donor',

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

    public function statusTone(
        mixed $status
    ): string {
        return match (
            $this->nilaiEnum($status)
        ) {
            'completed',
            'eligible',
            'attended',
            'approved' => 'success',

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
        ?JadwalDonor $jadwal
    ): string {
        return $jadwal?->judul
            ?? 'Jadwal tidak tersedia';
    }

    public function tanggalJadwal(
        ?JadwalDonor $jadwal
    ): string {
        $mulai = $this->tanggalCarbon(
            $jadwal?->mulai_pada
        );

        return $mulai
            ? $mulai->translatedFormat(
                'l, d F Y'
            )
            : 'Tanggal belum ditentukan';
    }

    public function jamJadwal(
        ?JadwalDonor $jadwal
    ): string {
        $mulai = $this->tanggalCarbon(
            $jadwal?->mulai_pada
        );

        $selesai = $this->tanggalCarbon(
            $jadwal?->selesai_pada
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

    public function mapsUrl(
        ?LokasiDonor $lokasi
    ): string {
        return $lokasi
            ?->google_maps_tampilan
            ?? 'https://www.google.com/maps';
    }

    public function alasanStatus(
        PendaftaranDonor $pendaftaran
    ): string {
        return (string) (
            $pendaftaran
                ->alasan_penolakan
            ?? $pendaftaran
                ->alasan_pembatalan
            ?? $pendaftaran
                ->pemeriksaanKesehatan
                ?->alasan_tidak_layak
            ?? '-'
        );
    }

    public function labelEnum(
        mixed $value
    ): string {
        if (
            is_object($value)
            && method_exists(
                $value,
                'label'
            )
        ) {
            return (string) $value->label();
        }

        $enumValue = $this->nilaiEnum(
            $value
        );

        return $enumValue !== ''
            ? Str::headline($enumValue)
            : '-';
    }

    public function golonganPemeriksaan(
        ?PemeriksaanKesehatan $pemeriksaan
    ): string {
        if ($pemeriksaan === null) {
            return '-';
        }

        $golongan = Str::upper(
            $this->nilaiEnum(
                $pemeriksaan->golongan_darah
            )
        );

        $rhesusValue = Str::lower(
            $this->nilaiEnum(
                $pemeriksaan->rhesus
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

        return $golongan !== ''
            ? $golongan . $rhesus
            : '-';
    }

    public function tanggalFormat(
        mixed $value
    ): string {
        $tanggal = $this->tanggalCarbon(
            $value
        );

        return $tanggal
            ? $tanggal->translatedFormat(
                'd F Y H:i'
            )
            : '-';
    }

    private function tanggalTahap(
        PendaftaranDonor $pendaftaran,
        string $status
    ): string {
        $value = match ($status) {
            'created',
            'pending' =>
                $pendaftaran->created_at,

            'approved',
            'rejected' =>
                $pendaftaran->ditinjau_pada,

            'attended' =>
                $pendaftaran->hadir_pada,

            'eligible',
            'ineligible' =>
                $pendaftaran
                    ->pemeriksaanKesehatan
                    ?->diperiksa_pada,

            'completed' =>
                $pendaftaran
                    ->selesai_pada,

            'cancelled' =>
                $pendaftaran
                    ->dibatalkan_pada,

            'no_show' =>
                $pendaftaran
                    ->ditinjau_pada,

            default => null,
        };

        return $this->tanggalFormat(
            $value
        );
    }

    /**
     * Dipakai oleh Blade untuk mendapatkan
     * nilai string dari backed enum.
     */
    public function nilaiEnumForView(
        mixed $value
    ): string {
        return $this->nilaiEnum(
            $value
        );
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