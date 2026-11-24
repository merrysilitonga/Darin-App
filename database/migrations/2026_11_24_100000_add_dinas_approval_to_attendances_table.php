<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persetujuan Dinas Luar oleh Mentor.
 * dinas_status: null (bukan dinas) | Menunggu | Disetujui | Ditolak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('dinas_status', 20)->nullable()->after('work_location');
            $table->foreignId('dinas_reviewed_by')->nullable()->after('dinas_status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('dinas_reviewed_at')->nullable()->after('dinas_reviewed_by');
            $table->text('dinas_review_notes')->nullable()->after('dinas_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dinas_reviewed_by');
            $table->dropColumn(['dinas_status', 'dinas_reviewed_at', 'dinas_review_notes']);
        });
    }
};
