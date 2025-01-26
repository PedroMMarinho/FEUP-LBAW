<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class ReportAuction extends Model
{
    public $timestamps = false;
    protected $table = 'report_auction';


    protected $casts = [
        'timestamp' => 'datetime',
    ];

    protected $fillable = [
        'description',
        'auction',
        'reporter',
    ];


    public function reporterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter');
    }

    public function reportedAuction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction');
    }

    public static function reportsGroupedByAuction($perPage)
    {
        return self::select('auction.id', 'auction.name', DB::raw('count(report_auction.id) as count'))
            ->join('auction', 'report_auction.auction', '=', 'auction.id')
            ->groupBy('auction.id', 'auction.name')
            ->where('auction.auction_state', 'Active')
            ->orderBy('count', 'desc')
            ->paginate($perPage);
    }
    

}
