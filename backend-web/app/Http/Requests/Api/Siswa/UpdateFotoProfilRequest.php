<?php

namespace App\Http\Requests\Api\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFotoProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
