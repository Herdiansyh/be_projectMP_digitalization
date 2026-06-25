<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeStatus;
use App\Models\RoleLevel;
use App\Models\Section;
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
                'employee_statuses' => EmployeeStatus::all(['id', 'name', 'level_default']),
                'role_levels' => RoleLevel::all(['id', 'name']),
            ],
        ]);
    }
}
