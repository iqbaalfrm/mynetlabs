<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'nama',
        'role',
        'kelas',
        'kelas_id',
        'foto_profil',
    ];

    protected $appends = ['foto_profil_url'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function kelasRelation()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->kelas_id) {
                $kelas = Kelas::find($user->kelas_id);
                if ($kelas) {
                    $user->kelas = $kelas->nama_kelas;
                }
            }
        });
    }

    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            return url('storage/' . $this->foto_profil);
        }
        return null;
    }

    public function hasilKuis()
    {
        return $this->hasMany(HasilKuis::class, 'siswa_id');
    }

    public function progressSiswa()
    {
        return $this->hasMany(ProgressSiswa::class, 'siswa_id');
    }
}
