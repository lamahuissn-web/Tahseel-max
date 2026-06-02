@props(['label' => '', 'name' => '', 'value' => '', 'required' => false, 'placeholder' => ''])
<div class=mb-3>
    <label for={{ }} class=form-label>{{  }} @if()<span class=text-danger>*</span>@endif</label>
    <input type=tel name={{ }} id={{ }} value={{ }} 
           class=form-control placeholder={{ }} 
           @if() required @endif>
</div>
