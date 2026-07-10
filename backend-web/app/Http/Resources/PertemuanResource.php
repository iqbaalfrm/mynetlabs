<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PertemuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor' => $this->nomor_urut,
            'urutan' => $this->nomor_urut,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'semester' => $this->semester,
            'warna_tema' => $this->warna_tema,
            'topik_count' => $this->whenHas('total_topik', $this->total_topik),
            'progress' => $this->when(isset($this->progress), $this->progress),
            'is_completed' => $this->when(isset($this->progress), $this->progress >= 1.0),
            'status_indexing' => $this->when(isset($this->status_indexing), $this->status_indexing),
            'daftar_topik' => TopikMateriResource::collection($this->whenLoaded('topikMateris')),
        ];
    }
}
