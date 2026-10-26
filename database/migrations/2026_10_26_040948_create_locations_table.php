<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150)->unique();
            $table->string('address')->nullable();

            // Koordinat titik lokasi magang.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Jarak maksimum absensi dari titik lokasi dalam meter.
            $table->unsignedInteger('radius_meters')->default(100);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
