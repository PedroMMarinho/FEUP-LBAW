@extends('adminManagement.management')

@section('slot')
    <div class="w-full">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'User Reports (' . $reports->total() . ')' }}
                </h2>
            </header>
        
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>
            @if ($reports->isEmpty())
                <div class="text-gray-600 flex justify-center items-center min-h-[20rem]">No reports</div>
            @else
                @foreach ($reports as $report)
                <a href="{{ route('settings.show', ['section' => 'reports', 'userId' => $report->reported]) }}">
                    <div class="grid grid-cols-4 grid-rows-2 sm:grid-rows-1 gap-x-4 text-center items-center mt-6 hover:bg-slate-100 cursor-pointer p-4">
                        <div class="col-start-1 col-end-2 row-start-1 row-end-2 flex justify-center items-center">
                            <img src="{{$report->reportedUser->generalUser->getProfileImage()}}" class="aspect-square object-cover rounded-full max-h-24" alt="Profile Image">
                        </div>
                        <div class="col-start-1 col-end-2 sm:col-start-2 sm:col-end-3 row-start-2 row-end-3 sm:row-start-1 pb-6 sm:pb-0 text-sm sm:text-base">
                            {{$report->reportedUser->generalUser->username}}
                        </div>
                        <div class="col-start-2 col-end-4 sm:col-start-3 row-start-1 row-end-3 sm:row-start-1 text-sm sm:text-base">
                            {{$report->count . trans_choice(' report| reports', $report->count)}} 
                        </div>
                        <div class="col-start-4 col-end-5 row-span-full text-sm sm:text-base select-none" >
                            See Details
                        </div>            
                    </div>
                </a>
                    <hr class="mt-4">
                @endforeach 
                @if ($reports->total() > 5)
                    <div class="mt-6">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </section>                
    </div>
@endsection