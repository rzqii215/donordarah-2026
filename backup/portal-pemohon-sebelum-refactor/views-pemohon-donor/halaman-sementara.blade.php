@php
    $pengguna = auth()->user();

    $profil = $pengguna !== null
        ? \App\Models\ProfilRumahSakit::query()
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first()
        : null;

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna?->name
        ?? 'Pemohon Donor';

    $daftarPanduan = [
        [
            'nomor' => '01',
            'judul' => 'Lengkapi Profil',
            'deskripsi' =>
                'Isi identitas rumah sakit, nomor izin, penanggung jawab, alamat, dan informasi lokasi.',
            'warna' => 'red',
        ],
        [
            'nomor' => '02',
            'judul' => 'Buat Pengajuan',
            'deskripsi' =>
                'Masukkan referensi pasien, dokter, golongan darah, rhesus, jumlah kantong, dan urgensi.',
            'warna' => 'orange',
        ],
        [
            'nomor' => '03',
            'judul' => 'Pantau Status',
            'deskripsi' =>
                'Periksa perubahan status pengajuan melalui Dashboard, Pengajuan, dan Riwayat.',
            'warna' => 'blue',
        ],
        [
            'nomor' => '04',
            'judul' => 'Cek Distribusi',
            'deskripsi' =>
                'Lihat jadwal penyerahan, status distribusi, serta bukti distribusi dari petugas.',
            'warna' => 'green',
        ],
    ];

    $daftarFaq = [
        [
            'pertanyaan' =>
                'Kenapa tombol Kirim Pengajuan tidak dapat digunakan?',
            'jawaban' =>
                'Pastikan profil rumah sakit sudah tersedia dan seluruh kolom wajib pada formulir pengajuan sudah diisi. Buka menu Profil untuk melengkapi identitas rumah sakit terlebih dahulu.',
        ],
        [
            'pertanyaan' =>
                'Kenapa status pengajuan belum berubah?',
            'jawaban' =>
                'Status pengajuan berubah setelah petugas memeriksa kebutuhan darah melalui panel admin. Pemohon dapat memantau perubahan status melalui Dashboard, Pengajuan, atau Riwayat.',
        ],
        [
            'pertanyaan' =>
                'Apa arti status Menunggu Stok?',
            'jawaban' =>
                'Status Menunggu Stok berarti pengajuan sudah diperiksa, tetapi jumlah kantong darah yang sesuai belum mencukupi. Petugas akan melanjutkan proses ketika stok tersedia.',
        ],
        [
            'pertanyaan' =>
                'Kenapa halaman Distribusi masih kosong?',
            'jawaban' =>
                'Distribusi baru tersedia setelah pengajuan disetujui, kebutuhan kantong darah terpenuhi, dan petugas membuat jadwal penyerahan.',
        ],
        [
            'pertanyaan' =>
                'Apa perbedaan bukti pengajuan dan bukti distribusi?',
            'jawaban' =>
                'Bukti pengajuan menunjukkan bahwa kebutuhan darah sudah tercatat. Bukti distribusi menunjukkan jadwal atau proses penyerahan kantong darah sudah dibuat oleh petugas.',
        ],
        [
            'pertanyaan' =>
                'Bagaimana cara mengunduh bukti pengajuan?',
            'jawaban' =>
                'Buka menu Pengajuan, cari data yang dibutuhkan, kemudian tekan tombol Unduh. Bukti terbaru juga dapat diunduh melalui tombol Bukti Pengajuan Terbaru pada Dashboard.',
        ],
        [
            'pertanyaan' =>
                'Apakah data rumah sakit dapat dilihat oleh pemohon lain?',
            'jawaban' =>
                'Tidak. Data profil, pengajuan, distribusi, dan riwayat dibatasi berdasarkan akun Pemohon Donor yang sedang login.',
        ],
        [
            'pertanyaan' =>
                'Apa yang harus dilakukan jika profil ditolak?',
            'jawaban' =>
                'Buka menu Profil, baca alasan penolakan, perbaiki informasi atau dokumen izin, lalu simpan kembali. Status akan dikembalikan menjadi menunggu verifikasi.',
        ],
        [
            'pertanyaan' =>
                'Bagaimana jika lupa password?',
            'jawaban' =>
                'Keluar dari akun, buka halaman Login, kemudian pilih Lupa Password. Sistem akan mengirimkan tautan pengaturan ulang password ke email akun.',
        ],
    ];
