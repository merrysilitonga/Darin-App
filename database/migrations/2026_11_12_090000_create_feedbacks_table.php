<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            // Peserta yang mengirim saran/masukan.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Saran, Kendala Aplikasi, Lainnya.
            $table->string('category', 30);

            $table->string('subject', 150);
            $table->text('message');

            // Baru, Diproses, Selesai.
            $table->string('status', 20)->default('Baru');

            // Tanggapan Admin/HR.
            $table->text('admin_reply')->nullable();

            $table->foreignId('replied_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
