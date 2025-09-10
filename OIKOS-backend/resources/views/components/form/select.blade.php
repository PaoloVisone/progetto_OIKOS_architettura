@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'help' => null,
    'options' => []
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    
    <select name="{{ $name }}" 
            id="{{ $name }}"
            @if($required) required @endif
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error($name) border-red-300 @enderror">
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
