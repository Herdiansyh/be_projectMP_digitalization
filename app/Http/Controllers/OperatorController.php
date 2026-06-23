<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Models\Operator;
use App\Services\OperatorService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected OperatorService $operatorService) {}

  public function index(Request $request): JsonResponse
{
    try {
        $operators = $this->operatorService->getAll($request->all());
        return $this->successResponse($operators, 'Operators retrieved successfully');
    } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 500);
    }
}

    public function store(StoreOperatorRequest $request): JsonResponse
    {
        try {
            $operator = $this->operatorService->store($request->validated());
            return $this->successResponse($operator, 'Operator created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(Operator $operator): JsonResponse
    {
        try {
            $result = $this->operatorService->show($operator);
            return $this->successResponse($result, 'Operator retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateOperatorRequest $request, Operator $operator): JsonResponse
    {
        try {
            $result = $this->operatorService->update($request->validated(), $operator);
            return $this->successResponse($result, 'Operator updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Operator $operator): JsonResponse
    {
        try {
            $this->operatorService->destroy($operator);
            return $this->successResponse(null, 'Operator deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}