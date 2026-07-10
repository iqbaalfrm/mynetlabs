<?php

namespace App\Http\Requests\Api\Kuis;

use Illuminate\Foundation\Http\FormRequest;

class SubmitKuisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'jawaban' => 'required|array',
            'jawaban.*.soal_id' => 'required|exists:soal_kuis,id',
            'jawaban.*.jawaban' => 'required|in:A,B,C,D',
        ];
    }
}
