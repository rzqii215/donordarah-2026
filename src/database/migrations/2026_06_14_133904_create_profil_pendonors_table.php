<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_pendonor', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pengguna_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('kode_pendonor', 30)->unique();

            $table->date('tanggal_lahir');

            $table->string('jenis_kelamin', 20);

            $table->string('golongan_darah', 3)->nullable();

            $table->string('rhesus', 10)->nullable();

            $table->text('alamat');

            $table->string('provinsi', 100);

            $table->string('kota', 100)->index();

            $table->string('kecamatan', 100)->nullable();

            $table->string('kode_pos', 10)->nullable();

            $table->string('nama_kontak_darurat')->nullable();

            $table->string('telepon_kontak_darurat', 30)->nullable();

            $table->dateTime('terakhir_donor_pada')->nullable();

            $table->boolean('bersedia_dihubungi')
                ->default(true)
                ->index();

            $table->timestamps();

            $table->index(
                ['golongan_darah', 'rhesus'],
                'profil_pendonor_golongan_rhesus_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_pendonor');
    }
};