<?php

namespace App\Http\Controllers;

use App\Models\CompetencyCategory;
use App\Models\CompetencyMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompetencyCategoryController extends Controller
{
    /**
     * Tambah kategori baru ke sebuah matrix yang sudah ada.
     * Ini bagian "step 2" dari alur bertahap: matrix dulu, baru kategori.
     */
    public function store(Request $request, int $matrixId): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $matrix = CompetencyMatrix::findOrFail($matrixId);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator);
        }

        // Kalau order tidak diisi, taruh di urutan paling akhir otomatis.
        $order = $request->input('order')
            ?? ($matrix->categories()->max('order') + 1);

        $category = $matrix->categories()->create([
            'name'  => $request->name,
            'order' => $order,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Category \"{$category->name}\" added.",
            'data'    => $category,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $category = CompetencyCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|required|string|max:255',
            'order' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator);
        }

        $category->update($request->only(['name', 'order']));

        return response()->json([
            'success' => true,
            'message' => 'Category updated.',
            'data'    => $category->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->isAdmin()) {
            return $this->forbidden();
        }

        $category = CompetencyCategory::findOrFail($id);

        // Cascade otomatis hapus checkpoint di dalamnya (lihat migration).
        // Kalau matrix ini sudah punya assessment history, sebaiknya kita
        // cegah penghapusan supaya skor lama tidak jadi timpang/rusak.
        $hasHistory = $category->checkpoints()
            ->whereHas('scores')
            ->exists();

        if ($hasHistory) {
            return response()->json([
                'success' => false,
                'message' => 'This category has assessment history and cannot be deleted.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted.',
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