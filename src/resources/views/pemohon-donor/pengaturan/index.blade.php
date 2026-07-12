@php
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

    $statusAkunValue =
        $nilaiEnum($pengguna->status);

    $statusAkunLabel =
        $labelEnum($pengguna->status);

    $statusAkunClass = match (
        $statusAkunValue
    ) {
        'active',
        'aktif' =>
            'bg-[#dff7e7] text-[#176b3a]',

        'pending',
        'menunggu' =>
            'bg-[#fff1c9] text-[#8a5a00]',

        'suspended',
        'ditangguhkan' =>
            'bg-[#ffe4e7] text-[#991b2f]',

        default =>
            'bg-[#f1ecea] text-[#655253]',
    };

    $emailTerverifikasi =
        $pengguna->email_verified_at !==
        null;

    $terakhirLogin =
        $pengguna->terakhir_login_pada
        ?? null;
@endphp

<x-layouts.pemohon-donor
    title="Pengaturan Akun"
    heading="Pengaturan Akun"
    description="Kelola informasi login, keamanan password, dan sesi akun."
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
                    <span
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[24px] border border-white/15 bg-white/10 text-2xl font-bold"
                    >
                        {{ $inisial }}
                    </span>

                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-white/60"
                        >
                            Keamanan Akun
                        </p>

                        <h2
                            class="mt-2 text-3xl font-bold tracking-[-0.05em]"
                        >
                            {{ $namaPemohon }}
                        </h2>

                        <p
                            class="mt-2 text-sm text-white/65"
                        >
                            {{ $pengguna->email }}
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-wrap gap-2"
                >
                    <span
                        class="inline-flex min-h-9 items-center rounded-full bg-white/15 px-4 text-xs font-bold"
                    >
                        {{ $statusAkunLabel }}
                    </span>

                    <span
                        class="inline-flex min-h-9 items-center rounded-full bg-white/15 px-4 text-xs font-bold"
                    >
                        {{ $emailTerverifikasi ? 'Email Terverifikasi' : 'Email Belum Terverifikasi' }}
                    </span>
                </div>
            </div>
        </section>

        <section
            class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
        >
            <div class="space-y-6">
                <form
                    method="POST"
                    action="{{ route('pemohon-donor.pengaturan.akun.update') }}"
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    @csrf
                    @method('PUT')

                    <div
                        class="border-b border-[#eee8e5] pb-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Informasi Login
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Email dan Nomor Telepon
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-[#755f60]"
                        >
                            Informasi ini digunakan untuk proses login, notifikasi,
                            dan komunikasi terkait pengajuan darah.
                        </p>
                    </div>

                    <div
                        class="mt-6 grid gap-5 md:grid-cols-2"
                    >
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Alamat Email
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#8c7071]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                    />

                                    <path d="m3 7 9 6 9-6" />
                                </svg>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $pengguna->email) }}"
                                    maxlength="255"
                                    required
                                    autocomplete="email"
                                    @class([
                                        'min-h-12 w-full rounded-xl border bg-[#fbf9f8] pl-12 pr-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                        'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('email'),
                                        'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('email'),
                                    ])
                                >
                            </div>

                            @error('email')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror

                            <div
                                class="mt-2 flex items-center gap-2"
                            >
                                <span
                                    @class([
                                        'h-2 w-2 rounded-full',
                                        'bg-[#229653]' => $emailTerverifikasi,
                                        'bg-[#d79500]' => ! $emailTerverifikasi,
                                    ])
                                ></span>

                                <span
                                    class="text-xs text-[#8c7071]"
                                >
                                    {{ $emailTerverifikasi
                                        ? 'Email sudah diverifikasi.'
                                        : 'Email belum diverifikasi.' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label
                                for="nomor_telepon"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Nomor Telepon
                            </label>

                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#8c7071]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.4 1.8.6 2.8.7a2 2 0 0 1 1.7 2.1Z"
                                    />
                                </svg>

                                <input
                                    id="nomor_telepon"
                                    type="tel"
                                    name="nomor_telepon"
                                    value="{{ old('nomor_telepon', $pengguna->nomor_telepon) }}"
                                    maxlength="30"
                                    autocomplete="tel"
                                    placeholder="Contoh: 081234567890"
                                    @class([
                                        'min-h-12 w-full rounded-xl border bg-[#fbf9f8] pl-12 pr-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                        'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('nomor_telepon'),
                                        'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('nomor_telepon'),
                                    ])
                                >
                            </div>

                            @error('nomor_telepon')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror

                            <p
                                class="mt-2 text-xs text-[#8c7071]"
                            >
                                Maksimal 30 karakter.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-wrap items-center gap-3 border-t border-[#eee8e5] pt-5"
                    >
                        <button
                            type="submit"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#991b2f] px-6 text-sm font-bold text-white shadow-[0_12px_28px_rgba(153,27,47,0.18)] transition hover:bg-[#76001c]"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"
                                />

                                <path d="M17 21v-8H7v8M7 3v5h8" />
                            </svg>

                            Simpan Informasi Akun
                        </button>

                        <a
                            href="{{ route('pemohon-donor.profil.index') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e5dadd] bg-white px-5 text-sm font-bold text-[#76001c]"
                        >
                            Buka Profil
                        </a>
                    </div>
                </form>

                <form
                    method="POST"
                    action="{{ route('pemohon-donor.pengaturan.password.update') }}"
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-5 shadow-[0_16px_45px_rgba(25,28,32,0.05)] sm:p-6"
                >
                    @csrf
                    @method('PUT')

                    <div
                        class="border-b border-[#eee8e5] pb-5"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.1em] text-[#991b2f]"
                        >
                            Keamanan Password
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-[-0.04em] text-[#191c20]"
                        >
                            Ubah Password Akun
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-[#755f60]"
                        >
                            Gunakan password minimal delapan karakter dan hindari
                            menggunakan password yang sama dengan layanan lain.
                        </p>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label
                                for="password_lama"
                                class="mb-2 block text-sm font-semibold text-[#191c20]"
                            >
                                Password Lama
                                <span class="text-[#b42318]">*</span>
                            </label>

                            <div class="relative">
                                <svg
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#8c7071]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <rect
                                        x="4"
                                        y="10"
                                        width="16"
                                        height="11"
                                        rx="2"
                                    />

                                    <path
                                        d="M8 10V7a4 4 0 0 1 8 0v3"
                                    />
                                </svg>

                                <input
                                    id="password_lama"
                                    type="password"
                                    name="password_lama"
                                    required
                                    autocomplete="current-password"
                                    @class([
                                        'min-h-12 w-full rounded-xl border bg-[#fbf9f8] pl-12 pr-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                        'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('password_lama'),
                                        'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('password_lama'),
                                    ])
                                >
                            </div>

                            @error('password_lama')
                                <p
                                    class="mt-2 text-xs font-semibold text-[#b42318]"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div
                            class="grid gap-5 md:grid-cols-2"
                        >
                            <div>
                                <label
                                    for="password_baru"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Password Baru
                                    <span class="text-[#b42318]">*</span>
                                </label>

                                <input
                                    id="password_baru"
                                    type="password"
                                    name="password_baru"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                    @class([
                                        'min-h-12 w-full rounded-xl border bg-[#fbf9f8] px-4 text-sm outline-none transition focus:ring-4 focus:ring-[#991b2f]/10',
                                        'border-[#e5b9c2] focus:border-[#991b2f]' => $errors->has('password_baru'),
                                        'border-[#ded8d5] focus:border-[#991b2f]' => ! $errors->has('password_baru'),
                                    ])
                                >

                                @error('password_baru')
                                    <p
                                        class="mt-2 text-xs font-semibold text-[#b42318]"
                                    >
                                        {{ $message }}
                                    </p>
                                @enderror

                                <p
                                    class="mt-2 text-xs text-[#8c7071]"
                                >
                                    Minimal delapan karakter.
                                </p>
                            </div>

                            <div>
                                <label
                                    for="password_baru_confirmation"
                                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                                >
                                    Konfirmasi Password
                                    <span class="text-[#b42318]">*</span>
                                </label>

                                <input
                                    id="password_baru_confirmation"
                                    type="password"
                                    name="password_baru_confirmation"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                    class="min-h-12 w-full rounded-xl border border-[#ded8d5] bg-[#fbf9f8] px-4 text-sm outline-none transition focus:border-[#991b2f] focus:ring-4 focus:ring-[#991b2f]/10"
                                >

                                <p
                                    class="mt-2 text-xs text-[#8c7071]"
                                >
                                    Ketik ulang password baru.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-wrap items-center gap-3 border-t border-[#eee8e5] pt-5"
                    >
                        <button
                            type="submit"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#991b2f] px-6 text-sm font-bold text-white shadow-[0_12px_28px_rgba(153,27,47,0.18)] transition hover:bg-[#76001c]"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <rect
                                    x="4"
                                    y="10"
                                    width="16"
                                    height="11"
                                    rx="2"
                                />

                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>

                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>

            <aside
                class="space-y-5 xl:sticky xl:top-28 xl:self-start"
            >
                <section
                    class="rounded-[24px] border border-[#e8e2df] bg-white p-6 shadow-[0_16px_45px_rgba(25,28,32,0.05)]"
                >
                    <div
                        class="flex items-center gap-4 border-b border-[#eee8e5] pb-5"
                    >
                        <span
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#ffe7ec] text-lg font-bold text-[#991b2f]"
                        >
                            {{ $inisial }}
                        </span>

                        <div class="min-w-0">
                            <h2
                                class="truncate text-base font-bold text-[#191c20]"
                            >
                                {{ $namaPemohon }}
                            </h2>

                            <p
                                class="mt-1 truncate text-xs text-[#8c7071]"
                            >
                                {{ $pengguna->email }}
                            </p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-4">
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Role
                            </dt>

                            <dd class="text-right text-sm font-bold text-[#191c20]">
                                Pemohon Donor
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Status Akun
                            </dt>

                            <dd>
                                <span
                                    class="inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold {{ $statusAkunClass }}"
                                >
                                    {{ $statusAkunLabel }}
                                </span>
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Email
                            </dt>

                            <dd>
                                <span
                                    @class([
                                        'inline-flex min-h-7 items-center rounded-full px-2.5 text-[11px] font-bold',
                                        'bg-[#dff7e7] text-[#176b3a]' => $emailTerverifikasi,
                                        'bg-[#fff1c9] text-[#8a5a00]' => ! $emailTerverifikasi,
                                    ])
                                >
                                    {{ $emailTerverifikasi
                                        ? 'Terverifikasi'
                                        : 'Belum Terverifikasi' }}
                                </span>
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Kode Pemohon
                            </dt>

                            <dd class="text-right text-sm font-bold text-[#191c20]">
                                {{ $profil?->kode_rumah_sakit ?? '-' }}
                            </dd>
                        </div>

                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <dt class="text-sm text-[#8c7071]">
                                Nomor Telepon
                            </dt>

                            <dd class="text-right text-sm font-bold text-[#191c20]">
                                {{ $pengguna->nomor_telepon ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section
                    class="rounded-[24px] border border-[#dce5f4] bg-[#f6f9ff] p-6"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#e7effc] text-[#315b9b]"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                            />

                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </div>

                    <h2
                        class="mt-4 text-lg font-bold text-[#263f67]"
                    >
                        Informasi Keamanan
                    </h2>

                    <ul
                        class="mt-4 space-y-3 text-sm leading-6 text-[#506787]"
                    >
                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#537bb5]"
                            ></span>

                            Jangan berikan password kepada siapa pun.
                        </li>

                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#537bb5]"
                            ></span>

                            Gunakan password berbeda untuk setiap layanan.
                        </li>

                        <li class="flex gap-3">
                            <span
                                class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[#537bb5]"
                            ></span>

                            Keluar dari akun setelah memakai perangkat umum.
                        </li>
                    </ul>
                </section>

                @if ($terakhirLogin !== null)
                    <section
                        class="rounded-[24px] border border-[#e8e2df] bg-white p-6"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.08em] text-[#8c7071]"
                        >
                            Login Terakhir
                        </p>

                        <strong
                            class="mt-2 block text-sm text-[#191c20]"
                        >
                            {{ $terakhirLogin->translatedFormat('d F Y, H:i') }}
                            WIB
                        </strong>

                        <p
                            class="mt-1 text-xs text-[#8c7071]"
                        >
                            IP:
                            {{ $pengguna->ip_terakhir_login ?? '-' }}
                        </p>
                    </section>
                @endif

                <form
                    method="POST"
                    action="{{ route('pemohon-donor.logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#f0cbd2] bg-[#fff4f6] px-5 text-sm font-bold text-[#991b2f] transition hover:bg-[#ffe8ec]"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M10 17l5-5-5-5" />
                            <path d="M15 12H3" />
                            <path d="M21 3v18" />
                        </svg>

                        Keluar dari Akun
                    </button>
                </form>
            </aside>
        </section>
    </div>
</x-layouts.pemohon-donor>