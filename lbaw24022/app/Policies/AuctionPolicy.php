<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\GeneralUser;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;
class AuctionPolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(GeneralUser $generalUser, Auction $auction): bool
    {   
        return ($auction->seller == $generalUser->id && !$generalUser->user->blocked) || $generalUser->role == "Admin";
    }


    public function advertise(GeneralUser $generalUser, Auction $auction) : bool
    {
        return ($auction->seller == $generalUser->specificRole()) && ($generalUser->role == "Regular User") && ($generalUser->user->blocked == false);
    }

    

    public function follow(GeneralUser $generalUser, Auction $auction) : bool
    {
        return $generalUser->id !== $auction->seller->id && $generalUser->role == "Regular User" && !$generalUser->user->blocked;
    }

    public function report(GeneralUser $generalUser, Auction $auction) : bool
    {
        return $generalUser->id !== $auction->seller->id && $generalUser->role == "Regular User" && $generalUser->user->blocked == false;
    }

}
