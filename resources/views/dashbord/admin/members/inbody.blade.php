@extends('dashbord.admin.members.details')

@section('member_content')

    <div class="tab-content" id="myTabContent">
        <!--begin:::Tab pane-->
        <div id="kt_customer_view_overview_tab"
             role="tabpanel">
            <!--begin::Card-->
            <div class="card pt-4 mb-6 mb-xl-9">
                <!--begin::Card header-->
                <div class="card-header border-0" style="display: block !important;">
                    <!--begin::Card title-->
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <h2 class="me-auto">{{trans('members.inbody')}}</h2>
                        <a data-bs-toggle="modal" data-bs-target="#inbody_modal"
                           class="btn btn-icon btn-sm btn-success">
                            <span class="svg-icon svg-icon-2">
                               <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                          transform="rotate(-90 11.364 20.364)" fill="currentColor"/>
                                    <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="currentColor"/>
                               </svg>
                             </span>
                        </a>
                    </div>

                    <!--end::Card title-->
                    <!--begin::Card toolbar-->

                </div>
                <!--end::Card header-->
                <!------------------------------------------------------------------------------------------->


                <!--begin::Card body-->
                <div class="card-body pt-0 pb-5">


                    <!--begin::Table-->
                    <table class="table table-striped border rounded gy-5 gs-7  data-table"
                           id="kt_table_customers_payment">
                        <!--begin::Table head-->
                        <thead class="border-bottom border-gray-200 fs-7 fw-bold">
                        <!--begin::Table row-->
                        <tr style="text-align: center"
                            class="text-start text-muted text-uppercase gs-0">
                            <th style="text-align: center">{{trans('members.date')}}</th>
                            <th style="text-align: center">{{trans('members.height')}}</th>
                            <th style="text-align: center">{{trans('members.weight')}}</th>
                            <th style="text-align: center">{{trans('members.fat_percentage')}}</th>
                            <th style="text-align: center">{{trans('members.muscle_mass_percentage')}}</th>
                            <th style="text-align: center">{{trans('members.body_status')}}</th>
                            <th style="text-align: center">{{trans('forms.action')}}</th>

                        </tr>
                        <!--end::Table row-->
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody class="fs-6 fw-semibold text-gray-600">
                        @if($inbody_data)
{{--                            {{dd($inbody_data)}}--}}
                            @foreach($inbody_data as $row)
                                <tr>
                                    <td style="text-align: center">{{$row->date}}</td>
                                    <td style="text-align: center">{{$row->height}}.cm</td>
                                    <td style="text-align: center">{{$row->weight}}.kg</td>
                                    <td style="text-align: center">{{$row->fat_percentage}} .%</td>
                                    <td style="text-align: center">{{$row->muscle_mass_percentage}}.%</td>
                                    <td style="text-align: center">{{$row->body_status}}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            {{trans('forms.action')}}
                                            <span class="svg-icon svg-icon-5 m-0">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.4343 12.7344L7.25 8.55005C6.83579
                            8.13583 6.16421 8.13584 5.75 8.55005C5.33579
                            8.96426 5.33579 9.63583 5.75 10.05L11.2929
                            15.5929C11.6834 15.9835 12.3166 15.9835
                            12.7071 15.5929L18.25 10.05C18.6642 9.63584
                            18.6642 8.96426 18.25 8.55005C17.8358 8.13584
                            17.1642 8.13584 16.75 8.55005L12.5657
                            12.7344C12.2533 13.0468 11.7467 13.0468
                            11.4343 12.7344Z" fill="currentColor"/>
                        </svg>
                    </span>
                                        </a>
                                        <div
                                            class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                            data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a data-bs-toggle="modal"
                                                   data-bs-target="#inbody_modal_edit_{{$row->id}}"
                                                   class="menu-link px-3">{{trans('forms.edit_btn')}}</a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a onclick="return confirm('هل تريد الحذف ؟')" class="menu-link px-3"
                                                   href="{{route('admin.delete_inbody',$row->id)}}">{{trans('forms.delete_btn')}}</a>
                                            </div>
                                        </div>

                                        <div class="modal fade" tabindex="-1" id="inbody_modal_edit_{{$row->id}}">
                                            <div class="modal-dialog " style="max-width: 70%;">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h3 class="modal-title bg-gray-25">{{trans('members.inbody')}}</h3>
                                                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                                                             data-bs-dismiss="modal" aria-label="Close">
                                                            <i class="ki-duotone ki-cross fs-1">&times;</i>
                                                        </div>
                                                    </div>
                                                    <form id="form" method="post"
                                                          action="{{route('admin.update_inbody',$row->id)}}"
                                                          enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.member')}}</label>
                                                                    <input class="form-control form-control-solid"
                                                                           value="{{old('member_name',$one_data->memberName)}}"
                                                                           name="member_name"
                                                                           placeholder="Pick date rage" id="member_name"
                                                                           readonly/>
                                                                    <input type="hidden" name="member_id"
                                                                           value="{{$one_data->memberId}}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.date')}}</label>
                                                                    <input type="date"
                                                                           class="form-control form-control-solid"
                                                                           value="{{$row->date}}" name="date"
                                                                           placeholder="Pick date rage" id="date"/>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.height')}}</label>
                                                                    <input type="number" value="{{$row->height}}"
                                                                           step="any"
                                                                           class="form-control form-control-solid"
                                                                           name="height" id="height"/>
                                                                </div>
                                                            </div>
                                                            <div class="row" style="margin-top: 10px">
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.weight')}}</label>
                                                                    <input type="number" step="any"
                                                                           value="{{$row->weight}}"
                                                                           class="form-control form-control-solid"
                                                                           name="weight" id="weight"/>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.fat_percentage')}}</label>
                                                                    <input type="number" step="any"
                                                                           value="{{$row->fat_percentage}}"
                                                                           class="form-control form-control-solid"
                                                                           name="fat_percentage" id="fat_percentage"/>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="required fs-6 fw-semibold mb-2">{{trans('members.muscle_mass_percentage')}}</label>
                                                                    <input type="number" step="any"
                                                                           value="{{$row->muscle_mass_percentage}}"
                                                                           class="form-control form-control-solid"
                                                                           name="muscle_mass_percentage"
                                                                           id="muscle_mass_percentage"/>
                                                                </div>
                                                            </div>
                                                            <div class="mb-10 fv-row">
                                                                <label
                                                                    class="required form-label">{{trans('members.body_status')}}</label>
                                                                <input type="text" name="body_status"
                                                                       class="form-control mb-2" id="body_status"
                                                                       placeholder="{{trans('members.body_status')}}"
                                                                       value="{{$row->body_status}}"/>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" name="add" value="add" id="add_ezn"
                                                                    class="btn btn-success btn-flat">
                                                                <i class="bi bi-save"></i>{{trans('forms.save_btn')}}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" style="text-align: center">{{trans('messages.no_data')}}</td>
                            </tr>
                        @endif

                        </tbody>
                        <!--end::Table body-->
                    </table>


                </div>

                <!--end::Card body-->

                <!--------------------------------------------------------------------------------------------->
            </div>
            <!--end::Card-->
        </div>
    </div>


    <div class="modal fade" tabindex="-1" id="inbody_modal">
        <div class="modal-dialog " style="max-width: 70%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title bg-gray-25">{{trans('members.inbody')}}</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                         aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1">&times;</i>
                    </div>

                </div>
                <form id="form" method="post" action="{{route('admin.add_inbody')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.member')}}</label>
                                <input class="form-control form-control-solid"
                                       value="{{old('member_name',$one_data->memberName)}}" name="member_name"
                                       placeholder="Pick date rage" id="member_name" readonly/>
                                <input type="hidden" name="member_id" value="{{$one_data->memberId}}">
                            </div>

                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.date')}}</label>
                                <input type="date" class="form-control form-control-solid"
                                       value="{{date('Y-m-d')}}" name="date"
                                       placeholder="Pick date rage" id="date"/>
                            </div>

                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.height')}}</label>
                                <input type="number" step="any" class="form-control form-control-solid"
                                       name="height"
                                       id="height"/>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px">
                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">{{trans('members.weight')}}</label>
                                <input type="number" step="any" class="form-control form-control-solid"
                                       name="weight"
                                       id="weight"/>
                            </div>
                            <div class="col-md-4">
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('members.fat_percentage')}}</label>
                                <input type="number" step="any" class="form-control form-control-solid"
                                       name="fat_percentage"
                                       id="fat_percentage"/>
                            </div>

                            <div class="col-md-4">
                                <label
                                    class="required fs-6 fw-semibold mb-2">{{trans('members.muscle_mass_percentage')}}</label>
                                <input type="number" step="any" class="form-control form-control-solid"
                                       name="muscle_mass_percentage"
                                       id="muscle_mass_percentage"/>
                            </div>

                        </div>

                        <div class="mb-10 fv-row">
                            <!--begin::Label-->
                            <label class="required form-label">{{trans('members.body_status')}}</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="email"
                                   class="form-control mb-2" id="body_status"
                                   placeholder="{{trans('members.body_status')}}" name="body_status"
                                   value="{{old('body_status')}}"/>
                            <!--end::Input-->
                        </div>


                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="add" value="add" id="add_ezn" class="btn btn-success btn-flat ">
                            <i class="bi bi-save"></i>{{trans('forms.save_btn')}}
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>

@endsection
@section('js2')
    <script>
        var KTAppBlogSave = function () {


            // Init daterangepicker
            const initDaterangepicker = () => {

                $("#date").daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        minYear: 2000,
                        maxYear: parseInt(moment().format("YYYY"), 12)
                    }
                );
            }

            return {
                init: function () {
                    initDaterangepicker();

                }
            };
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTAppBlogSave.init();
        });
    </script>
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>
    {!! JsValidator::formRequest('App\Http\Requests\Admin\Members\SaveInbodyRequest', '#form') !!}

@endsection
