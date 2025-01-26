    
@props([
  'adCost' => 0.30,
  'adDiscountedCost' => 0.10
])

@php
    $isSeller = Auth::check() && Auth::id() == $auction->seller_id;
    $isExpert = Auth::check() && Auth::user()->role === "Expert";
    $isAdmin = Auth::check() && Auth::user()->role === "Admin";
    $isSubscribed = Auth::check() && Auth::user()->role === 'Regular User' && Auth::user()->user->subscribed;
    $isActiveAuction =  $auction->auction_state === 'Active';
    $adEndTime = optional($auction->activeLastAdvertisement())->end_time;
@endphp

<x-app-layout>

  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:flex lg:justify-between">
      <div class="auction-name text-2xl sm:text-4xl font-bold col-span-2 text-start row-start-1 flex items-end" auction-id="{{ $auction->id }}">
        <h2>{{ $auction->name }}</h2>
      </div>
        @if($isActiveAuction)
          <div class="countdown font-bold col-span-1 row-start-1 flex items-center justify-between lg:justify-normal">
            <p id="closes" class="countdown-text text-xl sm:text-2xl mr-8">Closes in 
                <span id="countdown" data-endtime="{{ $auction->end_time }}" class="countdown-text"></span>
            </p>
            <div class="flex gap-8">
            @auth 
              @if ($isSeller || $isAdmin)
                <div id="sellerOptionsButton" class="cursor-pointer flex">
                  <svg fill="#000000" width="28px" height="28px" viewBox="0 0 1920.00 1920.00" xmlns="http://www.w3.org/2000/svg" stroke="#000000" stroke-width="0.019200000000000002"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M1703.534 960c0-41.788-3.84-84.48-11.633-127.172l210.184-182.174-199.454-340.856-265.186 88.433c-66.974-55.567-143.323-99.389-223.85-128.415L1158.932 0h-397.78L706.49 269.704c-81.43 29.138-156.423 72.282-223.962 128.414l-265.073-88.32L18 650.654l210.184 182.174C220.39 875.52 216.55 918.212 216.55 960s3.84 84.48 11.633 127.172L18 1269.346l199.454 340.856 265.186-88.433c66.974 55.567 143.322 99.389 223.85 128.415L761.152 1920h397.779l54.663-269.704c81.318-29.138 156.424-72.282 223.963-128.414l265.073 88.433 199.454-340.856-210.184-182.174c7.793-42.805 11.633-85.497 11.633-127.285m-743.492 395.294c-217.976 0-395.294-177.318-395.294-395.294 0-217.976 177.318-395.294 395.294-395.294 217.977 0 395.294 177.318 395.294 395.294 0 217.976-177.317 395.294-395.294 395.294" fill-rule="evenodd"></path> </g></svg>
                  <svg width="32px" height="32px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M5.70711 9.71069C5.31658 10.1012 5.31658 10.7344 5.70711 11.1249L10.5993 16.0123C11.3805 16.7927 12.6463 16.7924 13.4271 16.0117L18.3174 11.1213C18.708 10.7308 18.708 10.0976 18.3174 9.70708C17.9269 9.31655 17.2937 9.31655 16.9032 9.70708L12.7176 13.8927C12.3271 14.2833 11.6939 14.2832 11.3034 13.8927L7.12132 9.71069C6.7308 9.32016 6.09763 9.32016 5.70711 9.71069Z" fill="#000000"></path> </g></svg>
                </div>
              @endif

              @can('follow', $auction)
                <button id="follow-btn" auction-id="{{ $auction->id }}" class="flex flex-col place-items-center w-8">
                  <svg viewBox="0 0 512 512" class="heart-icon w-5 h-5" style="fill: {{ ($auction->isFollowedBy(Auth::user()->id)) ? 'red' : 'black' }}">
                    <path d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/>
                  </svg>
                  <p class="text-xs" id="followText">{{($auction->isFollowedBy(Auth::user()->id)) ? 'Following' : 'Follow'}}</p>
                </button>
              @endcan

              @can('report', $auction)
                <div class="cursor-pointer flex flex-col place-items-center"
                  id="report-btn"
                  x-data=""
                  @if (!$auction->isReportedBy(Auth::id()))
                    x-on:click.prevent="$dispatch('open-modal', 'auction-report-forms')"
                  @else
                    x-on:click.prevent="$dispatch('notify', { message: 'Auction Already Reported', type: 'error' })"
                  @endif
                  >
                  <svg class="report-icon hover:scale-105 transition-transform" style="fill: {{ ($auction->isReportedBy(Auth::id())) ? '#aa0000' : '#000000' }}" height="25px" width="25px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:#000000;} </style> <g> 
                      <path class="" d="M387.317,0.005H284.666h-57.332h-102.65L0,124.688v102.67v57.294v102.67l124.684,124.674h102.65h57.332 h102.651L512,387.321v-102.67v-57.294v-102.67L387.317,0.005z M255.45,411.299c-19.082,0-34.53-15.467-34.53-34.549 c0-19.053,15.447-34.52,34.53-34.52c19.082,0,34.53,15.467,34.53,34.52C289.98,395.832,274.532,411.299,255.45,411.299z M283.414,278.692c0,15.448-12.516,27.964-27.964,27.964c-15.458,0-27.964-12.516-27.964-27.964l-6.566-135.368 c0-19.072,15.447-34.54,34.53-34.54c19.082,0,34.53,15.467,34.53,34.54L283.414,278.692z"></path> </g> </g>
                  </svg>
                  <p class="text-xs select-none" id="blockText">{{($auction->isReportedBy(Auth::user()->id)) ? 'Reported' : 'Report'}}</p>
                </div>
              @endcan
            @endauth
            </div>
          </div>
        @else
          <div class="font-bold col-span-1 row-start-1 flex items-center ">
            <p class="mr-12 text-3xl">
              {{$auction->auction_state}}
            </p>
          </div>
        @endif
    </h2>
  </x-slot>
  
  @can('report', $auction)
    <x-modal name="auction-report-forms" :show="$errors->auctionReport->isNotEmpty()" focusable>
      <form id="report-form" method="post" action="{{ route('auction.report', ['id' => $auction->id]) }}" class="p-6">
        @csrf
        @method('post')
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Are you sure you want to report this auction?') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Please provide the reason for the report.') }}
        </p>

        <div class="mt-6">
            <x-input-label for="motive" value="{{ __('Motive') }}" class="sr-only" />
            <x-text-input
                id="motive"
                name="motive"
                type="text"
                class="mt-1 block w-3/4"
                placeholder="{{ __('Motive') }}"
                value="{{ old('motive') }}"
            />

            <x-input-error :messages="$errors->auctionReport->get('motive')" class="mt-2" />
        </div>
        <div class=" flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-primary-button class="ms-3">
                {{ __('Report') }}
            </x-primary-button>
        </div>
      </form>
    </x-modal>
  @endcan


  @if ($isSeller || $isAdmin)
  <x-modal name="auction-followers-list" focusable>
    <div class="p-5 max-h-[80vh] overflow-y-scroll hide-scrollbar fade">
      <div class="text-center font-bold text-2xl mb-10" >Auction Followers</div>
      <ul class="flex flex-wrap justify-center gap-5">
        @if ($auction->followers->count() == 0)
          <li class="text-2xl text-gray-300 font-bold my-5">No followers yet</li>
        @else
          @foreach ($auction->followers as $follower)
            <li class="text-center items-center mt-4 mb-5 hover:bg-slate-50 p-2 rounded-sm cursor-pointer select-none" onclick="route()">
              <a href="{{route("profile.show", $follower->id)}}">
                <div class="col-start-1 col-end-2 flex justify-center items-center">
                    <img src="{{$follower->generalUser->getProfileImage()}}" class="aspect-square object-cover rounded-full max-h-24" alt="Profile Image">
                </div>
                <div class="col-start-1 col-end-2 mt-2 text-sm sm:text-base overflow-hidden text-ellipsis">
                    {{$follower->generalUser->username}}
                </div>
              </a>
            </li>
            <hr class="h-4">
          @endforeach
        @endif
      </ul>
    </div>
  </x-modal>

  @endif


  @admin
  <x-modal name="auction-report-list" focusable>
    <div class="p-5 max-h-[80vh] overflow-y-scroll hide-scrollbar fade">
      <div class="text-center font-bold text-2xl mb-10" >Auction Reports</div>
      <ul class="">
        @if ($auction->reports->count() == 0)
          <li class="text-2xl text-gray-300 font-bold my-5 justify-self-center">No reports yet</li>
        @else
            @foreach ($auction->reports as $report)
              @php
                $user = $report->reporterUser;
                $generalUser = $user->generalUser;
              @endphp
                  <li class="mt-4 mb-5 hover:bg-slate-100 p-4 cursor-pointer rounded-sm transition-all">
                    <a href="{{route('profile.show', $user->id)}}" class="grid grid-cols-4 gap-x-4 text-center items-center">
                      <div class="select-none">
                        <div class="col-start-1 col-end-2 flex justify-center items-center">
                            <img src="{{$generalUser->getProfileImage()}}" class="aspect-square object-cover rounded-full max-h-24" alt="Profile Image">
                        </div>
                        <div class="col-start-1 col-end-2 mt-2 text-sm sm:text-base overflow-hidden text-ellipsis">
                            {{$generalUser->username}}
                        </div>

                      </div>
                      <div class="col-start-2 col-end-4 text-sm sm:text-base select-none">
                          {{$report->description}} 
                      </div>
                      <div class=" col-start-4 col-end-5  text-sm sm:text-base select-none">
                          {{$report->timestamp->format('d/m/Y H:i') }}
                      </div>                
                    </a>
                  </li>

                  <hr class="h-4">
            @endforeach

        @endif
      </ul>
    </div>
  </x-modal>
