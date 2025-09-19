<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reversal extends Model
{
    protected $fillable = [
        'transaction_id',
        'requested_by',
        'reason',
    ];

}
