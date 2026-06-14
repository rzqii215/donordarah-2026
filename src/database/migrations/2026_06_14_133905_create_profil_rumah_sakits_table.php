<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_rumah_sakit', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pengguna_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('kode_rumah_sakit', 30)->unique();

            $table->string('nama_rumah_sakit');

            $table->string('nomor_izin', 100)->unique();

            $table->string('path_dokumen_izin')->nullable();

            $table->string('nama_penanggung_jawab');

            $table->string('jabatan_penanggung_jawab', 150)
                ->nullable();

            $table->text('alamat');

            $table->string('provinsi', 100);

            $table->string('kota', 100)->index();

            $table->string('kecamatan', 100)->nullable();

            $table->string('kode_pos', 10)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();

            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('status_verifikasi', 30)
                ->default('pending')
                ->index();

            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('diverifikasi_pada')->nullable();

            $table->text('alasan_penolakan')->nullable();

            $table->timestamps();

            $table->index(
                ['latitude', 'longitude'],
                'profil_rumah_sakit_koordinat_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_rumah_sakit');
    }
};