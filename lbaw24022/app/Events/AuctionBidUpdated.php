<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Auction;
use Illuminate\Support\Facades\Log;

class AuctionBidUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $bids;
    public $siteConfigBidInterval;
    public $surprassedBidder;

    public function __construct($bids, $siteConfigBidInterval,$surprassedBidder)
    {
        $this->bids = $bids;
        $this->siteConfigBidInterval = $siteConfigBidInterval;
        $this->surprassedBidder = $surprassedBidder;
    }
    

    public function broadcastOn()
    {
        $firstBid = $this->bids[0];

    // Now return the auction channel, using the auction ID from the first bid
        return 'auction.' . $firstBid['auctionId'];
    }

    public function broadcastWith()
    {
        // Return the JSON structure with all bids and other info
        return [
            'bids' => $this->bids,  // List of all bids with the associated data
            'bidInterval' => $this->siteConfigBidInterval,  // The bid interval from your config
            'surprassedBidder' => $this->surprassedBidder,
        ];
    }
    public function broadcastAs()
    {
        // This sets the event name to 'my-event'
        return 'my-event';
    }
}
