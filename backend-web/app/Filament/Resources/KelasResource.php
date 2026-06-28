<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelasResource\Pages;
use App\Models\Kelas;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Support\Icons\Heroicon;

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manajemen Kelas';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama_kelas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->placeholder('Contoh: XI TKJ 1')
                    ->required()
                    ->maxLength(50),
                Select::make('wali_kelas_id')
                    ->label('Wali Kelas')
                    ->placeholder('Pilih Guru Wali Kelas')
                    ->options(User::where('role', 'guru')->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('waliKelas.nama')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum ada wali kelas'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->label('Kelola Siswa')
                    ->icon('heroicon-o-users')
                    ->color('info'),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            KelasResource\RelationManagers\SiswaRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            KelasResource\Widgets\KelasStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelas::route('/'),
            'view' => Pages\ViewKelas::route('/{record}'),
        ];
    }
}
