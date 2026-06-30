<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = [
            'APP_NAME' => env('APP_NAME', 'NetLabs'),
            'AI_API_URL' => env('AI_API_URL', ''),
            'AI_API_KEY' => env('AI_API_KEY', ''),
            'SCHOOL_NAME' => env('SCHOOL_NAME', 'NetLabs Academy'),
            'SCHOOL_ADDRESS' => env('SCHOOL_ADDRESS', ''),
            'SCHOOL_PHONE' => env('SCHOOL_PHONE', ''),
            'SCHOOL_EMAIL' => env('SCHOOL_EMAIL', ''),
        ];

        return view('admin.pengaturan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'APP_NAME' => 'required|string|max:100',
            'AI_API_URL' => 'nullable|url',
            'AI_API_KEY' => 'nullable|string',
            'SCHOOL_NAME' => 'required|string|max:100',
            'SCHOOL_ADDRESS' => 'nullable|string|max:255',
            'SCHOOL_PHONE' => 'nullable|string|max:20',
            'SCHOOL_EMAIL' => 'nullable|email|max:100',
        ]);

        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        foreach ($validated as $key => $value) {
            $value = '"' . str_replace('"', '\\"', $value) . '"';
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);

        // Clear config cache biar env terbaca ulang
        \Artisan::call('config:clear');

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}