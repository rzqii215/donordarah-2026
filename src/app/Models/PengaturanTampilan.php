<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanTampilan extends Model
{
    protected $table = 'pengaturan_tampilan';

    protected $fillable = [
        'gambar_auth',
        'gambar_auth_alt',
    ];
}