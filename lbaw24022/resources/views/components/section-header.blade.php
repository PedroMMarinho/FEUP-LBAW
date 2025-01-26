@props(['class' => '', 'title' => 'Generic title'])

@php
    $style = "font-bold mb-2 ml-5 text-3xl w-full";
@endphp

<header {{ $attributes->merge(['class' => "font-bold mb-2 ml-5 text-3xl w-full " . $class]) }}>
    {{$title}}
</header>