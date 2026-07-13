<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    @error('lokasi')
        <div class="rounded-2xl border border-[#ffb4ab] bg-[#ffdad6] px-4 py-3 text-sm font-medium text-[#93000a]">
            {{ $message }}
        </div>
    @enderror

    {{-- Filter --}}
    <section class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_220px_220px]">
            <div>
                <label
                    for="pencarian-lokasi"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Cari Lokasi
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#8c7071]">
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
                        id="pencarian-lokasi"
                        type="search"
                        wire:model.live.debounce.300ms="pencarian"
                        placeholder="Cari nama, alamat, kota, atau kontak..."
                        autocomplete="off"
                        class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white py-2 pl-12 pr-4 text-sm text-[#191c20] placeholder:text-[#8c7071] focus:border-[#76001c] focus:ring-[#76001c]/15"
                    >
                </div>
            </div>

            <div>
                <label
                    for="filter-kota-lokasi"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Kota
                </label>

                <select
                    id="filter-kota-lokasi"
                    wire:model.live="kota"
                    class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-3 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
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
            </div>

            <div>
                <label
                    for="urutan-lokasi"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Urutkan
                </label>

                <select
                    id="urutan-lokasi"
                    wire:model.live="urutan"
                    class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-3 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                >
                    <option value="nama">
                        Nama Lokasi
                    </option>

                    <option value="jadwal_terdekat">
                        Jadwal Terdekat
                    </option>

                    <option value="terbaru">
                        Baru Ditambahkan
                    </option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 border-t border-[#e2e2e9] pt-4 sm:flex-row sm:items-center sm:justify-between">
            <label class="flex cursor-pointer items-start gap-3">
                <input
                    type="checkbox"
                    wire:model.live="hanyaDenganJadwal"
                    class="mt-0.5 h-5 w-5 rounded border-[#e0bfbf] text-[#76001c] focus:ring-[#76001c]/20"
                >

                <span>
                    <span class="block text-sm font-semibold text-[#191c20]">
                        Hanya lokasi dengan jadwal aktif
                    </span>

                    <span class="mt-0.5 block text-xs text-[#584141]">
                        Menampilkan lokasi yang memiliki jadwal donor mendatang.
                    </span>
                </span>
            </label>

            @if (
                filled($pencarian)
                || filled($kota)
                || $hanyaDenganJadwal
                || $urutan !== 'nama'
            )
                <button
                    type="button"
                    wire:click="resetFilter"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-4 text-sm font-semibold text-[#76001c]"
                >
                    Reset Filter
                </button>
            @endif
        </div>
    </section>

    <div class="flex items-center justify-between gap-4">
        <p class="text-sm text-[#584141]">
            Menampilkan

            <strong class="text-[#191c20]">
                {{ number_format($lokasiDonors->total()) }}
            </strong>

            lokasi aktif
        </p>
    </div>

    <div
        wire:loading.delay
        wire:target="pencarian,kota,hanyaDenganJadwal,urutan,resetFilter"
        class="rounded-xl border border-[#e0bfbf] bg-[#fff7f7] px-4 py-3 text-sm font-medium text-[#76001c]"
    >
        Memuat lokasi donor...
    </div>

    @if ($lokasiDonors->isEmpty())
        <section class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-dashed border-[#e0bfbf] bg-white px-6 py-14 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-8 [&>svg]:w-8">
                <x-donor.icon name="map-pin" />
            </span>

            <h2 class="mt-5 text-xl font-semibold text-[#191c20]">
                Lokasi tidak ditemukan
            </h2>

            <p class="mt-2 max-w-md text-sm leading-6 text-[#584141]">
                Belum ada lokasi yang sesuai dengan pencarian atau filter yang dipilih.
            </p>

            <button
                type="button"
                wire:click="resetFilter"
                class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#76001c]"
            >
                Reset filter
            </button>
        </section>
    @else
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach ($lokasiDonors as $lokasi)
                @php
                    $jadwalTerdekat =
                        $this->jadwalTerdekat(
                            $lokasi
                        );

                    $jumlahJadwal =
                        $this->jumlahJadwalAktif(
                            $lokasi
                        );

                    $teleponUrl =
                        $this->teleponUrl(
                            $lokasi
                        );
                @endphp

                <article
                    wire:key="lokasi-{{ $lokasi->id }}"
                    class="overflow-hidden rounded-2xl border border-[#e6e3df] bg-white transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.06)]"
                >
                    <div class="relative h-52 overflow-hidden bg-[#ededf4]">
                        <iframe
                            src="{{ $this->embedMapsUrl($lokasi) }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Peta {{ $this->namaLokasi($lokasi) }}"
                            class="h-full w-full border-0"
                        ></iframe>

                        <span class="pointer-events-none absolute left-4 top-4 inline-flex rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-[#76001c] shadow-sm backdrop-blur">
                            {{ $jumlahJadwal }}
                            jadwal aktif
                        </span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <h2 class="text-xl font-semibold tracking-[-0.03em] text-[#191c20]">
                            {{ $this->namaLokasi($lokasi) }}
                        </h2>

                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-[#584141]">
                            {{ $this->alamatLengkap($lokasi) }}
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <article class="rounded-xl bg-[#f3f3fa] p-3.5">
                                <p class="text-xs font-semibold text-[#8c7071]">
                                    Wilayah
                                </p>

                                <p class="mt-1 text-sm font-semibold text-[#191c20]">
                                    {{ $this->wilayahLokasi($lokasi) }}
                                </p>
                            </article>

                            <article class="rounded-xl bg-[#f3f3fa] p-3.5">
                                <p class="text-xs font-semibold text-[#8c7071]">
                                    Kontak
                                </p>

                                @if ($teleponUrl)
                                    <a
                                        href="{{ $teleponUrl }}"
                                        class="mt-1 block text-sm font-semibold text-[#76001c] hover:underline"
                                    >
                                        {{ $this->kontakLokasi($lokasi) }}
                                    </a>
                                @else
                                    <p class="mt-1 text-sm font-semibold text-[#191c20]">
                                        -
                                    </p>
                                @endif
                            </article>
                        </div>

                        @if ($jadwalTerdekat)
                            <div class="mt-4 rounded-xl border border-[#e0bfbf] bg-[#fff7f7] p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#991b2f]">
                                    Jadwal Terdekat
                                </p>

                                <p class="mt-2 text-sm font-semibold text-[#191c20]">
                                    {{ $this->judulJadwal($jadwalTerdekat) }}
                                </p>

                                <p class="mt-1 text-sm text-[#584141]">
                                    {{ $this->tanggalJadwal($jadwalTerdekat) }}
                                </p>

                                <p class="text-sm text-[#584141]">
                                    {{ $this->jamJadwal($jadwalTerdekat) }}
                                    WIB
                                </p>
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] p-4 text-sm text-[#584141]">
                                Belum ada jadwal donor mendatang di lokasi ini.
                            </div>
                        @endif

                        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                            <button
                                type="button"
                                wire:click="pilihLokasi({{ $lokasi->id }})"
                                class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white transition hover:bg-[#991b2f]"
                            >
                                Lihat Detail
                            </button>

                            <a
                                href="{{ $this->mapsUrl($lokasi) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#24262b] transition hover:bg-[#f3f3fa]"
                            >
                                Google Maps
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($lokasiDonors->hasPages())
            <div class="rounded-2xl border border-[#e6e3df] bg-white px-4 py-3">
                {{ $lokasiDonors->links() }}
            </div>
        @endif
    @endif

    {{-- Detail lokasi --}}
    @if ($lokasiTerpilih !== null)
        @php
            $teleponUrlTerpilih =
                $this->teleponUrl(
                    $lokasiTerpilih
                );

            $jadwalAktifTerpilih =
                $lokasiTerpilih
                    ->jadwalDonors;

            $catatanTerpilih =
                $this->catatanLokasi(
                    $lokasiTerpilih
                );
        @endphp

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-detail-lokasi"
            wire:click="tutupDetailLokasi"
            x-on:keydown.escape.window="$wire.tutupDetailLokasi()"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-[#191c20]/50 p-3 backdrop-blur-sm sm:p-6"
        >
            <section
                wire:click.stop
                class="max-h-[calc(100vh-1.5rem)] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white shadow-[0_32px_90px_rgba(25,28,32,0.3)] sm:max-h-[calc(100vh-3rem)]"
            >
                <header class="flex items-start justify-between gap-4 border-b border-[#e2e2e9] px-5 py-5 sm:px-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#991b2f]">
                            Detail Lokasi Donor
                        </p>

                        <h2
                            id="judul-detail-lokasi"
                            class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-[#191c20]"
                        >
                            {{ $this->namaLokasi($lokasiTerpilih) }}
                        </h2>

                        <p class="mt-2 text-sm text-[#584141]">
                            {{ $this->wilayahLokasi($lokasiTerpilih) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="tutupDetailLokasi"
                        aria-label="Tutup detail lokasi"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f3f3fa] text-2xl text-[#584141]"
                    >
                        ×
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="min-h-[320px] bg-[#ededf4]">
                        <iframe
                            src="{{ $this->embedMapsUrl($lokasiTerpilih) }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Peta {{ $this->namaLokasi($lokasiTerpilih) }}"
                            class="h-full min-h-[320px] w-full border-0 lg:min-h-[600px]"
                        ></iframe>
                    </div>

                    <div class="space-y-5 p-5 sm:p-7">
                        <section>
                            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                Alamat Lengkap
                            </p>

                            <p class="mt-2 text-sm leading-6 text-[#191c20]">
                                {{ $this->alamatLengkap($lokasiTerpilih) }}
                            </p>
                        </section>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <article class="rounded-xl bg-[#f3f3fa] p-4">
                                <p class="text-xs font-semibold text-[#8c7071]">
                                    Kontak
                                </p>

                                <p class="mt-2 text-sm font-semibold text-[#191c20]">
                                    {{ $this->namaKontakLokasi($lokasiTerpilih) }}
                                </p>

                                @if ($teleponUrlTerpilih)
                                    <a
                                        href="{{ $teleponUrlTerpilih }}"
                                        class="mt-1 block text-sm text-[#76001c] hover:underline"
                                    >
                                        {{ $this->kontakLokasi($lokasiTerpilih) }}
                                    </a>
                                @endif
                            </article>

                            <article class="rounded-xl bg-[#f3f3fa] p-4">
                                <p class="text-xs font-semibold text-[#8c7071]">
                                    Koordinat
                                </p>

                                <p class="mt-2 break-all text-sm font-semibold text-[#191c20]">
                                    {{ $this->koordinatLokasi($lokasiTerpilih) }}
                                </p>
                            </article>
                        </div>

                        <section>
                            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                Deskripsi
                            </p>

                            <p class="mt-2 text-sm leading-6 text-[#584141]">
                                {{ $this->deskripsiLokasi($lokasiTerpilih) }}
                            </p>
                        </section>

                        @if ($catatanTerpilih !== '-')
                            <section class="rounded-xl border border-[#f2c879] bg-[#fff4de] p-4">
                                <p class="text-xs font-bold uppercase text-[#8a4f00]">
                                    Catatan Lokasi
                                </p>

                                <p class="mt-2 text-sm leading-6 text-[#8a4f00]">
                                    {{ $catatanTerpilih }}
                                </p>
                            </section>
                        @endif

                        <section>
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-base font-semibold text-[#191c20]">
                                    Jadwal Mendatang
                                </h3>

                                <span class="text-xs font-semibold text-[#8c7071]">
                                    {{ $jadwalAktifTerpilih->count() }}
                                    jadwal
                                </span>
                            </div>

                            @if ($jadwalAktifTerpilih->isEmpty())
                                <p class="mt-3 rounded-xl bg-[#f3f3fa] p-4 text-sm text-[#584141]">
                                    Belum ada jadwal donor mendatang.
                                </p>
                            @else
                                <div class="mt-3 space-y-3">
                                    @foreach ($jadwalAktifTerpilih as $jadwal)
                                        <article class="rounded-xl border border-[#e2e2e9] p-4">
                                            <p class="text-sm font-semibold text-[#191c20]">
                                                {{ $this->judulJadwal($jadwal) }}
                                            </p>

                                            <p class="mt-1 text-xs text-[#584141]">
                                                {{ $this->tanggalJadwal($jadwal) }}
                                            </p>

                                            <p class="text-xs text-[#584141]">
                                                {{ $this->jamJadwal($jadwal) }}
                                                WIB
                                            </p>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>
                </div>

                <footer class="flex flex-col-reverse gap-3 border-t border-[#e2e2e9] px-5 py-5 sm:flex-row sm:justify-end sm:px-7">
                    <button
                        type="button"
                        wire:click="tutupDetailLokasi"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#584141]"
                    >
                        Tutup
                    </button>

                    <a
                        href="{{ route('donor.jadwal', ['cari' => $this->namaLokasi($lokasiTerpilih)]) }}"
                        wire:navigate
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e0bfbf] px-5 text-sm font-semibold text-[#76001c]"
                    >
                        Lihat Jadwal
                    </a>

                    <a
                        href="{{ $this->mapsUrl($lokasiTerpilih) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white"
                    >
                        Buka Google Maps
                    </a>
                </footer>
            </section>
        </div>
    @endif
</div>