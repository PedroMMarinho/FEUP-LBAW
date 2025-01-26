<a href="/auctions/{{ $id }}" class="w-full flex flex-col shadow-md cursor-pointer hover:drop-shadow-xl duration-75">
    <div class="relative w-full aspect-square rounded-t-lg bg-white overflow-hidden">
        <div class="absolute px-2 py-2 flex items-center justify-between w-full space-x-2">
            {{-- Auction End Time --}}
            <div class="rounded-full bg-white px-3 py-1 flex items-center justify-center space-x-2 shadow-md">
                <svg viewBox="0 0 384 512" class="w-4 h-4 fill-current text-black">
                    <path d="M0 24C0 10.7 10.7 0 24 0L360 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-8 0 0 19c0 40.3-16 79-44.5 107.5L225.9 256l81.5 81.5C336 366 352 404.7 352 445l0 19 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 512c-13.3 0-24-10.7-24-24s10.7-24 24-24l8 0 0-19c0-40.3 16-79 44.5-107.5L158.1 256 76.5 174.5C48 146 32 107.3 32 67l0-19-8 0C10.7 48 0 37.3 0 24zM110.5 371.5c-3.9 3.9-7.5 8.1-10.7 12.5l184.4 0c-3.2-4.4-6.8-8.6-10.7-12.5L192 289.9l-81.5 81.5zM284.2 128C297 110.4 304 89 304 67l0-19L80 48l0 19c0 22.1 7 43.4 19.8 61l184.4 0z"/>
                </svg>
                @if ($state == 'Canceled')
                    <p class="text-sm">Canceled</p>
                @elseif ($state == 'Shipped')
                    <p class="text-sm">Shipped</p>
                @elseif ($state != 'Active')
                    <p class="text-sm">Finished</p>
                @else
                    <p class="text-sm">Ends in<span class="font-bold"> {{ \Carbon\Carbon::parse($endTime)->diffForHumans(null, false) }}</span></p>
                @endif
            </div>
            {{-- Follow Auction --}}
            @auth
                @if ($seller != Auth::id() && $state == 'Active' && Auth::user()->role == 'Regular User')
                    <div class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                        <button class="follow-button" auction-id="{{ $id }}">
                            @if ($following)
                                {{-- Full Heart --}}
                                <svg viewBox="0 0 512 512" class="w-5 h-5 fill-current text-black">
                                    <path d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/>
                                </svg>
                            @else
                                {{-- Empty Heart --}}
                                <svg viewBox="0 0 512 512" class="w-5 h-5 fill-current text-black">
                                    <path d="M225.8 468.2l-2.5-2.3L48.1 303.2C17.4 274.7 0 234.7 0 192.8l0-3.3c0-70.4 50-130.8 119.2-144C158.6 37.9 198.9 47 231 69.6c9 6.4 17.4 13.8 25 22.3c4.2-4.8 8.7-9.2 13.5-13.3c3.7-3.2 7.5-6.2 11.5-9c0 0 0 0 0 0C313.1 47 353.4 37.9 392.8 45.4C462 58.6 512 119.1 512 189.5l0 3.3c0 41.9-17.4 81.9-48.1 110.4L288.7 465.9l-2.5 2.3c-8.2 7.6-19 11.9-30.2 11.9s-22-4.2-30.2-11.9zM239.1 145c-.4-.3-.7-.7-1-1.1l-17.8-20-.1-.1s0 0 0 0c-23.1-25.9-58-37.7-92-31.2C81.6 101.5 48 142.1 48 189.5l0 3.3c0 28.5 11.9 55.8 32.8 75.2L256 430.7 431.2 268c20.9-19.4 32.8-46.7 32.8-75.2l0-3.3c0-47.3-33.6-88-80.1-96.9c-34-6.5-69 5.4-92 31.2c0 0 0 0-.1 .1s0 0-.1 .1l-17.8 20c-.3 .4-.7 .7-1 1.1c-4.5 4.5-10.6 7-16.9 7s-12.4-2.5-16.9-7z"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                @endif
            @endauth
            {{-- Ship Auction --}}
            @auth
                @if ($seller == Auth::id() && $state == 'Finished')
                    @if ($deliveryLocation)
                        <div title="Ship Item" class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                            <button class="ship-button" auction-id="{{ $id }}">
                                <svg viewBox="0 0 640 512" class="w-5 h-5 fill-current text-black">
                                    <path d="M112 0C85.5 0 64 21.5 64 48l0 48L16 96c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 208 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 160l-16 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l16 0 176 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 224l-48 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 288l0 128c0 53 43 96 96 96s96-43 96-96l128 0c0 53 43 96 96 96s96-43 96-96l32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-64 0-32 0-18.7c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7L416 96l0-48c0-26.5-21.5-48-48-48L112 0zM544 237.3l0 18.7-128 0 0-96 50.7 0L544 237.3zM160 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm272 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z"/>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div title="Delivery Location Pending from Winner" class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                            <button type="button" onclick="showPopUp('No delivery location yet! Wait for the winner to set the delivery location', 'error'); return false;">
                                <svg viewBox="0 0 640 512" class="w-5 h-5 fill-current text-slate-400">
                                    <path d="M112 0C85.5 0 64 21.5 64 48l0 48L16 96c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 208 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 160l-16 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l16 0 176 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 224l-48 0c-8.8 0-16 7.2-16 16s7.2 16 16 16l48 0 144 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L64 288l0 128c0 53 43 96 96 96s96-43 96-96l128 0c0 53 43 96 96 96s96-43 96-96l32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-64 0-32 0-18.7c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7L416 96l0-48c0-26.5-21.5-48-48-48L112 0zM544 237.3l0 18.7-128 0 0-96 50.7 0L544 237.3zM160 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm272 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0z"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                @elseif ($winner != null && $winner->id == Auth::id() && $state == 'Finished')
                    @if (!$deliveryLocation)
                        <div title="Add delivery location" class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                            <button type="button" class="add-delivery-location-button" auction-id="{{ $id }}">
                                <svg viewBox="0 0 384 512" class="w-5 h-5 fill-current text-black">
                                    <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div title="Waiting for item to be shipped" class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                            <button type="button" onclick="showPopUp('Already has a delivery location! Wait for it to be shipped', 'error'); return false;">
                                <svg viewBox="0 0 448 512" class="w-5 h-5 fill-current text-black">
                                    <path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                @endif
            @endauth
        </div>
        {{-- Auction Image --}}
        @if ($image)
            <img class="object-cover object-center w-full h-full"
            src="{{ asset($image) }}" 
            alt="Auction Image" />
        @else
            <div class="flex w-full h-full justify-center items-center bg-slate-50">
                <p>
                    No auction image
                </p>
            </div>
        @endif
    </div>
    
    <div class="w-full rounded-b-lg bg-white p-2 space-y-1">
        <div>
            <div class="h-20 space-y-1">
                <h3 class="line-clamp-2">
                    {{ $name }}
                </h3>
                <div class="flex row">
                    <h3 class="text-xl font-bold">{{ $highestBid }} €</h3>
                </div>
            </div>
        </div>
        <div class="h-8">
            <p class="text-xs text-slate-500 line-clamp-2">
                {{ $location }}
            </p>
        </div>
    </div>
</a>
