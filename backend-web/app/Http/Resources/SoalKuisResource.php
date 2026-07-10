<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoalKuisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pertanyaan' => $this->pertanyaan,
            'pilihan' => [
                'A' => $this->pilihan_a,
                'B' => $this->pilihan_b,
                'C' => $this->pilihan_c,
                'D' => $this->pilihan_d,
            ],
        ];
    }
}
