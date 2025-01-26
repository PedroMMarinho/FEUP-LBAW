@props([
    'type' => 'text',
    'label' => 'Filter',
    'id' => 'filter',
    'name' => $id,
    'options' => [],
    'defaultValue' => null,
    'defaultText' => 'Show everything',
    'nullOption' => true,
    'fromDefaultValue' => null,
    'toDefaultValue' => null,
    'class' => ''
])

<div class="flex flex-col gap-2">

    @if ($type == 'price')
        <label class="text-md" for="from-{{ $id }}">{{ $label }}</label>
        <div class="flex gap-2">
            <x-input 
                type="price"
                id="from-{{ $id }}"
                name="{{ $name }}[to]"
                placeholder="From"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}"
                default-value="{{ $fromDefaultValue === null ? null : floor((float) $fromDefaultValue * 100) / 100 }}" />
            <x-input 
                type="price"
                id="to-{{ $id }}"
                name="{{ $name }}[to]"
                placeholder="To"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}" 
                default-value="{{ $toDefaultValue === null ? null : floor((float) $toDefaultValue * 100) / 100 }}" />
        </div>
    @elseif ($type == 'float')
        <label class="text-md" for="{{ $id }}[from]">{{ $label }}</label>
        <div class="flex gap-2">
            <x-input 
                type="float"
                id="from-{{ $id }}"
                name="{{ $name }}[from]"
                placeholder="From"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}"
                default-value="{{ $fromDefaultValue === null ? null : (float) $fromDefaultValue }}" />
            <x-input 
                type="float"
                id="to-{{ $id }}"
                name="{{ $name }}[to]"
                placeholder="To"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}"
                default-value="{{ $toDefaultValue === null ? null : (float) $toDefaultValue }}" />
        </div>
    @elseif ($type == 'int')
        <label class="text-md" for="{{ $id }}[from]">{{ $label }}</label>
        <div class="flex gap-2">
            <x-input 
                type="int"
                id="from-{{ $id }}"
                name="{{ $name }}[from]"
                placeholder="From"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}"
                default-value="{{ $fromDefaultValue === null ? null : (int) $fromDefaultValue }}" />
            <x-input 
                type="int"
                id="to-{{ $id }}"
                name="{{ $name }}[to]"
                placeholder="To"
                class="w-48 text-lg py-3 px-5 bg-white rounded-md border-0 {{$class}}"
                default-value="{{ $toDefaultValue === null ? null : (int) $toDefaultValue }}" />
        </div>
    @elseif ($type == 'enum')
        <label class="text-md" for="{{ $id }}">{{ $label }}</label>
        <x-select
            :options="[
                '-1' => $defaultText,
            ] + $options" 
            :default-value="$defaultValue !== null ? $defaultValue : '-1'"
            :nullOption=$nullOption
            id="{{$id}}"
            name="{{ $name }}"
            container-class="w-52 text-lg"
            selected-class="!h-full !py-3 !px-5 !bg-white !rounded-md border-0 {{$class}}"
        />
    @else
        <label class="text-md" for="{{ $id }}">{{ $label }}</label>
        <div class="flex gap-2">
            <x-input
                id="{{ $id }}"
                name="{{ $name }}"
                placeholder="Show everything"
                :default-value="$defaultValue !== null ? $defaultValue : ''"
                class="w-52 text-lg py-3 px-5 bg-white rounded-md border-0" />
        </div>
    @endif

</div>