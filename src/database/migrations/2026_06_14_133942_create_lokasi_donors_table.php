<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_donor', function (Blueprint $table): void {
            $table->id();

            $table->string('nama');

            $table->string('slug')->unique();

            $table->text('alamat');

            $table->string('provinsi', 100);

            $table->string('kota', 100)->index();

            $table->string('kecamatan', 100)->nullable();

            $table->string('kode_pos', 10)->nullable();

            $table->decimal('latitude', 10, 7);

            $table->decimal('longitude', 10, 7);

            $table->string('nama_kontak')->nullable();

            $table->string('nomor_kontak', 30)->nullable();

            $table->text('deskripsi')->nullable();

            $table->boolean('aktif')
                ->default(true)
                ->index();

            $table->foreignId('dibuat_oleh')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            $table->softDeletes();

            $table->index(
                ['latitude', 'longitude'],
                'lokasi_donor_koordinat_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_donor');
    }
};