@extends('settings.settings')

@section('slot')
    <div class="w-full">
        <section>
            <form id="send-verification"
                method="post"
                action="{{ route('verification.send') }}">
                @csrf
            </form>

            <h2 class="text-lg font-medium text-gray-900 mb-6">
                {{ 'Reports (' . $reports->total() .')' }}
            </h2>
            @if ($reports->isEmpty())
                <div class="text-gray-600 flex justify-center items-center min-h-[20rem]">No reports</div>
            @else
                @foreach ($reports as $report)
                    <div class="grid grid-cols-4 gap-x-4 text-center items-center mt-4 mb-5 p-4 rounded-sm">
                        <div class="col-start-1 col-end-2 ">
                            <div class="flex justify-center items-center">
                                <img src="{{$report->reporterUser->generalUser->getProfileImage()}}" class="aspect-square object-cover rounded-full max-h-24" alt="Profile Image">
                            </div>
                            <div class="mt-2 text-sm sm:text-base overflow-hidden text-ellipsis">
                                {{$report->reporterUser->generalUser->username}}
                            </div>
                        </div>

                        <div class="col-start-2 col-end-4 text-sm sm:text-base">
                            {{$report->description}} 
                        </div>
                        <div class=" col-start-4 col-end-5 text-sm sm:text-base select-none">
                            {{ $report->timestamp->format('d/m/Y H:i') }}
                        </div>
                    </div>
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

