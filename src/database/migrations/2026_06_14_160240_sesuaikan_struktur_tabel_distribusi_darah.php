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
            Schema::hasTable('distribusi_darahs')
            && ! Schema::hasTable('distribusi_darah')
        ) {
            Schema::rename(
                'distribusi_darahs',
                'distribusi_darah'
            );
        }

        if (! Schema::hasTable('distribusi_darah')) {
            Schema::create(
                'distribusi_darah',
                function (Blueprint $table): void {
                    $table->id();

                    $table->string('nomor_distribusi', 40)
                        ->unique();

                    $table->foreignId('permintaan_darah_id')
                        ->unique()
                        ->constrained('permintaan_darah')
                        ->restrictOnDelete();

                    $table->foreignId('disiapkan_oleh')
                        ->constrained('users')
                        ->restrictOnDelete();

                    $table->dateTime('dijadwalkan_pada')
                        ->index();

                    $table->string('status', 30)
                        ->default('scheduled')
                        ->index();

                    $table->foreignId('diserahkan_oleh')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->string('nama_penerima')
                        ->nullable();

                    $table->string('jabatan_penerima', 150)
                        ->nullable();

                    $table->string(
                        'nomor_identitas_penerima',
                        100
                    )->nullable();

                    $table->string(
                        'path_bukti_serah_terima'
                    )->nullable();

                    $table->timestamp('diserahkan_pada')
                        ->nullable();

                    $table->timestamp('dibatalkan_pada')
                        ->nullable();

                    $table->text('alasan_pembatalan')
                        ->nullable();

                    $table->text('catatan')
                        ->nullable();

                    $table->timestamps();

                    $table->index(
                        [
                            'status',
                            'dijadwalkan_pada',
                        ],
                        'distribusi_darah_status_jadwal_idx'
                    );
                }
            );

            return;
        }

        Schema::table(
            'distribusi_darah',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'nomor_distribusi'
                    )
                ) {
                    $table->string(
                        'nomor_distribusi',
                        40
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'permintaan_darah_id'
                    )
                ) {
                    $table->foreignId(
                        'permintaan_darah_id'
                    )
                        ->nullable()
                        ->constrained('permintaan_darah')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'disiapkan_oleh'
                    )
                ) {
                    $table->foreignId(
                        'disiapkan_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'dijadwalkan_pada'
                    )
                ) {
                    $table->dateTime(
                        'dijadwalkan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'status'
                    )
                ) {
                    $table->string('status', 30)
                        ->default('scheduled')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'diserahkan_oleh'
                    )
                ) {
                    $table->foreignId(
                        'diserahkan_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'nama_penerima'
                    )
                ) {
                    $table->string(
                        'nama_penerima'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'jabatan_penerima'
                    )
                ) {
                    $table->string(
                        'jabatan_penerima',
                        150
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'nomor_identitas_penerima'
                    )
                ) {
                    $table->string(
                        'nomor_identitas_penerima',
                        100
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'path_bukti_serah_terima'
                    )
                ) {
                    $table->string(
                        'path_bukti_serah_terima'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'diserahkan_pada'
                    )
                ) {
                    $table->timestamp(
                        'diserahkan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'dibatalkan_pada'
                    )
                ) {
                    $table->timestamp(
                        'dibatalkan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'alasan_pembatalan'
                    )
                ) {
                    $table->text(
                        'alasan_pembatalan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'catatan'
                    )
                ) {
                    $table->text(
                        'catatan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'created_at'
                    )
                ) {
                    $table->timestamp(
                        'created_at'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'distribusi_darah',
                        'updated_at'
                    )
                ) {
                    $table->timestamp(
                        'updated_at'
                    )->nullable();
                }
            }
        );

        if (
            ! $this->indexExists(
                table: 'distribusi_darah',
                index: 'distribusi_darah_nomor_distribusi_unique'
            )
        ) {
            Schema::table(
                'distribusi_darah',
                function (Blueprint $table): void {
                    $table->unique(
                        'nomor_distribusi',
                        'distribusi_darah_nomor_distribusi_unique'
                    );
                }
            );
        }

        if (
            ! $this->indexExists(
                table: 'distribusi_darah',
                index: 'distribusi_darah_permintaan_darah_id_unique'
            )
        ) {
            Schema::table(
                'distribusi_darah',
                function (Blueprint $table): void {
                    $table->unique(
                        'permintaan_darah_id',
                        'distribusi_darah_permintaan_darah_id_unique'
                    );
                }
            );
        }

        if (
            ! $this->indexExists(
                table: 'distribusi_darah',
                index: 'distribusi_darah_status_jadwal_idx'
            )
        ) {
            Schema::table(
                'distribusi_darah',
                function (Blueprint $table): void {
                    $table->index(
                        [
                            'status',
                            'dijadwalkan_pada',
                        ],
                        'distribusi_darah_status_jadwal_idx'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        /*
         * Migration penyesuaian tidak menghapus tabel
         * agar riwayat distribusi tetap aman.
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