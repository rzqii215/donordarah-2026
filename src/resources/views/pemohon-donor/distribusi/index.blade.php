@php
    $nilaiEnum = function (
        mixed $value
    ): string {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        return trim((string) $value);
    };

    $labelEnum = function (
        mixed $value
    ) use (
        $nilaiEnum
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

        return str(
            $nilaiEnum($value)
        )
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->headline()
            ->toString();
    };

    $statusClass = function (
        mixed $status
    ) use (
        $nilaiEnum
    ): string {
        return match (
            $nilaiEnum($status)
        ) {
            'scheduled',
            'dijadwalkan' =>
                'bg-[#fff1c9] text-[#8a5a00]',

            'ready',
            'ready_for_handover',
            'siap',
            'siap_diserahkan' =>
                'bg-[#e7effc] text-[#315b9b]',

            'in_delivery',
            'dalam_pengiriman' =>
                'bg-[#ede9fe] text-[#6d28d9]',

            'completed',
            'selesai' =>
                'bg-[#dff7e7] text-[#176b3a]',

            'cancelled',
            'dibatalkan' =>
                'bg-[#ffe4e7] text-[#991b2f]',

            default =>
                'bg-[#f1ecea] text-[#655253]',
        };
    };

    $golonganDarah = function (
        mixed $golongan,
        mixed $rhesus
    ) use (
        $nilaiEnum
    ): string {
        $golonganLabel = is_object($golongan)
            && method_exists(
                $golongan,
                'label'
            )
                ? (string) $golongan->label()
                : $nilaiEnum($golongan);

        if (
            is_object($rhesus)
            && method_exists(
                $rhesus,
                'simbol'
            )
        ) {
            $rhesusLabel =
                (string) $rhesus->simbol();
        } else {
            $rhesusLabel = match (
                $nilaiEnum($rhesus)
            ) {
                'positive',
                'positif',
                '+' => '+',

                'negative',
                'negatif',
                '-' => '-',

                default =>
                    $nilaiEnum($rhesus),
            };
        }

        return $golonganLabel .
            $rhesusLabel;
    };

    $filterSedangAktif =
        filled($q)
        || filled($statusAktif);
@endphp

<x-layouts.pemohon-donor
    title="Distribusi Darah"
    heading="Distribusi Kantong Darah"
    description="Pantau jadwal penyerahan dan status distribusi darah rumah sakit."
    :pengguna="$pengguna"
    :profil="$profil"
    :notification-count="$siapDiserahkan"
