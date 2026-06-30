<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopikMateri extends Model
{
    protected $table = 'topik_materi'; // Sesuai nama tabel di database

    protected $fillable = [
        'pertemuan_id',
        'judul',
        'isi_materi',
        'deskripsi',
        'urutan',
        'file_materi',
    ];

    // Relasi balik: Satu topik materi dimiliki oleh satu pertemuan
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
}