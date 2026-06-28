<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Kelas;
use App\Models\HasilKuis;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalKelas = Kelas::count();
        $rataRataKuis = round(HasilKuis::avg('nilai') ?? 0, 1);

        return [
            Stat::make('Total Siswa Aktif', "{$totalSiswa} Siswa")
                ->description('Daftar siswa yang terdaftar dalam praktikum')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Total Kelas Jaringan', "{$totalKelas} Kelas")
                ->description('Kelas praktikum yang dibina')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make('Rata-rata Nilai Kuis', "{$rataRataKuis} / 100")
                ->description('Rata-rata akumulasi nilai evaluasi')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
