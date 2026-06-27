<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulPdf extends Model
{
    use HasFactory;

    protected $table = 'modul_pdf';

    protected $fillable = [
        'pertemuan_id',
        'file_name',
        'status_indexing',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
}
