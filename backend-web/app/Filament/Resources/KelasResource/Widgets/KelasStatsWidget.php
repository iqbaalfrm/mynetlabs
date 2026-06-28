<?php

namespace App\Filament\Resources\KelasResource\Widgets;

use App\Models\Kelas;
use App\Models\HasilKuis;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class KelasStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $totalSiswa = User::where('kelas_id', $this->record->id)->count();

        $rataRataKelas = HasilKuis::whereHas('siswa', function ($query) {
            $query->where('kelas_id', $this->record->id);
        })->avg('nilai') ?? 0;

        $totalKuis = HasilKuis::whereHas('siswa', function ($query) {
            $query->where('kelas_id', $this->record->id);
        })->count();

        return [
            Stat::make('Jumlah Siswa', $totalSiswa . ' Orang')
                ->description('Total siswa terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Rata-rata Kelas', number_format($rataRataKelas, 1) . ' / 100')
                ->description('Rata-rata nilai seluruh siswa')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($rataRataKelas >= 75 ? 'success' : 'warning'),
            Stat::make('Kuis Diselesaikan', $totalKuis . ' Kali')
                ->description('Total percobaan kuis siswa')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
        ];
    }
}
