<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budget extends Model
{
    use HasFactory;

    protected $fillabel = [
        'master_budget_id',
        'user_id',
        'category_id',
        'name',
        'amount',
        'description',
    ];

    protected $primaryKey = 'id';

    public function masterBudget() {
        return $this->belongsTo(MasterBudget::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
