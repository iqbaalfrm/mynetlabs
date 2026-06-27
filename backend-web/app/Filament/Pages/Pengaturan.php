<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Filament\Forms;

class Pengaturan extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Pengaturan Akun & AI';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $settings = $this->loadSettings();

        $this->form->fill([
            'nama' => $user->nama,
            'username' => $user->username,
            'kkm' => $settings['kkm'] ?? 70,
            'gemini_api_key' => $settings['gemini_api_key'] ?? '',
            'openai_api_key' => $settings['openai_api_key'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Forms\Components\Section::make('Profil Guru & Ganti Password')
                    ->description('Perbarui nama, username login, serta password Anda.')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required(),
                        TextInput::make('username')
                            ->label('Username / NIP')
                            ->required(),
                        TextInput::make('new_password')
                            ->label('Password Baru (Opsional)')
                            ->password()
                            ->nullable()
                            ->placeholder('Kosongkan jika tidak ingin ganti'),
                    ])->columns(3),

                Forms\Components\Section::make('Konfigurasi KKM & AI')
                    ->description('Atur batasan KKM kelulusan kuis dan API Key penyedia AI.')
                    ->schema([
                        TextInput::make('kkm')
                            ->label('KKM Kuis')
                            ->numeric()
                            ->default(70)
                            ->required(),
                        TextInput::make('gemini_api_key')
                            ->label('Gemini API Key')
                            ->password()
                            ->nullable(),
                        TextInput::make('openai_api_key')
                            ->label('OpenAI API Key')
                            ->password()
                            ->nullable(),
                    ])->columns(3),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        SchemaActions::make($this->getFormActions())
                            ->alignment(Alignment::Start),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $user = auth()->user();

        // Update Profile
        $user->nama = $state['nama'];
        $user->username = $state['username'];

        if (filled($state['new_password'] ?? null)) {
            $user->password = Hash::make($state['new_password']);
        }

        $user->save();

        // Save Settings to JSON
        $settings = [
            'kkm' => floatval($state['kkm']),
            'gemini_api_key' => $state['gemini_api_key'] ?? '',
            'openai_api_key' => $state['openai_api_key'] ?? '',
        ];
        
        Storage::disk('local')->put('settings.json', json_encode($settings, JSON_PRETTY_PRINT));

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }

    protected function loadSettings(): array
    {
        if (Storage::disk('local')->exists('settings.json')) {
            $content = Storage::disk('local')->get('settings.json');
            return json_decode($content, true) ?? [];
        }

        return [
            'kkm' => 70,
            'gemini_api_key' => '',
            'openai_api_key' => '',
        ];
    }
}
