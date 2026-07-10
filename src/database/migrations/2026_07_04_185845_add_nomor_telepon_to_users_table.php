<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'nomor_telepon')) {
            Schema::table('users', function (Blueprint $table): void {
                $table
                    ->string('nomor_telepon', 30)
                    ->nullable()
                    ->after('email');
            });
        }
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'nomor_telepon')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('nomor_telepon');
            });
        }
    }
};