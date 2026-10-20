<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // Data akun
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role pengguna
            // magang = mahasiswa
            // mentor = pembimbing
            // admin = HR / administrator
            $table->string('role')->default('magang');

            // Divisi
            $table->unsignedBigInteger('division_id')->nullable();

            // Data mahasiswa / peserta magang
            $table->string('nim')->nullable();
            $table->string('phone')->nullable();
            $table->string('university')->nullable();
            $table->string('study_program')->nullable();

            // Foto profil
            $table->string('photo')->nullable();

            // Data pegawai / mentor / admin
            $table->string('nip')->nullable();
            $table->string('position')->nullable();

            // Periode magang
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });


        // Token reset password
        Schema::create('password_reset_tokens', function (Blueprint $table) {

            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

        });


        // Session Laravel
        Schema::create('sessions', function (Blueprint $table) {

            $table->string('id')->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

        });
    }


    /**
     * Membatalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};