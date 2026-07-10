<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'nama' => $this->nama,
            'role' => $this->role,
            'kelas' => $this->kelas,
            'foto_profil_url' => $this->foto_profil_url,
            'password_is_default' => is_null($this->password_set_at),
        ];
    }
}
