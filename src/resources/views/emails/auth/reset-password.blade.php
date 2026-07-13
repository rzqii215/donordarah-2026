@php
    $namaAplikasi = filled($appName ?? null)
        ? (string) $appName
        : (string) config(
            'app.name',
            'Donor Darah'
        );

    $namaPengguna = filled($userName ?? null)
        ? (string) $userName
        : (
            isset($notifiable)
            && filled($notifiable->name ?? null)
                ? (string) $notifiable->name
                : 'Pengguna'
        );

    $tautanReset = (string) (
        $resetUrl
        ?? $resetPasswordUrl
        ?? $url
        ?? url('/forgot-password')
    );

    $masaBerlaku = (int) (
        $expiresInMinutes
        ?? config(
            'auth.passwords.users.expire',
            60
        )
    );

    $tahunSekarang = now()->year;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="color-scheme"
        content="light"
    >

    <meta
        name="supported-color-schemes"
        content="light"
    >

    <title>
        Atur Ulang Password — {{ $namaAplikasi }}
    </title>

    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
        }

        body {
            width: 100% !important;
            min-width: 100%;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f7f4f4;
        }

        a {
            color: #991b2f;
        }

        @media only screen and (max-width: 680px) {
            .email-shell {
                width: 100% !important;
            }

            .email-card {
                border-radius: 0 !important;
            }

            .email-header {
                padding: 32px 24px !important;
            }

            .email-body {
                padding: 32px 24px !important;
            }

            .email-footer {
                padding: 24px !important;
            }

            .email-title {
                font-size: 27px !important;
                line-height: 1.25 !important;
            }

            .email-button {
                display: block !important;
                width: auto !important;
                padding: 15px 20px !important;
            }

            .email-url {
                font-size: 11px !important;
            }
        }
    </style>
</head>

