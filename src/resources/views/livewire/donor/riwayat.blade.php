<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    @error('riwayat')
        <div class="rounded-2xl border border-[#ffb4ab] bg-[#ffdad6] px-4 py-3 text-sm font-medium text-[#93000a]">
            {{ $message }}
        </div>
    @enderror

    {{-- Search --}}
    <section class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
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
                type="search"
                wire:model.live.debounce.300ms="pencarian"
                placeholder="Cari nomor registrasi, jadwal, lokasi, atau kota..."
                autocomplete="off"
                class="h-13 w-full rounded-xl border border-[#e2e2e9] bg-white py-3 pl-12 pr-4 text-sm text-[#191c20] placeholder:text-[#8c7071] focus:border-[#76001c] focus:ring-[#76001c]/15"
            >
        </div>
    </section>

    {{-- Ringkasan --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Total
            </p>

            <strong class="mt-2 block text-3xl font-bold text-[#191c20]">
                {{ number_format($ringkasan['total']) }}
            </strong>

            <p class="mt-1 text-xs text-[#584141]">
                Semua pendaftaran
            </p>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Diproses
            </p>

            <strong class="mt-2 block text-3xl font-bold text-[#b86e12]">
                {{ number_format($ringkasan['proses']) }}
            </strong>

            <p class="mt-1 text-xs text-[#584141]">
                Masih berjalan
            </p>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Selesai
            </p>

            <strong class="mt-2 block text-3xl font-bold text-[#257a57]">
                {{ number_format($ringkasan['selesai']) }}
            </strong>

            <p class="mt-1 text-xs text-[#584141]">
                Donor diselesaikan
            </p>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Tidak Lanjut
            </p>

            <strong class="mt-2 block text-3xl font-bold text-[#ba1a1a]">
                {{ number_format($ringkasan['bermasalah']) }}
            </strong>

            <p class="mt-1 text-xs text-[#584141]">
                Ditolak, batal, atau tidak layak
            </p>
        </article>
    </section>

    {{-- Filter --}}
    <section class="flex gap-2 overflow-x-auto pb-1">
        @foreach ($this->opsiFilterStatus() as $opsi)
            <button
                type="button"
                wire:click="$set('filterStatus', '{{ $opsi['value'] }}')"
                @class([
                    'inline-flex min-h-11 shrink-0 items-center justify-center rounded-full border px-5 text-sm font-semibold transition',
                    'border-[#76001c] bg-[#76001c] text-white' => $filterStatus === $opsi['value'],
                    'border-[#e2e2e9] bg-white text-[#584141] hover:bg-[#f3f3fa] hover:text-[#76001c]' => $filterStatus !== $opsi['value'],
                ])
            >
                {{ $opsi['label'] }}
            </button>
        @endforeach

        @if (
            filled($pencarian)
            || $filterStatus !== 'semua'
        )
            <button
                type="button"
                wire:click="resetFilter"
                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-full border border-[#e2e2e9] bg-white px-5 text-sm font-semibold text-[#76001c]"
            >
                Reset
            </button>
        @endif
    </section>

    <div
        wire:loading.delay
        wire:target="pencarian,filterStatus,resetFilter"
        class="rounded-xl border border-[#e0bfbf] bg-[#fff7f7] px-4 py-3 text-sm font-medium text-[#76001c]"
    >
        Memuat riwayat donor...
    </div>

    @if ($riwayatDonors->isEmpty())
        <section class="flex min-h-[420px] flex-col items-center justify-center rounded-2xl border border-dashed border-[#e0bfbf] bg-white px-6 py-14 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-8 [&>svg]:w-8">
                <x-donor.icon name="history" />
            </span>

            <h2 class="mt-5 text-xl font-semibold text-[#191c20]">
                Riwayat tidak ditemukan
            </h2>

            <p class="mt-2 max-w-md text-sm leading-6 text-[#584141]">
                Belum ada riwayat yang sesuai dengan pencarian atau filter yang dipilih.
            </p>

            @if (
                filled($pencarian)
                || $filterStatus !== 'semua'
            )
                <button
                    type="button"
                    wire:click="resetFilter"
                    class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#76001c]"
                >
                    Reset filter
                </button>
            @else
                <a
                    href="{{ route('donor.jadwal') }}"
                    wire:navigate
                    class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white"
                >
                    Lihat jadwal donor
                </a>
            @endif
        </section>
    @else
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach ($riwayatDonors as $pendaftaran)
                @php
                    $jadwal =
                        $pendaftaran->jadwal;

                    $lokasi =
                        $jadwal?->lokasi;

                    $tone =
                        $this->statusTone(
                            $pendaftaran->status
                        );
                @endphp

                <article
                    wire:key="riwayat-{{ $pendaftaran->id }}"
                    class="flex flex-col rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.06)] sm:p-6"
                >
                    <header class="flex items-start justify-between gap-4 border-b border-[#e2e2e9] pb-4">
                        <p class="break-all text-sm font-medium text-[#584141]">
                            {{ $this->nomorPendaftaran($pendaftaran) }}
                        </p>

                        <span
                            @class([
                                'inline-flex shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.05em]',
                                'bg-[#eaf7f0] text-[#257a57]' => $tone === 'success',
                                'bg-[#ffdad6] text-[#93000a]' => $tone === 'danger',
                                'bg-[#fff4de] text-[#b86e12]' => $tone === 'warning',
                            ])
                        >
                            {{ $this->labelStatusPendaftaran($pendaftaran->status) }}
                        </span>
                    </header>

                    <div class="space-y-4 py-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-5 [&>svg]:w-5">
                                <x-donor.icon name="calendar" />
                            </span>

                            <div>
                                <h2 class="text-base font-semibold leading-6 text-[#191c20]">
                                    {{ $this->judulJadwal($jadwal) }}
                                </h2>

                                <p class="mt-1 text-sm font-medium text-[#191c20]">
                                    {{ $this->tanggalJadwal($jadwal) }}
                                </p>

                                <p class="text-sm text-[#584141]">
                                    {{ $this->jamJadwal($jadwal) }}
                                    WIB
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-5 [&>svg]:w-5">
                                <x-donor.icon name="map-pin" />
                            </span>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#191c20]">
                                    {{ $this->namaLokasi($lokasi) }}
                                </p>

                                <p class="mt-1 line-clamp-2 text-sm leading-5 text-[#584141]">
                                    {{ $this->alamatLokasi($lokasi) }}
                                </p>

                                @if ($this->wilayahLokasi($lokasi) !== '-')
                                    <p class="mt-1 text-xs text-[#8c7071]">
                                        {{ $this->wilayahLokasi($lokasi) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (
                        in_array(
                            $this->nilaiEnumForView($pendaftaran->status),
                            [
                                'rejected',
                                'cancelled',
                                'ineligible',
                                'no_show',
                            ],
                            true
                        )
                    )
                        <div class="mb-4 rounded-xl border border-[#ffb4ab] bg-[#fff5f4] px-4 py-3 text-xs leading-5 text-[#93000a]">
                            {{ $this->alasanStatus($pendaftaran) }}
                        </div>
                    @endif

                    <footer class="mt-auto flex flex-col gap-2 border-t border-[#e2e2e9] pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            type="button"
                            wire:click="pilihRiwayat({{ $pendaftaran->id }})"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                        >
                            Lihat Detail
                        </button>

                        @if ($pendaftaran->dapatDibatalkan())
                            <button
                                type="button"
                                wire:click="bukaPembatalan({{ $pendaftaran->id }})"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e0bfbf] bg-white px-5 text-sm font-semibold text-[#991b2f] transition hover:bg-[#fff1f2]"
                            >
                                Batalkan
                            </button>
                        @endif
                    </footer>
                </article>
            @endforeach
        </section>

        @if ($riwayatDonors->hasPages())
            <div class="rounded-2xl border border-[#e6e3df] bg-white px-4 py-3">
                {{ $riwayatDonors->links() }}
            </div>
        @endif
    @endif

    {{-- Detail riwayat --}}
    @if ($pendaftaranTerpilih !== null)
        @php
            $jadwalTerpilih =
                $pendaftaranTerpilih->jadwal;

            $lokasiTerpilih =
                $jadwalTerpilih?->lokasi;

            $pemeriksaan =
                $pendaftaranTerpilih
                    ->pemeriksaanKesehatan;

            $skrining =
                $this->jawabanSkrining(
                    $pendaftaranTerpilih
                );

            $timeline =
                $this->timeline(
                    $pendaftaranTerpilih
                );

            $toneTerpilih =
                $this->statusTone(
                    $pendaftaranTerpilih
                        ->status
                );
        @endphp

        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-detail-riwayat"
            wire:click="tutupDetailRiwayat"
            x-on:keydown.escape.window="$wire.tutupDetailRiwayat()"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-[#191c20]/50 p-3 backdrop-blur-sm sm:p-6"
        >
            <section
                wire:click.stop
                class="max-h-[calc(100vh-1.5rem)] w-full max-w-6xl overflow-y-auto rounded-2xl bg-[#f9f9ff] shadow-[0_32px_90px_rgba(25,28,32,0.3)] sm:max-h-[calc(100vh-3rem)]"
            >
                <header class="flex items-start justify-between gap-4 border-b border-[#e2e2e9] bg-white px-5 py-5 sm:px-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#991b2f]">
                            Detail Pendaftaran
                        </p>

                        <h2
                            id="judul-detail-riwayat"
                            class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-[#191c20]"
                        >
                            {{ $this->nomorPendaftaran($pendaftaranTerpilih) }}
                        </h2>

                        <span
                            @class([
                                'mt-3 inline-flex rounded-full px-3 py-1.5 text-xs font-bold',
                                'bg-[#eaf7f0] text-[#257a57]' => $toneTerpilih === 'success',
                                'bg-[#ffdad6] text-[#93000a]' => $toneTerpilih === 'danger',
                                'bg-[#fff4de] text-[#b86e12]' => $toneTerpilih === 'warning',
                            ])
                        >
                            {{ $this->labelStatusPendaftaran($pendaftaranTerpilih->status) }}
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="tutupDetailRiwayat"
                        aria-label="Tutup detail"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f3f3fa] text-2xl text-[#584141]"
                    >
                        ×
                    </button>
                </header>

                <div class="grid grid-cols-1 gap-5 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <main class="space-y-5">
                        <section class="rounded-2xl border border-[#e6e3df] bg-white p-5">
                            <h3 class="text-lg font-semibold text-[#191c20]">
                                Informasi Jadwal
                            </h3>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <article class="rounded-xl bg-[#f3f3fa] p-4">
                                    <p class="text-xs font-bold uppercase text-[#8c7071]">
                                        Lokasi
                                    </p>

                                    <p class="mt-2 text-sm font-semibold text-[#191c20]">
                                        {{ $this->namaLokasi($lokasiTerpilih) }}
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-[#584141]">
                                        {{ $this->alamatLokasi($lokasiTerpilih) }}
                                    </p>
                                </article>

                                <article class="rounded-xl bg-[#f3f3fa] p-4">
                                    <p class="text-xs font-bold uppercase text-[#8c7071]">
                                        Jadwal
                                    </p>

                                    <p class="mt-2 text-sm font-semibold text-[#191c20]">
                                        {{ $this->tanggalJadwal($jadwalTerpilih) }}
                                    </p>

                                    <p class="mt-1 text-sm text-[#584141]">
                                        {{ $this->jamJadwal($jadwalTerpilih) }}
                                        WIB
                                    </p>
                                </article>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-[#e6e3df] bg-white p-5">
                            <h3 class="text-lg font-semibold text-[#191c20]">
                                Skrining Awal
                            </h3>

                            @if ($skrining === [])
                                <p class="mt-4 text-sm text-[#584141]">
                                    Jawaban skrining belum tersedia untuk pendaftaran ini.
                                </p>
                            @else
                                <div class="mt-4 divide-y divide-[#e2e2e9]">
                                    @foreach ($skrining as $jawaban)
                                        <div class="flex items-center justify-between gap-4 py-3">
                                            <p class="text-sm text-[#584141]">
                                                {{ $jawaban['label'] }}
                                            </p>

                                            <span
                                                @class([
                                                    'inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-bold',
                                                    'bg-[#eaf7f0] text-[#257a57]' => $jawaban['positif'],
                                                    'bg-[#fff4de] text-[#b86e12]' => ! $jawaban['positif'],
                                                ])
                                            >
                                                {{ $jawaban['jawaban'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>

                        @if ($pemeriksaan !== null)
                            <section class="rounded-2xl border border-[#e6e3df] bg-white p-5">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <h3 class="text-lg font-semibold text-[#191c20]">
                                        Hasil Pemeriksaan Kesehatan
                                    </h3>

                                    <span
                                        @class([
                                            'inline-flex rounded-full px-3 py-1.5 text-xs font-bold',
                                            'bg-[#eaf7f0] text-[#257a57]' => $this->nilaiEnumForView($pemeriksaan->status_kelayakan) === 'eligible',
                                            'bg-[#ffdad6] text-[#93000a]' => $this->nilaiEnumForView($pemeriksaan->status_kelayakan) === 'ineligible',
                                        ])
                                    >
                                        {{ $this->labelEnum($pemeriksaan->status_kelayakan) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Berat badan
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#191c20]">
                                            {{ $pemeriksaan->berat_badan_kg ?? '-' }}
                                            kg
                                        </strong>
                                    </article>

                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Tekanan darah
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#191c20]">
                                            {{ $pemeriksaan->tekanan_sistolik ?? '-' }}
                                            /
                                            {{ $pemeriksaan->tekanan_diastolik ?? '-' }}
                                        </strong>
                                    </article>

                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Hemoglobin
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#191c20]">
                                            {{ $pemeriksaan->kadar_hemoglobin ?? '-' }}
                                            g/dL
                                        </strong>
                                    </article>

                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Suhu tubuh
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#191c20]">
                                            {{ $pemeriksaan->suhu_tubuh ?? '-' }}
                                            °C
                                        </strong>
                                    </article>

                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Denyut nadi
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#191c20]">
                                            {{ $pemeriksaan->denyut_nadi ?? '-' }}
                                            /menit
                                        </strong>
                                    </article>

                                    <article class="rounded-xl bg-[#f3f3fa] p-4">
                                        <p class="text-xs text-[#8c7071]">
                                            Golongan darah
                                        </p>

                                        <strong class="mt-1 block text-sm text-[#76001c]">
                                            {{ $this->golonganPemeriksaan($pemeriksaan) }}
                                        </strong>
                                    </article>
                                </div>

                                @if (filled($pemeriksaan->alasan_tidak_layak))
                                    <div class="mt-4 rounded-xl border border-[#ffb4ab] bg-[#fff5f4] p-4 text-sm leading-6 text-[#93000a]">
                                        {{ $pemeriksaan->alasan_tidak_layak }}
                                    </div>
                                @endif

                                @if (filled($pemeriksaan->catatan_medis))
                                    <div class="mt-4">
                                        <p class="text-xs font-bold uppercase text-[#8c7071]">
                                            Catatan Pemeriksaan
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-[#584141]">
                                            {{ $pemeriksaan->catatan_medis }}
                                        </p>
                                    </div>
                                @endif
                            </section>
                        @endif

                        @if (
                            $this->alasanStatus($pendaftaranTerpilih)
                            !== '-'
                        )
                            <section class="rounded-2xl border border-[#ffb4ab] bg-[#fff5f4] p-5">
                                <p class="text-xs font-bold uppercase text-[#93000a]">
                                    Catatan atau alasan
                                </p>

                                <p class="mt-2 text-sm leading-6 text-[#93000a]">
                                    {{ $this->alasanStatus($pendaftaranTerpilih) }}
                                </p>
                            </section>
                        @endif

                        @if ($pendaftaranTerpilih->kantongDarah)
                            <section class="rounded-2xl border border-[#e6e3df] bg-white p-5">
                                <p class="text-xs font-bold uppercase text-[#8c7071]">
                                    Kantong Darah
                                </p>

                                <p class="mt-2 text-lg font-bold text-[#76001c]">
                                    {{ $pendaftaranTerpilih->kantongDarah->kode_kantong }}
                                </p>
                            </section>
                        @endif
                    </main>

                    <aside class="rounded-2xl border border-[#e6e3df] bg-white p-5">
                        <h3 class="text-lg font-semibold text-[#191c20]">
                            Status Pendaftaran
                        </h3>

                        <div class="relative mt-6 space-y-6 pl-8">
                            <div class="absolute bottom-5 left-[11px] top-3 w-px bg-[#e2e2e9]"></div>

                            @foreach ($timeline as $tahap)
                                <article class="relative">
                                    <span
                                        @class([
                                            'absolute -left-8 top-0 z-10 flex h-6 w-6 items-center justify-center rounded-full border',
                                            'border-[#257a57] bg-[#eaf7f0] text-[#257a57]' => $tahap['class'] === 'done',
                                            'border-[#76001c] bg-[#76001c] text-white ring-4 ring-[#ffdada]' => $tahap['class'] === 'active',
                                            'border-[#ba1a1a] bg-[#ffdad6] text-[#ba1a1a]' => $tahap['class'] === 'danger',
                                            'border-[#d9d9e0] bg-[#f3f3fa] text-[#8c7071]' => $tahap['class'] === 'waiting',
                                        ])
                                    >
                                        @if ($tahap['class'] === 'done')
                                            ✓
                                        @elseif ($tahap['class'] === 'active')
                                            <span class="h-2 w-2 rounded-full bg-white"></span>
                                        @elseif ($tahap['class'] === 'danger')
                                            !
                                        @else
                                            <span class="h-2 w-2 rounded-full bg-[#d9d9e0]"></span>
                                        @endif
                                    </span>

                                    <h4
                                        @class([
                                            'text-sm font-semibold',
                                            'text-[#191c20]' => in_array($tahap['class'], ['done', 'waiting'], true),
                                            'text-[#76001c]' => $tahap['class'] === 'active',
                                            'text-[#ba1a1a]' => $tahap['class'] === 'danger',
                                        ])
                                    >
                                        {{ $tahap['label'] }}
                                    </h4>

                                    <p class="mt-1 text-xs leading-5 text-[#584141]">
                                        {{ $tahap['description'] }}
                                    </p>

                                    @if ($tahap['tanggal'] !== '-')
                                        <p class="mt-1 text-[11px] font-medium text-[#8c7071]">
                                            {{ $tahap['tanggal'] }}
                                        </p>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        @if ($pendaftaranTerpilih->dapatDibatalkan())
                            <div class="mt-7 border-t border-[#e2e2e9] pt-5">
                                <button
                                    type="button"
                                    wire:click="bukaPembatalan({{ $pendaftaranTerpilih->id }})"
                                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-[#e0bfbf] bg-white px-5 text-sm font-semibold text-[#991b2f] transition hover:bg-[#fff1f2]"
                                >
                                    Batalkan Pendaftaran
                                </button>

                                <p class="mt-2 text-center text-xs leading-5 text-[#8c7071]">
                                    Hanya dapat dibatalkan ketika masih menunggu atau sudah disetujui.
                                </p>
                            </div>
                        @endif
                    </aside>
                </div>

                <footer class="flex flex-col-reverse gap-3 border-t border-[#e2e2e9] bg-white px-5 py-5 sm:flex-row sm:justify-end sm:px-7">
                    <a
                        href="{{ $this->mapsUrl($lokasiTerpilih) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#24262b]"
                    >
                        Buka Google Maps
                    </a>

                    <button
                        type="button"
                        wire:click="tutupDetailRiwayat"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#76001c] px-5 text-sm font-semibold text-white"
                    >
                        Tutup
                    </button>
                </footer>
            </section>
        </div>
    @endif

    {{-- Modal pembatalan --}}
    @if ($pendaftaranPembatalan !== null)
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="judul-pembatalan"
            wire:click="tutupPembatalan"
            x-on:keydown.escape.window="$wire.tutupPembatalan()"
            class="fixed inset-0 z-[90] flex items-center justify-center bg-[#191c20]/55 p-4 backdrop-blur-sm"
        >
            <section
                wire:click.stop
                class="w-full max-w-lg rounded-2xl bg-white shadow-[0_32px_90px_rgba(25,28,32,0.3)]"
            >
                <header class="border-b border-[#e2e2e9] px-5 py-5 sm:px-6">
                    <h2
                        id="judul-pembatalan"
                        class="text-xl font-semibold text-[#191c20]"
                    >
                        Batalkan Pendaftaran
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-[#584141]">
                        {{ $this->nomorPendaftaran($pendaftaranPembatalan) }}
                    </p>
                </header>

                <div class="p-5 sm:p-6">
                    <div class="rounded-xl border border-[#f2c879] bg-[#fff4de] p-4 text-sm leading-6 text-[#8a4f00]">
                        Pembatalan tidak dapat dibatalkan kembali. Jadwal yang sama juga tidak dapat didaftarkan ulang.
                    </div>

                    @error('pembatalan')
                        <div class="mt-4 rounded-xl border border-[#ffb4ab] bg-[#ffdad6] p-3 text-sm text-[#93000a]">
                            {{ $message }}
                        </div>
                    @enderror

                    <label
                        for="alasan-pembatalan"
                        class="mb-2 mt-5 block text-sm font-semibold text-[#191c20]"
                    >
                        Alasan Pembatalan
                    </label>

                    <textarea
                        id="alasan-pembatalan"
                        wire:model="alasanPembatalan"
                        rows="5"
                        maxlength="1000"
                        placeholder="Tuliskan alasan pembatalan secara jelas..."
                        class="w-full resize-y rounded-xl border border-[#e2e2e9] px-4 py-3 text-sm text-[#191c20] placeholder:text-[#8c7071] focus:border-[#76001c] focus:ring-[#76001c]/15"
                    ></textarea>

                    @error('alasanPembatalan')
                        <p class="mt-1 text-xs font-medium text-[#ba1a1a]">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <footer class="flex flex-col-reverse gap-3 border-t border-[#e2e2e9] px-5 py-5 sm:flex-row sm:justify-end sm:px-6">
                    <button
                        type="button"
                        wire:click="tutupPembatalan"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#584141]"
                    >
                        Kembali
                    </button>

                    <button
                        type="button"
                        wire:click="batalkanPendaftaran"
                        wire:loading.attr="disabled"
                        wire:target="batalkanPendaftaran"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#ba1a1a] px-5 text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-60"
                    >
                        <span
                            wire:loading.remove
                            wire:target="batalkanPendaftaran"
                        >
                            Batalkan Pendaftaran
                        </span>

                        <span
                            wire:loading
                            wire:target="batalkanPendaftaran"
                        >
                            Memproses...
                        </span>
                    </button>
                </footer>
            </section>
        </div>
    @endif
</div>