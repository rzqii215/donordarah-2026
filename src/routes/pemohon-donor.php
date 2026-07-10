<?php

use App\Enums\GolonganDarah;
use App\Enums\RhesusDarah;
use App\Enums\StatusDistribusiDarah;
use App\Enums\StatusPengguna;
use App\Enums\StatusPermintaanDarah;
use App\Enums\StatusVerifikasiRumahSakit;
use App\Enums\TingkatUrgensiPermintaanDarah;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use App\Services\LayananPermintaanDarah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

Route::middleware('auth')
    ->prefix('pemohon-donor')
    ->name('pemohon-donor.')
    ->group(function (): void {
        $ambilPenggunaPemohon = function (): User {
            $pengguna = Auth::user();

            if (! $pengguna instanceof User) {
                abort(401);
            }

            if (! $pengguna->hasRole('pemohon_donor')) {
                abort(403);
            }

            if ($pengguna->status !== StatusPengguna::Aktif) {
                Auth::guard('web')->logout();

                request()->session()->invalidate();
                request()->session()->regenerateToken();

                redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Akun Pemohon Donor belum aktif atau sedang dibatasi.'
                    )
                    ->send();

                exit;
            }

            return $pengguna;
        };

        $ambilProfilPemohon = function (
            User $pengguna
        ): ?ProfilRumahSakit {
            return ProfilRumahSakit::query()
                ->where(
                    'pengguna_id',
                    $pengguna->id
                )
                ->first();
        };

        $buatKodePemohon = function (): string {
            $nomorUrut = ProfilRumahSakit::query()
                ->count() + 1;

            do {
                $kode = 'PMH-' . now()->format('Y') . '-' . str_pad(
                    (string) $nomorUrut,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                $sudahAda = ProfilRumahSakit::query()
                    ->where(
                        'kode_rumah_sakit',
                        $kode
                    )
                    ->exists();

                $nomorUrut++;
            } while ($sudahAda);

            return $kode;
        };

        Route::get('/', function () {
            return redirect()->route(
                'pemohon-donor.beranda'
            );
        })->name('index');

        Route::get('/beranda', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $pengajuanBaru = 0;
            $diproses = 0;
            $diterima = 0;
            $distribusi = 0;
            $pengajuanTerbaru = collect();
            $riwayatAktivitas = collect();
            $jadwalDistribusi = collect();

            if ($profil !== null) {
                $pengajuanBaru = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->where(
                        'status',
                        StatusPermintaanDarah::Diajukan->value
                    )
                    ->count();

                $diproses = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->whereIn('status', [
                        StatusPermintaanDarah::Diajukan->value,
                        StatusPermintaanDarah::Ditinjau->value,
                        StatusPermintaanDarah::MenungguStok->value,
                        StatusPermintaanDarah::Disetujui->value,
                    ])
                    ->count();

                $diterima = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->where(
                        'status',
                        StatusPermintaanDarah::SiapDiambil->value
                    )
                    ->count();

                $distribusi = DistribusiDarah::query()
                    ->whereHas(
                        'permintaan',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'profil_rumah_sakit_id',
                                $profil->id
                            )
                    )
                    ->count();

                $pengajuanTerbaru = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->latest('created_at')
                    ->limit(3)
                    ->get();

                $riwayatAktivitas = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->latest('updated_at')
                    ->limit(3)
                    ->get();

                $jadwalDistribusi = DistribusiDarah::query()
                    ->with('permintaan')
                    ->whereHas(
                        'permintaan',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'profil_rumah_sakit_id',
                                $profil->id
                            )
                    )
                    ->orderByDesc('dijadwalkan_pada')
                    ->limit(2)
                    ->get();
            }

            return view('pemohon-donor.beranda', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'pengajuanBaru' => $pengajuanBaru,
                'diproses' => $diproses,
                'diterima' => $diterima,
                'distribusi' => $distribusi,
                'pengajuanTerbaru' => $pengajuanTerbaru,
                'riwayatAktivitas' => $riwayatAktivitas,
                'jadwalDistribusi' => $jadwalDistribusi,
            ]);
        })->name('beranda');

        Route::get('/pengajuan', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $q = trim(
                (string) request('q', '')
            );

            $statusAktif = request('status');

            $pengajuan = collect();

            $totalPengajuan = 0;
            $pengajuanAktif = 0;
            $pengajuanSelesai = 0;
            $pengajuanDibatalkan = 0;

            if ($profil !== null) {
                $queryDasar = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    );

                $totalPengajuan = (clone $queryDasar)
                    ->count();

                $pengajuanAktif = (clone $queryDasar)
                    ->whereIn('status', [
                        StatusPermintaanDarah::Diajukan->value,
                        StatusPermintaanDarah::Ditinjau->value,
                        StatusPermintaanDarah::MenungguStok->value,
                        StatusPermintaanDarah::Disetujui->value,
                        StatusPermintaanDarah::SiapDiambil->value,
                    ])
                    ->count();

                $pengajuanSelesai = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusPermintaanDarah::Selesai->value
                    )
                    ->count();

                $pengajuanDibatalkan = (clone $queryDasar)
                    ->whereIn('status', [
                        StatusPermintaanDarah::Ditolak->value,
                        StatusPermintaanDarah::Dibatalkan->value,
                    ])
                    ->count();

                $pengajuan = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->when(
                        filled($q),
                        fn (Builder $query): Builder => $query
                            ->where(function (Builder $subQuery) use ($q): void {
                                $subQuery
                                    ->where(
                                        'nomor_permintaan',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhere(
                                        'referensi_pasien',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhere(
                                        'nama_dokter',
                                        'like',
                                        "%{$q}%"
                                    );
                            })
                    )
                    ->when(
                        filled($statusAktif),
                        fn (Builder $query): Builder => $query
                            ->where(
                                'status',
                                $statusAktif
                            )
                    )
                    ->latest('created_at')
                    ->limit(20)
                    ->get();
            }

            return view('pemohon-donor.pengajuan.index', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'pengajuan' => $pengajuan,
                'q' => $q,
                'statusAktif' => $statusAktif,
                'statusOptions' => StatusPermintaanDarah::cases(),
                'totalPengajuan' => $totalPengajuan,
                'pengajuanAktif' => $pengajuanAktif,
                'pengajuanSelesai' => $pengajuanSelesai,
                'pengajuanDibatalkan' => $pengajuanDibatalkan,
            ]);
        })->name('pengajuan.index');

        Route::get('/pengajuan/buat', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            return view('pemohon-donor.pengajuan.create', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'golonganOptions' => GolonganDarah::cases(),
                'rhesusOptions' => RhesusDarah::cases(),
                'urgensiOptions' => TingkatUrgensiPermintaanDarah::cases(),
            ]);
        })->name('pengajuan.create');

        Route::post('/pengajuan', function (Request $request) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if ($profil === null) {
                return redirect()
                    ->route('pemohon-donor.profil.index')
                    ->with(
                        'error',
                        'Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu.'
                    );
            }

            $data = $request->validate([
                'referensi_pasien' => [
                    'required',
                    'string',
                    'max:150',
                ],
                'nama_dokter' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'golongan_darah' => [
                    'required',
                    Rule::enum(GolonganDarah::class),
                ],
                'rhesus' => [
                    'required',
                    Rule::enum(RhesusDarah::class),
                ],
                'jumlah_kantong' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:100',
                ],
                'tingkat_urgensi' => [
                    'required',
                    Rule::enum(TingkatUrgensiPermintaanDarah::class),
                ],
                'dibutuhkan_pada' => [
                    'required',
                    'date',
                    'after_or_equal:now',
                ],
                'dokumen_permintaan' => [
                    'nullable',
                    'file',
                    'mimes:pdf,jpg,jpeg,png',
                    'max:4096',
                ],
                'catatan' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

            $pathDokumen = null;

            if ($request->hasFile('dokumen_permintaan')) {
                $pathDokumen = $request
                    ->file('dokumen_permintaan')
                    ?->store(
                        'dokumen-pengajuan-kebutuhan-donor',
                        'public'
                    );
            }

            try {
                app(
                    LayananPermintaanDarah::class
                )->buat(
                    rumahSakit: $profil,
                    data: [
                        'referensi_pasien' => $data['referensi_pasien'],
                        'nama_dokter' => $data['nama_dokter'],
                        'golongan_darah' => $data['golongan_darah'],
                        'rhesus' => $data['rhesus'],
                        'jumlah_kantong' => (int) $data['jumlah_kantong'],
                        'tingkat_urgensi' => $data['tingkat_urgensi'],
                        'dibutuhkan_pada' => $data['dibutuhkan_pada'],
                        'path_dokumen_permintaan' => $pathDokumen,
                        'catatan' => $data['catatan'] ?? null,
                    ],
                );
            } catch (\Throwable $throwable) {
                if (filled($pathDokumen)) {
                    Storage::disk('public')->delete(
                        $pathDokumen
                    );
                }

                throw $throwable;
            }

            return redirect()
                ->route('pemohon-donor.pengajuan.index')
                ->with(
                    'success',
                    'Pengajuan kebutuhan donor berhasil dibuat.'
                );
        })->name('pengajuan.store');

        Route::get('/pengajuan/bukti/unduh-terbaru', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if ($profil === null) {
                return redirect()
                    ->route('pemohon-donor.profil.index')
                    ->with(
                        'error',
                        'Profil Pemohon Donor belum tersedia. Lengkapi profil terlebih dahulu.'
                    );
            }

            $pengajuan = PermintaanDarah::query()
                ->where(
                    'profil_rumah_sakit_id',
                    $profil->id
                )
                ->latest('created_at')
                ->first();

            if ($pengajuan === null) {
                return redirect()
                    ->route('pemohon-donor.pengajuan.index')
                    ->with(
                        'error',
                        'Belum ada pengajuan yang bisa diunduh buktinya.'
                    );
            }

            return redirect()->route(
                'pemohon-donor.pengajuan.bukti.unduh',
                $pengajuan
            );
        })->name('pengajuan.bukti.terbaru');

        Route::get('/pengajuan/{permintaanDarah}/bukti', function (
            PermintaanDarah $permintaanDarah
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if (
                $profil === null
                || (int) $permintaanDarah->profil_rumah_sakit_id !== (int) $profil->id
            ) {
                abort(404);
            }

            return view('pemohon-donor.pengajuan.bukti', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'pengajuan' => $permintaanDarah,
                'mode' => 'lihat',
            ]);
        })->name('pengajuan.bukti');

        Route::get('/pengajuan/{permintaanDarah}/bukti/unduh', function (
            PermintaanDarah $permintaanDarah
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            if (
                $profil === null
                || (int) $permintaanDarah->profil_rumah_sakit_id !== (int) $profil->id
            ) {
                abort(404);
            }

            $namaFile = 'bukti-pengajuan-'
                . Str::slug(
                    $permintaanDarah->nomor_permintaan
                )
                . '.html';

            return response()
                ->view('pemohon-donor.pengajuan.bukti', [
                    'pengguna' => $pengguna,
                    'profil' => $profil,
                    'pengajuan' => $permintaanDarah,
                    'mode' => 'unduh',
                ])
                ->header(
                    'Content-Type',
                    'text/html; charset=UTF-8'
                )
                ->header(
                    'Content-Disposition',
                    'attachment; filename="' . $namaFile . '"'
                );
        })->name('pengajuan.bukti.unduh');

        Route::get('/distribusi', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $q = trim(
                (string) request('q', '')
            );

            $statusAktif = request('status');

            $distribusi = collect();

            $totalDistribusi = 0;
            $terjadwal = 0;
            $siapDiserahkan = 0;
            $selesai = 0;
            $dibatalkan = 0;

            if ($profil !== null) {
                $queryDasar = DistribusiDarah::query()
                    ->whereHas(
                        'permintaan',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'profil_rumah_sakit_id',
                                $profil->id
                            )
                    );

                $totalDistribusi = (clone $queryDasar)
                    ->count();

                $terjadwal = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusDistribusiDarah::Dijadwalkan->value
                    )
                    ->count();

                $siapDiserahkan = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusDistribusiDarah::SiapDiserahkan->value
                    )
                    ->count();

                $selesai = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusDistribusiDarah::Selesai->value
                    )
                    ->count();

                $dibatalkan = (clone $queryDasar)
                    ->where(
                        'status',
                        StatusDistribusiDarah::Dibatalkan->value
                    )
                    ->count();

                $distribusi = DistribusiDarah::query()
                    ->with([
                        'permintaan',
                    ])
                    ->whereHas(
                        'permintaan',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'profil_rumah_sakit_id',
                                $profil->id
                            )
                    )
                    ->when(
                        filled($q),
                        fn (Builder $query): Builder => $query
                            ->where(function (Builder $subQuery) use ($q): void {
                                $subQuery
                                    ->where(
                                        'nomor_distribusi',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhereHas(
                                        'permintaan',
                                        function (Builder $permintaanQuery) use ($q): void {
                                            $permintaanQuery
                                                ->where(
                                                    'nomor_permintaan',
                                                    'like',
                                                    "%{$q}%"
                                                )
                                                ->orWhere(
                                                    'referensi_pasien',
                                                    'like',
                                                    "%{$q}%"
                                                );
                                        }
                                    );
                            })
                    )
                    ->when(
                        filled($statusAktif),
                        fn (Builder $query): Builder => $query
                            ->where(
                                'status',
                                $statusAktif
                            )
                    )
                    ->orderByDesc('dijadwalkan_pada')
                    ->limit(20)
                    ->get();
            }

            return view('pemohon-donor.distribusi.index', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'distribusi' => $distribusi,
                'q' => $q,
                'statusAktif' => $statusAktif,
                'statusOptions' => StatusDistribusiDarah::cases(),
                'totalDistribusi' => $totalDistribusi,
                'terjadwal' => $terjadwal,
                'siapDiserahkan' => $siapDiserahkan,
                'selesai' => $selesai,
                'dibatalkan' => $dibatalkan,
            ]);
        })->name('distribusi.index');

        Route::get('/distribusi/{distribusiDarah}/bukti', function (
            DistribusiDarah $distribusiDarah
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $distribusiDarah->loadMissing('permintaan');

            if (
                $profil === null
                || $distribusiDarah->permintaan === null
                || (int) $distribusiDarah->permintaan->profil_rumah_sakit_id !== (int) $profil->id
            ) {
                abort(404);
            }

            return view('pemohon-donor.distribusi.bukti', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'distribusi' => $distribusiDarah,
                'pengajuan' => $distribusiDarah->permintaan,
                'mode' => 'lihat',
            ]);
        })->name('distribusi.bukti');

        Route::get('/distribusi/{distribusiDarah}/bukti/unduh', function (
            DistribusiDarah $distribusiDarah
        ) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $distribusiDarah->loadMissing('permintaan');

            if (
                $profil === null
                || $distribusiDarah->permintaan === null
                || (int) $distribusiDarah->permintaan->profil_rumah_sakit_id !== (int) $profil->id
            ) {
                abort(404);
            }

            $namaFile = 'bukti-distribusi-'
                . Str::slug(
                    $distribusiDarah->nomor_distribusi
                )
                . '.html';

            return response()
                ->view('pemohon-donor.distribusi.bukti', [
                    'pengguna' => $pengguna,
                    'profil' => $profil,
                    'distribusi' => $distribusiDarah,
                    'pengajuan' => $distribusiDarah->permintaan,
                    'mode' => 'unduh',
                ])
                ->header(
                    'Content-Type',
                    'text/html; charset=UTF-8'
                )
                ->header(
                    'Content-Disposition',
                    'attachment; filename="' . $namaFile . '"'
                );
        })->name('distribusi.bukti.unduh');

        Route::get('/profil', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            return view('pemohon-donor.profil.index', [
                'pengguna' => $pengguna,
                'profil' => $profil,
            ]);
        })->name('profil.index');

        Route::put('/profil', function (Request $request) use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon,
            $buatKodePemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $data = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'nomor_telepon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
                'nama_rumah_sakit' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'nomor_izin' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'dokumen_izin' => [
                    'nullable',
                    'file',
                    'mimes:pdf,jpg,jpeg,png',
                    'max:4096',
                ],
                'nama_penanggung_jawab' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'jabatan_penanggung_jawab' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'alamat' => [
                    'required',
                    'string',
                    'max:500',
                ],
                'provinsi' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'kota' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'kecamatan' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'kode_pos' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
                'latitude' => [
                    'nullable',
                    'numeric',
                    'between:-90,90',
                ],
                'longitude' => [
                    'nullable',
                    'numeric',
                    'between:-180,180',
                ],
            ]);

            $pathDokumenIzin = $profil?->path_dokumen_izin;

            if ($request->hasFile('dokumen_izin')) {
                $pathBaru = $request
                    ->file('dokumen_izin')
                    ?->store(
                        'dokumen-izin-pemohon-donor',
                        'public'
                    );

                if (
                    filled($pathDokumenIzin)
                    && filled($pathBaru)
                    && $pathDokumenIzin !== $pathBaru
                ) {
                    Storage::disk('public')->delete(
                        $pathDokumenIzin
                    );
                }

                $pathDokumenIzin = $pathBaru;
            }

            $pengguna->forceFill([
                'name' => $data['name'],
                'nomor_telepon' => $data['nomor_telepon'] ?? null,
            ])->save();

            $statusVerifikasi = $profil?->status_verifikasi
                ?? StatusVerifikasiRumahSakit::Menunggu;

            if (
                $statusVerifikasi === StatusVerifikasiRumahSakit::Ditolak
            ) {
                $statusVerifikasi = StatusVerifikasiRumahSakit::Menunggu;
            }

            ProfilRumahSakit::query()
                ->updateOrCreate(
                    [
                        'pengguna_id' => $pengguna->id,
                    ],
                    [
                        'kode_rumah_sakit' =>
                            $profil?->kode_rumah_sakit
                            ?? $buatKodePemohon(),

                        'nama_rumah_sakit' =>
                            $data['nama_rumah_sakit'],

                        'nomor_izin' =>
                            $data['nomor_izin'] ?? null,

                        'path_dokumen_izin' =>
                            $pathDokumenIzin,

                        'nama_penanggung_jawab' =>
                            $data['nama_penanggung_jawab'],

                        'jabatan_penanggung_jawab' =>
                            $data['jabatan_penanggung_jawab'] ?? null,

                        'alamat' =>
                            $data['alamat'],

                        'provinsi' =>
                            $data['provinsi'] ?? null,

                        'kota' =>
                            $data['kota'] ?? null,

                        'kecamatan' =>
                            $data['kecamatan'] ?? null,

                        'kode_pos' =>
                            $data['kode_pos'] ?? null,

                        'latitude' =>
                            $data['latitude'] ?? null,

                        'longitude' =>
                            $data['longitude'] ?? null,

                        'status_verifikasi' =>
                            $statusVerifikasi,

                        'alasan_penolakan' =>
                            $statusVerifikasi === StatusVerifikasiRumahSakit::Menunggu
                                ? null
                                : $profil?->alasan_penolakan,
                    ]
                );

            return redirect()
                ->route('pemohon-donor.profil.index')
                ->with(
                    'success',
                    'Profil Pemohon Donor berhasil diperbarui.'
                );
        })->name('profil.update');

        Route::get('/riwayat', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            $q = trim(
                (string) request('q', '')
            );

            $jenisAktif = request('jenis');

            $riwayat = collect();

            $totalRiwayat = 0;
            $totalPengajuan = 0;
            $totalDistribusi = 0;

            if ($profil !== null) {
                $riwayatPengajuan = PermintaanDarah::query()
                    ->where(
                        'profil_rumah_sakit_id',
                        $profil->id
                    )
                    ->when(
                        filled($q),
                        fn (Builder $query): Builder => $query
                            ->where(function (Builder $subQuery) use ($q): void {
                                $subQuery
                                    ->where(
                                        'nomor_permintaan',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhere(
                                        'referensi_pasien',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhere(
                                        'nama_dokter',
                                        'like',
                                        "%{$q}%"
                                    );
                            })
                    )
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(function (PermintaanDarah $pengajuan): array {
                        return [
                            'jenis' => 'pengajuan',
                            'judul' => 'Pengajuan Kebutuhan Donor',
                            'nomor' => $pengajuan->nomor_permintaan,
                            'keterangan' => $pengajuan->referensi_pasien,
                            'status' => $pengajuan->status,
                            'waktu' => $pengajuan->updated_at,
                            'deskripsi' => 'Pengajuan diperbarui dengan status '
                                . (
                                    is_object($pengajuan->status)
                                    && method_exists($pengajuan->status, 'label')
                                        ? $pengajuan->status->label()
                                        : Str::headline((string) $pengajuan->status)
                                )
                                . '.',
                        ];
                    });

                $riwayatDistribusi = DistribusiDarah::query()
                    ->with('permintaan')
                    ->whereHas(
                        'permintaan',
                        fn (Builder $query): Builder => $query
                            ->where(
                                'profil_rumah_sakit_id',
                                $profil->id
                            )
                    )
                    ->when(
                        filled($q),
                        fn (Builder $query): Builder => $query
                            ->where(function (Builder $subQuery) use ($q): void {
                                $subQuery
                                    ->where(
                                        'nomor_distribusi',
                                        'like',
                                        "%{$q}%"
                                    )
                                    ->orWhereHas(
                                        'permintaan',
                                        function (Builder $permintaanQuery) use ($q): void {
                                            $permintaanQuery
                                                ->where(
                                                    'nomor_permintaan',
                                                    'like',
                                                    "%{$q}%"
                                                )
                                                ->orWhere(
                                                    'referensi_pasien',
                                                    'like',
                                                    "%{$q}%"
                                                );
                                        }
                                    );
                            })
                    )
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(function (DistribusiDarah $distribusi): array {
                        return [
                            'jenis' => 'distribusi',
                            'judul' => 'Distribusi Kantong Darah',
                            'nomor' => $distribusi->nomor_distribusi,
                            'keterangan' => $distribusi->permintaan?->nomor_permintaan ?? '-',
                            'status' => $distribusi->status,
                            'waktu' => $distribusi->updated_at,
                            'deskripsi' => 'Distribusi diperbarui dengan status '
                                . (
                                    is_object($distribusi->status)
                                    && method_exists($distribusi->status, 'label')
                                        ? $distribusi->status->label()
                                        : Str::headline((string) $distribusi->status)
                                )
                                . '.',
                        ];
                    });

                $totalPengajuan = $riwayatPengajuan->count();
                $totalDistribusi = $riwayatDistribusi->count();

                $riwayat = $riwayatPengajuan
                    ->merge($riwayatDistribusi)
                    ->when(
                        $jenisAktif === 'pengajuan',
                        fn ($collection) => $collection
                            ->where('jenis', 'pengajuan')
                            ->values()
                    )
                    ->when(
                        $jenisAktif === 'distribusi',
                        fn ($collection) => $collection
                            ->where('jenis', 'distribusi')
                            ->values()
                    )
                    ->sortByDesc('waktu')
                    ->values()
                    ->take(50);

                $totalRiwayat = $riwayat->count();
            }

            return view('pemohon-donor.riwayat.index', [
                'pengguna' => $pengguna,
                'profil' => $profil,
                'riwayat' => $riwayat,
                'q' => $q,
                'jenisAktif' => $jenisAktif,
                'totalRiwayat' => $totalRiwayat,
                'totalPengajuan' => $totalPengajuan,
                'totalDistribusi' => $totalDistribusi,
            ]);
        })->name('riwayat.index');

        Route::get('/bantuan', function () {
            return view('pemohon-donor.halaman-sementara', [
                'judul' => 'Bantuan',
                'deskripsi' => 'Pusat bantuan penggunaan Portal Pemohon Donor.',
                'aktif' => 'bantuan',
            ]);
        })->name('bantuan.index');

        Route::get('/pengaturan', function () use (
            $ambilPenggunaPemohon,
            $ambilProfilPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $profil = $ambilProfilPemohon(
                $pengguna
            );

            return view('pemohon-donor.pengaturan.index', [
                'pengguna' => $pengguna,
                'profil' => $profil,
            ]);
        })->name('pengaturan.index');

        Route::put('/pengaturan/akun', function (Request $request) use (
            $ambilPenggunaPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $data = $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($pengguna->id),
                ],

                'nomor_telepon' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
            ], [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah digunakan oleh akun lain.',
                'nomor_telepon.max' => 'Nomor telepon maksimal 30 karakter.',
            ]);

            $pengguna->forceFill([
                'email' => $data['email'],
                'nomor_telepon' => $data['nomor_telepon'] ?? null,
            ])->save();

            return redirect()
                ->route('pemohon-donor.pengaturan.index')
                ->with(
                    'success',
                    'Pengaturan akun berhasil diperbarui.'
                );
        })->name('pengaturan.akun.update');

        Route::put('/pengaturan/password', function (Request $request) use (
            $ambilPenggunaPemohon
        ) {
            $pengguna = $ambilPenggunaPemohon();

            $data = $request->validate([
                'password_lama' => [
                    'required',
                    'string',
                ],

                'password_baru' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ], [
                'password_lama.required' => 'Password lama wajib diisi.',
                'password_baru.required' => 'Password baru wajib diisi.',
                'password_baru.min' => 'Password baru minimal 8 karakter.',
                'password_baru.confirmed' => 'Konfirmasi password baru tidak sesuai.',
            ]);

            if (! Hash::check($data['password_lama'], $pengguna->password)) {
                return back()
                    ->withErrors([
                        'password_lama' => 'Password lama tidak sesuai.',
                    ])
                    ->onlyInput();
            }

            $pengguna->forceFill([
                'password' => Hash::make($data['password_baru']),
            ])->save();

            return redirect()
                ->route('pemohon-donor.pengaturan.index')
                ->with(
                    'success',
                    'Password akun berhasil diperbarui.'
                );
        })->name('pengaturan.password.update');
    });