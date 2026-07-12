@php
    $statusLabel = function (
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

        $nilai = $status instanceof \BackedEnum
            ? $status->value
            : (string) $status;

        return str($nilai)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->headline()
            ->toString();
    };

    $statusClass = function (
        mixed $status
    ): string {
        $nilai = $status instanceof \BackedEnum
            ? $status->value
            : (string) $status;

        return match ($nilai) {
            'submitted',
            'under_review',
            'waiting_for_stock',
            'draft',
            'diajukan',
            'ditinjau',
            'menunggu_stok',
            'draf' =>
                'bg-[#fff1c9] text-[#8a5a00]',

            'approved',
            'ready_for_pickup',
            'disetujui',
            'siap_diambil' =>
                'bg-[#e7effc] text-[#315b9b]',

            'completed',
            'selesai' =>
                'bg-[#dff7e7] text-[#176b3a]',

            'rejected',
            'cancelled',
            'ditolak',
            'dibatalkan' =>
                'bg-[#ffe4e7] text-[#991b2f]',

            default =>
                'bg-[#f1ecea] text-[#655253]',
        };
    };

    $urgensiLabel = function (
        mixed $urgensi
    ): string {
        if (
            is_object($urgensi)
            && method_exists(
                $urgensi,
                'label'
            )
        ) {
            return (string) $urgensi->label();
        }

        $nilai = $urgensi instanceof \BackedEnum
            ? $urgensi->value
            : (string) $urgensi;

        return str($nilai)
            ->replace('_', ' ')
            ->headline()
            ->toString();
    };

    $urgensiClass = function (
        mixed $urgensi
    ): string {
        $nilai = $urgensi instanceof \BackedEnum
            ? $urgensi->value
            : (string) $urgensi;

        return match ($nilai) {
            'urgent',
            'emergency',
            'mendesak',
            'darurat' =>
                'bg-[#ffe4e7] text-[#991b2f]',

            default =>
                'bg-[#fff1c9] text-[#8a5a00]',
        };
    };

    $golonganLabel = function (
        mixed $golongan
    ): string {
        if (
            is_object($golongan)
            && method_exists(
                $golongan,
                'label'
            )
        ) {
            return (string) $golongan->label();
        }

        return (string) (
            $golongan instanceof \BackedEnum
                ? $golongan->value
                : $golongan
        );
    };

    $rhesusSimbol = function (
        mixed $rhesus
    ): string {
        if (
            is_object($rhesus)
            && method_exists(
                $rhesus,
                'simbol'
            )
        ) {
            return (string) $rhesus->simbol();
        }

        $nilai = $rhesus instanceof \BackedEnum
            ? $rhesus->value
            : (string) $rhesus;

        return match ($nilai) {
            'positive',
            'positif',
            '+' => '+',

            'negative',
            'negatif',
            '-' => '-',

            default => $nilai,
        };
    };

    $distribusiTerbaru =
        $jadwalDistribusi->first();

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna->name;
@endphp

<x-layouts.pemohon-donor
    title="Dashboard Pemohon"
    heading="Dashboard Pemohon"
    :description="'Selamat datang kembali, ' . $namaPemohon . '.'"
    :pengguna="$pengguna"
    :profil="$profil"
    :notification-count="$pengajuanBaru"
