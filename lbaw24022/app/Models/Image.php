<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    public $timestamps = false;
    protected $table = 'image';

    protected $fillable = [
        'path',
        'general_user_id',
        'auction',
    ];

    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'general_user_id');
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction');
    }

    
}
