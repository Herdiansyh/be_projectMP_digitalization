<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    /**
     * Approve or reject a requisition.
     */
public function review(Request $request, string $noReq): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'action'           => 'required|in:approved,rejected',
        'rejection_reason' => 'required_if:action,rejected|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $requisition = Requisition::findOrFail($noReq);
    $user        = Auth::user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $currentStatus = $requisition->approval_status;
    $roleName      = $user->roleLevel?->name;

    // Validasi: apakah user ini memang yang ditunjuk & statusnya sesuai
    $isAuthorized = match($currentStatus) {
        'Waiting for Manager Approval'      => $roleName === 'Manager'
                                            && $requisition->manager === $user->name,
        'Waiting for Division Head Approval' => $roleName === 'Division Head'
                                            && $requisition->division === $user->name,
        'Waiting for Director Approval'     => $roleName === 'Director'
                                            && $requisition->director === $user->name,
        default => false,
    };

    if (!$isAuthorized) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak berwenang melakukan approval pada tahap ini',
        ], 403);
    }

    // Handle rejection
    if ($request->action === 'rejected') {
        $requisition->update([
            'approval_status'  => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
            // ── ditambahkan agar FPTK yang di-reject bisa muncul di history approver ──
            'rejected_by'      => $user->name,
            'rejected_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Requisition rejected successfully',
            'data'    => $requisition,
        ]);
    }

    // Handle approval — tentukan status berikutnya
    $nextStatus = match($currentStatus) {
        'Waiting for Manager Approval' => $requisition->division
            ? 'Waiting for Division Head Approval'
            : ($requisition->director ? 'Waiting for Director Approval' : 'Approved'),
        'Waiting for Division Head Approval' => $requisition->director
            ? 'Waiting for Director Approval'
            : 'Approved',
        'Waiting for Director Approval' => 'Approved',
        default => 'Approved',
    };

    $updateData = match($currentStatus) {
        'Waiting for Manager Approval' => [
            'manager_approved_at' => now(),
            'approval_status'     => $nextStatus,
        ],
        'Waiting for Division Head Approval' => [
            'division_approved_at' => now(),
            'approval_status'      => $nextStatus,
        ],
        'Waiting for Director Approval' => [
            'director_approved_at' => now(),
            'approval_status'      => $nextStatus,
        ],
        default => [],
    };

    $requisition->update($updateData);

    return response()->json([
        'success' => true,
        'message' => 'Requisition approved successfully',
        'data'    => $requisition,
    ]);
}

    /**
     * Get requisition details for review.
     */
    public function showForReview(string $noReq): JsonResponse
    {
        $requisition = Requisition::with(['replacementEmployee:id,npk,name'])
        ->findOrFail($noReq);

        return response()->json([
            'success' => true,
            'data' => $requisition,
        ]);
    }

    /**
     * Get approval history for a requisition.
     */
    public function history(string $noReq): JsonResponse
    {
        $requisition = Requisition::findOrFail($noReq);

        $history = [
            'manager' => [
                'name' => $requisition->manager,
                'approved_at' => $requisition->manager_approved_at,
            ],
            'division' => [
                'name' => $requisition->division,
                'approved_at' => $requisition->division_approved_at,
            ],
            'director' => [
                'name' => $requisition->director,
                'approved_at' => $requisition->director_approved_at,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
