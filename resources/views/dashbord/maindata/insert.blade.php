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
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">
                    <a href="{{route('admin.dashboard')}}"
                       class="text-muted text-hover-primary">{{trans('maindata.home')}}</a>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">{{trans('maindata.siteData')}}</li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->
                <!--begin::Item-->
                <li class="breadcrumb-item text-muted">{{trans('maindata.maindata')}}</li>
                <!--end::Item-->
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
            @csrf
            @php $mdata=optional($mdata); @endphp


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
                                background-image: url('{{asset($mdata['image']? $mdata['image']:'assets/media/avatars/blank.png')}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url('{{asset($mdata['image']? $mdata['image']:'assets/media/avatars/blank.png')}}');
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
                        <div class="mb-10 fv-row">
                            <label class="required form-label"> {{trans('maindata.discount_ratio')}}</label>
                            <input type="number" name="discount_ratio" id="discount_ratio" class="form-control mb-2"
                                   placeholder=" {{trans('maindata.discount_ratio')}} "
                                   value="{{old('discount_ratio',$mdata->discount_ratio)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label class="required form-label"> {{trans('maindata.tax_number')}}</label>
                            <input type="number" name="tax_number" id="tax_number" class="form-control mb-2"
                                   placeholder=" {{trans('maindata.tax_number')}} "
                                   value="{{old('tax_number',$mdata->tax_number)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label
                                class="required form-label"> {{trans('maindata.commercial_registration_number')}}</label>
                            <input type="number" name="commercial_registration_number"
                                   id="commercial_registration_number" class="form-control mb-2"
                                   placeholder=" {{trans('maindata.commercial_registration_number')}} "
                                   value="{{old('commercial_registration_number',$mdata->commercial_registration_number)}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label
                                class="required form-label"> {{trans('maindata.transport_value')}}</label>
                            <input type="number" name="transport_value"
                                   id="transport_value" class="form-control mb-2"
                                   placeholder=" {{trans('maindata.transport_value')}} "
                                   value="{{old('transport_value',$mdata->transport_value)}}"/>
                        </div>

                    </div>
                    <!--end::Card body-->

                </div>
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2 style="text-align:center">{{trans('maindata.print_Image')}}</h2>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body text-center pt-0">
                        <!--begin::Image input-->
                        <!--begin::Image input placeholder-->
                        <style>.image-input-placeholder2 {
                                background-image: url('{{$mdata['image_print_url']}}');
                            }

                            [data-bs-theme="dark"] .image-input-placeholder2 {
                                background-image: url('{{$mdata['image_print_url']}}');
                            }</style>

                        <div
                            class="image-input image-input-empty image-input-outline image-input-placeholder2 mb-3"
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
                                <input type="file" name="image_print" id="image_print"
                                       value="{{old('image',$mdata->image_print)}}" accept=".png, .jpg, .jpeg"/>
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
                        $name = optional($mdata->getTranslations('name'));
                        $description = optional($mdata->getTranslations('description'));
                        $contract_terms = optional($mdata->getTranslations('contract_terms'));
                        $address = optional($mdata->getTranslations('address'));
                        ?>
                        <div class="mb-10 fv-row">
                            <label for="name_ar"
                                   class="required form-label"> {{trans('maindata.Company Name')}}</label>
                            <span
                                class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <input type="text" name="name_ar" id="name" class="form-control mb-2"
                                   placeholder="اسم الشركه" value="{{old('name_ar',$name['ar'])}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label for="name_en"
                                   class="required form-label"> {{trans('maindata.Company Name')}}</label>
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
                            <label for="address_ar"
                                   class="required form-label">{{trans('maindata.Address')}}</label>
                            <span
                                class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <input type="text" name="address_ar" id="address" class="form-control mb-2"
                                   placeholder=" العنوان "
                                   value="{{old('address_ar',$address['ar'])}}"/>
                        </div>
                        <div class="mb-10 fv-row">
                            <label for=address_en
                                   class="required form-label">{{trans('maindata.Address')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_en') }})</span>
                            <input type="text" name="address_en" id="address" class="form-control mb-2"
                                   placeholder=" Address "
                                   value="{{old('address_en',$address['en'])}}"/>
                        </div>
                        {{-- <div class="mb-10 fv-row">
                             <label for=video
                                    class="required form-label">{{trans('maindata.video')}}</label>
                             <input type="text" name="video" id="video" class="form-control mb-2"
                                    placeholder=" video "
                                    value="{{old('video',$mdata->video)}}"/>
                         </div>--}}

                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label for=description_ar
                                   class="form-label">{{trans('maindata.Description')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <div class="text-muted fs-7">
                                            <textarea name="description_ar"
                                                      class="form-control form-control form-control-solid"
                                                      data-kt-autosize="true"
                                                      placeholder="اكتب رسالـه">{{old('description_ar',$description['ar'])}}</textarea>
                            </div>
                        </div>

                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label for=description_en
                                   class="form-label">{{trans('maindata.Description')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_en') }})</span>
                            <div class="text-muted fs-7">
                                            <textarea name="description_en"
                                                      class="form-control form-control form-control-solid"
                                                      data-kt-autosize="true"
                                                      placeholder="Type a message">{{old('description_en',$description['en'])}}</textarea>
                            </div>
                        </div>
                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label for=description_ar
                                   class="form-label">{{trans('maindata.contract_terms')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_ar') }})</span>
                            <div class="text-muted fs-7">
                                            <textarea name="contract_terms_ar"
                                                      class="form-control form-control form-control-solid"
                                                      data-kt-autosize="true"
                                                      placeholder="">{{old('contract_terms_ar',$contract_terms['ar'])}}</textarea>
                            </div>
                        </div>

                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label for=description_en
                                   class="form-label">{{trans('maindata.contract_terms')}}</label>
                            <span class="text-muted">({{ trans('forms.lable_en') }})</span>
                            <div class="text-muted fs-7">
                                            <textarea name="contract_terms_en"
                                                      class="form-control form-control form-control-solid"
                                                      data-kt-autosize="true"
                                                      placeholder="">{{old('contract_terms_en',$contract_terms['en'])}}</textarea>
                            </div>
                        </div>

                       {{-- <div class="mb-10 fv-row">                        <!--begin::Label-->
                            <label class="form-label">{{trans('maindata.Map_Location')}}</label>
                            <!--end::Label-->
                            <div class="text-muted fs-7">
                             <textarea name="maplocation"
                                       class="form-control form-control form-control-solid" data-kt-autosize="true"
                                       placeholder="">{{old('maplocation',$mdata->maplocation)}}</textarea>

                            </div>
                        </div>
                        @if(!empty($mdata->maplocation))
                            <div class="mb-10 fv-row">


                                <div>
                                    {!!  $mdata->maplocation !!}
                                </div>
                            </div>
                    @endif
--}}
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
                        <span class="indicator-label">{{trans('forms.reset')}}</span>

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

    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\Site\MainRequest', '#mainform') !!}
@endsection



