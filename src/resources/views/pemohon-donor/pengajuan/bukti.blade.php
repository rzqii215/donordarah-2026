@php
    $modeBukti =
        $mode ?? 'lihat';

    $modeUnduh =
        $modeBukti === 'unduh';

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

    $statusValue =
        $nilaiEnum($pengajuan->status);

    $statusClass = match (
        $statusValue
    ) {
        'completed',
        'selesai' =>
            'success',

        'approved',
        'ready_for_pickup',
        'disetujui',
        'siap_diambil' =>
            'info',

        'rejected',
        'cancelled',
        'ditolak',
        'dibatalkan' =>
            'danger',

        default =>
            'warning',
    };

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna->name;

    $dokumenUrl =
        filled(
            $pengajuan
                ->path_dokumen_permintaan
        )
            ? \Illuminate\Support\Facades\Storage
                ::disk('public')
                ->url(
                    $pengajuan
                        ->path_dokumen_permintaan
                )
            : null;

    $alasanStatus =
        $pengajuan->alasan_penolakan
        ?? $pengajuan->alasan_pembatalan
        ?? null;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Bukti Pengajuan {{ $pengajuan->nomor_permintaan }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            color-scheme: light;
            --red: #991b2f;
            --red-dark: #76001c;
            --red-soft: #fff1f3;
            --text: #191c20;
            --muted: #755f60;
            --line: #e8e2df;
            --surface: #ffffff;
            --background: #f5f3f2;
        }

        body {
            margin: 0;
            color: var(--text);
            background: var(--background);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            max-width: 960px;
            margin: 24px auto 0;
            padding: 0 20px;
        }

        .toolbar-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .button {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--text);
            background: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .button.primary {
            border-color: var(--red);
            color: #ffffff;
            background: var(--red);
        }

        .document {
            width: min(100% - 40px, 920px);
            margin: 24px auto 40px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: 0 24px 70px rgba(25, 28, 32, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 32px;
            padding: 34px;
            color: #ffffff;
            background: var(--red-dark);
        }

        .brand {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .brand-mark {
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 24px;
        }

        .brand p,
        .document-title p {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .brand h1 {
            margin: 5px 0 0;
            font-size: 20px;
        }

        .document-title {
            text-align: right;
        }

        .document-title h2 {
            margin: 7px 0 0;
            font-size: 25px;
        }

        .body {
            padding: 32px 34px;
        }

        .number-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
            padding: 22px;
            border: 1px solid #ebd8dc;
            border-radius: 16px;
            background: var(--red-soft);
        }

        .label {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .number-card strong {
            display: block;
            margin-top: 7px;
            color: var(--red-dark);
            font-size: 24px;
        }

        .status {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            padding: 0 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .status.success {
            color: #176b3a;
            background: #dff7e7;
        }

        .status.info {
            color: #315b9b;
            background: #e7effc;
        }

        .status.warning {
            color: #8a5a00;
            background: #fff1c9;
        }

        .status.danger {
            color: #991b2f;
            background: #ffe4e7;
        }

        .section {
            margin-top: 26px;
        }

        .section-title {
            margin: 0 0 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--line);
            font-size: 16px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .item {
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fbfaf9;
        }

        .item.full {
            grid-column: 1 / -1;
        }

        .item strong {
            display: block;
            margin-top: 7px;
            font-size: 14px;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .blood {
            color: var(--red);
            font-size: 22px !important;
        }

        .timeline {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .timeline-item {
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .timeline-item strong {
            display: block;
            margin-top: 6px;
            font-size: 13px;
        }

        .timeline-item span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .notice {
            margin-top: 26px;
            padding: 17px;
            border-left: 4px solid var(--red);
            border-radius: 8px;
            color: #5f4147;
            background: #fff7f8;
            font-size: 12px;
            line-height: 1.7;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            padding: 20px 34px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            background: #fbfaf9;
            font-size: 11px;
            line-height: 1.6;
        }

        @media (max-width: 700px) {
            .header,
            .footer,
            .number-card {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .document-title {
                text-align: left;
            }

            .grid,
            .timeline {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: #ffffff;
            }

            .toolbar {
                display: none !important;
            }

            .document {
                width: 100%;
                margin: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .header {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .status,
            .number-card {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @if (! $modeUnduh)
        <nav class="toolbar">
            <div class="toolbar-group">
                <a
                    href="{{ route('pemohon-donor.pengajuan.index') }}"
                    class="button"
                >
                    Kembali
                </a>

                <a
                    href="{{ route('pemohon-donor.pengajuan.bukti.unduh', $pengajuan) }}"
                    class="button"
                >
                    Unduh HTML
                </a>
            </div>

            <button
                type="button"
                class="button primary"
                onclick="window.print()"
            >
                Cetak Bukti
            </button>
        </nav>
    @endif

    <main class="document">
        <header class="header">
            <div class="brand">
                <div class="brand-mark">
                    ♥
                </div>

                <div>
                    <p>Portal Pemohon Donor</p>

                    <h1>
                        {{ config('app.name', 'Donor Darah') }}
                    </h1>
                </div>
            </div>

            <div class="document-title">
                <p>Dokumen Elektronik</p>

                <h2>Bukti Pengajuan</h2>
            </div>
        </header>

        <div class="body">
            <section class="number-card">
                <div>
                    <span class="label">
                        Nomor Pengajuan
                    </span>

                    <strong>
                        {{ $pengajuan->nomor_permintaan }}
                    </strong>
                </div>

                <span class="status {{ $statusClass }}">
                    {{ $labelEnum($pengajuan->status) }}
                </span>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Informasi Pemohon
                </h3>

                <div class="grid">
                    <div class="item">
                        <span class="label">
                            Rumah Sakit/Instansi
                        </span>

                        <strong>
                            {{ $namaPemohon }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Kode Pemohon
                        </span>

                        <strong>
                            {{ $profil?->kode_rumah_sakit ?? '-' }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Penanggung Jawab
                        </span>

                        <strong>
                            {{ $profil?->nama_penanggung_jawab ?? '-' }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Email Akun
                        </span>

                        <strong>
                            {{ $pengguna->email }}
                        </strong>
                    </div>

                    <div class="item full">
                        <span class="label">
                            Alamat
                        </span>

                        <strong>
                            {{ collect([
                                $profil?->alamat,
                                $profil?->kecamatan,
                                $profil?->kota,
                                $profil?->provinsi,
                                $profil?->kode_pos,
                            ])->filter()->implode(', ') ?: '-' }}
                        </strong>
                    </div>
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Detail Kebutuhan Darah
                </h3>

                <div class="grid">
                    <div class="item">
                        <span class="label">
                            Referensi Pasien
                        </span>

                        <strong>
                            {{ $pengajuan->referensi_pasien ?? '-' }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Dokter Penanggung Jawab
                        </span>

                        <strong>
                            {{ $pengajuan->nama_dokter ?? '-' }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Golongan Darah
                        </span>

                        <strong class="blood">
                            {{ $golonganDarah($pengajuan->golongan_darah, $pengajuan->rhesus) }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Jumlah Kebutuhan
                        </span>

                        <strong>
                            {{ number_format($pengajuan->jumlah_kantong) }}
                            kantong
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Tingkat Urgensi
                        </span>

                        <strong>
                            {{ $labelEnum($pengajuan->tingkat_urgensi) }}
                        </strong>
                    </div>

                    <div class="item">
                        <span class="label">
                            Dibutuhkan Pada
                        </span>

                        <strong>
                            {{ $pengajuan->dibutuhkan_pada?->translatedFormat('d F Y, H:i') ?? '-' }}
                            WIB
                        </strong>
                    </div>

                    <div class="item full">
                        <span class="label">
                            Catatan
                        </span>

                        <strong>
                            {{ $pengajuan->catatan ?? '-' }}
                        </strong>
                    </div>

                    @if ($alasanStatus !== null)
                        <div class="item full">
                            <span class="label">
                                Alasan Status
                            </span>

                            <strong>
                                {{ $alasanStatus }}
                            </strong>
                        </div>
                    @endif

                    @if ($dokumenUrl !== null)
                        <div class="item full">
                            <span class="label">
                                Dokumen Pendukung
                            </span>

                            <strong>
                                <a
                                    href="{{ $dokumenUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    style="color: #991b2f;"
                                >
                                    Buka Dokumen Permintaan
                                </a>
                            </strong>
                        </div>
                    @endif
                </div>
            </section>

            <section class="section">
                <h3 class="section-title">
                    Timeline Pengajuan
                </h3>

                <div class="timeline">
                    <div class="timeline-item">
                        <span class="label">
                            Dibuat
                        </span>

                        <strong>
                            {{ $pengajuan->created_at?->translatedFormat('d M Y') ?? '-' }}
                        </strong>

                        <span>
                            {{ $pengajuan->created_at?->format('H:i') ?? '-' }} WIB
                        </span>
                    </div>

                    <div class="timeline-item">
                        <span class="label">
                            Ditinjau
                        </span>

                        <strong>
                            {{ $pengajuan->ditinjau_pada?->translatedFormat('d M Y') ?? '-' }}
                        </strong>

                        <span>
                            {{ $pengajuan->ditinjau_pada?->format('H:i') ?? '-' }}
                            @if ($pengajuan->ditinjau_pada !== null)
                                WIB
                            @endif
                        </span>
                    </div>

                    <div class="timeline-item">
                        <span class="label">
                            Disetujui
                        </span>

                        <strong>
                            {{ $pengajuan->disetujui_pada?->translatedFormat('d M Y') ?? '-' }}
                        </strong>

                        <span>
                            {{ $pengajuan->disetujui_pada?->format('H:i') ?? '-' }}
                            @if ($pengajuan->disetujui_pada !== null)
                                WIB
                            @endif
                        </span>
                    </div>

                    <div class="timeline-item">
                        <span class="label">
                            Selesai
                        </span>

                        <strong>
                            {{ $pengajuan->selesai_pada?->translatedFormat('d M Y') ?? '-' }}
                        </strong>

                        <span>
                            {{ $pengajuan->selesai_pada?->format('H:i') ?? '-' }}
                            @if ($pengajuan->selesai_pada !== null)
                                WIB
                            @endif
                        </span>
                    </div>
                </div>
            </section>

            <div class="notice">
                Dokumen ini dibuat otomatis oleh sistem dan menjadi bukti bahwa
                pengajuan kebutuhan darah telah tercatat. Dokumen ini bukan bukti
                bahwa seluruh kebutuhan darah sudah tersedia atau telah diserahkan.
            </div>
        </div>

        <footer class="footer">
            <span>
                Dibuat oleh {{ config('app.name', 'Donor Darah') }}
            </span>

            <span>
                Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </span>
        </footer>
    </main>
</body>
</html>