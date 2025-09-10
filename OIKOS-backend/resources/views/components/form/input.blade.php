@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'help' => null,
    'maxlength' => null
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    
    <input type="{{ $type }}" 
           name="{{ $name }}" 
           id="{{ $name }}"
           value="{{ old($name, $value) }}"
           @if($placeholder) placeholder="{{ $placeholder }}" @endif
           @if($maxlength) maxlength="{{ $maxlength }}" @endif
           @if($required) required @endif
           class="form-input @error($name) form-input-error @enderror">
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>



