<?php

namespace App\Livewire\Donor;

use App\Models\LokasiDonor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.donor')]
class Lokasi extends Component
{
    public string $pencarian = '';

    public ?int $lokasiTerpilihId = null;

    public function pilihLokasi(int $lokasiId): void
    {
        $this->lokasiTerpilihId = $lokasiId;
    }

    public function tutupDetailLokasi(): void
    {
        $this->lokasiTerpilihId = null;
    }

    public function render(): View
    {
        return view('livewire.donor.lokasi', [
            'lokasiDonors' => $this->ambilLokasiDonor(),
            'lokasiTerpilih' => $this->ambilLokasiTerpilih(),
        ]);
    }

    /**
     * @return Collection<int, LokasiDonor>
     */
    private function ambilLokasiDonor(): Collection
    {
        $query = LokasiDonor::query();

        $this->filterStatusAktifJikaAda($query);

        if (filled($this->pencarian)) {
            $this->filterPencarian($query);
        }

        $this->urutkanLokasi($query);

        return $query
            ->limit(50)
            ->get();
    }

    private function ambilLokasiTerpilih(): ?LokasiDonor
    {
        if ($this->lokasiTerpilihId === null) {
            return null;
        }

        return LokasiDonor::query()
            ->find($this->lokasiTerpilihId);
    }

    private function filterStatusAktifJikaAda(Builder $query): void
    {
        if (! $this->kolomAda('status')) {
            return;
        }

        $query->whereIn('status', [
            'active',
            'aktif',
            'published',
            'dipublikasikan',
        ]);
    }

    private function filterPencarian(Builder $query): void
    {
        $columns = collect([
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
                fn (string $column): bool => $this->kolomAda($column)
            )
            ->values();

        if ($columns->isEmpty()) {
            return;
        }

        $keyword = '%' . Str::lower(trim($this->pencarian)) . '%';

        $query->where(function (Builder $query) use (
            $columns,
            $keyword
        ): void {
            foreach ($columns as $column) {
                $query->orWhereRaw(
                    'LOWER(' . $column . ') LIKE ?',
                    [$keyword]
                );
            }
        });
    }

    private function urutkanLokasi(Builder $query): void
    {
        if ($this->kolomAda('nama')) {
            $query->orderBy('nama');

            return;
        }

        if ($this->kolomAda('nama_lokasi')) {
            $query->orderBy('nama_lokasi');

            return;
        }

        $query->latest();
    }

    private function tabelLokasi(): string
    {
        return (new LokasiDonor())->getTable();
    }

    private function kolomAda(string $column): bool
    {
        return Schema::hasColumn(
            $this->tabelLokasi(),
            $column
        );
    }

    public function namaLokasi(LokasiDonor $lokasi): string
    {
        return (string) (
            $lokasi->nama
            ?? $lokasi->nama_lokasi
            ?? 'Lokasi Donor'
        );
    }

    public function alamatLokasi(LokasiDonor $lokasi): string
    {
        return (string) (
            $lokasi->alamat
            ?? $lokasi->alamat_lengkap
            ?? '-'
        );
    }

    public function wilayahLokasi(LokasiDonor $lokasi): string
    {
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

    public function kontakLokasi(LokasiDonor $lokasi): string
    {
        return (string) (
            $lokasi->nomor_telepon
            ?? $lokasi->telepon
            ?? $lokasi->kontak
            ?? '-'
        );
    }

    public function catatanLokasi(LokasiDonor $lokasi): string
    {
        return (string) (
            $lokasi->catatan_lokasi
            ?? '-'
        );
    }

    public function koordinatLokasi(LokasiDonor $lokasi): string
    {
        $latitude = $lokasi->latitude ?? null;
        $longitude = $lokasi->longitude ?? null;

        if (blank($latitude) || blank($longitude)) {
            return '-';
        }

        return $latitude . ', ' . $longitude;
    }

    public function mapsUrl(LokasiDonor $lokasi): string
    {
        if (
            $this->kolomAda('url_google_maps')
            && filled($lokasi->url_google_maps)
        ) {
            return (string) $lokasi->url_google_maps;
        }

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode($this->queryMaps($lokasi));
    }

    public function embedMapsUrl(LokasiDonor $lokasi): string
    {
        return 'https://maps.google.com/maps?q='
            . rawurlencode($this->queryMapsUntukEmbed($lokasi))
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

    private function queryMapsUntukEmbed(LokasiDonor $lokasi): string
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
}