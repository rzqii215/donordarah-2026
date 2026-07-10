<div class="donor-home-page">
    

    @include('components.shared.safe-flash-message')
<section class="donor-home-hero">
        <div class="donor-home-hero-content">
            <p class="donor-home-eyebrow">
                Portal Pendonor
            </p>

            <h1>
                Selamat datang, {{ $ringkasan['nama_user'] }}
            </h1>

            <p>
                Pantau jadwal donor, lokasi kegiatan, stok darah, dan riwayat
                donor Anda dalam satu halaman yang terhubung langsung dengan data terbaru.
            </p>

            <div class="donor-home-hero-actions">
                <a href="{{ route('donor.jadwal') }}">
                    Cari Jadwal Donor
                </a>

                <a href="{{ route('donor.riwayat') }}" class="is-outline">
                    Lihat Riwayat
                </a>
            </div>
        </div>

        <div class="donor-home-profile-card">
            <span>Profil Pendonor</span>

            <strong>
                {{ $ringkasan['profil_lengkap'] }}%
            </strong>

            <div class="donor-home-progress">
                <div style="width: {{ $ringkasan['profil_lengkap'] }}%;"></div>
            </div>

            <p>
                Kode: {{ $ringkasan['kode_pendonor'] }}
            </p>

            <p>
                Golongan darah: {{ $ringkasan['golongan_rhesus'] }}
            </p>

            <a href="{{ route('donor.profil') }}">
                Lengkapi Profil
            </a>
        </div>
    </section>

    <section class="donor-home-summary">
        <article>
            <span>Total Pendaftaran</span>
            <strong>{{ $ringkasan['total_pendaftaran'] }}</strong>
            <p>Semua riwayat donor Anda</p>
        </article>

        <article>
            <span>Dalam Proses</span>
            <strong>{{ $ringkasan['pendaftaran_proses'] }}</strong>
            <p>Menunggu atau sedang berjalan</p>
        </article>

        <article>
            <span>Donor Selesai</span>
            <strong>{{ $ringkasan['donor_selesai'] }}</strong>
            <p>Donor berhasil diselesaikan</p>
        </article>

        <article>
            <span>Stok Tersedia</span>
            <strong>{{ $ringkasan['stok_tersedia'] }}</strong>
            <p>Kantong darah siap digunakan</p>
        </article>
    </section>

    <section class="donor-home-layout">
        <div class="donor-home-main">
            <section class="donor-home-panel">
                <div class="donor-home-panel-header">
                    <div>
                        <p>Jadwal Terdekat</p>
                        <h2>Donor yang dapat Anda ikuti</h2>
                    </div>

                    <a href="{{ route('donor.jadwal') }}">
                        Lihat Semua
                    </a>
                </div>

                <div class="donor-home-schedule-list">
                    @forelse ($jadwalTerdekat as $jadwal)
                        @php
                            $lokasi = $jadwal->lokasi;
                        @endphp

                        <article class="donor-home-schedule-card">
                            <div class="donor-home-date-box">
                                <strong>
                                    {{ $this->tanggalJadwal($jadwal) }}
                                </strong>

                                <span>
                                    {{ $this->jamJadwal($jadwal) }}
                                </span>
                            </div>

                            <div>
                                <h3>
                                    {{ $this->judulJadwal($jadwal) }}
                                </h3>

                                <p>
                                    {{ $this->namaLokasi($lokasi) }}
                                </p>

                                <small>
                                    {{ $this->alamatLokasi($lokasi) }}
                                </small>
                            </div>

                            <div class="donor-home-schedule-actions">
                                <a href="{{ route('donor.jadwal') }}">
                                    Detail
                                </a>

                                <a
                                    href="{{ $this->mapsUrl($lokasi) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="is-outline"
                                >
                                    Peta
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="donor-home-empty">
                            <h3>Belum ada jadwal aktif</h3>
                            <p>Silakan cek kembali nanti.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="donor-home-panel">
                <div class="donor-home-panel-header">
                    <div>
                        <p>Riwayat Terbaru</p>
                        <h2>Perjalanan donor Anda</h2>
                    </div>

                    <a href="{{ route('donor.riwayat') }}">
                        Lihat Semua
                    </a>
                </div>

                <div class="donor-home-history-list">
                    @forelse ($riwayatTerbaru as $pendaftaran)
                        @php
                            $jadwal = $pendaftaran->jadwal;
                            $lokasi = $jadwal?->lokasi;
                        @endphp

                        <article class="donor-home-history-card">
                            <div>
                                <span>
                                    {{ $this->nomorPendaftaran($pendaftaran) }}
                                </span>

                                <h3>
                                    {{ $this->judulJadwal($jadwal) }}
                                </h3>

                                <p>
                                    {{ $this->tanggalJadwal($jadwal) }},
                                    {{ $this->jamJadwal($jadwal) }}
                                </p>

                                <small>
                                    {{ $this->namaLokasi($lokasi) }}
                                </small>
                            </div>

                            <strong class="{{ $this->statusBadgeClass($pendaftaran->status) }}">
                                {{ $this->labelStatusPendaftaran($pendaftaran->status) }}
                            </strong>
                        </article>
                    @empty
                        <div class="donor-home-empty">
                            <h3>Belum ada riwayat</h3>
                            <p>Mulai daftar donor dari menu Jadwal Donor.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="donor-home-side">
            <section class="donor-home-panel">
                <div class="donor-home-panel-header">
                    <div>
                        <p>Stok Darah</p>
                        <h2>Ringkasan tersedia</h2>
                    </div>

                    <a href="{{ route('donor.stok') }}">
                        Detail
                    </a>
                </div>

                <div class="donor-home-stock-grid">
                    @foreach ($stokRingkas as $stok)
                        <article class="donor-home-stock-card">
                            <div>
                                <strong>{{ $stok['golongan'] }}</strong>

                                <span class="{{ $stok['class'] }}">
                                    {{ $stok['status'] }}
                                </span>
                            </div>

                            <p>
                                {{ $stok['total'] }} kantong
                            </p>

                            <small>
                                Rh+ {{ $stok['positif'] }} · Rh- {{ $stok['negatif'] }}
                            </small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="donor-home-panel">
                <div class="donor-home-panel-header">
                    <div>
                        <p>Lokasi Donor</p>
                        <h2>{{ $ringkasan['lokasi_aktif'] }} lokasi aktif</h2>
                    </div>

                    <a href="{{ route('donor.lokasi') }}">
                        Semua
                    </a>
                </div>

                <div class="donor-home-location-list">
                    @forelse ($lokasiTerdekat as $lokasi)
                        <article>
                            <h3>
                                {{ $this->namaLokasi($lokasi) }}
                            </h3>

                            <p>
                                {{ $this->alamatLokasi($lokasi) }}
                            </p>

                            <small>
                                {{ $this->wilayahLokasi($lokasi) }}
                            </small>

                            <a
                                href="{{ $this->mapsUrl($lokasi) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Buka Peta
                            </a>
                        </article>
                    @empty
                        <div class="donor-home-empty">
                            <h3>Lokasi belum tersedia</h3>
                            <p>Petugas belum menambahkan lokasi aktif.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>

    <section class="donor-home-cta">
        <div>
            <p>Siap berdonor?</p>

            <h2>
                Satu pendaftaran Anda bisa membantu banyak pasien.
            </h2>
        </div>

        <div>
            <a href="{{ route('donor.jadwal') }}">
                Pilih Jadwal
            </a>

            <a href="{{ route('donor.lokasi') }}" class="is-outline">
                Cari Lokasi
            </a>
        </div>
    </section>

    <style>
        .donor-home-page {
            display: grid;
            gap: 28px;
        }

        .donor-home-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 24px;
            align-items: stretch;
            padding: 34px;
            border: 1px solid #fee2e2;
            border-radius: 30px;
            background:
                radial-gradient(circle at top right, rgba(239, 68, 68, 0.16), transparent 22rem),
                linear-gradient(135deg, #fff1f2 0%, #ffffff 72%);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.07);
        }

        .donor-home-eyebrow,
        .donor-home-panel-header p,
        .donor-home-cta p {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .donor-home-hero h1 {
            max-width: 820px;
            margin: 0;
            color: #0f172a;
            font-size: clamp(38px, 6vw, 64px);
            line-height: 1.03;
            letter-spacing: -0.065em;
        }

        .donor-home-hero-content > p:not(.donor-home-eyebrow) {
            max-width: 720px;
            margin: 18px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.85;
        }

        .donor-home-hero-actions,
        .donor-home-cta > div:last-child {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 26px;
        }

        .donor-home-hero-actions a,
        .donor-home-profile-card a,
        .donor-home-panel-header a,
        .donor-home-schedule-actions a,
        .donor-home-location-list a,
        .donor-home-cta a {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border-radius: 14px;
            color: #ffffff;
            background: #dc2626;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            transition: 160ms ease;
        }

        .donor-home-hero-actions a.is-outline,
        .donor-home-schedule-actions a.is-outline,
        .donor-home-cta a.is-outline {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        .donor-home-profile-card {
            display: grid;
            align-content: start;
            gap: 12px;
            padding: 24px;
            border: 1px solid #fecaca;
            border-radius: 26px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(220, 38, 38, 0.08);
        }

        .donor-home-profile-card span {
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .donor-home-profile-card strong {
            color: #0f172a;
            font-size: 54px;
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .donor-home-profile-card p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .donor-home-progress {
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .donor-home-progress div {
            height: 100%;
            border-radius: 999px;
            background: #dc2626;
        }

        .donor-home-profile-card a {
            margin-top: 6px;
        }

        .donor-home-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-home-summary article,
        .donor-home-panel {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.06);
        }

        .donor-home-summary article {
            padding: 22px;
        }

        .donor-home-summary span {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .donor-home-summary strong {
            display: block;
            margin-top: 8px;
            color: #0f172a;
            font-size: 36px;
            line-height: 1;
        }

        .donor-home-summary p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .donor-home-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 390px;
            gap: 22px;
            align-items: start;
        }

        .donor-home-main,
        .donor-home-side {
            display: grid;
            gap: 22px;
        }

        .donor-home-panel {
            overflow: hidden;
        }

        .donor-home-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 22px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .donor-home-panel-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            line-height: 1.25;
            letter-spacing: -0.035em;
        }

        .donor-home-panel-header a {
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid #fee2e2;
            color: #dc2626;
            background: #fff7f7;
            white-space: nowrap;
        }

        .donor-home-schedule-list,
        .donor-home-history-list,
        .donor-home-location-list {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .donor-home-schedule-card {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #f8fafc;
        }

        .donor-home-date-box {
            display: grid;
            gap: 6px;
            padding: 15px;
            border-radius: 16px;
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-home-date-box strong {
            font-size: 14px;
            line-height: 1.35;
        }

        .donor-home-date-box span {
            font-size: 12px;
            font-weight: 900;
        }

        .donor-home-schedule-card h3,
        .donor-home-history-card h3,
        .donor-home-location-list h3 {
            margin: 0;
            color: #0f172a;
            font-size: 17px;
            line-height: 1.35;
            letter-spacing: -0.02em;
        }

        .donor-home-schedule-card p,
        .donor-home-history-card p,
        .donor-home-location-list p {
            margin: 7px 0 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.65;
        }

        .donor-home-schedule-card small,
        .donor-home-history-card small,
        .donor-home-location-list small {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .donor-home-schedule-actions {
            display: flex;
            gap: 8px;
        }

        .donor-home-schedule-actions a {
            min-height: 38px;
            padding: 0 14px;
        }

        .donor-home-history-card {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #f8fafc;
        }

        .donor-home-history-card span {
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-home-history-card > strong {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 12px;
            white-space: nowrap;
        }

        .donor-home-history-card > strong.is-success {
            color: #166534;
            background: #dcfce7;
        }

        .donor-home-history-card > strong.is-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .donor-home-history-card > strong.is-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-home-stock-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 18px;
        }

        .donor-home-stock-card {
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .donor-home-stock-card div {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .donor-home-stock-card strong {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #dc2626;
            background: #fee2e2;
            font-size: 20px;
            font-weight: 1000;
        }

        .donor-home-stock-card span {
            min-height: 28px;
            display: inline-flex;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
        }

        .donor-home-stock-card span.is-success {
            color: #166534;
            background: #dcfce7;
        }

        .donor-home-stock-card span.is-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .donor-home-stock-card span.is-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-home-stock-card p {
            margin: 14px 0 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 900;
        }

        .donor-home-stock-card small {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .donor-home-location-list article {
            padding: 18px;
            border: 1px solid #fee2e2;
            border-radius: 20px;
            background: #fff7f7;
        }

        .donor-home-location-list a {
            min-height: 38px;
            margin-top: 12px;
            padding: 0 14px;
        }

        .donor-home-empty {
            padding: 22px;
            border: 1px dashed #fecaca;
            border-radius: 18px;
            background: #fff7f7;
        }

        .donor-home-empty h3 {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
        }

        .donor-home-empty p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-home-cta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 26px;
            border: 1px solid #fee2e2;
            border-radius: 28px;
            background: #fff7f7;
        }

        .donor-home-cta h2 {
            max-width: 720px;
            margin: 0;
            color: #0f172a;
            font-size: 28px;
            line-height: 1.2;
            letter-spacing: -0.045em;
        }

        .donor-home-cta > div:last-child {
            margin-top: 0;
        }

        @media (max-width: 1180px) {
            .donor-home-layout {
                grid-template-columns: 1fr;
            }

            .donor-home-side {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 980px) {
            .donor-home-hero,
            .donor-home-summary,
            .donor-home-side {
                grid-template-columns: 1fr;
            }

            .donor-home-schedule-card {
                grid-template-columns: 1fr;
            }

            .donor-home-schedule-actions {
                flex-wrap: wrap;
            }

            .donor-home-cta {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .donor-home-hero {
                padding: 24px;
            }

            .donor-home-stock-grid {
                grid-template-columns: 1fr;
            }

            .donor-home-history-card {
                flex-direction: column;
            }

            .donor-home-hero-actions,
            .donor-home-cta > div:last-child {
                flex-direction: column;
                width: 100%;
            }

            .donor-home-hero-actions a,
            .donor-home-cta a,
            .donor-home-schedule-actions a {
                width: 100%;
            }
        }
    </style>
</div>