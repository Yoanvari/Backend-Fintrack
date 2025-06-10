<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BudgetDetail;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'period',
        'submission_date',
        'status',
        'revision_note',
    ];

    protected $primaryKey = 'id';

    protected $casts = [
        'submission_date' => 'datetime',
        'period' => 'date',
    ];

    protected static function booted() {
        static::deleting(function ($budget) {
            $budget->detail()->delete();
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function detail() {
        return $this->hasMany(BudgetDetail::class);
    }
}
