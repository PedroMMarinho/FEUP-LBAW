<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public $timestamps = false;
    protected $table = 'message';
    protected $fillable = [
        'content',
        'auction',
        'general_user_id',
    ];

    // If you want to work with the timestamp as Carbon instance
    
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction'); 
    }

    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'general_user_id');
    }
    public function getMessagesByAuction($auctionId)
{
    // Retrieve messages ordered by timestamp for the given auction
    $messages = Message::where('auction', $auctionId)  // Filter by auction id
                ->orderBy('timestamp', 'asc')  // Order by timestamp (ascending)
                ->get();

    // Return the messages in JSON format
    return response()->json($messages);
}


}
