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

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Station::with('line:id,name,area_id', 'line.area:id,name');

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('line_id')) {
                $query->where('line_id', $request->line_id);
            }

            // Filter tambahan: bisa langsung filter by area lewat relasi line
            if ($request->filled('area_id')) {
                $query->whereHas('line', function ($q) use ($request) {
                    $q->where('area_id', $request->area_id);
                });
            }

            if ($request->boolean('paginate', false)) {
                $perPage = min((int) $request->input('per_page', 15), 100);
                $stations = $query->orderBy('name')->paginate($perPage);
            } else {
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
            'line_id' => 'required|exists:lines,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('stations')->where(
                    fn ($query) => $query->where('line_id', $request->line_id)
                ),
            ],
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
            $station->load('line:id,name,area_id', 'line.area:id,name');

            return $this->successResponse($station, 'Station created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Station $station): JsonResponse
    {
        $station->load('line:id,name,area_id', 'line.area:id,name');

        return $this->successResponse($station, 'Station retrieved successfully');
    }

    public function update(Request $request, Station $station): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'line_id' => 'required|exists:lines,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('stations')
                    ->where(fn ($query) => $query->where('line_id', $request->line_id))
                    ->ignore($station->id),
            ],
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
            $station->load('line:id,name,area_id', 'line.area:id,name');

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