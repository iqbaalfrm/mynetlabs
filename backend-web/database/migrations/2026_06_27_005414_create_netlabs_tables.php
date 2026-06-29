<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Users (Guru & Siswa)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique(); // NIS untuk siswa atau NIP/ID untuk guru
            $table->string('password');
            $table->string('nama', 100);
            $table->enum('role', ['guru', 'siswa']);
            $table->string('kelas', 20)->nullable(); // Khusus siswa
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel Pertemuan
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_urut');
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->enum('semester', ['1', '2']);
            $table->string('warna_tema', 7)->default('#3B82F6');
            $table->timestamps();
        });

        // 3. Tabel Topik Materi
        Schema::create('topik_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->onDelete('cascade');
            $table->string('judul', 150);
            $table->text('isi_materi');
            $table->timestamps();
        });

        // 4. Tabel Progress Belajar Siswa
        Schema::create('progress_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('topik_id')->constrained('topik_materi')->onDelete('cascade');
            $table->boolean('is_completed')->default(true);
            $table->timestamps();
            $table->unique(['siswa_id', 'topik_id']);
        });

        // 5. Tabel Modul PDF (Tracking Indexing RAG)
        Schema::create('modul_pdf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->onDelete('cascade');
            $table->string('file_name', 255);
            $table->enum('status_indexing', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });

        // 6. Tabel Soal Kuis
        Schema::create('soal_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->onDelete('cascade');
            $table->text('pertanyaan');
            $table->text('pilihan_a');
            $table->text('pilihan_b');
            $table->text('pilihan_c');
            $table->text('pilihan_d');
            $table->enum('kunci_jawaban', ['A', 'B', 'C', 'D']);
            $table->text('penjelasan')->nullable();
            $table->timestamps();
        });

        // 7. Tabel Hasil Kuis
        Schema::create('hasil_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->onDelete('cascade');
            $table->decimal('nilai', 5, 2);
            $table->integer('jumlah_benar');
            $table->integer('total_soal');
            $table->text('rekomendasi_ai')->nullable();
            $table->timestamps();
        });

        // 8. Tabel Riwayat Chat AI Tutor
        Schema::create('chat_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->onDelete('cascade');
            $table->enum('sender', ['siswa', 'ai']);
            $table->text('pesan');
            $table->string('sumber_referensi', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_history');
        Schema::dropIfExists('hasil_kuis');
        Schema::dropIfExists('soal_kuis');
        Schema::dropIfExists('modul_pdf');
        Schema::dropIfExists('progress_siswa');
        Schema::dropIfExists('topik_materi');
        Schema::dropIfExists('pertemuan');
        Schema::dropIfExists('users');
    }
};