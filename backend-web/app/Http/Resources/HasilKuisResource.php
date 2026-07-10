<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HasilKuisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pertemuan' => $this->pertemuan ? $this->pertemuan->judul : '-',
            'nomor_pertemuan' => $this->pertemuan ? $this->pertemuan->nomor_urut : '-',
            'nilai' => (float) $this->nilai,
            'jumlah_benar' => $this->jumlah_benar,
            'total_soal' => $this->total_soal,
            'rekomendasi_ai' => $this->whenHas('rekomendasi_ai', $this->rekomendasi_ai),
            'tanggal' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
