<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
{
    public $timestamps = false;
    protected $table = 'user_transaction';

    protected $casts = [
        'timestamp' => 'datetime',
    ];


    protected $fillable = [
        'amount',
        'transaction_type',
        'winner_bid',
        'seller_id',
        'user_id',
        'advertisement_id',
        'subscription_id',
    ];

    // FK
    public function winnerBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'winner_bid');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

}
