<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'npk'          => $this->npk,
            'name'         => $this->name,
            'username'     => $this->username,
            'email'        => $this->email,
            'is_admin'     => (bool) $this->is_admin,
            'can_view_manpower' => (bool) $this->can_view_manpower,
            'department'   => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'section'      => $this->whenLoaded('section', fn() => [
                'id'   => $this->section->id,
                'name' => $this->section->name,
            ]),
            'area'         => $this->whenLoaded('area', fn() => $this->area ? [
                'id'   => $this->area->id,
                'name' => $this->area->name,
            ] : null),
            'area_id'      => $this->area_id,
            'role_level'   => $this->whenLoaded('roleLevel', fn() => [
                'id'   => $this->roleLevel->id,
                'name' => $this->roleLevel->name,
            ]),
            'director'     => $this->whenLoaded('director', fn() => $this->director ? [
                'id'   => $this->director->id,
                'name' => $this->director->name,
                'npk'  => $this->director->npk,
            ] : null),
            'approver_manager' => $this->whenLoaded('approverManager', fn() => $this->approverManager ? [
                'id'   => $this->approverManager->id,
                'name' => $this->approverManager->name,
                'npk'  => $this->approverManager->npk,
            ] : null),
            'approver_division' => $this->whenLoaded('approverDivision', fn() => $this->approverDivision ? [
                'id'   => $this->approverDivision->id,
                'name' => $this->approverDivision->name,
                'npk'  => $this->approverDivision->npk,
            ] : null),
            'approver_director' => $this->whenLoaded('approverDirector', fn() => $this->approverDirector ? [
                'id'   => $this->approverDirector->id,
                'name' => $this->approverDirector->name,
                'npk'  => $this->approverDirector->npk,
            ] : null),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at'   => $this->created_at->toISOString(),
            'updated_at'   => $this->updated_at->toISOString(),
        ];
    }
}