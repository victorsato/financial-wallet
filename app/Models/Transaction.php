<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'user_from_id',
        'user_to_id',
        'type',
        'amount',
        'status',
        'reference_id',
    ];

    public function user_from(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_from_id');
    }

    public function user_to(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_to_id');
    }
}
