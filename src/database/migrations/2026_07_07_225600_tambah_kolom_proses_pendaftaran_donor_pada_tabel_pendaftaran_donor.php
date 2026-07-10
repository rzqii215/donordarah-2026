<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pendaftaran_donor')) {
            return;
        }

        Schema::table('pendaftaran_donor', function (Blueprint $table): void {
            if (! Schema::hasColumn('pendaftaran_donor', 'peninjau_id')) {
                $table
                    ->foreignId('peninjau_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'ditinjau_pada')) {
                $table->timestamp('ditinjau_pada')->nullable();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'hadir_pada')) {
                $table->timestamp('hadir_pada')->nullable();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'selesai_pada')) {
                $table->timestamp('selesai_pada')->nullable();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'dibatalkan_pada')) {
                $table->timestamp('dibatalkan_pada')->nullable();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable();
            }

            if (! Schema::hasColumn('pendaftaran_donor', 'alasan_pembatalan')) {
                $table->text('alasan_pembatalan')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pendaftaran_donor')) {
            return;
        }

        Schema::table('pendaftaran_donor', function (Blueprint $table): void {
            if (Schema::hasColumn('pendaftaran_donor', 'peninjau_id')) {
                $table->dropForeign(['peninjau_id']);
                $table->dropColumn('peninjau_id');
            }

            $columns = [
                'ditinjau_pada',
                'hadir_pada',
                'selesai_pada',
                'dibatalkan_pada',
                'alasan_penolakan',
                'alasan_pembatalan',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pendaftaran_donor', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};