>
    <div class="space-y-6">
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
                            Profil belum lengkap
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-[#3f0716]"
                        >
                            Lengkapi profil rumah sakit terlebih dahulu
                        </h2>

                        <p
                            class="mt-1 text-sm leading-6 text-[#755b61]"
                        >
                            Profil diperlukan sebelum membuat pengajuan kebutuhan darah.
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
            class="grid grid-cols-2 gap-3 xl:grid-cols-4"
        >
            <article
                class="rounded-[22px] border border-[#eadfe1] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#ffe7ec] text-[#991b2f]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M9 4h6a2 2 0 0 1 2 2v1H7V6a2 2 0 0 1 2-2Z" />
                        <path d="M8 7H6a2 2 0 0 0-2 2v11h12V9a2 2 0 0 0-2-2h-2" />
                    </svg>
                </div>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Pengajuan Baru
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($pengajuanBaru) }}
                </strong>

                <p
                    class="mt-1 text-xs text-[#755f60]"
                >
                    Menunggu diproses
                </p>
            </article>

            <article
                class="rounded-[22px] border border-[#eee0bd] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#fff1c9] text-[#8a5a00]"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                </div>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Sedang Diproses
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($diproses) }}
                </strong>

                <p
                    class="mt-1 text-xs text-[#755f60]"
                >
                    Dalam verifikasi
                </p>
            </article>

            <article
                class="rounded-[22px] border border-[#d9eadf] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#dff7e7] text-[#176b3a]"
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
                </div>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Siap Diterima
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($diterima) }}
                </strong>

                <p
                    class="mt-1 text-xs text-[#755f60]"
                >
                    Siap didistribusikan
                </p>
            </article>

            <article
                class="rounded-[22px] border border-[#dce5f4] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
            >
                <div
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
                </div>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                >
                    Distribusi
                </p>

                <strong
                    class="mt-1 block text-4xl tracking-[-0.05em] text-[#191c20]"
                >
                    {{ number_format($distribusi) }}
                </strong>

                <p
                    class="mt-1 text-xs text-[#755f60]"
                >
                    Distribusi tercatat
                </p>
            </article>
        </section>

        <section
            class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_340px]"
        >
            <div class="space-y-6">
                <article
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    <div
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                            >
                                Pengajuan
                            </p>

                            <h2
                                class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                            >
                                Pengajuan Terbaru
                            </h2>
                        </div>

                        <a
                            href="{{ route('pemohon-donor.pengajuan.index') }}"
                            class="text-sm font-bold text-[#991b2f]"
                        >
                            Lihat semua
                        </a>
                    </div>

                    @if ($pengajuanTerbaru->isEmpty())
                        <div
                            class="mt-6 rounded-2xl border border-dashed border-[#e5d5d8] bg-[#fff8f9] px-5 py-12 text-center"
                        >
                            <p
                                class="font-semibold text-[#584141]"
                            >
                                Belum ada pengajuan kebutuhan darah.
                            </p>

                            <a
                                href="{{ route('pemohon-donor.pengajuan.create') }}"
                                class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                            >
                                Buat Pengajuan
                            </a>
                        </div>
                    @else
                        <div
                            class="mt-6 overflow-x-auto rounded-2xl border border-[#eee8e5]"
                        >
                            <table
                                class="w-full min-w-[850px] border-collapse"
                            >
                                <thead class="bg-[#faf8f7]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Pengajuan
                                        </th>

                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Darah
                                        </th>

                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Kebutuhan
                                        </th>

                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Urgensi
                                        </th>

                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Status
                                        </th>

                                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($pengajuanTerbaru as $pengajuan)
                                        <tr
                                            class="border-t border-[#eee8e5]"
                                        >
                                            <td class="px-4 py-4">
                                                <strong
                                                    class="block text-sm text-[#191c20]"
                                                >
                                                    {{ $pengajuan->nomor_permintaan }}
                                                </strong>

                                                <span
                                                    class="mt-1 block text-xs text-[#8c7071]"
                                                >
                                                    {{ $pengajuan->referensi_pasien ?? 'Tanpa referensi pasien' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex min-h-9 items-center rounded-xl border border-[#efcbd2] bg-[#fff5f7] px-3 text-sm font-bold text-[#991b2f]"
                                                >
                                                    {{ $golonganLabel($pengajuan->golongan_darah) }}{{ $rhesusSimbol($pengajuan->rhesus) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <strong
                                                    class="text-sm text-[#191c20]"
                                                >
                                                    {{ number_format($pengajuan->jumlah_kantong) }} kantong
                                                </strong>

                                                <span
                                                    class="mt-1 block text-xs text-[#8c7071]"
                                                >
                                                    {{ $pengajuan->dibutuhkan_pada?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold {{ $urgensiClass($pengajuan->tingkat_urgensi) }}"
                                                >
                                                    {{ $urgensiLabel($pengajuan->tingkat_urgensi) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold {{ $statusClass($pengajuan->status) }}"
                                                >
                                                    {{ $statusLabel($pengajuan->status) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4 text-right">
                                                <a
                                                    href="{{ route('pemohon-donor.pengajuan.bukti', $pengajuan) }}"
                                                    class="inline-flex min-h-9 items-center justify-center rounded-xl bg-[#f1ecea] px-3 text-xs font-bold text-[#655253] hover:bg-[#ffe9ed] hover:text-[#991b2f]"
                                                >
                                                    Lihat Bukti
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>

                <article
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    <div
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                            >
                                Aktivitas
                            </p>

                            <h2
                                class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                            >
                                Riwayat Terbaru
                            </h2>
                        </div>

                        @if (
                            \Illuminate\Support\Facades\Route::has(
                                'pemohon-donor.riwayat.index'
                            )
                        )
                            <a
                                href="{{ route('pemohon-donor.riwayat.index') }}"
                                class="text-sm font-bold text-[#991b2f]"
                            >
                                Lihat semua
                            </a>
                        @endif
                    </div>

                    @if ($riwayatAktivitas->isEmpty())
                        <div
                            class="mt-6 rounded-2xl bg-[#faf8f7] px-5 py-10 text-center text-sm text-[#755f60]"
                        >
                            Belum ada aktivitas terbaru.
                        </div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($riwayatAktivitas as $aktivitas)
                                <article
                                    class="flex gap-4 rounded-2xl border border-[#eee8e5] p-4"
                                >
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#ffe7ec] text-[#991b2f]"
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

                                    <div>
                                        <p
                                            class="text-sm font-semibold leading-6 text-[#3f3030]"
                                        >
                                            Pengajuan
                                            <strong>{{ $aktivitas->nomor_permintaan }}</strong>
                                            diperbarui menjadi
                                            <strong>{{ mb_strtolower($statusLabel($aktivitas->status)) }}</strong>.
                                        </p>

                                        <time
                                            class="mt-1 block text-xs text-[#8c7071]"
                                        >
                                            {{ $aktivitas->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                                            WIB
                                        </time>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>

            <aside class="space-y-6">
                <article
                    class="rounded-[24px] bg-[#76001c] p-6 text-white shadow-[0_20px_50px_rgba(118,0,28,0.2)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.12em] text-white/65"
                    >
                        Aksi Cepat
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-[-0.04em]"
                    >
                        Apa yang ingin dilakukan?
                    </h2>

                    <div class="mt-6 space-y-3">
                        <a
                            href="{{ route('pemohon-donor.pengajuan.create') }}"
                            class="flex min-h-12 items-center justify-center rounded-xl bg-white px-4 text-sm font-bold text-[#76001c]"
                        >
                            Buat Pengajuan Baru
                        </a>

                        <a
                            href="{{ route('pemohon-donor.distribusi.index') }}"
                            class="flex min-h-12 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-bold text-white"
                        >
                            Lihat Distribusi
                        </a>

                        @if ($distribusiTerbaru !== null)
                            <a
                                href="{{ route('pemohon-donor.distribusi.bukti', $distribusiTerbaru) }}"
                                class="flex min-h-12 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-bold text-white"
                            >
                                Bukti Distribusi Terbaru
                            </a>
                        @else
                            <a
                                href="{{ route('pemohon-donor.pengajuan.bukti.terbaru') }}"
                                class="flex min-h-12 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-bold text-white"
                            >
                                Bukti Pengajuan Terbaru
                            </a>
                        @endif
                    </div>
                </article>

                <article
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <div
                        class="flex items-center justify-between gap-3"
                    >
                        <h2
                            class="text-lg font-bold tracking-[-0.03em] text-[#191c20]"
                        >
                            Jadwal Distribusi
                        </h2>

                        <a
                            href="{{ route('pemohon-donor.distribusi.index') }}"
                            class="text-xs font-bold text-[#991b2f]"
                        >
                            Lihat semua
                        </a>
                    </div>

                    @if ($jadwalDistribusi->isEmpty())
                        <div
                            class="mt-5 rounded-2xl bg-[#faf8f7] px-4 py-8 text-center text-sm text-[#755f60]"
                        >
                            Belum ada jadwal distribusi.
                        </div>
                    @else
                        <div class="mt-5 space-y-4">
                            @foreach ($jadwalDistribusi as $jadwal)
                                <article
                                    class="border-b border-[#eee8e5] pb-4 last:border-0 last:pb-0"
                                >
                                    <div class="flex gap-3">
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#ffe7ec] text-[#991b2f]"
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

                                        <div class="min-w-0">
                                            <strong
                                                class="block text-sm text-[#191c20]"
                                            >
                                                {{ $jadwal->dijadwalkan_pada?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                WIB
                                            </strong>

                                            <span
                                                class="mt-1 block truncate text-xs text-[#8c7071]"
                                            >
                                                {{ $jadwal->nomor_distribusi ?? '-' }}
                                            </span>

                                            <span
                                                class="mt-2 inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold {{ $statusClass($jadwal->status) }}"
                                            >
                                                {{ $statusLabel($jadwal->status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('pemohon-donor.distribusi.bukti', $jadwal) }}"
                                        class="mt-3 inline-flex text-xs font-bold text-[#991b2f]"
                                    >
                                        Lihat bukti distribusi
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </article>

                <article
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Informasi Pemohon
                    </p>

                    <h2
                        class="mt-2 text-lg font-bold tracking-[-0.03em] text-[#191c20]"
                    >
                        {{ $namaPemohon }}
                    </h2>

                    <dl class="mt-5 space-y-4">
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Kode Pemohon
                            </dt>

                            <dd
                                class="text-right text-sm font-bold text-[#191c20]"
                            >
                                {{ $profil?->kode_rumah_sakit ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Penanggung Jawab
                            </dt>

                            <dd
                                class="text-right text-sm font-bold text-[#191c20]"
                            >
                                {{ $profil?->nama_penanggung_jawab ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Kota
                            </dt>

                            <dd
                                class="text-right text-sm font-bold text-[#191c20]"
                            >
                                {{ $profil?->kota ?? '-' }}
                            </dd>
                        </div>
                    </dl>

                    <a
                        href="{{ route('pemohon-donor.profil.index') }}"
                        class="mt-6 flex min-h-11 items-center justify-center rounded-xl border border-[#eadde0] bg-[#fff8f9] px-4 text-sm font-bold text-[#991b2f]"
                    >
                        Lihat Profil Lengkap
                    </a>
                </article>
            </aside>
        </section>
    </div>
</x-layouts.pemohon-donor>