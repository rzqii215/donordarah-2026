<div>
    @php
        $googleRouteAda = \Illuminate\Support\Facades\Route::has('google.redirect');
        $googleUrl = $googleRouteAda
            ? route('google.redirect', ['tujuan' => 'pemohon-donor'])
            : '#';
    @endphp

    <header class="auth-heading auth-heading-row">
        <div class="auth-heading-copy">
            <p class="auth-kicker">Akun institusi</p>
            <h2>Daftar sebagai pemohon donor</h2>
            <p>Untuk rumah sakit, klinik, yayasan, atau institusi yang mengajukan kebutuhan darah.</p>
        </div>
        <a href="{{ route('login') }}" class="auth-back-link">Sudah punya akun?</a>
    </header>

    @error('metodePendaftaran')
        <div class="auth-alert auth-alert--danger" role="alert"><span>{{ $message }}</span></div>
    @enderror

    @if ($metodePendaftaran === '')
        <div class="auth-method-grid">
            <button type="button" wire:click="pilihManual" class="auth-method-card">
                <span class="auth-method-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
                </span>
                <h3>Daftar dengan email</h3>
                <p>Isi data institusi dan verifikasi alamat email resmi yang digunakan.</p>
                <strong>Pilih pendaftaran manual →</strong>
            </button>

            <a href="{{ $googleUrl }}" class="auth-method-card" @if (! $googleRouteAda) aria-disabled="true" @endif>
                <span class="auth-method-icon">
                    <svg class="auth-google-icon" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9Z"/>
                        <path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 8 3l5.6-5.6C34 6.1 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7Z"/>
                        <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44Z"/>
                        <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3a12 12 0 0 1-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9Z"/>
                    </svg>
                </span>
                <h3>Daftar dengan Google</h3>
                <p>Hubungkan akun Google penanggung jawab untuk mempercepat verifikasi email.</p>
                <strong>Lanjutkan dengan Google →</strong>
            </a>
        </div>
    @else
        <div class="auth-method-active">
            <span>{{ $this->menggunakanGoogle() ? 'Akun Google berhasil terhubung.' : 'Pendaftaran manual dipilih.' }}</span>
            <button type="button" wire:click="resetMetodePendaftaran">Ganti metode</button>
        </div>

        <form wire:submit="register" class="auth-form" novalidate>
            <section class="auth-section">
                <div class="auth-section-title">
                    <h3>Informasi institusi</h3>
                    <p>Gunakan identitas resmi yang dapat diverifikasi oleh petugas.</p>
                </div>

                <div class="auth-grid">
                    <label class="auth-field auth-span-full">
                        <span class="auth-field-label">Nama rumah sakit/institusi <span class="auth-required">*</span></span>
                        <span class="auth-control @error('nama_rumah_sakit') auth-control--error @enderror">
                            <input type="text" wire:model="nama_rumah_sakit" placeholder="Nama resmi institusi" autocomplete="organization">
                        </span>
                        @error('nama_rumah_sakit') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Nomor izin institusi <span class="auth-required">*</span></span>
                        <span class="auth-control @error('nomor_izin') auth-control--error @enderror">
                            <input type="text" wire:model="nomor_izin" placeholder="Nomor izin operasional">
                        </span>
                        @error('nomor_izin') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Nomor telepon <span class="auth-required">*</span></span>
                        <span class="auth-control @error('nomor_telepon') auth-control--error @enderror">
                            <input type="tel" wire:model="nomor_telepon" placeholder="021 atau 08xxxxxxxxxx" autocomplete="tel" inputmode="tel">
                        </span>
                        @error('nomor_telepon') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section class="auth-section">
                <div class="auth-section-title">
                    <h3>Penanggung jawab akun</h3>
                    <p>Kontak utama untuk komunikasi terkait pengajuan dan distribusi darah.</p>
                </div>

                <div class="auth-grid">
                    <label class="auth-field">
                        <span class="auth-field-label">Nama penanggung jawab <span class="auth-required">*</span></span>
                        <span class="auth-control @error('nama_penanggung_jawab') auth-control--error @enderror">
                            <input type="text" wire:model="nama_penanggung_jawab" placeholder="Nama lengkap" autocomplete="name" @readonly($this->menggunakanGoogle())>
                        </span>
                        @error('nama_penanggung_jawab') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Jabatan</span>
                        <span class="auth-control @error('jabatan_penanggung_jawab') auth-control--error @enderror">
                            <input type="text" wire:model="jabatan_penanggung_jawab" placeholder="Contoh: Kepala Unit Pelayanan">
                        </span>
                        @error('jabatan_penanggung_jawab') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field auth-span-full">
                        <span class="auth-field-label">Alamat email <span class="auth-required">*</span></span>
                        <span class="auth-control @error('email') auth-control--error @enderror">
                            <input type="email" wire:model="email" placeholder="email@institusi.id" autocomplete="email" inputmode="email" @readonly($this->menggunakanGoogle())>
                        </span>
                        @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    @unless ($this->menggunakanGoogle())
                        <label class="auth-field" x-data="{ tampil: false }">
                            <span class="auth-field-label">Password <span class="auth-required">*</span></span>
                            <span class="auth-control @error('password') auth-control--error @enderror">
                                <input x-bind:type="tampil ? 'text' : 'password'" wire:model="password" placeholder="Minimal 8 karakter" autocomplete="new-password">
                                <button type="button" class="auth-password-toggle" x-on:click="tampil = ! tampil" aria-label="Tampilkan password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                </button>
                            </span>
                            @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="auth-field" x-data="{ tampil: false }">
                            <span class="auth-field-label">Konfirmasi password <span class="auth-required">*</span></span>
                            <span class="auth-control">
                                <input x-bind:type="tampil ? 'text' : 'password'" wire:model="password_confirmation" placeholder="Ulangi password" autocomplete="new-password">
                                <button type="button" class="auth-password-toggle" x-on:click="tampil = ! tampil" aria-label="Tampilkan konfirmasi password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                </button>
                            </span>
                        </label>
                    @endunless
                </div>
            </section>

            <section class="auth-section">
                <div class="auth-section-title">
                    <h3>Alamat institusi</h3>
                    <p>Alamat ini digunakan pada pengajuan dan dokumen distribusi.</p>
                </div>

                <div class="auth-grid">
                    <label class="auth-field auth-span-full">
                        <span class="auth-field-label">Alamat lengkap <span class="auth-required">*</span></span>
                        <span class="auth-control @error('alamat') auth-control--error @enderror">
                            <textarea wire:model="alamat" placeholder="Nama jalan, nomor, gedung, RT/RW"></textarea>
                        </span>
                        @error('alamat') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Provinsi <span class="auth-required">*</span></span>
                        <span class="auth-control @error('provinsi') auth-control--error @enderror"><input type="text" wire:model="provinsi" placeholder="Provinsi"></span>
                        @error('provinsi') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Kota/Kabupaten <span class="auth-required">*</span></span>
                        <span class="auth-control @error('kota') auth-control--error @enderror"><input type="text" wire:model="kota" placeholder="Kota atau kabupaten"></span>
                        @error('kota') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Kecamatan</span>
                        <span class="auth-control"><input type="text" wire:model="kecamatan" placeholder="Kecamatan"></span>
                        @error('kecamatan') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="auth-field">
                        <span class="auth-field-label">Kode pos</span>
                        <span class="auth-control"><input type="text" wire:model="kode_pos" placeholder="Contoh: 12190" inputmode="numeric"></span>
                        @error('kode_pos') <span class="auth-field-error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <label class="auth-check">
                <input type="checkbox" wire:model="menyetujui_ketentuan">
                <span>Saya menyatakan data institusi benar dan menyetujui proses verifikasi akun oleh petugas Donor Darah.</span>
            </label>
            @error('menyetujui_ketentuan') <span class="auth-field-error">{{ $message }}</span> @enderror

            <div class="auth-form-actions">
                <button type="button" wire:click="resetMetodePendaftaran" class="auth-button auth-button--secondary">Kembali</button>
                <button type="submit" class="auth-button auth-button--primary" wire:loading.attr="disabled" wire:target="register">
                    <span wire:loading.remove wire:target="register">Buat akun pemohon</span>
                    <span wire:loading wire:target="register">Mendaftarkan...</span>
                </button>
            </div>
        </form>
    @endif
</div>