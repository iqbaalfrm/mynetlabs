<?php

namespace App\Filament\Resources;

use App\Models\TopikMateri;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class TopikMateriResource extends Resource
{
    protected static ?string $model = TopikMateri::class;

    // Menyamakan tipe data strict dengan standard Filament v3 terbaru
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Isi Materi Bacaan';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Konten Materi Pembelajaran')
                    ->schema([
                        Forms\Components\Select::make('pertemuan_id')
                            ->label('Hubungkan ke Pertemuan Bab')
                            ->relationship('pertemuan', 'judul') // Otomatis ngambil judul dari bab pertemuan
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('judul')
                            ->label('Sub-Judul / Topik Materi')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Pengenalan Tabel Routing'),

                        Forms\Components\RichEditor::make('isi_materi')
                            ->label('Isi Lengkap Bacaan Materi')
                            ->required()
                            ->placeholder('Tulis isi materi di sini (bisa copas dari PDF modul)...')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pertemuan.judul')
                    ->label('Bab Pertemuan')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('judul')
                    ->label('Topik / Sub-Judul')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array { return []; }
    
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\TopikMateriResource\Pages\ListTopikMateris::route('/'),
            'create' => \App\Filament\Resources\TopikMateriResource\Pages\CreateTopikMateri::route('/create'),
            'edit' => \App\Filament\Resources\TopikMateriResource\Pages\EditTopikMateri::route('/{record}/edit'),
        ];
    }
}