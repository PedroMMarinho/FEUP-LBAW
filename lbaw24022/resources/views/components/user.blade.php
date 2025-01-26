{{-- sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 --}}

<a href="/profile/{{ $id }}" class="w-full flex flex-col shadow-md cursor-pointer hover:drop-shadow-xl duration-75">
    <div class="relative w-full aspect-square rounded-t-lg bg-white overflow-hidden">
        <div class="absolute px-2 py-2 flex items-center justify-end w-full space-x-2">
            {{-- Follow User --}}
            @if (Auth::check() && $role == 'Regular User' && $id != Auth::id() && Auth::user()->role == 'Regular User' && $id != 1) 
                <div class="flex flex-shrink-0 rounded-full bg-white size-9 align-middle justify-center shadow-md hover:bg-gray-100 hover:scale-110 active:scale-100 duration-75">
                    <button class="follow-button" user-id="{{ $id }}">
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
        </div>
        {{-- User Image --}}
        @if ($image)
            <img class="object-cover object-center w-full h-full"
            src="{{ asset('images/profile/' . $image) }}" 
            alt="User Image" />
        @else
            <img class="object-cover object-center w-full h-full"
            src="{{ asset('images/profile/default2.jpg') }}" 
            alt="User Image" />
        @endif
    </div>
    
    <div class="w-full rounded-b-lg bg-white p-2 space-y-1">
        <div>
            <div class="h-20 space-y-1">
                <div class="flex row">
                    <h3 class="text-xl font-bold truncate">{{ $username }}</h3>
                </div>
                <h3 class="line-clamp-2">
                    {{ $email }}
                </h3>
            </div>
        </div>
        {{-- <div class="h-8">
            <p class="text-xs text-slate-500 line-clamp-2">
                {{ $location }}
            </p>
        </div> --}}
    </div>
</a>
