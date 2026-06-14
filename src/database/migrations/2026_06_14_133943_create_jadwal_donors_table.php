<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_donor', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('lokasi_donor_id')
                ->constrained('lokasi_donor')
                ->restrictOnDelete();

            $table->string('kode_jadwal', 30)->unique();

            $table->string('judul');

            $table->string('slug')->unique();

            $table->text('deskripsi')->nullable();

            $table->dateTime('mulai_pada')->index();

            $table->dateTime('selesai_pada');

            $table->dateTime('pendaftaran_dibuka_pada')->index();

            $table->dateTime('pendaftaran_ditutup_pada')->index();

            $table->unsignedInteger('kuota');

            $table->string('status', 30)
                ->default('draft')
                ->index();

            $table->string('path_banner')->nullable();

            $table->foreignId('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamp('dipublikasikan_pada')->nullable();

            $table->timestamp('dibatalkan_pada')->nullable();

            $table->text('alasan_pembatalan')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index(
                ['lokasi_donor_id', 'status'],
                'jadwal_donor_lokasi_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_donor');
    }
};