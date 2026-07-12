@php
    /*
     * Normalisasi data agar tampilan tetap aman ketika
     * sebagian data profil atau riwayat belum tersedia.
     */
    $kelayakan = $statusKelayakan ?? [];

    $golonganRhesus = (string) data_get(
        $kelayakan,
        'golongan_rhesus',
        data_get(
            $kelayakan,
            'golongan_darah',
            data_get(
                $ringkasan,
                'golongan_rhesus',
                '-'
            )
        )
    );

    if (blank($golonganRhesus)) {
        $golonganRhesus = '-';
    }

    $labelKelayakan = trim(
        (string) data_get(
            $kelayakan,
            'label',
            data_get(
                $kelayakan,
                'status_label',
                'Data donor belum lengkap'
            )
        )
    );

    $rawBolehDonor = data_get(
        $kelayakan,
        'boleh_donor',
        data_get(
            $kelayakan,
            'dapat_donor',
            data_get(
                $kelayakan,
                'layak',
                null
            )
        )
    );

    $bolehDonor = filter_var(
        $rawBolehDonor,
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE
    );

    if ($bolehDonor === null) {
        $labelKelayakanKecil = mb_strtolower(
            $labelKelayakan
        );

        $bolehDonor =
            str_contains(
                $labelKelayakanKecil,
                'siap'
            )
            || str_contains(
                $labelKelayakanKecil,
                'layak donor'
            );
    }

    $persentaseKelayakan = (int) data_get(
        $kelayakan,
        'persentase',
        data_get(
            $kelayakan,
            'progress',
            data_get(
                $kelayakan,
                'persentase_interval',
                0
            )
        )
    );

    $persentaseKelayakan = max(
        0,
        min(
            100,
            $persentaseKelayakan
        )
    );

    $sisaHari = data_get(
        $kelayakan,
        'sisa_hari',
        data_get(
            $kelayakan,
            'hari_tersisa',
            null
        )
    );

    $deskripsiKelayakan = trim(
        (string) data_get(
            $kelayakan,
            'deskripsi',
            data_get(
                $kelayakan,
                'pesan',
                data_get(
                    $kelayakan,
                    'keterangan',
                    ''
                )
            )
        )
    );

    if ($deskripsiKelayakan === '') {
        $profilLengkap = (int) data_get(
            $ringkasan,
            'profil_lengkap',
            0
        );

        $deskripsiKelayakan = $profilLengkap < 100
            ? 'Lengkapi profil pendonor agar status kelayakan dapat dihitung dengan tepat.'
            : 'Status kelayakan akan diperbarui berdasarkan riwayat donor Anda.';
    }

    $labelProgressKanan = trim(
        (string) data_get(
            $kelayakan,
            'label_progress',
            ''
        )
    );

    if ($labelProgressKanan === '') {
        if (
            is_numeric($sisaHari)
            && (int) $sisaHari > 0
        ) {
            $labelProgressKanan =
                (int) $sisaHari
                . ' Hari Lagi';
        } elseif ($bolehDonor) {
            $labelProgressKanan = 'Siap Donor';
        } else {
            $labelProgressKanan = 'Menunggu Data';
        }
    }

    $jadwalUtama = $jadwalBerikutnya ?? null;

    if (is_array($jadwalUtama)) {
        $jadwalUtama = data_get(
            $jadwalUtama,
            'jadwal'
        );
    }

    if (
        $jadwalUtama === null
        && isset($jadwalTerdekat)
    ) {
        $jadwalUtama = collect(
            $jadwalTerdekat
        )->first();
    }

    $lokasiJadwal = $jadwalUtama?->lokasi;

    $sisaKuota = (
        $jadwalUtama
        && method_exists(
            $jadwalUtama,
            'sisaKuota'
        )
    )
        ? $jadwalUtama->sisaKuota()
        : null;

    $pendaftaranAktif = $pendaftaranTerakhir ?? null;

    $tahapan = collect(
        $tahapanPendaftaran ?? []
    );

    if (
        $tahapan->isEmpty()
        && $pendaftaranAktif instanceof
            \App\Models\PendaftaranDonor
    ) {
        $tahapan = collect([
            [
                'label' => 'Pendaftaran dikirim',
                'status' => 'selesai',
            ],
            [
                'label' => $this->labelStatusPendaftaran(
                    $pendaftaranAktif->status
                ),
                'status' => 'aktif',
            ],
        ]);
    }

    $totalDonor = (int) data_get(
        $ringkasan,
        'donor_selesai',
        0
    );

    $totalPendaftaran = (int) data_get(
        $ringkasan,
        'total_pendaftaran',
        0
    );

    $pendaftaranProses = (int) data_get(
        $ringkasan,
        'pendaftaran_proses',
        0
    );

    $terakhirDonorValue = data_get(
        $kelayakan,
        'terakhir_donor_pada',
        data_get(
            $kelayakan,
            'donor_terakhir',
            $profilPendonor?->terakhir_donor_pada
        )
    );

    if (
        $terakhirDonorValue instanceof
            \DateTimeInterface
    ) {
        $terakhirDonorLabel =
            \Carbon\Carbon::instance(
                $terakhirDonorValue
            )->translatedFormat('d F Y');
    } elseif (filled($terakhirDonorValue)) {
        $terakhirDonorLabel =
            (string) $terakhirDonorValue;
    } else {
        $terakhirDonorLabel = 'Belum ada';
    }
