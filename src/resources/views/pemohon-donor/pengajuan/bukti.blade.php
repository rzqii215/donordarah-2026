<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Bukti Pengajuan {{ $pengajuan->nomor_permintaan }}</title>

    <style>
        :root {
            --red: #ef1d26;
            --red-soft: #fff1f2;
            --red-border: #fecdd3;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e5e7eb;
            --surface: #ffffff;
            --body: #f8fafc;
            --green: #16a34a;
            --green-soft: #dcfce7;
            --blue: #2563eb;
            --blue-soft: #dbeafe;
            --yellow: #d97706;
            --yellow-soft: #fef3c7;
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(
                    circle at top right,
                    rgba(254, 226, 226, 0.72),
                    transparent 28rem
                ),
                var(--body);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            max-width: 960px;
            margin: 34px auto;
            padding: 0 20px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
        }

        .button-primary {
            color: #ffffff;
            border: 0;
            background: var(--red);
        }

        .button-secondary {
            color: var(--red);
            border: 1px solid var(--red-border);
            background: #ffffff;
        }

        .document {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 26px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .header {
            position: relative;
            padding: 32px;
            color: #ffffff;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(255, 255, 255, 0.24),
                    transparent 15rem
                ),
                linear-gradient(135deg, var(--red), #ef4444);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 29px;
            font-weight: 900;
        }

        .brand-title {
            margin: 0;
            font-size: 24px;
            font-weight: 950;
            line-height: 1.1;
            letter-spacing: -0.035em;
        }

        .brand-subtitle {
            margin: 5px 0 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 14px;
            font-weight: 700;
        }

        .doc-number {
            margin-top: 28px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
        }

        .doc-number span {
            display: block;
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .doc-number strong {
            display: block;
            margin-top: 6px;
            font-size: 27px;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .content {
            padding: 32px;
        }

        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 26px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 900;
        }

        .badge-red {
            color: var(--red);
            background: var(--red-soft);
        }

        .badge-blue {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .badge-green {
            color: var(--green);
            background: var(--green-soft);
        }

        .badge-yellow {
            color: var(--yellow);
            background: var(--yellow-soft);
        }

        .section {
            margin-top: 28px;
        }

        .section:first-child {
            margin-top: 0;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 950;
            letter-spacing: -0.03em;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .info {
            min-height: 84px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #ffffff;
        }

        .info.full {
            grid-column: 1 / -1;
        }

        .label {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .value {
            display: block;
            margin-top: 7px;
            font-size: 15px;
            font-weight: 850;
            line-height: 1.55;
        }

        .note-box {
            padding: 18px;
            border: 1px dashed var(--red-border);
            border-radius: 16px;
            background: #fff7f7;
            color: #475569;
            font-size: 14px;
            font-weight: 650;
            line-height: 1.7;
        }

        .footer {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 24px;
            align-items: end;
            margin-top: 34px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .footer-note {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .signature {
            text-align: center;
        }

        .signature-city {
            font-size: 14px;
            font-weight: 800;
        }

        .signature-space {
            height: 76px;
        }

        .signature-name {
            padding-top: 10px;
            border-top: 1px solid var(--text);
            font-size: 14px;
            font-weight: 900;
        }

        .signature-role {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .document {
                border-radius: 0;
                box-shadow: none;
            }
        }

        @media (max-width: 720px) {
            .grid,
            .footer {
                grid-template-columns: 1fr;
            }

            .header,
            .content {
                padding: 24px;
            }
        }
    </style>
</head>

<body>
    @php
        $statusLabel = function ($status): string {
            if (is_object($status) && method_exists($status, 'label')) {
                return $status->label();
            }

            return str((string) $status)
                ->replace('_', ' ')
                ->headline()
                ->toString();
        };

        $urgensiLabel = function ($urgensi): string {
            if (is_object($urgensi) && method_exists($urgensi, 'label')) {
                return $urgensi->label();
            }

            return str((string) $urgensi)
                ->replace('_', ' ')
                ->headline()
                ->toString();
        };

        $golonganLabel = function ($golongan): string {
            if (is_object($golongan) && method_exists($golongan, 'label')) {
                return $golongan->label();
            }

            return (string) $golongan;
        };

        $rhesusSimbol = function ($rhesus): string {
            if (is_object($rhesus) && method_exists($rhesus, 'simbol')) {
                return $rhesus->simbol();
            }

            return (string) $rhesus;
        };

        $statusValue = $pengajuan->status instanceof \BackedEnum
            ? $pengajuan->status->value
            : (string) $pengajuan->status;

        $statusBadgeClass = match ($statusValue) {
            'selesai' => 'badge-green',
            'disetujui',
            'siap_diambil' => 'badge-blue',
            'ditolak',
            'dibatalkan' => 'badge-red',
            default => 'badge-yellow',
        };
    @endphp

    <div class="page">
        
@if (($mode ?? 'lihat') !== 'unduh')
            <div class="toolbar">
                <a
                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                    class="button button-secondary"
                >
                    Kembali ke Pengajuan
                </a>

                <div>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="button button-secondary"
                    >
                        Cetak
                    </button>

                    <a
                        href="{{ route('pemohon-donor.pengajuan.bukti.unduh', $pengajuan) }}"
                        class="button button-primary"
                    >
                        Unduh Bukti
                    </a>
                </div>
            </div>
        @endif

        <article class="document">
            <header class="header">
                <div class="brand">
                    <div class="brand-mark">
                        ♥
                    </div>

                    <div>
                        <h1 class="brand-title">
                            Bukti Pengajuan Kebutuhan Donor
                        </h1>

                        <p class="brand-subtitle">
                            Sistem Informasi Manajemen Donor Darah
                        </p>
                    </div>
                </div>

                <div class="doc-number">
                    <span>
                        Nomor Pengajuan
                    </span>

                    <strong>
                        {{ $pengajuan->nomor_permintaan }}
                    </strong>
                </div>
            </header>

            <main class="content">
                <div class="status-row">
                    <span class="badge {{ $statusBadgeClass }}">
                        Status: {{ $statusLabel($pengajuan->status) }}
                    </span>

                    <span class="badge badge-red">
                        {{ $golonganLabel($pengajuan->golongan_darah) }}{{ $rhesusSimbol($pengajuan->rhesus) }}
                    </span>

                    <span class="badge badge-blue">
                        {{ number_format($pengajuan->jumlah_kantong) }} Kantong
                    </span>

                    <span class="badge badge-yellow">
                        {{ $urgensiLabel($pengajuan->tingkat_urgensi) }}
                    </span>
                </div>

                <section class="section">
                    <h2 class="section-title">
                        Data Pemohon
                    </h2>

                    <div class="grid">
                        <div class="info">
                            <span class="label">
                                Kode Pemohon
                            </span>

                            <span class="value">
                                {{ $profil->kode_rumah_sakit ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Nama Pemohon
                            </span>

                            <span class="value">
                                {{ $profil->nama_rumah_sakit ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Penanggung Jawab
                            </span>

                            <span class="value">
                                {{ $profil->nama_penanggung_jawab ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Email Akun
                            </span>

                            <span class="value">
                                {{ $pengguna->email }}
                            </span>
                        </div>

                        <div class="info full">
                            <span class="label">
                                Alamat Pemohon
                            </span>

                            <span class="value">
                                {{ $profil->alamat ?? '-' }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <h2 class="section-title">
                        Detail Pengajuan
                    </h2>

                    <div class="grid">
                        <div class="info">
                            <span class="label">
                                Referensi Pengajuan
                            </span>

                            <span class="value">
                                {{ $pengajuan->referensi_pasien ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Penanggung Jawab Pengajuan
                            </span>

                            <span class="value">
                                {{ $pengajuan->nama_dokter ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Golongan Darah
                            </span>

                            <span class="value">
                                {{ $golonganLabel($pengajuan->golongan_darah) }}{{ $rhesusSimbol($pengajuan->rhesus) }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Jumlah Kebutuhan
                            </span>

                            <span class="value">
                                {{ number_format($pengajuan->jumlah_kantong) }} kantong
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Tingkat Urgensi
                            </span>

                            <span class="value">
                                {{ $urgensiLabel($pengajuan->tingkat_urgensi) }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Dibutuhkan Pada
                            </span>

                            <span class="value">
                                {{ $pengajuan->dibutuhkan_pada?->format('d M Y H:i') ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Tanggal Pengajuan
                            </span>

                            <span class="value">
                                {{ $pengajuan->created_at?->format('d M Y H:i') ?? '-' }}
                            </span>
                        </div>

                        <div class="info">
                            <span class="label">
                                Terakhir Diperbarui
                            </span>

                            <span class="value">
                                {{ $pengajuan->updated_at?->format('d M Y H:i') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <h2 class="section-title">
                        Catatan
                    </h2>

                    <div class="note-box">
                        {{ filled($pengajuan->catatan) ? $pengajuan->catatan : 'Tidak ada catatan tambahan.' }}
                    </div>
                </section>

                <footer class="footer">
                    <p class="footer-note">
                        Dokumen ini dibuat otomatis oleh Sistem Informasi Manajemen Donor Darah.
                        Bukti ini digunakan sebagai tanda bahwa Pemohon Donor telah membuat pengajuan kebutuhan donor melalui portal.
                    </p>

                    <div class="signature">
                        <div class="signature-city">
                            Dicetak pada {{ now()->format('d M Y') }}
                        </div>

                        <div class="signature-space"></div>

                        <div class="signature-name">
                            {{ $profil->nama_penanggung_jawab ?? $pengguna->name }}
                        </div>

                        <div class="signature-role">
                            Pemohon Donor
                        </div>
                    </div>
                </footer>
            </main>
        </article>
    </div>
</body>
</html>