@endadmin

@if($isSeller || $isAdmin)
  <div class="bg-white border-gray-100 border-t-2 h-min transition-all overflow-hidden duration-300 ease-in {{ session('settings') ? 'max-h-[400px]' : 'max-h-0' }}" id="sellerOptions">
      <div class="max-w-7xl m-auto px-7 xl:px-20 pt-4">
        <p class="w-full text-xl font-semibold mb-5 text-center lg:text-left">
              @if ($auction->advertised)
                  @php
                      $remainingDays = ceil($adEndTime->diffInDays(now(), true));
                      $endDiff = floor($auction->end_time->diffInDays($adEndTime, true));
                  @endphp
                  {{ $endDiff == 0 
                      ? 'Advertised - until auction ends'
                      : "Advertised - $remainingDays " . ($remainingDays == 1 ? "day" : "days") . ' remaining' }}
              @else
                  Not advertised
              @endif
          </p>

        <div class="flex  justify-center lg:justify-between flex-wrap mb-2">

            @admin
            <div class="flex items-center mb-5">
              <p class="font-semibold text-lg mr-4">Reported {{ $auction->reports->count() }} {{ $auction->reports->count() === 1 ? 'time' : 'times' }}</p>
              <x-secondary-button
                class="!text-base"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'auction-report-list')">
              See reports </x-secondary-button>
            </div>
            @endadmin

          @regularuser
            <form id="send-verification"
              method="post"
              action="{{ route('verification.send') }}"
              class="hidden">
              @csrf
            </form>

            <form method="post"
              action="{{ route('auction.advertise', ['id' => $auction->id]) }}">
              @csrf

            <input type="number" class="hidden" name="auctionId" value="{{$auction->id}}">

            <div class="flex items-center ml-5">

              <div class="mr-9">
                <label for="advertiseDateInput" class="block"> End Day </label>

                @if ($auction->advertised)
                  <input type="date" 
                    min="{{$adEndTime->toDateString()}}"
                    max="{{$auction->end_time->toDateString()}}"
                    value="{{$adEndTime->toDateString()}}"
                    id="advertiseDateInput"
                    name="endDate">
                @else
                  <input type="date"
                    min="{{now()->toDateString()}}"
                    max="{{$auction->end_time->toDateString()}}"
                    value="{{now()->toDateString()}}"
                    id="advertiseDateInput"
                    name="endDate">
                @endif

              </div>

              @php
                $subscribed = Auth::user()->specificRole()->subscribed;
              @endphp
              @if (floor($auction->end_time->diffInDays($adEndTime, true)) == 0)
                <p class="text-lg font-semibold mt-4">No more advertising</p>
              @else

                <div class="w-28 overflow-hidden mr-4">
                  <p class="mt-6 text-xl font-semibold" id="advertisePrice">0 €</p>
                  <div class="flex">
                    @if ($subscribed)
                      <p class="text-sm mr-1 line-through">{{$adCost * 100}}</p>
                    @endif
                      <p class="text-sm" id="dayPrice">{{$subscribed ? $adDiscountedCost * 100 : $adCost * 100}} cents / day</p>
                  </div>
                </div>
                
                <x-secondary-button class="mt-6 !text-base" type="submit">{{$auction->advertised ? "Extend" : "Advertise"}}</x-secondary-button>
              @endif
              </div>
                  <x-input-error :messages="$errors->get('endDate')" class="mt-1 ml-5"/>
            </form>
          @endregularuser


            <div class="{{auth()->user()->role == "Admin" ? '' : 'lg:mt-6'}} ml-12 lg:mb-0 mb-4">

              <x-secondary-button
                class="px-4 !py-2 !text-base !font-semibold mr-4"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'auction-followers-list')"
              > Followers </x-secondary-button>
              <a href="{{route("auction.edit.show", $auction->id)}}">
                <x-primary-button
                  class="px-4 !py-2 !text-base !font-semibold mr-4"
                > Edit </x-primary-button>
              </a>
              <x-danger-button 
                class="px-4 !py-2 !text-base"
                id='cancel-auction-btn'
                auction-id="{{ $auction->id }}"
              > Cancel </x-danger-button>
            </div>
        </div>
        @admin
          <div class="h-4"></div>
        @endadmin
      </div>
    </div>
  @endif



