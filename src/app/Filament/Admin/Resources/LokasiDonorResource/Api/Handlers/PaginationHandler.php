<?php

namespace App\Filament\Admin\Resources\LokasiDonorResource\Api\Handlers;

use App\Filament\Admin\Resources\LokasiDonorResource;
use App\Filament\Admin\Resources\LokasiDonorResource\Api\Transformers\LokasiDonorTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Rupadana\ApiService\Http\Handlers;

class PaginationHandler extends Handlers
{
    public static string|null $uri = '/';

    public static string|null $resource =
        LokasiDonorResource::class;

    /**
     * Endpoint dapat diakses tanpa token Sanctum.
     */
    public static bool $public = true;

    /**
     * Daftar lokasi donor publik.
     *
     * Parameter:
     *
     * - filter[q]
     * - filter[kota]
     * - filter[provinsi]
     * - sort=nama
     * - sort=-created_at
     * - page
     * - per_page
     */
    public function handler(
        Request $request
    ) {
        $query = static::getEloquentQuery();

        $table = $query
            ->getModel()
            ->getTable();

        $columns = Schema::getColumnListing(
            $table
        );

        $kolomNama = $this->kolomTersedia(
            columns: $columns,
            candidates: [
                'nama',
                'nama_lokasi',
                'judul',
            ],
        );

        $kolomAlamat = $this->kolomTersedia(
            columns: $columns,
            candidates: [
                'alamat',
                'alamat_lengkap',
            ],
        );

        $kolomKota = $this->kolomTersedia(
            columns: $columns,
            candidates: [
                'kota',
                'kabupaten_kota',
                'kabupaten',
            ],
        );

        $kolomProvinsi = $this->kolomTersedia(
            columns: $columns,
            candidates: [
                'provinsi',
            ],
        );

        $filters = $request->input(
            'filter',
            []
        );

        if (! is_array($filters)) {
            $filters = [];
        }

        $pencarian = trim(
            (string) ($filters['q'] ?? '')
        );

        if ($pencarian !== '') {
            $kolomPencarian = array_values(
                array_filter([
                    $kolomNama,
                    $kolomAlamat,
                    $kolomKota,
                    $kolomProvinsi,
                ])
            );

            if ($kolomPencarian !== []) {
                $query->where(
                    function (
                        Builder $subQuery
                    ) use (
                        $kolomPencarian,
                        $pencarian
                    ): void {
                        foreach (
                            $kolomPencarian
                            as $index => $column
                        ) {
                            if ($index === 0) {
                                $subQuery->where(
                                    $column,
                                    'like',
                                    '%' . $pencarian . '%'
                                );

                                continue;
                            }

                            $subQuery->orWhere(
                                $column,
                                'like',
                                '%' . $pencarian . '%'
                            );
                        }
                    }
                );
            }
        }

        $filterKota = trim(
            (string) ($filters['kota'] ?? '')
        );

        if (
            $filterKota !== ''
            && $kolomKota !== null
        ) {
            $query->where(
                $kolomKota,
                'like',
                '%' . $filterKota . '%'
            );
        }

        $filterProvinsi = trim(
            (string) ($filters['provinsi'] ?? '')
        );

        if (
            $filterProvinsi !== ''
            && $kolomProvinsi !== null
        ) {
            $query->where(
                $kolomProvinsi,
                'like',
                '%' . $filterProvinsi . '%'
            );
        }

        $this->terapkanSorting(
            query: $query,
            request: $request,
            columns: $columns,
            kolomNama: $kolomNama,
            kolomKota: $kolomKota,
            kolomProvinsi: $kolomProvinsi,
        );

        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    12
                ),
                1
            ),
            50
        );

        $records = $query
            ->paginate($perPage)
            ->appends(
                $request->query()
            );

        return LokasiDonorTransformer::collection(
            $records
        );
    }

    /**
     * @param array<int, string> $columns
     */
    private function terapkanSorting(
        Builder $query,
        Request $request,
        array $columns,
        ?string $kolomNama,
        ?string $kolomKota,
        ?string $kolomProvinsi
    ): void {
        $sort = trim(
            (string) $request->query(
                'sort',
                ''
            )
        );

        $direction = str_starts_with(
            $sort,
            '-'
        )
            ? 'desc'
            : 'asc';

        $sortKey = ltrim(
            $sort,
            '-'
        );

        $mapping = [
            'nama' => $kolomNama,
            'nama_lokasi' => $kolomNama,
            'kota' => $kolomKota,
            'provinsi' => $kolomProvinsi,

            'created_at' => in_array(
                'created_at',
                $columns,
                true
            )
                ? 'created_at'
                : null,

            'updated_at' => in_array(
                'updated_at',
                $columns,
                true
            )
                ? 'updated_at'
                : null,
        ];

        $sortColumn = $mapping[$sortKey]
            ?? null;

        if ($sortColumn === null) {
            $sortColumn = $kolomNama;

            if (
                $sortColumn === null
                && in_array(
                    'created_at',
                    $columns,
                    true
                )
            ) {
                $sortColumn = 'created_at';
                $direction = 'desc';
            }

            if (
                $sortColumn === null
                && in_array(
                    'id',
                    $columns,
                    true
                )
            ) {
                $sortColumn = 'id';
                $direction = 'asc';
            }
        }

        if ($sortColumn !== null) {
            $query->orderBy(
                $sortColumn,
                $direction
            );
        }
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $candidates
     */
    private function kolomTersedia(
        array $columns,
        array $candidates
    ): ?string {
        foreach ($candidates as $candidate) {
            if (
                in_array(
                    $candidate,
                    $columns,
                    true
                )
            ) {
                return $candidate;
            }
        }

        return null;
    }
}