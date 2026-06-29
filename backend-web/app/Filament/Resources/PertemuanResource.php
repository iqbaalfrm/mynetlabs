<?php

namespace App\Filament\Resources;

use App\Models\Pertemuan;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use App\Filament\Resources\PertemuanResource\RelationManagers;

class PertemuanResource extends Resource
{
    protected static ?string $model = Pertemuan::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Modul Pertemuan';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Form Input Modul Praktikum Network')
                    ->description('Isi data pertemuan bab jaringan di bawah ini.')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_urut')
                            ->label('Pertemuan Ke-')
                            ->numeric()
                            ->required()
                            ->placeholder('Contoh: 1'),

                        Forms\Components\Select::make('semester')
                            ->options([
                                '1' => 'Semester 1',
                                '2' => 'Semester 2',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('judul')
                            ->label('Judul Praktikum')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Konfigurasi Routing Statis Cisco'),

                        Forms\Components\ColorPicker::make('warna_tema')
                            ->label('Warna Tema Card di Mobile')
                            ->default('#3B82F6'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi / Tujuan Pembelajaran')
                            ->rows(4)
                            ->placeholder('Tuliskan capaian praktikum bab ini...')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_urut')
                    ->label('No')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul Praktikum')
                    ->searchable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'info',
                        '2' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\ColorColumn::make('warna_tema')
                    ->label('Warna'),
            ])
            ->filters([])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()
                    ->label('Kelola Materi')
                    ->icon('heroicon-o-book-open')
                    ->color('info'),
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\TopikMateriRelationManager::class,
            RelationManagers\ModulPdfRelationManager::class,
            RelationManagers\SoalKuisRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PertemuanResource\Pages\ListPertemuans::route('/'),
            'view' => \App\Filament\Resources\PertemuanResource\Pages\ViewPertemuan::route('/{record}'),
            'edit' => \App\Filament\Resources\PertemuanResource\Pages\EditPertemuan::route('/{record}/edit'),
        ];
    }
}