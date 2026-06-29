<?php

namespace App\Filament\Resources\KelasResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class SiswaRelationManager extends RelationManager
{
    protected static string $relationship = 'siswa';

    protected static ?string $title = 'Daftar Siswa';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('NIS')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('nama')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\Hidden::make('role')
                    ->default('siswa'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\ImageColumn::make('foto_profil')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?background=4f46e5&color=fff&name=' . urlencode($record->nama)),
                TextColumn::make('username')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hasil_kuis_avg_nilai')
                    ->avg('hasilKuis', 'nilai')
                    ->label('Rata-rata Nilai')
                    ->numeric(1)
                    ->sortable()
                    ->placeholder('Belum kuis')
                    ->badge()
                    ->color(function ($state) {
                        $kkm = 75;
                        if (\Illuminate\Support\Facades\Storage::disk('local')->exists('settings.json')) {
                            $settings = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('settings.json'), true);
                            $kkm = $settings['kkm'] ?? 75;
                        }
                        return $state >= $kkm ? 'success' : 'danger';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Tambah Siswa Baru'),
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

    public function isReadOnly(): bool
    {
        return false;
    }
}
