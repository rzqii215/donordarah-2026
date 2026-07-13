@php
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

    $statusVerifikasi =
        $profil?->status_verifikasi;

    $statusVerifikasiValue =
        $nilaiEnum($statusVerifikasi);

    $statusVerifikasiLabel =
        $profil !== null
            ? $labelEnum($statusVerifikasi)
            : 'Profil belum tersedia';

    $statusVerifikasiClass = match (
        $statusVerifikasiValue
    ) {
        'verified',
        'approved',
        'terverifikasi',
        'disetujui' =>
            'bg-[#dff7e7] text-[#176b3a]',

        'rejected',
        'ditolak' =>
            'bg-[#ffe4e7] text-[#991b2f]',

        default =>
            'bg-[#fff1c9] text-[#8a5a00]',
    };

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna->name;

    $inisial = collect(
        explode(' ', $namaPemohon)
    )
        ->filter()
        ->take(2)
        ->map(
            fn (string $bagian): string =>
                mb_substr($bagian, 0, 1)
        )
        ->implode('');

    $inisial = filled($inisial)
        ? mb_strtoupper($inisial)
        : 'PD';

    $fieldProfil = [
        $pengguna->name,
        $pengguna->nomor_telepon,
        $profil?->nama_rumah_sakit,
        $profil?->nama_penanggung_jawab,
        $profil?->alamat,
        $profil?->kota,
        $profil?->provinsi,
    ];

    $jumlahFieldTerisi = collect(
        $fieldProfil
    )
        ->filter(
            fn (mixed $value): bool =>
                filled($value)
        )
        ->count();

    $persentaseProfil = (int) round(
        (
            $jumlahFieldTerisi
            / count($fieldProfil)
        ) * 100
    );

    $dokumenIzinUrl =
        filled($profil?->path_dokumen_izin)
            ? \Illuminate\Support\Facades\Storage
                ::disk('public')
                ->url(
                    $profil->path_dokumen_izin
                )
            : null;

    $latitude =
        old(
            'latitude',
            $profil?->latitude
        );

    $longitude =
        old(
            'longitude',
            $profil?->longitude
        );

    $mapsQuery = filled($latitude)
        && filled($longitude)
            ? $latitude . ',' . $longitude
            : collect([
                $profil?->nama_rumah_sakit,
                $profil?->alamat,
                $profil?->kota,
                $profil?->provinsi,
            ])
                ->filter()
                ->implode(', ');

    $mapsEmbedUrl =
        filled($mapsQuery)
            ? 'https://maps.google.com/maps?q='
                . rawurlencode($mapsQuery)
                . '&z=15&output=embed'
            : null;
@endphp

<x-layouts.pemohon-donor
    title="Profil Pemohon"
    heading="Profil Pemohon Donor"
    description="Kelola identitas rumah sakit, penanggung jawab, izin, dan informasi lokasi."
    :pengguna="$pengguna"
    :profil="$profil"
