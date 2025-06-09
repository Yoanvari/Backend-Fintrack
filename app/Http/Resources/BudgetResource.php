<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
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
            'period' => $this->period->format('Y-m-d'),
            'submission_date' => $this->submission_date->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'revision_note' => $this->revision_note,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->branch_name,
            ],
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
