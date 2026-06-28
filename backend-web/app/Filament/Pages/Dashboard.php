<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        // Menentukan waktu salam (pagi, siang, sore, malam) berdasarkan waktu lokal WIB/GMT+7
        date_default_timezone_set('Asia/Jakarta');
        $hour = date('H');
        
        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat siang';
        } elseif ($hour >= 15 && $hour < 19) {
            $greeting = 'Selamat sore';
        } else {
            $greeting = 'Selamat malam';
        }
        
        $nama = auth()->user()->nama ?? 'Guru';
        return "{$greeting}, {$nama}! 👋";
    }

    public function getSubheading(): ?string
    {
        return 'Berikut adalah ringkasan perkembangan praktikum jaringan komputer dan evaluasi kuis siswa Anda.';
    }
}
