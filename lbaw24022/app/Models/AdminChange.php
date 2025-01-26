<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminChange extends Model
{
    public $timestamps = false;
    protected $table = 'admin_change';

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    protected $fillable = [
        'description',
        'admin',
    ];

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin'); 
    }
}
