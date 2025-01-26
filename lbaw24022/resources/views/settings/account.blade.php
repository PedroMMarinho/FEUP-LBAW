@extends('settings.settings')

@section('slot')
    <div class="max-w-xl pb-8">
        @include('settings.partials.update-account-information')    
    </div>
    <div class="max-w-xl pb-8">
        @include('settings.partials.update-password')    
    </div>
    <div class="max-w-xl">
        @include('settings.partials.delete-user-form')
    </div>
@endsection