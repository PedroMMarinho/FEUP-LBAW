<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false;
    protected $table = 'notification';
    protected $fillable = [
        'general_user_id',
        'auction',
        'bid',
        'report',
        'block',
        'rate_user',
        'report_auction',
        'timestamp',
        'viewed',
    ];
    // FK
    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'general_user_id');
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction'); 
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'bid'); 
    }

    public function userReport(): BelongsTo
    {
        return $this->belongsTo(ReportUser::class, 'report'); 
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'block'); 
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(RateUser::class, 'rate_user'); 
    }

    public function auctionReport(): BelongsTo
    {
        return $this->belongsTo(ReportAuction::class, 'report_auction'); 
    }
}
