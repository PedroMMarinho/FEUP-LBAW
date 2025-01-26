<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    
    public $timestamps = false;
    protected $table = 'subscription';

    protected $fillable = [
        'start_time',
        'end_time',
        'cost',
        'user_id',
    ];

    protected $casts = [
        'end_time' => 'datetime',
        'start_time' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
