<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'branch_id',
        'total_amount',
        'payment_status',
    ];

    protected $primaryKey = 'id';

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

}
