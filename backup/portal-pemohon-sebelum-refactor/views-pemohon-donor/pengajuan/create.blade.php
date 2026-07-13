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

    $simbolRhesus = function (
        mixed $rhesus
    ) use (
        $nilaiEnum
    ): string {
        if (
            is_object($rhesus)
            && method_exists(
                $rhesus,
                'simbol'
            )
        ) {
            return (string) $rhesus->simbol();
        }

        return match (
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
    };

    $statusProfil = $profil?->status_verifikasi;

    $statusProfilLabel =
        $statusProfil !== null
            ? $labelEnum($statusProfil)
            : 'Profil belum tersedia';

    $statusProfilValue =
        $nilaiEnum($statusProfil);

    $statusProfilClass = match (
        $statusProfilValue
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

    $tanggalDefault = now()
        ->addDay()
        ->format('Y-m-d\TH:i');

    $tanggalMinimum = now()
        ->format('Y-m-d\TH:i');

    $namaPemohon =
        $profil?->nama_rumah_sakit
        ?? $pengguna->name;

    $profilLengkap =
        $profil !== null;
@endphp

<x-layouts.pemohon-donor
    title="Buat Pengajuan"
    heading="Buat Pengajuan Kebutuhan Darah"
    description="Lengkapi informasi kebutuhan darah agar dapat ditinjau oleh petugas."
    :pengguna="$pengguna"
    :profil="$profil"
>
    <div class="space-y-6">
        <nav
            class="flex flex-wrap items-center gap-2 text-sm text-[#8c7071]"
            aria-label="Breadcrumb"
        >
            <a
                href="{{ route('pemohon-donor.beranda') }}"
                class="font-semibold hover:text-[#991b2f]"
            >
                Dashboard
            </a>

            <span>/</span>

            <a
                href="{{ route('pemohon-donor.pengajuan.index') }}"
                class="font-semibold hover:text-[#991b2f]"
            >
                Pengajuan
            </a>

            <span>/</span>

            <span class="font-bold text-[#191c20]">
                Buat Pengajuan
            </span>
        </nav>

        <section
            class="relative overflow-hidden rounded-[28px] bg-[#76001c] px-6 py-7 text-white shadow-[0_22px_55px_rgba(118,0,28,0.2)] sm:px-8"
        >
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"
            ></div>

            <div
                class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-center"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.14em] text-white/65"
                    >
                        Formulir Kebutuhan Darah
                    </p>

                    <h2
                        class="mt-2 text-3xl font-bold tracking-[-0.05em] sm:text-4xl"
                    >
                        Ajukan kebutuhan darah pasien
                    </h2>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-7 text-white/70"
                    >
                        Pastikan informasi pasien, dokter, golongan darah, jumlah
                        kantong, dan waktu kebutuhan sudah benar sebelum dikirim.
                    </p>
                </div>

                <article
                    class="rounded-2xl border border-white/15 bg-white/10 p-5"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-white/60"
                    >
                        Pemohon
                    </p>

                    <strong
                        class="mt-2 block text-lg"
                    >
                        {{ $namaPemohon }}
                    </strong>

                    <span
                        class="mt-3 inline-flex min-h-8 items-center rounded-full bg-white/15 px-3 text-xs font-bold"
                    >
                        {{ $statusProfilLabel }}
                    </span>
                </article>
            </div>
        </section>

        @if (! $profilLengkap)
            <section
                class="rounded-[22px] border border-[#f0cbd2] bg-[#fff4f6] p-5"
            >
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Profil belum lengkap
                        </p>

                        <h2
                            class="mt-1 text-lg font-bold text-[#3f0716]"
                        >
                            Lengkapi profil rumah sakit sebelum mengajukan
                        </h2>

                        <p
                            class="mt-1 text-sm leading-6 text-[#755b61]"
                        >
                            Sistem membutuhkan identitas rumah sakit dan penanggung jawab.
                        </p>
                    </div>

                    <a
                        href="{{ route('pemohon-donor.profil.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                    >
                        Lengkapi Profil
                    </a>
                </div>
            </section>
        @endif

        @if (
            in_array(
                $statusProfilValue,
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
                    Perbaiki informasi profil rumah sakit
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-[#755b61]"
                >
                    {{ $profil?->alasan_penolakan ?? 'Silakan periksa dan perbarui kembali informasi profil.' }}
                </p>

                <a
                    href="{{ route('pemohon-donor.profil.index') }}"
                    class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#991b2f] px-5 text-sm font-bold text-white"
                >
                    Perbaiki Profil
                </a>
            </section>
        @endif

        <form
            method="POST"
            action="{{ route('pemohon-donor.pengajuan.store') }}"
            enctype="multipart/form-data"
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
        >
            @csrf

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
                            Bagian 1
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Informasi Pasien dan Dokter
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-[#755f60]"
                        >
                            Gunakan nomor rekam medis atau referensi internal rumah sakit.
                        </p>
                    </div>

                    <div
                        class="mt-6 grid gap-5 md:grid-cols-2"
                    >
                        <div>
                            <label
                                for="referensi_pasien"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Referensi Pasien
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="referensi_pasien"
                                type="text"
                                name="referensi_pasien"
                                value="{{ old('referensi_pasien') }}"
                                maxlength="150"
                                required
                                autocomplete="off"
                                placeholder="Contoh: RM-2026-001245"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('referensi_pasien'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('referensi_pasien'),
                                ])
                            >

                            @error('referensi_pasien')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="nama_dokter"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nama Dokter
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="nama_dokter"
                                type="text"
                                name="nama_dokter"
                                value="{{ old('nama_dokter') }}"
                                maxlength="255"
                                required
                                autocomplete="name"
                                placeholder="Nama dokter penanggung jawab"
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nama_dokter'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nama_dokter'),
                                ])
                            >

                            @error('nama_dokter')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
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
                            Bagian 2
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Kebutuhan Darah
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-[#755f60]"
                        >
                            Pilih golongan darah dan jumlah kantong sesuai kebutuhan medis.
                        </p>
                    </div>

                    <div
                        class="mt-6 grid gap-5 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                for="golongan_darah"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Golongan Darah
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <select
                                id="golongan_darah"
                                name="golongan_darah"
                                required
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('golongan_darah'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('golongan_darah'),
                                ])
                            >
                                <option value="">
                                    Pilih golongan darah
                                </option>

                                @foreach ($golonganOptions as $golongan)
                                    <option
                                        value="{{ $nilaiEnum($golongan) }}"
                                        @selected(
                                            old('golongan_darah') ===
                                            $nilaiEnum($golongan)
                                        )
                                    >
                                        {{ $labelEnum($golongan) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('golongan_darah')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="rhesus"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Rhesus
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <select
                                id="rhesus"
                                name="rhesus"
                                required
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('rhesus'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('rhesus'),
                                ])
                            >
                                <option value="">
                                    Pilih rhesus
                                </option>

                                @foreach ($rhesusOptions as $rhesus)
                                    <option
                                        value="{{ $nilaiEnum($rhesus) }}"
                                        @selected(
                                            old('rhesus') ===
                                            $nilaiEnum($rhesus)
                                        )
                                    >
                                        {{ $labelEnum($rhesus) }}
                                        ({{ $simbolRhesus($rhesus) }})
                                    </option>
                                @endforeach
                            </select>

                            @error('rhesus')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="jumlah_kantong"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Jumlah Kantong
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <div class="relative">
                                <input
                                    id="jumlah_kantong"
                                    type="number"
                                    name="jumlah_kantong"
                                    value="{{ old('jumlah_kantong', 1) }}"
                                    min="1"
                                    max="100"
                                    required
                                    @class([
                                        'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 pr-24 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                        'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('jumlah_kantong'),
                                        'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('jumlah_kantong'),
                                    ])
                                >

                                <span
                                    class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-[#8c7071]"
                                >
                                    kantong
                                </span>
                            </div>

                            @error('jumlah_kantong')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="tingkat_urgensi"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Tingkat Urgensi
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <select
                                id="tingkat_urgensi"
                                name="tingkat_urgensi"
                                required
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('tingkat_urgensi'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('tingkat_urgensi'),
                                ])
                            >
                                <option value="">
                                    Pilih tingkat urgensi
                                </option>

                                @foreach ($urgensiOptions as $urgensi)
                                    <option
                                        value="{{ $nilaiEnum($urgensi) }}"
                                        @selected(
                                            old('tingkat_urgensi') ===
                                            $nilaiEnum($urgensi)
                                        )
                                    >
                                        {{ $labelEnum($urgensi) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('tingkat_urgensi')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                for="dibutuhkan_pada"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Dibutuhkan Pada
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <input
                                id="dibutuhkan_pada"
                                type="datetime-local"
                                name="dibutuhkan_pada"
                                value="{{ old('dibutuhkan_pada', $tanggalDefault) }}"
                                min="{{ $tanggalMinimum }}"
                                required
                                @class([
                                    'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('dibutuhkan_pada'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('dibutuhkan_pada'),
                                ])
                            >

                            @error('dibutuhkan_pada')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
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
                            Bagian 3
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Dokumen dan Catatan
                        </h2>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label
                                for="dokumen_permintaan"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Dokumen Permintaan
                            </label>

                            <label
                                for="dokumen_permintaan"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-[#dcbfc5] bg-[#fff9fa] px-5 py-10 text-center transition hover:border-[#991b2f] hover:bg-[#fff4f6]"
                            >
                                <svg
                                    class="h-9 w-9 text-[#991b2f]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M12 3v12" />
                                    <path d="m7 8 5-5 5 5" />
                                    <path d="M5 21h14" />
                                </svg>

                                <strong
                                    class="mt-3 text-sm text-[#191c20]"
                                >
                                    Pilih dokumen permintaan
                                </strong>

                                <span
                                    class="mt-1 text-xs text-[#8c7071]"
                                >
                                    PDF, JPG, JPEG, atau PNG. Maksimal 4 MB.
                                </span>
                            </label>

                            <input
                                id="dokumen_permintaan"
                                type="file"
                                name="dokumen_permintaan"
                                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                class="sr-only"
                            >

                            @error('dokumen_permintaan')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="catatan"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Catatan Tambahan
                            </label>

                            <textarea
                                id="catatan"
                                name="catatan"
                                rows="5"
                                maxlength="5000"
                                placeholder="Tambahkan informasi medis atau kebutuhan khusus bila diperlukan..."
                                @class([
                                    'w-full rounded-xl border bg-[#fbf9f8] px-4 py-3 text-sm leading-6 outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                    'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('catatan'),
                                    'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('catatan'),
                                ])
                            >{{ old('catatan') }}</textarea>

                            @error('catatan')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-5 xl:sticky xl:top-28 xl:self-start">
                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                    >
                        Informasi Pemohon
                    </p>

                    <h2
                        class="mt-2 text-lg font-bold text-[#191c20]"
                    >
                        {{ $namaPemohon }}
                    </h2>

                    <dl class="mt-5 space-y-4">
                        <div
                            class="flex justify-between gap-4 border-b border-[#eee8e5] pb-3"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Kode
                            </dt>

                            <dd
                                class="text-right text-sm font-bold text-[#191c20]"
                            >
                                {{ $profil?->kode_rumah_sakit ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between gap-4 border-b border-[#eee8e5] pb-3"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Penanggung Jawab
                            </dt>

                            <dd
                                class="text-right text-sm font-bold text-[#191c20]"
                            >
                                {{ $profil?->nama_penanggung_jawab ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex justify-between gap-4"
                        >
                            <dt
                                class="text-sm text-[#8c7071]"
                            >
                                Status Profil
                            </dt>

                            <dd>
                                <span
                                    class="inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold {{ $statusProfilClass }}"
                                >
                                    {{ $statusProfilLabel }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section
                    class="rounded-[24px] border border-[#eee0bd] bg-[#fffaf0] p-6"
                >
                    <h2
                        class="text-lg font-bold text-[#5f3d00]"
                    >
                        Sebelum mengirim
                    </h2>

                    <ul
                        class="mt-4 space-y-3 text-sm leading-6 text-[#755516]"
                    >
                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#d79500]"
                            ></span>

                            Pastikan golongan darah dan rhesus sesuai kebutuhan pasien.
                        </li>

                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#d79500]"
                            ></span>

                            Gunakan tingkat urgensi berdasarkan kondisi medis.
                        </li>

                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#d79500]"
                            ></span>

                            Lampirkan dokumen pendukung bila tersedia.
                        </li>
                    </ul>
                </section>

                <div class="space-y-3">
                    <button
                        type="submit"
                        @disabled(! $profilLengkap)
                        @class([
                            'inline-flex min-h-13 w-full items-center justify-center rounded-xl px-5 text-sm font-bold transition',
                            'cursor-not-allowed bg-[#d9cdcf] text-[#8c7071]' => ! $profilLengkap,
                            'bg-[#991b2f] text-white shadow-[0_14px_30px_rgba(153,27,47,0.2)] hover:bg-[#76001c]' => $profilLengkap,
                        ])
                    >
                        Kirim Pengajuan
                    </button>

                    <a
                        href="{{ route('pemohon-donor.pengajuan.index') }}"
                        class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-5 text-sm font-bold text-[#76001c]"
                    >
                        Batal
                    </a>
                </div>

                <p
                    class="text-center text-xs leading-5 text-[#8c7071]"
                >
                    Dengan mengirim, Anda memastikan seluruh data yang dimasukkan benar.
                </p>
            </aside>
        </form>
    </div>
</x-layouts.pemohon-donor>