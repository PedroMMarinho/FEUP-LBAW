@extends('adminManagement.management')

@section('slot')
    <div class="w-full">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'Appeals (' . $appeals->total() . ')' }}
                </h2>
            </header>
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
            @if ($appeals->isEmpty())
                <div class="text-gray-600 flex justify-center items-center min-h-[20rem]">No appeals</div>
            @else
                @foreach ($appeals as $appeal)
                <a href="{{ route('settings.show', ['section' => 'block', 'userId' => $appeal->blocked_user]) }}">
                    <div class="grid grid-cols-4 gap-x-4 text-center items-center mt-4 mb-5 hover:bg-slate-100 p-4 cursor-pointer rounded-sm transition-all">
                        <div class="col-start-1 col-end-2 ">
                            <div class="flex justify-center items-center">
                                <img src="{{$appeal->blockedUser->generalUser->getProfileImage()}}" class="aspect-square object-cover rounded-full max-h-24" alt="Profile Image">
                            </div>
                            <div class="mt-2 text-sm sm:text-base overflow-hidden text-ellipsis">
                                {{$appeal->blockedUser->generalUser->username}}
                            </div>
                        </div>

                        <div class="col-start-2 col-end-4 text-sm sm:text-base">
                            {{$appeal->appeal_message}} 
                        </div>
                        <div class=" col-start-4 col-end-5 text-sm sm:text-base select-none">
                            See Details
                        </div>
                    </div>
                </a>
                    <hr class="mt-4">
                @endforeach
                @if ($appeals->total() > 5)
                    <div class="mt-6">
                        {{ $appeals->links() }}
                    </div>
                @endif
            @endif
        </section>                
    </div>
@endsection