<div class="min-h-screen py-10 px-10 bg-cover bg-center grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 grid-rows-4 md:grid-rows-3 lg:grid-rows-2 gap-4 max-w-7xl m-auto" >       
  <!-- Auction images (9) -->
  <div class="auction-images col-span-full lg:col-start-1 lg:col-end-3 row-start-1 row-end-2 p-4 rounded-lg border-4 border-gray-300 shadow-lg bg-white">    

     <div class="swiper" id="swiper-1">

        <div class="swiper-wrapper py-8 w-full items-center">

          @foreach($auction->getImages() as $image)
            <div class="swiper-slide m-0 overflow-hidden relative">
              <img src="{{ $image }}" alt="Auction Image" class="object-cover justify-self-center" >
          </div>
          @endforeach
                      

        </div>
        <!-- End of swiper-wrapper -->
        <div class="swiper-custom-nav absolute top-1/2 transform -translate-y-1/2 left-0 z-10 w-full h-16 flex justify-between p-0 px-8">
          <img src="{{ asset('images/arrow_left.png') }}" alt="Left Arrow" class="w-auto object-contain ml-0 opacity-60 cursor-pointer transition-all duration-300 ease-in-out hover:opacity-100" id="nav-left">
          <img src="{{ asset('images/arrow_right.png') }}" alt="Right Arrow" class="w-auto object-contain ml-auto opacity-60 cursor-pointer transition-all duration-300 ease-in-out hover:opacity-100" id="nav-right">
        </div>

        <div class="swiper-custom-pagination"></div>
     </div> <!-- End of swiper -->
  </div>

  <!-- Auction bids -->
