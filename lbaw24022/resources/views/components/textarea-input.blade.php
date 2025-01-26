@props(['disabled' => false, 'value' => '', 'name'=>'','id', 'maxLength' => 700])


<div>
    <textarea id="{{ $id }}" name="{{$name}}" @disabled($disabled){{ $attributes->merge(['class' => 'resize-none h-16 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>{{trim($value ?? $slot)}}</textarea>
    <div class="flex justify-between px-4">
        <x-input-error :messages="$errors->get($name)"/>
        <x-character-count :targetId="$id" :maxLength="$maxLength" />
    </div>
</div>
