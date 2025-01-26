<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateUser extends Model
{
    public $timestamps = false;
    protected $table = 'rate_user';

    protected $fillable = [
        'rate',
        'rated_user',
        'rater_user',
    ];


    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_user');
    }

    public function rated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user');
    }

}
