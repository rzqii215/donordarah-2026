<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $judul ?? 'Bukti Pemohon Donor' }}</title>

    <style>
        @page {
            margin: 24px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #242124;
            background: #ffffff;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.55;
        }

        .header-table,
        .meta-table,
        .data-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 27px;
            color: #ffffff;
            background: #a20d2f;
            font-size: 25px;
            font-weight: bold;
            line-height: 54px;
            text-align: center;
        }

        .brand-name {
            margin: 0;
            color: #76001c;
            font-size: 20px;
            font-weight: bold;
        }

        .brand-description {
            margin: 2px 0 0;
            color: #715b60;
            font-size: 9px;
        }

        .document-label {
            color: #8b6d73;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: right;
        }

        .document-number {
            margin-top: 3px;
            color: #242124;
            font-size: 11px;
            font-weight: bold;
            text-align: right;
        }

        .header-line {
            height: 3px;
            margin: 14px 0 22px;
            background: #a20d2f;
        }

        .title {
            margin: 0;
            color: #111111;
            font-size: 21px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            margin: 4px 0 20px;
            color: #765f64;
            font-size: 10px;
            text-align: center;
        }

        .status-wrapper {
            margin-bottom: 18px;
            text-align: center;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border: 1px solid #e4b9c2;
            border-radius: 14px;
            color: #89102b;
            background: #fff1f3;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section {
            margin-bottom: 16px;
            border: 1px solid #ded8da;
            border-radius: 7px;
        }

        .section-title {
            padding: 8px 11px;
            color: #76001c;
            background: #faf0f2;
            border-bottom: 1px solid #ded8da;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .section-content {
            padding: 7px 11px 9px;
        }

        .data-table td {
            padding: 5px 4px;
            vertical-align: top;
            border-bottom: 1px solid #eee9ea;
        }

        .data-table tr:last-child td {
            border-bottom: 0;
        }

        .data-label {
            width: 32%;
            color: #725f63;
        }

        .data-separator {
            width: 3%;
            color: #725f63;
        }

        .data-value {
            width: 65%;
            color: #1c191a;
            font-weight: bold;
        }

        .note {
            padding: 10px 12px;
            color: #5e4a4e;
            background: #f7f6f6;
            border-left: 3px solid #a20d2f;
            font-size: 9px;
        }

        .signature-table {
            margin-top: 25px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            display: inline-block;
            min-width: 160px;
            padding-top: 4px;
            border-top: 1px solid #4a4143;
            font-weight: bold;
        }

        .signature-role {
            margin-top: 2px;
            color: #725f63;
            font-size: 9px;
        }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            color: #806d71;
            border-top: 1px solid #ded8da;
            font-size: 8px;
            text-align: center;
        }

        .footer strong {
            color: #76001c;
        }
    </style>
</head>

