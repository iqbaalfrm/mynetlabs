<?php

namespace App\Filament\Widgets;

use App\Models\Pertemuan;
use App\Models\HasilKuis;
use Filament\Widgets\ChartWidget;

class NilaiKuisChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Nilai Siswa Per Modul';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $pertemuans = Pertemuan::orderBy('nomor_urut')->get();
        $labels = [];
        $data = [];

        foreach ($pertemuans as $pertemuan) {
            $labels[] = "Modul " . $pertemuan->nomor_urut;
            $data[] = round(HasilKuis::where('pertemuan_id', $pertemuan->id)->avg('nilai') ?? 0, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Nilai',
                    'data' => $data,
                    'backgroundColor' => '#4F46E5', // Warna Indigo brand NetLabs
                    'borderColor' => '#4F46E5',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
