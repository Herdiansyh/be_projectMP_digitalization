<?php

namespace App\Http\Controllers;

use App\Models\EvaluationCriteria;
use App\Models\EvaluationCriteriaGroup;
use App\Models\EvaluationCriteriaScaleOptions;
use App\Models\EvaluationCriteriaSubgroup;
use App\Models\EvaluationScore;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationCriteriaController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $groups = EvaluationCriteriaGroup::with(['subgroups.criteria.scaleOptions', 'criteria.scaleOptions'])
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Evaluation criteria loaded successfully',
                'data' => $groups,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // --- Groups ---

    public function storeGroup(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10',
                'description' => 'nullable|string',
                'order' => 'integer'
            ]);

            $group = EvaluationCriteriaGroup::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => $group,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateGroup(Request $request, $id): JsonResponse
    {
        try {
            $group = EvaluationCriteriaGroup::findOrFail($id);
            $group->update($request->only(['name', 'code', 'description', 'order']));

            return response()->json([
                'success' => true,
                'message' => 'Group updated successfully',
                'data' => $group,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyGroup($id): JsonResponse
    {
        try {
            $group = EvaluationCriteriaGroup::findOrFail($id);

            // Check if any criteria inside this group is used in EvaluationScore
            $criteriaIds = EvaluationCriteria::where('group_id', $id)->pluck('id');
            if (EvaluationScore::whereIn('criteria_id', $criteriaIds)->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete group because its criteria are already used in evaluations.'], 400);
            }

            $group->delete(); // This will not cascade automatically unless configured in DB, so we should delete manually or assume DB cascades. Let's do manual for safety or assume framework handles it if we don't have constraints. Better to delete criteria first.
            EvaluationCriteria::where('group_id', $id)->delete();
            EvaluationCriteriaSubgroup::where('group_id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reorderGroups(Request $request): JsonResponse
    {
        try {
            $orders = $request->input('orders', []); // e.g. [['id' => 1, 'order' => 1], ...]
            foreach ($orders as $item) {
                EvaluationCriteriaGroup::where('id', $item['id'])->update(['order' => $item['order']]);
            }
            return response()->json(['success' => true, 'message' => 'Groups reordered successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Subgroups ---

    public function storeSubgroup(Request $request, $groupId): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'roman_code' => 'required|string|max:10',
                'description' => 'nullable|string',
                'order' => 'integer'
            ]);

            $subgroup = EvaluationCriteriaSubgroup::create(array_merge($request->all(), ['group_id' => $groupId]));

            return response()->json([
                'success' => true,
                'message' => 'Subgroup created successfully',
                'data' => $subgroup,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSubgroup(Request $request, $id): JsonResponse
    {
        try {
            $subgroup = EvaluationCriteriaSubgroup::findOrFail($id);
            $subgroup->update($request->only(['name', 'roman_code', 'description', 'order']));

            return response()->json([
                'success' => true,
                'message' => 'Subgroup updated successfully',
                'data' => $subgroup,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroySubgroup($id): JsonResponse
    {
        try {
            $subgroup = EvaluationCriteriaSubgroup::findOrFail($id);

            // Check if any criteria inside this subgroup is used
            $criteriaIds = EvaluationCriteria::where('subgroup_id', $id)->pluck('id');
            if (EvaluationScore::whereIn('criteria_id', $criteriaIds)->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete subgroup because its criteria are already used in evaluations.'], 400);
            }

            EvaluationCriteria::where('subgroup_id', $id)->delete();
            $subgroup->delete();

            return response()->json(['success' => true, 'message' => 'Subgroup deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reorderSubgroups(Request $request, $groupId): JsonResponse
    {
        try {
            $orders = $request->input('orders', []);
            foreach ($orders as $item) {
                EvaluationCriteriaSubgroup::where('id', $item['id'])->where('group_id', $groupId)->update(['order' => $item['order']]);
            }
            return response()->json(['success' => true, 'message' => 'Subgroups reordered successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Criteria ---

    public function storeCriteria(Request $request, $groupId): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'subgroup_id' => 'nullable|integer',
                'weight' => 'required|numeric|min:0',
                'order' => 'integer'
            ]);

            $criteria = EvaluationCriteria::create(array_merge($request->all(), [
                'group_id' => $groupId,
                'scale_type' => 'custom_text',
                'is_active' => true,
            ]));

            // Default scale options
            for ($i = 1; $i <= 5; $i++) {
                EvaluationCriteriaScaleOptions::create([
                    'criteria_id' => $criteria->id,
                    'score' => $i,
                    'description' => 'Option ' . $i
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Criteria created successfully',
                'data' => $criteria->load('scaleOptions'),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateCriteria(Request $request, $id): JsonResponse
    {
        try {
            $criteria = EvaluationCriteria::findOrFail($id);
            $criteria->update($request->only(['name', 'weight', 'order', 'is_active', 'subgroup_id']));

            return response()->json([
                'success' => true,
                'message' => 'Criteria updated successfully',
                'data' => $criteria,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyCriteria($id): JsonResponse
    {
        try {
            $criteria = EvaluationCriteria::findOrFail($id);

            if (EvaluationScore::where('criteria_id', $id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete criteria because it is already used in evaluations.'], 400);
            }

            EvaluationCriteriaScaleOptions::where('criteria_id', $id)->delete();
            $criteria->delete();

            return response()->json(['success' => true, 'message' => 'Criteria deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reorderCriteria(Request $request, $groupId): JsonResponse
    {
        try {
            $orders = $request->input('orders', []);
            foreach ($orders as $item) {
                EvaluationCriteria::where('id', $item['id'])->where('group_id', $groupId)->update(['order' => $item['order']]);
            }
            return response()->json(['success' => true, 'message' => 'Criteria reordered successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Scale Options ---

    public function updateScaleOptions(Request $request, $criteriaId): JsonResponse
    {
        try {
            $options = $request->input('options', []); // [{score: 1, description: '...'}, ...]

            DB::transaction(function () use ($criteriaId, $options) {
                foreach ($options as $opt) {
                    EvaluationCriteriaScaleOptions::updateOrCreate(
                        ['criteria_id' => $criteriaId, 'score' => $opt['score']],
                        ['description' => $opt['description']]
                    );
                }
            });

            return response()->json(['success' => true, 'message' => 'Scale options updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    // --- Bulk Save ---

    public function bulkSave(Request $request): JsonResponse
    {
        try {
            $groups = $request->input('groups', []);

            DB::transaction(function () use ($groups) {
                foreach ($groups as $gIdx => $groupData) {
                    if (!isset($groupData['id'])) continue;
                    $group = EvaluationCriteriaGroup::find($groupData['id']);
                    if ($group) {
                        $group->update([
                            'name' => $groupData['name'] ?? $group->name,
                            'code' => $groupData['code'] ?? $group->code,
                            'description' => $groupData['description'] ?? $group->description,
                            'order' => $gIdx
                        ]);
                    }

                    if (isset($groupData['subgroups']) && is_array($groupData['subgroups'])) {
                        foreach ($groupData['subgroups'] as $sIdx => $subgroupData) {
                            if (!isset($subgroupData['id'])) continue;
                            $subgroup = EvaluationCriteriaSubgroup::find($subgroupData['id']);
                            if ($subgroup) {
                                $subgroup->update([
                                    'name' => $subgroupData['name'] ?? $subgroup->name,
                                    'roman_code' => $subgroupData['roman_code'] ?? $subgroup->roman_code,
                                    'description' => $subgroupData['description'] ?? $subgroup->description,
                                    'order' => $sIdx
                                ]);
                            }

                            if (isset($subgroupData['criteria']) && is_array($subgroupData['criteria'])) {
                                foreach ($subgroupData['criteria'] as $cIdx => $criteriaData) {
                                    if (!isset($criteriaData['id'])) continue;
                                    $criteria = EvaluationCriteria::find($criteriaData['id']);
                                    if ($criteria) {
                                        $criteria->update([
                                            'name' => $criteriaData['name'] ?? $criteria->name,
                                            'weight' => $criteriaData['weight'] ?? $criteria->weight,
                                            'order' => $cIdx,
                                            'is_active' => $criteriaData['is_active'] ?? $criteria->is_active
                                        ]);
                                    }

                                    if (isset($criteriaData['scale_options']) && is_array($criteriaData['scale_options'])) {
                                        foreach ($criteriaData['scale_options'] as $optData) {
                                            EvaluationCriteriaScaleOptions::updateOrCreate(
                                                ['criteria_id' => $criteriaData['id'], 'score' => $optData['value'] ?? $optData['score']],
                                                ['description' => $optData['label'] ?? $optData['description'] ?? null]
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'All changes saved successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
