@props([
    'route' => '',
    'options' => [],
])

@foreach ($options as $showValue => $routeValue)
    <a 
        class="block mt-2 p-2 {{ request()->route('section') === $routeValue['section'] ? 'bg-blue-500 text-white rounded' : '' }}" 
        href="{{ route($route, $routeValue) }}">
        {{ $showValue }}
    </a>
@endforeach