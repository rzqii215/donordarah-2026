@php
    $langkahPendaftaran = [
        [
            'nomor' => 1,
            'label' => 'Pilih Jadwal',
        ],
        [
            'nomor' => 2,
            'label' => 'Data Pendonor',
        ],
        [
            'nomor' => 3,
            'label' => 'Skrining Awal',
        ],
        [
            'nomor' => 4,
            'label' => 'Konfirmasi',
        ],
    ];
@endphp

<div class="space-y-6">
    @include('components.shared.safe-flash-message')

    @error('pendaftaran')
        <div class="rounded-2xl border border-[#ffb4ab] bg-[#ffdad6] px-5 py-4 text-sm font-medium text-[#93000a]">
            {{ $message }}
        </div>
    @enderror

    {{-- Indikator langkah --}}
    <section class="rounded-2xl border border-[#e6e3df] bg-white px-4 py-5 sm:px-6">
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-4">
            @foreach ($langkahPendaftaran as $item)
                @php
                    $sudahSelesai =
                        $item['nomor'] < $langkah;

                    $sedangAktif =
                        $item['nomor'] === $langkah;
                @endphp

                <div class="relative flex flex-col items-center text-center">
                    @if (! $loop->last)
                        <div class="absolute left-[calc(50%+24px)] top-5 hidden h-px w-[calc(100%-48px)] bg-[#e2e2e9] sm:block"></div>
                    @endif

                    <span
                        @class([
                            'relative z-10 flex h-10 w-10 items-center justify-center rounded-full border text-sm font-bold',
                            'border-[#76001c] bg-[#76001c] text-white' => $sudahSelesai,
                            'border-[#76001c] bg-[#76001c] text-white ring-4 ring-[#ffdada]' => $sedangAktif,
                            'border-[#d9d9e0] bg-[#f3f3fa] text-[#584141]' => ! $sudahSelesai && ! $sedangAktif,
                        ])
                    >
                        @if ($sudahSelesai)
                            <svg
                                viewBox="0 0 20 20"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.3"
                                aria-hidden="true"
                            >
                                <path
                                    d="m4 10 4 4 8-9"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                ></path>
                            </svg>
                        @else
                            {{ $item['nomor'] }}
                        @endif
                    </span>

                    <span
                        @class([
                            'mt-2 text-xs font-semibold sm:text-sm',
                            'text-[#76001c]' => $sudahSelesai || $sedangAktif,
                            'text-[#584141]' => ! $sudahSelesai && ! $sedangAktif,
                        ])
                    >
                        {{ $item['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Langkah 2: data pendonor --}}
    @if ($langkah === 2)
        <section class="rounded-2xl border border-[#e0bfbf] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-5">
                <h2 class="text-2xl font-semibold tracking-[-0.035em] text-[#191c20]">
                    Periksa Data Pendonor
                </h2>

                <p class="mt-2 text-sm leading-6 text-[#584141]">
                    Pastikan informasi profil berikut sudah benar sebelum melanjutkan ke skrining awal.
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-[#e2e2e9] bg-[#f3f3fa] p-4 sm:flex-row sm:items-center">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#ffdada] text-[#76001c] [&>svg]:h-6 [&>svg]:w-6">
                    <x-donor.icon name="map-pin" />
                </span>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-semibold text-[#191c20]">
                        {{ $this->namaLokasi() }}
                    </h3>

                    <p class="mt-1 text-sm text-[#584141]">
                        {{ $this->tanggalJadwal() }}
                        •
                        {{ $this->jamJadwal() }}
                        WIB
                    </p>

                    <p class="mt-1 text-xs text-[#8c7071]">
                        {{ $this->alamatLokasi() }}
                    </p>
                </div>

                <a
                    href="{{ route('donor.jadwal') }}"
                    wire:navigate
                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[#e6e3df] bg-white px-4 text-sm font-semibold text-[#76001c]"
                >
                    Ganti Jadwal
                </a>
            </div>

            @error('profil')
                <div class="mt-5 rounded-xl border border-[#ffb4ab] bg-[#ffdad6] px-4 py-3 text-sm font-medium leading-6 text-[#93000a]">
                    {{ $message }}

                    <a
                        href="{{ route('donor.profil') }}"
                        wire:navigate
                        class="ml-1 font-bold underline"
                    >
                        Lengkapi profil
                    </a>
                </div>
            @enderror

            @if ($dataProfilBelumLengkap !== [])
                <div class="mt-5 rounded-xl border border-[#f2c879] bg-[#fff4de] px-4 py-3 text-sm text-[#8a4f00]">
                    <strong>Profil belum lengkap:</strong>
                    {{ implode(', ', $dataProfilBelumLengkap) }}.
                </div>
            @endif

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <article class="sm:col-span-2">
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Nama Lengkap
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm text-[#191c20]">
                        {{ $pengguna?->name ?? '-' }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Email
                    </p>

                    <div class="min-h-12 break-all rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm text-[#191c20]">
                        {{ $pengguna?->email ?? '-' }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Nomor Telepon/WhatsApp
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm text-[#191c20]">
                        {{ $pengguna?->nomor_telepon ?? '-' }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Tanggal Lahir
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm text-[#191c20]">
                        {{ $this->tanggalLahir($profilPendonor) }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Jenis Kelamin
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm text-[#191c20]">
                        {{ $this->labelJenisKelamin($profilPendonor) }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Golongan Darah
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm font-bold text-[#76001c]">
                        {{ $this->golonganDarah($profilPendonor) }}
                    </div>
                </article>

                <article>
                    <p class="mb-2 text-sm font-semibold text-[#191c20]">
                        Alamat
                    </p>

                    <div class="min-h-12 rounded-xl border border-[#e2e2e9] bg-[#f9f9ff] px-4 py-3 text-sm leading-6 text-[#191c20]">
                        {{ $profilPendonor?->alamat ?? '-' }}
                    </div>
                </article>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-[#e2e2e9] pt-5 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    wire:click="kembali"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                >
                    Kembali
                </button>

                <button
                    type="button"
                    wire:click="lanjutkanKeSkrining"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-6 text-sm font-semibold text-white transition hover:bg-[#991b2f]"
                >
                    Lanjut ke Skrining
                </button>
            </div>
        </section>
    @endif

    {{-- Langkah 3: skrining --}}
    @if ($langkah === 3)
        <section class="rounded-2xl border border-[#e0bfbf] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-5">
                <h2 class="text-2xl font-semibold tracking-[-0.035em] text-[#191c20]">
                    Skrining Awal
                </h2>

                <p class="mt-2 text-sm leading-6 text-[#584141]">
                    Jawab seluruh pertanyaan sesuai kondisi sebenarnya. Jawaban ini akan diperiksa kembali oleh petugas kesehatan.
                </p>
            </div>

            <div class="mt-5 rounded-xl border border-[#f2c879] bg-[#fff4de] px-4 py-3 text-sm leading-6 text-[#8a4f00]">
                Skrining online bukan penetapan kelayakan akhir. Keputusan kelayakan donor tetap berdasarkan pemeriksaan petugas di lokasi.
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($pertanyaanSkrining as $index => $pertanyaan)
                    <article class="rounded-2xl border border-[#e2e2e9] bg-[#f9f9ff] p-4 sm:p-5">
                        <div>
                            <p class="text-sm font-semibold leading-6 text-[#191c20]">
                                {{ $index + 1 }}.
                                {{ $pertanyaan['pertanyaan'] }}
                            </p>

                            <p class="mt-1 text-xs leading-5 text-[#584141]">
                                {{ $pertanyaan['bantuan'] }}
                            </p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    value="1"
                                    wire:model="{{ $pertanyaan['property'] }}"
                                    class="peer sr-only"
                                >

                                <span class="flex min-h-11 items-center justify-center rounded-xl border border-[#e2e2e9] bg-white text-sm font-semibold text-[#584141] transition peer-checked:border-[#76001c] peer-checked:bg-[#ffdada] peer-checked:text-[#76001c]">
                                    Ya
                                </span>
                            </label>

                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    value="0"
                                    wire:model="{{ $pertanyaan['property'] }}"
                                    class="peer sr-only"
                                >

                                <span class="flex min-h-11 items-center justify-center rounded-xl border border-[#e2e2e9] bg-white text-sm font-semibold text-[#584141] transition peer-checked:border-[#76001c] peer-checked:bg-[#ffdada] peer-checked:text-[#76001c]">
                                    Tidak
                                </span>
                            </label>
                        </div>

                        @error($pertanyaan['property'])
                            <p class="mt-2 text-xs font-medium text-[#ba1a1a]">
                                {{ $message }}
                            </p>
                        @enderror
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                <label
                    for="catatan-pendaftaran"
                    class="mb-2 block text-sm font-semibold text-[#191c20]"
                >
                    Catatan Tambahan
                    <span class="font-normal text-[#8c7071]">
                        (opsional)
                    </span>
                </label>

                <textarea
                    id="catatan-pendaftaran"
                    wire:model="catatan"
                    rows="4"
                    maxlength="1000"
                    placeholder="Tuliskan informasi tambahan yang perlu diketahui petugas..."
                    class="w-full resize-y rounded-xl border border-[#e2e2e9] bg-white px-4 py-3 text-sm text-[#191c20] placeholder:text-[#8c7071] focus:border-[#76001c] focus:ring-[#76001c]/15"
                ></textarea>

                @error('catatan')
                    <p class="mt-1 text-xs text-[#ba1a1a]">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-[#e2e2e9] pt-5 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    wire:click="kembaliKeDataPendonor"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                >
                    Kembali
                </button>

                <button
                    type="button"
                    wire:click="lanjutkanKeKonfirmasi"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-6 text-sm font-semibold text-white transition hover:bg-[#991b2f]"
                >
                    Lanjut ke Konfirmasi
                </button>
            </div>
        </section>
    @endif

    {{-- Langkah 4: konfirmasi --}}
    @if ($langkah === 4)
        <section class="rounded-2xl border border-[#e0bfbf] bg-white p-5 sm:p-7">
            <div class="border-b border-[#e2e2e9] pb-5">
                <h2 class="text-2xl font-semibold tracking-[-0.035em] text-[#191c20]">
                    Konfirmasi Pendaftaran
                </h2>

                <p class="mt-2 text-sm leading-6 text-[#584141]">
                    Periksa kembali jadwal dan jawaban skrining sebelum mengirim pendaftaran.
                </p>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
                <article class="rounded-2xl border border-[#e2e2e9] bg-[#f3f3fa] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#991b2f]">
                        Jadwal Donor
                    </p>

                    <h3 class="mt-3 text-lg font-semibold text-[#191c20]">
                        {{ $jadwal->judul }}
                    </h3>

                    <p class="mt-3 text-sm font-semibold text-[#191c20]">
                        {{ $this->namaLokasi() }}
                    </p>

                    <p class="mt-1 text-sm text-[#584141]">
                        {{ $this->tanggalJadwal() }}
                    </p>

                    <p class="text-sm text-[#584141]">
                        {{ $this->jamJadwal() }}
                        WIB
                    </p>

                    <p class="mt-2 text-xs leading-5 text-[#8c7071]">
                        {{ $this->alamatLokasi() }}
                    </p>
                </article>

                <article class="rounded-2xl border border-[#e2e2e9] bg-white p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#991b2f]">
                        Data Pendonor
                    </p>

                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-[#584141]">
                                Nama
                            </dt>

                            <dd class="text-right font-semibold text-[#191c20]">
                                {{ $pengguna?->name ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-[#584141]">
                                Telepon
                            </dt>

                            <dd class="text-right font-semibold text-[#191c20]">
                                {{ $pengguna?->nomor_telepon ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex justify-between gap-4">
                            <dt class="text-[#584141]">
                                Golongan darah
                            </dt>

                            <dd class="text-right font-bold text-[#76001c]">
                                {{ $this->golonganDarah($profilPendonor) }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <article class="mt-5 rounded-2xl border border-[#e2e2e9] bg-[#f9f9ff] p-5">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#991b2f]">
                        Jawaban Skrining
                    </p>

                    <button
                        type="button"
                        wire:click="kembaliKeSkrining"
                        class="text-xs font-bold text-[#76001c] hover:underline"
                    >
                        Ubah jawaban
                    </button>
                </div>

                <div class="mt-4 divide-y divide-[#e2e2e9]">
                    @foreach ($pertanyaanSkrining as $pertanyaan)
                        <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm leading-6 text-[#584141]">
                                {{ $pertanyaan['pertanyaan'] }}
                            </p>

                            <strong class="shrink-0 text-sm text-[#191c20]">
                                {{ $this->jawabanLabel($pertanyaan['property']) }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            </article>

            @if (filled($catatan))
                <article class="mt-5 rounded-2xl border border-[#e2e2e9] bg-white p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.07em] text-[#8c7071]">
                        Catatan Tambahan
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#584141]">
                        {{ $catatan }}
                    </p>
                </article>
            @endif

            <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-2xl border border-[#e0bfbf] bg-[#fff7f7] p-4">
                <input
                    type="checkbox"
                    wire:model="persetujuan"
                    class="mt-0.5 h-5 w-5 rounded border-[#e0bfbf] text-[#76001c] focus:ring-[#76001c]/20"
                >

                <span class="text-sm leading-6 text-[#584141]">
                    Saya menyatakan bahwa data profil dan jawaban skrining yang diberikan benar sesuai kondisi saya. Saya memahami bahwa kelayakan donor akhir ditentukan oleh petugas kesehatan.
                </span>
            </label>

            @error('persetujuan')
                <p class="mt-2 text-xs font-medium text-[#ba1a1a]">
                    {{ $message }}
                </p>
            @enderror

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-[#e2e2e9] pt-5 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    wire:click="kembaliKeSkrining"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-[#76001c] transition hover:bg-[#f3f3fa]"
                >
                    Kembali
                </button>

                <button
                    type="button"
                    wire:click="kirimPendaftaran"
                    wire:loading.attr="disabled"
                    wire:target="kirimPendaftaran"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#c52a3d] px-7 text-sm font-semibold text-white transition hover:bg-[#991b2f] disabled:cursor-wait disabled:opacity-60"
                >
                    <span
                        wire:loading.remove
                        wire:target="kirimPendaftaran"
                    >
                        Kirim Pendaftaran
                    </span>

                    <span
                        wire:loading
                        wire:target="kirimPendaftaran"
                    >
                        Mengirim...
                    </span>
                </button>
            </div>
        </section>
    @endif
</div>