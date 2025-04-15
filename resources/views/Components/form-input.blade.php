@props(['type' => 'text', 'name', 'id', 'label', 'value' => null])
<div class="sm:col-span-4 sm:col-start-2">
    <label for="{{ $id }}" class="block text-sm/6 font-medium text-gray-900">{{ $label }}</label>
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $id }}" 
        value="{{ $value ?? '' }}" 
        {{ $attributes->merge(['class' => 'mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600 sm:text-sm/6']) }}
    >
</div>