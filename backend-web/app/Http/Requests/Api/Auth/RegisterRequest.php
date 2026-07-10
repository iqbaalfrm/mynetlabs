<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'nama' => 'required|string|max:100',
            'role' => 'required|in:guru,siswa',
            'kelas' => 'required_if:role,siswa|string|max:20',
        ];
    }
}
