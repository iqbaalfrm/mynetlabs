<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->sender,
            'pesan' => $this->pesan,
            'sumber' => $this->sumber_referensi,
            'waktu' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