<body>
    @php
        $nilaiEnum = static function (mixed $value): string {
            if ($value instanceof \BackedEnum) {
                return (string) $value->value;
            }

            if ($value instanceof \UnitEnum) {
                return (string) $value->name;
            }

            return trim((string) ($value ?? ''));
        };

        $labelEnum = static function (mixed $value) use ($nilaiEnum): string {
            if (is_object($value) && method_exists($value, 'label')) {
                return (string) $value->label();
            }

            $nilai = $nilaiEnum($value);

            if ($nilai === '') {
                return '-';
            }

            return \Illuminate\Support\Str::headline(
                str_replace(['_', '-'], ' ', $nilai)
            );
        };

        $tanggal = static function (mixed $value, string $format = 'd F Y, H:i'): string {
            if (blank($value)) {
                return '-';
            }

            try {
                return \Illuminate\Support\Carbon::parse($value)
                    ->locale('id')
                    ->translatedFormat($format);
            } catch (\Throwable) {
                return (string) $value;
            }
        };

        $jenisDokumen = $jenisBukti ?? $jenis ?? 'pengajuan';
        $adalahDistribusi = $jenisDokumen === 'distribusi';

        $nomorDokumen = $adalahDistribusi
            ? ($distribusi?->nomor_distribusi ?? '-')
            : ($pengajuan?->nomor_permintaan ?? '-');

        $statusDokumen = $adalahDistribusi
            ? ($distribusi?->status ?? null)
            : ($pengajuan?->status ?? null);

        $namaRumahSakit = $profil->nama_rumah_sakit
            ?? $profil->nama_instansi
            ?? $pengguna->name
            ?? '-';

        $alamatRumahSakit = collect([
            $profil->alamat ?? null,
            $profil->kota ?? null,
            $profil->provinsi ?? null,
        ])->filter()->implode(', ');

        $golonganDarah = $labelEnum(
            $pengajuan?->golongan_darah
        );

        $rhesus = $nilaiEnum(
            $pengajuan?->rhesus
        );

        if (in_array(strtolower($rhesus), ['positive', 'positif', 'rh+', '+'], true)) {
            $rhesus = '+';
        } elseif (in_array(strtolower($rhesus), ['negative', 'negatif', 'rh-', '-'], true)) {
            $rhesus = '-';
        }

        $golonganLengkap = trim(
            $golonganDarah . ($rhesus !== '' ? " ({$rhesus})" : '')
        );
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 64px;">
                <div class="brand-mark">D</div>
            </td>

            <td>
                <p class="brand-name">
                    {{ config('app.name', 'Donor Darah') }}
                </p>

                <p class="brand-description">
                    Sistem Informasi Pelayanan dan Distribusi Darah
                </p>
            </td>

            <td style="width: 220px;">
                <div class="document-label">
                    Nomor Dokumen
                </div>

                <div class="document-number">
                    {{ $nomorDokumen }}
                </div>
            </td>
        </tr>
    </table>

    <div class="header-line"></div>

    <h1 class="title">
        {{ $judul ?? 'Bukti Pemohon Donor' }}
    </h1>

    <p class="subtitle">
        Dokumen ini diterbitkan secara elektronik oleh
        {{ config('app.name', 'Donor Darah') }}.
    </p>

    <div class="status-wrapper">
        <span class="status">
            Status: {{ $labelEnum($statusDokumen) }}
        </span>
    </div>

    <section class="section">
        <div class="section-title">
            Informasi Pemohon
        </div>

        <div class="section-content">
            <table class="data-table">
                <tr>
                    <td class="data-label">Nama Rumah Sakit/Instansi</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">{{ $namaRumahSakit }}</td>
                </tr>

                <tr>
                    <td class="data-label">Kode Pemohon</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $profil->kode_rumah_sakit ?? $profil->kode_pemohon ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Alamat</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $alamatRumahSakit !== '' ? $alamatRumahSakit : '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Email</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">{{ $pengguna->email ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="data-label">Nomor Telepon</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $profil->nomor_telepon ?? $pengguna->nomor_telepon ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>
    </section>

    <section class="section">
        <div class="section-title">
            Detail Pengajuan Darah
        </div>

        <div class="section-content">
            <table class="data-table">
                <tr>
                    <td class="data-label">Nomor Permintaan</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $pengajuan?->nomor_permintaan ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Referensi Pasien</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $pengajuan?->referensi_pasien ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Golongan Darah</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $golonganLengkap !== '' ? $golonganLengkap : '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Jumlah Kantong</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $pengajuan?->jumlah_kantong ?? $pengajuan?->jumlah_dibutuhkan ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Tingkat Urgensi</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $labelEnum($pengajuan?->tingkat_urgensi) }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Tanggal Dibutuhkan</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $tanggal($pengajuan?->tanggal_dibutuhkan, 'd F Y') }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Dokter Penanggung Jawab</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $pengajuan?->nama_dokter ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Diajukan Pada</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $tanggal($pengajuan?->created_at) }} WIB
                    </td>
                </tr>

                <tr>
                    <td class="data-label">Status Pengajuan</td>
                    <td class="data-separator">:</td>
                    <td class="data-value">
                        {{ $labelEnum($pengajuan?->status) }}
                    </td>
                </tr>

                @if (filled($pengajuan?->catatan))
                    <tr>
                        <td class="data-label">Catatan</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $pengajuan->catatan }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </section>

    @if ($adalahDistribusi && $distribusi !== null)
        <section class="section">
            <div class="section-title">
                Detail Distribusi Darah
            </div>

            <div class="section-content">
                <table class="data-table">
                    <tr>
                        <td class="data-label">Nomor Distribusi</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $distribusi->nomor_distribusi ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="data-label">Status Distribusi</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $labelEnum($distribusi->status ?? null) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="data-label">Jumlah Kantong</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $distribusi->jumlah_kantong ?? $pengajuan?->jumlah_kantong ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td class="data-label">Dijadwalkan Pada</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $tanggal($distribusi->dijadwalkan_pada ?? null) }} WIB
                        </td>
                    </tr>

                    <tr>
                        <td class="data-label">Diserahkan Pada</td>
                        <td class="data-separator">:</td>
                        <td class="data-value">
                            {{ $tanggal($distribusi->diserahkan_pada ?? null) }}
                            @if (filled($distribusi->diserahkan_pada ?? null))
                                WIB
                            @endif
                        </td>
                    </tr>

                    @if (filled($distribusi->catatan ?? null))
                        <tr>
                            <td class="data-label">Catatan Distribusi</td>
                            <td class="data-separator">:</td>
                            <td class="data-value">
                                {{ $distribusi->catatan }}
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </section>
    @endif

    <div class="note">
        Dokumen ini merupakan bukti elektronik yang dihasilkan langsung
        oleh sistem. Pastikan nomor dokumen dan seluruh informasi yang
        tercantum sesuai dengan data pengajuan Anda.
    </div>

    <table class="signature-table">
        <tr>
            <td>
                Pemohon,
                <div class="signature-space"></div>
                <div class="signature-name">
                    {{ $namaRumahSakit }}
                </div>
                <div class="signature-role">
                    Pemohon Donor
                </div>
            </td>

            <td>
                Dicetak pada,
                <div class="signature-space"></div>
                <div class="signature-name">
                    {{ $tanggal($dicetakPada ?? now()) }} WIB
                </div>
                <div class="signature-role">
                    Dokumen Elektronik
                </div>
            </td>
        </tr>
    </table>

    <footer class="footer">
        <strong>{{ config('app.name', 'Donor Darah') }}</strong><br>
        Dokumen dibuat otomatis dan tidak memerlukan tanda tangan basah.
    </footer>
</body>
</html>