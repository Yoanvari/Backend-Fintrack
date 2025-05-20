<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return TransactionResource::collection($this->collection)->resolve();
    }

    /**
     * Additional data to be returned with the resource array.
     */
    public function with($request)
    {
        return [
            'meta' => [
                'status' => 'success',
            ],
        ];
    }
}
