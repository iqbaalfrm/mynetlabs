<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HasilKuisResource\Pages;
use App\Models\HasilKuis;
use App\Models\Kelas;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions;

class HasilKuisResource extends Resource
{
    protected static ?string $model = HasilKuis::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Manajemen Nilai';

    protected static ?int $navigationSort = 4;

    protected static ?string $pluralModelLabel = 'Nilai Siswa';

    public static function form(Schema $schema): Schema
    {
        // View page schema (read-only form)
        return $schema
            ->components([
                Forms\Components\TextInput::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->disabled(),
                Forms\Components\TextInput::make('siswa.kelas')
                    ->label('Kelas')
                    ->disabled(),
                Forms\Components\TextInput::make('pertemuan.judul')
                    ->label('Pertemuan / Bab')
                    ->disabled(),
                Forms\Components\TextInput::make('nilai')
                    ->label('Nilai Kuis')
                    ->disabled(),
                Forms\Components\TextInput::make('jumlah_benar')
                    ->label('Jawaban Benar')
                    ->disabled(),
                Forms\Components\TextInput::make('total_soal')
                    ->label('Total Soal')
                    ->disabled(),
                Forms\Components\Textarea::make('rekomendasi_ai')
                    ->label('Rekomendasi Belajar dari AI')
                    ->disabled()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('siswa.nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('siswa.kelas')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pertemuan.judul')
                    ->label('Judul Pertemuan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nilai')
                    ->label('Nilai Kuis')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => floatval($state) >= 70 ? 'success' : 'danger'),
                TextColumn::make('jumlah_benar')
                    ->label('Benar')
                    ->formatStateUsing(fn ($record) => "{$record->jumlah_benar} dari {$record->total_soal}"),
                TextColumn::make('rekomendasi_ai')
                    ->label('Rekomendasi AI')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->rekomendasi_ai),
            ])
            ->filters([
                SelectFilter::make('kelas')
                    ->label('Filter Kelas')
                    ->relationship('siswa.kelasRelation', 'nama_kelas'),
                SelectFilter::make('pertemuan_id')
                    ->label('Filter Pertemuan')
                    ->relationship('pertemuan', 'judul'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(), // Mengizinkan hapus jika ada kesalahan kuis
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHasilKuis::route('/'),
            'view' => Pages\ViewHasilKuis::route('/{record}'),
        ];
    }
}
