<?php

namespace App\Filament\Widgets;

use App\Models\HasilKuis;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class HasilKuisTerbaruTable extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Ujian/Kuis Terbaru';

    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HasilKuis::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('siswa.nama')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pertemuan.judul')
                    ->label('Modul Jaringan'),
                TextColumn::make('nilai')
                    ->label('Nilai')
                    ->badge()
                    ->color(fn (string $state): string => floatval($state) >= 70 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
