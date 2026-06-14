<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('item_permintaan_darahs')
            && ! Schema::hasTable('item_permintaan_darah')
        ) {
            Schema::rename(
                'item_permintaan_darahs',
                'item_permintaan_darah'
            );
        }

        if (! Schema::hasTable('item_permintaan_darah')) {
            Schema::create(
                'item_permintaan_darah',
                function (Blueprint $table): void {
                    $table->id();

                    $table->foreignId('permintaan_darah_id')
                        ->constrained('permintaan_darah')
                        ->restrictOnDelete();

                    $table->foreignId('kantong_darah_id')
                        ->constrained('kantong_darah')
                        ->restrictOnDelete();

                    $table->string('status', 30)
                        ->default('allocated')
                        ->index();

                    /*
                     * Nilai true berarti alokasi masih aktif.
                     *
                     * Ketika dilepas atau didistribusikan, nilainya menjadi null.
                     * MariaDB mengizinkan beberapa nilai null pada unique index,
                     * tetapi hanya mengizinkan satu nilai true untuk kantong sama.
                     */
                    $table->boolean('aktif')
                        ->nullable()
                        ->default(true);

                    $table->foreignId('dialokasikan_oleh')
                        ->constrained('users')
                        ->restrictOnDelete();

                    $table->timestamp('dialokasikan_pada');

                    $table->foreignId('dilepas_oleh')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->timestamp('dilepas_pada')
                        ->nullable();

                    $table->text('alasan_pelepasan')
                        ->nullable();

                    $table->timestamp('didistribusikan_pada')
                        ->nullable();

                    $table->text('catatan')
                        ->nullable();

                    $table->timestamps();

                    $table->unique(
                        [
                            'kantong_darah_id',
                            'aktif',
                        ],
                        'item_permintaan_kantong_aktif_unique'
                    );

                    $table->index(
                        [
                            'permintaan_darah_id',
                            'status',
                        ],
                        'item_permintaan_darah_status_idx'
                    );

                    $table->index(
                        [
                            'permintaan_darah_id',
                            'aktif',
                        ],
                        'item_permintaan_darah_aktif_idx'
                    );
                }
            );

            return;
        }

        Schema::table(
            'item_permintaan_darah',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'permintaan_darah_id'
                    )
                ) {
                    $table->foreignId('permintaan_darah_id')
                        ->nullable()
                        ->constrained('permintaan_darah')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'kantong_darah_id'
                    )
                ) {
                    $table->foreignId('kantong_darah_id')
                        ->nullable()
                        ->constrained('kantong_darah')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'status'
                    )
                ) {
                    $table->string('status', 30)
                        ->default('allocated')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'aktif'
                    )
                ) {
                    $table->boolean('aktif')
                        ->nullable()
                        ->default(true);
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'dialokasikan_oleh'
                    )
                ) {
                    $table->foreignId('dialokasikan_oleh')
                        ->nullable()
                        ->constrained('users')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'dialokasikan_pada'
                    )
                ) {
                    $table->timestamp('dialokasikan_pada')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'dilepas_oleh'
                    )
                ) {
                    $table->foreignId('dilepas_oleh')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'dilepas_pada'
                    )
                ) {
                    $table->timestamp('dilepas_pada')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'alasan_pelepasan'
                    )
                ) {
                    $table->text('alasan_pelepasan')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'didistribusikan_pada'
                    )
                ) {
                    $table->timestamp('didistribusikan_pada')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'catatan'
                    )
                ) {
                    $table->text('catatan')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'created_at'
                    )
                ) {
                    $table->timestamp('created_at')
                        ->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'item_permintaan_darah',
                        'updated_at'
                    )
                ) {
                    $table->timestamp('updated_at')
                        ->nullable();
                }
            }
        );

        if (
            ! $this->indexExists(
                table: 'item_permintaan_darah',
                index: 'item_permintaan_kantong_aktif_unique'
            )
        ) {
            Schema::table(
                'item_permintaan_darah',
                function (Blueprint $table): void {
                    $table->unique(
                        [
                            'kantong_darah_id',
                            'aktif',
                        ],
                        'item_permintaan_kantong_aktif_unique'
                    );
                }
            );
        }

        if (
            ! $this->indexExists(
                table: 'item_permintaan_darah',
                index: 'item_permintaan_darah_status_idx'
            )
        ) {
            Schema::table(
                'item_permintaan_darah',
                function (Blueprint $table): void {
                    $table->index(
                        [
                            'permintaan_darah_id',
                            'status',
                        ],
                        'item_permintaan_darah_status_idx'
                    );
                }
            );
        }

        if (
            ! $this->indexExists(
                table: 'item_permintaan_darah',
                index: 'item_permintaan_darah_aktif_idx'
            )
        ) {
            Schema::table(
                'item_permintaan_darah',
                function (Blueprint $table): void {
                    $table->index(
                        [
                            'permintaan_darah_id',
                            'aktif',
                        ],
                        'item_permintaan_darah_aktif_idx'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Migration normalisasi tidak menghapus data alokasi
         * ketika rollback.
         */
    }

    private function indexExists(
        string $table,
        string $index
    ): bool {
        $result = DB::selectOne(
            '
                SELECT COUNT(*) AS total
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                  AND index_name = ?
            ',
            [
                $table,
                $index,
            ]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};