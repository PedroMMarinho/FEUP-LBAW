<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoBid extends Model
{
    public $timestamps = false;
    protected $table = 'auto_bid';
    protected $fillable = ['max', 'auction', 'bidder'];
    protected $casts = [
        'timestamp' => 'datetime',
    ];
    // FK
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder');
    }

    public function myAuction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction');
    }
    public function getMaxValueAttribute()
    {
        return $this->max;
    }

    public static function getActiveAutoBid(int $auctionId, ?int $userId): ?AutoBid
{
    return self::where('auction', $auctionId)
        ->where('bidder', $userId)
        ->where('active', true)
        ->first();
}

}
