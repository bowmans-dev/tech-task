<div class="sm:col-span-4 sm:col-start-2">
    <label for="{{ $id ?? $name }}" class="h-6 leading-6 block text-sm font-medium text-gray-900">
      {{ $label ?? 'Select an option' }}
    </label>
    <select
      id="{{ $id ?? $name }}"
      name="{{ $name }}"
      {{ $attributes->merge(['class' => 'mt-2 block w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-sm text-gray-900 outline outline-1 outline-gray-300 focus:outline-2 focus:outline-indigo-600']) }}
      {{ $required ? 'required' : '' }}
    >
      @if($placeholder)
        <option value="">{{ $placeholder }}</option>
      @endif
  
      @foreach ($options as $value => $label)
        <option value="{{ $value }}" {{ $value == $selected ? 'selected' : '' }}>
          {{ $label }}
        </option>
      @endforeach
    </select>
  </div>