<div class="bid-details bg-white col-span-1 md:col-start-2 md:col-end-3 lg:col-start-3 lg:col-end-4 row-start-3 row-end-4 md:row-start-2 md:row-end-4 lg:row-span-full flex flex-col sm:p-4 p-6 w-full border-4 border-gray-300 rounded-lg shadow-lg">
     <!-- Row for Current Bid -->
  <div class="bid-row flex justify-between items-center">
    <p id='auction-status' class="text-gray-800 text-sm sm:text-base md:text-lg lg:text-xl">
      @if ($auction->bids->isEmpty())
          Starting Bid
      @else
          Current Bid
      @endif
  </p>

   
  </div>
  <p class="current-bid-price text-gray-800 min-[640px]:text-[4vw] min-[950px]:text-6xl lg:text-7x" id='current-bid'>
    € {{ number_format($currentHighestBid ?? $auction->minimum_bid, 0, '.', ',') }}
  </p>
  
  <div class=" mt-6 expert-estimate bg-green-100 border border-green-500 rounded-lg p-3 min-[640px]:p-1">
    @if($isActiveAuction && $auction->end_time > now())
      @if($auction->evaluation !== null)
        @if($isAdmin || $isExpert || $isSeller || $isSubscribed)

          <p class="font-bold text-lg px-2">Expert Estimate made by {{$auction->expertUser->generalUser->username}}</p>
          <div class="text-gray-800 font-bold mt-4 mb-2 w-full px-2 grid grid-cols-4">
            <img src="{{$auction->expertUser->generalUser->getProfileImage()}}" alt="Expert Profile Image" class="col-start-1 col-end-2 place-self-center aspect-square object-cover rounded-full h-14 select-none">
            <p class="col-start-2 col-end-5 text-4xl self-center pl-2">€ {{$auction->evaluation}} </p>
          </div>
        @else
          <a href="/subscription" class="block">
            <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">Subscribe to see the evaluation</p>
          </a>
        @endif
      @else
        @if($isSubscribed)
          @if(!$auction->evaluation_requested)
            <form method="post"
                  action="{{ route('auction.requestEvaluation' , $auction->id) }}">
                  @csrf
                  <button type="submit" class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">Request an Evaluation</button>
            </form>
          @else
            <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">Evaluation already requested</p>
          @endif
        @elseif($isExpert)
          @if($auction->evaluation_requested)
            <p
            class="opacity-50 font-bold text-center w-full break-words text-3xl py-6 cursor-pointer"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'evaluate')">
            Evaluate</p>
          @else
            <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">No evaluation requests</p>
          @endif
        @elseif($isAdmin)
          <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">No evaluation</p>
        @else
        <a href="/subscription" class="block">
          <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">
              Subscribe to ask for an evaluation
          </p>
        </a>
            @endif
      @endif
    @else
      @if($auction->evaluation !== null)
        <p class="font-bold text-lg px-2">Expert Estimate made by {{$auction->expertUser->generalUser->username}}</p>
        <div class="text-gray-800 font-bold mt-4 mb-2 w-full px-2 grid grid-cols-4">
          <img src="{{$auction->expertUser->generalUser->getProfileImage()}}" alt="Expert Profile Image" class="col-start-1 col-end-2 place-self-center aspect-square object-cover rounded-full h-14 select-none">
          <p class="col-start-2 col-end-5 text-4xl self-center pl-2">€ {{$auction->evaluation}} </p>
        </div>
      @else
        <p class="opacity-50 font-bold text-center w-full break-words text-3xl py-6">No evaluation</p>
      @endif
    @endif
  </div>

  @if($isExpert && $auction->evaluation_requested && $auction->evalution === null)
  <x-modal name="evaluate" :show="$errors->auctionEvaluation->isNotEmpty()" focusable>
    <form id="evaluate-form" method="post" action="{{ route('auction.evaluate', $auction->id) }}" class="p-6">
        @csrf
        @method('post')

        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Are you sure you want to evaluate this auction?') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Please provide an evaluation that accurately reflects the quality and characteristics of the product.') }}
        </p>

        <div class="mt-6">
            <x-input-label for="evaluation" value="{{ __('Evaluation') }}" class="sr-only" />

            <x-text-input
                id="evaluation"
                name="evaluation"
                type="number"
                class="mt-1 block w-3/4"
                placeholder="{{ __('Evaluation') }}"
                value="{{ old('evaluation') }}"
            />

            <x-input-error :messages="$errors->auctionEvaluation->get('evaluation')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button class="ms-3">
                {{ __('Evaluate') }}
            </x-primary-button>
        </div>
    </form>
  </x-modal>
  @endif
  
  <div class="bid-text-area w-full mt-8 flex">
    <input id="bid-text"
      type="text"
      class="w-full p-2 sm:p-3 md:p-4 border border-gray-300 rounded-md text-left placeholder-gray-400 text-base max-[640px]:text-[0.7rem] md:text-xl lg:text-2xl"
      @if ($isActiveAuction)
        placeholder="€ {{ number_format($minBidValid, 0, '.', ',') }} or up"
      @else
        placeholder='No more bids accepted'
      @endif
      @if(!Auth::check() || !$isActiveAuction || $isAdmin || $isExpert || Auth::user()->user->blocked) disabled @endif
      />
  </div>

  
  <div class="bid-button w-full mt-5 flex gap-4">
    <!-- Place Bid Button -->
    <button id="place-bid-btn" class="w-full bg-blue-500 text-white p-2 sm:p-3 md:p-4 rounded-md text-center text-base md:text-xl  hover:bg-blue-700 focus:outline-none" data-auction-id="{{ $auction->id }}"
      @if( !Auth::check() || !$isActiveAuction || $isAdmin || $isExpert || Auth::user()->user->blocked) disabled @endif >
      Place Bid
    </button>
  
    <!-- Set Max Bid Button -->
    <button id="place-max-bid-btn" class="w-full bg-green-500 text-white p-2 sm:p-3 md:p-4 rounded-md text-center text-base md:text-xl hover:bg-green-700 focus:outline-none" data-auction-id="{{ $auction->id }}"
      @if(!Auth::check() || !$isActiveAuction || $isAdmin || $isExpert || Auth::user()->user->blocked) disabled @endif >
      Set Max Bid
    </button>
  </div>
  
  @if(!($isSeller || $isAdmin || !$isActiveAuction))
    <div class="flex items-center gap-4 mt-3 w-full" id="autoBid-container">
      @if( $activeAutoBid !== NULL )
        <p id="max-bid-text" class="m-0 text-xl font-bold text-green-600">
          Current Max Bid: <span class="text-2xl font-extrabold text-green-800">€{{ number_format($activeAutoBid->max, 0, '.', ',') }}</span>
        </p>
        <button id="cancel-autoBid-btn" data-auction-id="{{ $auction->id }}" data-user-id="{{ Auth::id() }}" class="bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 ml-auto">
            Cancel AutoBid
        </button>
      @else
          <p id="no-current-auto-bid">No current AutoBid set</p>
      @endif
    </div>
  @endif

  <div class="flex items-center w-full mt-3">
    <p class="location text-left text-base sm:text-lg md:text-xl">
      Location: {{ $auction->location ?? 'Not specified' }}
  </p>
  </div>
  @if($isActiveAuction)
    <div class="flex items-center w-full">
      <p class="closes text-left text-base sm:text-lg md:text-xl">
        Closes: {{ $auction->end_time->translatedFormat('d F, H:i') }}
      </p>
    </div>
  @else
    <div class="flex items-center w-full">
      <p class="closes text-left text-base sm:text-lg md:text-xl">
        Closes: Closed
      </p>
    </div>
  @endif

  <div class="user-bids mt-4 border-t border-gray-300 pt-4">
    @if ($auction->bids->isEmpty())
        <!-- No Bids: Starting Bid -->
        <p class="text-center text-xl sm:text-2xl font-semibold text-gray-700">No bids placed</p>
    @else
    <p class="text-xl sm:text-2xl font-semibold text-gray-700 text-start mb-2">Top Bids</p>
    @php
        $bidsToShow = $auction->getHighestBids(3);  // Get the first 3 bids
    @endphp
        @foreach ($bidsToShow as $bid)
        <div class="user-info flex flex-row justify-between items-center">
            <a href= "{{ route('profile.show',$bid->user->id)}}" class="username text-base sm:text-lg font-semibold">{{  $bid->user->generalUser->username}}</a>
            <p class="time text-sm sm:text-base text-gray-500">{{ \Carbon\Carbon::parse($bid->timestamp)->translatedFormat('d F, H:i') }}</p>
            <p class="amount text-sm sm:text-base font-medium text-blue-500">€ {{ number_format($bid->value, 0, '.', ',') }}</p>
        </div>
    @endforeach
    @endif
  </div>


  @if ($auction->bids->count() > 3)
  <button class="dropdown-button mt-2" id="show-bid-history">Show More Bids</button>
