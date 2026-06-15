<?php

namespace App\Services;

use App\Enums\PeranPengguna;
use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use App\Enums\StatusPengguna;
use App\Models\JadwalDonor;
use App\Models\PendaftaranDonor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LayananPendaftaranDonor
{
    /**
     * @param array<string, mixed> $data
     */
    public function daftar(
        int $jadwalDonorId,
        int $pendonorId,
        array $data = []
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $jadwalDonorId,
            $pendonorId,
            $data
        ): PendaftaranDonor {
            $jadwal = JadwalDonor::query()
                ->lockForUpdate()
                ->findOrFail($jadwalDonorId);

            $pendonor = User::query()
                ->with('profilPendonor')
                ->findOrFail($pendonorId);

            $this->pastikanPendonorValid(
                $pendonor
            );

            $this->pastikanJadwalDapatDidaftar(
                $jadwal
            );

            $this->pastikanBelumTerdaftar(
                jadwalDonorId: $jadwal->id,
                pendonorId: $pendonor->id,
            );

            $this->pastikanKuotaTersedia(
                $jadwal
            );

            return PendaftaranDonor::query()->create([
                'nomor_pendaftaran' =>
                    $this->buatNomorPendaftaran(),

                'jadwal_donor_id' =>
                    $jadwal->id,

                'pendonor_id' =>
                    $pendonor->id,

                'jawaban_skrining' =>
                    $data['jawaban_skrining'] ?? null,

                'status' =>
                    StatusPendaftaranDonor::Menunggu,

                'ditinjau_oleh' =>
                    null,

                'ditinjau_pada' =>
                    null,

                'alasan_penolakan' =>
                    null,

                'hadir_pada' =>
                    null,

                'dibatalkan_pada' =>
                    null,

                'alasan_pembatalan' =>
                    null,

                'selesai_pada' =>
                    null,

                'catatan' =>
                    filled($data['catatan'] ?? null)
                        ? trim(
                            (string) $data['catatan']
                        )
                        : null,
            ]);
        });
    }

    public function setujui(
        PendaftaranDonor $pendaftaran,
        int $petugasId
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $pendaftaran,
            $petugasId
        ): PendaftaranDonor {
            $record = PendaftaranDonor::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            if (! $record->dapatDisetujui()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Pendaftaran ini tidak dapat disetujui dari status saat ini.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPendaftaranDonor::Disetujui,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    now(),

                'alasan_penolakan' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function tolak(
        PendaftaranDonor $pendaftaran,
        int $petugasId,
        string $alasan
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $pendaftaran,
            $petugasId,
            $alasan
        ): PendaftaranDonor {
            $record = PendaftaranDonor::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            if (! $record->dapatDitolak()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Pendaftaran ini tidak dapat ditolak dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan penolakan wajib diisi.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPendaftaranDonor::Ditolak,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    now(),

                'alasan_penolakan' =>
                    $alasanBersih,

                'hadir_pada' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function catatKehadiran(
        PendaftaranDonor $pendaftaran,
        int $petugasId
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $pendaftaran,
            $petugasId
        ): PendaftaranDonor {
            $record = PendaftaranDonor::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            if (! $record->dapatDicatatHadir()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Kehadiran hanya dapat dicatat untuk pendaftaran yang sudah disetujui.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPendaftaranDonor::Hadir,

                'hadir_pada' =>
                    now(),

                'ditinjau_oleh' =>
                    $record->ditinjau_oleh
                    ?? $petugasId,

                'ditinjau_pada' =>
                    $record->ditinjau_pada
                    ?? now(),
            ]);

            return $record->refresh();
        });
    }

    public function tandaiTidakHadir(
        PendaftaranDonor $pendaftaran,
        int $petugasId
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $pendaftaran,
            $petugasId
        ): PendaftaranDonor {
            $record = PendaftaranDonor::query()
                ->with('jadwal')
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            if (
                $record->status !==
                StatusPendaftaranDonor::Disetujui
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Status tidak hadir hanya dapat diberikan pada pendaftaran yang sudah disetujui.',
                ]);
            }

            if (
                now()->lessThan(
                    $record->jadwal->mulai_pada
                )
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Pendonor belum dapat ditandai tidak hadir sebelum kegiatan dimulai.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPendaftaranDonor::TidakHadir,

                'ditinjau_oleh' =>
                    $petugasId,

                'ditinjau_pada' =>
                    now(),

                'hadir_pada' =>
                    null,
            ]);

            return $record->refresh();
        });
    }

    public function batalkan(
        PendaftaranDonor $pendaftaran,
        string $alasan
    ): PendaftaranDonor {
        return DB::transaction(function () use (
            $pendaftaran,
            $alasan
        ): PendaftaranDonor {
            $record = PendaftaranDonor::query()
                ->lockForUpdate()
                ->findOrFail($pendaftaran->id);

            if (! $record->dapatDibatalkan()) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Pendaftaran ini tidak dapat dibatalkan dari status saat ini.',
                ]);
            }

            $alasanBersih = trim($alasan);

            if ($alasanBersih === '') {
                throw ValidationException::withMessages([
                    'alasan' =>
                        'Alasan pembatalan wajib diisi.',
                ]);
            }

            $record->update([
                'status' =>
                    StatusPendaftaranDonor::Dibatalkan,

                'dibatalkan_pada' =>
                    now(),

                'alasan_pembatalan' =>
                    $alasanBersih,
            ]);

            return $record->refresh();
        });
    }

    private function pastikanPendonorValid(
        User $pendonor
    ): void {
        if (
            ! $pendonor->hasRole(
                PeranPengguna::Pendonor->value
            )
        ) {
            throw ValidationException::withMessages([
                'pendonor_id' =>
                    'Pengguna tidak memiliki role Pendonor.',
            ]);
        }

        if (
            $pendonor->status !==
            StatusPengguna::Aktif
        ) {
            throw ValidationException::withMessages([
                'pendonor_id' =>
                    'Akun Pendonor belum aktif.',
            ]);
        }

        if ($pendonor->profilPendonor === null) {
            throw ValidationException::withMessages([
                'pendonor_id' =>
                    'Pengguna belum mempunyai profil Pendonor.',
            ]);
        }
    }

    private function pastikanJadwalDapatDidaftar(
        JadwalDonor $jadwal
    ): void {
        if (
            $jadwal->status !==
            StatusJadwalDonor::Dipublikasikan
        ) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Jadwal donor belum dipublikasikan.',
            ]);
        }

        if (! $jadwal->pendaftaranSedangDibuka()) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Periode pendaftaran belum dibuka atau sudah ditutup.',
            ]);
        }
    }

    private function pastikanBelumTerdaftar(
        int $jadwalDonorId,
        int $pendonorId
    ): void {
        $sudahTerdaftar =
            PendaftaranDonor::withTrashed()
                ->where(
                    'jadwal_donor_id',
                    $jadwalDonorId
                )
                ->where(
                    'pendonor_id',
                    $pendonorId
                )
                ->exists();

        if ($sudahTerdaftar) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Pendonor sudah pernah terdaftar pada jadwal ini.',
            ]);
        }
    }

    private function pastikanKuotaTersedia(
        JadwalDonor $jadwal
    ): void {
        $jumlahPendaftarAktif =
            PendaftaranDonor::query()
                ->where(
                    'jadwal_donor_id',
                    $jadwal->id
                )
                ->whereIn(
                    'status',
                    StatusPendaftaranDonor
                        ::statusMengurangiKuota()
                )
                ->lockForUpdate()
                ->get(['id'])
                ->count();

        $kuota = (int) $jadwal->getAttribute(
            'kuota'
        );

        if ($kuota < 1) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Kuota jadwal donor belum ditentukan.',
            ]);
        }

        if ($jumlahPendaftarAktif >= $kuota) {
            throw ValidationException::withMessages([
                'jadwal_donor_id' =>
                    'Kuota jadwal donor sudah penuh.',
            ]);
        }
    }

    private function buatNomorPendaftaran(): string
    {
        do {
            $nomor = sprintf(
                'REG-%s-%s',
                now()->format('Ymd'),
                Str::upper(
                    Str::random(6)
                )
            );
        } while (
            PendaftaranDonor::withTrashed()
                ->where(
                    'nomor_pendaftaran',
                    $nomor
                )
                ->exists()
        );

        return $nomor;
    }
}