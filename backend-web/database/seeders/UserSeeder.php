<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Dummy GURU (Menggunakan NIP/ID Bebas)
        User::updateOrCreate(
            ['username' => '19950812001'], // Kunci unik cek agar tidak duplikat
            [
                'nama' => 'Pak Budi Hartono, S.Kom.',
                'password' => Hash::make('guru123'), // Password akun guru
                'role' => 'guru',
                'kelas' => null,
            ]
        );

        // 2. Akun Dummy SISWA (Menggunakan Nama & NIS Kamu)
        User::updateOrCreate(
            ['username' => '22041001'], // Username bertindak sebagai NIS Siswa
            [
                'nama' => 'Moch Iqbal Firmansyah',
                'password' => Hash::make('siswa123'), // Password untuk test login di Flutter
                'role' => 'siswa',
                'kelas' => 'XI TKJ 1',
            ]
        );
    }
}