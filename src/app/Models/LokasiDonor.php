<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LokasiDonor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'lokasi_donor';

    protected $fillable = [
        'nama',
        'slug',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kode_pos',
        'latitude',
        'longitude',
        'nama_kontak',
        'nomor_kontak',
        'deskripsi',
        'aktif',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (LokasiDonor $lokasiDonor): void {
            if (
                blank($lokasiDonor->slug)
                || $lokasiDonor->isDirty('nama')
            ) {
                $lokasiDonor->slug = static::buatSlugUnik(
                    nama: $lokasiDonor->nama,
                    abaikanId: $lokasiDonor->getKey(),
                );
            }
        });
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function jadwalDonor(): HasMany
    {
        return $this->hasMany(
            JadwalDonor::class,
            'lokasi_donor_id'
        );
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    private static function buatSlugUnik(
        string $nama,
        ?int $abaikanId = null
    ): string {
        $slugDasar = Str::slug($nama);

        if ($slugDasar === '') {
            $slugDasar = 'lokasi-donor';
        }

        $slug = $slugDasar;
        $urutan = 2;

        while (
            static::slugSudahDigunakan(
                slug: $slug,
                abaikanId: $abaikanId,
            )
        ) {
            $slug = $slugDasar . '-' . $urutan;
            $urutan++;
        }

        return $slug;
    }

    private static function slugSudahDigunakan(
        string $slug,
        ?int $abaikanId = null
    ): bool {
        $query = static::withTrashed()
            ->where('slug', $slug);

        if ($abaikanId !== null) {
            $query->where('id', '!=', $abaikanId);
        }

        return $query->exists();
    }
}