@endif
<div class="dropdown-bid-history hidden overflow-scroll" id="bid-history-container">
</div>


  </div>

  <!-- Description -->
  <div class="auction-description bg-white border-4 border-gray-300 rounded-lg shadow-lg  col-span-1 row-span-1 p-6  px-8">    
    
    <p class="mb-4 text-start text-4xl text-black  mt-2">Description</p>
    <p class="text-black text-base text-start break-words">{{ $auction->description }}</p>
    
    <div class="mt-4 text-start text-black">
 
      <!-- Attribute Values -->
          <p class="text-lg font-semibold">More info about the product:</p>
    <ul class="list-disc pl-6 text-black">
      @foreach($auction->attribute_values as $key => $value)
        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
      @endforeach
    </ul>
    </div>

    @php
        $owner = $auction->seller->generalUser;
    @endphp

    @if($owner)
    <div class="mt-6 text-start">
      <!-- Text for Product Owner -->
      <p class="text-xl text-black font-semibold">Product Owner:</p>
      <div class="flex items-center mt-4 ml-2">
          <!-- Link for Profile Image -->
          <a href="{{ route('profile.show', $owner->id) }}" class="flex">
              <img src="{{ $owner->getProfileImage() }}" alt="{{ $owner->username }}'s profile image" class="w-12 h-12 rounded-full mr-4 object-cover">
          </a>
          <!-- Link for Username -->
          <a href="{{ route('profile.show',  $owner->id) }}" class="text-lg text-black font-semibold ">
              {{ $owner->username }}
          </a>
      </div>
  </div>
    @else
    <p class="mt-6 text-black">Owner information not available.</p>
    @endif

  </div>

  <!-- Chat (19) -->
  <div id="chat-container" class="chat-container col-span-1 md:col-start-1 md:col-end-2 lg:col-start-2 lg:col-end-3 row-start-4 row-end-5 md:row-start-3 md:row-end-4 lg:row-start-2 lg:row-end-3" data-user-id="{{Auth::id()}}">
    @php
        $currentDate = null;
    @endphp

    @if( Auth::check() && (!Auth::user()->isGeneralUser() || (Auth::id() === $auction->seller_id) || (!Auth::user()->user->blocked && Auth::user()->user->isSubscribed())))
        <div id="chat-box" class="chat-box">
            @if($messages->isEmpty())
            <div class="no-messages" style="display: flex; justify-self: center; align-items: center; height: 100%; flex-direction: column; margin-bottom: 20%;">
              <p style="font-size: 1.8rem; font-weight: bold; color: #555; margin-bottom: 10px; text-align: center;">
                  No one has started the conversation yet.
              </p>
              <p style="font-size: 1.5rem; color: #777; margin-bottom: 20px; text-align: center;">
                  Be the first to share your thoughts!
              </p>
              <span style="font-size: 2rem; color: #007bff;">💬</span>
          </div>
            @else
                @foreach($messages as $message)
                    @php
                        $messageDate = \Carbon\Carbon::parse($message->timestamp)->format('d/m/Y');
                    @endphp

                    <!-- Show date separator if a new day -->
                    @if($messageDate !== $currentDate)
                        <div class="date-separator">
                            <span>{{ $messageDate }}</span>
                        </div>
                        @php
                            $currentDate = $messageDate;
                        @endphp
                    @endif

                    <!-- Check if the message is from the authenticated user -->
                    @if($message->generalUser->id === Auth::user()->id)
                        <div class="my-message" data-date="{{ $message->timestamp }}">
                            <p class="message-content">{{ $message->content }}</p>
                            <p class="message-time" >{{ \Carbon\Carbon::parse($message->timestamp)->format('H:i') }}</p>
                        </div>
                    @else
                        <div class="chat-other-message" data-date="{{ $message->timestamp }}">
                            <div class="chat-profile-container">
                                <a href="{{ route('profile.show', $message->generalUser->id) }}" class="chat-profile-link">
                                    <img src="{{$message->generalUser->getProfileImage()}}" alt="User Image" class="chat-profile-image">
                                </a>
                                <a href="{{ route('profile.show', $message->generalUser->id) }}" class="chat-user-name-link">
                                    <p class="chat-user-name">{{ $message->generalUser->username }}</p>
                                </a>
                            </div>
                            <div class="chat-message-info">
                                <p class="chat-message-content">{{ $message->content }}</p>
                                <p class="chat-message-time">{{ \Carbon\Carbon::parse($message->timestamp)->format('H:i') }}</p>
                            </div>
                        </div>        
                    @endif
                @endforeach
            @endif
        </div>
    @else
        
      <div class="non-access-message" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; margin: auto;">
        @if(Auth::guest())
            <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; text-align:center;">Subscribe to start messaging!</p>
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Subscribe</a>
        @elseif(Auth::user()->user->blocked)
            <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; text-align:center;">You are blocked from messaging!</p>
            Wait for the admin to unblock you
        @else
            <p style="font-size: 1.5rem; font-weight: bold; margin-bottom: 20px; text-align:center;">Subscribe to start messaging!</p>
            <a href="{{ route('subscription.show') }}" class="btn btn-primary" style="padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Subscribe</a>
        @endif
    </div>
  
        
    @endif

    <form id="chat-form" class="chat-form">
        <textarea
        id="chat-input"
        class="chat-input"
        placeholder="Type a message..."
        required
        @if(!(Auth::check() && (!Auth::user()->isGeneralUser() ||  (Auth::id() === $auction->seller_id) || (!Auth::user()->user->blocked && Auth::user()->user->isSubscribed())) && ($isActiveAuction))) disabled @endif
        rows="2"
    ></textarea>
        <button type="submit" class="chat-submit-btn p-2 bg-blue-500 text-white rounded-full focus:outline-none"  @if(!(Auth::check() && (!Auth::user()->isGeneralUser() || (Auth::id() === $auction->seller_id) || (!Auth::user()->user->blocked && Auth::user()->user->isSubscribed())))) disabled @endif>
            <!-- Paper Icon (e.g., using Heroicons) -->
            <svg viewBox="0 0 24 24" height="24" width="24" preserveAspectRatio="xMidYMid meet" class="send-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24">
                <title>send</title>
                <path fill="currentColor" d="M1.101,21.757L23.8,12.028L1.101,2.3l0.011,7.912l13.623,1.816L1.112,13.845L1.101,21.757z"></path>
            </svg>
        </button>
    </form>
