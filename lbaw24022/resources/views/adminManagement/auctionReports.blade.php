@extends('adminManagement.management')

@section('slot')
    <div class="w-full">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    {{ 'Auction Reports (' . $reports->total() . ')' }}
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
                <a href="{{ route('auction.show', ['id' => $report->id]) }}">
                    <div class="grid grid-cols-4 ap-x-4 text-center items-center mt-6 hover:bg-slate-100 cursor-pointer p-4">
                        <div class="col-start-1 col-end-3 pb-6 sm:pb-0 text-sm sm:text-base">
                            {{$report->name}}
                        </div>
                        <div class="col-start-3 col-end-4 sm:col-start-3 text-sm sm:text-base">
                            {{$report->count . trans_choice(' report| reports', $report->count)}} 
                        </div>
                        <div class="col-start-4 col-end-5 text-sm sm:text-base select-none" >
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