<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_code',
        'branch_name',
        'branch_address',
    ];

    protected $primaryKey = 'id';
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
