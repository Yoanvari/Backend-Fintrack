<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'transaction_id',
        'amount',
    ];

    public function budget() {
        return $this->belongsTo(budget::class);
    }

    public function transaction() {
        return $this->belongsTo(transaction::class);
    }
}
