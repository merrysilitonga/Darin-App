<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable();
        });

        // Catatan lama yang belum pernah direview dianggap sebagai draft.
        DB::table('logbooks')
            ->where('status', 'Menunggu')
            ->whereNull('reviewed_at')
            ->update([
                'status' => 'Draft',
            ]);
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};