<?php

namespace App\Http\Controllers\PemohonDonor;

use App\Enums\StatusPengguna;
use App\Http\Controllers\Controller;
use App\Models\DistribusiDarah;
use App\Models\PermintaanDarah;
use App\Models\ProfilRumahSakit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BuktiPemohonDonorController extends Controller
{
    public function pengajuan(
        PermintaanDarah $permintaanDarah
    ): Response {
        [$pengguna, $profil] = $this->pemohonAktif();

        $this->pastikanPengajuanMilikPemohon(
            $permintaanDarah,
            $profil
        );

        return $this->buatPdf(
            $this->dataPengajuan(
                $pengguna,
                $profil,
                $permintaanDarah
            )
        )->stream(
            $this->namaFilePengajuan(
                $permintaanDarah
            )
        );
    }

    public function unduhPengajuan(
        PermintaanDarah $permintaanDarah
    ): Response {
        [$pengguna, $profil] = $this->pemohonAktif();

        $this->pastikanPengajuanMilikPemohon(
            $permintaanDarah,
            $profil
        );

        return $this->buatPdf(
            $this->dataPengajuan(
                $pengguna,
                $profil,
                $permintaanDarah
            )
        )->download(
            $this->namaFilePengajuan(
                $permintaanDarah
            )
        );
    }

    public function distribusi(
        DistribusiDarah $distribusiDarah
    ): Response {
        [$pengguna, $profil] = $this->pemohonAktif();

        $pengajuan = $this->ambilPengajuanDistribusi(
            $distribusiDarah,
            $profil
        );

        return $this->buatPdf(
            $this->dataDistribusi(
                $pengguna,
                $profil,
                $pengajuan,
                $distribusiDarah
            )
        )->stream(
            $this->namaFileDistribusi(
                $distribusiDarah
            )
        );
    }

    public function unduhDistribusi(
        DistribusiDarah $distribusiDarah
    ): Response {
        [$pengguna, $profil] = $this->pemohonAktif();

        $pengajuan = $this->ambilPengajuanDistribusi(
            $distribusiDarah,
            $profil
        );

        return $this->buatPdf(
            $this->dataDistribusi(
                $pengguna,
                $profil,
                $pengajuan,
                $distribusiDarah
            )
        )->download(
            $this->namaFileDistribusi(
                $distribusiDarah
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dataPengajuan(
        User $pengguna,
        ProfilRumahSakit $profil,
        PermintaanDarah $pengajuan
    ): array {
        return [
            'jenis' => 'pengajuan',
            'jenisBukti' => 'pengajuan',
            'judul' => 'Bukti Pengajuan Permintaan Darah',
            'pengguna' => $pengguna,
            'profil' => $profil,
            'pengajuan' => $pengajuan,
            'permintaanDarah' => $pengajuan,
            'distribusi' => null,
            'distribusiDarah' => null,
            'dibuatPada' => now(),
            'dicetakPada' => now(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dataDistribusi(
        User $pengguna,
        ProfilRumahSakit $profil,
        PermintaanDarah $pengajuan,
        DistribusiDarah $distribusi
    ): array {
        return [
            'jenis' => 'distribusi',
            'jenisBukti' => 'distribusi',
            'judul' => 'Bukti Distribusi Darah',
            'pengguna' => $pengguna,
            'profil' => $profil,
            'pengajuan' => $pengajuan,
            'permintaanDarah' => $pengajuan,
            'distribusi' => $distribusi,
            'distribusiDarah' => $distribusi,
            'dibuatPada' => now(),
            'dicetakPada' => now(),
        ];
    }

    /**
     * @return array{0: User, 1: ProfilRumahSakit}
     */
    private function pemohonAktif(): array
    {
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

            abort(
                403,
                'Akun Pemohon Donor belum aktif atau sedang dibatasi.'
            );
        }

        $profil = ProfilRumahSakit::query()
            ->where(
                'pengguna_id',
                $pengguna->id
            )
            ->first();

        if (! $profil instanceof ProfilRumahSakit) {
            abort(
                404,
                'Profil rumah sakit tidak ditemukan.'
            );
        }

        return [
            $pengguna,
            $profil,
        ];
    }

    private function pastikanPengajuanMilikPemohon(
        PermintaanDarah $pengajuan,
        ProfilRumahSakit $profil
    ): void {
        if (
            (int) $pengajuan->profil_rumah_sakit_id
            !== (int) $profil->id
        ) {
            abort(404);
        }
    }

    private function ambilPengajuanDistribusi(
        DistribusiDarah $distribusi,
        ProfilRumahSakit $profil
    ): PermintaanDarah {
        $distribusi->loadMissing('permintaan');

        $pengajuan = $distribusi->permintaan;

        if (! $pengajuan instanceof PermintaanDarah) {
            abort(404);
        }

        $this->pastikanPengajuanMilikPemohon(
            $pengajuan,
            $profil
        );

        return $pengajuan;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buatPdf(
        array $data
    ): DomPdf {
        return Pdf::loadView(
            'pemohon-donor.bukti-pdf',
            $data
        )
            ->setPaper(
                'a4',
                'portrait'
            )
            ->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
    }

    private function namaFilePengajuan(
        PermintaanDarah $pengajuan
    ): string {
        $nomor = Str::slug(
            (string) $pengajuan->nomor_permintaan
        );

        if ($nomor === '') {
            $nomor = (string) $pengajuan->getKey();
        }

        return "bukti-pengajuan-{$nomor}.pdf";
    }

    private function namaFileDistribusi(
        DistribusiDarah $distribusi
    ): string {
        $nomor = Str::slug(
            (string) $distribusi->nomor_distribusi
        );

        if ($nomor === '') {
            $nomor = (string) $distribusi->getKey();
        }

        return "bukti-distribusi-{$nomor}.pdf";
    }
}