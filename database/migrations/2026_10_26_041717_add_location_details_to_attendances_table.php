<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Snapshot lokasi untuk absensi masuk.
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->string('location_name')->nullable();
            $table->unsignedInteger('distance_meters')->nullable();

            // Bukti lokasi dan foto terpisah untuk absensi pulang.
            $table->foreignId('clock_out_location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->string('clock_out_location_name')->nullable();
            $table->string('clock_out_photo_path')->nullable();

            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();

            $table->unsignedInteger('clock_out_distance_meters')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('clock_out_location_id');

            $table->dropColumn([
                'location_name',
                'distance_meters',
                'clock_out_location_name',
                'clock_out_photo_path',
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_out_distance_meters',
            ]);
        });
    }
};
