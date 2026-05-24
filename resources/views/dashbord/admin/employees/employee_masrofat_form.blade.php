<form method="post" action="{{ route('admin.employee_add_masrofat',$all_data->id) }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label for="band_id" class="form-label fw-bold">{{ trans('masrofat.band') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light">{!! form_icon('select') !!}</span>
                <select class="form-select" name="band_id" id="band_id">
                    <option value="">{{ trans('masrofat.select') }}</option>
                    @foreach($bands as $item)
                        <option value="{{$item->id}}" {{ old('band_id') == $item->id ? 'selected' : '' }}>
                            {{$item->title}}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('band_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="value" class="form-label fw-bold">{{ trans('masrofat.value') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-light">{!! form_icon('text') !!}</span>
                <input type="number" class="form-control" name="value" id="value" value="{{ old('value') }}" placeholder="0.00">
            </div>
            @error('value')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-10">
            <label for="notes" class="form-label fw-bold">{{ trans('masrofat.notes') }}</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ trans('masrofat.enter_notes') }}">{{ old('notes') }}</textarea>
            @error('notes')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="add" value="add" id="add_ezn" class="btn btn-success w-100">
                <i class="bi bi-save"></i> {{ trans('employees.SaveButton') }}
            </button>
        </div>
    </div>

</form>
