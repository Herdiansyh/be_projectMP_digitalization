<?php

namespace App\Services;

use App\Http\Resources\OperatorResource;
use App\Models\Operator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OperatorService
{
   public function getAll(array $params = []): array
{
    $query = Operator::with('creator');

    if (!empty($params['search'])) {
        $search = $params['search'];
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('nrp', 'like', "%{$search}%");
        });
    }

    $perPage = $params['per_page'] ?? 10;
    $paginated = $query->latest()->paginate($perPage);

    return [
        'data' => OperatorResource::collection($paginated->items()),
        'pagination' => [
            'total'        => $paginated->total(),
            'last_page'    => $paginated->lastPage(),
            'current_page' => $paginated->currentPage(),
            'per_page'     => $paginated->perPage(),
        ],
    ];
}

    public function store(array $data): OperatorResource
    {
        $operator = Operator::create([
            'name'       => $data['name'],
            'nrp'        => $data['nrp'],
            'created_by' => Auth::id(),
        ]);

        return new OperatorResource($operator->load('creator'));
    }

    public function show(Operator $operator): OperatorResource
    {
        return new OperatorResource($operator->load('creator'));
    }

    public function update(array $data, Operator $operator): OperatorResource
    {
        $operator->update($data);
        return new OperatorResource($operator->load('creator'));
    }

    public function destroy(Operator $operator): void
    {
        $operator->delete();
    }
}