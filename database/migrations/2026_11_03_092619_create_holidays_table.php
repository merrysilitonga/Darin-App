<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            // Satu tanggal hanya boleh memiliki satu entri kalender.
            $table->date('date')->unique();

            // Nama hari libur.
            $table->string('name', 150);

            // Nasional atau Khusus ESDM.
            $table->string('type', 30)->default('Nasional');

            // Keterangan tambahan.
            $table->text('description')->nullable();

            // Hari libur dapat dinonaktifkan tanpa menghapus datanya.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
