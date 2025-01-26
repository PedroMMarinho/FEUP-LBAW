<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    public $timestamps = false;
    protected $table = 'bid';
    protected $fillable = ['value', 'auction', 'bidder'];
    protected $casts = [
        'timestamp' => 'datetime',
    ];
    
    // FK
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder');
    }

    public function targetAuction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction');
    }
}
