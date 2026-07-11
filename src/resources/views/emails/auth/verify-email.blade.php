<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Verifikasi Email</title>

    <style>
        @media only screen and (max-width: 640px) {
            .email-container {
                width: 100% !important;
            }

            .email-header,
            .email-content,
            .email-footer {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .verification-button {
                display: block !important;
                width: auto !important;
            }
        }
    </style>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f4f7fb;
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
">
    <div style="
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
    ">
        Verifikasi alamat email akun {{ $appName }} Anda.
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        style="
            width: 100%;
            background-color: #f4f7fb;
        "
    >
        <tr>
            <td
                align="center"
                style="padding: 40px 16px;"
            >
                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    class="email-container"
                    style="
                        width: 600px;
                        max-width: 600px;
                        overflow: hidden;
                        background-color: #ffffff;
                        border: 1px solid #e5e7eb;
                        border-radius: 16px;
                        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
                    "
                >
                    <tr>
                        <td
                            class="email-header"
                            style="
                                padding: 30px 40px;
                                background-color: #b91c1c;
                                text-align: center;
                            "
                        >
                            <div style="
                                display: inline-block;
                                margin-bottom: 12px;
                                padding: 8px 14px;
                                border: 1px solid rgba(255, 255, 255, 0.35);
                                border-radius: 999px;
                                color: #ffffff;
                                font-size: 12px;
                                font-weight: 700;
                                letter-spacing: 1.2px;
                            ">
                                DONOR DARAH
                            </div>

                            <h1 style="
                                margin: 0;
                                color: #ffffff;
                                font-size: 25px;
                                line-height: 1.3;
                            ">
                                Verifikasi Email
                            </h1>

                            <p style="
                                margin: 10px 0 0;
                                color: #fee2e2;
                                font-size: 14px;
                                line-height: 1.6;
                            ">
                                Aktifkan akun Anda untuk melanjutkan
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="email-content"
                            style="padding: 40px;"
                        >
                            <p style="
                                margin: 0 0 18px;
                                color: #111827;
                                font-size: 16px;
                                font-weight: 700;
                            ">
                                Halo, {{ $userName }}
                            </p>

                            <p style="
                                margin: 0 0 18px;
                                color: #4b5563;
                                font-size: 15px;
                                line-height: 1.75;
                            ">
                                Terima kasih telah mendaftar di {{ $appName }}.
                                Tekan tombol berikut untuk memastikan bahwa
                                alamat email ini benar dan menjadi milik Anda.
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                style="margin: 28px 0;"
                            >
                                <tr>
                                    <td align="center">
                                        <a
                                            href="{{ $verificationUrl }}"
                                            class="verification-button"
                                            style="
                                                display: inline-block;
                                                padding: 14px 30px;
                                                background-color: #dc2626;
                                                border-radius: 9px;
                                                color: #ffffff;
                                                font-size: 15px;
                                                font-weight: 700;
                                                line-height: 1.4;
                                                text-align: center;
                                                text-decoration: none;
                                            "
                                        >
                                            Verifikasi Email
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                style="
                                    margin-bottom: 24px;
                                    background-color: #fff7ed;
                                    border: 1px solid #fed7aa;
                                    border-radius: 10px;
                                "
                            >
                                <tr>
                                    <td style="padding: 16px 18px;">
                                        <p style="
                                            margin: 0;
                                            color: #9a3412;
                                            font-size: 13px;
                                            line-height: 1.65;
                                        ">
                                            Link verifikasi berlaku selama
                                            <strong>
                                                {{ $expiresInMinutes }} menit
                                            </strong>.
                                            Jika kedaluwarsa, Anda dapat meminta
                                            link baru melalui halaman verifikasi.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="
                                margin: 0 0 10px;
                                color: #4b5563;
                                font-size: 14px;
                                line-height: 1.7;
                            ">
                                Jika tombol tidak bisa digunakan, salin dan
                                buka alamat berikut melalui browser:
                            </p>

                            <p style="
                                margin: 0 0 24px;
                                padding: 13px 15px;
                                background-color: #f8fafc;
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                color: #b91c1c;
                                font-size: 12px;
                                line-height: 1.6;
                                word-break: break-all;
                            ">
                                {{ $verificationUrl }}
                            </p>

                            <p style="
                                margin: 0;
                                color: #6b7280;
                                font-size: 13px;
                                line-height: 1.7;
                            ">
                                Jika Anda tidak merasa membuat akun ini,
                                abaikan email tersebut. Tidak ada tindakan
                                lain yang diperlukan.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="email-footer"
                            style="
                                padding: 24px 40px;
                                background-color: #f8fafc;
                                border-top: 1px solid #e5e7eb;
                                text-align: center;
                            "
                        >
                            <p style="
                                margin: 0 0 6px;
                                color: #374151;
                                font-size: 13px;
                                font-weight: 700;
                            ">
                                {{ $appName }}
                            </p>

                            <p style="
                                margin: 0;
                                color: #9ca3af;
                                font-size: 12px;
                                line-height: 1.6;
                            ">
                                Email otomatis. Mohon tidak membalas email ini.
                                <br>
                                &copy; {{ now()->year }} {{ $appName }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>