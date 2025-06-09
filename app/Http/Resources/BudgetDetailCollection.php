<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BudgetDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return BudgetDetailResource::collection($this->collection)->resolve();
    }

    public function with($request) {
        return [
            'meta' => [
                'status' => 'success',
            ],
        ];
    }
}
