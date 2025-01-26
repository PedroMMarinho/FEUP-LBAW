<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;


class ReportUser extends Model
{
    public $timestamps = false;
    protected $table = 'report_user';

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    protected $fillable = [
        'description',
        'reported',
        'reporter',
    ];

    public function reporterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported');
    }
    
    

    public function block(): HasOne
    {
        return $this->hasOne(Block::class, 'report');
    }


    public static function reportsGroupedByUser($perPage)
    {
        return self::select('reported', DB::raw('count(*) as count'))
            ->join('users', 'report_user.reported', '=', 'users.id') 
            ->where('users.blocked', false) 
            ->groupBy('reported')
            ->orderBy('count','desc')
            ->paginate($perPage);
    }
    


}
