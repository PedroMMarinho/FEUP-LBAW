<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    
    public $timestamps = false;
    protected $table = 'admin';

    // FK
    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'id'); 
    }


    // Adminchange
    public function changes(): HasMany
    {
        return $this->hasMany(AdminChange::class, 'admin');
    }

    // Block
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'block_admin');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(Block::class, 'appeal_admin');
    }
    

}
