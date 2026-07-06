<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Area::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->boolean('paginate', false)) {
                $perPage = min((int) $request->input('per_page', 15), 100);
                $areas = $query->orderBy('name')->paginate($perPage);
            } else {
                $areas = $query->orderBy('name')->get();
            }

            return $this->successResponse($areas, 'Areas retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:areas,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $area = Area::create($validator->validated());

            return $this->successResponse($area, 'Area created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Area $area): JsonResponse
    {
        return $this->successResponse($area, 'Area retrieved successfully');
    }

    public function update(Request $request, Area $area): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $area->update($validator->validated());

            return $this->successResponse($area, 'Area updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Area $area): JsonResponse
    {
        try {
            // Cegah hapus area yang masih punya line — supaya tidak ada
            // line "yatim" tanpa area setelah area induknya terhapus.
            if ($area->lines()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete area that still has lines. Please remove or reassign its lines first.',
                ], 422);
            }

            $area->delete();

            return $this->successResponse(null, 'Area deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}