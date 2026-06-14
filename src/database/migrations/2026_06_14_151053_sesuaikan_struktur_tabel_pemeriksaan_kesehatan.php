<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pemeriksaan_kesehatan')) {
            Schema::create(
                'pemeriksaan_kesehatan',
                function (Blueprint $table): void {
                    $table->id();

                    $table->foreignId('pendaftaran_donor_id')
                        ->unique()
                        ->constrained('pendaftaran_donor')
                        ->restrictOnDelete();

                    $table->foreignId('diperiksa_oleh')
                        ->constrained('users')
                        ->restrictOnDelete();

                    $table->decimal('berat_badan_kg', 5, 2);

                    $table->unsignedSmallInteger(
                        'tekanan_sistolik'
                    );

                    $table->unsignedSmallInteger(
                        'tekanan_diastolik'
                    );

                    $table->decimal(
                        'kadar_hemoglobin',
                        4,
                        2
                    )->nullable();

                    $table->decimal(
                        'suhu_tubuh',
                        4,
                        2
                    )->nullable();

                    $table->unsignedSmallInteger(
                        'denyut_nadi'
                    )->nullable();

                    $table->string(
                        'golongan_darah',
                        3
                    )->nullable();

                    $table->string(
                        'rhesus',
                        10
                    )->nullable();

                    $table->string(
                        'status_kelayakan',
                        30
                    )->index();

                    $table->text(
                        'alasan_tidak_layak'
                    )->nullable();

                    $table->text(
                        'catatan_medis'
                    )->nullable();

                    $table->dateTime(
                        'diperiksa_pada'
                    )->index();

                    $table->timestamps();
                }
            );

            return;
        }

        Schema::table(
            'pemeriksaan_kesehatan',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'pendaftaran_donor_id'
                    )
                ) {
                    $table->foreignId(
                        'pendaftaran_donor_id'
                    )
                        ->nullable()
                        ->unique()
                        ->constrained(
                            'pendaftaran_donor'
                        )
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'diperiksa_oleh'
                    )
                ) {
                    $table->foreignId(
                        'diperiksa_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'berat_badan_kg'
                    )
                ) {
                    $table->decimal(
                        'berat_badan_kg',
                        5,
                        2
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'tekanan_sistolik'
                    )
                ) {
                    $table->unsignedSmallInteger(
                        'tekanan_sistolik'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'tekanan_diastolik'
                    )
                ) {
                    $table->unsignedSmallInteger(
                        'tekanan_diastolik'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'kadar_hemoglobin'
                    )
                ) {
                    $table->decimal(
                        'kadar_hemoglobin',
                        4,
                        2
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'suhu_tubuh'
                    )
                ) {
                    $table->decimal(
                        'suhu_tubuh',
                        4,
                        2
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'denyut_nadi'
                    )
                ) {
                    $table->unsignedSmallInteger(
                        'denyut_nadi'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
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
                        'pemeriksaan_kesehatan',
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
                        'pemeriksaan_kesehatan',
                        'status_kelayakan'
                    )
                ) {
                    $table->string(
                        'status_kelayakan',
                        30
                    )
                        ->nullable()
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'alasan_tidak_layak'
                    )
                ) {
                    $table->text(
                        'alasan_tidak_layak'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'catatan_medis'
                    )
                ) {
                    $table->text(
                        'catatan_medis'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'diperiksa_pada'
                    )
                ) {
                    $table->dateTime(
                        'diperiksa_pada'
                    )
                        ->nullable()
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'created_at'
                    )
                ) {
                    $table->timestamp(
                        'created_at'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'pemeriksaan_kesehatan',
                        'updated_at'
                    )
                ) {
                    $table->timestamp(
                        'updated_at'
                    )->nullable();
                }
            }
        );
    }

    public function down(): void
    {
        /*
         * Migration ini tidak menghapus kolom ketika rollback
         * agar data pemeriksaan kesehatan tidak hilang.
         */
    }
};