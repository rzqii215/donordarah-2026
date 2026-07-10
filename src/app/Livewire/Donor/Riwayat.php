<?php

namespace App\Livewire\Donor;

use App\Models\JadwalDonor;
use App\Models\LokasiDonor;
use App\Models\PendaftaranDonor;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.donor')]
class Riwayat extends Component
{
    public string $filterStatus = 'semua';

    public ?int $pendaftaranTerpilihId = null;

    public function pilihRiwayat(int $pendaftaranId): void
    {
        $this->pendaftaranTerpilihId = $pendaftaranId;
    }

    public function tutupDetailRiwayat(): void
    {
        $this->pendaftaranTerpilihId = null;
    }

    public function render(): View
    {
        $riwayatDonors = $this->ambilRiwayatDonor();

        return view('livewire.donor.riwayat', [
            'riwayatDonors' => $riwayatDonors,
            'pendaftaranTerpilih' => $this->ambilPendaftaranTerpilih(),
            'ringkasan' => $this->ringkasanRiwayat(),
        ]);
    }

    /**
     * @return Collection<int, PendaftaranDonor>
     */
    private function ambilRiwayatDonor(): Collection
    {
        $query = PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
                'kantongDarah',
            ])
            ->where('pendonor_id', Auth::id());

        if ($this->filterStatus === 'proses') {
            $query->whereIn('status', [
                'pending',
                'approved',
                'attended',
                'eligible',
            ]);
        }

        if ($this->filterStatus === 'selesai') {
            $query->where('status', 'completed');
        }

        if ($this->filterStatus === 'bermasalah') {
            $query->whereIn('status', [
                'ineligible',
                'rejected',
                'cancelled',
                'absent',
                'not_attended',
            ]);
        }

        return $query
            ->latest()
            ->limit(100)
            ->get();
    }

    private function ambilPendaftaranTerpilih(): ?PendaftaranDonor
    {
        if ($this->pendaftaranTerpilihId === null) {
            return null;
        }

        return PendaftaranDonor::query()
            ->with([
                'jadwal.lokasi',
                'pemeriksaanKesehatan',
                'kantongDarah',
            ])
            ->where('pendonor_id', Auth::id())
            ->find($this->pendaftaranTerpilihId);
    }

    /**
     * @return array<string, int>
     */
    private function ringkasanRiwayat(): array
    {
        $query = PendaftaranDonor::query()
            ->where('pendonor_id', Auth::id());

        return [
            'total' => (clone $query)->count(),

            'proses' => (clone $query)
                ->whereIn('status', [
                    'pending',
                    'approved',
                    'attended',
                    'eligible',
                ])
                ->count(),

            'selesai' => (clone $query)
                ->where('status', 'completed')
                ->count(),

            'bermasalah' => (clone $query)
                ->whereIn('status', [
                    'ineligible',
                    'rejected',
                    'cancelled',
                    'absent',
                    'not_attended',
                ])
                ->count(),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function opsiFilterStatus(): array
    {
        return [
            [
                'value' => 'semua',
                'label' => 'Semua Riwayat',
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
     * @return array<int, array<string, mixed>>
     */
    public function timeline(PendaftaranDonor $pendaftaran): array
    {
        $status = $this->nilaiEnum($pendaftaran->status);

        $steps = match ($status) {
            'rejected' => [
                [
                    'status' => 'pending',
                    'label' => 'Menunggu Verifikasi',
                    'description' => 'Pendaftaran donor dibuat.',
                ],
                [
                    'status' => 'rejected',
                    'label' => 'Ditolak',
                    'description' => 'Pendaftaran tidak disetujui petugas.',
                ],
            ],

            'cancelled' => [
                [
                    'status' => 'pending',
                    'label' => 'Menunggu Verifikasi',
                    'description' => 'Pendaftaran donor dibuat.',
                ],
                [
                    'status' => 'cancelled',
                    'label' => 'Dibatalkan',
                    'description' => 'Pendaftaran donor dibatalkan.',
                ],
            ],

            'ineligible' => [
                [
                    'status' => 'pending',
                    'label' => 'Menunggu Verifikasi',
                    'description' => 'Pendaftaran donor dibuat.',
                ],
                [
                    'status' => 'approved',
                    'label' => 'Disetujui',
                    'description' => 'Pendaftaran disetujui petugas.',
                ],
                [
                    'status' => 'attended',
                    'label' => 'Hadir',
                    'description' => 'Pendonor hadir di lokasi donor.',
                ],
                [
                    'status' => 'ineligible',
                    'label' => 'Tidak Layak',
                    'description' => 'Hasil pemeriksaan belum memenuhi syarat donor.',
                ],
            ],

            default => [
                [
                    'status' => 'pending',
                    'label' => 'Menunggu Verifikasi',
                    'description' => 'Pendaftaran donor dibuat.',
                ],
                [
                    'status' => 'approved',
                    'label' => 'Disetujui',
                    'description' => 'Pendaftaran disetujui petugas.',
                ],
                [
                    'status' => 'attended',
                    'label' => 'Hadir',
                    'description' => 'Pendonor hadir di lokasi donor.',
                ],
                [
                    'status' => 'eligible',
                    'label' => 'Layak Donor',
                    'description' => 'Hasil pemeriksaan memenuhi syarat donor.',
                ],
                [
                    'status' => 'completed',
                    'label' => 'Donor Selesai',
                    'description' => 'Donor selesai dan kantong darah dibuat.',
                ],
            ],
        };

        $currentIndex = array_search(
            $status,
            array_column($steps, 'status'),
            true
        );

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return collect($steps)
            ->map(function (
                array $step,
                int $index
            ) use (
                $pendaftaran,
                $currentIndex
            ): array {
                $class = 'is-waiting';

                if ($index < $currentIndex) {
                    $class = 'is-done';
                }

                if ($index === $currentIndex) {
                    $class = 'is-active';
                }

                if (
                    in_array(
                        $step['status'],
                        [
                            'rejected',
                            'cancelled',
                            'ineligible',
                        ],
                        true
                    )
                    && $index === $currentIndex
                ) {
                    $class = 'is-danger';
                }

                return [
                    'status' => $step['status'],
                    'label' => $step['label'],
                    'description' => $step['description'],
                    'class' => $class,
                    'tanggal' => $this->tanggalTahap(
                        $pendaftaran,
                        $step['status']
                    ),
                ];
            })
            ->values()
            ->all();
    }

    public function nomorPendaftaran(
        PendaftaranDonor $pendaftaran
    ): string {
        return (string) (
            $pendaftaran->nomor_pendaftaran
            ?? 'REG-' . str_pad(
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
            'absent', 'not_attended' => 'Tidak Hadir',
            default => $value !== '' ? $value : '-',
        };
    }

    public function statusBadgeClass(mixed $status): string
    {
        $value = $this->nilaiEnum($status);

        return match ($value) {
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

    public function judulJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return 'Jadwal tidak tersedia';
        }

        return (string) (
            $jadwal->judul
            ?? $jadwal->nama
            ?? 'Jadwal Donor'
        );
    }

    public function tanggalJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return '-';
        }

        $mulai = $this->tanggalCarbon(
            $jadwal->mulai_pada
                ?? $jadwal->tanggal_mulai
                ?? null
        );

        return $mulai
            ? $mulai->translatedFormat('d F Y')
            : 'Tanggal belum ditentukan';
    }

    public function jamJadwal(?JadwalDonor $jadwal): string
    {
        if ($jadwal === null) {
            return '-';
        }

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

    public function mapsUrl(?LokasiDonor $lokasi): string
    {
        if ($lokasi === null) {
            return 'https://www.google.com/maps';
        }

        if (filled($lokasi->url_google_maps ?? null)) {
            return (string) $lokasi->url_google_maps;
        }

        if (
            filled($lokasi->latitude ?? null)
            && filled($lokasi->longitude ?? null)
        ) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode(
                    $lokasi->latitude . ',' . $lokasi->longitude
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

    public function alasanStatus(
        PendaftaranDonor $pendaftaran
    ): string {
        return (string) (
            $pendaftaran->alasan_penolakan
            ?? $pendaftaran->alasan_pembatalan
            ?? $pendaftaran->pemeriksaanKesehatan?->alasan_tidak_layak
            ?? '-'
        );
    }

    public function tanggalFormat(mixed $value): string
    {
        $tanggal = $this->tanggalCarbon($value);

        return $tanggal
            ? $tanggal->translatedFormat('d F Y H:i')
            : '-';
    }

    private function tanggalTahap(
        PendaftaranDonor $pendaftaran,
        string $status
    ): string {
        $value = match ($status) {
            'pending' => $pendaftaran->created_at,
            'approved' => $pendaftaran->ditinjau_pada,
            'attended' => $pendaftaran->hadir_pada,
            'eligible' => $pendaftaran
                ->pemeriksaanKesehatan
                ?->diperiksa_pada,

            'ineligible' => $pendaftaran
                ->pemeriksaanKesehatan
                ?->diperiksa_pada,

            'completed' => $pendaftaran->selesai_pada,
            'rejected' => $pendaftaran->ditinjau_pada,
            'cancelled' => $pendaftaran->dibatalkan_pada,
            default => null,
        };

        return $this->tanggalFormat($value);
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
}