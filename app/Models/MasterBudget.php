<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'name',
        'total_amount',
        'start_date',
        'end_date',
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}
