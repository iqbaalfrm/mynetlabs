<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $passwordIsDefault = is_null($this->password_set_at);
        $graceDaysRemaining = 0;
        $mustChangePassword = false;

        if ($passwordIsDefault) {
            $created = $this->created_at ?? now();
            $diffInDays = now()->diffInDays($created);
            if ($diffInDays < 7) {
                $graceDaysRemaining = 7 - $diffInDays;
            } else {
                $mustChangePassword = true;
            }
        }

        return [
            'username' => $this->username,
            'nama' => $this->nama,
            'role' => $this->role,
            'kelas' => $this->kelas,
            'foto_profil_url' => $this->foto_profil_url,
            'password_is_default' => $passwordIsDefault,
            'must_change_password' => $mustChangePassword,
            'password_grace_days_remaining' => (int) $graceDaysRemaining,
        ];
    }
}
