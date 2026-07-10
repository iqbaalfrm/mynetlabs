<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;

class KirimPesanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pertemuan_id' => 'nullable|exists:pertemuan,id',
            'pesan' => 'required|string|max:1000',
        ];
    }
}
