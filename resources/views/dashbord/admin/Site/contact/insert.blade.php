@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                {{trans('maindata.Main Data')}}</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('maindata.Back')}}</a>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}"
                       class="text-muted text-hover-primary"> {{trans('maindata.Next')}}</a>
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>

    </div>
    <!--end::Toolbar container-->
@endsection

@section('content')

    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxxl">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{route('admin.mdata.store')}}"
              class="form d-flex flex-column flex-lg-row"
              id="mainform" enctype="multipart/form-data">
            {{csrf_field()}}


            @if(isset($mdata))
                <input name="id" value="{{$mdata->id}}" type="hidden">
        @endif
        <!--begin::Aside column-->
            <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                <!--begin::Thumbnail settings-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2 style="text-align:center">{{trans('maindata.Logo Image')}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <!--begin::Image input-->
                        <!--begin::Image input placeholder-->
                        <style>.image-input-placeholder {
                                background-image: url('{{asset($mdata['image']? 'imgs/'.$mdata['image']:'assets/media/avatars/300-3.jpg')}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url('{{asset($mdata['image']? 'imgs/'.$mdata['image']:'assets/media/avatars/300-3.jpg')}}');
                            }</style>

                        <div
                            class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                            data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-150px"></div>
                            <label
                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Change avatar">
                                <i class="fa fa-pencil ">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="file" name="image" id="image"
                                       value="{{old('image',$mdata->image)}}" accept=".png, .jpg, .jpeg"/>
                                <input type="hidden" name="avatar_remove"/>
                            </label>

                            <span
                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                title="Cancel image">
															<i class="ki-duotone ki-cross fs-2">
																<span class="path1"></span>
																<span class="path2"></span>
															</i>
														</span>

                            <span
                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                title="Remove image">
															<i class="ki-duotone ki-cross fs-2">
																<span class="path1"></span>
																<span class="path2"></span>
															</i>
														</span>
                            <!--end::Remove-->
                        </div>
                        <!--end::Image input-->

                    </div>
                    <!--end::Card body-->
                </div>
                <!--begin::Status-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2>{{trans('maindata.Contacts')}}</h2>
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <div class="rounded-circle  w-15px h-15px"
                                 id="kt_ecommerce_add_category_status"></div>
                        </div>
                        <!--begin::Card toolbar-->
                    </div>
                    <!--end::Card header-->


                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div class="mb-10 fv-row">
                            <label class="required form-label"> {{trans('maindata.Fax')}}</label>
                            <input type="text" name="fax" id="fax" class="form-control mb-2"
                                   placeholder=" fax " value="{{old('fax',$mdata->fax)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label class="required form-label"> {{trans('maindata.Phone')}}</label>
                            <input type="text" name="phone" id="phone" class="form-control mb-2"
                                   placeholder=" Phone " value="{{old('phone',$mdata->phone)}}"/>
                        </div>

                    </div>
                    <!--end::Card body-->

                </div>


                <!--end::Status-->

            </div>
            <!--end::Aside column-->
            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::General options-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <div class="card-title">
                            <h2> {{trans('maindata.General')}}</h2>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <?php
                        // dd($mdata);
                        $name = $mdata->getTranslations('name');
                        $description = $mdata->getTranslations('description');
                        $address = $mdata->getTranslations('address');
                        ?>
                        <div class="mb-10 fv-row">
                            <label for="name_ar" class="required form-label"> {{trans('maindata.Company Name')}}</label>
                            <span
                                class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <input type="text" name="name_ar" id="name" class="form-control mb-2"
                                   placeholder="اسم الشركه" value="{{old('name_ar',$name['ar'])}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label for="name_en" class="required form-label"> {{trans('maindata.Company Name')}}</label>
                            <span
                                class="text-muted">({{ trans('forms.lable_en') }})</span>
                            <input type="text" name="name_en" id="name" class="form-control mb-2"
                                   placeholder="Company name" value="{{old('name_en',$name['en'])}}"/>
                        </div>

                        <div class="mb-10 fv-row">
                            <label class="required form-label">{{trans('maindata.Email')}} </label>
                            <input type="text" name="email" id="email" class="form-control mb-2"
                                   placeholder=" Email " value="{{old('email',$mdata->email)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label for="address_ar" class="required form-label">{{trans('maindata.Address')}}</label>
                            <span
                                class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <input type="text" name="address_ar" id="address" class="form-control mb-2"
                                   placeholder=" العنوان " value="{{old('address_ar',$mdata->address)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label for=address_en class="required form-label">{{trans('maindata.Address')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_en') }})</span>
                            <input type="text" name="address_en" id="address" class="form-control mb-2"
                                   placeholder=" Address " value="{{old('address_en',$mdata->address)}}"/>
                        </div>


                        <!--begin::Label-->
                        <label for=description_ar class="form-label">{{trans('maindata.Description')}}</label>
                        <span class="text-muted">({{ trans('forms.lable_ar') }})</span>
                        <div class="text-muted fs-7">
                                            <textarea name="description_ar"
                                                      class="form-control form-control form-control-solid"
                                                      rows="2" placeholder="اكتب رسالـه"></textarea></div>


                        <!--begin::Label-->
                        <label for=description_en class="form-label">{{trans('maindata.Description')}}</label>
                        <span class="text-muted">({{ trans('forms.lable_en') }})</span>
                        <div class="text-muted fs-7">
                                            <textarea name="description_en"
                                                      class="form-control form-control form-control-solid"
                                                      rows="2" placeholder="Type a message"></textarea></div>

                        <br>
                        <!--begin::Label-->
                        <label class="form-label">{{trans('maindata.Map_Location')}}</label>
                        <!--end::Label-->
                        <div class="text-muted fs-7">
      <textarea name="maplocation"
                class="form-control form-control form-control-solid"
                rows="2" placeholder="">
      </textarea></div>

                        <div class="mb-10 fv-row">
                            <label class="required form-label">{{trans('maindata.Location on Map')}}</label>

                            <div>
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d13735.308237673138!2d30.976159350000003!3d30.61064085!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sar!2seg!4v1696971270229!5m2!1sar!2seg"
                                    width="730" height="450"
                                    style="border:0;"
                                    allowfullscreen="" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>


                        <!--end::Input group-->

                        <!--end::Input group-->
                    </div>
                    <!--end::Card header-->
                </div>
                <!--end::General options-->


                <div class="d-flex justify-content-end">
                    <!--begin::Button-->
                    <button type="reset" id="kt_ecommerce_add_category_submit"
                            class="btn btn-primary"
                            style="margin-right: 5%;background-color: rgba(169, 169, 169, 0.959)">
                        <span class="indicator-label">{{trans('maindata.Cancel')}}</span>

                    </button>

                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
                        <span class="indicator-label">{{trans('maindata.Save')}}</span>
                        <span class="indicator-progress">{{trans('maindata.Please wait...')}}
													<span
                                                        class="spinner-border spinner-border-sm align-middle ms-2">
                                                    </span></span>
                    </button>
                    <!--end::Button-->
                </div>
            </div>
            <!--end::Main column-->
        </form>
    </div>
    <!--end::Content container-->

@endsection
@section('js')
    <script>
        $(document).ready(function () {
            $("#password-toggle").click(function () {
                var passwordField = $("#password-field");
                var passwordIcon = $(".toggle-password");

                if (passwordField.attr("type") === "password") {
                    passwordField.attr("type", "text");
                    passwordIcon.each(function () {
                        if ($(this).data("mode") === "password") {
                            $(this).addClass("d-none");
                        } else {
                            $(this).removeClass("d-none");
                        }
                    });
                } else {
                    passwordField.attr("type", "password");
                    passwordIcon.each(function () {
                        if ($(this).data("mode") === "password") {
                            $(this).removeClass("d-none");
                        } else {
                            $(this).addClass("d-none");
                        }
                    });
                }
            });
            set_status(value);
        });
    </script>


    <script>
        function set_status(value) {
            var status = $('#kt_ecommerce_add_category_status');
            if (value == 0) {
                status.removeClass('bg-success').addClass('bg-danger');
            } else if (value == 1) {
                status.removeClass('bg-danger').addClass('bg-success');
            }
        }
    </script>

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\mainrequest', '#mainform') !!}
@endsection



