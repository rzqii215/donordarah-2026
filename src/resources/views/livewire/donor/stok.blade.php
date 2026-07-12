<div
    wire:poll.60s
    class="space-y-6"
>
    <section
        class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-8 text-white shadow-[0_24px_60px_rgba(118,0,28,0.22)] sm:px-8 lg:px-10"
    >
        <div
            class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10"
        ></div>

        <div
            class="pointer-events-none absolute -bottom-24 right-36 h-56 w-56 rounded-full bg-[#fdb7c5]/10"
        ></div>

        <div
            class="relative grid gap-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-end"
        >
            <div>
                <div
                    class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em]"
                >
                    <span
                        class="h-2 w-2 rounded-full bg-[#ffb1c0]"
                    ></span>

                    Informasi stok real-time
                </div>

                <h1
                    class="max-w-3xl text-3xl font-bold tracking-[-0.05em] sm:text-4xl lg:text-5xl"
                >
                    Ketersediaan Stok Darah
                </h1>

                <p
                    class="mt-4 max-w-2xl text-sm leading-7 text-white/75 sm:text-base"
                >
                    Pantau ketersediaan darah berdasarkan golongan darah dan rhesus.
                    Stok tersedia hanya menghitung kantong yang sudah lulus pemeriksaan
                    mutu, belum kedaluwarsa, dan belum dialokasikan.
                </p>

                <div
                    class="mt-6 flex flex-wrap items-center gap-3 text-xs font-semibold text-white/70"
                >
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 8v4l3 2"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />
                        </svg>

                        Diperbarui {{ $diperbaruiPada }}
                    </span>

                    <span
                        wire:loading
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2"
                    >
                        <span
                            class="h-3 w-3 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        ></span>

                        Memperbarui data
                    </span>
                </div>
            </div>

            <article
                class="rounded-[24px] border border-white/15 bg-white/10 p-6 backdrop-blur-sm"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.12em] text-white/65"
                >
                    Total siap digunakan
                </p>

                <div
                    class="mt-3 flex items-end gap-3"
                >
                    <strong
                        class="text-6xl font-bold leading-none tracking-[-0.06em]"
                    >
                        {{ number_format($ringkasan['tersedia']) }}
                    </strong>

                    <span
                        class="pb-1 text-sm font-semibold text-white/70"
                    >
                        kantong
                    </span>
                </div>

                <div
                    class="mt-5 border-t border-white/15 pt-4"
                >
                    <p
                        class="text-xs leading-5 text-white/65"
                    >
                        Total volume tersedia
                    </p>

                    <strong
                        class="mt-1 block text-lg"
                    >
                        {{ number_format($ringkasan['volume_tersedia_ml']) }} ml
                    </strong>
                </div>
            </article>
        </div>
    </section>

    <section
        class="grid grid-cols-2 gap-3 lg:grid-cols-5"
    >
        <article
            class="rounded-2xl border border-[#d9eadf] bg-white p-4 shadow-[0_12px_35px_rgba(25,28,32,0.05)]"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#dff7e7] text-[#176b3a]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <p
                class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
            >
                Tersedia
            </p>

            <strong
                class="mt-1 block text-3xl tracking-[-0.04em] text-[#191c20]"
            >
                {{ number_format($ringkasan['tersedia']) }}
            </strong>

            <p
                class="mt-1 text-xs text-[#6f5b5c]"
            >
                Siap dialokasikan
            </p>
        </article>

        <article
            class="rounded-2xl border border-[#eee0bd] bg-white p-4 shadow-[0_12px_35px_rgba(25,28,32,0.05)]"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1c9] text-[#8a5a00]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    />

                    <path d="M12 7v5l3 2" />
                </svg>
            </div>

            <p
                class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
            >
                Dialokasikan
            </p>

            <strong
                class="mt-1 block text-3xl tracking-[-0.04em] text-[#191c20]"
            >
                {{ number_format($ringkasan['dipesan']) }}
            </strong>

            <p
                class="mt-1 text-xs text-[#6f5b5c]"
            >
                Untuk permintaan
            </p>
        </article>

        <article
            class="rounded-2xl border border-[#dce5f4] bg-white p-4 shadow-[0_12px_35px_rgba(25,28,32,0.05)]"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M3 7h11v10H3z" />
                    <path d="M14 10h4l3 3v4h-7z" />
                </svg>
            </div>

            <p
                class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
            >
                Didistribusikan
            </p>

            <strong
                class="mt-1 block text-3xl tracking-[-0.04em] text-[#191c20]"
            >
                {{ number_format($ringkasan['didistribusikan']) }}
            </strong>

            <p
                class="mt-1 text-xs text-[#6f5b5c]"
            >
                Sudah diserahkan
            </p>
        </article>

        <article
            class="rounded-2xl border border-[#f1dce1] bg-white p-4 shadow-[0_12px_35px_rgba(25,28,32,0.05)]"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#ffe8ed] text-[#991b2f]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M12 2.5S5.5 10 5.5 15a6.5 6.5 0 0 0 13 0C18.5 10 12 2.5 12 2.5Z"
                    />
                </svg>
            </div>

            <p
                class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
            >
                Lulus mutu
            </p>

            <strong
                class="mt-1 block text-3xl tracking-[-0.04em] text-[#191c20]"
            >
                {{ number_format($ringkasan['lulus_mutu']) }}
            </strong>

            <p
                class="mt-1 text-xs text-[#6f5b5c]"
            >
                Kantong valid
            </p>
        </article>

        <article
            class="col-span-2 rounded-2xl border border-[#f1d9d9] bg-white p-4 shadow-[0_12px_35px_rgba(25,28,32,0.05)] lg:col-span-1"
        >
            <div
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff0f0] text-[#b42318]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 8v5" />
                    <path d="M12 17h.01" />
                    <path
                        d="m10.3 3.9-7.8 13.5A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0Z"
                    />
                </svg>
            </div>

            <p
                class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
            >
                Segera kedaluwarsa
            </p>

            <strong
                class="mt-1 block text-3xl tracking-[-0.04em] text-[#191c20]"
            >
                {{ number_format($ringkasan['hampir_kedaluwarsa']) }}
            </strong>

            <p
                class="mt-1 text-xs text-[#6f5b5c]"
            >
                Dalam tujuh hari
            </p>
        </article>
    </section>

    @if ($peringatanStok->isNotEmpty())
        <section
            class="rounded-[22px] border border-[#f2d1d7] bg-[#fff5f6] p-5"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Perhatian stok
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold tracking-[-0.03em] text-[#3f0716]"
                    >
                        Beberapa golongan darah memiliki stok rendah
                    </h2>

                    <p
                        class="mt-1 text-sm leading-6 text-[#755b61]"
                    >
                        Golongan darah berikut memiliki maksimal dua kantong siap digunakan.
                    </p>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    @foreach ($peringatanStok as $stok)
                        <span
                            class="inline-flex min-h-10 items-center rounded-full border border-[#e8bcc6] bg-white px-4 text-sm font-bold text-[#76001c]"
                        >
                            {{ $stok['kode'] }}:
                            {{ $stok['tersedia'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section
        class="rounded-[24px] border border-[#e6e3df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
    >
        <div
            class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_190px_220px]"
        >
            <div>
                <label
                    for="pencarian-stok"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Cari golongan darah
                </label>

                <div class="relative">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#8c7071]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="7"
                        />

                        <path d="m20 20-3.5-3.5" />
                    </svg>

                    <input
                        id="pencarian-stok"
                        type="search"
                        wire:model.live.debounce.300ms="pencarian"
                        placeholder="Contoh: A+, O-, positif..."
                        class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] pl-12 pr-4 text-sm text-[#191c20] outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                    >
                </div>
            </div>

            <div>
                <label
                    for="filter-rhesus"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Rhesus
                </label>

                <select
                    id="filter-rhesus"
                    wire:model.live="filterRhesus"
                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm text-[#191c20] outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                >
                    <option value="semua">
                        Semua Rhesus
                    </option>

                    <option value="positive">
                        Positif (+)
                    </option>

                    <option value="negative">
                        Negatif (-)
                    </option>
                </select>
            </div>

            <div>
                <label
                    for="urutan-stok"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Urutkan
                </label>

                <select
                    id="urutan-stok"
                    wire:model.live="urutan"
                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm text-[#191c20] outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                >
                    <option value="golongan">
                        Golongan darah
                    </option>

                    <option value="tersedia_terbanyak">
                        Stok terbanyak
                    </option>

                    <option value="tersedia_tersedikit">
                        Stok tersedikit
                    </option>

                    <option value="total_terbanyak">
                        Total terbanyak
                    </option>
                </select>
            </div>
        </div>

        <div
            class="mt-4 flex flex-col gap-4 border-t border-[#eee9e6] pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <label
                class="inline-flex cursor-pointer items-start gap-3"
            >
                <input
                    type="checkbox"
                    wire:model.live="hanyaTersedia"
                    class="mt-1 h-4 w-4 rounded border-[#d8ceca] text-[#991b2f] focus:ring-[#991b2f]"
                >

                <span>
                    <strong
                        class="block text-sm text-[#191c20]"
                    >
                        Hanya tampilkan stok tersedia
                    </strong>

                    <span
                        class="mt-1 block text-xs text-[#755f60]"
                    >
                        Sembunyikan golongan darah dengan stok kosong.
                    </span>
                </span>
            </label>

            @if (
                filled($pencarian)
                || $filterRhesus !== 'semua'
                || $hanyaTersedia
                || $urutan !== 'golongan'
            )
                <button
                    type="button"
                    wire:click="resetFilter"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#e6d9dc] bg-white px-4 text-sm font-semibold text-[#76001c] transition hover:bg-[#fff5f6]"
                >
                    Reset Filter
                </button>
            @endif
        </div>
    </section>

    @if (count($stokDarah) === 0)
        <section
            class="rounded-[26px] border border-dashed border-[#e7c9cf] bg-[#fff8f9] px-6 py-16 text-center"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#ffe7ec] text-[#991b2f]"
            >
                <svg
                    class="h-8 w-8"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M12 2.5S5.5 10 5.5 15a6.5 6.5 0 0 0 13 0C18.5 10 12 2.5 12 2.5Z"
                    />
                </svg>
            </div>

            <h2
                class="mt-5 text-xl font-bold text-[#191c20]"
            >
                Stok tidak ditemukan
            </h2>

            <p
                class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#755f60]"
            >
                Tidak ada golongan darah yang sesuai dengan filter yang dipilih.
            </p>

            <button
                type="button"
                wire:click="resetFilter"
                class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
            >
                Reset Filter
            </button>
        </section>
    @else
        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            @foreach ($stokDarah as $stok)
                @php
                    $statusBadgeClass = match (
                        $stok['status_class']
                    ) {
                        'success' =>
                            'bg-[#dff7e7] text-[#176b3a]',

                        'warning' =>
                            'bg-[#fff1c9] text-[#8a5a00]',

                        'critical' =>
                            'bg-[#ffe4e1] text-[#b42318]',

                        default =>
                            'bg-[#f2e7e9] text-[#76001c]',
                    };

                    $progressClass = match (
                        $stok['status_class']
                    ) {
                        'success' =>
                            'bg-[#229653]',

                        'warning' =>
                            'bg-[#e9a400]',

                        'critical' =>
                            'bg-[#dd4a3d]',

                        default =>
                            'bg-[#991b2f]',
                    };
                @endphp

                <article
                    wire:key="stok-{{ $stok['golongan'] }}-{{ $stok['rhesus'] }}"
                    class="group rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_42px_rgba(25,28,32,0.055)] transition duration-200 hover:-translate-y-1 hover:border-[#e2c6cc] hover:shadow-[0_22px_55px_rgba(118,0,28,0.1)]"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-[20px] bg-[#ffe7ec] text-2xl font-bold tracking-[-0.05em] text-[#991b2f] transition group-hover:bg-[#991b2f] group-hover:text-white"
                        >
                            {{ $stok['kode'] }}
                        </div>

                        <span
                            class="inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold {{ $statusBadgeClass }}"
                        >
                            {{ $stok['status_label'] }}
                        </span>
                    </div>

                    <div class="mt-6">
                        <div
                            class="flex items-end gap-2"
                        >
                            <strong
                                class="text-5xl font-bold leading-none tracking-[-0.06em] text-[#191c20]"
                            >
                                {{ number_format($stok['tersedia']) }}
                            </strong>

                            <span
                                class="pb-1 text-xs font-semibold text-[#755f60]"
                            >
                                kantong tersedia
                            </span>
                        </div>

                        <div
                            class="mt-4 h-2 overflow-hidden rounded-full bg-[#eee9e7]"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $progressClass }}"
                                style="width: {{ $stok['persentase'] }}%;"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="mt-5 grid grid-cols-3 gap-2 border-t border-[#eee9e7] pt-4"
                    >
                        <div
                            class="rounded-xl bg-[#f7f5f4] p-3 text-center"
                        >
                            <span
                                class="block text-[10px] font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                            >
                                Tersedia
                            </span>

                            <strong
                                class="mt-1 block text-lg text-[#191c20]"
                            >
                                {{ number_format($stok['tersedia']) }}
                            </strong>
                        </div>

                        <div
                            class="rounded-xl bg-[#f7f5f4] p-3 text-center"
                        >
                            <span
                                class="block text-[10px] font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                            >
                                Alokasi
                            </span>

                            <strong
                                class="mt-1 block text-lg text-[#191c20]"
                            >
                                {{ number_format($stok['dipesan']) }}
                            </strong>
                        </div>

                        <div
                            class="rounded-xl bg-[#f7f5f4] p-3 text-center"
                        >
                            <span
                                class="block text-[10px] font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                            >
                                Distribusi
                            </span>

                            <strong
                                class="mt-1 block text-lg text-[#191c20]"
                            >
                                {{ number_format($stok['didistribusikan']) }}
                            </strong>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    <section
        class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center"
    >
        <article
            class="rounded-[24px] border border-[#e6e3df] bg-white p-6"
        >
            <h2
                class="text-lg font-bold tracking-[-0.03em] text-[#191c20]"
            >
                Keterangan status stok
            </h2>

            <p
                class="mt-2 max-w-2xl text-sm leading-6 text-[#755f60]"
            >
                Data ini bersifat informatif. Ketersediaan dapat berubah ketika
                kantong darah dialokasikan untuk permintaan rumah sakit atau
                selesai didistribusikan.
            </p>

            <div
                class="mt-5 flex flex-wrap gap-4 text-sm font-semibold text-[#584141]"
            >
                <span
                    class="inline-flex items-center gap-2"
                >
                    <i
                        class="h-3 w-3 rounded-full bg-[#229653]"
                    ></i>

                    Aman, lebih dari 5
                </span>

                <span
                    class="inline-flex items-center gap-2"
                >
                    <i
                        class="h-3 w-3 rounded-full bg-[#e9a400]"
                    ></i>

                    Rendah, 3–5
                </span>

                <span
                    class="inline-flex items-center gap-2"
                >
                    <i
                        class="h-3 w-3 rounded-full bg-[#dd4a3d]"
                    ></i>

                    Kritis, 1–2
                </span>

                <span
                    class="inline-flex items-center gap-2"
                >
                    <i
                        class="h-3 w-3 rounded-full bg-[#991b2f]"
                    ></i>

                    Kosong
                </span>
            </div>
        </article>

        <div
            class="flex flex-col gap-3 sm:flex-row lg:flex-col"
        >
            <a
                href="{{ route('donor.jadwal') }}"
                wire:navigate
                class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#991b2f] px-6 text-sm font-bold text-white shadow-[0_12px_28px_rgba(153,27,47,0.2)] transition hover:bg-[#76001c]"
            >
                Lihat Jadwal Donor
            </a>

            <a
                href="{{ route('donor.lokasi') }}"
                wire:navigate
                class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e2d4d7] bg-white px-6 text-sm font-bold text-[#76001c] transition hover:bg-[#fff5f6]"
            >
                Lihat Lokasi Donor
            </a>
        </div>
    </section>
</div>