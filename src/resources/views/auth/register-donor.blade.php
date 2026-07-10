<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pendonor — donordarah</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(239, 68, 68, 0.12), transparent 32rem),
                #f8fafc;
            color: #0f172a;
        }

        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
        }

        .card {
            width: 100%;
            max-width: 760px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 28px;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .header {
            padding: 32px;
            background: linear-gradient(135deg, #fff1f2, #ffffff);
            border-bottom: 1px solid #fee2e2;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 900;
            letter-spacing: 0.08em;
            color: #dc2626;
            text-transform: uppercase;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            color: #dc2626;
            background: #ffffff;
            border: 1px solid #fecaca;
        }

        .content {
            padding: 36px 32px 32px;
        }

        .eyebrow {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.06;
            letter-spacing: -0.05em;
            color: #0f172a;
        }

        .description {
            margin: 18px 0 0;
            max-width: 600px;
            color: #64748b;
            line-height: 1.75;
            font-size: 16px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 20px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 800;
            transition: 0.2s ease;
        }

        .button-primary {
            background: #ef233c;
            color: #ffffff;
            box-shadow: 0 16px 35px rgba(239, 35, 60, 0.25);
        }

        .button-secondary {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .info {
            margin-top: 28px;
            padding: 18px;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            line-height: 1.65;
        }

        .info strong {
            color: #0f172a;
        }

        @media (max-width: 640px) {
            .page {
                padding: 18px;
            }

            .header,
            .content {
                padding: 24px;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="header">
                
<div class="brand">
                    <div class="brand-icon">
                        ♡
                    </div>

                    <div>
                        Donor<br>Darah
                    </div>
                </div>
            </div>

            <div class="content">
                <p class="eyebrow">
                    Pendaftaran Pendonor
                </p>

                <h1>
                    Daftar sebagai Pendonor Darah
                </h1>

                <p class="description">
                    Halaman pendaftaran pendonor sedang disiapkan. Setelah selesai,
                    Anda dapat membuat akun, melengkapi profil, memilih jadwal donor,
                    dan melihat riwayat donor dari portal pendonor.
                </p>

                <div class="actions">
                    <a href="{{ url('/login') }}" class="button button-primary">
                        Kembali ke Login
                    </a>

                    <a href="{{ route('register.pemohon-donor') }}" class="button button-secondary">
                        Daftar sebagai Pemohon Donor
                    </a>
                </div>

                <div class="info">
                    <strong>Tahap berikutnya:</strong>
                    form pendaftaran pendonor akan dibuat dengan data nama lengkap,
                    email, password, nomor HP, tanggal lahir, golongan darah,
                    rhesus, dan alamat.
                </div>
            </div>
        </section>
    </main>
</body>
</html>