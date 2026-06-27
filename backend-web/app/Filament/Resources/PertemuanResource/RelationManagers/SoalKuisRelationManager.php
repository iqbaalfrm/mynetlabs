<?php

namespace App\Filament\Resources\PertemuanResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;

class SoalKuisRelationManager extends RelationManager
{
    protected static string $relationship = 'soalKuis';

    protected static ?string $title = 'Bank Soal Kuis';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('pertanyaan')
                    ->label('Pertanyaan Kuis')
                    ->placeholder('Tuliskan pertanyaan soal kuis di sini...')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('pilihan_a')
                    ->label('Pilihan Ganda A')
                    ->required(),
                TextInput::make('pilihan_b')
                    ->label('Pilihan Ganda B')
                    ->required(),
                TextInput::make('pilihan_c')
                    ->label('Pilihan Ganda C')
                    ->required(),
                TextInput::make('pilihan_d')
                    ->label('Pilihan Ganda D')
                    ->required(),
                Select::make('kunci_jawaban')
                    ->label('Kunci Jawaban')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                    ])
                    ->required(),
                Textarea::make('penjelasan')
                    ->label('Penjelasan / Pembahasan Jawaban')
                    ->placeholder('Opsional: Berikan ulasan singkat mengenai jawaban yang benar...')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('pertanyaan')
            ->columns([
                TextColumn::make('pertanyaan')
                    ->label('Pertanyaan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('kunci_jawaban')
                    ->label('Kunci')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Tambah Soal Kuis'),
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
}
