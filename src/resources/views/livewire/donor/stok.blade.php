<div class="donor-stock-page">
    
<section class="donor-stock-hero">
        <div>
            <p class="donor-stock-eyebrow">
                Informasi Stok Darah
            </p>

            <h1>
                Stok Darah
            </h1>

            <p>
                Pantau ketersediaan darah berdasarkan golongan darah dan rhesus.
                Stok tersedia hanya menghitung kantong darah yang sudah lulus mutu
                dan belum dialokasikan.
            </p>
        </div>

        <div class="donor-stock-hero-card">
            <span>Total Tersedia</span>

            <strong>
                {{ $ringkasan['tersedia'] }}
            </strong>

            <p>
                Kantong darah siap digunakan
            </p>
        </div>
    </section>

    <section class="donor-stock-summary">
        <article>
            <div class="donor-stock-summary-icon is-success">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <div>
                <span>Tersedia</span>
                <strong>{{ $ringkasan['tersedia'] }}</strong>
                <p>Siap dialokasikan</p>
            </div>
        </article>

        <article>
            <div class="donor-stock-summary-icon is-warning">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 8v5" />
                    <path d="M12 17h.01" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
            </div>

            <div>
                <span>Dipesan</span>
                <strong>{{ $ringkasan['dipesan'] }}</strong>
                <p>Sudah dialokasikan</p>
            </div>
        </article>

        <article>
            <div class="donor-stock-summary-icon is-info">
                <svg
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

            <div>
                <span>Didistribusikan</span>
                <strong>{{ $ringkasan['didistribusikan'] }}</strong>
                <p>Sudah diserahkan</p>
            </div>
        </article>

        <article>
            <div class="donor-stock-summary-icon is-muted">
                <svg
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

            <div>
                <span>Lulus Mutu</span>
                <strong>{{ $ringkasan['lulus_mutu'] }}</strong>
                <p>Total kantong valid</p>
            </div>
        </article>
    </section>

    <section class="donor-stock-grid">
        @foreach ($stokDarah as $stok)
            <article class="donor-stock-card">
                <div class="donor-stock-card-header">
                    <div class="donor-stock-blood-type">
                        {{ $stok['kode'] }}
                    </div>

                    <span class="donor-stock-status {{ $stok['status_class'] }}">
                        {{ $stok['status_label'] }}
                    </span>
                </div>

                <div class="donor-stock-main-number">
                    <strong>
                        {{ $stok['tersedia'] }}
                    </strong>

                    <span>
                        kantong tersedia
                    </span>
                </div>

                <div class="donor-stock-progress">
                    <div
                        style="width: {{ $stok['persentase'] }}%;"
                        class="{{ $stok['status_class'] }}"
                    ></div>
                </div>

                <div class="donor-stock-detail">
                    <div>
                        <span>Tersedia</span>
                        <strong>{{ $stok['tersedia'] }}</strong>
                    </div>

                    <div>
                        <span>Dipesan</span>
                        <strong>{{ $stok['dipesan'] }}</strong>
                    </div>

                    <div>
                        <span>Distribusi</span>
                        <strong>{{ $stok['didistribusikan'] }}</strong>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="donor-stock-info">
        <div>
            <h2>
                Keterangan Status Stok
            </h2>

            <p>
                Data stok darah ini bersifat informatif. Kantong darah yang
                sudah dipesan atau sudah didistribusikan tidak dihitung sebagai
                stok tersedia.
            </p>
        </div>

        <div class="donor-stock-legend">
            <span>
                <i class="is-success"></i>
                Aman
            </span>

            <span>
                <i class="is-warning"></i>
                Rendah
            </span>

            <span>
                <i class="is-danger"></i>
                Kosong
            </span>
        </div>

        <div class="donor-stock-actions">
            <a href="{{ route('donor.jadwal') }}">
                Lihat Jadwal Donor
            </a>

            <a href="{{ route('donor.lokasi') }}" class="is-outline">
                Lihat Lokasi Donor
            </a>
        </div>
    </section>

    <style>
        .donor-stock-page {
            display: grid;
            gap: 28px;
        }

        .donor-stock-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 24px;
            align-items: end;
        }

        .donor-stock-eyebrow {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .donor-stock-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(36px, 5vw, 54px);
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .donor-stock-hero p:not(.donor-stock-eyebrow) {
            max-width: 760px;
            margin: 16px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.8;
        }

        .donor-stock-hero-card {
            padding: 24px;
            border: 1px solid #fee2e2;
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(239, 68, 68, 0.14), transparent 9rem),
                #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .donor-stock-hero-card span {
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .donor-stock-hero-card strong {
            display: block;
            margin-top: 12px;
            color: #0f172a;
            font-size: 56px;
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .donor-stock-hero-card p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .donor-stock-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-stock-summary article {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.05);
        }

        .donor-stock-summary-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 18px;
        }

        .donor-stock-summary-icon svg {
            width: 26px;
            height: 26px;
        }

        .donor-stock-summary-icon.is-success {
            color: #16a34a;
            background: #dcfce7;
        }

        .donor-stock-summary-icon.is-warning {
            color: #f59e0b;
            background: #fef3c7;
        }

        .donor-stock-summary-icon.is-info {
            color: #2563eb;
            background: #dbeafe;
        }

        .donor-stock-summary-icon.is-muted {
            color: #dc2626;
            background: #fee2e2;
        }

        .donor-stock-summary span {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .donor-stock-summary strong {
            display: block;
            margin-top: 4px;
            color: #0f172a;
            font-size: 30px;
            line-height: 1;
        }

        .donor-stock-summary p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .donor-stock-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-stock-card {
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.06);
        }

        .donor-stock-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .donor-stock-blood-type {
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            color: #dc2626;
            background: #fee2e2;
            font-size: 24px;
            font-weight: 1000;
            letter-spacing: -0.04em;
        }

        .donor-stock-status {
            min-height: 30px;
            display: inline-flex;
            align-items: center;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
        }

        .donor-stock-status.is-success {
            color: #166534;
            background: #dcfce7;
        }

        .donor-stock-status.is-warning {
            color: #92400e;
            background: #fef3c7;
        }

        .donor-stock-status.is-danger {
            color: #991b1b;
            background: #fee2e2;
        }

        .donor-stock-main-number {
            margin-top: 22px;
        }

        .donor-stock-main-number strong {
            display: block;
            color: #0f172a;
            font-size: 46px;
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .donor-stock-main-number span {
            display: block;
            margin-top: 8px;
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
        }

        .donor-stock-progress {
            height: 9px;
            overflow: hidden;
            margin-top: 20px;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .donor-stock-progress div {
            height: 100%;
            border-radius: 999px;
        }

        .donor-stock-progress div.is-success {
            background: #22c55e;
        }

        .donor-stock-progress div.is-warning {
            background: #f59e0b;
        }

        .donor-stock-progress div.is-danger {
            background: #ef4444;
        }

        .donor-stock-detail {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .donor-stock-detail div {
            display: grid;
            gap: 4px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            background: #f8fafc;
            text-align: center;
        }

        .donor-stock-detail span {
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .donor-stock-detail strong {
            color: #0f172a;
            font-size: 18px;
        }

        .donor-stock-info {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 24px;
            align-items: center;
            padding: 24px;
            border: 1px solid #fee2e2;
            border-radius: 26px;
            background: #fff7f7;
        }

        .donor-stock-info h2 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .donor-stock-info p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .donor-stock-legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .donor-stock-legend span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
        }

        .donor-stock-legend i {
            width: 12px;
            height: 12px;
            border-radius: 999px;
        }

        .donor-stock-legend i.is-success {
            background: #22c55e;
        }

        .donor-stock-legend i.is-warning {
            background: #f59e0b;
        }

        .donor-stock-legend i.is-danger {
            background: #ef4444;
        }

        .donor-stock-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .donor-stock-actions a {
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
        }

        .donor-stock-actions a.is-outline {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        @media (max-width: 1100px) {
            .donor-stock-summary,
            .donor-stock-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .donor-stock-info {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .donor-stock-hero {
                grid-template-columns: 1fr;
            }

            .donor-stock-summary,
            .donor-stock-grid {
                grid-template-columns: 1fr;
            }

            .donor-stock-actions {
                flex-direction: column;
            }

            .donor-stock-actions a {
                width: 100%;
            }
        }
    </style>
</div>