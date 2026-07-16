<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom isi_materi langsung di tabel pertemuan
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->text('isi_materi')->nullable()->after('deskripsi');
        });

        // Tambah kolom pertemuan_id di progress_siswa, jadikan topik_id nullable
        Schema::table('progress_siswa', function (Blueprint $table) {
            $table->foreignId('pertemuan_id')->nullable()->after('siswa_id')->constrained('pertemuan')->onDelete('cascade');
            $table->unsignedBigInteger('topik_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->dropColumn('isi_materi');
        });

        Schema::table('progress_siswa', function (Blueprint $table) {
            $table->dropForeign(['pertemuan_id']);
            $table->dropColumn('pertemuan_id');
        });
    }
};
