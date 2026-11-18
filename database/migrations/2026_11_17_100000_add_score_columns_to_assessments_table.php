<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel assessments awalnya hanya berisi id & timestamps.
 * Migration ini menambahkan kolom nilai yang dipakai MentorAssessmentController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'user_id')) {
                // Satu mahasiswa hanya punya satu penilaian akhir.
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('assessments', 'mentor_id')) {
                $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            }

            foreach (['kedisiplinan', 'kinerja_teknis', 'sikap_etika', 'laporan'] as $kolom) {
                if (!Schema::hasColumn('assessments', $kolom)) {
                    $table->decimal($kolom, 5, 2)->default(0);
                }
            }

            if (!Schema::hasColumn('assessments', 'tanggal_penilaian')) {
                $table->timestamp('tanggal_penilaian')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('mentor_id');
            $table->dropColumn(['kedisiplinan', 'kinerja_teknis', 'sikap_etika', 'laporan', 'tanggal_penilaian']);
        });
    }
};
