
@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'help' => null
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input type="checkbox" 
                   name="{{ $name }}" 
                   id="{{ $name }}"
                   value="{{ $value }}"
                   {{ old($name, $checked) ? 'checked' : '' }}
                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
        </div>
        <div class="ml-3 text-sm">
            <label for="{{ $name }}" class="font-medium text-gray-700">{{ $label }}</label>
            @if($help)
                <p class="text-gray-500">{{ $help }}</p>
            @endif
        </div>
    </div>
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>