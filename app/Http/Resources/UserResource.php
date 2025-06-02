<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'branch_name' => $this->branch->branch_name,
                'branch_code' => $this->branch->branch_code,
                'branch_address' => $this->branch->branch_address,
            ] : null,
        ];
    }
}
