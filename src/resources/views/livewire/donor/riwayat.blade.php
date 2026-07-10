<div class="donor-history-page">
    
<section class="donor-history-hero">
        <div>
            <p class="donor-history-eyebrow">
                Riwayat Donor
            </p>

            <h1>
                Perjalanan Donor Anda
            </h1>

            <p>
                Pantau semua riwayat pendaftaran donor, mulai dari menunggu
                verifikasi, disetujui, hadir, pemeriksaan kesehatan, hingga donor selesai.
            </p>
        </div>

        <div class="donor-history-hero-card">
            <span>Total Riwayat</span>
            <strong>{{ $ringkasan['total'] }}</strong>
            <p>Pendaftaran donor tercatat</p>
        </div>
    </section>

    <section class="donor-history-summary">
        <article>
            <span>Total</span>
            <strong>{{ $ringkasan['total'] }}</strong>
            <p>Semua pendaftaran</p>
        </article>

        <article>
            <span>Diproses</span>
            <strong>{{ $ringkasan['proses'] }}</strong>
            <p>Masih berjalan</p>
        </article>

        <article>
            <span>Selesai</span>
            <strong>{{ $ringkasan['selesai'] }}</strong>
            <p>Donor selesai</p>
        </article>

        <article>
            <span>Tidak Lanjut</span>
            <strong>{{ $ringkasan['bermasalah'] }}</strong>
            <p>Ditolak / batal / tidak layak</p>
        </article>
    </section>

    <section class="donor-history-filter">
        @foreach ($this->opsiFilterStatus() as $opsi)
            <button
                type="button"
                wire:click="$set('filterStatus', '{{ $opsi['value'] }}')"
                class="{{ $filterStatus === $opsi['value'] ? 'is-active' : '' }}"
            >
                {{ $opsi['label'] }}
            </button>
        @endforeach
    </section>

    @if ($riwayatDonors->isEmpty())
        <section class="donor-history-empty">
            <div class="donor-history-empty-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M3 3v5h5" />
                    <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8" />
                    <path d="M12 7v5l3 2" />
                </svg>
            </div>

            <h2>
                Riwayat belum tersedia
            </h2>

            <p>
                Belum ada pendaftaran donor pada kategori ini.
                Silakan pilih jadwal donor untuk mulai mendaftar.
            </p>

            <a href="{{ route('donor.jadwal') }}">
                Lihat Jadwal Donor
            </a>
        </section>
    @else
        <section class="donor-history-list">
            @foreach ($riwayatDonors as $pendaftaran)
                @php
                    $jadwal = $pendaftaran->jadwal;
                    $lokasi = $jadwal?->lokasi;
                @endphp

                <article
                    class="donor-history-card"
                    wire:key="riwayat-donor-{{ $pendaftaran->id }}"
                >
                    <div class="donor-history-card-header">
                        <div>
                            <span>
                                {{ $this->nomorPendaftaran($pendaftaran) }}
                            </span>

                            <h2>
                                {{ $this->judulJadwal($jadwal) }}
                            </h2>

                            <p>
                                {{ $this->tanggalJadwal($jadwal) }},
                                {{ $this->jamJadwal($jadwal) }}
                            </p>
                        </div>

                        <span
                            class="donor-history-status {{ $this->statusBadgeClass($pendaftaran->status) }}"
                        >
                            {{ $this->labelStatusPendaftaran($pendaftaran->status) }}
                        </span>
                    </div>

                    <div class="donor-history-location">
                        <div>
                            <span>Lokasi</span>
                            <strong>{{ $this->namaLokasi($lokasi) }}</strong>
                            <p>{{ $this->alamatLokasi($lokasi) }}</p>
                            <small>{{ $this->wilayahLokasi($lokasi) }}</small>
                        </div>

                        <a
                            href="{{ $this->mapsUrl($lokasi) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Buka Peta
                        </a>
                    </div>

                    <div class="donor-history-timeline">
                        @foreach ($this->timeline($pendaftaran) as $step)
                            <div class="donor-history-step {{ $step['class'] }}">
                                <div class="donor-history-step-marker">
                                    <span></span>
                                </div>

                                <div>
                                    <strong>{{ $step['label'] }}</strong>
                                    <p>{{ $step['description'] }}</p>
                                    <small>{{ $step['tanggal'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="donor-history-actions">
                        <button
                            type="button"
                            wire:click="pilihRiwayat({{ $pendaftaran->id }})"
                        >
                            Lihat Detail
                        </button>

                        <a href="{{ route('donor.jadwal') }}">
                            Jadwal Lain
                        </a>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($pendaftaranTerpilih !== null)
        @php
            $jadwalTerpilih = $pendaftaranTerpilih->jadwal;
            $lokasiTerpilih = $jadwalTerpilih?->lokasi;
        @endphp

        <div
            class="donor-history-modal-backdrop"
            wire:click="tutupDetailRiwayat"
        >
            <section
                class="donor-history-modal"
                wire:click.stop
            >
                <div class="donor-history-modal-header">
                    <div>
                        <p>Detail Riwayat Donor</p>

                        <h2>
                            {{ $this->nomorPendaftaran($pendaftaranTerpilih) }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        wire:click="tutupDetailRiwayat"
                        aria-label="Tutup detail riwayat"
                    >
                        ×
                    </button>
                </div>

                <div class="donor-history-modal-content">
                    <div>
                        <span>Status</span>
                        <strong>
                            {{ $this->labelStatusPendaftaran($pendaftaranTerpilih->status) }}
                        </strong>
                    </div>

                    <div>
                        <span>Jadwal</span>
                        <strong>{{ $this->judulJadwal($jadwalTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Tanggal</span>
                        <strong>
                            {{ $this->tanggalJadwal($jadwalTerpilih) }},
                            {{ $this->jamJadwal($jadwalTerpilih) }}
                        </strong>
                    </div>

                    <div>
                        <span>Lokasi</span>
                        <strong>{{ $this->namaLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div class="donor-history-modal-full">
                        <span>Alamat</span>
                        <strong>{{ $this->alamatLokasi($lokasiTerpilih) }}</strong>
                    </div>

                    <div>
                        <span>Dibuat Pada</span>
                        <strong>{{ $this->tanggalFormat($pendaftaranTerpilih->created_at) }}</strong>
                    </div>

                    <div>
                        <span>Hadir Pada</span>
                        <strong>{{ $this->tanggalFormat($pendaftaranTerpilih->hadir_pada) }}</strong>
                    </div>

                    <div>
                        <span>Selesai Pada</span>
                        <strong>{{ $this->tanggalFormat($pendaftaranTerpilih->selesai_pada) }}</strong>
                    </div>

                    <div>
                        <span>Kantong Darah</span>
                        <strong>
                            {{ $pendaftaranTerpilih->kantongDarah?->kode_kantong ?? '-' }}
                        </strong>
                    </div>

                    <div class="donor-history-modal-full">
                        <span>Catatan / Alasan</span>
                        <strong>{{ $this->alasanStatus($pendaftaranTerpilih) }}</strong>
                    </div>
                </div>

                <div class="donor-history-modal-actions">
                    <a
                        href="{{ $this->mapsUrl($lokasiTerpilih) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Buka Google Maps
                    </a>

                    <button
                        type="button"
                        wire:click="tutupDetailRiwayat"
                    >
                        Tutup
                    </button>
                </div>
            </section>
        </div>
    @endif

    <style>
        .donor-history-page {
            display: grid;
            gap: 28px;
        }

        .donor-history-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 24px;
            align-items: end;
        }

        .donor-history-eyebrow {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .donor-history-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(36px, 5vw, 54px);
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .donor-history-hero p:not(.donor-history-eyebrow) {
            max-width: 760px;
            margin: 16px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.8;
        }

        .donor-history-hero-card {
            padding: 24px;
            border: 1px solid #fee2e2;
            border-radius: 26px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .donor-history-hero-card span {
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .donor-history-hero-card strong {
            display: block;
            margin-top: 12px;
            color: #0f172a;
            font-size: 54px;
            line-height: 1;
        }

        .donor-history-hero-card p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .donor-history-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-history-summary article {
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.05);
        }

        .donor-history-summary span {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .donor-history-summary strong {
            display: block;
            margin-top: 8px;
            color: #0f172a;
            font-size: 34px;
            line-height: 1;
        }

        .donor-history-summary p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .donor-history-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .donor-history-filter button {
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #334155;
            background: #ffffff;
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .donor-history-filter button.is-active {
            border-color: #dc2626;
            color: #ffffff;
            background: #dc2626;
            box-shadow: 0 14px 28px rgba(220, 38, 38, 0.18);
        }

        .donor-history-list {
            display: grid;
            gap: 22px;
        }

        .donor-history-card {
            padding: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 26px;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .donor-history-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .donor-history-card-header span:first-child {
            display: inline-flex;
            margin-bottom: 8px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
        }

        .donor-history-card-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            line-height: 1.25;
            letter-spacing: -0.04em;
        }

        .donor-history-card-header p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .donor-history-status {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            padding: 0 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .donor-history-status.is-success {
            color: #166534;
            background: #dcfce7;
        }

        .donor-history-status.is-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .donor-history-status.is-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-history-location {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            margin-top: 20px;
            padding: 18px;
            border: 1px solid #fee2e2;
            border-radius: 20px;
            background: #fff7f7;
        }

        .donor-history-location span {
            color: #dc2626;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .donor-history-location strong {
            display: block;
            margin-top: 6px;
            color: #0f172a;
            font-size: 16px;
        }

        .donor-history-location p {
            margin: 8px 0 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-history-location small {
            display: block;
            margin-top: 8px;
            color: #64748b;
            font-weight: 800;
        }

        .donor-history-location a,
        .donor-history-actions button,
        .donor-history-actions a,
        .donor-history-empty a,
        .donor-history-modal-actions a,
        .donor-history-modal-actions button {
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

        .donor-history-location a,
        .donor-history-actions button,
        .donor-history-empty a,
        .donor-history-modal-actions a {
            border: 0;
            color: #ffffff;
            background: #dc2626;
        }

        .donor-history-timeline {
            display: grid;
            gap: 0;
            margin-top: 24px;
        }

        .donor-history-step {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 14px;
            position: relative;
            padding-bottom: 20px;
        }

        .donor-history-step:not(:last-child)::before {
            content: "";
            position: absolute;
            left: 16px;
            top: 32px;
            bottom: -2px;
            width: 2px;
            background: #e2e8f0;
        }

        .donor-history-step-marker {
            position: relative;
            z-index: 1;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .donor-history-step-marker span {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #94a3b8;
        }

        .donor-history-step strong {
            display: block;
            color: #0f172a;
            font-size: 15px;
        }

        .donor-history-step p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-history-step small {
            display: block;
            margin-top: 6px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 800;
        }

        .donor-history-step.is-done .donor-history-step-marker,
        .donor-history-step.is-active .donor-history-step-marker {
            background: #dcfce7;
        }

        .donor-history-step.is-done .donor-history-step-marker span,
        .donor-history-step.is-active .donor-history-step-marker span {
            background: #16a34a;
        }

        .donor-history-step.is-danger .donor-history-step-marker {
            background: #fee2e2;
        }

        .donor-history-step.is-danger .donor-history-step-marker span {
            background: #dc2626;
        }

        .donor-history-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 4px;
        }

        .donor-history-actions button {
            font: inherit;
            cursor: pointer;
        }

        .donor-history-actions a,
        .donor-history-modal-actions button {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        .donor-history-empty {
            display: grid;
            place-items: center;
            padding: 60px 24px;
            border: 1px dashed #fecaca;
            border-radius: 26px;
            background: #fff7f7;
            text-align: center;
        }

        .donor-history-empty-icon {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            color: #dc2626;
            background: #fee2e2;
        }

        .donor-history-empty-icon svg {
            width: 34px;
            height: 34px;
        }

        .donor-history-empty h2 {
            margin: 18px 0 8px;
            color: #0f172a;
        }

        .donor-history-empty p {
            margin: 0 0 20px;
            max-width: 420px;
            color: #64748b;
            line-height: 1.7;
        }

        .donor-history-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(8px);
        }

        .donor-history-modal {
            width: min(100%, 900px);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 32px 90px rgba(15, 23, 42, 0.28);
        }

        .donor-history-modal-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 26px 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .donor-history-modal-header p {
            margin: 0 0 8px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .donor-history-modal-header h2 {
            margin: 0;
            color: #0f172a;
            font-size: 28px;
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .donor-history-modal-header button {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 14px;
            color: #0f172a;
            background: #f1f5f9;
            font-size: 28px;
            cursor: pointer;
        }

        .donor-history-modal-content {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 24px 28px;
        }

        .donor-history-modal-content div {
            display: grid;
            gap: 7px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .donor-history-modal-content .donor-history-modal-full {
            grid-column: 1 / -1;
            border-color: #fee2e2;
            background: #fff7f7;
        }

        .donor-history-modal-content span {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-history-modal-content strong {
            color: #0f172a;
            font-size: 14px;
            line-height: 1.6;
        }

        .donor-history-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 0 28px 28px;
        }

        @media (max-width: 980px) {
            .donor-history-hero,
            .donor-history-summary {
                grid-template-columns: 1fr;
            }

            .donor-history-location {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .donor-history-card-header,
            .donor-history-modal-actions {
                flex-direction: column;
            }

            .donor-history-modal-content {
                grid-template-columns: 1fr;
            }

            .donor-history-modal-content .donor-history-modal-full {
                grid-column: auto;
            }

            .donor-history-actions,
            .donor-history-modal-actions {
                flex-direction: column;
            }

            .donor-history-actions button,
            .donor-history-actions a,
            .donor-history-location a,
            .donor-history-modal-actions a,
            .donor-history-modal-actions button {
                width: 100%;
            }
        }
    </style>
</div>