<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('topik_materi', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('judul');
            $table->integer('urutan')->default(0)->after('isi_materi');
            $table->string('file_materi', 255)->nullable()->after('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topik_materi', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'urutan', 'file_materi']);
        });
    }
};
