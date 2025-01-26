


<section class="">

    @php
        $user = Auth::user()->specificRole();
    @endphp

    <div class="text-4xl font-extrabold">
        <p class="xl:mb-5 mb-3">Total: {{$user->wallet}}€</p>
        <p>Available: {{$user->available_balance}}€</p>
    </div>

    <div class="py-7 xl:pl-6">
        <h2 class="text-2xl font-semibold">Currently in hold: </h2>
        <div class="xl:ml-3">
            <p>Top-Bids: {{$user->topBidsValue()}}€</p>
            <p>Auto-bids: {{$user->autoBidsHoldMoney()}}€</p>
        </div>
    </div>

    <div class="xl:pl-6">
        <h2 class="text-2xl font-semibold" >Waiting for ship: </h2>
        @php
            $wonAuctions = $user->wonAuctions();
            $soldAuctions = $user->soldAuctions();
        @endphp
        <div class="xl:ml-3">
        @if ($wonAuctions->count() == 0)
            
            <p>No won auctions</p>
        @else
            <p>{{$wonAuctions->count()}} won auctions: -{{$user->toPay()}}€</p>
        @endif

        @if ($soldAuctions->count() == 0)
            
            <p>No sold auctions</p>
        @else
            <p>{{$soldAuctions->count()}} sold auctions: +{{$user->toReceive()}}€</p>
        @endif
        </div>


    </div>

</section>