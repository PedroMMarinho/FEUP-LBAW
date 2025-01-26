<section>
    <x-section-header
        :title="'Transaction History'">
    </x-section-header>
    <div class="min-w-full bg-white p-5 h-96">

        <div class="grid grid-cols-6 border-b-2 border-blue-500 pb-3 p-3 mx-3">
            <p class="text-center">Type</p>
            <p class="text-center">Amount (€)</p>
            <p class="text-center">Auction</p>
            <p class="text-center">Start Date</p>
            <p class="text-center">Duration (Days)</p>
            <p class="text-center">Date</p>
        </div>
        <div class="max-h-72 h-full flex justify-around mx-3 overflow-y-scroll hide-scrollbar fade">

            @php
                $allTransactions = Auth::user()->specificRole()->allTransactions()->reverse();
            @endphp
        
        @if ($allTransactions->isNotEmpty())
                
            <ul class="max-h-96 grow">
                @foreach ( Auth::user()->specificRole()->allTransactions()->reverse() as $transaction)
                    <li class="grid grid-cols-6 border-b-2 p-3 m-1">
                        @php
                            $type = $transaction->transaction_type;
                            $timestamp = $transaction->timestamp;
                            $formatedTimestamp= $timestamp->format("d/m/Y");
                            $amount = $transaction->amount;
                            $duration = "-";
                            $auction = "-";
                            $startDate = "-";

                            if ($type == "Auction")
                            {
                                $auction = $transaction->winnerBid->targetAuction->title;
                            }elseif ($type == "Advertisement") {
                                $advertisement = $transaction->advertisement;
                                $auction = $advertisement->auction->name;
                                $duration = ceil($advertisement->end_time->diffInDays($timestamp, true));
                                $startDate = $advertisement->start_time->format("d/m/Y");
                            }elseif ($type == "Subscription") {
                                $subscription = $transaction->subscription;
                                $duration = ceil($subscription->end_time->diffInDays($subscription->start_time, true));
                                $startDate = $subscription->start_time->format("d/m/Y");
                            }elseif ($type == "Wallet")
                            {
                                $type = $amount > 0 ? "Deposit" : "Withdraw";
                            }
                        @endphp

                        <p class="text-center">{{$type}}</p>
                        <p class="text-center">{{$amount}}</p>
                        <p class="text-center">{{$auction}}</p>
                        <p class="text-center">{{$startDate}}</p>
                        <p class="text-center">{{$duration}}</p>
                        <p class="text-center">{{$formatedTimestamp}}</p>


                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-300 font-bold self-center text-5xl">Transaction history empty</p>

        @endif
        </div>

    </div>
</section>