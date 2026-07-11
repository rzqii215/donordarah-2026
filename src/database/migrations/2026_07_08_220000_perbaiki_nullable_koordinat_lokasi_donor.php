<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = $this->tableName();

        if ($tableName === null) {
            return;
        }

        $latitudeAda = Schema::hasColumn(
            $tableName,
            'latitude'
        );

        $longitudeAda = Schema::hasColumn(
            $tableName,
            'longitude'
        );

        if (! $latitudeAda && ! $longitudeAda) {
            return;
        }

        Schema::table(
            $tableName,
            function (Blueprint $table) use (
                $latitudeAda,
                $longitudeAda
            ): void {
                if ($latitudeAda) {
                    $table->decimal(
                        'latitude',
                        10,
                        7
                    )
                        ->nullable()
                        ->change();
                }

                if ($longitudeAda) {
                    $table->decimal(
                        'longitude',
                        10,
                        7
                    )
                        ->nullable()
                        ->change();
                }
            }
        );
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if ($tableName === null) {
            return;
        }

        $latitudeAda = Schema::hasColumn(
            $tableName,
            'latitude'
        );

        $longitudeAda = Schema::hasColumn(
            $tableName,
            'longitude'
        );

        if (! $latitudeAda && ! $longitudeAda) {
            return;
        }

        if ($latitudeAda) {
            DB::table($tableName)
                ->whereNull('latitude')
                ->update([
                    'latitude' => 0,
                ]);
        }

        if ($longitudeAda) {
            DB::table($tableName)
                ->whereNull('longitude')
                ->update([
                    'longitude' => 0,
                ]);
        }

        Schema::table(
            $tableName,
            function (Blueprint $table) use (
                $latitudeAda,
                $longitudeAda
            ): void {
                if ($latitudeAda) {
                    $table->decimal(
                        'latitude',
                        10,
                        7
                    )
                        ->nullable(false)
                        ->change();
                }

                if ($longitudeAda) {
                    $table->decimal(
                        'longitude',
                        10,
                        7
                    )
                        ->nullable(false)
                        ->change();
                }
            }
        );
    }

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
};