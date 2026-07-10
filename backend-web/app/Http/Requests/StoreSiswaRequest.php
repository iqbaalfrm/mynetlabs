<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('siswa');

        return [
            'nama' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'kelas_id' => 'required|exists:kelas,id',
            'password' => $this->isMethod('POST')
                ? 'required|string|min:8|confirmed'
                : 'nullable|string|min:8|confirmed',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nama' => 'Nama Lengkap',
            'username' => 'NIS / Username',
            'kelas_id' => 'Kelas',
            'password' => 'Kata Sandi',
            'status' => 'Status Akun',
        ];
    }
}
