<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Log;

class User extends Model
{

    public $timestamps = false;
    protected $table = 'users';

    protected $fillable = [
        'following_auction_new_bid_notifications',
        'selling_auction_new_bid_notifications',
        'new_message_notifications',
        'new_auction_notifications',
        'following_auction_closed_notifications',
        'following_auction_canceled_notifications',
        'following_auction_ending_notifications',
        'seller_auction_ending_notifications',
        'bidder_auction_ending_notifications',
        'rating_notifications',
    ];

    public function generalUser(): BelongsTo
    {
        return $this->belongsTo(GeneralUser::class, 'id'); 
    }

    // ReportUser
    public function userReportsAsReporter(): HasMany
    {
        return $this->hasMany(ReportUser::class, 'reporter');
    }

    public function userReportsAsReported(): HasMany
    {
        return $this->hasMany(ReportUser::class, 'reported');
    }

    public function myReporter($userId)
    {
        return $this->userReportsAsReported()->where('reporter', $userId)->exists();
    }

    // RateUser
    public function ratesAsRater(): HasMany
    {
        return $this->hasMany(RateUser::class, 'rater_user');
    }

    public function ratesAsRated(): HasMany
    {
        return $this->hasMany(RateUser::class, 'rated_user');
    }

    public function myRating(): float
    {
        return $this->ratesAsRated()->avg('rate') ?? 0.0;
    }

    public function myRater($userId)
    {
        return $this->ratesAsRated()->where('rater_user', $userId)->first();
    } 

    // Subscription
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function totalDaysSubscribed()
    {
        $totalDays = 0;
        foreach ($this->subscriptions as $subscription) {
            $totalDays += ceil($subscription->start_time->diffInDays($subscription->end_time, true));
        }
        return $totalDays;
    }

    public function activeLastSubscription()
    {
        if ($this->subscribed) {
            return $this->subscriptions()->orderBy('end_time', 'desc')->first();
        } else {
            return null;
        }
    }

    public function activeFirstSubscription()
    {
        if ($this->subscribed) {
            return $this->subscriptions()->orderBy('end_time', 'asc')->first();
        } else {
            return null;
        }
    }

    public function isSubscribed(): bool
{
    return $this->subscribed;
}


