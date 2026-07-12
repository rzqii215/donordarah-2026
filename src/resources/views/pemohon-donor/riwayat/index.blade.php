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

    $statusLabel = function (
        mixed $status
    ) use (
        $nilaiEnum
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

        return str(
            $nilaiEnum($status)
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
            'submitted',
            'diajukan',
            'scheduled',
            'dijadwalkan' =>
                'bg-[#fff1c9] text-[#8a5a00]',

            'under_review',
            'ditinjau' =>
                'bg-[#ede9fe] text-[#6d28d9]',

            'waiting_for_stock',
            'menunggu_stok' =>
                'bg-[#ffedd5] text-[#c2410c]',

            'approved',
            'disetujui',
            'ready',
            'ready_for_handover',
            'ready_for_pickup',
            'siap_diambil',
            'siap_diserahkan' =>
                'bg-[#e7effc] text-[#315b9b]',

            'completed',
            'selesai' =>
                'bg-[#dff7e7] text-[#176b3a]',

            'rejected',
            'ditolak',
            'cancelled',
            'dibatalkan' =>
                'bg-[#ffe4e7] text-[#991b2f]',

            default =>
                'bg-[#f1ecea] text-[#655253]',
        };
    };

    $urlData = function (
        array $item
    ): string {
        $jenis =
            $item['jenis']
            ?? null;

        $nomor =
            $item['nomor']
            ?? null;

        if ($jenis === 'pengajuan') {
            return route(
                'pemohon-donor.pengajuan.index',
                [
                    'q' => $nomor,
                ]
            );
        }

        if ($jenis === 'distribusi') {
            return route(
                'pemohon-donor.distribusi.index',
                [
                    'q' => $nomor,
                ]
            );
        }

        return route(
            'pemohon-donor.riwayat.index'
        );
    };

    $urlBukti = function (
        array $item
    ): ?string {
        $jenis =
            $item['jenis']
            ?? null;

        $nomor =
            $item['nomor']
            ?? null;

        if (
            ! filled($jenis)
            || ! filled($nomor)
            || $nomor === '-'
            || ! \Illuminate\Support\Facades\Route
                ::has(
                    'pemohon-donor.riwayat.bukti'
                )
        ) {
            return null;
        }

        return route(
            'pemohon-donor.riwayat.bukti',
            [
                'jenis' => $jenis,
                'nomor' => $nomor,
            ]
        );
    };

    $urlUnduhBukti = function (
        array $item
    ): ?string {
        $jenis =
            $item['jenis']
            ?? null;

        $nomor =
            $item['nomor']
            ?? null;

        if (
            ! filled($jenis)
            || ! filled($nomor)
            || $nomor === '-'
            || ! \Illuminate\Support\Facades\Route
                ::has(
                    'pemohon-donor.riwayat.bukti.unduh'
                )
        ) {
            return null;
        }

        return route(
            'pemohon-donor.riwayat.bukti.unduh',
            [
                'jenis' => $jenis,
                'nomor' => $nomor,
            ]
        );
    };

    $filterSedangAktif =
        filled($q)
        || filled($jenisAktif);

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna->name;
@endphp

<x-layouts.pemohon-donor
    title="Riwayat Aktivitas"
    heading="Riwayat Aktivitas"
    description="Lihat perjalanan pengajuan kebutuhan darah dan proses distribusi."
    :pengguna="$pengguna"
    :profil="$profil"
