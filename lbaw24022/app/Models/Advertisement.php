<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    
    public $timestamps = false;
    protected $table = 'advertisement';

    protected $casts = [
        'end_time' => 'datetime',
        'start_time' => 'datetime',
    ];

    protected $fillable = [
        'end_time',
        'start_time',
        'cost',
        'auction_id',
    ];

    // FK
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

}
