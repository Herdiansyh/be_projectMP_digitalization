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
            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'is_active' => (bool) $this->is_active,

            'last_login_at' => $this->last_login_at,

            'role' => [
                'id' => $this->role?->id,
                'name' => $this->role?->name,
            ],

            // persiapan RBAC nanti
            'permissions' => [],
        ];
    }
}