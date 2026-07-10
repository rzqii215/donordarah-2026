<div class="donor-schedule-page">
    
<section class="donor-schedule-hero">
        <div>
            <p class="donor-schedule-eyebrow">
                Jadwal Donor
            </p>

            <h1>
                Pilih Jadwal Donor
            </h1>

            <p>
                Lihat jadwal donor yang tersedia, cek detail lokasi,
                buka peta kegiatan, lalu daftar pada jadwal yang paling sesuai.
            </p>
        </div>

        <div class="donor-schedule-search">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>

            <input
                type="search"
                wire:model.live.debounce.400ms="pencarian"
                placeholder="Cari jadwal, lokasi, atau kota..."
            >
        </div>
    </section>

    @if (session('success'))
        <div class="donor-schedule-alert is-success">
            {{ session('success') }}
        </div>
    @endif

    @error('jadwal')
        <div class="donor-schedule-alert is-danger">
            {{ $message }}
        </div>
    @enderror

    @if ($jadwalDonors->isEmpty())
        <section class="donor-schedule-empty">
            <div class="donor-schedule-empty-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M16 3v4M8 3v4M3 10h18" />
                </svg>
            </div>

            <h2>
                Jadwal donor belum tersedia
            </h2>

            <p>
                Belum ada jadwal donor yang dapat ditampilkan.
                Silakan cek kembali nanti.
            </p>
        </section>
    @else
        <section class="donor-schedule-grid">
            @foreach ($jadwalDonors as $jadwal)
                @php
                    $lokasi = $jadwal->lokasi;
                    $pendaftaran = $pendaftaranJadwals->get($jadwal->id);
                    $catatanLokasi = $this->catatanLokasi($lokasi);
                @endphp

                <article
                    class="donor-schedule-card"
                    wire:key="jadwal-donor-{{ $jadwal->id }}"
                >
                    <div class="donor-schedule-map">
                        <iframe
                            src="{{ $this->embedMapsUrl($lokasi) }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Peta {{ $this->namaLokasi($lokasi) }}"
                        ></iframe>

                        <div class="donor-schedule-map-badge">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
                                />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                        </div>
                    </div>

                    <div class="donor-schedule-body">
                        <div class="donor-schedule-date">
                            <div>
                                <span>Tanggal</span>
                                <strong>{{ $this->tanggalJadwal($jadwal) }}</strong>
                            </div>

                            <div>
                                <span>Waktu</span>
                                <strong>{{ $this->jamJadwal($jadwal) }}</strong>
                            </div>
                        </div>

                        <h2>
                            {{ $this->judulJadwal($jadwal) }}
                        </h2>

                        <p class="donor-schedule-description">
                            {{ $this->deskripsiJadwal($jadwal) }}
                        </p>

                        <div class="donor-schedule-location">
                            <h3>
                                {{ $this->namaLokasi($lokasi) }}
                            </h3>

                            <p>
                                {{ $this->alamatLokasi($lokasi) }}
                            </p>

                            <span>
                                {{ $this->wilayahLokasi($lokasi) }}
                            </span>
                        </div>

                        <div class="donor-schedule-info">
                            <div>
                                <span>Kuota</span>
                                <strong>{{ $this->kuotaJadwal($jadwal) }}</strong>
                            </div>

                            <div>
                                <span>Pendaftaran</span>
                                <strong>{{ $this->periodePendaftaran($jadwal) }}</strong>
                            </div>
                        </div>

                        @if ($catatanLokasi !== '-')
                            <div class="donor-schedule-note">
                                <span>Catatan Lokasi</span>
                                <p>{{ $catatanLokasi }}</p>
                            </div>
                        @endif

                        <div class="donor-schedule-actions">
                            @if ($pendaftaran)
                                <span
                                    class="donor-schedule-status {{ $this->statusBadgeClass($pendaftaran->status) }}"
                                >
                                    {{ $this->labelStatusPendaftaran($pendaftaran->status) }}
                                </span>
                            @elseif ($this->jadwalDapatDidaftar($jadwal))
                                <button
                                    type="button"
                                    wire:click="daftarJadwal({{ $jadwal->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="daftarJadwal({{ $jadwal->id }})"
                                >
                                    <span wire:loading.remove wire:target="daftarJadwal({{ $jadwal->id }})">
                                        Daftar Donor
                                    </span>

                                    <span wire:loading wire:target="daftarJadwal({{ $jadwal->id }})">
                                        Memproses...
                                    </span>
                                </button>
                            @else
                                <span class="donor-schedule-status is-muted">
                                    Pendaftaran Tidak Tersedia
                                </span>
                            @endif

                            <button
                                type="button"
                                class="is-outline"
                                wire:click="pilihJadwal({{ $jadwal->id }})"
                            >
                                Lihat Detail
                            </button>

                            <a
                                href="{{ $this->mapsUrl($lokasi) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Buka Peta
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($jadwalTerpilih !== null)
        @php
            $lokasiTerpilih = $jadwalTerpilih->lokasi;
            $catatanLokasiTerpilih = $this->catatanLokasi($lokasiTerpilih);
        @endphp

        <div
            class="donor-schedule-modal-backdrop"
            wire:click="tutupDetailJadwal"
        >
            <section
                class="donor-schedule-modal"
                wire:click.stop
            >
                <div class="donor-schedule-modal-header">
                    <div>
                        <p>Detail Jadwal Donor</p>

                        <h2>
                            {{ $this->judulJadwal($jadwalTerpilih) }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        wire:click="tutupDetailJadwal"
                        aria-label="Tutup detail jadwal"
                    >
                        ×
                    </button>
                </div>

                <div class="donor-schedule-modal-map">
                    <iframe
                        src="{{ $this->embedMapsUrl($lokasiTerpilih) }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Peta {{ $this->namaLokasi($lokasiTerpilih) }}"
                    ></iframe>
                </div>

                <div class="donor-schedule-modal-content">
                    <div>
                        <span>Jadwal</span>
                        <strong>
                            {{ $this->tanggalJadwal($jadwalTerpilih) }},
                            {{ $this->jamJadwal($jadwalTerpilih) }}
                        </strong>
                    </div>

                    <div>
                        <span>Lokasi</span>
                        <strong>{{ $this->namaLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Alamat</span>
                        <strong>{{ $this->alamatLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Wilayah</span>
                        <strong>{{ $this->wilayahLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Kontak Lokasi</span>
                        <strong>{{ $this->kontakLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Kuota</span>
                        <strong>{{ $this->kuotaJadwal($jadwalTerpilih) }}</strong>
                    </div>

                    <div class="donor-schedule-modal-full">
                        <span>Periode Pendaftaran</span>
                        <strong>{{ $this->periodePendaftaran($jadwalTerpilih) }}</strong>
                    </div>

                    <div class="donor-schedule-modal-full">
                        <span>Catatan Lokasi</span>
                        <strong>{{ $catatanLokasiTerpilih }}</strong>
                    </div>
                </div>

                <div class="donor-schedule-modal-actions">
                    <a
                        href="{{ $this->mapsUrl($lokasiTerpilih) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Buka Google Maps
                    </a>

                    <button
                        type="button"
                        wire:click="tutupDetailJadwal"
                    >
                        Tutup
                    </button>
                </div>
            </section>
        </div>
    @endif

    <style>
        .donor-schedule-page {
            display: grid;
            gap: 28px;
        }

        .donor-schedule-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
            gap: 24px;
            align-items: end;
        }

        .donor-schedule-eyebrow {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .donor-schedule-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(36px, 5vw, 54px);
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .donor-schedule-hero p:not(.donor-schedule-eyebrow) {
            max-width: 760px;
            margin: 16px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.8;
        }

        .donor-schedule-search {
            min-height: 56px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 18px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 16px 42px rgba(15, 23, 42, 0.06);
        }

        .donor-schedule-search svg {
            width: 21px;
            height: 21px;
            color: #94a3b8;
        }

        .donor-schedule-search input {
            width: 100%;
            border: 0;
            outline: 0;
            color: #0f172a;
            background: transparent;
            font: inherit;
            font-size: 14px;
        }

        .donor-schedule-alert {
            padding: 16px 18px;
            border-radius: 18px;
            font-size: 14px;
            font-weight: 800;
        }

        .donor-schedule-alert.is-success {
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
        }

        .donor-schedule-alert.is-danger {
            border: 1px solid #fecaca;
            color: #b91c1c;
            background: #fff1f2;
        }

        .donor-schedule-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .donor-schedule-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 26px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .donor-schedule-map {
            position: relative;
            height: 210px;
            overflow: hidden;
            background: #f1f5f9;
        }

        .donor-schedule-map iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .donor-schedule-map-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            transform: translate(-50%, -50%);
            border: 8px solid rgba(254, 202, 202, 0.6);
            border-radius: 999px;
            color: #ffffff;
            background: #dc2626;
            box-shadow: 0 16px 34px rgba(220, 38, 38, 0.28);
            pointer-events: none;
        }

        .donor-schedule-map-badge svg {
            width: 23px;
            height: 23px;
        }

        .donor-schedule-body {
            padding: 24px;
        }

        .donor-schedule-date {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .donor-schedule-date div,
        .donor-schedule-info div {
            display: grid;
            gap: 6px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #f8fafc;
        }

        .donor-schedule-date span,
        .donor-schedule-info span {
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .donor-schedule-date strong,
        .donor-schedule-info strong {
            color: #0f172a;
            font-size: 13px;
            line-height: 1.5;
        }

        .donor-schedule-body h2 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            line-height: 1.25;
            letter-spacing: -0.04em;
        }

        .donor-schedule-description {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .donor-schedule-location {
            margin-top: 18px;
            padding: 16px;
            border: 1px solid #fee2e2;
            border-radius: 18px;
            background: #fff7f7;
        }

        .donor-schedule-location h3 {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            letter-spacing: -0.02em;
        }

        .donor-schedule-location p {
            margin: 8px 0 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-schedule-location span {
            display: inline-flex;
            margin-top: 10px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-schedule-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .donor-schedule-note {
            margin-top: 18px;
            padding: 15px;
            border: 1px solid #fee2e2;
            border-radius: 18px;
            background: #fff7f7;
        }

        .donor-schedule-note span {
            display: block;
            margin-bottom: 6px;
            color: #dc2626;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .donor-schedule-note p {
            margin: 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-schedule-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .donor-schedule-actions button,
        .donor-schedule-actions a,
        .donor-schedule-status {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .donor-schedule-actions button {
            border: 0;
            color: #ffffff;
            background: #dc2626;
            cursor: pointer;
            box-shadow: 0 14px 26px rgba(220, 38, 38, 0.18);
        }

        .donor-schedule-actions button.is-outline,
        .donor-schedule-actions a {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
            box-shadow: none;
        }

        .donor-schedule-actions button:hover,
        .donor-schedule-actions a:hover {
            transform: translateY(-1px);
        }

        .donor-schedule-status.is-success {
            color: #166534;
            background: #dcfce7;
        }

        .donor-schedule-status.is-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .donor-schedule-status.is-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-schedule-status.is-muted {
            color: #475569;
            background: #f1f5f9;
        }

        .donor-schedule-empty {
            display: grid;
            place-items: center;
            padding: 60px 24px;
            border: 1px dashed #fecaca;
            border-radius: 26px;
            background: #fff7f7;
            text-align: center;
        }

        .donor-schedule-empty-icon {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            color: #dc2626;
            background: #fee2e2;
        }

        .donor-schedule-empty-icon svg {
            width: 34px;
            height: 34px;
        }

        .donor-schedule-empty h2 {
            margin: 18px 0 8px;
            color: #0f172a;
        }

        .donor-schedule-empty p {
            margin: 0;
            max-width: 420px;
            color: #64748b;
            line-height: 1.7;
        }

        .donor-schedule-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(8px);
        }

        .donor-schedule-modal {
            width: min(100%, 900px);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 32px 90px rgba(15, 23, 42, 0.28);
        }

        .donor-schedule-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 26px 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .donor-schedule-modal-header p {
            margin: 0 0 8px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .donor-schedule-modal-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 28px;
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .donor-schedule-modal-header button {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 14px;
            color: #0f172a;
            background: #f1f5f9;
            font-size: 28px;
            cursor: pointer;
        }

        .donor-schedule-modal-map {
            height: 360px;
            background: #f1f5f9;
        }

        .donor-schedule-modal-map iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .donor-schedule-modal-content {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 24px 28px;
        }

        .donor-schedule-modal-content div {
            display: grid;
            gap: 7px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .donor-schedule-modal-content .donor-schedule-modal-full {
            grid-column: 1 / -1;
            border-color: #fee2e2;
            background: #fff7f7;
        }

        .donor-schedule-modal-content span {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-schedule-modal-content strong {
            color: #0f172a;
            font-size: 14px;
            line-height: 1.6;
        }

        .donor-schedule-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 0 28px 28px;
        }

        .donor-schedule-modal-actions a,
        .donor-schedule-modal-actions button {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .donor-schedule-modal-actions a {
            border: 0;
            color: #ffffff;
            background: #dc2626;
        }

        .donor-schedule-modal-actions button {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        @media (max-width: 980px) {
            .donor-schedule-hero,
            .donor-schedule-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .donor-schedule-date,
            .donor-schedule-info,
            .donor-schedule-modal-content {
                grid-template-columns: 1fr;
            }

            .donor-schedule-modal-content .donor-schedule-modal-full {
                grid-column: auto;
            }

            .donor-schedule-actions,
            .donor-schedule-modal-actions {
                flex-direction: column;
            }

            .donor-schedule-actions button,
            .donor-schedule-actions a,
            .donor-schedule-status,
            .donor-schedule-modal-actions a,
            .donor-schedule-modal-actions button {
                width: 100%;
            }
        }
    </style>
</div>