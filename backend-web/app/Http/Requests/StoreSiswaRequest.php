<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiswaRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
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
                ? ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()]
                : ['nullable', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    /**
     * Kustomisasi nama atribut untuk pesan kesalahan validator.
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
