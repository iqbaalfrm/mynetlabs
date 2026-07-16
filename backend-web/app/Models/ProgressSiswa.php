<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressSiswa extends Model
{
    use HasFactory;

    protected $table = 'progress_siswa';

    protected $fillable = [
        'siswa_id',
        'pertemuan_id',
        'topik_id',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function topik()
    {
        return $this->belongsTo(TopikMateri::class, 'topik_id');
    }
}
