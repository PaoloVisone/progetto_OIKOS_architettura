@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'help' => null,
    'rows' => 3,
    'maxlength' => null
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    
    <textarea name="{{ $name }}" 
              id="{{ $name }}"
              rows="{{ $rows }}"
              @if($placeholder) placeholder="{{ $placeholder }}" @endif
              @if($maxlength) maxlength="{{ $maxlength }}" @endif
              @if($required) required @endif
              class="form-input @error($name) form-input-error @enderror">{{ old($name, $value) }}</textarea>
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>