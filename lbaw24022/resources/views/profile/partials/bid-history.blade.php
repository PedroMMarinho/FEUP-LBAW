
<section class="w-[600px] 2xl:w-[400px] col-span-2 row-start-1 col-start-1 2xl:row-start-1 2xl:col-start-2 mx-auto ">

    <x-section-header title="Bid History" class="!ml-0 text-center mb-5 mt-3"/>

    <div class="bg-white rounded-md p-4">

        <div class="grid grid-cols-[100px,1fr,70px] border-b-2 border-blue-500 py-2">
            <p class="text-center">Amount (€)</p>
            <p class="text-center">Auction</p>
            <p class="text-center">Date</p>
        </div>


        @php
            $bids = Auth::user()->user->bids->reverse();
        @endphp
        <div class="flex justify-center h-[28.3rem] overflow-y-scroll overflow-x-hidden hide-scrollbar fade">
            @if ($bids->isEmpty())
                <p class="text-gray-300 font-bold text-center self-center text-3xl">Bid history empty</p>
            @else
                <ul class="w-full mb-32">
                    @foreach ($bids as $bid)
                        <li class="grid grid-cols-[100px,auto,75px] w-full border-b-2 py-4">
                            <p class="text-center">{{$bid->value}}</p>
                            <p class="text-center">{{$bid->targetAuction->name}}</p>
                            <p class="text-center pr-1">{{$bid->timestamp->format('d/m/y')}}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        

    </div>



</section>