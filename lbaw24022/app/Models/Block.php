<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    
    public $timestamps = false;
    protected $table = 'block';

    protected $casts = [
        'end_time' => 'datetime',
    ];

    protected $fillable = [
        'block_message',
        'block_admin',
        'blocked_user',
        'end_time'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ReportUser::class, 'report');
    }

    public function blockAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'block_admin');
    }

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user');
    }

    public function appealAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'appeal_admin');
    }

    public static function getNewAppeals($perPage)
    {
        return self::whereNotNull('appeal_message')
        ->whereNull('appeal_accepted')
        ->where('end_time', '>', \Carbon\Carbon::now())
        ->paginate($perPage);
    }

}
