<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
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

        if (Schema::hasColumn($tableName, 'latitude')) {
            DB::statement(
                "ALTER TABLE `{$tableName}` MODIFY `latitude` DECIMAL(10,7) NULL"
            );
        }

        if (Schema::hasColumn($tableName, 'longitude')) {
            DB::statement(
                "ALTER TABLE `{$tableName}` MODIFY `longitude` DECIMAL(10,7) NULL"
            );
        }
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if ($tableName === null) {
            return;
        }

        if (Schema::hasColumn($tableName, 'latitude')) {
            DB::table($tableName)
                ->whereNull('latitude')
                ->update([
                    'latitude' => 0,
                ]);

            DB::statement(
                "ALTER TABLE `{$tableName}` MODIFY `latitude` DECIMAL(10,7) NOT NULL"
            );
        }

        if (Schema::hasColumn($tableName, 'longitude')) {
            DB::table($tableName)
                ->whereNull('longitude')
                ->update([
                    'longitude' => 0,
                ]);

            DB::statement(
                "ALTER TABLE `{$tableName}` MODIFY `longitude` DECIMAL(10,7) NOT NULL"
            );
        }
    }
};