<?php

namespace App\Http\Requests\Api\Chat;

use Illuminate\Foundation\Http\FormRequest;

class KirimPesanAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pertemuan_id' => 'nullable|exists:pertemuan,id',
            'audio' => 'required|file|mimes:wav,mp3,m4a,ogg,webm,aac|max:10240',
        ];
    }
}