@endphp

<x-layouts.pemohon-donor
    :title="$judul ?? 'Bantuan'"
    :heading="$judul ?? 'Bantuan'"
    :description="$deskripsi ?? 'Pusat bantuan penggunaan Portal Pemohon Donor.'"
    :pengguna="$pengguna"
    :profil="$profil"
>
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-8 text-white shadow-[0_22px_55px_rgba(118,0,28,0.2)] sm:px-8"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/10"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-24 right-36 h-56 w-56 rounded-full bg-[#fdb7c5]/10"
            ></div>

            <div
                class="relative grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-center"
            >
                <div>
                    <span
                        class="inline-flex min-h-9 items-center rounded-full border border-white/15 bg-white/10 px-4 text-xs font-bold uppercase tracking-[0.12em]"
                    >
                        Panduan Portal
                    </span>

                    <h2
                        class="mt-5 max-w-3xl text-3xl font-bold tracking-[-0.05em] sm:text-4xl lg:text-5xl"
                    >
                        Ada yang bisa kami bantu?
                    </h2>

                    <p
                        class="mt-4 max-w-2xl text-sm leading-7 text-white/70 sm:text-base"
                    >
                        Pelajari cara membuat pengajuan kebutuhan darah, memantau
                        status, melihat distribusi, dan mengelola akun
                        {{ $namaPemohon }}.
                    </p>

                    <div
                        class="mt-6 flex flex-wrap gap-3"
                    >
                        <a
                            href="{{ route('pemohon-donor.pengajuan.create') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-white px-6 text-sm font-bold text-[#76001c]"
                        >
                            Buat Pengajuan
                        </a>

                        <a
                            href="{{ route('pemohon-donor.riwayat.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-6 text-sm font-bold text-white"
                        >
                            Lihat Riwayat
                        </a>
                    </div>
                </div>

                <article
                    class="rounded-[24px] border border-white/15 bg-white/10 p-6"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15"
                    >
                        <svg
                            class="h-7 w-7"
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

                            <path
                                d="M9.5 9a2.7 2.7 0 1 1 5 1.4c-.8 1.2-2.5 1.4-2.5 3.6"
                            />

                            <path d="M12 18h.01" />
                        </svg>
                    </div>

                    <h3
                        class="mt-5 text-xl font-bold"
                    >
                        Pusat Bantuan Pemohon
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-white/65"
                    >
                        Ikuti panduan penggunaan atau buka pertanyaan yang sering ditanyakan.
                    </p>
                </article>
            </div>
        </section>

        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            @foreach ($daftarPanduan as $panduan)
                @php
                    $warnaNomor = match (
                        $panduan['warna']
                    ) {
                        'orange' =>
                            'bg-[#fff1c9] text-[#8a5a00]',

                        'blue' =>
                            'bg-[#e7effc] text-[#315b9b]',

                        'green' =>
                            'bg-[#dff7e7] text-[#176b3a]',

                        default =>
                            'bg-[#ffe7ec] text-[#991b2f]',
                    };
                @endphp

                <article
                    class="rounded-[22px] border border-[#e8e2df] bg-white p-5 shadow-[0_14px_38px_rgba(25,28,32,0.05)]"
                >
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-bold {{ $warnaNomor }}"
                    >
                        {{ $panduan['nomor'] }}
                    </span>

                    <h3
                        class="mt-5 text-lg font-bold tracking-[-0.03em] text-[#191c20]"
                    >
                        {{ $panduan['judul'] }}
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-[#755f60]"
                    >
                        {{ $panduan['deskripsi'] }}
                    </p>
                </article>
            @endforeach
        </section>

        <section
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"
        >
            <article
                class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
            >
                <div
                    class="border-b border-[#eee8e5] pb-5"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Pertanyaan Umum
                    </p>

                    <h2
                        class="mt-1 text-2xl font-bold tracking-[-0.04em] text-[#191c20]"
                    >
                        Pertanyaan yang Sering Ditanyakan
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-[#755f60]"
                    >
                        Tekan pertanyaan untuk melihat jawabannya.
                    </p>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($daftarFaq as $index => $faq)
                        <details
                            class="group rounded-2xl border border-[#eee8e5] bg-white open:border-[#e4c9cf] open:bg-[#fffafb]"
                            @if ($index === 0)
                                open
                            @endif
                        >
                            <summary
                                class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4"
                            >
                                <span
                                    class="text-sm font-bold leading-6 text-[#191c20]"
                                >
                                    {{ $faq['pertanyaan'] }}
                                </span>

                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f4efed] text-[#76001c] transition group-open:rotate-45 group-open:bg-[#ffe7ec]"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M12 5v14M5 12h14" />
                                    </svg>
                                </span>
                            </summary>

                            <div
                                class="border-t border-[#eee8e5] px-5 py-4"
                            >
                                <p
                                    class="text-sm leading-7 text-[#655253]"
                                >
                                    {{ $faq['jawaban'] }}
                                </p>
                            </div>
                        </details>
                    @endforeach
                </div>
            </article>

            <aside class="space-y-5">
                <article
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Akses Cepat
                    </p>

                    <h2
                        class="mt-2 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                    >
                        Menu yang Sering Digunakan
                    </h2>

                    <div class="mt-5 space-y-3">
                        <a
                            href="{{ route('pemohon-donor.profil.index') }}"
                            class="flex min-h-12 items-center justify-between rounded-xl border border-[#eee8e5] px-4 text-sm font-bold text-[#584141] transition hover:border-[#e1c8cd] hover:bg-[#fffafb] hover:text-[#991b2f]"
                        >
                            Lengkapi Profil
                            <span>→</span>
                        </a>

                        <a
                            href="{{ route('pemohon-donor.pengajuan.index') }}"
                            class="flex min-h-12 items-center justify-between rounded-xl border border-[#eee8e5] px-4 text-sm font-bold text-[#584141] transition hover:border-[#e1c8cd] hover:bg-[#fffafb] hover:text-[#991b2f]"
                        >
                            Lihat Pengajuan
                            <span>→</span>
                        </a>

                        <a
                            href="{{ route('pemohon-donor.distribusi.index') }}"
                            class="flex min-h-12 items-center justify-between rounded-xl border border-[#eee8e5] px-4 text-sm font-bold text-[#584141] transition hover:border-[#d0dceb] hover:bg-[#f9fbff] hover:text-[#315b9b]"
                        >
                            Lihat Distribusi
                            <span>→</span>
                        </a>

                        <a
                            href="{{ route('pemohon-donor.riwayat.index') }}"
                            class="flex min-h-12 items-center justify-between rounded-xl border border-[#eee8e5] px-4 text-sm font-bold text-[#584141] transition hover:border-[#e1c8cd] hover:bg-[#fffafb] hover:text-[#991b2f]"
                        >
                            Lihat Riwayat
                            <span>→</span>
                        </a>

                        <a
                            href="{{ route('pemohon-donor.pengaturan.index') }}"
                            class="flex min-h-12 items-center justify-between rounded-xl border border-[#eee8e5] px-4 text-sm font-bold text-[#584141] transition hover:border-[#e1c8cd] hover:bg-[#fffafb] hover:text-[#991b2f]"
                        >
                            Pengaturan Akun
                            <span>→</span>
                        </a>
                    </div>
                </article>

                <article
                    class="rounded-[24px] border border-[#dce5f4] bg-[#f6f9ff] p-6"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
                    >
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                            />

                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </div>

                    <h2
                        class="mt-4 text-lg font-bold text-[#263f67]"
                    >
                        Keamanan Data
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-[#506787]"
                    >
                        Data pengajuan, distribusi, profil, dan riwayat hanya dapat
                        diakses oleh akun Pemohon Donor yang memilikinya.
                    </p>
                </article>

                <article
                    class="rounded-[24px] border border-[#eee0bd] bg-[#fffaf0] p-6"
                >
                    <h2
                        class="text-lg font-bold text-[#5f3d00]"
                    >
                        Masih Mengalami Kendala?
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-[#755516]"
                    >
                        Hubungi administrator sistem apabila akun tidak aktif,
                        role tidak sesuai, atau data tidak dapat diakses.
                    </p>

                    <div
                        class="mt-4 rounded-xl border border-[#eedaa8] bg-white/70 p-4"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.08em] text-[#8a5a00]"
                        >
                            Informasi yang perlu disiapkan
                        </p>

                        <ul
                            class="mt-3 space-y-2 text-sm text-[#755516]"
                        >
                            <li>• Email akun</li>
                            <li>• Kode pemohon</li>
                            <li>• Nomor pengajuan/distribusi</li>
                            <li>• Screenshot kendala</li>
                        </ul>
                    </div>
                </article>
            </aside>
        </section>
    </div>
</x-layouts.pemohon-donor>