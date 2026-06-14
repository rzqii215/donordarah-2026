<x-filament-panels::page>
    <style>
        .laporan-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: end;
        }

        .laporan-summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .laporan-content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .laporan-status-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .laporan-card {
            padding: 1.25rem;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            background-color: rgb(255 255 255);
            box-shadow:
                0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .laporan-card-label {
            margin: 0;
            color: rgb(107 114 128);
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
        }

        .laporan-card-value {
            margin: 0.5rem 0 0;
            color: rgb(17 24 39);
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 2.25rem;
            letter-spacing: -0.025em;
        }

        .laporan-card-description {
            margin: 0.5rem 0 0;
            color: rgb(107 114 128);
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .laporan-table-container {
            width: 100%;
            overflow-x: auto;
        }

        .laporan-table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .laporan-table th {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgb(229 231 235);
            background-color: rgb(249 250 251);
            color: rgb(17 24 39);
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
        }

        .laporan-table th.is-number {
            text-align: right;
        }

        .laporan-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgb(229 231 235);
            color: rgb(55 65 81);
            vertical-align: middle;
        }

        .laporan-table td.is-number {
            text-align: right;
            white-space: nowrap;
        }

        .laporan-table td.is-primary {
            color: rgb(17 24 39);
            font-weight: 600;
            white-space: nowrap;
        }

        .laporan-status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.625rem;
        }

        .laporan-status-value {
            color: rgb(17 24 39);
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.75rem;
        }

        .laporan-distribusi-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .laporan-distribusi-item {
            padding: 1rem;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.625rem;
        }

        .laporan-distribusi-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .laporan-distribusi-number {
            margin: 0;
            color: rgb(17 24 39);
            font-weight: 600;
        }

        .laporan-distribusi-hospital {
            margin: 0.25rem 0 0;
            color: rgb(75 85 99);
            font-size: 0.875rem;
        }

        .laporan-distribusi-date {
            margin: 0.25rem 0 0;
            color: rgb(107 114 128);
            font-size: 0.75rem;
        }

        .laporan-empty-state {
            padding: 2rem 1rem;
            border: 1px dashed rgb(209 213 219);
            border-radius: 0.625rem;
            color: rgb(107 114 128);
            font-size: 0.875rem;
            text-align: center;
        }

        .laporan-filter-action {
            display: flex;
            align-items: flex-end;
        }

        .dark .laporan-card,
        .dark .laporan-status-item,
        .dark .laporan-distribusi-item {
            border-color: rgb(255 255 255 / 0.1);
            background-color: rgb(24 24 27);
        }

        .dark .laporan-card-label,
        .dark .laporan-card-description,
        .dark .laporan-distribusi-hospital,
        .dark .laporan-distribusi-date,
        .dark .laporan-empty-state {
            color: rgb(161 161 170);
        }

        .dark .laporan-card-value,
        .dark .laporan-status-value,
        .dark .laporan-distribusi-number {
            color: rgb(255 255 255);
        }

        .dark .laporan-table th {
            border-color: rgb(255 255 255 / 0.1);
            background-color: rgb(255 255 255 / 0.05);
            color: rgb(255 255 255);
        }

        .dark .laporan-table td {
            border-color: rgb(255 255 255 / 0.1);
            color: rgb(212 212 216);
        }

        .dark .laporan-table td.is-primary {
            color: rgb(255 255 255);
        }

        .dark .laporan-empty-state {
            border-color: rgb(255 255 255 / 0.2);
        }

        @media (min-width: 640px) {
            .laporan-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .laporan-status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .laporan-distribusi-content {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
        }

        @media (min-width: 768px) {
            .laporan-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
            }
        }

        @media (min-width: 1280px) {
            .laporan-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .laporan-content-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    @php
        $ringkasan = $this->ringkasan();
        $stokDarah = $this->stokDarah();
        $permintaanPerStatus = $this->permintaanPerStatus();
        $distribusiTerbaru = $this->distribusiTerbaru();
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Filter Periode
        </x-slot>

        <x-slot name="description">
            Pilih periode laporan yang ingin ditampilkan.
        </x-slot>

        <div class="laporan-filter-grid">
            <div>
                <label
                    for="tanggal-mulai"
                    class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                >
                    Tanggal Mulai
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="tanggal-mulai"
                        type="date"
                        wire:model.live="tanggalMulai"
                    />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label
                    for="tanggal-selesai"
                    class="mb-2 block text-sm font-medium text-gray-950 dark:text-white"
                >
                    Tanggal Selesai
                </label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        id="tanggal-selesai"
                        type="date"
                        wire:model.live="tanggalSelesai"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="laporan-filter-action">
                <x-filament::button
                    type="button"
                    color="gray"
                    icon="heroicon-o-arrow-path"
                    wire:click="resetFilter"
                >
                    Atur Ulang
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <div class="laporan-summary-grid">
        @foreach ($ringkasan as $item)
            <article class="laporan-card">
                <p class="laporan-card-label">
                    {{ $item['label'] }}
                </p>

                <p class="laporan-card-value">
                    {{ number_format($item['nilai']) }}
                </p>

                <p class="laporan-card-description">
                    {{ $item['keterangan'] }}
                </p>
            </article>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Stok Darah Saat Ini
        </x-slot>

        <x-slot name="description">
            Stok dihitung langsung dari data kantong darah, bukan angka manual.
        </x-slot>

        <div class="laporan-table-container">
            <table class="laporan-table">
                <thead>
                    <tr>
                        <th>Golongan</th>
                        <th>Rhesus</th>
                        <th class="is-number">Tersedia</th>
                        <th class="is-number">Dialokasikan</th>
                        <th class="is-number">Kedaluwarsa ≤ 7 Hari</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($stokDarah as $stok)
                        <tr>
                            <td class="is-primary">
                                {{ $stok['golongan'] }}
                            </td>

                            <td>
                                {{ $stok['rhesus'] }}
                            </td>

                            <td class="is-number">
                                {{ number_format($stok['tersedia']) }}
                            </td>

                            <td class="is-number">
                                {{ number_format($stok['dialokasikan']) }}
                            </td>

                            <td class="is-number">
                                @if ($stok['mendekati_kedaluwarsa'] > 0)
                                    <x-filament::badge color="warning">
                                        {{ number_format($stok['mendekati_kedaluwarsa']) }}
                                    </x-filament::badge>
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <div class="laporan-content-grid">
        <x-filament::section>
            <x-slot name="heading">
                Permintaan Berdasarkan Status
            </x-slot>

            <x-slot name="description">
                Jumlah permintaan yang dibuat pada periode terpilih.
            </x-slot>

            <div class="laporan-status-grid">
                @foreach ($permintaanPerStatus as $status)
                    <div class="laporan-status-item">
                        <x-filament::badge :color="$status['warna']">
                            {{ $status['label'] }}
                        </x-filament::badge>

                        <span class="laporan-status-value">
                            {{ number_format($status['jumlah']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Distribusi Terbaru
            </x-slot>

            <x-slot name="description">
                Distribusi yang dibuat pada periode terpilih.
            </x-slot>

            <div class="laporan-distribusi-list">
                @forelse ($distribusiTerbaru as $distribusi)
                    <article class="laporan-distribusi-item">
                        <div class="laporan-distribusi-content">
                            <div>
                                <p class="laporan-distribusi-number">
                                    {{ $distribusi->nomor_distribusi }}
                                </p>

                                <p class="laporan-distribusi-hospital">
                                    {{ $distribusi->permintaan?->rumahSakit?->nama_rumah_sakit ?? '-' }}
                                </p>

                                <p class="laporan-distribusi-date">
                                    Dijadwalkan
                                    {{ $distribusi->dijadwalkan_pada?->format('d M Y H:i') ?? '-' }}
                                </p>
                            </div>

                            <x-filament::badge :color="$distribusi->status->warna()">
                                {{ $distribusi->status->label() }}
                            </x-filament::badge>
                        </div>
                    </article>
                @empty
                    <div class="laporan-empty-state">
                        Belum ada distribusi pada periode ini.
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>