@endphp

<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- Kolom utama --}}
        <div class="space-y-6 xl:col-span-8">
            {{-- Status kelayakan --}}
            <section
                class="relative overflow-hidden rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.05)] sm:p-7"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-[#ffdada]/45 blur-3xl"
                    aria-hidden="true"
                ></div>

                <div class="relative z-10">
                    <h2 class="text-xl font-semibold tracking-[-0.025em] text-[#191c20] sm:text-[22px]">
                        Status Kelayakan
                    </h2>

                    <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border border-[#e0bfbf] bg-[#fcedef] text-2xl font-bold text-[#991b2f]"
                        >
                            {{ $golonganRhesus }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <span
                                @class([
                                    'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-[#eaf7f0] text-[#257a57]' => $bolehDonor,
                                    'bg-[#fff4de] text-[#b86e12]' => ! $bolehDonor,
                                ])
                            >
                                <span
                                    @class([
                                        'h-2 w-2 rounded-full',
                                        'bg-[#257a57]' => $bolehDonor,
                                        'bg-[#b86e12]' => ! $bolehDonor,
                                    ])
                                ></span>

                                {{ $labelKelayakan }}
                            </span>

                            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#584141]">
                                {{ $deskripsiKelayakan }}
                            </p>
                        </div>

                        <svg
                            viewBox="0 0 120 140"
                            class="hidden h-32 w-28 shrink-0 text-[#991b2f]/20 md:block"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M60 8C45 32 17 58 17 91C17 116 36 134 60 134C84 134 103 116 103 91C103 58 75 32 60 8Z"
                                stroke="currentColor"
                                stroke-width="11"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M40 92C40 105 49 114 61 114"
                                stroke="currentColor"
                                stroke-width="9"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>

                    <div class="mt-5">
                        <div class="h-2 overflow-hidden rounded-full bg-[#e2e2e9]">
                            <div
                                @class([
                                    'h-full rounded-full transition-all duration-500',
                                    'bg-[#257a57]' => $bolehDonor,
                                    'bg-[#76001c]' => ! $bolehDonor,
                                ])
                                style="width: {{ $persentaseKelayakan }}%;"
                            ></div>
                        </div>

                        <div class="mt-2 flex items-center justify-between gap-4 text-xs font-bold text-[#8c7071]">
                            <span>Donor Terakhir</span>

                            <span class="text-right">
                                {{ $labelProgressKanan }}
                            </span>
                        </div>
                    </div>

                    <a
                        href="{{ route('donor.riwayat') }}"
                        wire:navigate
                        class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#24262b] transition hover:bg-[#f3f3fa]"
                    >
                        Lihat riwayat donor
                    </a>
                </div>
            </section>

            {{-- Jadwal dan dampak --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Jadwal berikutnya --}}
                <section
                    class="flex min-h-[330px] flex-col rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.05)] sm:p-6"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-5 [&>svg]:w-5"
                        >
                            <x-donor.icon name="calendar" />
                        </span>

                        <h2 class="text-lg font-semibold tracking-[-0.025em] text-[#191c20]">
                            Jadwal Donor Berikutnya
                        </h2>
                    </div>

                    @if (
                        $jadwalUtama instanceof
                            \App\Models\JadwalDonor
                    )
                        <div class="mt-5 flex flex-1 flex-col">
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#ededf4] text-[#584141] [&>svg]:h-5 [&>svg]:w-5"
                                >
                                    <x-donor.icon name="map-pin" />
                                </span>

                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold leading-6 text-[#191c20]">
                                        {{ $this->namaLokasi($lokasiJadwal) }}
                                    </h3>

                                    <p class="mt-1 text-sm leading-5 text-[#584141]">
                                        {{ $this->tanggalJadwal($jadwalUtama) }}
                                    </p>

                                    <p class="text-sm leading-5 text-[#584141]">
                                        {{ $this->jamJadwal($jadwalUtama) }}
                                        WIB
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-[#e2e2e9] bg-[#f3f3fa] px-4 py-3">
                                <span class="text-sm text-[#584141]">
                                    Sisa kuota
                                </span>

                                <strong class="text-sm font-bold text-[#76001c]">
                                    {{ $sisaKuota ?? 0 }} orang
                                </strong>
                            </div>

                            <div class="mt-auto pt-5">
                                <a
                                    href="{{ route('donor.jadwal') }}"
                                    wire:navigate
                                    class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white transition hover:bg-[#991b2f]"
                                >
                                    Lihat detail
                                </a>

                                @if ($lokasiJadwal)
                                    <a
                                        href="{{ $this->mapsUrl($lokasiJadwal) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-2 inline-flex min-h-10 w-full items-center justify-center text-xs font-semibold text-[#76001c] hover:underline"
                                    >
                                        Buka lokasi di peta
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex flex-1 flex-col items-center justify-center py-8 text-center">
                            <span
                                class="flex h-14 w-14 items-center justify-center rounded-full bg-[#f3f3fa] text-[#8c7071] [&>svg]:h-6 [&>svg]:w-6"
                            >
                                <x-donor.icon name="calendar" />
                            </span>

                            <h3 class="mt-4 text-sm font-semibold text-[#191c20]">
                                Belum ada jadwal tersedia
                            </h3>

                            <p class="mt-2 max-w-xs text-sm leading-6 text-[#584141]">
                                Jadwal donor berikutnya akan muncul setelah dipublikasikan petugas.
                            </p>

                            <a
                                href="{{ route('donor.jadwal') }}"
                                wire:navigate
                                class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                            >
                                Periksa jadwal
                            </a>
                        </div>
                    @endif
                </section>

                {{-- Ringkasan dampak --}}
                <section
                    class="flex min-h-[330px] flex-col rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.05)] sm:p-6"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#fcedef] text-[#991b2f] [&>svg]:h-5 [&>svg]:w-5"
                        >
                            <x-donor.icon name="droplet" />
                        </span>

                        <h2 class="text-lg font-semibold tracking-[-0.025em] text-[#191c20]">
                            Ringkasan Dampak
                        </h2>
                    </div>

                    <div class="mt-5 grid flex-1 grid-cols-2 gap-4">
                        <article class="flex flex-col items-center justify-center rounded-2xl border border-[#e6e3df] bg-white p-4 text-center">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#fcedef] text-[#c52a3d] [&>svg]:h-5 [&>svg]:w-5"
                            >
                                <x-donor.icon name="droplet" />
                            </span>

                            <strong class="mt-3 text-[28px] font-bold leading-none text-[#191c20]">
                                {{ number_format($totalDonor) }}
                            </strong>

                            <span class="mt-3 text-[11px] font-bold uppercase tracking-[0.09em] text-[#584141]">
                                Donor Selesai
                            </span>
                        </article>

                        <article class="flex flex-col items-center justify-center rounded-2xl border border-[#e6e3df] bg-white p-4 text-center">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#fcedef] text-[#c52a3d] [&>svg]:h-5 [&>svg]:w-5"
                            >
                                <x-donor.icon name="history" />
                            </span>

                            <strong class="mt-3 text-[28px] font-bold leading-none text-[#191c20]">
                                {{ number_format($totalPendaftaran) }}
                            </strong>

                            <span class="mt-3 text-[11px] font-bold uppercase tracking-[0.09em] text-[#584141]">
                                Pendaftaran
                            </span>
                        </article>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4 border-t border-[#e2e2e9] pt-4">
                        <span class="text-sm text-[#584141]">
                            Donor terakhir
                        </span>

                        <strong class="text-right text-sm font-semibold text-[#191c20]">
                            {{ $terakhirDonorLabel }}
                        </strong>
                    </div>

                    @if ($pendaftaranProses > 0)
                        <p class="mt-3 text-xs text-[#8c7071]">
                            {{ $pendaftaranProses }}
                            pendaftaran sedang diproses.
                        </p>
                    @endif
                </section>
            </div>
        </div>

        {{-- Timeline kanan --}}
        <aside class="xl:col-span-4">
            <section
                class="h-full min-h-[460px] rounded-2xl border border-[#e6e3df] bg-white p-5 transition duration-200 hover:shadow-[0_4px_14px_rgba(36,38,43,0.05)] sm:p-6"
            >
                <div>
                    <h2 class="text-lg font-semibold tracking-[-0.025em] text-[#191c20]">
                        Status Pendaftaran Terakhir
                    </h2>

                    @if (
                        $pendaftaranAktif instanceof
                            \App\Models\PendaftaranDonor
                    )
                        <p class="mt-1 text-xs text-[#8c7071]">
                            {{ $this->nomorPendaftaran($pendaftaranAktif) }}
                        </p>
                    @endif
                </div>

                @if ($tahapan->isNotEmpty())
                    <div class="relative mt-7 pl-8">
                        <div
                            class="absolute bottom-6 left-[11px] top-3 w-px bg-[#e2e2e9]"
                            aria-hidden="true"
                        ></div>

                        <div class="space-y-7">
                            @foreach ($tahapan as $tahap)
                                @php
                                    $judulTahap = is_string($tahap)
                                        ? $tahap
                                        : (string) data_get(
                                            $tahap,
                                            'label',
                                            data_get(
                                                $tahap,
                                                'judul',
                                                'Tahap Pendaftaran'
                                            )
                                        );

                                    $statusTahap = mb_strtolower(
                                        (string) data_get(
                                            $tahap,
                                            'status',
                                            data_get(
                                                $tahap,
                                                'state',
                                                'menunggu'
                                            )
                                        )
                                    );

                                    $isDone =
                                        (bool) data_get(
                                            $tahap,
                                            'selesai',
                                            false
                                        )
                                        || in_array(
                                            $statusTahap,
                                            [
                                                'done',
                                                'completed',
                                                'complete',
                                                'selesai',
                                                'success',
                                            ],
                                            true
                                        );

                                    $isCurrent =
                                        (bool) data_get(
                                            $tahap,
                                            'aktif',
                                            false
                                        )
                                        || in_array(
                                            $statusTahap,
                                            [
                                                'active',
                                                'current',
                                                'aktif',
                                                'saat_ini',
                                                'process',
                                                'processing',
                                            ],
                                            true
                                        );

                                    $isFailed = in_array(
                                        $statusTahap,
                                        [
                                            'failed',
                                            'rejected',
                                            'ditolak',
                                            'cancelled',
                                            'dibatalkan',
                                            'ineligible',
                                        ],
                                        true
                                    );

                                    $keteranganTahap = trim(
                                        (string) data_get(
                                            $tahap,
                                            'keterangan',
                                            data_get(
                                                $tahap,
                                                'status_label',
                                                ''
                                            )
                                        )
                                    );

                                    if ($keteranganTahap === '') {
                                        $keteranganTahap = match (true) {
                                            $isDone => 'Selesai',
                                            $isCurrent => 'Saat ini',
                                            $isFailed => 'Berhenti',
                                            default => 'Menunggu',
                                        };
                                    }
                                @endphp

                                <div class="relative">
                                    <span
                                        @class([
                                            'absolute -left-8 top-0 z-10 flex h-6 w-6 items-center justify-center rounded-full border',
                                            'border-[#257a57] bg-[#eaf7f0] text-[#257a57]' => $isDone,
                                            'border-[#76001c] bg-[#76001c] text-white ring-4 ring-[#ffdada]/70' => $isCurrent && ! $isFailed,
                                            'border-[#ba1a1a] bg-[#ffdad6] text-[#ba1a1a]' => $isFailed,
                                            'border-[#d9d9e0] bg-[#ededf4] text-[#8c7071]' => ! $isDone && ! $isCurrent && ! $isFailed,
                                        ])
                                    >
                                        @if ($isDone)
                                            <svg
                                                viewBox="0 0 20 20"
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="m4 10 4 4 8-9"
                                                    stroke="currentColor"
                                                    stroke-width="2.2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        @elseif ($isCurrent && ! $isFailed)
                                            <span class="h-2 w-2 rounded-full bg-white"></span>
                                        @elseif ($isFailed)
                                            <span class="text-xs font-bold">!</span>
                                        @else
                                            <span class="h-2 w-2 rounded-full bg-[#d9d9e0]"></span>
                                        @endif
                                    </span>

                                    <div class="pt-0.5">
                                        <p
                                            @class([
                                                'text-sm leading-5',
                                                'font-semibold text-[#191c20]' => $isDone,
                                                'font-bold text-[#76001c]' => $isCurrent && ! $isFailed,
                                                'font-bold text-[#ba1a1a]' => $isFailed,
                                                'font-normal text-[#584141]' => ! $isDone && ! $isCurrent && ! $isFailed,
                                            ])
                                        >
                                            {{ $judulTahap }}
                                        </p>

                                        <p
                                            @class([
                                                'mt-1 text-[10px] font-bold uppercase tracking-[0.08em]',
                                                'text-[#257a57]' => $isDone,
                                                'text-[#991b2f]' => $isCurrent && ! $isFailed,
                                                'text-[#ba1a1a]' => $isFailed,
                                                'text-[#8c7071]' => ! $isDone && ! $isCurrent && ! $isFailed,
                                            ])
                                        >
                                            {{ $keteranganTahap }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <a
                        href="{{ route('donor.riwayat') }}"
                        wire:navigate
                        class="mt-8 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                    >
                        Lihat riwayat lengkap
                    </a>
                @else
                    <div class="flex min-h-[330px] flex-col items-center justify-center text-center">
                        <span
                            class="flex h-14 w-14 items-center justify-center rounded-full bg-[#f3f3fa] text-[#8c7071] [&>svg]:h-6 [&>svg]:w-6"
                        >
                            <x-donor.icon name="history" />
                        </span>

                        <h3 class="mt-4 text-sm font-semibold text-[#191c20]">
                            Belum ada pendaftaran
                        </h3>

                        <p class="mt-2 max-w-xs text-sm leading-6 text-[#584141]">
                            Status proses donor akan tampil setelah Anda melakukan pendaftaran.
                        </p>

                        <a
                            href="{{ route('donor.jadwal') }}"
                            wire:navigate
                            class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#c52a3d] px-5 text-sm font-semibold text-white transition hover:bg-[#991b2f]"
                        >
                            Cari jadwal donor
                        </a>
                    </div>
                @endif
            </section>
        </aside>
    </div>

    <div class="flex flex-col gap-3 rounded-2xl border border-[#e6e3df] bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#191c20]">
                Informasi dashboard berasal dari data terbaru sistem.
            </p>

            <p class="mt-1 text-xs leading-5 text-[#8c7071]">
                Perubahan status pendaftaran, jadwal, dan riwayat donor akan diperbarui otomatis.
            </p>
        </div>

        <a
            href="{{ route('donor.profil') }}"
            wire:navigate
            class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-[#e6e3df] px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
        >
            Periksa profil
        </a>
    </div>
</div>