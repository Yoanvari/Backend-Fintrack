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

    protected $primaryKey = 'id';

    // Relasi ke Branch
    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    // Tambahkan relasi ke User agar lebih lengkap
    public function user() {
        return $this->belongsTo(User::class);
    }
}
