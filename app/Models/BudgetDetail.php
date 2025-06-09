<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'category_id',
        'budget_id',
        'description',
        'amount'
    ];

    protected $primaryKey = 'id';

    public function budget() {
        return $this->belongsTo(Budget::class);
    }

    public function category() {
        return $this->belongsTo(category::class);
    }
}