>
    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-7 text-white shadow-[0_22px_55px_rgba(118,0,28,0.2)] sm:px-8"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"
            ></div>

            <div
                class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-24 w-24 shrink-0 items-center justify-center rounded-[28px] border border-white/15 bg-white/10 text-3xl font-bold"
                    >
                        {{ $inisial }}
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-white/60"
                        >
                            Profil Rumah Sakit
                        </p>

                        <h2
                            class="mt-2 text-3xl font-bold tracking-[-0.05em]"
                        >
                            {{ $namaPemohon }}
                        </h2>

                        <div
                            class="mt-3 flex flex-wrap items-center gap-2"
                        >
                            <span
                                class="inline-flex min-h-8 items-center rounded-full bg-white/15 px-3 text-xs font-bold"
                            >
                                {{ $profil?->kode_rumah_sakit ?? 'Kode belum tersedia' }}
                            </span>

                            <span
                                class="inline-flex min-h-8 items-center rounded-full bg-white/15 px-3 text-xs font-bold"
                            >
                                {{ $statusVerifikasiLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <article
                    class="w-full max-w-sm rounded-[22px] border border-white/15 bg-white/10 p-5"
                >
                    <div
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.1em] text-white/60"
                            >
                                Kelengkapan Profil
                            </p>

                            <strong
                                class="mt-2 block text-3xl"
                            >
                                {{ $persentaseProfil }}%
                            </strong>
                        </div>

                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15"
                        >
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21a8 8 0 0 1 16 0" />
                            </svg>
                        </span>
                    </div>

                    <div
                        class="mt-4 h-2 overflow-hidden rounded-full bg-white/15"
                    >
                        <div
                            class="h-full rounded-full bg-white"
                            style="width: {{ $persentaseProfil }}%;"
                        ></div>
                    </div>
                </article>
            </div>
        </section>

        @if (
            in_array(
                $statusVerifikasiValue,
                [
                    'rejected',
                    'ditolak',
                ],
                true
            )
        )
            <section
                class="rounded-[22px] border border-[#f0cbd2] bg-[#fff4f6] p-5"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                >
                    Verifikasi profil ditolak
                </p>

                <h2
                    class="mt-1 text-lg font-bold text-[#3f0716]"
                >
                    Perbaiki data profil dan kirim ulang
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-[#755b61]"
                >
                    {{ $profil?->alasan_penolakan ?? 'Silakan periksa kembali informasi rumah sakit dan dokumen izin.' }}
                </p>
            </section>
        @elseif (
            in_array(
                $statusVerifikasiValue,
                [
                    'pending',
                    'menunggu',
                ],
                true
            )
        )
            <section
                class="rounded-[22px] border border-[#eee0bd] bg-[#fffaf0] p-5"
            >
                <p
                    class="text-xs font-bold uppercase tracking-[0.1em] text-[#8a5a00]"
                >
                    Menunggu verifikasi
                </p>

                <p
                    class="mt-2 text-sm leading-6 text-[#755516]"
                >
                    Profil sedang menunggu pemeriksaan petugas. Anda tetap dapat
                    memperbarui data apabila terdapat informasi yang kurang tepat.
                </p>
            </section>
        @endif

        <form
            method="POST"
            action="{{ route('pemohon-donor.profil.update') }}"
            enctype="multipart/form-data"
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
        >
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    <div
                        class="border-b border-[#eee8e5] pb-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Informasi Akun
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Data Pengguna
                        </h2>
                    </div>

                    <div
                        class="mt-6 grid gap-5 md:grid-cols-2"
                    >
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nama Pengguna
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $pengguna->name) }}"
                                maxlength="255"
                                required
                                autocomplete="name"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('name'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('name'),
                                ])
                            >

                            @error('name')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Alamat Email
                            </label>

                            <input
                                id="email"
                                type="email"
                                value="{{ $pengguna->email }}"
                                disabled
                                class="min-h-12 w-full cursor-not-allowed rounded-xl border border-[#e4dfdc] bg-[#f1eeee] px-4 text-sm text-[#8c7071]"
                            >

                            <p
                                class="mt-2 text-xs text-[#8c7071]"
                            >
                                Email tidak dapat diubah melalui formulir profil.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                for="nomor_telepon"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nomor Telepon
                            </label>

                            <input
                                id="nomor_telepon"
                                type="tel"
                                name="nomor_telepon"
                                value="{{ old('nomor_telepon', $pengguna->nomor_telepon) }}"
                                maxlength="30"
                                autocomplete="tel"
                                placeholder="Contoh: 081234567890"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nomor_telepon'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nomor_telepon'),
                                ])
                            >

                            @error('nomor_telepon')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    <div
                        class="border-b border-[#eee8e5] pb-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Identitas Rumah Sakit
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Informasi Instansi
                        </h2>
                    </div>

                    <div
                        class="mt-6 grid gap-5 md:grid-cols-2"
                    >
                        <div class="md:col-span-2">
                            <label
                                for="nama_rumah_sakit"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nama Rumah Sakit/Instansi
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="nama_rumah_sakit"
                                type="text"
                                name="nama_rumah_sakit"
                                value="{{ old('nama_rumah_sakit', $profil?->nama_rumah_sakit) }}"
                                maxlength="255"
                                required
                                placeholder="Nama lengkap rumah sakit atau instansi"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nama_rumah_sakit'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nama_rumah_sakit'),
                                ])
                            >

                            @error('nama_rumah_sakit')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="nomor_izin"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nomor Izin
                            </label>

                            <input
                                id="nomor_izin"
                                type="text"
                                name="nomor_izin"
                                value="{{ old('nomor_izin', $profil?->nomor_izin) }}"
                                maxlength="255"
                                placeholder="Nomor izin operasional"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nomor_izin'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nomor_izin'),
                                ])
                            >

                            @error('nomor_izin')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="dokumen_izin"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Dokumen Izin
                            </label>

                            <input
                                id="dokumen_izin"
                                type="file"
                                name="dokumen_izin"
                                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#ffe7ec] file:px-3 file:py-2 file:text-xs file:font-bold file:text-[#991b2f]"
                            >

                            <p
                                class="mt-2 text-xs text-[#8c7071]"
                            >
                                PDF, JPG, JPEG, atau PNG. Maksimal 4 MB.
                            </p>

                            @error('dokumen_izin')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="nama_penanggung_jawab"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nama Penanggung Jawab
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="nama_penanggung_jawab"
                                type="text"
                                name="nama_penanggung_jawab"
                                value="{{ old('nama_penanggung_jawab', $profil?->nama_penanggung_jawab) }}"
                                maxlength="255"
                                required
                                placeholder="Nama lengkap penanggung jawab"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nama_penanggung_jawab'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nama_penanggung_jawab'),
                                ])
                            >

                            @error('nama_penanggung_jawab')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="jabatan_penanggung_jawab"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Jabatan Penanggung Jawab
                            </label>

                            <input
                                id="jabatan_penanggung_jawab"
                                type="text"
                                name="jabatan_penanggung_jawab"
                                value="{{ old('jabatan_penanggung_jawab', $profil?->jabatan_penanggung_jawab) }}"
                                maxlength="255"
                                placeholder="Contoh: Kepala Unit Transfusi"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('jabatan_penanggung_jawab'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('jabatan_penanggung_jawab'),
                                ])
                            >

                            @error('jabatan_penanggung_jawab')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    <div
                        class="border-b border-[#eee8e5] pb-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Alamat dan Lokasi
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Lokasi Rumah Sakit
                        </h2>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label
                                for="alamat"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Alamat Lengkap
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <textarea
                                id="alamat"
                                name="alamat"
                                rows="4"
                                maxlength="500"
                                required
                                placeholder="Jalan, nomor bangunan, dan informasi alamat lainnya"
                                @class([
                                    'w-full rounded-xl border bg-[#fbf9f8] px-4 py-3 text-sm leading-6 outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('alamat'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('alamat'),
                                ])
                            >{{ old('alamat', $profil?->alamat) }}</textarea>

                            @error('alamat')
                                <p class="mt-2 text-xs font-semibold text-[#b42318]">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div
                            class="grid gap-5 sm:grid-cols-2"
                        >
                            <div>
                                <label
                                    for="provinsi"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Provinsi
                                </label>

                                <input
                                    id="provinsi"
                                    type="text"
                                    name="provinsi"
                                    value="{{ old('provinsi', $profil?->provinsi) }}"
                                    maxlength="100"
                                    placeholder="Nama provinsi"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>

                            <div>
                                <label
                                    for="kota"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Kota/Kabupaten
                                </label>

                                <input
                                    id="kota"
                                    type="text"
                                    name="kota"
                                    value="{{ old('kota', $profil?->kota) }}"
                                    maxlength="100"
                                    placeholder="Nama kota atau kabupaten"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>

                            <div>
                                <label
                                    for="kecamatan"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Kecamatan
                                </label>

                                <input
                                    id="kecamatan"
                                    type="text"
                                    name="kecamatan"
                                    value="{{ old('kecamatan', $profil?->kecamatan) }}"
                                    maxlength="100"
                                    placeholder="Nama kecamatan"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>

                            <div>
                                <label
                                    for="kode_pos"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Kode Pos
                                </label>

                                <input
                                    id="kode_pos"
                                    type="text"
                                    name="kode_pos"
                                    value="{{ old('kode_pos', $profil?->kode_pos) }}"
                                    maxlength="20"
                                    placeholder="Kode pos"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>

                            <div>
                                <label
                                    for="latitude"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Latitude
                                </label>

                                <input
                                    id="latitude"
                                    type="number"
                                    name="latitude"
                                    value="{{ $latitude }}"
                                    min="-90"
                                    max="90"
                                    step="0.0000001"
                                    placeholder="-7.1234567"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>

                            <div>
                                <label
                                    for="longitude"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Longitude
                                </label>

                                <input
                                    id="longitude"
                                    type="number"
                                    name="longitude"
                                    value="{{ $longitude }}"
                                    min="-180"
                                    max="180"
                                    step="0.0000001"
                                    placeholder="109.1234567"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside
                class="space-y-5 xl:sticky xl:top-28 xl:self-start"
            >
                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Status Profil
                    </p>

                    <span
                        class="mt-3 inline-flex min-h-8 items-center rounded-full px-3 text-xs font-bold {{ $statusVerifikasiClass }}"
                    >
                        {{ $statusVerifikasiLabel }}
                    </span>

                    <dl class="mt-5 space-y-4">
                        <div
                            class="flex justify-between gap-4 border-b border-[#eee8e5] pb-3"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Kode Pemohon
                            </dt>

                            <dd class="text-right text-sm font-bold text-[#191c20]">
                                {{ $profil?->kode_rumah_sakit ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between gap-4 border-b border-[#eee8e5] pb-3"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Nomor Izin
                            </dt>

                            <dd class="max-w-40 truncate text-right text-sm font-bold text-[#191c20]">
                                {{ $profil?->nomor_izin ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-sm text-[#8c7071]">
                                Dokumen
                            </dt>

                            <dd>
                                @if ($dokumenIzinUrl !== null)
                                    <a
                                        href="{{ $dokumenIzinUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm font-bold text-[#991b2f]"
                                    >
                                        Lihat Dokumen
                                    </a>
                                @else
                                    <span class="text-sm font-bold text-[#8c7071]">
                                        Belum ada
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                @if ($mapsEmbedUrl !== null)
                    <section
                        class="overflow-hidden rounded-[24px] border border-[#e8e2df] bg-white shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                    >
                        <iframe
                            src="{{ $mapsEmbedUrl }}"
                            class="h-64 w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi {{ $namaPemohon }}"
                        ></iframe>

                        <div class="p-4">
                            <p
                                class="text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                            >
                                Lokasi Tersimpan
                            </p>

                            <p
                                class="mt-2 text-sm leading-6 text-[#584141]"
                            >
                                {{ $profil?->alamat ?? 'Alamat belum tersedia.' }}
                            </p>
                        </div>
                    </section>
                @endif

                <div class="space-y-3">
                    <button
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white shadow-[0_14px_30px_rgba(153,27,47,0.2)] transition hover:bg-[#76001c]"
                    >
                        Simpan Perubahan
                    </button>

                    <a
                        href="{{ route('pemohon-donor.beranda') }}"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-5 text-sm font-bold text-[#76001c]"
                    >
                        Kembali ke Dashboard
                    </a>
                </div>

                <p
                    class="text-center text-xs leading-5 text-[#8c7071]"
                >
                    Perubahan profil yang ditolak akan diajukan kembali untuk diverifikasi.
                </p>
            </aside>
        </form>
    </div>
</x-layouts.pemohon-donor>