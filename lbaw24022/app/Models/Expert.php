<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expert extends Model
{
    public $timestamps = false;
    protected $table = 'expert';


    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'id'); 
    }

    // Category
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'expert_category', 
        'expert', 'category_id');
    }


    // Auction
    public function evaluatedAuctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'expert');
    }
}