>
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-7 text-white shadow-[0_22px_55px_rgba(118,0,28,0.2)] sm:px-8"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"
            ></div>

            <div
                class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-center"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.14em] text-white/65"
                    >
                        Riwayat Pemohon
                    </p>

                    <h2
                        class="mt-2 max-w-2xl text-3xl font-bold tracking-[-0.05em] sm:text-4xl"
                    >
                        Seluruh aktivitas dalam satu timeline
                    </h2>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-7 text-white/70"
                    >
                        Pantau perubahan status pengajuan kebutuhan darah dan
                        proses distribusi milik {{ $namaPemohon }}.
                    </p>
                </div>

                <article
                    class="rounded-[22px] border border-white/15 bg-white/10 p-5"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-white/60"
                    >
                        Aktivitas Ditampilkan
                    </p>

                    <strong
                        class="mt-3 block text-5xl font-bold leading-none tracking-[-0.06em]"
                    >
                        {{ number_format($totalRiwayat) }}
                    </strong>

                    <p
                        class="mt-3 text-xs leading-5 text-white/65"
                    >
                        Gabungan aktivitas pengajuan dan distribusi.
                    </p>
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
                            Riwayat akan tersedia setelah profil dilengkapi
                        </h2>

                        <p
                            class="mt-1 text-sm leading-6 text-[#755b61]"
                        >
                            Lengkapi informasi rumah sakit untuk mulai membuat pengajuan.
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
            class="grid grid-cols-1 gap-3 sm:grid-cols-3"
        >
            <article
                class="rounded-[22px] border border-[#e8e2df] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                        >
                            Total Aktivitas
                        </p>

                        <strong
                            class="mt-2 block text-4xl tracking-[-0.05em] text-[#191c20]"
                        >
                            {{ number_format($totalRiwayat) }}
                        </strong>

                        <p
                            class="mt-1 text-xs text-[#755f60]"
                        >
                            Sesuai filter aktif
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f1ecea] text-[#655253]"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M3 12a9 9 0 1 0 3-6.7" />
                            <path d="M3 3v6h6" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    </span>
                </div>
            </article>

            <article
                class="rounded-[22px] border border-[#f0d7dc] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                        >
                            Pengajuan
                        </p>

                        <strong
                            class="mt-2 block text-4xl tracking-[-0.05em] text-[#191c20]"
                        >
                            {{ number_format($totalPengajuan) }}
                        </strong>

                        <p
                            class="mt-1 text-xs text-[#755f60]"
                        >
                            Aktivitas kebutuhan darah
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#ffe7ec] text-[#991b2f]"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16h14V8Z" />
                            <path d="M14 2v6h6M8 13h8M8 17h5" />
                        </svg>
                    </span>
                </div>
            </article>

            <article
                class="rounded-[22px] border border-[#dce5f4] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex items-start justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                        >
                            Distribusi
                        </p>

                        <strong
                            class="mt-2 block text-4xl tracking-[-0.05em] text-[#191c20]"
                        >
                            {{ number_format($totalDistribusi) }}
                        </strong>

                        <p
                            class="mt-1 text-xs text-[#755f60]"
                        >
                            Aktivitas penyerahan darah
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
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
                </div>
            </article>
        </section>

        <section
            class="grid gap-3 sm:grid-cols-3"
        >
            <a
                href="{{ route('pemohon-donor.pengajuan.index') }}"
                class="flex items-center gap-4 rounded-[20px] border border-[#e8e2df] bg-white p-4 transition hover:border-[#e1c8cd] hover:bg-[#fffafb]"
            >
                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#ffe7ec] text-[#991b2f]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M14 2H6a2 2 0 0 0-2 2v16h14V8Z" />
                        <path d="M14 2v6h6M8 13h8M8 17h5" />
                    </svg>
                </span>

                <span>
                    <strong
                        class="block text-sm text-[#191c20]"
                    >
                        Buka Pengajuan
                    </strong>

                    <span
                        class="mt-1 block text-xs text-[#8c7071]"
                    >
                        Lihat seluruh kebutuhan darah.
                    </span>
                </span>
            </a>

            <a
                href="{{ route('pemohon-donor.distribusi.index') }}"
                class="flex items-center gap-4 rounded-[20px] border border-[#e8e2df] bg-white p-4 transition hover:border-[#d0dceb] hover:bg-[#f9fbff]"
            >
                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
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

                <span>
                    <strong
                        class="block text-sm text-[#191c20]"
                    >
                        Buka Distribusi
                    </strong>

                    <span
                        class="mt-1 block text-xs text-[#8c7071]"
                    >
                        Pantau jadwal penyerahan.
                    </span>
                </span>
            </a>

            <a
                href="{{ route('pemohon-donor.pengajuan.create') }}"
                class="flex items-center gap-4 rounded-[20px] border border-[#e8e2df] bg-white p-4 transition hover:border-[#cfe4d7] hover:bg-[#f8fff9]"
            >
                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#dff7e7] text-[#176b3a]"
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

                        <path d="M12 9v6M9 12h6" />
                    </svg>
                </span>

                <span>
                    <strong
                        class="block text-sm text-[#191c20]"
                    >
                        Pengajuan Baru
                    </strong>

                    <span
                        class="mt-1 block text-xs text-[#8c7071]"
                    >
                        Ajukan kebutuhan baru.
                    </span>
                </span>
            </a>
        </section>

        <section
            class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
        >
            <form
                method="GET"
                action="{{ route('pemohon-donor.riwayat.index') }}"
                class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px_auto]"
            >
                <div>
                    <label
                        for="pencarian-riwayat"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Cari Aktivitas
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
                            id="pencarian-riwayat"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Cari nomor, pasien, atau pengajuan..."
                            class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] pl-12 pr-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                        >
                    </div>
                </div>

                <div>
                    <label
                        for="jenis-riwayat"
                        class="mb-2 block text-sm font-semibold text-[#191c20]"
                    >
                        Jenis Aktivitas
                    </label>

                    <select
                        id="jenis-riwayat"
                        name="jenis"
                        class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                    >
                        <option value="">
                            Semua Jenis
                        </option>

                        <option
                            value="pengajuan"
                            @selected(
                                $jenisAktif ===
                                'pengajuan'
                            )
                        >
                            Pengajuan
                        </option>

                        <option
                            value="distribusi"
                            @selected(
                                $jenisAktif ===
                                'distribusi'
                            )
                        >
                            Distribusi
                        </option>
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
                            href="{{ route('pemohon-donor.riwayat.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-4 text-sm font-bold text-[#76001c]"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section
            class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
        >
            <div
                class="flex flex-col gap-2 border-b border-[#eee8e5] pb-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Timeline
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                    >
                        Aktivitas Terbaru
                    </h2>
                </div>

                <p
                    class="text-sm text-[#755f60]"
                >
                    Menampilkan {{ number_format($riwayat->count()) }} aktivitas.
                </p>
            </div>

            @if ($riwayat->isEmpty())
                <div
                    class="mt-6 rounded-2xl border border-dashed border-[#e5d5d8] bg-[#fff8f9] px-5 py-14 text-center"
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
                            <path d="M3 12a9 9 0 1 0 3-6.7" />
                            <path d="M3 3v6h6" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold text-[#191c20]"
                    >
                        Belum ada aktivitas
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#755f60]"
                    >
                        Belum ada riwayat atau tidak ada aktivitas yang cocok dengan filter.
                    </p>

                    <a
                        href="{{ route('pemohon-donor.pengajuan.create') }}"
                        class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                    >
                        Buat Pengajuan
                    </a>
                </div>
            @else
                <div class="relative mt-6 space-y-5">
                    <div
                        class="absolute bottom-8 left-5 top-8 hidden w-px bg-[#eadfe1] sm:block"
                    ></div>

                    @foreach ($riwayat as $item)
                        @php
                            $jenis =
                                $item['jenis']
                                ?? 'pengajuan';

                            $waktu =
                                $item['waktu']
                                ?? null;

                            $nomor =
                                $item['nomor']
                                ?? '-';

                            $keterangan =
                                $item['keterangan']
                                ?? '-';

                            $judul =
                                $item['judul']
                                ?? 'Aktivitas';

                            $deskripsi =
                                $item['deskripsi']
                                ?? '-';

                            $status =
                                $item['status']
                                ?? '-';

                            $buktiUrl =
                                $urlBukti($item);

                            $unduhBuktiUrl =
                                $urlUnduhBukti($item);

                            $jenisPengajuan =
                                $jenis === 'pengajuan';
                        @endphp

                        <article
                            class="relative grid gap-4 rounded-[22px] border border-[#eee8e5] bg-white p-5 sm:grid-cols-[42px_minmax(0,1fr)_auto]"
                        >
                            <span
                                @class([
                                    'relative z-10 flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold',
                                    'bg-[#ffe7ec] text-[#991b2f]' => $jenisPengajuan,
                                    'bg-[#e7effc] text-[#315b9b]' => ! $jenisPengajuan,
                                ])
                            >
                                {{ $jenisPengajuan ? 'P' : 'D' }}
                            </span>

                            <div class="min-w-0">
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <h3
                                        class="text-base font-bold text-[#191c20]"
                                    >
                                        {{ $judul }}
                                    </h3>

                                    <span
                                        @class([
                                            'inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold',
                                            'bg-[#ffe7ec] text-[#991b2f]' => $jenisPengajuan,
                                            'bg-[#e7effc] text-[#315b9b]' => ! $jenisPengajuan,
                                        ])
                                    >
                                        {{ $jenisPengajuan ? 'Pengajuan' : 'Distribusi' }}
                                    </span>

                                    <span
                                        class="inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold {{ $statusClass($status) }}"
                                    >
                                        {{ $statusLabel($status) }}
                                    </span>
                                </div>

                                <p
                                    class="mt-2 text-sm leading-6 text-[#584141]"
                                >
                                    {{ $deskripsi }}
                                </p>

                                <div
                                    class="mt-3 flex flex-wrap gap-2"
                                >
                                    <span
                                        class="inline-flex min-h-8 items-center rounded-lg bg-[#f7f4f2] px-3 text-xs font-semibold text-[#655253]"
                                    >
                                        Nomor: {{ $nomor }}
                                    </span>

                                    <span
                                        class="inline-flex min-h-8 items-center rounded-lg bg-[#f7f4f2] px-3 text-xs font-semibold text-[#655253]"
                                    >
                                        Referensi: {{ $keterangan }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex flex-col gap-3 sm:min-w-40 sm:items-end"
                            >
                                <time
                                    class="text-left text-xs font-semibold leading-5 text-[#8c7071] sm:text-right"
                                >
                                    {{ $waktu?->translatedFormat('d F Y') ?? '-' }}

                                    <br>

                                    {{ $waktu?->format('H:i') ?? '-' }}
                                    @if ($waktu !== null)
                                        WIB
                                    @endif
                                </time>

                                <div
                                    class="flex flex-wrap gap-2 sm:justify-end"
                                >
                                    <a
                                        href="{{ $urlData($item) }}"
                                        class="inline-flex min-h-9 items-center justify-center rounded-xl bg-[#f1ecea] px-3 text-xs font-bold text-[#655253]"
                                    >
                                        Buka Data
                                    </a>

                                    @if ($buktiUrl !== null)
                                        <a
                                            href="{{ $buktiUrl }}"
                                            class="inline-flex min-h-9 items-center justify-center rounded-xl bg-[#991b2f] px-3 text-xs font-bold text-white"
                                        >
                                            Bukti
                                        </a>
                                    @endif

                                    @if ($unduhBuktiUrl !== null)
                                        <a
                                            href="{{ $unduhBuktiUrl }}"
                                            class="inline-flex min-h-9 items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-3 text-xs font-bold text-[#76001c]"
                                        >
                                            Unduh
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.pemohon-donor>