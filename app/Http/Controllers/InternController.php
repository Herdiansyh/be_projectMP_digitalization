<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternRequest;
use App\Http\Requests\UpdateInternRequest;
use App\Http\Resources\InternResource;
use App\Models\Intern;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Intern::with(['department', 'section']);

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

            if ($request->boolean('near_expiry')) {
                $query->whereNotNull('end_contract')
                      ->whereDate('end_contract', '>=', today())
                      ->whereDate('end_contract', '<=', today()->addDays(30));
            }

            // Cap per_page supaya request tidak bisa minta ribuan baris sekaligus
            $perPage = min((int) $request->input('per_page', 15), 100);
            $interns = $query->orderBy('name')->paginate($perPage);

            return $this->successResponse(
                InternResource::collection($interns)->response()->getData(true),
                'Interns retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreInternRequest $request): JsonResponse
    {
        try {
            $intern = Intern::create($request->validated());
            $intern->load(['department', 'section']);

            return $this->successResponse(
                new InternResource($intern),
                'Intern created successfully',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Intern $intern): JsonResponse
    {
        try {
            $intern->load(['department', 'section']);

            return $this->successResponse(
                new InternResource($intern),
                'Intern retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateInternRequest $request, Intern $intern): JsonResponse
    {
        try {
            $intern->update($request->validated());
            $intern->load(['department', 'section']);

            return $this->successResponse(
                new InternResource($intern),
                'Intern updated successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Intern $intern): JsonResponse
    {
        try {
            $intern->delete();

            return $this->successResponse(null, 'Intern deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * List ringkas intern (untuk dropdown, dsb).
     * Dibatasi 200 baris — cukup untuk dropdown, mencegah query unbounded.
     */
    public function activeList(): JsonResponse
    {
        try {
            $interns = Intern::select('id', 'npk', 'name', 'jabatan', 'department_id')
                ->with('department:id,name')
                ->orderBy('name')
                ->limit(200)
                ->get();

            return $this->successResponse(
                InternResource::collection($interns),
                'Interns retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}