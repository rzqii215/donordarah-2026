<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    {{-- Ringkasan identitas --}}
    <section class="relative overflow-hidden rounded-2xl border border-[#e6e3df] bg-white p-5 sm:p-7">
        <div class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[#ffdada]/50 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
            <img
                src="{{ $this->avatarUrl() }}"
                alt="Foto profil {{ $name ?: 'Pendonor' }}"
                class="h-24 w-24 shrink-0 rounded-full border-4 border-white object-cover shadow-[0_8px_30px_rgba(25,28,32,0.12)]"
            >

            <div class="min-w-0 flex-1">
                <h2 class="truncate text-2xl font-semibold tracking-[-0.035em] text-[#191c20]">
                    {{ $name ?: 'Pendonor' }}
                </h2>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-[#584141]">
                        ID:
                        {{ $ringkasan['kode_pendonor'] }}
                    </span>

                    @if ($pengguna?->hasVerifiedEmail())
                        <span class="inline-flex rounded-full bg-[#eaf7f0] px-3 py-1 text-xs font-bold text-[#257a57]">
                            Email Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-[#fff4de] px-3 py-1 text-xs font-bold text-[#b86e12]">
                            Email Belum Terverifikasi
                        </span>
                    @endif
                </div>

                <div class="mt-4 max-w-md">
                    <div class="flex items-center justify-between gap-4 text-xs font-semibold text-[#584141]">
                        <span>Kelengkapan Profil</span>

                        <span>
                            {{ $kelengkapan['persentase'] }}%
                        </span>
                    </div>

                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e2e2e9]">
                        <div
                            @class([
                                'h-full rounded-full transition-all',
                                'bg-[#257a57]' => $kelengkapan['lengkap'],
                                'bg-[#76001c]' => ! $kelengkapan['lengkap'],
                            ])
                            style="width: {{ $kelengkapan['persentase'] }}%;"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:w-auto">
                <article class="rounded-xl border border-[#e0bfbf] bg-[#fcedef] px-5 py-4 text-center">
                    <p class="text-xs font-semibold text-[#991b2f]">
                        Golongan Darah
                    </p>

                    <strong class="mt-1 block text-2xl text-[#76001c]">
                        {{ $ringkasan['golongan_rhesus'] }}
                    </strong>
                </article>

                <article class="rounded-xl border border-[#e2e2e9] bg-[#f3f3fa] px-5 py-4 text-center">
                    <p class="text-xs font-semibold text-[#584141]">
                        Donor Selesai
                    </p>

                    <strong class="mt-1 block text-2xl text-[#191c20]">
                        {{ $ringkasan['donor_selesai'] }}
                    </strong>
                </article>
            </div>
        </div>
    </section>

    {{-- Statistik --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Umur
            </p>

            <strong class="mt-2 block text-xl text-[#191c20]">
                {{ $ringkasan['umur'] }}
            </strong>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Total Pendaftaran
            </p>

            <strong class="mt-2 block text-xl text-[#191c20]">
                {{ $ringkasan['total_pendaftaran'] }}
            </strong>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Rhesus
            </p>

            <strong class="mt-2 block text-xl text-[#191c20]">
                {{ $this->labelRhesusTampilan() }}
            </strong>
        </article>

        <article class="rounded-2xl border border-[#e6e3df] bg-white p-4">
            <p class="text-xs font-bold uppercase tracking-[0.06em] text-[#8c7071]">
                Donor Terakhir
            </p>

            <strong class="mt-2 block text-sm leading-6 text-[#191c20]">
                {{ $ringkasan['terakhir_donor'] }}
            </strong>
        </article>
    </section>

    @if (! $kelengkapan['lengkap'])
        <section class="rounded-2xl border border-[#f2c879] bg-[#fff4de] p-5">
            <h2 class="text-base font-semibold text-[#8a4f00]">
                Profil belum lengkap
            </h2>

            <p class="mt-1 text-sm leading-6 text-[#8a4f00]">
                Lengkapi data berikut agar proses pendaftaran donor berjalan lancar.
            </p>

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($kelengkapan['belum_lengkap'] as $item)
                    <span class="inline-flex rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-[#8a4f00]">
                        {{ $item }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    <form
        wire:submit="simpanProfil"
        class="space-y-5"
    >
        {{-- Informasi pribadi --}}
        <section class="rounded-2xl border border-[#e6e3df] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-4">
                <h2 class="text-xl font-semibold text-[#191c20]">
                    Informasi Pribadi
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#584141]">
                    Data identitas utama pendonor.
                </p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <label class="sm:col-span-2">
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Nama Lengkap
                    </span>

                    <input
                        type="text"
                        wire:model="name"
                        autocomplete="name"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('name'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('name'),
                        ])
                    >

                    @error('name')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Email
                    </span>

                    <input
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('email'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('email'),
                        ])
                    >

                    <span class="mt-1 block text-xs leading-5 text-[#8c7071]">
                        Perubahan email mewajibkan verifikasi ulang.
                    </span>

                    @error('email')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Nomor Telepon/WhatsApp
                    </span>

                    <input
                        type="tel"
                        wire:model="nomor_telepon"
                        autocomplete="tel"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('nomor_telepon'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('nomor_telepon'),
                        ])
                    >

                    @error('nomor_telepon')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Tanggal Lahir
                    </span>

                    <input
                        type="date"
                        wire:model="tanggal_lahir"
                        max="{{ now()->subYears(17)->format('Y-m-d') }}"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('tanggal_lahir'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('tanggal_lahir'),
                        ])
                    >

                    @error('tanggal_lahir')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Jenis Kelamin
                    </span>

                    <select
                        wire:model="jenis_kelamin"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('jenis_kelamin'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('jenis_kelamin'),
                        ])
                    >
                        <option value="">
                            Pilih jenis kelamin
                        </option>

                        @foreach ($this->opsiJenisKelamin() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('jenis_kelamin')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Golongan Darah
                    </span>

                    <select
                        wire:model="golongan_darah"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('golongan_darah'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('golongan_darah'),
                        ])
                    >
                        <option value="">
                            Pilih golongan darah
                        </option>

                        @foreach ($this->opsiGolonganDarah() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('golongan_darah')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Rhesus
                    </span>

                    <select
                        wire:model="rhesus"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('rhesus'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('rhesus'),
                        ])
                    >
                        <option value="">
                            Pilih rhesus
                        </option>

                        @foreach ($this->opsiRhesusDarah() as $opsi)
                            <option value="{{ $opsi['value'] }}">
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>

                    @error('rhesus')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>
            </div>
        </section>

        {{-- Alamat --}}
        <section class="rounded-2xl border border-[#e6e3df] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-4">
                <h2 class="text-xl font-semibold text-[#191c20]">
                    Alamat Domisili
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#584141]">
                    Gunakan alamat tempat tinggal saat ini.
                </p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <label class="sm:col-span-2">
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Alamat Lengkap
                    </span>

                    <textarea
                        wire:model="alamat"
                        rows="4"
                        @class([
                            'w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('alamat'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('alamat'),
                        ])
                    ></textarea>

                    @error('alamat')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Provinsi
                    </span>

                    <input
                        type="text"
                        wire:model="provinsi"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('provinsi'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('provinsi'),
                        ])
                    >

                    @error('provinsi')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Kota/Kabupaten
                    </span>

                    <input
                        type="text"
                        wire:model="kota"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('kota'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('kota'),
                        ])
                    >

                    @error('kota')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Kecamatan
                    </span>

                    <input
                        type="text"
                        wire:model="kecamatan"
                        class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-4 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                    >

                    @error('kecamatan')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Kode Pos
                    </span>

                    <input
                        type="text"
                        wire:model="kode_pos"
                        inputmode="numeric"
                        class="h-12 w-full rounded-xl border border-[#e2e2e9] bg-white px-4 text-sm text-[#191c20] focus:border-[#76001c] focus:ring-[#76001c]/15"
                    >

                    @error('kode_pos')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>
            </div>
        </section>

        {{-- Kontak darurat --}}
        <section class="rounded-2xl border border-[#e6e3df] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-4">
                <h2 class="text-xl font-semibold text-[#191c20]">
                    Kontak Darurat
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#584141]">
                    Kontak ini dapat digunakan apabila terjadi kondisi darurat saat proses donor.
                </p>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Nama Kontak Darurat
                    </span>

                    <input
                        type="text"
                        wire:model="nama_kontak_darurat"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('nama_kontak_darurat'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('nama_kontak_darurat'),
                        ])
                    >

                    @error('nama_kontak_darurat')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>

                <label>
                    <span class="mb-2 block text-sm font-semibold text-[#191c20]">
                        Telepon Kontak Darurat
                    </span>

                    <input
                        type="tel"
                        wire:model="telepon_kontak_darurat"
                        @class([
                            'h-12 w-full rounded-xl border bg-white px-4 text-sm text-[#191c20] focus:ring-[#76001c]/15',
                            'border-[#ba1a1a] focus:border-[#ba1a1a]' => $errors->has('telepon_kontak_darurat'),
                            'border-[#e2e2e9] focus:border-[#76001c]' => ! $errors->has('telepon_kontak_darurat'),
                        ])
                    >

                    @error('telepon_kontak_darurat')
                        <span class="mt-1 block text-xs text-[#ba1a1a]">
                            {{ $message }}
                        </span>
                    @enderror
                </label>
            </div>

            <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] p-4">
                <input
                    type="checkbox"
                    wire:model="bersedia_dihubungi"
                    class="mt-0.5 h-5 w-5 rounded border-[#e0bfbf] text-[#76001c] focus:ring-[#76001c]/20"
                >

                <span>
                    <span class="block text-sm font-semibold text-[#191c20]">
                        Bersedia dihubungi
                    </span>

                    <span class="mt-1 block text-xs leading-5 text-[#584141]">
                        Saya bersedia menerima informasi jadwal donor dan kebutuhan donor darah dari sistem.
                    </span>
                </span>
            </label>
        </section>

        {{-- Actions --}}
        <section class="sticky bottom-20 z-20 rounded-2xl border border-[#e6e3df] bg-white/95 p-4 shadow-[0_10px_40px_rgba(25,28,32,0.1)] backdrop-blur lg:bottom-4">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button
                    type="button"
                    wire:click="batalkanPerubahan"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-6 text-sm font-semibold text-[#584141] transition hover:bg-[#f3f3fa]"
                >
                    Batalkan Perubahan
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpanProfil"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-7 text-sm font-semibold text-white transition hover:bg-[#991b2f] disabled:cursor-wait disabled:opacity-60"
                >
                    <span
                        wire:loading.remove
                        wire:target="simpanProfil"
                    >
                        Simpan Profil
                    </span>

                    <span
                        wire:loading
                        wire:target="simpanProfil"
                    >
                        Menyimpan...
                    </span>
                </button>
            </div>
        </section>
    </form>
</div>