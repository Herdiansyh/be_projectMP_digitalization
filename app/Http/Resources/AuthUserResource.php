<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'npk'           => $this->npk,
            'username'      => $this->username,
            'photo'         => $this->photo,
            'is_admin'      => (bool) $this->is_admin, // <-- TAMBAHAN
            'is_active'     => (bool) $this->is_active,
            'last_login_at' => $this->last_login_at,
            'role' => [
                'id'   => $this->roleLevel?->id,
                'name' => $this->roleLevel?->name,
            ],
            'department' => [
                'id'   => $this->department?->id,
                'name' => $this->department?->name,
            ],
            'section' => [
                'id'   => $this->section?->id,
                'name' => $this->section?->name,
            ],
            'permissions' => [],
        ];
    }
}