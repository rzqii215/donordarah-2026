<div class="donor-profile-page">
    
<section class="donor-profile-hero">
        <div>
            <p class="donor-profile-eyebrow">
                Profil Pendonor
            </p>

            <h1>
                Data Pendonor Anda
            </h1>

            <p>
                Pastikan data pribadi, golongan darah, rhesus, dan alamat
                sudah sesuai agar proses pendaftaran donor berjalan lancar.
            </p>
        </div>

        <div class="donor-profile-hero-card">
            <span>Kelengkapan Profil</span>

            <strong>
                {{ $kelengkapan['persentase'] }}%
            </strong>

            <div class="donor-profile-progress">
                <div style="width: {{ $kelengkapan['persentase'] }}%;"></div>
            </div>

            <p>
                {{ $kelengkapan['lengkap'] ? 'Profil sudah lengkap' : 'Masih ada data yang perlu dilengkapi' }}
            </p>
        </div>
    </section>

    @if ($notifikasiBerhasil || session('success'))
        <div class="donor-profile-toast">
            <div class="donor-profile-toast-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <path d="M20 6 9 17l-5-5" />
                </svg>
            </div>

            <div class="donor-profile-toast-content">
                <strong>Berhasil</strong>
                <p>{{ $notifikasiBerhasil ?: session('success') }}</p>
            </div>

            <button
                type="button"
                wire:click="hapusNotifikasi"
                aria-label="Tutup notifikasi"
            >
                &times;
            </button>
        </div>
    @endif

    <section class="donor-profile-summary">
        <article>
            <span>Kode Pendonor</span>
            <strong>{{ $ringkasan['kode_pendonor'] }}</strong>
            <p>Identitas pendonor</p>
        </article>

        <article>
            <span>Golongan Darah</span>
            <strong>{{ $ringkasan['golongan_rhesus'] }}</strong>
            <p>Golongan dan rhesus</p>
        </article>

        <article>
            <span>Usia</span>
            <strong>{{ $ringkasan['umur'] }}</strong>
            <p>Berdasarkan tanggal lahir</p>
        </article>

        <article>
            <span>Donor Selesai</span>
            <strong>{{ $ringkasan['donor_selesai'] }}</strong>
            <p>Dari {{ $ringkasan['total_pendaftaran'] }} pendaftaran</p>
        </article>
    </section>

    @if (! $kelengkapan['lengkap'])
        <section class="donor-profile-warning">
            <div>
                <h2>
                    Data belum lengkap
                </h2>

                <p>
                    Lengkapi data berikut supaya proses pendaftaran donor lebih mudah:
                </p>
            </div>

            <div class="donor-profile-missing-list">
                @foreach ($kelengkapan['belum_lengkap'] as $item)
                    <span>{{ $item }}</span>
                @endforeach
            </div>
        </section>
    @endif

    <form wire:submit="simpanProfil" class="donor-profile-form">
        <section class="donor-profile-section">
            <div class="donor-profile-section-heading">
                <h2>Informasi Akun</h2>
                <p>Data ini digunakan untuk masuk dan menerima informasi donor.</p>
            </div>

            <div class="donor-profile-grid">
                <label class="donor-profile-field">
                    <span>Nama Lengkap</span>

                    <input
                        type="text"
                        wire:model="name"
                        placeholder="Nama lengkap"
                        autocomplete="name"
                    >

                    @error('name')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Alamat Email</span>

                    <input
                        type="email"
                        wire:model="email"
                        placeholder="nama@email.com"
                        autocomplete="email"
                    >

                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Nomor HP</span>

                    <input
                        type="text"
                        wire:model="nomor_telepon"
                        placeholder="08xxxxxxxxxx"
                        autocomplete="tel"
                    >

                    @error('nomor_telepon')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Bersedia Dihubungi</span>

                    <select wire:model="bersedia_dihubungi">
                        <option value="1">Ya, bersedia</option>
                        <option value="0">Tidak</option>
                    </select>

                    @error('bersedia_dihubungi')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        <section class="donor-profile-section">
            <div class="donor-profile-section-heading">
                <h2>Profil Pendonor</h2>
                <p>Data ini membantu petugas menyesuaikan proses donor darah.</p>
            </div>

            <div class="donor-profile-grid">
                <label class="donor-profile-field">
                    <span>Tanggal Lahir</span>

                    <input
                        type="date"
                        wire:model="tanggal_lahir"
                    >

                    @error('tanggal_lahir')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Jenis Kelamin</span>

                    <select wire:model="jenis_kelamin">
                        <option value="">Pilih jenis kelamin</option>

                        @foreach ($this->opsiJenisKelamin() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('jenis_kelamin')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Golongan Darah</span>

                    <select wire:model="golongan_darah">
                        <option value="">Pilih golongan darah</option>

                        @foreach ($this->opsiGolonganDarah() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('golongan_darah')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Rhesus</span>

                    <select wire:model="rhesus">
                        <option value="">Pilih rhesus</option>

                        @foreach ($this->opsiRhesusDarah() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('rhesus')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        <section class="donor-profile-section">
            <div class="donor-profile-section-heading">
                <h2>Alamat Domisili</h2>
                <p>Alamat digunakan untuk kebutuhan administrasi donor.</p>
            </div>

            <div class="donor-profile-grid">
                <label class="donor-profile-field">
                    <span>Provinsi</span>

                    <input
                        type="text"
                        wire:model="provinsi"
                        placeholder="Contoh: DKI Jakarta"
                    >

                    @error('provinsi')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Kota/Kabupaten</span>

                    <input
                        type="text"
                        wire:model="kota"
                        placeholder="Contoh: Jakarta Selatan"
                    >

                    @error('kota')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Kecamatan</span>

                    <input
                        type="text"
                        wire:model="kecamatan"
                        placeholder="Opsional"
                    >

                    @error('kecamatan')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field">
                    <span>Kode Pos</span>

                    <input
                        type="text"
                        wire:model="kode_pos"
                        placeholder="Opsional"
                    >

                    @error('kode_pos')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="donor-profile-field donor-profile-field-full">
                    <span>Alamat Lengkap</span>

                    <textarea
                        wire:model="alamat"
                        rows="4"
                        placeholder="Tulis alamat lengkap domisili Anda"
                    ></textarea>

                    @error('alamat')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </section>

        <div class="donor-profile-actions">
            <a href="{{ route('donor.riwayat') }}">
                Lihat Riwayat Donor
            </a>

            <button
                type="submit"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    Simpan Profil
                </span>

                <span wire:loading>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>

    <style>

        .donor-profile-toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 120;
            width: min(420px, calc(100vw - 32px));
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            border: 1px solid #bbf7d0;
            border-radius: 20px;
            color: #14532d;
            background: #f0fdf4;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        }

        .donor-profile-toast-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 15px;
            color: #16a34a;
            background: #dcfce7;
        }

        .donor-profile-toast-icon svg {
            width: 22px;
            height: 22px;
        }

        .donor-profile-toast-content {
            flex: 1;
            min-width: 0;
        }

        .donor-profile-toast-content strong {
            display: block;
            color: #14532d;
            font-size: 14px;
            font-weight: 900;
        }

        .donor-profile-toast-content p {
            margin: 4px 0 0;
            color: #166534;
            font-size: 13px;
            line-height: 1.6;
            font-weight: 700;
        }

        .donor-profile-toast button {
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 10px;
            color: #166534;
            background: transparent;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }

        .donor-profile-toast button:hover {
            background: #dcfce7;
        }

        .donor-profile-page {
            display: grid;
            gap: 28px;
        }

        .donor-profile-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 24px;
            align-items: end;
        }

        .donor-profile-eyebrow {
            margin: 0 0 12px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .donor-profile-hero h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(36px, 5vw, 54px);
            line-height: 1.05;
            letter-spacing: -0.06em;
        }

        .donor-profile-hero p:not(.donor-profile-eyebrow) {
            max-width: 760px;
            margin: 16px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.8;
        }

        .donor-profile-hero-card,
        .donor-profile-summary article,
        .donor-profile-section {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
        }

        .donor-profile-hero-card {
            padding: 24px;
            border-color: #fee2e2;
            border-radius: 26px;
        }

        .donor-profile-hero-card span {
            color: #dc2626;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .donor-profile-hero-card strong {
            display: block;
            margin-top: 12px;
            color: #0f172a;
            font-size: 52px;
            line-height: 1;
        }

        .donor-profile-progress {
            height: 9px;
            overflow: hidden;
            margin-top: 18px;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .donor-profile-progress div {
            height: 100%;
            border-radius: 999px;
            background: #dc2626;
        }

        .donor-profile-hero-card p {
            margin: 12px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .donor-profile-alert {
            padding: 16px 18px;
            border: 1px solid #bbf7d0;
            border-radius: 18px;
            color: #166534;
            background: #f0fdf4;
            font-size: 14px;
            font-weight: 900;
        }

        .donor-profile-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-profile-summary article {
            padding: 22px;
            border-radius: 22px;
        }

        .donor-profile-summary span {
            color: #64748b;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .donor-profile-summary strong {
            display: block;
            margin-top: 8px;
            color: #0f172a;
            font-size: 28px;
            line-height: 1.15;
        }

        .donor-profile-summary p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .donor-profile-warning {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 420px);
            gap: 20px;
            padding: 22px;
            border: 1px solid #fee2e2;
            border-radius: 24px;
            background: #fff7f7;
        }

        .donor-profile-warning h2 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .donor-profile-warning p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .donor-profile-missing-list {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .donor-profile-missing-list span {
            display: inline-flex;
            min-height: 34px;
            align-items: center;
            padding: 0 13px;
            border-radius: 999px;
            color: #991b1b;
            background: #fee2e2;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-profile-form {
            display: grid;
            gap: 24px;
        }

        .donor-profile-section {
            display: grid;
            gap: 22px;
            padding: 24px;
            border-radius: 24px;
        }

        .donor-profile-section-heading h2 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .donor-profile-section-heading p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .donor-profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .donor-profile-field {
            display: grid;
            gap: 8px;
        }

        .donor-profile-field-full {
            grid-column: 1 / -1;
        }

        .donor-profile-field span {
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .donor-profile-field input,
        .donor-profile-field select,
        .donor-profile-field textarea {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            font: inherit;
            font-size: 13px;
            transition: 160ms ease;
        }

        .donor-profile-field input,
        .donor-profile-field select {
            min-height: 50px;
            padding: 0 15px;
        }

        .donor-profile-field textarea {
            resize: vertical;
            padding: 14px 15px;
        }

        .donor-profile-field input:focus,
        .donor-profile-field select:focus,
        .donor-profile-field textarea:focus {
            border-color: #f87171;
            box-shadow: 0 0 0 4px rgba(254, 202, 202, 0.42);
        }

        .donor-profile-field small {
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
        }

        .donor-profile-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .donor-profile-actions a,
        .donor-profile-actions button {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 18px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .donor-profile-actions a {
            border: 1px solid #e2e8f0;
            color: #0f172a;
            background: #ffffff;
        }

        .donor-profile-actions button {
            min-width: 180px;
            border: 0;
            color: #ffffff;
            background: #dc2626;
            cursor: pointer;
            box-shadow: 0 16px 32px rgba(220, 38, 38, 0.18);
        }

        .donor-profile-actions button:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        @media (max-width: 980px) {
            .donor-profile-hero,
            .donor-profile-summary,
            .donor-profile-warning {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .donor-profile-grid {
                grid-template-columns: 1fr;
            }

            .donor-profile-field-full {
                grid-column: auto;
            }

            .donor-profile-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }

            .donor-profile-actions a,
            .donor-profile-actions button {
                width: 100%;
            }
        }
    </style>
</div>