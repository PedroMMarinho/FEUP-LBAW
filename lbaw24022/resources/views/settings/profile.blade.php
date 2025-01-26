@extends('settings.settings')

@section('slot')
    <div class=" max-w-2xl w-full">
        @include('settings.partials.update-profile-information')   

        @if (Auth::user()->role === 'Admin' && Auth::user()->id !== $user->id)
            @include('settings.partials.delete-user-form')              
        @endif
    </div>

@endsection
