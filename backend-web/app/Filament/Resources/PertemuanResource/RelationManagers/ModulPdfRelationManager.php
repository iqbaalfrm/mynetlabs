<?php

namespace App\Filament\Resources\PertemuanResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;

class ModulPdfRelationManager extends RelationManager
{
    protected static string $relationship = 'modulPdfs';

    protected static ?string $title = 'Modul PDF RAG';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_name')
                    ->label('File Modul PDF')
                    ->disk('public')
                    ->directory('modul_pdf')
                    ->acceptedFileTypes(['application/pdf'])
                    ->preserveFilenames()
                    ->required()
                    ->columnSpanFull(),
                Select::make('status_indexing')
                    ->label('Status Indexing AI')
                    ->options([
                        'pending' => 'Menunggu Antrean (Pending)',
                        'processing' => 'Sedang Diproses (Processing)',
                        'success' => 'Sukses Ter-indeks (Success)',
                        'failed' => 'Gagal Ter-indeks (Failed)',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_indexing')
                    ->label('Status Indexing AI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Unggah PDF Baru'),
            ])
            ->actions([
                Actions\Action::make('index_pdf_ai')
                    ->label('Index AI')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $filePath = storage_path('app/public/' . $record->file_name);
                            $response = \Illuminate\Support\Facades\Http::timeout(300)->post('http://127.0.0.1:5050/index-pdf', [
                                'pertemuan_id' => $record->pertemuan_id,
                                'file_path' => $filePath,
                            ]);

                            if ($response->successful() && $response->json('success')) {
                                $record->update(['status_indexing' => 'success']);
                                \Filament\Notifications\Notification::make()
                                    ->title('Berhasil di-index ke AI!')
                                    ->success()
                                    ->send();
                            } else {
                                $record->update(['status_indexing' => 'failed']);
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal: ' . $response->json('message', 'Terjadi kesalahan'))
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            $record->update(['status_indexing' => 'failed']);
                            \Filament\Notifications\Notification::make()
                                ->title('Error koneksi ke AI Backend')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
