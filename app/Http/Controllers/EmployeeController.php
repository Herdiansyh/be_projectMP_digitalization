<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

public function index(Request $request): JsonResponse
{
    try {
        $query = Employee::with(['department', 'section', 'area', 'line', 'station']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('npk', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('line_id')) {
            $query->where('line_id', $request->line_id);
        }        
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->boolean('near_expiry')) {
            $query->whereNotNull('end_contract')
                  ->whereDate('end_contract', '>=', today())
                  ->whereDate('end_contract', '<=', today()->addDays(30));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'updated_at', 'name', 'npk', 'end_contract', 'id'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Untuk keperluan print "semua sesuai filter" — bypass pagination.
        if ($request->boolean('all')) {
            $employees = $query->get();

            return $this->successResponse(
                ['data' => EmployeeResource::collection($employees)],
                'All filtered employees retrieved successfully'
            );
        }

        // Samakan dengan InternController: cap per_page maksimal 100.
        $perPage = min((int) $request->input('per_page', 15), 100);
        $employees = $query->paginate($perPage);

        return $this->successResponse(
            EmployeeResource::collection($employees)->response()->getData(true),
            'Employees retrieved successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['deactivated_at'] = ($data['is_active'] ?? true) === false
                ? now()
                : null;

            $employee = Employee::create($data);
            $employee->load(['department', 'section', 'area', 'line', 'station']);

            return $this->successResponse(
                new EmployeeResource($employee),
                'Employee created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

  public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            $data = $request->validated();

            if (array_key_exists('is_active', $data)) {
                if ($data['is_active'] === false && $employee->is_active !== false) {
                    // baru saja dinonaktifkan → catat waktunya
                    $data['deactivated_at'] = now();
                } elseif ($data['is_active'] === true) {
                    // diaktifkan kembali → reset
                    $data['deactivated_at'] = null;
                    $data['deactivated_reason'] = null;
                }
            }

            $employee->update($data);
            $employee->load(['department', 'section', 'area', 'line', 'station']);

            return $this->successResponse(
                new EmployeeResource($employee),
                'Employee updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Employee $employee): JsonResponse
{
    try {
        $employee->load([
            'department', 'section', 'area', 'line', 'station',
            'replacementRequisition.employees',
        ]);
        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee retrieved successfully'
        );
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

   
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $employee->delete();

            return $this->successResponse(null, 'Employee deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Di EmployeeController.php
public function activeList(): JsonResponse
    {
        try {
            $employees = Employee::select('id', 'npk', 'name', 'jabatan', 'department_id')
                ->where('is_active', true)
                ->with('department:id,name')
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                EmployeeResource::collection($employees),
                'Employees retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}