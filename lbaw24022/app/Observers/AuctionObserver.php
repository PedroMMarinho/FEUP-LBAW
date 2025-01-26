<?php

namespace App\Observers;

use App\Models\Auction;
use Illuminate\Support\Facades\Log;

class AuctionObserver
{

    public function retrieved(Auction $auction)
    {
        if ($auction->auction_state === 'Active' && $auction->end_time <= now()) {
            Log::info("Updated auction");
            if ($auction->bids->count() == 0) $auction->auction_state = "Finished without bids";
            else $auction->auction_state = 'Finished';
            $auction->save(); 
        }
    }

    /**
     * Handle the Auction "created" event.
     */
    public function created(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "updated" event.
     */
    public function updated(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "deleted" event.
     */
    public function deleted(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "restored" event.
     */
    public function restored(Auction $auction): void
    {
        //
    }

    /**
     * Handle the Auction "force deleted" event.
     */
    public function forceDeleted(Auction $auction): void
    {
        //
    }
}
