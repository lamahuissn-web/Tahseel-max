<form action="{{ route('admin.company_store_project',$all_data->id) }}" method="post" enctype="multipart/form-data" id="store_form">
    @csrf
    <div class="col-md-12 row" style="margin-top: 10px">
        <input type="hidden" name="company_id" id="company_id" value="{{$all_data->id}}">
        <div class="col-md-4">
            <label for="project_code" class="form-label">{{ trans('company.project_code') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                <input type="text" class="form-control" name="project_code" id="project_code" value="{{$project_code}}" readonly>
            </div>
            @error('project_code')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="col-md-4">
            <label for="first_name" class="form-label">{{ trans('company.clients') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="basic-addon3">{!! form_icon('select') !!}</span>
                <select class="form-select rounded-start-0" name="client_id" id="client_id">
                    <option value="">{{trans('clients.select')}}</option>
                    @foreach($company_clients as $item)
                        <option value="{{$item->id}}" {{ old('client_id') == $item->id ? 'selected' : '' }}>{{$item->name}}</option>
                    @endforeach
                </select>
            </div>
            @error('client_id')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>


        <div class="col-md-4">
            <label for="project_name" class="form-label">{{ trans('company.project_name') }}</label>
            <div class="input-group flex-nowrap">
                <span class="input-group-text" id="basic-addon3">{!! form_icon('text') !!}</span>
                <input type="text" class="form-control" name="project_name" id="project_name" value="{{ old('project_name') }}">
            </div>
            @error('project_name')
            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

    </div>



    <div class="col-md-12 row" style="margin-top: 10px">
        <div class="col-md-12 d-flex justify-content-end">
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-flat">
                    <?= trans('company.SaveButton') ?>
                </button>
            </div>
        </div>
    </div>
</form>
