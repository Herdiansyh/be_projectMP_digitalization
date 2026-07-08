<?php

namespace App\Http\Controllers;

use App\Models\CompetencyCategory;
use App\Models\CompetencyCheckpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompetencyCheckpointController extends Controller
{
    /**
     * Tambah checkpoint baru ke sebuah kategori. Ini "step 3" — bagian
     * paling detail dari rubrik (satu baris kriteria penilaian).
     */
  public function store(Request $request, int $categoryId): JsonResponse
{
    if (!$this->isAdmin()) {
        return $this->forbidden();
    }

    $category = CompetencyCategory::findOrFail($categoryId);

    $validator = Validator::make($request->all(), [
        'description'  => 'required|string',
        'sequence'     => 'nullable|integer|min:0',   // ← tambahkan
        'main_process' => 'nullable|string|max:255',
        'weight'       => 'required|integer|min:1|max:255',
        'order'        => 'nullable|integer|min:0',
    ]);

    if ($validator->fails()) {
        return $this->validationFailed($validator);
    }

    $order = $request->input('order')
        ?? ($category->checkpoints()->max('order') + 1);

    $checkpoint = $category->checkpoints()->create([
        'description'  => $request->description,
        'sequence'     => $request->sequence,       // ← tambahkan
        'main_process' => $request->main_process,
        'weight'       => $request->weight,
        'order'        => $order,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Checkpoint added.',
        'data'    => $checkpoint,
    ], 201);
}

public function update(Request $request, int $id): JsonResponse
{
    if (!$this->isAdmin()) {
        return $this->forbidden();
    }

    $checkpoint = CompetencyCheckpoint::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'description'  => 'sometimes|required|string',
        'sequence'     => 'sometimes|nullable|integer|min:0',  // ← tambahkan
        'main_process' => 'sometimes|nullable|string|max:255',
        'weight'       => 'sometimes|required|integer|min:1|max:255',
        'order'        => 'sometimes|integer|min:0',
    ]);

    if ($validator->fails()) {
        return $this->validationFailed($validator);
    }

    $hasHistory = $checkpoint->scores()->exists();

    $checkpoint->update($request->only([
        'description', 'sequence', 'main_process', 'weight', 'order', // ← tambahkan sequence
    ]));

    return response()->json([
        'success' => true,
        'message' => $hasHistory
            ? 'Checkpoint updated. Note: this checkpoint already has assessment history — changing the weight will affect how old scores are calculated.'
            : 'Checkpoint updated.',
        'data'    => $checkpoint->fresh(),
    ]);
}
    public function destroy(int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $checkpoint = CompetencyCheckpoint::findOrFail($id);

        if ($checkpoint->scores()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This checkpoint already has assessment history and cannot be deleted.',
            ], 422);
        }

        $checkpoint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checkpoint deleted.',
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