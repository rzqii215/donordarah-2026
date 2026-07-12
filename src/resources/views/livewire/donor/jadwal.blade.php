<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    @error('jadwal')
        <div
            role="alert"
            class="flex items-start gap-3 rounded-2xl border border-[#ffb4ab] bg-[#ffdad6] px-4 py-3.5 text-sm font-medium text-[#93000a]"
        >
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-current text-xs font-bold">
                !
            </span>

            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-[#e6e3df] bg-white p-5 lg:sticky lg:top-[106px] lg:p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold tracking-[-0.025em] text-[#191c20]">
                    Filter Jadwal
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#584141]">
                    Temukan jadwal berdasarkan lokasi dan tanggal.
                </p>
            </div>

            <form
                wire:submit="terapkanFilter"
                class="space-y-5"
            >
                <div>
                    <label
                        for="pencarian-jadwal"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Cari Lokasi/Penyelenggara
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#8c7071]">
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="11"
                                    cy="11"
                                    r="7"
                                ></circle>

                                <path
                                    d="m20 20-3.5-3.5"
                                ></path>
                            </svg>
                        </span>

                        <input
                            id="pencarian-jadwal"
                            type="search"
                            wire:model.live.debounce.300ms="pencarian"
                            placeholder="Contoh: PMI Jakarta"
                            autocomplete="off"
                            class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white py-2 pl-10 pr-4 text-sm text-[#191c20] placeholder:text-[#8c7071] focus:border-[#76001c] focus:ring-[#76001c]/15"
                        >
                    </div>
                </div>

                <div>
                    <label
                        for="filter-kota"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Kota
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#8c7071] [&>svg]:h-5 [&>svg]:w-5">
                            <x-donor.icon name="map-pin" />
                        </span>

                        <select
                            id="filter-kota"
                            wire:model="kota"
                            class="h-12 w-full appearance-none rounded-xl border border-[#e2e2e9] bg-white py-2 pl-10 pr-9 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                        >
                            <option value="">
                                Semua Kota
                            </option>

                            @foreach ($kotaTersedia as $namaKota)
                                <option value="{{ $namaKota }}">
                                    {{ $namaKota }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            viewBox="0 0 20 20"
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8c7071]"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                d="m5 7.5 5 5 5-5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Rentang Tanggal
                    </label>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div>
                            <label
                                for="tanggal-mulai"
                                class="sr-only"
                            >
                                Tanggal mulai
                            </label>

                            <input
                                id="tanggal-mulai"
                                type="date"
                                wire:model="tanggalMulai"
                                class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-3 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                            >

                            @error('tanggalMulai')
                                <p class="mt-1 text-xs text-[#ba1a1a]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="tanggal-selesai"
                                class="sr-only"
                            >
                                Tanggal selesai
                            </label>

                            <input
                                id="tanggal-selesai"
                                type="date"
                                wire:model="tanggalSelesai"
                                class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-3 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                            >

                            @error('tanggalSelesai')
                                <p class="mt-1 text-xs text-[#ba1a1a]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] p-3.5">
                    <input
                        type="checkbox"
                        wire:model.live="hanyaTersedia"
                        class="mt-0.5 h-4 w-4 rounded border-[#e0bfbf] text-[#76001c] focus:ring-[#76001c]/20"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-[#191c20]">
                            Pendaftaran tersedia
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-[#584141]">
                            Sembunyikan jadwal yang belum dibuka, sudah didaftarkan, ditutup, atau kuotanya penuh.
                        </span>
                    </span>
                </label>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="terapkanFilter"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white transition hover:bg-[#991b2f] disabled:cursor-wait disabled:opacity-60"
                    >
                        <span
                            wire:loading.remove
                            wire:target="terapkanFilter"
                        >
                            Terapkan Filter
                        </span>

                        <span
                            wire:loading
                            wire:target="terapkanFilter"
                        >
                            Memproses...
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="resetFilter"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#584141] transition hover:bg-[#f3f3fa] hover:text-[#76001c]"
                    >
                        Reset
                    </button>
                </div>
            </form>
        </aside>

        <section class="min-w-0">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#584141]">
                    Menampilkan

                    <strong class="text-[#191c20]">
                        {{ number_format($jadwalDonors->total()) }}
                    </strong>

                    jadwal tersedia
                </p>

                <div class="flex items-center gap-2">
                    <label
                        for="urutan-jadwal"
                        class="shrink-0 text-sm text-[#584141]"
                    >
                        Urutkan:
                    </label>

                    <select
                        id="urutan-jadwal"
                        wire:model.live="urutan"
                        class="h-11 rounded-xl border border-[#e2e2e9] bg-white py-2 pl-3 pr-9 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                    >
                        <option value="terdekat">
                            Terdekat
                        </option>

                        <option value="kuota_terbesar">
                            Kuota Terbesar
                        </option>

                        <option value="terbaru">
                            Baru Ditambahkan
                        </option>
                    </select>
                </div>
            </div>

            <div
                wire:loading.delay
                wire:target="pencarian,kota,tanggalMulai,tanggalSelesai,urutan,hanyaTersedia,terapkanFilter,resetFilter"
                class="mb-4 rounded-xl border border-[#e0bfbf] bg-[#fff7f7] px-4 py-3 text-sm font-medium text-[#76001c]"
            >
                Memuat jadwal donor...
            </div>

            @if ($jadwalDonors->isEmpty())
                <div class="flex min-h-[430px] flex-col items-center justify-center rounded-2xl border border-dashed border-[#e0bfbf] bg-white px-6 py-14 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-8 [&>svg]:w-8">
                        <x-donor.icon name="calendar" />
                    </span>

                    <h2 class="mt-5 text-xl font-semibold text-[#191c20]">
                        Jadwal donor tidak ditemukan
                    </h2>

                    <p class="mt-2 max-w-md text-sm leading-6 text-[#584141]">
                        Belum ada jadwal yang cocok dengan filter Anda. Coba ubah kata pencarian, kota, atau rentang tanggal.
                    </p>

                    <button
                        type="button"
                        wire:click="resetFilter"
                        class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                    >
                        Reset filter
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                    @foreach ($jadwalDonors as $jadwal)
                        @php
                            $lokasi = $jadwal->lokasi;

                            $pendaftaran =
                                $pendaftaranJadwals->get(
                                    $jadwal->id
                                );

                            $statusJadwal =
                                $this->statusKetersediaanJadwal(
                                    $jadwal
                                );

                            $statusKey =
                                data_get(
                                    $statusJadwal,
                                    'key',
                                    'closed'
                                );

                            $statusLabel =
                                data_get(
                                    $statusJadwal,
                                    'label',
                                    'Tidak Tersedia'
                                );

                            $kuotaTotal =
                                $this->kuotaTotal(
                                    $jadwal
                                );

                            $sisaKuota =
                                $this->sisaKuota(
                                    $jadwal
                                );

                            $kuotaTerisi =
                                $this->persentaseKuotaTerisi(
                                    $jadwal
                                );

                            $kuotaRendah =
                                $kuotaTotal > 0
                                && $sisaKuota > 0
                                && $sisaKuota <= max(
                                    5,
                                    (int) ceil(
                                        $kuotaTotal * 0.2
                                    )
                                );

                            $pendaftaranTone =
                                $pendaftaran
                                    ? $this->statusPendaftaranTone(
                                        $pendaftaran->status
                                    )
                                    : null;
                        @endphp

                        <article
                            wire:key="jadwal-donor-{{ $jadwal->id }}"
                            class="flex min-h-[390px] flex-col rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.06)] sm:p-6"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    @if ($pendaftaran)
                                        <span
                                            @class([
                                                'inline-flex rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.05em]',
                                                'bg-[#eaf7f0] text-[#257a57]' => $pendaftaranTone === 'success',
                                                'bg-[#ffdad6] text-[#93000a]' => $pendaftaranTone === 'danger',
                                                'bg-[#fff4de] text-[#b86e12]' => $pendaftaranTone === 'warning',
                                            ])
                                        >
                                            {{ $this->labelStatusPendaftaran($pendaftaran->status) }}
                                        </span>
                                    @else
                                        <span
                                            @class([
                                                'inline-flex rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.05em]',
                                                'bg-[#eaf7f0] text-[#257a57]' => $statusKey === 'open',
                                                'bg-[#fff4de] text-[#b86e12]' => $statusKey === 'soon',
                                                'bg-[#ffdad6] text-[#93000a]' => in_array($statusKey, ['closed', 'full', 'finished'], true),
                                            ])
                                        >
                                            {{ $statusLabel }}
                                        </span>
                                    @endif

                                    <h2 class="mt-3 text-xl font-semibold leading-7 tracking-[-0.03em] text-[#191c20]">
                                        {{ $this->judulJadwal($jadwal) }}
                                    </h2>

                                    <p class="mt-1 flex items-start gap-2 text-sm text-[#584141]">
                                        <span class="mt-0.5 shrink-0 [&>svg]:h-4 [&>svg]:w-4">
                                            <x-donor.icon name="map-pin" />
                                        </span>

                                        <span>
                                            {{ $this->namaLokasi($lokasi) }}
                                        </span>
                                    </p>
                                </div>

                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#e2e2e9] bg-[#f3f3fa] text-[#76001c] [&>svg]:h-6 [&>svg]:w-6">
                                    <x-donor.icon name="droplet" />
                                </span>
                            </div>

                            <div class="my-5 space-y-4 border-y border-[#e2e2e9] py-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f3f3fa] text-[#584141] [&>svg]:h-4 [&>svg]:w-4">
                                        <x-donor.icon name="calendar" />
                                    </span>

                                    <div>
                                        <p class="text-sm font-semibold text-[#191c20]">
                                            {{ $this->tanggalJadwal($jadwal) }}
                                        </p>

                                        <p class="mt-0.5 text-sm text-[#584141]">
                                            {{ $this->jamJadwal($jadwal) }}
                                            WIB
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f3f3fa] text-[#584141] [&>svg]:h-4 [&>svg]:w-4">
                                        <x-donor.icon name="map-pin" />
                                    </span>

                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-[#191c20]">
                                            {{ $this->namaLokasi($lokasi) }}
                                        </p>

                                        <p class="mt-0.5 line-clamp-2 text-sm leading-5 text-[#584141]">
                                            {{ $this->alamatLokasi($lokasi) }}
                                        </p>

                                        @if ($this->wilayahLokasi($lokasi) !== '-')
                                            <p class="mt-0.5 text-xs text-[#8c7071]">
                                                {{ $this->wilayahLokasi($lokasi) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto flex items-end justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-[#584141]">
                                        Kuota Tersedia
                                    </p>

                                    @if ($kuotaTotal > 0)
                                        <p class="mt-1 flex items-baseline gap-1">
                                            <strong
                                                @class([
                                                    'text-xl font-bold',
                                                    'text-[#b86e12]' => $kuotaRendah,
                                                    'text-[#76001c]' => ! $kuotaRendah,
                                                ])
                                            >
                                                {{ $sisaKuota }}
                                            </strong>

                                            <span class="text-sm text-[#584141]">
                                                / {{ $kuotaTotal }}
                                            </span>
                                        </p>

                                        <div class="mt-2 h-1.5 w-28 overflow-hidden rounded-full bg-[#e2e2e9]">
                                            <div
                                                @class([
                                                    'h-full rounded-full',
                                                    'bg-[#c47a0b]' => $kuotaRendah,
                                                    'bg-[#76001c]' => ! $kuotaRendah,
                                                ])
                                                style="width: {{ $kuotaTerisi }}%;"
                                            ></div>
                                        </div>
                                    @else
                                        <p class="mt-1 text-sm font-semibold text-[#584141]">
                                            Menyesuaikan lokasi
                                        </p>
                                    @endif
                                </div>

                                <button
                                    type="button"
                                    wire:click="pilihJadwal({{ $jadwal->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="pilihJadwal({{ $jadwal->id }})"
                                    class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-4 text-sm font-semibold text-[#24262b] transition hover:bg-[#f3f3fa] disabled:cursor-wait disabled:opacity-60"
                                >
                                    Lihat Jadwal
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($jadwalDonors->hasPages())
                    <div class="mt-6 rounded-2xl border border-[#e6e3df] bg-white px-4 py-3">
                        {{ $jadwalDonors->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>

    @if ($jadwalTerpilih !== null)
        @php
            $lokasiTerpilih =
                $jadwalTerpilih->lokasi;

            $statusTerpilih =
                $this->statusKetersediaanJadwal(
                    $jadwalTerpilih
                );

            $statusTerpilihKey =
                data_get(
                    $statusTerpilih,
                    'key',
                    'closed'
                );

            $statusTerpilihLabel =
                data_get(
                    $statusTerpilih,
                    'label',
                    'Tidak Tersedia'
                );

            $dapatDidaftar =
                $pendaftaranTerpilih === null
                && $this->jadwalDapatDidaftar(
                    $jadwalTerpilih,
                    false
                );

            $catatanLokasi =
                $this->catatanLokasi(
                    $lokasiTerpilih
                );

            $kuotaTotalTerpilih =
                $this->kuotaTotal(
                    $jadwalTerpilih
                );

            $sisaKuotaTerpilih =
                $this->sisaKuota(
                    $jadwalTerpilih
                );

            $tonePendaftaranTerpilih =
                $pendaftaranTerpilih
                    ? $this->statusPendaftaranTone(
                        $pendaftaranTerpilih->status
                    )
                    : null;
        @endphp

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-detail-jadwal"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-[#191c20]/50 p-3 backdrop-blur-sm sm:p-6"
            wire:click="tutupDetailJadwal"
            x-on:keydown.escape.window="$wire.tutupDetailJadwal()"
        >
            <section
                class="max-h-[calc(100vh-1.5rem)] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-[0_32px_90px_rgba(25,28,32,0.3)] sm:max-h-[calc(100vh-3rem)]"
                wire:click.stop
            >
                <header class="flex items-start justify-between gap-4 border-b border-[#e2e2e9] px-5 py-5 sm:px-7">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-[#991b2f]">
                            Detail Jadwal Donor
                        </p>

                        <h2
                            id="judul-detail-jadwal"
                            class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-[#191c20]"
                        >
                            {{ $this->judulJadwal($jadwalTerpilih) }}
                        </h2>

                        <span
                            @class([
                                'mt-3 inline-flex rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.05em]',
                                'bg-[#eaf7f0] text-[#257a57]' => $statusTerpilihKey === 'open',
                                'bg-[#fff4de] text-[#b86e12]' => $statusTerpilihKey === 'soon',
                                'bg-[#ffdad6] text-[#93000a]' => in_array($statusTerpilihKey, ['closed', 'full', 'finished'], true),
                            ])
                        >
                            {{ $statusTerpilihLabel }}
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="tutupDetailJadwal"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f3f3fa] text-2xl leading-none text-[#584141] transition hover:bg-[#e2e2e9] hover:text-[#76001c]"
                        aria-label="Tutup detail jadwal"
                    >
                        ×
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="min-h-[280px] bg-[#ededf4] lg:min-h-full">
                        <iframe
                            src="{{ $this->embedMapsUrl($lokasiTerpilih) }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Peta {{ $this->namaLokasi($lokasiTerpilih) }}"
                            class="h-full min-h-[280px] w-full border-0 lg:min-h-[520px]"
                        ></iframe>
                    </div>

                    <div class="space-y-5 p-5 sm:p-7">
                        @error('jadwal')
                            <div class="rounded-xl border border-[#ffb4ab] bg-[#ffdad6] px-4 py-3 text-sm font-medium text-[#93000a]">
                                {{ $message }}
                            </div>
                        @enderror

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#8c7071]">
                                Deskripsi
                            </p>

                            <p class="mt-2 text-sm leading-6 text-[#584141]">
                                {{ $this->deskripsiJadwal($jadwalTerpilih) }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <article class="rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                    Jadwal
                                </p>

                                <p class="mt-2 text-sm font-semibold leading-6 text-[#191c20]">
                                    {{ $this->tanggalJadwal($jadwalTerpilih) }}
                                </p>

                                <p class="text-sm text-[#584141]">
                                    {{ $this->jamJadwal($jadwalTerpilih) }}
                                    WIB
                                </p>
                            </article>

                            <article class="rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                    Kuota
                                </p>

                                <p class="mt-2 text-sm font-semibold text-[#191c20]">
                                    @if ($kuotaTotalTerpilih > 0)
                                        {{ $sisaKuotaTerpilih }}
                                        dari
                                        {{ $kuotaTotalTerpilih }}
                                        tersedia
                                    @else
                                        Menyesuaikan lokasi
                                    @endif
                                </p>
                            </article>
                        </div>

                        <div class="rounded-xl border border-[#e0bfbf] bg-[#fff7f7] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#991b2f]">
                                Lokasi
                            </p>

                            <h3 class="mt-2 text-sm font-semibold text-[#191c20]">
                                {{ $this->namaLokasi($lokasiTerpilih) }}
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-[#584141]">
                                {{ $this->alamatLokasi($lokasiTerpilih) }}
                            </p>

                            @if ($this->wilayahLokasi($lokasiTerpilih) !== '-')
                                <p class="mt-1 text-xs text-[#8c7071]">
                                    {{ $this->wilayahLokasi($lokasiTerpilih) }}
                                </p>
                            @endif

                            @if ($this->kontakLokasi($lokasiTerpilih) !== '-')
                                <p class="mt-3 text-xs font-semibold text-[#584141]">
                                    Kontak:
                                    {{ $this->kontakLokasi($lokasiTerpilih) }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                Periode Pendaftaran
                            </p>

                            <p class="mt-2 text-sm font-semibold leading-6 text-[#191c20]">
                                {{ $this->periodePendaftaran($jadwalTerpilih) }}
                            </p>
                        </div>

                        @if ($catatanLokasi !== '-')
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                    Catatan Lokasi
                                </p>

                                <p class="mt-2 text-sm leading-6 text-[#584141]">
                                    {{ $catatanLokasi }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <footer class="flex flex-col-reverse gap-3 border-t border-[#e2e2e9] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a
                            href="{{ $this->mapsUrl($lokasiTerpilih) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#24262b] transition hover:bg-[#f3f3fa]"
                        >
                            Buka Google Maps
                        </a>

                        <button
                            type="button"
                            wire:click="tutupDetailJadwal"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#584141] transition hover:bg-[#f3f3fa]"
                        >
                            Tutup
                        </button>
                    </div>

                    @if ($pendaftaranTerpilih)
                        <span
                            @class([
                                'inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold',
                                'bg-[#eaf7f0] text-[#257a57]' => $tonePendaftaranTerpilih === 'success',
                                'bg-[#ffdad6] text-[#93000a]' => $tonePendaftaranTerpilih === 'danger',
                                'bg-[#fff4de] text-[#b86e12]' => $tonePendaftaranTerpilih === 'warning',
                            ])
                        >
                            {{ $this->labelStatusPendaftaran($pendaftaranTerpilih->status) }}
                        </span>
                    @elseif ($dapatDidaftar)
                        <button
                            type="button"
                            wire:click="daftarJadwal({{ $jadwalTerpilih->id }})"
                            wire:loading.attr="disabled"
                            wire:target="daftarJadwal({{ $jadwalTerpilih->id }})"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-6 text-sm font-semibold text-white transition hover:bg-[#991b2f] disabled:cursor-wait disabled:opacity-60"
                        >
                            <span
                                wire:loading.remove
                                wire:target="daftarJadwal({{ $jadwalTerpilih->id }})"
                            >
                                Daftar Donor
                            </span>

                            <span
                                wire:loading
                                wire:target="daftarJadwal({{ $jadwalTerpilih->id }})"
                            >
                                Memproses...
                            </span>
                        </button>
                    @else
                        <span class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#ededf4] px-5 text-sm font-semibold text-[#584141]">
                            {{ $statusTerpilihLabel }}
                        </span>
                    @endif
                </footer>
            </section>
        </div>
    @endif
</div>