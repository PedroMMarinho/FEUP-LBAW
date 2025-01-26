<?php


namespace App\Providers;

use App\Models\Auction;
use App\Policies\AuctionPolicy;
use App\Policies\WalletPolicy;
use App\Policies\GeneralUserPolicy;
use App\Models\GeneralUser;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;


class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Auction::class => AuctionPolicy::class,
        GeneralUser::class => GeneralUserPolicy::class,
    ];

    public function boot() : void
    {
        $this->registerPolicies();
    }
}