<section class=" px-10 2xl:px-0">
    <x-section-header title="Advanced Statistics" class="text-center mt-3 mb-5 !ml-0"></x-section-header>

    @php
        $auctionsSpent = $user->user->spentMoneyAuctions();
        $subscriptionsSpent = $user->user->subscriptions->sum('cost');
        $advertiseSpent = $user->user->spentMoneyAdvertisements();
    @endphp


    <div class="grid grid-cols-[1fr,1fr,1fr] grid-rows-[auto,auto,auto] gap-4 mb-5 ">

        <x-information-card 
            mainText="{{formatNumberSpace($auctionsSpent + $subscriptionsSpent + $advertiseSpent)}}"
            dataType="€"
            description="money spent"
            class="m-auto !w-full mb-7 col-span-3 col-start-1 !lg:w-full  lg:col-span-1 lg:col-start-2 lg:row-start-1 2xl:col-start-1 2xl:col-span-3 "
        />

        <div class="  flex flex-wrap gap-16 justify-center   col-start-1 col-span-3 row-span-1 lg:row-span-2 2xl:col-span-2 ">
            <x-information-card 
                mainText="{{formatNumber($auctionsSpent)}}"
                dataType="€"
                description="in auctions"
            />

            <x-information-card 
                mainText="{{formatNumber($subscriptionsSpent)}}"
                dataType="€"
                description="in subscriptions"
            />

            <x-information-card 
                mainText="{{formatNumber($advertiseSpent)}}"
                dataType="€"
                description="in advertisements"
            />
            @php
                $wonAuctions = $user->user->wonAuctions()->count();
                $participatedAuctions = $user->user->auctionsParticipated()->count();
            @endphp

            <x-information-card 
                mainText="{{ $participatedAuctions > 0 ? formatNumber(($wonAuctions / $participatedAuctions) * 100) : '0' }}"
                dataType="%"
                description="win percentage"
                extra="true"
                secondaryText="How often you win auctions you have participated in"
                :extraPoints="['won' => $wonAuctions, 'participated' => $participatedAuctions]"
            />

            <x-information-card 
                mainText="{{formatNumber($user->user->totalDaysSubscribed())}}"
                dataType="DAYS"
                description="subscribed"
            />

            @php
                $numberOfAuctions = $user->user->numberAdvertisedAuctions();
                $auctionText = $numberOfAuctions === 1 ? 'auction' : 'auctions' . ' advertised';
            @endphp
            <x-information-card 
                mainText="{{formatNumber($user->user->totalDaysAdvertised())}}"
                dataType="DAYS"
                description="advertised"
                extra="true"
                secondaryText="Total days of advertisement across all your auctions"
                :extraPoints="[$auctionText => $user->user->numberAdvertisedAuctions()]"
            />
        </div>
        
            <div class=" row-start-2 col-start-1   lg:row-start-1 lg:col-start-1 2xl:row-start-2 2xl:col-start-3 h-56 bg-white flex justify-center items-center rounded-xl p-2">
                @include('profile.partials.pie-chart-spent')
            </div>
            <div class="row-start-2 col-span-2 lg:row-start-1 lg:col-start-3 2xl:row-start-3 2xl:col-start-3  h-56 bg-white flex justify-center items-center rounded-xl  2xl:self-end mb-7  2xl:mb-1 p-2">
                @include('profile.partials.spent-line-chart')
            </div>

    </div>


    <div class="grid grid-cols-[auto,1fr] grid-rows-[auto,auto,auto] gap-4 mb-5">

        <x-information-card 
            mainText="{{formatNumberSpace($user->user->sellTransactions()->sum('amount'))}}"
            dataType="€"
            description="money won"
            class="m-auto !w-full lg:mb-7 mt-12 col-span-3 col-start-1 !lg:w-full lg:col-span-2 lg:col-start-2 lg:row-start-1 2xl:col-start-1 2xl:col-span-3"
        />

        <div class="flex flex-wrap gap-16 justify-center  col-start-1 col-span-3 row-span-1 lg:row-span-2 2xl:col-span-2  ">
            <x-information-card 
                mainText="{{formatNumber($user->user->totalAuctionsParticipants())}}"
                dataType=""
                description="participants"
                extra="true"
                secondaryText="The total number of different users that have participated in one of your auctions"
            />

            <x-information-card 
                mainText="{{formatNumber($user->user->auctions->sum('evaluation'))}}"
                dataType="€"
                description="total evaluation"
                extra="true"
                secondaryText="The total value assessed by experts for your auctions"
                :extraPoints="['evaluated' => $user->user->auctions->whereNotNull('evaluation')->count()]"

            />

            <x-information-card 
                mainText="{{formatNumber($user->user->totalAuctionsMessages())}}"
                dataType=""
                description="messages"
            />

        </div>

        <div class="bg-white h-56 rounded-xl p-2 mb-4 mt-0 row-start-2 col-span-2 col-start-1 lg:col-span-1 lg:row-start-1 lg:col-start-1 lg:mt-5 2xl:mt-0 2xl:row-span-2 2xl:row-start-2 2xl:col-start-3 self-center">
            <div class="flex justify-center h-full items-center">
                @include('profile.partials.won-line-chart')
            </div>
        </div>
    </div>

</section>
