<?php

namespace App\Filament\Resources\PertemuanResource\RelationManagers;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;

class TopikMateriRelationManager extends RelationManager
{
    protected static string $relationship = 'topikMateris';

    protected static ?string $title = 'Topik Materi Bacaan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('judul')
                    ->label('Sub-Judul / Topik Materi')
                    ->required()
                    ->maxLength(150)
                    ->placeholder('Contoh: Pengenalan Tabel Routing'),
                RichEditor::make('isi_materi')
                    ->label('Isi Lengkap Bacaan Materi')
                    ->required()
                    ->placeholder('Tulis isi materi di sini...')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul')
            ->columns([
                TextColumn::make('judul')
                    ->label('Topik / Sub-Judul')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Tambah Topik Baru'),
            ])
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

    public static function canCreate(): bool
    {
        return true;
    }

    public function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }

    public function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return true;
    }
}
