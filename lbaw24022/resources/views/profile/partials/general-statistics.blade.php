


<section>
    <div class="grid 2xl:grid-cols-[5fr,2fr] 2xl:grid-rows-[1fr] grid-cols-[1fr] grid-rows-[1fr, 1fr] items-center  gap-4 mb-10 px-10 2xl:px-0">

        <div class="2xl:row-start-1 2xl:col-start-1 2xl:col-span-1 col-span-2 col-start-1 row-start-2 ">
            <x-section-header title="Statistics" class="text-center mt-3 mb-5 !ml-0"></x-section-header>
            <div class="flex flex-wrap gap-16 justify-center">
                <x-information-card 
                    mainText="{{formatNumber($user->user->wonAuctions()->count())}}"
                    dataType=""
                    description="won auctions"
                />

                <x-information-card 
                    mainText="{{formatNumber($user->user->bids->count())}}"
                    dataType=""
                    description="bids placed"
                />

                <x-information-card 
                    mainText="{{formatNumber($user->user->autoBids->count())}}"
                    dataType=""
                    description="auto-bids created"
                />

                <x-information-card 
                    mainText="{{formatNumber($user->user->soldAuctions->count())}}"
                    dataType=""
                    description="sold auctions"
                />

                <x-information-card 
                    mainText="{{formatNumber($user->user->totalBidsReceived())}}"
                    dataType=""
                    description="bids received"
                />

                @php
                    $auctionFollowers = $user->user->totalAuctionsFollowers();
                    $userFollowers = $user->user->followers->count();
                    $info = ["auctions" => $auctionFollowers, "you" => $userFollowers];
                @endphp
                <x-information-card 
                    mainText="{{formatNumber($auctionFollowers + $userFollowers)}}"
                    dataType=""
                    description="followers"
                    extra="true"
                    secondaryText="The total number of users following you directly and your auctions."
                    :extraPoints="$info"
                />


            </div>
        </div>

        @include('profile.partials.bid-history')

    </div>

</section>