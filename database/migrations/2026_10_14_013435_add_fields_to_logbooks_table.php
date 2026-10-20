<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('date')->nullable();
            $table->string('topic')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default('Menunggu');
            $table->text('mentor_feedback')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            // Satu mahasiswa hanya boleh memiliki satu catatan per tanggal.
            $table->unique(
                ['user_id', 'date'],
                'logbooks_user_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropUnique('logbooks_user_date_unique');

            $table->dropForeign(['user_id']);
            $table->dropForeign(['reviewed_by']);

            $table->dropColumn([
                'user_id',
                'date',
                'topic',
                'description',
                'status',
                'mentor_feedback',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};