>
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-7 text-white shadow-[0_22px_55px_rgba(118,0,28,0.2)] sm:px-8"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-24 right-32 h-52 w-52 rounded-full bg-[#fdb7c5]/10"
            ></div>

            <div
                class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_290px] lg:items-center"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.14em] text-white/65"
                    >
                        Distribusi Rumah Sakit
                    </p>

                    <h2
                        class="mt-2 max-w-2xl text-3xl font-bold tracking-[-0.05em] sm:text-4xl"
                    >
                        Pantau proses penyerahan kantong darah
                    </h2>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-7 text-white/70"
                    >
                        Distribusi dibuat setelah pengajuan disetujui dan kantong
                        darah selesai dialokasikan oleh petugas.
                    </p>
                </div>

                <article
                    class="rounded-[22px] border border-white/15 bg-white/10 p-5"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-white/60"
                    >
                        Siap Diserahkan
                    </p>

                    <div
                        class="mt-3 flex items-end gap-3"
                    >
                        <strong
                            class="text-5xl font-bold leading-none tracking-[-0.06em]"
                        >
                            {{ number_format($siapDiserahkan) }}
                        </strong>

                        <span
                            class="pb-1 text-sm font-semibold text-white/65"
                        >
                            distribusi
                        </span>
                    </div>

                    <a
                        href="{{ route('pemohon-donor.pengajuan.create') }}"
                        class="mt-5 inline-flex min-h-10 w-full items-center justify-center rounded-xl bg-white px-4 text-sm font-bold text-[#76001c]"
                    >
                        Buat Pengajuan Baru
                    </a>
                </article>
            </div>
        </section>

        @if ($profil === null)
            <section
                class="rounded-[22px] border border-[#f0cbd2] bg-[#fff4f6] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Profil belum tersedia
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-[#3f0716]"
                        >
                            Lengkapi profil rumah sakit
                        </h2>

                        <p
                            class="mt-1 text-sm text-[#755b61]"
                        >
                            Informasi distribusi akan muncul setelah profil dan pengajuan tersedia.
                        </p>
                    </div>

                    <a
                        href="{{ route('pemohon-donor.profil.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                    >
                        Lengkapi Profil
                    </a>
                </div>
            </section>
        @endif

        <section
            class="grid grid-cols-2 gap-3 xl:grid-cols-5"
        >
            <article
                class="rounded-[22px] border border-[#e8e2df] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f1ecea] text-[#655253]"
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
                </span>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Total
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($totalDistribusi) }}
                </strong>
            </article>

            <article
                class="rounded-[22px] border border-[#eee0bd] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff1c9] text-[#8a5a00]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path d="M8 3v4M16 3v4M3 11h18" />
                    </svg>
                </span>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Terjadwal
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($terjadwal) }}
                </strong>
            </article>

            <article
                class="rounded-[22px] border border-[#dce5f4] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
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
                </span>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Siap Diserahkan
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($siapDiserahkan) }}
                </strong>
            </article>

            <article
                class="rounded-[22px] border border-[#d9eadf] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#dff7e7] text-[#176b3a]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path d="m8 12 2.5 2.5L16 9" />
                    </svg>
                </span>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Selesai
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($selesai) }}
                </strong>
            </article>

            <article
                class="col-span-2 rounded-[22px] border border-[#f0d7dc] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)] xl:col-span-1"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#ffe7ec] text-[#991b2f]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path d="m9 9 6 6M15 9l-6 6" />
                    </svg>
                </span>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Dibatalkan
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($dibatalkan) }}
                </strong>
            </article>
        </section>

        <section
            class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
        >
            <form
                method="GET"
                action="{{ route('pemohon-donor.distribusi.index') }}"
                class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_250px_auto]"
            >
                <div>
                    <label
                        for="pencarian-distribusi"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Cari Distribusi
                    </label>

                    <div class="relative">
                        <svg
                            class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#8c7071]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            id="pencarian-distribusi"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Nomor distribusi, pengajuan, atau pasien..."
                            class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] pl-12 pr-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                        >
                    </div>
                </div>

                <div>
                    <label
                        for="status-distribusi"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Status
                    </label>

                    <select
                        id="status-distribusi"
                        name="status"
                        class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                    >
                        <option value="">
                            Semua Status
                        </option>

                        @foreach ($statusOptions as $status)
                            <option
                                value="{{ $nilaiEnum($status) }}"
                                @selected(
                                    $statusAktif ===
                                    $nilaiEnum($status)
                                )
                            >
                                {{ $labelEnum($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div
                    class="flex items-end gap-2"
                >
                    <button
                        type="submit"
                        class="inline-flex min-h-12 flex-1 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                    >
                        Terapkan
                    </button>

                    @if ($filterSedangAktif)
                        <a
                            href="{{ route('pemohon-donor.distribusi.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-4 text-sm font-bold text-[#76001c]"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        @if ($distribusi->isEmpty())
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
                        <path d="M3 7h11v10H3z" />
                        <path d="M14 10h4l3 3v4h-7z" />
                        <circle cx="7" cy="19" r="2" />
                        <circle cx="17" cy="19" r="2" />
                    </svg>
                </div>

                <h2
                    class="mt-5 text-xl font-bold text-[#191c20]"
                >
                    Distribusi tidak ditemukan
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#755f60]"
                >
                    Belum ada distribusi atau tidak ada data yang cocok dengan filter.
                </p>

                <a
                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                    class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                >
                    Lihat Pengajuan
                </a>
            </section>
        @else
            <section class="space-y-4">
                @foreach ($distribusi as $item)
                    @php
                        $permintaan =
                            $item->permintaan;

                        $tanggalDistribusi =
                            $item->dijadwalkan_pada;

                        $tanggalSelesai =
                            $item->diserahkan_pada
                            ?? $item->selesai_pada
                            ?? null;
                    @endphp

                    <article
                        class="overflow-hidden rounded-[24px] border border-[#e8e2df] bg-white shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                    >
                        <div
                            class="flex flex-col gap-4 border-b border-[#eee8e5] px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        class="inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold {{ $statusClass($item->status) }}"
                                    >
                                        {{ $labelEnum($item->status) }}
                                    </span>

                                    @if ($permintaan !== null)
                                        <span
                                            class="inline-flex min-h-8 items-center rounded-full border border-[#efcbd2] bg-[#fff5f7] px-3 text-xs font-bold text-[#991b2f]"
                                        >
                                            {{ $golonganDarah($permintaan->golongan_darah, $permintaan->rhesus) }}
                                        </span>
                                    @endif
                                </div>

                                <h2
                                    class="mt-3 truncate text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                                >
                                    {{ $item->nomor_distribusi ?? 'Distribusi Darah' }}
                                </h2>

                                <p
                                    class="mt-1 text-sm text-[#755f60]"
                                >
                                    Pengajuan:
                                    <strong class="text-[#3f3030]">
                                        {{ $permintaan?->nomor_permintaan ?? '-' }}
                                    </strong>
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap gap-2"
                            >
                                <a
                                    href="{{ route('pemohon-donor.distribusi.bukti', $item) }}"
                                    class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[#991b2f] px-4 text-sm font-bold text-white"
                                >
                                    Lihat Bukti
                                </a>

                                <a
                                    href="{{ route('pemohon-donor.distribusi.bukti.unduh', $item) }}"
                                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-4 text-sm font-bold text-[#76001c]"
                                >
                                    Unduh
                                </a>
                            </div>
                        </div>

                        <div
                            class="grid gap-5 px-5 py-5 sm:grid-cols-2 xl:grid-cols-4"
                        >
                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                                >
                                    Jadwal Distribusi
                                </span>

                                <strong
                                    class="mt-2 block text-sm text-[#191c20]"
                                >
                                    {{ $tanggalDistribusi?->translatedFormat('d F Y') ?? '-' }}
                                </strong>

                                <span
                                    class="mt-1 block text-xs text-[#755f60]"
                                >
                                    {{ $tanggalDistribusi?->format('H:i') ?? '-' }} WIB
                                </span>
                            </div>

                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                                >
                                    Referensi Pasien
                                </span>

                                <strong
                                    class="mt-2 block text-sm text-[#191c20]"
                                >
                                    {{ $permintaan?->referensi_pasien ?? '-' }}
                                </strong>

                                <span
                                    class="mt-1 block text-xs text-[#755f60]"
                                >
                                    Dr. {{ $permintaan?->nama_dokter ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                                >
                                    Kebutuhan
                                </span>

                                <strong
                                    class="mt-2 block text-sm text-[#191c20]"
                                >
                                    {{ number_format($permintaan?->jumlah_kantong ?? 0) }}
                                    kantong
                                </strong>

                                <span
                                    class="mt-1 block text-xs text-[#755f60]"
                                >
                                    {{ $permintaan !== null
                                        ? $golonganDarah($permintaan->golongan_darah, $permintaan->rhesus)
                                        : '-' }}
                                </span>
                            </div>

                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                                >
                                    Diselesaikan
                                </span>

                                <strong
                                    class="mt-2 block text-sm text-[#191c20]"
                                >
                                    {{ $tanggalSelesai?->translatedFormat('d F Y') ?? '-' }}
                                </strong>

                                <span
                                    class="mt-1 block text-xs text-[#755f60]"
                                >
                                    {{ $tanggalSelesai?->format('H:i') ?? '-' }}
                                    @if ($tanggalSelesai !== null)
                                        WIB
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if (
                            filled($item->catatan)
                            || filled($item->alasan_pembatalan)
                        )
                            <div
                                class="border-t border-[#eee8e5] bg-[#faf8f7] px-5 py-4"
                            >
                                <span
                                    class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]"
                                >
                                    Catatan
                                </span>

                                <p
                                    class="mt-1 text-sm leading-6 text-[#584141]"
                                >
                                    {{ $item->alasan_pembatalan ?? $item->catatan }}
                                </p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</x-layouts.pemohon-donor>