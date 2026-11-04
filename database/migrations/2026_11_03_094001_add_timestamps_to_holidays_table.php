<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan created_at hanya jika belum tersedia.
        if (!Schema::hasColumn('holidays', 'created_at')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        // Tambahkan updated_at hanya jika belum tersedia.
        if (!Schema::hasColumn('holidays', 'updated_at')) {
            Schema::table('holidays', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Tidak menghapus timestamps karena mungkin sudah
        // tersedia sebelum migration ini dijalankan.
    }
};
