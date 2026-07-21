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
        'role_level'       => $this->role_level,
        'jabatan'          => $this->jabatan,

        // kolom id baru
        'area_id'          => $this->area_id,
        'line_id'          => $this->line_id,
        'station_id'       => $this->station_id,

        'employment_type'  => $this->employment_type,
        'join_date'   => $this->join_date?->format('Y-m-d'),
        'start_contract'   => $this->start_contract?->format('Y-m-d'),
        'end_contract'     => $this->end_contract?->format('Y-m-d'),
        'is_near_expiry'   => $this->is_near_expiry,
        'days_until_expiry'=> $this->days_until_expiry,
        'is_active'           => (bool) $this->is_active,
        'deactivated_at'      => $this->deactivated_at?->format('Y-m-d H:i:s'),
        'deactivated_reason'  => $this->deactivated_reason,
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
        'replaced_by' => $this->whenLoaded('replacementRequisition', function () {
            $req = $this->replacementRequisition;
            if (!$req) return null;

            $newHires = $req->employees->map(fn($e) => [
                'id'             => $e->id,
                'npk'            => $e->npk,
                'name'           => $e->name,
                'start_contract' => $e->start_contract?->format('Y-m-d'),
            ]);

            if ($newHires->isEmpty()) return null; // FPTK ada tapi belum ada yang direkrut

            return [
                'no_req'    => $req->no_req,
                'employees' => $newHires,
            ];
        }),
        'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
    ];
}
}