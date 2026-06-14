<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('kantong_darahs')
            && ! Schema::hasTable('kantong_darah')
        ) {
            Schema::rename(
                'kantong_darahs',
                'kantong_darah'
            );
        }

        if (! Schema::hasTable('kantong_darah')) {
            Schema::create(
                'kantong_darah',
                function (Blueprint $table): void {
                    $table->id();

                    $table->string('kode_kantong', 50)
                        ->unique();

                    $table->foreignId(
                        'pendaftaran_donor_id'
                    )
                        ->unique()
                        ->constrained('pendaftaran_donor')
                        ->restrictOnDelete();

                    $table->string('golongan_darah', 3);

                    $table->string('rhesus', 10);

                    $table->string(
                        'jenis_komponen',
                        30
                    )->default('whole_blood');

                    $table->unsignedSmallInteger(
                        'volume_ml'
                    );

                    $table->dateTime(
                        'diambil_pada'
                    )->index();

                    $table->dateTime(
                        'kedaluwarsa_pada'
                    )->index();

                    $table->string('status_mutu', 30)
                        ->default('pending')
                        ->index();

                    $table->string('status', 30)
                        ->default('pending')
                        ->index();

                    $table->string(
                        'lokasi_penyimpanan'
                    )->nullable();

                    $table->foreignId(
                        'diverifikasi_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->timestamp(
                        'diverifikasi_pada'
                    )->nullable();

                    $table->text(
                        'alasan_penolakan'
                    )->nullable();

                    $table->timestamp(
                        'didistribusikan_pada'
                    )->nullable();

                    $table->text('catatan')->nullable();

                    $table->timestamps();

                    $table->softDeletes();

                    $table->index(
                        [
                            'golongan_darah',
                            'rhesus',
                            'status',
                        ],
                        'kantong_darah_golongan_rhesus_status_idx'
                    );

                    $table->index(
                        [
                            'status',
                            'kedaluwarsa_pada',
                        ],
                        'kantong_darah_status_kedaluwarsa_idx'
                    );
                }
            );

            return;
        }

        Schema::table(
            'kantong_darah',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'kode_kantong'
                    )
                ) {
                    $table->string(
                        'kode_kantong',
                        50
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'pendaftaran_donor_id'
                    )
                ) {
                    $table->foreignId(
                        'pendaftaran_donor_id'
                    )
                        ->nullable()
                        ->constrained('pendaftaran_donor')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'golongan_darah'
                    )
                ) {
                    $table->string(
                        'golongan_darah',
                        3
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'rhesus'
                    )
                ) {
                    $table->string(
                        'rhesus',
                        10
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'jenis_komponen'
                    )
                ) {
                    $table->string(
                        'jenis_komponen',
                        30
                    )
                        ->default('whole_blood');
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'volume_ml'
                    )
                ) {
                    $table->unsignedSmallInteger(
                        'volume_ml'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'diambil_pada'
                    )
                ) {
                    $table->dateTime(
                        'diambil_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'kedaluwarsa_pada'
                    )
                ) {
                    $table->dateTime(
                        'kedaluwarsa_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'status_mutu'
                    )
                ) {
                    $table->string(
                        'status_mutu',
                        30
                    )
                        ->default('pending')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'status'
                    )
                ) {
                    $table->string('status', 30)
                        ->default('pending')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'lokasi_penyimpanan'
                    )
                ) {
                    $table->string(
                        'lokasi_penyimpanan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'diverifikasi_oleh'
                    )
                ) {
                    $table->foreignId(
                        'diverifikasi_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'diverifikasi_pada'
                    )
                ) {
                    $table->timestamp(
                        'diverifikasi_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'alasan_penolakan'
                    )
                ) {
                    $table->text(
                        'alasan_penolakan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'didistribusikan_pada'
                    )
                ) {
                    $table->timestamp(
                        'didistribusikan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'catatan'
                    )
                ) {
                    $table->text('catatan')->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'kantong_darah',
                        'deleted_at'
                    )
                ) {
                    $table->softDeletes();
                }
            }
        );
    }

    public function down(): void
    {
        /*
         * Migration normalisasi tidak menghapus kolom
         * agar data kantong darah tidak hilang.
         */
    }
};