<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'npk'              => $this->npk,
            'name'             => $this->name,
            'gender'           => $this->gender,
            'department_id'    => $this->department_id,
            'section_id'       => $this->section_id,
            'role_level_id'    => $this->role_level_id,
            'jabatan'          => $this->jabatan,
            'area'             => $this->area,
            'station'          => $this->station,
            'employment_type'  => $this->employment_type,
            'status'           => $this->status,
            'start_contract'   => $this->start_contract?->format('Y-m-d'),
            'end_contract'     => $this->end_contract?->format('Y-m-d'),
            'is_near_expiry'   => $this->is_near_expiry,
            'days_until_expiry'=> $this->days_until_expiry,

            // Relasi
            'department'  => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'section'     => $this->whenLoaded('section', fn() => [
                'id'   => $this->section->id,
                'name' => $this->section->name,
            ]),
            'role_level'  => $this->whenLoaded('roleLevel', fn() => [
                'id'   => $this->roleLevel->id,
                'name' => $this->roleLevel->name,
            ]),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}