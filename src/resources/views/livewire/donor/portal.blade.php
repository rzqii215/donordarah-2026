<div class="donor-page">
    @if (session()->has('success'))
        <div class="donor-alert donor-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @switch($section)
        @case('beranda')
            <section class="donor-hero">
                <div class="donor-hero-decoration donor-hero-cross-one">
                    +
                </div>

                <div class="donor-hero-decoration donor-hero-cross-two">
                    +
                </div>

                <div class="donor-hero-decoration donor-hero-cross-three">
                    +
                </div>

                <div class="donor-hero-copy">
                    <span class="donor-eyebrow">
                        Portal Pendonor
                    </span>

                    <h1>
                        Setetes Darah Anda,
                        <br>
                        Sejuta Harapan Mereka
                    </h1>

                    <p>
                        Donor darah hari ini, selamatkan
                        nyawa esok hari.
                    </p>

                    <a
                        href="{{ route('donor.jadwal') }}"
                        wire:navigate
                        class="donor-primary-button"
                    >
                        Cari Jadwal Donor

                        <x-donor.icon name="arrow-right" />
                    </a>
                </div>

                <div class="donor-hero-illustration">
                    <svg
                        viewBox="0 0 420 320"
                        role="img"
                        aria-label="Ilustrasi kantong donor darah"
                    >
                        <defs>
                            <linearGradient
                                id="bagGradient"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stop-color="#fff7f7"
                                />

                                <stop
                                    offset="100%"
                                    stop-color="#ffe0e0"
                                />
                            </linearGradient>

                            <linearGradient
                                id="bloodGradient"
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="1"
                            >
                                <stop
                                    offset="0%"
                                    stop-color="#ef4444"
                                />

                                <stop
                                    offset="100%"
                                    stop-color="#c81e1e"
                                />
                            </linearGradient>

                            <filter id="bagShadow">
                                <feDropShadow
                                    dx="0"
                                    dy="14"
                                    stdDeviation="14"
                                    flood-color="#ef4444"
                                    flood-opacity=".18"
                                />
                            </filter>
                        </defs>

                        <path
                            d="M250 50v32"
                            stroke="#ef4444"
                            stroke-width="8"
                            stroke-linecap="round"
                        />

                        <rect
                            x="214"
                            y="35"
                            width="72"
                            height="30"
                            rx="10"
                            fill="#fff"
                            stroke="#fecaca"
                            stroke-width="5"
                        />

                        <rect
                            x="170"
                            y="70"
                            width="160"
                            height="190"
                            rx="34"
                            fill="url(#bagGradient)"
                            stroke="#fecaca"
                            stroke-width="7"
                            filter="url(#bagShadow)"
                        />

                        <path
                            d="M178 165c24-20 50-13 72 0 25 14 50 18 73-2v71c0 10-8 18-18 18H196c-10 0-18-8-18-18Z"
                            fill="url(#bloodGradient)"
                        />

                        <path
                            d="M250 135c-22-28-66 7 0 57 66-50 22-85 0-57Z"
                            fill="#fff"
                        />

                        <path
                            d="M180 246c-65 18-82 71-64 95"
                            fill="none"
                            stroke="#dc2626"
                            stroke-width="8"
                            stroke-linecap="round"
                        />

                        <path
                            d="M116 340c-15-15-39-5-39 15 0 17 18 29 39 44 21-15 39-27 39-44 0-20-24-30-39-15Z"
                            fill="none"
                            stroke="#dc2626"
                            stroke-width="7"
                        />

                        <circle
                            cx="101"
                            cy="92"
                            r="7"
                            fill="#fecaca"
                        />

                        <path
                            d="M101 75v34M84 92h34"
                            stroke="#fecaca"
                            stroke-width="7"
                            stroke-linecap="round"
                        />

                        <path
                            d="M359 125v34M342 142h34"
                            stroke="#fecaca"
                            stroke-width="7"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
            </section>

            <section class="donor-dashboard-grid">
                <article class="donor-dashboard-card">
                    <div class="donor-card-icon donor-card-icon-red">
                        <x-donor.icon name="calendar" />
                    </div>

                    <div class="donor-card-heading">
                        <span>Jadwal Terdekat</span>
                    </div>

                    @if ($jadwalTerdekat)
                        <strong class="donor-card-primary-value">
                            {{ $jadwalTerdekat
                                ->mulai_pada
                                ?->translatedFormat('d F Y') }}
                        </strong>

                        <p>
                            {{ $jadwalTerdekat
                                ->mulai_pada
                                ?->format('H:i') }}
                            –
                            {{ $jadwalTerdekat
                                ->selesai_pada
                                ?->format('H:i') }}
                        </p>

                        <p>{{ $namaLokasiJadwal }}</p>
                    @else
                        <strong class="donor-card-primary-value">
                            Belum tersedia
                        </strong>

                        <p>
                            Jadwal donor berikutnya belum
                            dipublikasikan.
                        </p>
                    @endif

                    <a
                        href="{{ route('donor.jadwal') }}"
                        wire:navigate
                        class="donor-text-link"
                    >
                        Lihat Detail
                    </a>
                </article>

                <article class="donor-dashboard-card">
                    <div class="donor-card-icon donor-card-icon-rose">
                        <x-donor.icon name="droplet" />
                    </div>

                    <div class="donor-card-heading">
                        <span>Stok Darah</span>

                        <strong>
                            {{ number_format($totalStok) }}
                        </strong>
                    </div>

                    <div class="donor-blood-stock-list">
                        @foreach (
                            ['A', 'B', 'O', 'AB']
                            as $golongan
                        )
                            <div>
                                <span>{{ $golongan }}</span>

                                <strong>
                                    {{ $stokPerGolongan[$golongan] ?? 0 }}
                                    kantong
                                </strong>
                            </div>
                        @endforeach
                    </div>

                    <a
                        href="{{ route('donor.stok') }}"
                        wire:navigate
                        class="donor-text-link"
                    >
                        Lihat Semua
                    </a>
                </article>

                <article class="donor-dashboard-card">
                    <div class="donor-card-icon donor-card-icon-gray">
                        <x-donor.icon name="map-pin" />
                    </div>

                    <div class="donor-card-heading">
                        <span>Lokasi Terdekat</span>
                    </div>

                    <strong class="donor-card-primary-value">
                        {{ number_format($jumlahLokasi) }}
                        lokasi tersedia
                    </strong>

                    <p>
                        Temukan lokasi donor yang paling
                        mudah dijangkau.
                    </p>

                    <a
                        href="{{ route('donor.lokasi') }}"
                        wire:navigate
                        class="donor-text-link"
                    >
                        Lihat Semua
                    </a>
                </article>
            </section>

            <section class="donor-secondary-grid">
                <article class="donor-wide-card donor-quick-card">
                    <div>
                        <span class="donor-eyebrow">
                            Dampak Anda
                        </span>

                        <h2>
                            Terima kasih sudah menjadi
                            bagian dari kebaikan.
                        </h2>

                        <p>
                            Setiap pendaftaran dan donor
                            yang selesai berkontribusi
                            langsung bagi pasien yang
                            membutuhkan.
                        </p>
                    </div>

                    <div class="donor-impact-stats">
                        <div>
                            <strong>
                                {{ number_format(
                                    $jumlahRiwayat
                                ) }}
                            </strong>

                            <span>Pendaftaran</span>
                        </div>

                        <div>
                            <strong>
                                {{ number_format(
                                    $donorSelesai
                                ) }}
                            </strong>

                            <span>Donor Selesai</span>
                        </div>
                    </div>
                </article>

                <article class="donor-wide-card donor-health-card">
                    <div class="donor-health-icon">
                        <x-donor.icon name="droplet" />
                    </div>

                    <div>
                        <span class="donor-eyebrow">
                            Persiapan Donor
                        </span>

                        <h3>
                            Pastikan tubuh cukup istirahat.
                        </h3>

                        <p>
                            Minum air putih, konsumsi
                            makanan bergizi, dan hindari
                            aktivitas berat sebelum donor.
                        </p>
                    </div>
                </article>
            </section>
            @break

        @case('jadwal')
            <section class="donor-page-header">
                <div>
                    <span class="donor-eyebrow">
                        Agenda Kegiatan
                    </span>

                    <h1>Jadwal Donor</h1>

                    <p>
                        Temukan jadwal donor yang masih
                        tersedia dan sesuai dengan waktu
                        Anda.
                    </p>
                </div>

                <label class="donor-search-field">
                    <x-donor.icon name="search" />

                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari jadwal donor..."
                    >
                </label>
            </section>

            <section class="donor-content-grid donor-schedule-grid">
                @forelse ($jadwal as $item)
                    <article
                        class="donor-schedule-card"
                        wire:key="jadwal-{{ $item->id }}"
                    >
                        <div class="donor-schedule-date">
                            <strong>
                                {{ $item
                                    ->mulai_pada
                                    ?->format('d') }}
                            </strong>

                            <span>
                                {{ $item
                                    ->mulai_pada
                                    ?->translatedFormat('M') }}
                            </span>
                        </div>

                        <div class="donor-schedule-content">
                            <span class="donor-status-pill donor-status-open">
                                Pendaftaran Dibuka
                            </span>

                            <h2>{{ $item->judul }}</h2>

                            <div class="donor-schedule-meta">
                                <span>
                                    <x-donor.icon name="calendar" />

                                    {{ $item
                                        ->mulai_pada
                                        ?->format('H:i') }}
                                    –
                                    {{ $item
                                        ->selesai_pada
                                        ?->format('H:i') }}
                                </span>

                                <span>
                                    <x-donor.icon name="map-pin" />

                                    {{ $lokasiJadwal[
                                        $item->lokasi_donor_id
                                    ] ?? 'Lokasi donor' }}
                                </span>
                            </div>

                            <p>
                                {{ Str::limit(
                                    strip_tags(
                                        (string) $item->deskripsi
                                    ),
                                    125
                                ) }}
                            </p>

                            <button
                                type="button"
                                class="donor-secondary-button"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="donor-empty-state">
                        <x-donor.icon name="calendar" />

                        <h2>Jadwal belum tersedia</h2>

                        <p>
                            Belum ada jadwal yang sesuai
                            dengan pencarian Anda.
                        </p>
                    </div>
                @endforelse
            </section>

            @if ($jadwal->hasPages())
                <div class="donor-pagination">
                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($jadwal->onFirstPage())
                    >
                        Sebelumnya
                    </button>

                    <span>
                        Halaman {{ $jadwal->currentPage() }}
                        dari {{ $jadwal->lastPage() }}
                    </span>

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled(! $jadwal->hasMorePages())
                    >
                        Berikutnya
                    </button>
                </div>
            @endif
            @break

        @case('lokasi')
            <section class="donor-page-header">
                <div>
                    <span class="donor-eyebrow">
                        Lokasi Kegiatan
                    </span>

                    <h1>Lokasi Donor</h1>

                    <p>
                        Cari lokasi donor yang paling mudah
                        dijangkau dari tempat Anda.
                    </p>
                </div>

                <label class="donor-search-field">
                    <x-donor.icon name="search" />

                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari lokasi atau kota..."
                    >
                </label>
            </section>

            <section class="donor-content-grid donor-location-grid">
                @forelse ($lokasiCards as $lokasiItem)
                    <article
                        class="donor-location-card"
                        wire:key="lokasi-{{ $lokasiItem['id'] }}"
                    >
                        <div class="donor-location-map">
                            <div class="donor-map-pin">
                                <x-donor.icon name="map-pin" />
                            </div>
                        </div>

                        <div class="donor-location-content">
                            <h2>
                                {{ $lokasiItem['nama'] }}
                            </h2>

                            <p>
                                {{ $lokasiItem['alamat'] }}
                            </p>

                            <div class="donor-location-meta">
                                <span>
                                    {{ $lokasiItem['kota'] }}
                                </span>

                                <span>
                                    {{ $lokasiItem['provinsi'] }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="donor-secondary-button"
                            >
                                Lihat Lokasi
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="donor-empty-state">
                        <x-donor.icon name="map-pin" />

                        <h2>Lokasi belum ditemukan</h2>

                        <p>
                            Coba gunakan kata pencarian
                            yang berbeda.
                        </p>
                    </div>
                @endforelse
            </section>

            @if ($lokasi->hasPages())
                <div class="donor-pagination">
                    <button
                        type="button"
                        wire:click="previousPage"
                        @disabled($lokasi->onFirstPage())
                    >
                        Sebelumnya
                    </button>

                    <span>
                        Halaman {{ $lokasi->currentPage() }}
                        dari {{ $lokasi->lastPage() }}
                    </span>

                    <button
                        type="button"
                        wire:click="nextPage"
                        @disabled(! $lokasi->hasMorePages())
                    >
                        Berikutnya
                    </button>
                </div>
            @endif
            @break

        @case('stok')
            <section class="donor-page-header">
                <div>
                    <span class="donor-eyebrow">
                        Informasi Ketersediaan
                    </span>

                    <h1>Stok Darah</h1>

                    <p>
                        Ringkasan stok darah tersedia
                        berdasarkan golongan darah dan
                        rhesus.
                    </p>
                </div>

                <div class="donor-stock-summary">
                    <span>Total Stok</span>

                    <strong>
                        {{ number_format(
                            $metaStok[
                                'total_kantong_tersedia'
                            ] ?? 0
                        ) }}
                    </strong>

                    <small>kantong tersedia</small>
                </div>
            </section>

            <section class="donor-blood-grid">
                @foreach ($stokDarah as $stok)
                    <article
                        class="donor-blood-card"
                        wire:key="stok-{{ $stok['kode'] }}"
                    >
                        <div class="donor-blood-type">
                            {{ $stok['kode'] }}
                        </div>

                        <div class="donor-blood-card-copy">
                            <span>Kantong tersedia</span>

                            <strong>
                                {{ number_format(
                                    $stok['jumlah_kantong']
                                ) }}
                            </strong>

                            <small>
                                {{ number_format(
                                    $stok['total_volume_ml']
                                ) }}
                                ml total volume
                            </small>
                        </div>

                        <div class="donor-blood-progress">
                            <span
                                style="
                                    width:
                                    {{ min(
                                        100,
                                        $stok['jumlah_kantong'] * 5
                                    ) }}%;
                                "
                            ></span>
                        </div>
                    </article>
                @endforeach
            </section>
            @break

        @case('riwayat')
            <section class="donor-page-header">
                <div>
                    <span class="donor-eyebrow">
                        Aktivitas Pendonor
                    </span>

                    <h1>Riwayat Donor</h1>

                    <p>
                        Pantau status seluruh pendaftaran
                        donor Anda.
                    </p>
                </div>
            </section>

            <div class="donor-table-card">
                <div class="donor-table-responsive">
                    <table class="donor-table">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Jadwal</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayat as $item)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $item
                                                ->nomor_pendaftaran }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $item
                                            ->jadwal
                                            ?->judul
                                            ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item
                                            ->jadwal
                                            ?->mulai_pada
                                            ?->translatedFormat(
                                                'd M Y'
                                            )
                                            ?? '-' }}
                                    </td>

                                    <td>
                                        <span
                                            class="
                                                donor-status-pill
                                                donor-status-{{
                                                    $this->valueEnum(
                                                        $item->status
                                                    )
                                                }}
                                            "
                                        >
                                            {{ $this->labelEnum(
                                                $item->status
                                            ) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="4"
                                        class="donor-table-empty"
                                    >
                                        Belum ada riwayat
                                        pendaftaran donor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($riwayat->hasPages())
                    <div class="donor-pagination donor-pagination-card">
                        <button
                            type="button"
                            wire:click="previousPage"
                            @disabled($riwayat->onFirstPage())
                        >
                            Sebelumnya
                        </button>

                        <span>
                            Halaman
                            {{ $riwayat->currentPage() }}
                            dari
                            {{ $riwayat->lastPage() }}
                        </span>

                        <button
                            type="button"
                            wire:click="nextPage"
                            @disabled(! $riwayat->hasMorePages())
                        >
                            Berikutnya
                        </button>
                    </div>
                @endif
            </div>
            @break

        @case('profil')
            <section class="donor-page-header">
                <div>
                    <span class="donor-eyebrow">
                        Pengaturan Akun
                    </span>

                    <h1>Profil Saya</h1>

                    <p>
                        Pastikan informasi pribadi dan
                        kontak darurat selalu terbaru.
                    </p>
                </div>
            </section>

            <form
                wire:submit="simpanProfil"
                class="donor-profile-layout"
            >
                <aside class="donor-profile-summary">
                    <div class="donor-profile-avatar">
                        {{ strtoupper(
                            mb_substr(
                                auth()->user()->name ?? 'P',
                                0,
                                1
                            )
                        ) }}
                    </div>

                    <h2>
                        {{ auth()->user()->name }}
                    </h2>

                    <p>
                        {{ auth()->user()->email }}
                    </p>

                    @if ($profil)
                        <div class="donor-profile-blood">
                            <span>Golongan Darah</span>

                            <strong>
                                {{ $this->labelEnum(
                                    $profil->golongan_darah
                                ) }}
                                {{ $this->simbolRhesus(
                                    $profil->rhesus
                                ) }}
                            </strong>
                        </div>
                    @endif
                </aside>

                <div class="donor-profile-form">
                    @error('profil')
                        <div class="donor-alert donor-alert-error">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="donor-form-section">
                        <h2>Informasi Pribadi</h2>

                        <div class="donor-form-grid">
                            <label class="donor-form-field">
                                <span>Nama Lengkap</span>

                                <input
                                    type="text"
                                    wire:model="name"
                                >

                                @error('name')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="donor-form-field">
                                <span>Nomor Telepon</span>

                                <input
                                    type="text"
                                    wire:model="nomorTelepon"
                                >

                                @error('nomorTelepon')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="donor-form-field">
                                <span>Tanggal Lahir</span>

                                <input
                                    type="date"
                                    wire:model="tanggalLahir"
                                >

                                @error('tanggalLahir')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="donor-form-field">
                                <span>Jenis Kelamin</span>

                                <select wire:model="jenisKelamin">
                                    <option value="">
                                        Pilih jenis kelamin
                                    </option>

                                    <option value="male">
                                        Laki-laki
                                    </option>

                                    <option value="female">
                                        Perempuan
                                    </option>
                                </select>

                                @error('jenisKelamin')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>
                        </div>
                    </div>

                    <div class="donor-form-section">
                        <h2>Alamat</h2>

                        <div class="donor-form-grid">
                            <label class="donor-form-field donor-form-field-full">
                                <span>Alamat Lengkap</span>

                                <textarea
                                    rows="4"
                                    wire:model="alamat"
                                ></textarea>

                                @error('alamat')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="donor-form-field">
                                <span>Provinsi</span>

                                <input
                                    type="text"
                                    wire:model="provinsi"
                                >
                            </label>

                            <label class="donor-form-field">
                                <span>Kota/Kabupaten</span>

                                <input
                                    type="text"
                                    wire:model="kota"
                                >
                            </label>

                            <label class="donor-form-field">
                                <span>Kecamatan</span>

                                <input
                                    type="text"
                                    wire:model="kecamatan"
                                >
                            </label>

                            <label class="donor-form-field">
                                <span>Kode Pos</span>

                                <input
                                    type="text"
                                    wire:model="kodePos"
                                >
                            </label>
                        </div>
                    </div>

                    <div class="donor-form-section">
                        <h2>Kontak Darurat</h2>

                        <div class="donor-form-grid">
                            <label class="donor-form-field">
                                <span>Nama Kontak</span>

                                <input
                                    type="text"
                                    wire:model="namaKontakDarurat"
                                >
                            </label>

                            <label class="donor-form-field">
                                <span>Nomor Telepon</span>

                                <input
                                    type="text"
                                    wire:model="teleponKontakDarurat"
                                >
                            </label>

                            <label class="donor-toggle-field donor-form-field-full">
                                <input
                                    type="checkbox"
                                    wire:model="bersediaDihubungi"
                                >

                                <span class="donor-toggle-control"></span>

                                <span>
                                    Saya bersedia dihubungi
                                    terkait kebutuhan donor darah.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="donor-form-actions">
                        <button
                            type="submit"
                            class="donor-primary-button"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>
                                Simpan Perubahan
                            </span>

                            <span wire:loading>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            @break
    @endswitch
</div>