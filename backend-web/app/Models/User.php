<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Pastikan ini di-import

class User extends Authenticatable implements HasName
{
    use HasApiTokens, HasFactory, Notifiable;

    // Definisikan kolom yang boleh diisi massal
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

    // Sembunyikan password saat data user di-return dalam bentuk JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Pastikan Laravel tahu password di-hash secara otomatis
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Beritahu Filament untuk menggunakan kolom 'username' (NIP/NIS) saat login.
     */
    public function getFilamentName(): string
    {
        return $this->nama;
    }

    // Tambahkan properti ini agar login auth mencocokkan field username, bukan email
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
}