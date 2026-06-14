<?php

namespace App\Models;

use App\Enums\StatusJadwalDonor;
use App\Enums\StatusPendaftaranDonor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JadwalDonor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'jadwal_donor';

    protected $fillable = [
        'lokasi_donor_id',
        'kode_jadwal',
        'judul',
        'slug',
        'deskripsi',
        'mulai_pada',
        'selesai_pada',
        'pendaftaran_dibuka_pada',
        'pendaftaran_ditutup_pada',
        'kuota',
        'status',
        'path_banner',
        'dibuat_oleh',
        'dipublikasikan_pada',
        'dibatalkan_pada',
        'alasan_pembatalan',
    ];

    protected function casts(): array
    {
        return [
            'mulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
            'pendaftaran_dibuka_pada' => 'datetime',
            'pendaftaran_ditutup_pada' => 'datetime',
            'kuota' => 'integer',
            'status' => StatusJadwalDonor::class,
            'dipublikasikan_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JadwalDonor $jadwalDonor): void {
            if (
                blank($jadwalDonor->slug)
                || $jadwalDonor->isDirty('judul')
            ) {
                $jadwalDonor->slug = static::buatSlugUnik(
                    judul: $jadwalDonor->judul,
                    abaikanId: $jadwalDonor->getKey(),
                );
            }
        });
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(
            LokasiDonor::class,
            'lokasi_donor_id'
        );
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    public function pendaftaranDonor(): HasMany
    {
        return $this->hasMany(
            PendaftaranDonor::class,
            'jadwal_donor_id'
        );
    }

    public function pendaftaranAktif(): HasMany
    {
        return $this->pendaftaranDonor()
            ->whereIn(
                'status',
                StatusPendaftaranDonor
                    ::statusMengurangiKuota()
            );
    }

    public function scopeDipublikasikan(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            StatusJadwalDonor::Dipublikasikan->value
        );
    }

    public function scopeAkanDatang(
        Builder $query
    ): Builder {
        return $query->where(
            'mulai_pada',
            '>',
            now()
        );
    }

    public function scopePendaftaranAktif(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                StatusJadwalDonor::Dipublikasikan->value
            )
            ->where(
                'pendaftaran_dibuka_pada',
                '<=',
                now()
            )
            ->where(
                'pendaftaran_ditutup_pada',
                '>=',
                now()
            );
    }

    public function pendaftaranSedangDibuka(): bool
    {
        return $this->status ===
                StatusJadwalDonor::Dipublikasikan
            && now()->betweenIncluded(
                $this->pendaftaran_dibuka_pada,
                $this->pendaftaran_ditutup_pada
            );
    }

    public function jumlahPendaftarAktif(): int
    {
        if ($this->relationLoaded('pendaftaranAktif')) {
            return $this->pendaftaranAktif->count();
        }

        return $this->pendaftaranAktif()->count();
    }

    public function sisaKuota(): int
    {
        return max(
            0,
            $this->kuota - $this->jumlahPendaftarAktif()
        );
    }

    private static function buatSlugUnik(
        string $judul,
        ?int $abaikanId = null
    ): string {
        $slugDasar = Str::slug($judul);

        if ($slugDasar === '') {
            $slugDasar = 'jadwal-donor';
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
            $query->where(
                'id',
                '!=',
                $abaikanId
            );
        }

        return $query->exists();
    }
}