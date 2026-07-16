<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pertemuan extends Model
{
    // Mengarahkan ke nama tabel MySQL yang kita buat di migration
    protected $table = 'pertemuan'; 

    protected $fillable = [
        'nomor_urut',
        'judul',
        'deskripsi',
        'isi_materi',
        'semester',
        'warna_tema'
    ];

    public function topikMateris()
    {
        return $this->hasMany(TopikMateri::class, 'pertemuan_id');
    }

    public function modulPdfs()
    {
        return $this->hasMany(ModulPdf::class, 'pertemuan_id');
    }

    public function soalKuis()
    {
        return $this->hasMany(SoalKuis::class, 'pertemuan_id');
    }

    public function hasilKuis()
    {
        return $this->hasMany(HasilKuis::class, 'pertemuan_id');
    }
}