<?php

namespace App\Http\Controllers\PemohonDonor\Portal;

use Illuminate\Contracts\View\View;

class BantuanController extends PortalPemohonController
{
    public function __invoke(): View
    {
        $this->penggunaPemohon();

        return view(
            'pemohon-donor.halaman-sementara',
            [
                'judul' => 'Bantuan',
                'deskripsi' => 'Pusat bantuan penggunaan Portal Pemohon Donor.',
                'aktif' => 'bantuan',
            ]
        );
    }
}
