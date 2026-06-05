@props(['label' => '', 'name' => '', 'value' => '', 'required' => false, 'placeholder' => ''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <input type="tel" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" 
           class="form-control" placeholder="{{ $placeholder }}" 
           @if($required) required @endif>
</div>
