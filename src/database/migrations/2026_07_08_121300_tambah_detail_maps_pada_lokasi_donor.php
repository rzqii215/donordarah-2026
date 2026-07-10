<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tableName(): ?string
    {
        if (Schema::hasTable('lokasi_donor')) {
            return 'lokasi_donor';
        }

        if (Schema::hasTable('lokasi_donors')) {
            return 'lokasi_donors';
        }

        return null;
    }

    public function up(): void
    {
        $tableName = $this->tableName();

        if ($tableName === null) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'nomor_telepon')) {
                $table->string('nomor_telepon', 30)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }

            if (! Schema::hasColumn($tableName, 'url_google_maps')) {
                $table->text('url_google_maps')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'catatan_lokasi')) {
                $table->text('catatan_lokasi')->nullable();
            }
        });
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if ($tableName === null) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            $columns = [
                'nomor_telepon',
                'latitude',
                'longitude',
                'url_google_maps',
                'catatan_lokasi',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};