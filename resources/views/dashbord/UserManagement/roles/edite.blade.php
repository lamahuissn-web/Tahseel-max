<form action="{{route('admin.UserManagement.roles.update',$one_data->id)}}" method="POST"
      id="kt_ecommerce_add_product_form my-form"
      class="form d-flex flex-column flex-lg-row"
      enctype="multipart/form-data">
    @method('PUT')
    {{csrf_field()}}
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">
                {{trans('roles.Update')}}</h5>
        </div>
        <!--begin::Formmmmm-->

        <div class="modal-body">
            <div class="container-fluid">
                <input type="hidden" name="id" value="{{$one_data->id}}">
                @php
                    $title=$one_data->getTranslations('title'); //return local lang
                @endphp
                <div class="row">
                    <div class="col">
                        <label class="required form-label">{{trans('roles.Name')}} (<span
                                class="text-gray-600">{{trans('forms.lable_en')}}</span>)</label>

                        <input type="text" name="title_en" class="form-control mb-2"
                               placeholder="{{trans('roles.Name')}}" value="{{$title['en']}}" required autocomplete/>
                    </div>
                    <div class="col">
                        <label class="required form-label">{{trans('roles.Name')}}(<span
                                class="text-gray-600">{{trans('forms.lable_ar')}}</span>)</label>

                        <input type="text" name="title_ar" class="form-control mb-2" value="{{$title['ar']}}"
                               placeholder="{{trans('roles.Name')}}" required autocomplete/>
                    </div>
                </div>
               {{-- <div class="row">
                    <div class="col">
                        <label class="required form-label">{{trans('roles.value')}} </label>

                        <input type="text" name="name" class="form-control mb-2"
                               placeholder="{{trans('roles.value')}}" value="{{$one_data->name}}" required
                               autocomplete/>
                    </div>
                    <div class="col">
                        <label class="required form-label">{{trans('roles.guard_name')}}</label>
                        <input type="text" name="guard_name" class="form-control mb-2" value="{{$one_data->guard_name}}"
                               placeholder="{{trans('roles.guard_name')}}" required autocomplete/>
                    </div>
                </div>--}}

                <div class="fv-row mt-5">
                    <!--begin::Label-->
                    <label class="fs-5 fw-bold form-label mb-5">{{trans('roles.permissions')}}</label>
                    <!--end::Label-->
                    @php
                        $chunkedItems = $permissions->chunk(4);

                    @endphp
                    @foreach ($chunkedItems as $chunk)
                        <div class="row mb-2">

                            @foreach ($chunk as $item)
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" name="permissions[]" type="checkbox"
                                               value="{{$item->name}}"
                                               id="flexCheckDefault{{$item->id}}" @if(in_array($item->id,$role_permissions)) {{'checked'}} @endif />
                                        <label class="form-check-label" for="flexCheckDefault{{$item->id}}">
                                            {{$item->title}}
                                        </label>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    @endforeach
                </div>

                <!--end::Main column-->
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                                                    <span
                                                        class="indicator-label">{{trans('roles.Save')}}</span>
                    <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    {{trans('roles.Close')}}</button>

            </div>

        </div>
    </div>
</form>
