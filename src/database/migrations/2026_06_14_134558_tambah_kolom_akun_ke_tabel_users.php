<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nomor_telepon', 30)
                ->nullable()
                ->after('email')
                ->index();

            $table->string('status', 30)
                ->default('active')
                ->after('password')
                ->index();

            $table->timestamp('terakhir_login_pada')
                ->nullable()
                ->after('status');

            $table->string('ip_terakhir_login', 45)
                ->nullable()
                ->after('terakhir_login_pada');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['nomor_telepon']);
            $table->dropIndex(['status']);

            $table->dropColumn([
                'nomor_telepon',
                'status',
                'terakhir_login_pada',
                'ip_terakhir_login',
            ]);
        });
    }
};