<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StationController extends Controller
{
    use ApiResponseTrait;

    /**
     * List semua station, dengan optional pencarian & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Station::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->boolean('paginate', false)) {
                $perPage = min((int) $request->input('per_page', 15), 100);
                $stations = $query->orderBy('name')->paginate($perPage);
            } else {
                // Default: list penuh tanpa pagination — dipakai untuk dropdown.
                $stations = $query->orderBy('name')->get();
            }

            return $this->successResponse($stations, 'Stations retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:stations,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $station = Station::create($validator->validated());

            return $this->successResponse($station, 'Station created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Station $station): JsonResponse
    {
        return $this->successResponse($station, 'Station retrieved successfully');
    }

    public function update(Request $request, Station $station): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:stations,name,' . $station->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $station->update($validator->validated());

            return $this->successResponse($station, 'Station updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Station $station): JsonResponse
    {
        try {
            $station->delete();

            return $this->successResponse(null, 'Station deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}