</div>



</div>

@push('scripts')

  @if ( Auth::check() && Auth::user()->isGeneralUser() && !$isSeller)
    
      <script>
        document.addEventListener('DOMContentLoaded', () => {
            const followButton = document.querySelector('#follow-btn');
            const heartIcon = document.querySelector('.heart-icon');


          followButton.addEventListener('click', async (event) => {
              event.preventDefault();
              const auctionId = followButton.getAttribute('auction-id');
              const followText = document.getElementById('followText');

              try {
                  const response = await fetch(`/followAuction/${auctionId}`, {
                      method: 'POST',
                      headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                          'Content-Type': 'application/json',
                      },
                  });

                  const data = await response.json();

                  if (data.success) {

                      if (data.isFollowing) {
                          heartIcon.style.fill = 'red';
                          followText.textContent = 'Following'
                      } else {
                          heartIcon.style.fill = 'black';
                          followText.textContent = 'Follow'
                      }
                  } else {
                      console.error(data.message);
                  }
              } catch (error) {
                  console.error('Error toggling follow:', error);
              }
          });

      });
    </script>
  @endif
  @if (($isSeller || $isAdmin) && $isActiveAuction)
    <script>
      document.addEventListener('DOMContentLoaded', () => {
            // Advertise logic
            const advertiseInput = document.getElementById("advertiseDateInput");
            const dayPriceBox = document.getElementById("dayPrice");
            if (dayPriceBox)
            {
            const dayPrice = parseFloat(dayPriceBox.textContent) / 100;
            const startDate = new Date(advertiseInput.value);


            advertiseInput.addEventListener('input', (event) => {

                const endDate = new Date(event.target.value);
                let totalPrice = document.getElementById("advertisePrice");

                if (!isNaN(startDate) && !isNaN(endDate)) {
                    const dayDifference = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)); 
                    console.log(dayDifference);
                    const price = dayDifference * dayPrice;
                    totalPrice.textContent = `${price.toFixed(2)} $`;
                }
            })

            }
            // Information logic
            const sellerButton = document.getElementById("sellerOptionsButton");
            const sellerOptions = document.getElementById("sellerOptions");

          sellerButton.addEventListener("click", () => {
              sellerOptions.classList.toggle("max-h-0"); // Collapse
              sellerOptions.classList.toggle("max-h-[400px]"); // Expand
          });

      });
    </script>


  @endif
@endpush



</x-app-layout>