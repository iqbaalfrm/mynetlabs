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
                Actions\Action::make('generate_ai_quiz')
                    ->label('Generate Soal AI')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Generate Soal dengan AI')
                    ->modalDescription('AI akan membaca materi modul praktikum bab ini dan menghasilkan soal pilihan ganda secara otomatis.')
                    ->form(function (Schema $schema) {
                        return $schema->components([
                            TextInput::make('jumlah_soal')
                                ->label('Jumlah Soal')
                                ->numeric()
                                ->default(5)
                                ->minValue(1)
                                ->maxValue(20)
                                ->required(),
                        ]);
                    })
                    ->action(function (array $data, \Filament\Notifications\Notification $notification, $livewire) {
                        $pertemuanId = $livewire->ownerRecord->id;
                        try {
                            $response = \Illuminate\Support\Facades\Http::timeout(120)->post('http://127.0.0.1:5050/generate-quiz', [
                                'pertemuan_id' => $pertemuanId,
                                'jumlah_soal' => (int) $data['jumlah_soal'],
                            ]);

                            if ($response->successful() && $response->json('success')) {
                                $soalList = $response->json('data.soal');
                                foreach ($soalList as $soal) {
                                    \App\Models\SoalKuis::create([
                                        'pertemuan_id' => $pertemuanId,
                                        'pertanyaan' => $soal['pertanyaan'],
                                        'pilihan_a' => $soal['pilihan_a'],
                                        'pilihan_b' => $soal['pilihan_b'],
                                        'pilihan_c' => $soal['pilihan_c'],
                                        'pilihan_d' => $soal['pilihan_d'],
                                        'kunci_jawaban' => $soal['kunci_jawaban'],
                                        'penjelasan' => $soal['pembahasan'] ?? $soal['penjelasan'] ?? null,
                                    ]);
                                }
                                \Filament\Notifications\Notification::make()
                                    ->title('Berhasil Generate ' . count($soalList) . ' Soal!')
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal: ' . $response->json('message', 'Terjadi kesalahan'))
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error koneksi ke AI Backend')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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

    public function canCreate(): bool
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
