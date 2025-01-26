@props([
    'mainText' => "",
    'dataType' => "",
    'description' => "",
    'class' => "",
    'explanation' => null,
    'extra' => false,
    'secondaryText' => '',
    'extraPoints' => [],
])

    <div class="relative group bg-white rounded-xl flex items-center justify-center w-40 h-40  lg:w-56 lg:h-56 select-none {{$class}}">

        @if ($extra)
            <div class="opacity-0 rounded-xl group-hover:opacity-100 flex flex-col items-center justify-center text-center px-4 absolute inset-0 text-white bg-black bg-opacity-90 transition-opacity duration-200"> 
                <p class="mb-3">{{$secondaryText}}</p>
                <div class="text-left w-max">
                    @foreach ($extraPoints as $text=>$number)
                        <p><strong>{{$number}}</strong> {{$text}}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="text-center">
            <div class="flex items-end mb-4 justify-center"> 
                <p class="text-4xl lg:text-6xl font-semibold">{{$mainText}}</p>
                <p class="text-xs lg:text-xl">{{$dataType}}</p>
            </div>
            <p class="text-xs lg:text-xl ">{{$description}}</p>
        </div>

        @if ($extra)
            <p class="absolute bottom-0 right-0 text-xs p-2">hover for details</p>
        @endif
    </div>