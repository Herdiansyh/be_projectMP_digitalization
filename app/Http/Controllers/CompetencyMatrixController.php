<?php

namespace App\Http\Controllers;

use App\Models\CompetencyMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompetencyMatrixController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $query = CompetencyMatrix::with(['station', 'categories.checkpoints'])
            ->orderBy('name');

        if ($request->has('station_id')) {
            $query->where('station_id', $request->station_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $matrix = CompetencyMatrix::with(['station', 'categories.checkpoints'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $matrix,
        ]);
    }

  
    public function store(Request $request): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $validator = Validator::make($request->all(), [
            'station_id' => 'required|exists:stations,id',
            'name'       => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator);
        }

        $matrix = DB::transaction(function () use ($request) {
          CompetencyMatrix::where('station_id', $request->station_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            return CompetencyMatrix::create([
                'station_id' => $request->station_id,
                'name'       => $request->name,
                'is_active'  => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Competency matrix \"{$matrix->name}\" created.",
            'data'    => $matrix,
        ], 201);
    }

  public function update(Request $request, int $id): JsonResponse
{
    if (!$this->isAdmin()) {
        return $this->forbidden();
    }

    $matrix = CompetencyMatrix::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'name'       => 'sometimes|required|string|max:255',
        'is_active'  => 'sometimes|boolean',
        'station_id' => 'sometimes|required|exists:stations,id',
    ]);

    if ($validator->fails()) {
        return $this->validationFailed($validator);
    }

    DB::transaction(function () use ($matrix, $request) {
        if ($request->boolean('is_active')) {
            CompetencyMatrix::where('station_id', $request->input('station_id', $matrix->station_id))
                ->where('id', '!=', $matrix->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $matrix->update($request->only(['name', 'is_active', 'station_id']));
    });

    return response()->json([
        'success' => true,
        'message' => 'Matrix updated.',
        'data'    => $matrix->fresh(),
    ]);
}

    public function destroy(int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $matrix = CompetencyMatrix::findOrFail($id);

      if ($matrix->assessments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This matrix already has assessment history and cannot be deleted. Deactivate it instead.',
            ], 422);
        }

        $matrix->delete();
        return response()->json([
            'success' => true,
            'message' => 'Matrix deleted.',
        ]);
    }

    private function isAdmin(): bool
    {
    return Auth::user()?->is_admin === true;
    }

    private function forbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Only Admin can manage the competency matrix.',
        ], 403);
    }

    private function validationFailed($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }
}