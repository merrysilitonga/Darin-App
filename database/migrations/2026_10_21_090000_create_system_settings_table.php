<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('clock_in_start', 5)->default('08:00');
            $table->string('clock_in_end', 5)->default('17:00');

            $table->string('friday_clock_in_end', 5)->default('17:30');

            $table->string('clock_out_time', 5)->default('17:00');
            $table->string('friday_clock_out_time', 5)->default('17:30');

            $table->timestamps();
        });

        // Konfigurasi global DARIN menggunakan ID tetap 1.
        DB::table('system_settings')->insertOrIgnore([
            'id' => 1,
            'clock_in_start' => '08:00',
            'clock_in_end' => '17:00',
            'friday_clock_in_end' => '17:30',
            'clock_out_time' => '17:00',
            'friday_clock_out_time' => '17:30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