    // Block
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_user');
    }

    public function currentBlock()
    {
        return $this->blocks()
            ->where('end_time', '>', now())
            ->latest('end_time')
            ->first();
    }

    // Auction
    public function sellingAuctions(): HasMany
    {
        return $this->HasMany(Auction::class, 'seller_id');
    }

    // Report Auction
    public function auctionReports(): HasMany
    {
        return $this->hasMany(ReportAuction::class, 'reporter_id');
    }

    // Auto Bid

    public function autoBids(): HasMany
    {
        return $this->hasMany(AutoBid::class, 'bidder');
    }

    public function activeAutoBids()
    {
        return $this->autoBids()->where('active', true);
    }

    public function autoBidsHoldMoney()
    {
        return $this->activeAutoBids()->get()->sum(function ($autoBid) {
            $topBid = $autoBid->myAuction->bids()->max('value');
            return max(0, $autoBid->max - $topBid);
        });
    }

    // Auctions
    public function auctions():HasMany
    {
        return $this->hasMany(Auction::class, 'seller_id');
    }

    public function totalAuctionsMessages()
    {
    
        $messageCount = 0;
        foreach ($this->auctions as $auction)
        {
            $messageCount += $auction->messages->count();
        }
        return $messageCount;
    }

    public function auctionsParticipated()
    {
        return Auction::whereIn('id', function ($query) {
            $query->select('auction')
                ->from('bid')
                ->where('bidder', $this->id);
        });
    }

    public function totalAuctionsParticipants()
    {
        $auctions = $this->sellingAuctions()->get();
        
        $totalParticipants = $auctions->sum(function ($auction) {
            return $auction->participants();
        });

        return $totalParticipants;
    }


    public function wonAuctions()
    {
        return Auction::where('auction_state', 'Finished')
            ->whereRaw('EXISTS (
                SELECT 1
                FROM bid
                WHERE bid.auction = auction.id
                AND bid.bidder = ?
                AND bid.value = (
                    SELECT MAX(b.value)
                    FROM bid AS b
                    WHERE b.auction = auction.id
                )
            )', [$this->id]);
    }


    public function spentMoneyAuctions()
    {
        $wonAuctions = $this->wonAuctions()->get();

        $totalSpent = $wonAuctions->sum(function($auction) {
            return $auction->highestBidValue();
        });

        return $totalSpent;
    }

    public function spentMoneyAdvertisements()
    {
        $totalSpent = $this->advertisementTransactions()->sum('amount');

        return $totalSpent;
    }

    public function totalDaysAdvertised()
    {

        $totalDays = 0;
        foreach ($this->advertisementTransactions()->get() as $transaction) {
            if ($transaction->advertisement) {
                $advertisement = $transaction->advertisement;
                $totalDays += ceil($advertisement->start_time->diffInDays($advertisement->end_time, true));
            }
        }
        return $totalDays;
    }

    public function numberAdvertisedAuctions()
    {
        return $this->advertisementTransactions()
            ->join('advertisement', 'user_transaction.advertisement_id', '=', 'advertisement.id') // Join with the advertisement table
            ->distinct('advertisement.auction_id') // Ensure only unique auction IDs are counted
            ->count('advertisement.auction_id');  // Count the distinct auction IDs
    }


    public function soldAuctions()
    {
        return $this->auctions()->where('auction_state', 'Finished');
    }

    public function shippedAuctions()
    {
        return $this->auctions()->where('auction_state', 'Shipped');
    }

    public function wonMoney()
    {
        return Auction::getTotalValue($this->shippedAuctions());
    }

    public function hasFinishedAuctions()
    {
        return $this->soldAuctions()->exists();
    }

    public function hasActiveAuctions(): bool
    {
        return $this->auctions()->where('auction_state', 'Active')->exists();
    }
    

    public function toPay()
    {
        return Auction::getTotalValue($this->wonAuctions());
    }

    public function toReceive()
    {
        return Auction::getTotalValue($this->soldAuctions());
    }

    // Bid
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'bidder');
    }

    public function totalBidsReceived(): int
    {
        return $this->sellingAuctions()
            ->join('bid', 'auction.id', '=', 'bid.auction')
            ->count();
    }


    public function topBids()
    {
        return $this->bids()
            ->whereHas('targetAuction', function ($query) {
                $query->where('auction_state', 'Active');
            })
            ->whereRaw('value = (SELECT MAX(value) FROM bid as b WHERE b.auction = bid.auction)');
    }

    public function topBidsValue()
    {
        return $this->topBids()->sum('value');
    }



    // Transactions
    public function sellTransactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class, 'seller_id');
    }

    public function variousTransactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class, 'user_id');
    }

    public function allTransactions()
    {
        return UserTransaction::where('user_id', $this->id)->orWhere('seller_id', $this->id)->get();
    }

    public function advertisementTransactions()
    {
        return UserTransaction::where('user_id', $this->id)
            ->where('transaction_type', 'Advertisement');
    }


    // Follow User  - pivot table

    public function followingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follow_user', 'follower_user', 'followed_user');
    }

    public function totalAuctionsFollowers() : int
    {
        return $this->sellingAuctions()
            ->withCount('followers') 
            ->get()
            ->sum('followers_count'); 
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follow_user', 'followed_user', 'follower_user');
    }
    
    public function myfollower(int $userid): bool 
    {
        return $this->followers()->where('follower_user', $userid)->exists();
    }

    public function following(int $userid): bool 
    {
        return $this->followingUsers()->where('followed_user', $userid)->exists();
    }

    // Follow auction - pivot table

    public function followingAuctions(): BelongsToMany
    {
        return $this->belongsToMany(Auction::class, 'follow_auction', 'follower', 'auction');
    }


}