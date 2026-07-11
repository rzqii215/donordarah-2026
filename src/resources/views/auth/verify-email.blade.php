<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Verifikasi Email — {{ config('app.name', 'Donor Darah') }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: #172033;
            background:
                radial-gradient(
                    circle at top left,
                    rgba(220, 38, 38, 0.13),
                    transparent 34%
                ),
                radial-gradient(
                    circle at bottom right,
                    rgba(15, 23, 42, 0.1),
                    transparent 38%
                ),
                #f7f8fc;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        button,
        input {
            font: inherit;
        }

        .page {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .card {
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            box-shadow:
                0 28px 70px rgba(15, 23, 42, 0.12),
                0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .card-header {
            padding: 34px 36px 30px;
            text-align: center;
            background:
                linear-gradient(
                    145deg,
                    #b91c1c,
                    #dc2626
                );
        }

        .icon {
            display: flex;
            width: 66px;
            height: 66px;
            margin: 0 auto;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 20px;
        }

        .icon svg {
            width: 32px;
            height: 32px;
        }

        .card-header h1 {
            margin: 18px 0 0;
            color: #ffffff;
            font-size: 27px;
            line-height: 1.25;
        }

        .card-header p {
            margin: 9px 0 0;
            color: #fee2e2;
            font-size: 14px;
            line-height: 1.7;
        }

        .card-body {
            padding: 34px 36px 36px;
        }

        .description {
            margin: 0;
            color: #5d6678;
            font-size: 14px;
            line-height: 1.75;
        }

        .email-label {
            margin: 22px 0 8px;
            color: #697386;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .email-box {
            padding: 14px 16px;
            color: #172033;
            background: #f8fafc;
            border: 1px solid #dfe4ec;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            word-break: break-all;
        }

        .steps {
            margin: 22px 0 0;
            padding: 17px 18px;
            color: #5d6678;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.7;
        }

        .steps strong {
            color: #9a3412;
        }

        .alert {
            margin-bottom: 20px;
            padding: 13px 15px;
            color: #047857;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.65;
        }

        .primary-button {
            display: block;
            width: 100%;
            margin-top: 24px;
            padding: 14px 20px;
            color: #ffffff;
            background: #dc2626;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 9px 20px rgba(220, 38, 38, 0.2);
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
            transition:
                background 160ms ease,
                transform 160ms ease,
                box-shadow 160ms ease;
        }

        .primary-button:hover {
            background: #b91c1c;
            box-shadow: 0 12px 24px rgba(185, 28, 28, 0.24);
            transform: translateY(-1px);
        }

        .primary-button:active {
            transform: translateY(0);
        }

        .secondary-link {
            display: block;
            margin-top: 20px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
        }

        .secondary-link:hover {
            color: #dc2626;
        }

        .help {
            margin: 22px 0 0;
            padding-top: 20px;
            color: #8991a1;
            border-top: 1px solid #edf0f4;
            font-size: 12px;
            line-height: 1.7;
            text-align: center;
        }

        @media (max-width: 560px) {
            .page {
                padding: 18px 14px;
            }

            .card {
                border-radius: 20px;
            }

            .card-header {
                padding: 29px 23px 26px;
            }

            .card-header h1 {
                font-size: 24px;
            }

            .card-body {
                padding: 27px 23px 29px;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <section class="card">
            <header class="card-header">
                <div class="icon">
                    <svg
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.659 5.408a2.25 2.25 0 0 1-2.382 0L2.25 6.75"
                        />
                    </svg>
                </div>

                <h1>Verifikasi Email</h1>

                <p>
                    Satu langkah lagi untuk mengaktifkan akun Anda.
                </p>
            </header>

            <div class="card-body">
                @if (session('status') === 'verification-link-sent')
                    <div class="alert">
                        Link verifikasi baru berhasil dikirim. Silakan periksa kotak masuk email Anda.
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="description">
                    Kami telah mengirimkan link verifikasi ke alamat email berikut:
                </p>

                <p class="email-label">
                    Alamat email
                </p>

                <div class="email-box">
                    {{ $user->email }}
                </div>

                <div class="steps">
                    Buka email tersebut, kemudian tekan tombol
                    <strong>Verifikasi Email</strong>
                    untuk mengaktifkan akun.
                </div>

                <form
                    method="POST"
                    action="{{ route('verification.send') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <a
                    href="{{ route('home') }}"
                    class="secondary-link"
                >
                    Saya Sudah Melakukan Verifikasi
                </a>

                <p class="help">
                    Belum menerima email? Periksa folder Spam atau Promosi.
                    Tunggu beberapa saat sebelum mengirim ulang.
                </p>
            </div>
        </section>
    </main>
</body>
</html>