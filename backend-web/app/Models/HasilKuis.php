<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilKuis extends Model
{
    use HasFactory;

    protected $table = 'hasil_kuis';

    protected $fillable = [
        'siswa_id',
        'pertemuan_id',
        'nilai',
        'jumlah_benar',
        'total_soal',
        'rekomendasi_ai',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
}
