<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeStatus;
use App\Models\Line;
use App\Models\RoleLevel;
use App\Models\Section;
use App\Models\Station;
use Illuminate\Http\JsonResponse;

class MasterDataController extends Controller
{
    /**
     * Get all master data required for FPTK dropdowns.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'companies' => Company::all(['id', 'name']),
                'departments' => Department::all(['id', 'name']),
                'sections' => Section::all(['id', 'name']),
                'stations' => Station::all(['id', 'name']),   
                'areas'=> Area::all(['id', 'name']),
                'lines' => Line::with('area:id,name')->get(),
                'employee_statuses' => EmployeeStatus::all(['id', 'name', 'level_default']),
                'role_levels' => RoleLevel::all(['id', 'name']),
            ],
        ]);
    }
}
