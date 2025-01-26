@props(['messages'])

<ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
    @php
        $message = $messages[0] ?? " "
    @endphp
    <li class="error min-h-5">{{ $message }}</li>
</ul>