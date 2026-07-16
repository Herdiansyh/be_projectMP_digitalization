<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternResource extends JsonResource
{
   public function toArray(Request $request): array
{
    return [
        'id'                => $this->id,
        'npk'               => $this->npk,
        'name'              => $this->name,
        'gender'            => $this->gender,
        'department_id'     => $this->department_id,
        'section_id'        => $this->section_id,
        'role_level'        => $this->role_level,
        'jabatan'           => $this->jabatan,
        'join_date'    => $this->join_date?->format('Y-m-d'),
        'start_contract'    => $this->start_contract?->format('Y-m-d'),
        'end_contract'      => $this->end_contract?->format('Y-m-d'),
        'is_near_expiry'    => $this->is_near_expiry,
        'days_until_expiry' => $this->days_until_expiry,

        'area_id'    => $this->area_id,
        'line_id'    => $this->line_id,
        'station_id' => $this->station_id,

        // Relasi
        'department' => $this->whenLoaded('department', fn() => [
            'id'   => $this->department->id,
            'name' => $this->department->name,
        ]),
        'section' => $this->whenLoaded('section', fn() => [
            'id'   => $this->section->id,
            'name' => $this->section->name,
        ]),
        'area' => $this->whenLoaded('area', fn() => $this->area ? [
            'id'   => $this->area->id,
            'name' => $this->area->name,
        ] : null),
        'line' => $this->whenLoaded('line', fn() => $this->line ? [
            'id'   => $this->line->id,
            'name' => $this->line->name,
        ] : null),
        'station' => $this->whenLoaded('station', fn() => $this->station ? [
            'id'   => $this->station->id,
            'name' => $this->station->name,
        ] : null),

        'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
    ];
}
}