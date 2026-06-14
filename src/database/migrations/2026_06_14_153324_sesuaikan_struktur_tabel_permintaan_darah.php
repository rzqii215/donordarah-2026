<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('permintaan_darahs')
            && ! Schema::hasTable('permintaan_darah')
        ) {
            Schema::rename(
                'permintaan_darahs',
                'permintaan_darah'
            );
        }

        if (! Schema::hasTable('permintaan_darah')) {
            Schema::create(
                'permintaan_darah',
                function (Blueprint $table): void {
                    $table->id();

                    $table->string('nomor_permintaan', 40)
                        ->unique();

                    $table->foreignId(
                        'profil_rumah_sakit_id'
                    )
                        ->constrained('profil_rumah_sakit')
                        ->restrictOnDelete();

                    $table->string(
                        'referensi_pasien',
                        100
                    );

                    $table->string('nama_dokter');

                    $table->string(
                        'golongan_darah',
                        3
                    );

                    $table->string('rhesus', 10);

                    $table->unsignedSmallInteger(
                        'jumlah_kantong'
                    );

                    $table->string(
                        'tingkat_urgensi',
                        30
                    )->default('normal');

                    $table->dateTime(
                        'dibutuhkan_pada'
                    )->index();

                    $table->string(
                        'path_dokumen_permintaan'
                    )->nullable();

                    $table->string('status', 30)
                        ->default('submitted')
                        ->index();

                    $table->foreignId('ditinjau_oleh')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->timestamp(
                        'ditinjau_pada'
                    )->nullable();

                    $table->timestamp(
                        'disetujui_pada'
                    )->nullable();

                    $table->timestamp(
                        'siap_diambil_pada'
                    )->nullable();

                    $table->timestamp(
                        'selesai_pada'
                    )->nullable();

                    $table->timestamp(
                        'dibatalkan_pada'
                    )->nullable();

                    $table->text(
                        'alasan_penolakan'
                    )->nullable();

                    $table->text(
                        'alasan_pembatalan'
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
                        'permintaan_darah_golongan_rhesus_status_idx'
                    );

                    $table->index(
                        [
                            'profil_rumah_sakit_id',
                            'status',
                        ],
                        'permintaan_darah_rumah_sakit_status_idx'
                    );

                    $table->index(
                        [
                            'tingkat_urgensi',
                            'status',
                        ],
                        'permintaan_darah_urgensi_status_idx'
                    );
                }
            );

            return;
        }

        Schema::table(
            'permintaan_darah',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'nomor_permintaan'
                    )
                ) {
                    $table->string(
                        'nomor_permintaan',
                        40
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'profil_rumah_sakit_id'
                    )
                ) {
                    $table->foreignId(
                        'profil_rumah_sakit_id'
                    )
                        ->nullable()
                        ->constrained(
                            'profil_rumah_sakit'
                        )
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'referensi_pasien'
                    )
                ) {
                    $table->string(
                        'referensi_pasien',
                        100
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'nama_dokter'
                    )
                ) {
                    $table->string(
                        'nama_dokter'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
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
                        'permintaan_darah',
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
                        'permintaan_darah',
                        'jumlah_kantong'
                    )
                ) {
                    $table->unsignedSmallInteger(
                        'jumlah_kantong'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'tingkat_urgensi'
                    )
                ) {
                    $table->string(
                        'tingkat_urgensi',
                        30
                    )->default('normal');
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'dibutuhkan_pada'
                    )
                ) {
                    $table->dateTime(
                        'dibutuhkan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'path_dokumen_permintaan'
                    )
                ) {
                    $table->string(
                        'path_dokumen_permintaan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'status'
                    )
                ) {
                    $table->string('status', 30)
                        ->default('submitted')
                        ->index();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'ditinjau_oleh'
                    )
                ) {
                    $table->foreignId(
                        'ditinjau_oleh'
                    )
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'ditinjau_pada'
                    )
                ) {
                    $table->timestamp(
                        'ditinjau_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'disetujui_pada'
                    )
                ) {
                    $table->timestamp(
                        'disetujui_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'siap_diambil_pada'
                    )
                ) {
                    $table->timestamp(
                        'siap_diambil_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'selesai_pada'
                    )
                ) {
                    $table->timestamp(
                        'selesai_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'dibatalkan_pada'
                    )
                ) {
                    $table->timestamp(
                        'dibatalkan_pada'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'alasan_penolakan'
                    )
                ) {
                    $table->text(
                        'alasan_penolakan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'alasan_pembatalan'
                    )
                ) {
                    $table->text(
                        'alasan_pembatalan'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'catatan'
                    )
                ) {
                    $table->text('catatan')->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'created_at'
                    )
                ) {
                    $table->timestamp(
                        'created_at'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
                        'updated_at'
                    )
                ) {
                    $table->timestamp(
                        'updated_at'
                    )->nullable();
                }

                if (
                    ! Schema::hasColumn(
                        'permintaan_darah',
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
         * agar data permintaan darah tidak hilang.
         */
    }
};