<body>
    <div
        style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
            mso-hide: all;
        "
    >
        Gunakan tautan ini untuk membuat password baru akun
        {{ $namaAplikasi }} Anda.
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width: 100%;
            background-color: #f7f4f4;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding: 42px 16px;
                "
            >
                <table
                    role="presentation"
                    class="email-shell email-card"
                    width="640"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 640px;
                        overflow: hidden;
                        border: 1px solid #e7dddf;
                        border-radius: 22px;
                        background-color: #ffffff;
                        box-shadow: 0 18px 50px rgba(79, 0, 18, 0.08);
                    "
                >
                    <tr>
                        <td
                            class="email-header"
                            align="left"
                            style="
                                padding: 36px 48px;
                                background-color: #76001c;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        width="58"
                                        valign="middle"
                                        style="
                                            width: 58px;
                                        "
                                    >
                                        <table
                                            role="presentation"
                                            width="48"
                                            height="48"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 48px;
                                                height: 48px;
                                                border: 1px solid rgba(255, 255, 255, 0.35);
                                                border-radius: 14px;
                                                background-color: rgba(255, 255, 255, 0.12);
                                            "
                                        >
                                            <tr>
                                                <td
                                                    align="center"
                                                    valign="middle"
                                                    style="
                                                        color: #ffffff;
                                                        font-family: Arial, Helvetica, sans-serif;
                                                        font-size: 25px;
                                                        font-weight: 700;
                                                    "
                                                >
                                                    ♡
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td
                                        valign="middle"
                                        style="
                                            padding-left: 4px;
                                        "
                                    >
                                        <p
                                            style="
                                                margin: 0;
                                                color: #ffffff;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 19px;
                                                font-weight: 700;
                                                line-height: 1.25;
                                                letter-spacing: -0.3px;
                                            "
                                        >
                                            {{ $namaAplikasi }}
                                        </p>

                                        <p
                                            style="
                                                margin: 3px 0 0;
                                                color: #f7cbd5;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 10px;
                                                font-weight: 700;
                                                line-height: 1.4;
                                                letter-spacing: 0.8px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Terhubung untuk menolong
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        style="
                                            padding-top: 34px;
                                        "
                                    >
                                        <p
                                            style="
                                                margin: 0 0 8px;
                                                color: #f3b7c4;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 11px;
                                                font-weight: 700;
                                                line-height: 1.4;
                                                letter-spacing: 1.2px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Keamanan akun
                                        </p>

                                        <h1
                                            class="email-title"
                                            style="
                                                margin: 0;
                                                color: #ffffff;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 32px;
                                                font-weight: 700;
                                                line-height: 1.2;
                                                letter-spacing: -0.7px;
                                            "
                                        >
                                            Atur Ulang Password
                                        </h1>

                                        <p
                                            style="
                                                margin: 12px 0 0;
                                                color: #f6dce2;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 14px;
                                                font-weight: 400;
                                                line-height: 1.6;
                                            "
                                        >
                                            Buat password baru untuk menjaga
                                            keamanan akun Anda.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="email-body"
                            style="
                                padding: 42px 48px 38px;
                                background-color: #ffffff;
                            "
                        >
                            <p
                                style="
                                    margin: 0 0 18px;
                                    color: #191c20;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 19px;
                                    font-weight: 700;
                                    line-height: 1.5;
                                "
                            >
                                Halo, {{ $namaPengguna }}
                            </p>

                            <p
                                style="
                                    margin: 0;
                                    color: #5f5558;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 15px;
                                    font-weight: 400;
                                    line-height: 1.75;
                                "
                            >
                                Kami menerima permintaan untuk mengatur ulang
                                password akun {{ $namaAplikasi }} Anda.
                                Tekan tombol di bawah ini untuk membuat
                                password baru.
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        align="center"
                                        style="
                                            padding: 30px 0;
                                        "
                                    >
                                        <a
                                            href="{{ $tautanReset }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="email-button"
                                            style="
                                                display: inline-block;
                                                padding: 15px 30px;
                                                border: 1px solid #991b2f;
                                                border-radius: 12px;
                                                color: #ffffff;
                                                background-color: #991b2f;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 14px;
                                                font-weight: 700;
                                                line-height: 1.2;
                                                text-align: center;
                                                text-decoration: none;
                                                box-shadow: 0 10px 24px rgba(153, 27, 47, 0.2);
                                            "
                                        >
                                            Buat Password Baru
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    margin-bottom: 26px;
                                    border: 1px solid #efd1d8;
                                    border-radius: 14px;
                                    background-color: #fff5f7;
                                "
                            >
                                <tr>
                                    <td
                                        width="52"
                                        valign="top"
                                        style="
                                            width: 52px;
                                            padding: 18px 0 18px 18px;
                                        "
                                    >
                                        <table
                                            role="presentation"
                                            width="34"
                                            height="34"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            style="
                                                width: 34px;
                                                height: 34px;
                                                border-radius: 10px;
                                                background-color: #f7dce2;
                                            "
                                        >
                                            <tr>
                                                <td
                                                    align="center"
                                                    valign="middle"
                                                    style="
                                                        color: #76001c;
                                                        font-family: Arial, Helvetica, sans-serif;
                                                        font-size: 16px;
                                                        font-weight: 700;
                                                    "
                                                >
                                                    ⏱
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td
                                        valign="top"
                                        style="
                                            padding: 18px 18px 18px 12px;
                                        "
                                    >
                                        <p
                                            style="
                                                margin: 0 0 4px;
                                                color: #76001c;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 13px;
                                                font-weight: 700;
                                                line-height: 1.5;
                                            "
                                        >
                                            Tautan berlaku selama
                                            {{ $masaBerlaku }} menit
                                        </p>

                                        <p
                                            style="
                                                margin: 0;
                                                color: #7a5961;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 12px;
                                                font-weight: 400;
                                                line-height: 1.65;
                                            "
                                        >
                                            Setelah melewati batas waktu,
                                            silakan ajukan pengaturan ulang
                                            password kembali.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    margin-bottom: 26px;
                                    border: 1px solid #e7dddf;
                                    border-radius: 14px;
                                    background-color: #faf8f8;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                        "
                                    >
                                        <p
                                            style="
                                                margin: 0 0 8px;
                                                color: #191c20;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 12px;
                                                font-weight: 700;
                                                line-height: 1.5;
                                            "
                                        >
                                            Tidak merasa meminta perubahan ini?
                                        </p>

                                        <p
                                            style="
                                                margin: 0;
                                                color: #6f6467;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 12px;
                                                font-weight: 400;
                                                line-height: 1.65;
                                            "
                                        >
                                            Abaikan email ini. Password akun
                                            Anda tidak akan berubah selama
                                            tombol atau tautan tidak digunakan.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="
                                    margin: 0 0 10px;
                                    color: #655a5d;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 12px;
                                    font-weight: 400;
                                    line-height: 1.65;
                                "
                            >
                                Jika tombol tidak dapat digunakan, salin dan
                                buka alamat berikut melalui browser:
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    border: 1px solid #e7dddf;
                                    border-radius: 12px;
                                    background-color: #f7f4f4;
                                "
                            >
                                <tr>
                                    <td
                                        class="email-url"
                                        style="
                                            padding: 14px 16px;
                                            color: #76001c;
                                            font-family: Consolas, Monaco, monospace;
                                            font-size: 11px;
                                            font-weight: 400;
                                            line-height: 1.6;
                                            word-break: break-all;
                                        "
                                    >
                                        <a
                                            href="{{ $tautanReset }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            style="
                                                color: #76001c;
                                                text-decoration: none;
                                                word-break: break-all;
                                            "
                                        >
                                            {{ $tautanReset }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        style="
                                            padding-top: 30px;
                                        "
                                    >
                                        <p
                                            style="
                                                margin: 0;
                                                color: #5f5558;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 13px;
                                                font-weight: 400;
                                                line-height: 1.7;
                                            "
                                        >
                                            Salam hangat,
                                        </p>

                                        <p
                                            style="
                                                margin: 3px 0 0;
                                                color: #191c20;
                                                font-family: Arial, Helvetica, sans-serif;
                                                font-size: 13px;
                                                font-weight: 700;
                                                line-height: 1.7;
                                            "
                                        >
                                            Tim {{ $namaAplikasi }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="email-footer"
                            align="center"
                            style="
                                padding: 26px 42px;
                                border-top: 1px solid #eee6e8;
                                background-color: #faf8f8;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #86797c;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 10px;
                                    font-weight: 400;
                                    line-height: 1.7;
                                "
                            >
                                Email ini dikirim secara otomatis untuk
                                melindungi akses akun Anda.
                                Mohon tidak membalas email ini.
                            </p>

                            <p
                                style="
                                    margin: 10px 0 0;
                                    color: #a09295;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 10px;
                                    font-weight: 400;
                                    line-height: 1.7;
                                "
                            >
                                &copy; {{ $tahunSekarang }}
                                {{ $namaAplikasi }}.
                                Seluruh hak dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>

                <table
                    role="presentation"
                    class="email-shell"
                    width="640"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 640px;
                    "
                >
                    <tr>
                        <td
                            align="center"
                            style="
                                padding: 18px 20px 0;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #9b8d90;
                                    font-family: Arial, Helvetica, sans-serif;
                                    font-size: 9px;
                                    font-weight: 400;
                                    line-height: 1.6;
                                "
                            >
                                Pesan keamanan dari {{ $namaAplikasi }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>