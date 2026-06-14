<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_donor', function (Blueprint $table): void {
            $table->id();

            $table->string('nomor_pendaftaran', 40)->unique();

            $table->foreignId('jadwal_donor_id')
                ->constrained('jadwal_donor')
                ->restrictOnDelete();

            $table->foreignId('pendonor_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->json('jawaban_skrining')->nullable();

            $table->string('status', 30)
                ->default('pending')
                ->index();

            $table->foreignId('ditinjau_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('ditinjau_pada')->nullable();

            $table->text('alasan_penolakan')->nullable();

            $table->timestamp('hadir_pada')->nullable();

            $table->timestamp('dibatalkan_pada')->nullable();

            $table->text('alasan_pembatalan')->nullable();

            $table->timestamp('selesai_pada')->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->unique(
                [
                    'jadwal_donor_id',
                    'pendonor_id',
                ],
                'pendaftaran_donor_jadwal_pendonor_unique'
            );

            $table->index(
                [
                    'jadwal_donor_id',
                    'status',
                ],
                'pendaftaran_donor_jadwal_status_idx'
            );

            $table->index(
                [
                    'pendonor_id',
                    'status',
                ],
                'pendaftaran_donor_pendonor_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_donor');
    }
};