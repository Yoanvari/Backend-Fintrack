<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'is_locked' => $this->is_locked,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->branch_name,
            ],

            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->category_name,
                'type' => $this->category->category_type,
            ],
        ];
    }
}
