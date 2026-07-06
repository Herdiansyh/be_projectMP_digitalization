<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LineController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Line::with('area:id,name');

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            if ($request->boolean('paginate', false)) {
                $perPage = min((int) $request->input('per_page', 15), 100);
                $lines = $query->orderBy('name')->paginate($perPage);
            } else {
                $lines = $query->orderBy('name')->get();
            }

            return $this->successResponse($lines, 'Lines retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:areas,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('lines')->where(
                    fn ($query) => $query->where('area_id', $request->area_id)
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
            $line = Line::create($validator->validated());
            $line->load('area:id,name');

            return $this->successResponse($line, 'Line created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Line $line): JsonResponse
    {
        $line->load('area:id,name');

        return $this->successResponse($line, 'Line retrieved successfully');
    }

    public function update(Request $request, Line $line): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:areas,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('lines')
                    ->where(fn ($query) => $query->where('area_id', $request->area_id))
                    ->ignore($line->id),
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
            $line->update($validator->validated());
            $line->load('area:id,name');

            return $this->successResponse($line, 'Line updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Line $line): JsonResponse
    {
        try {
            $line->delete();

            return $this->successResponse(null, 'Line deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}