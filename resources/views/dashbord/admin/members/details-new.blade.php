@extends('dashbord.layouts.master')
@section('toolbar')
    <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
            <!--begin::Title-->
            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                Show Data</h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">

                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">
                        {{trans('Toolbar.home')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.members')}}
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.Members.index') }}"
                       class="text-muted text-hover-primary">{{trans('Toolbar.members')}}</a>
                </li>
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <li class="breadcrumb-item text-muted">
                    {{trans('Toolbar.membersDetails')}}
                </li>


            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--begin::Actions-->
        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <!--begin::Filter menu-->
            <div class="d-flex">
                <a href="{{route('admin.Members.index')}}"
                   class="btn btn-icon btn-sm btn-primary flex-shrink-0 ms-4">

                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/arrows/arr054.svg-->
                    <span class="svg-icon svg-icon-2">
                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                       <path
                                           d="M17.6 4L9.6 12L17.6 20H13.6L6.3 12.7C5.9 12.3 5.9 11.7 6.3 11.3L13.6 4H17.6Z"
                                           fill="currentColor"/>
                                   </svg>
                                </span>
                    <!--end::Svg Icon-->
                </a>
            </div>
            <!--end::Filter menu-->
            <!--begin::Secondary button-->
            <!--end::Secondary button-->
            <!--begin::Primary button-->
            <!--end::Primary button-->
        </div>
        <!--end::Actions-->

    </div>
    <!--end::Toolbar container-->
@endsection
@section('content')

    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Navbar-->
        <div class="card card-flush mb-9" id="kt_user_profile_panel">
            <!--begin::Hero nav-->
            <div class="card-header rounded-top bgi-size-cover h-200px"
                 style="background-position: 100% 50%; background-image:url('{{asset('assets/media/misc/profile-head-bg.jpg')}}')"></div>
            <!--end::Hero nav-->
            <!--begin::Body-->
            <div class="card-body mt-n19">
                <!--begin::Details-->
                <div class="m-0">
                    <!--begin: Pic-->
                    <div class="d-flex flex-stack align-items-end pb-4 mt-n19">
                        <div class="symbol symbol-125px symbol-lg-150px symbol-fixed position-relative mt-n3">
                            <img src="{{$one_data->image_url}}" alt="image"
                                 class="border border-white border-4"
                                 style="border-radius: 20px"/>
                            {{--@if($one_data->status == 'active')
                                <div
                                    class="position-absolute translate-middle bottom-0 start-100 ms-n1 mb-9 bg-success rounded-circle h-15px w-15px"></div>
                            @endif--}}
                        </div>
                        {{--<!--begin::Toolbar-->
                        <div class="me-0">
                            <button class="btn btn-icon btn-sm btn-active-color-primary justify-content-end pt-3"
                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="fonticon-settings fs-2"></i>
                            </button>
                            <!--begin::Menu 3-->
                            <div
                                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                                data-kt-menu="true">
                                <!--begin::Heading-->
                                <div class="menu-item px-3">
                                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Payments</div>
                                </div>
                                <!--end::Heading-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">Create Invoice</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link flex-stack px-3">Create Payment
                                        <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                           title="Specify a target name for future usage and reference"></i></a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3">Generate Bill</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                     data-kt-menu-placement="right-end">
                                    <a href="#" class="menu-link px-3">
                                        <span class="menu-title">Subscription</span>
                                        <span class="menu-arrow"></span>
                                    </a>
                                    <!--begin::Menu sub-->
                                    <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3">Plans</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3">Billing</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3">Statements</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu separator-->
                                        <div class="separator my-2"></div>
                                        <!--end::Menu separator-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <div class="menu-content px-3">
                                                <!--begin::Switch-->
                                                <label
                                                    class="form-check form-switch form-check-custom form-check-solid">
                                                    <!--begin::Input-->
                                                    <input class="form-check-input w-30px h-20px" type="checkbox"
                                                           value="1" checked="checked" name="notifications"/>
                                                    <!--end::Input-->
                                                    <!--end::Label-->
                                                    <span class="form-check-label text-muted fs-6">Recuring</span>
                                                    <!--end::Label-->
                                                </label>
                                                <!--end::Switch-->
                                            </div>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu sub-->
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3 my-1">
                                    <a href="#" class="menu-link px-3">Settings</a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu 3-->
                        </div>
                        <!--end::Toolbar-->--}}
                    </div>
                    <!--end::Pic-->
                    <!--begin::Info-->
                    <div class="d-flex flex-stack flex-wrap align-items-end">
                        <!--begin::User-->
                        <div class="d-flex flex-column">
                            <!--begin::Name-->
                            <div class="d-flex align-items-center mb-2">
                                <a href="#" class="text-gray-800 text-hover-primary fs-2 fw-bolder me-1">
                                    {{$one_data->member_name}} </a>
                                <a href="#" class="" data-bs-toggle="tooltip" data-bs-placement="right"
                                   title="Account is verified">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen026.svg-->
                                    <span class="svg-icon svg-icon-1 svg-icon-primary">
																	<svg xmlns="http://www.w3.org/2000/svg" width="24px"
                                                                         height="24px" viewBox="0 0 24 24">
																		<path
                                                                            d="M10.0813 3.7242C10.8849 2.16438 13.1151 2.16438 13.9187 3.7242V3.7242C14.4016 4.66147 15.4909 5.1127 16.4951 4.79139V4.79139C18.1663 4.25668 19.7433 5.83365 19.2086 7.50485V7.50485C18.8873 8.50905 19.3385 9.59842 20.2758 10.0813V10.0813C21.8356 10.8849 21.8356 13.1151 20.2758 13.9187V13.9187C19.3385 14.4016 18.8873 15.491 19.2086 16.4951V16.4951C19.7433 18.1663 18.1663 19.7433 16.4951 19.2086V19.2086C15.491 18.8873 14.4016 19.3385 13.9187 20.2758V20.2758C13.1151 21.8356 10.8849 21.8356 10.0813 20.2758V20.2758C9.59842 19.3385 8.50905 18.8873 7.50485 19.2086V19.2086C5.83365 19.7433 4.25668 18.1663 4.79139 16.4951V16.4951C5.1127 15.491 4.66147 14.4016 3.7242 13.9187V13.9187C2.16438 13.1151 2.16438 10.8849 3.7242 10.0813V10.0813C4.66147 9.59842 5.1127 8.50905 4.79139 7.50485V7.50485C4.25668 5.83365 5.83365 4.25668 7.50485 4.79139V4.79139C8.50905 5.1127 9.59842 4.66147 10.0813 3.7242V3.7242Z"
                                                                            fill="currentColor"/>
																		<path
                                                                            d="M14.8563 9.1903C15.0606 8.94984 15.3771 8.9385 15.6175 9.14289C15.858 9.34728 15.8229 9.66433 15.6185 9.9048L11.863 14.6558C11.6554 14.9001 11.2876 14.9258 11.048 14.7128L8.47656 12.4271C8.24068 12.2174 8.21944 11.8563 8.42911 11.6204C8.63877 11.3845 8.99996 11.3633 9.23583 11.5729L11.3706 13.4705L14.8563 9.1903Z"
                                                                            fill="white"/>
																	</svg>
																</span>
                                    <!--end::Svg Icon-->
                                </a>
                            </div>
                            <!--end::Name-->
                            <!--begin::Text-->
                        {{--                            <span class="fw-bold text-gray-600 fs-6 mb-2 d-block">{{$one_data->short_description}}</span>--}}
                        <!--end::Text-->
                            <!--begin::Info-->
                            <div class="d-flex align-items-center flex-wrap fw-semibold fs-7 pe-2">
                                <a href="#"
                                   class="d-flex align-items-center text-gray-400 text-hover-primary">{{$one_data->email}}</a>
                                <span class="bullet bullet-dot h-5px w-5px bg-gray-400 mx-3"></span>
                                <a href="#"
                                   class="d-flex align-items-center text-gray-400 text-hover-primary">{{$one_data->phone}}</a>
                                {{--<span class="bullet bullet-dot h-5px w-5px bg-gray-400 mx-3"></span>
                                <a href="#" class="text-gray-400 text-hover-primary">3,450 Followers</a>--}}
                            </div>
                            <!--end::Info-->
                        </div>
                        <!--end::User-->

                    </div>
                    <!--end::Info-->
                </div>
                <!--end::Details-->
            </div>
        </div>
        <!--end::Navbar-->
        <!--begin::Content-->
        <div class="flex-lg-row-fluid ms-lg-15">
            <!--begin:::Tabs-->
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                       href="#kt_customer_view_overview_tab">{{trans('members.mainData')}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                       href="#kt_customer_view_overview_events_and_logs_tab">{{trans('members.subscriptions')}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4" data-kt-countup-tabs="true" data-bs-toggle="tab"
                       href="#kt_customer_view_overview_statements">{{trans('members.inbody')}}</a>
                </li>
                <!--end:::Tab item-->
                <!--begin:::Tab item-->
                <li class="nav-item ms-auto">
                    <!--begin::Action menu-->
                    <a href="#" class="btn btn-primary ps-7" data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                       data-kt-menu-placement="bottom-end">{{trans('forms.action')}}
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                        <span class="svg-icon svg-icon-2 me-0">
														<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                             xmlns="http://www.w3.org/2000/svg">
															<path
                                                                d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                                                fill="currentColor"/>
														</svg>
													</span>
                        <!--end::Svg Icon--></a>
                    <!--begin::Menu-->
                    <div
                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold py-4 w-250px fs-6"
                        data-kt-menu="true">
                    @if (auth()->user()->hasRole('Super-Admin') || ((auth()->user()->canAny(['edit']))&&auth()->user()->id==$one_data->add_by))
                        <!--begin::Menu item-->
                            <div class="menu-item px-5 my-1">
                                <a href="{{route('admin.Members.edit', $one_data->id)}}"
                                   class="menu-link px-5">{{trans('forms.edite_btn')}}</a>
                            </div>
                            <!--end::Menu item-->
                    @endif

                    <!--begin::Menu item-->
                        <div class="menu-item px-5">
                            <a href="{{route('admin.Members.destroy', $one_data->id)}}"
                               class="menu-link text-danger px-5">{{trans('forms.delete_btn')}}</a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                    <!--end::Menu-->
                </li>
                <!--end:::Tab item-->
            </ul>
            <!--end:::Tabs-->
            <!--begin:::Tab content-->
            <div class="tab-content" id="myTabContent">
                <!--begin:::Tab pane-->
                <div class="tab-pane fade show active" id="kt_customer_view_overview_tab" role="tabpanel">

                    <!--begin::details View-->
                    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
                        <!--begin::Card header-->
                        <div class="card-header cursor-pointer">
                            <!--begin::Card title-->
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">{{trans('members.mainData')}}</h3>
                            </div>
                            <!--end::Card title-->

                        </div>
                        <!--begin::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body p-9">
                            <!--begin::Row-->
                            <div class="row mb-7">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 fw-semibold text-muted">{{trans('members.name')}}</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <span class="fw-bold fs-6 text-gray-800">{{$one_data->member_name}}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Input group-->
                            <div class="row mb-7">
                                <!--begin::Label-->
                                <label class="col-lg-4 fw-semibold text-muted">{{trans('members.birth_date')}}</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <span class="fw-semibold text-gray-800 fs-6">{{$one_data->birth_date}}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-7">
                                <!--begin::Label-->
                                <label class="col-lg-4 fw-semibold text-muted">{{trans('members.phone')}}</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 d-flex align-items-center">
                                    <span class="fw-bold fs-6 text-gray-800 me-2">{{$one_data->phone}}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-7">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 fw-semibold text-muted">{{trans('members.email')}}</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <a href="#"
                                       class="fw-semibold fs-6 text-gray-800 text-hover-primary">{{$one_data->email}}</a>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-7">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 fw-semibold text-muted">{{trans('members.health_status')}}</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <span
                                        class="fw-bold fs-6 text-gray-800">{{optional($one_data->health_status)->title}}</span>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                        @if(isset($one_data->goals)&&(!empty($one_data->goals)))

                            <!--begin::Input group-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label
                                        class="col-lg-4 fw-semibold text-muted">{{trans('members.goals')}}</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8">
                                        @foreach($one_data->goals as $goal)
                                            <span
                                                class="fw-bold fs-6 text-gray-800">{{optional($goal->subsetting_goals)->title}}</span>
                                        @endforeach
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->
                            @endif

                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::details View-->
                </div>
                <!--end:::Tab pane-->
                <!--begin:::Tab pane-->
                <div class="tab-pane fade" id="kt_customer_view_overview_events_and_logs_tab" role="tabpanel">
                    <!--begin::Card-->
                    <div class="card pt-4 mb-6 mb-xl-9">
                        <!--begin::Card header-->
                        <div class="card-header border-0">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2 class="fw-bold mb-0">{{trans('members.subscription')}}</h2>
                            </div>
                            <!--end::Card title-->

                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div id="kt_customer_view_payment_method" class="card-body pt-0">
                            @if((isset($one_data->members_subscriptions)&&(!empty($one_data->members_subscriptions))))
                                <div class="card pt-4 mb-6 mb-xl-9">

                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div id="kt_customer_view_payment_method" class="card-body pt-0">
                                    @foreach($one_data->members_subscriptions as $subscription)

                                        <!--begin::Option-->
                                            <div class="py-0" data-kt-customer-payment-method="row">
                                                <!--begin::Header-->
                                                <div class="py-3 d-flex flex-stack flex-wrap">
                                                    <!--begin::Toggle-->
                                                    <div class="d-flex align-items-center collapsible rotate collapsed"
                                                         data-bs-toggle="collapse"
                                                         href="#kt_customer_view_payment_method_{{$subscription->process_num}}"
                                                         role="button"
                                                         aria-expanded="false"
                                                         aria-controls="kt_customer_view_payment_method_{{$subscription->process_num}}">
                                                        <!--begin::Arrow-->
                                                        <div class="me-3 rotate-90">
                                                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                                            <span class="svg-icon svg-icon-3">
																				<svg width="24" height="24"
                                                                                     viewBox="0 0 24 24" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
																					<path
                                                                                        d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                                        fill="currentColor"></path>
																				</svg>
																			</span>
                                                            <!--end::Svg Icon-->
                                                        </div>
                                                        <!--end::Arrow-->
                                                        @php
                                                            $today = date('Y-m-d');
                                                            $end_date = $subscription->end_date;
                                                        @endphp
                                                        @if ($today >= $end_date)

                                                            @php
                                                                $status = trans('members.closed');
                                                                $status_class = 'danger ';
                                                            @endphp
                                                        @else
                                                            @php
                                                                $status = trans('members.opened');
                                                                $status_class = 'primary';
                                                            @endphp

                                                        @endif
                                                        <table class="table">
                                                            <tbody>
                                                            <tr>
                                                                <td class="text-muted min-w-125px w-125px">{{trans('members.main_subscription')}}
                                                                </td>
                                                                <td class="text-gray-800">{{$subscription->main_subscriptions->name}}</td>

                                                                <td class="text-muted min-w-125px w-125px">{{trans('members.start_date')}}</td>
                                                                <td class="text-gray-800">{{$subscription->start_date}}
                                                                </td>

                                                                <td class="text-muted min-w-125px w-125px">{{trans('members.end_date')}}
                                                                </td>
                                                                <td class="text-gray-800">{{$subscription->end_date}}</td>

                                                                <td class="text-muted min-w-125px w-125px">{{trans('members.status')}}
                                                                </td>
                                                                <td class="text-gray-800">
                                                                    <div
                                                                        class="badge badge-light-{{$status_class}} ms-5">{{$status}}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                    <!--end::Toggle-->
                                                    <!--begin::Toolbar-->
                                                    <div class="d-flex my-3 ms-9">
                                                        <!--begin::Edit-->
                                                        <a href="javascript:print_subscription({{$subscription->process_num}})"
                                                           class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
																			<span data-bs-toggle="tooltip"
                                                                                  data-bs-trigger="hover"
                                                                                  aria-label="{{trans('members.print')}}"
                                                                                  data-bs-original-title="{{trans('members.print')}}"
                                                                                  data-kt-initialized="1">
																				<i class="fas fa-print"></i>
																			</span>
                                                        </a>
                                                        <!--end::Edit-->
                                                        {{--<!--begin::Delete-->
                                                        <a href="#"
                                                           class="btn btn-icon btn-active-light-primary w-30px h-30px me-3"
                                                           data-bs-toggle="tooltip"
                                                           data-kt-customer-payment-method="delete" aria-label="Delete"
                                                           data-bs-original-title="Delete" data-kt-initialized="1">
                                                            <!--begin::Svg Icon | path: icons/duotune/general/gen027.svg-->
                                                            <span class="svg-icon svg-icon-3">
																				<svg width="24" height="24"
                                                                                     viewBox="0 0 24 24" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
																					<path
                                                                                        d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                                                        fill="currentColor"></path>
																					<path opacity="0.5"
                                                                                          d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                                                          fill="currentColor"></path>
																					<path opacity="0.5"
                                                                                          d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                                                          fill="currentColor"></path>
																				</svg>
																			</span>
                                                            <!--end::Svg Icon-->
                                                        </a>
                                                        <!--end::Delete-->
                                                        <!--begin::More-->
                                                        <a href="#"
                                                           class="btn btn-icon btn-active-light-primary w-30px h-30px"
                                                           data-bs-toggle="tooltip" data-kt-menu-trigger="click"
                                                           data-kt-menu-placement="bottom-end" aria-label="More Options"
                                                           data-bs-original-title="More Options"
                                                           data-kt-initialized="1">
                                                            <!--begin::Svg Icon | path: icons/duotune/general/gen019.svg-->
                                                            <span class="svg-icon svg-icon-3">
																				<svg width="24" height="24"
                                                                                     viewBox="0 0 24 24" fill="none"
                                                                                     xmlns="http://www.w3.org/2000/svg">
																					<path
                                                                                        d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z"
                                                                                        fill="currentColor"></path>
																					<path opacity="0.3"
                                                                                          d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z"
                                                                                          fill="currentColor"></path>
																				</svg>
																			</span>
                                                            <!--end::Svg Icon-->
                                                        </a>
                                                        <!--begin::Menu-->
                                                        <div
                                                            class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-150px py-3"
                                                            data-kt-menu="true">
                                                            <!--begin::Menu item-->
                                                            <div class="menu-item px-3">
                                                                <a href="#" class="menu-link px-3"
                                                                   data-kt-payment-mehtod-action="set_as_primary">Set as
                                                                    Primary</a>
                                                            </div>
                                                            <!--end::Menu item-->
                                                        </div>
                                                        <!--end::Menu-->
                                                        <!--end::More-->--}}
                                                    </div>
                                                    <!--end::Toolbar-->
                                                </div>
                                                <!--end::Header-->
                                                <!--begin::Body-->
                                                <div id="kt_customer_view_payment_method_{{$subscription->process_num}}"
                                                     class="fs-6 ps-10 collapse"
                                                     data-bs-parent="#kt_customer_view_payment_method" style="">
                                                    <!--begin::Details-->
                                                    <div class="d-flex flex-wrap py-5">
                                                        <!--begin::Col-->
                                                        <div class="flex-equal me-5">
                                                            <table class="table ">
                                                                <tbody>
                                                                <tr>
                                                                    <th class="text-muted min-w-125px w-125px">{{trans('members_subscription.process_nun')}}</th>
                                                                    <td class="text-gray-800">{{$subscription->process_num}}</td>

                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members_subscription.process_date')}}
                                                                    </td>
                                                                    <td class="text-gray-800">{{$subscription->start_date}}</td>
                                                                </tr>
                                                                {{--<tr>
                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.main_subscription')}}
                                                                    </td>
                                                                    <td class="text-gray-800">{{$subscription->main_subscriptions->name}}</td>

                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.start_date')}}</td>
                                                                    <td class="text-gray-800">{{$subscription->start_date}}
                                                                    </td>

                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.end_date')}}
                                                                    </td>
                                                                    <td class="text-gray-800">{{$subscription->end_date}}</td>
                                                                </tr>--}}
                                                                <tr>
                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.discount')}}</td>
                                                                    <td class="text-gray-800">{{$subscription->discount}}
                                                                        ({{trans('members_subscription.discount_lable')}}
                                                                        )
                                                                    </td>

                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.package_duration')}}</td>
                                                                    <td class="text-gray-800">{{$subscription->main_subscriptions->duration}}
                                                                        ({{trans('members_subscription.duration_lable')}}
                                                                        )
                                                                    </td>

                                                                    <td class="text-muted min-w-125px w-125px">{{trans('members.package_price')}} </td>
                                                                    <td class="text-gray-800">{{$subscription->main_subscriptions->price}}
                                                                        ({{trans('members_subscription.price_lable')}})
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th class="text-muted min-w-125px w-125px">{{trans('members.transportation')}} </th>
                                                                    <?php $pay_method_arr = ['yes' => trans('members.subscribed'), 'no' => trans('members.not_subscribed')] ?>

                                                                    <td class="text-gray-800"> {{$pay_method_arr[$subscription->transport]}}</td>
                                                                    @if($subscription->transport=='yes')
                                                                        <th class="text-muted min-w-125px w-125px">{{trans('members.transport_duration')}}</th>
                                                                        <td class="text-gray-800">{{$subscription->main_subscriptions->duration}}
                                                                            ({{trans('members_subscription.duration_lable')}}
                                                                            )
                                                                        </td>
                                                                        <th class="text-muted min-w-125px w-125px">{{trans('members.transport_price')}} </th>
                                                                        <td class="text-gray-800">{{$subscription->transport_value}}
                                                                            ({{trans('members_subscription.price_lable')}}
                                                                            )
                                                                        </td>
                                                                    @endif

                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <!--end::Col-->
                                                        <div class="table-responsive border-bottom mb-9 mt-20">
                                                            <table
                                                                class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                                                <thead>
                                                                <tr class="border-bottom fs-6 fw-bold ">
                                                                    <th class="min-w-175px text-center pb-2">{{trans('members_subscription.subscription')}}</th>
                                                                    <th class="min-w-175px text-center pb-2">{{trans('members_subscription.type')}}</th>
                                                                    <th class="min-w-70px text-center pb-2">{{trans('members_subscription.startDate')}}</th>
                                                                    <th class="min-w-80px text-center pb-2">{{trans('members_subscription.endDate')}}</th>
                                                                    <th class="min-w-100px text-center pb-2">{{trans('members_subscription.cost')}}</th>
                                                                    <th class="min-w-100px text-center pb-2">{{trans('members_subscription.trainer')}}</th>
                                                                    <th class="min-w-100px text-center pb-2">{{trans('members_subscription.discount')}}</th>
                                                                    <th class="min-w-100px text-center pb-2">{{trans('members_subscription.after_discount')}}</th>

                                                                </tr>
                                                                </thead>
                                                                <tbody class="fw-semibold ">

                                                                @foreach($subscription->additional_subscriptions as $item)
                                                                    <tr>
                                                                        @if($item->type == 'main')

                                                                            <td class="text-center"> {{$item->main_subscriptions->name}}</td>
                                                                        @else
                                                                            <td class="text-center"> {{$item->special_subscriptions->name}}</td>
                                                                        @endif
                                                                        <?php $type_arr = ['main' => trans('members.main'), 'special' => trans('members.special')] ?>
                                                                        <td class="text-center"> {{$type_arr[$item->type]}}</td>
                                                                        <td class="text-center"> {{$item->start_date}}</td>
                                                                        <td class="text-center"> {{$item->end_date}}</td>
                                                                        <td class="text-center"> {{$item->special_subscriptions->price}}
                                                                            ({{trans('members_subscription.price_lable')}}
                                                                            )
                                                                        </td>
                                                                        <td class="text-center"> {{$item->trainer->user_name}}</td>
                                                                        <td class="text-center"> {{$item->discount}}
                                                                            ({{trans('members_subscription.price_lable')}}
                                                                            )
                                                                        </td>
                                                                        <td class="text-center"> {{((100-$item->discount)/100)*$item->special_subscriptions->price}}
                                                                            ({{trans('members_subscription.price_lable')}}
                                                                            )
                                                                        </td>

                                                                    </tr>

                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <!--end::Details-->
                                                </div>
                                                <!--end::Body-->
                                            </div>
                                            <!--end::Option-->
                                            <div class="separator separator-dashed"></div>
                                        @endforeach

                                    </div>
                                    <!--end::Card body-->
                                </div>



                            @endif


                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end:::Tab pane-->
                <!--begin:::Tab pane-->
                <div class="tab-pane fade" id="kt_customer_view_overview_statements" role="tabpanel">

                    <!--begin::Statements-->
                    <div class="card mb-6 mb-xl-9">

                        <!--begin::Card body-->
                        <div class="card-body pb-5">
                            <div class="table-responsive border-bottom mb-9">
                                <!--begin::Table-->
                                <table class="table table-striped border rounded gy-5 gs-7  data-table"
                                       id="kt_table_customers_inbody">
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

                                    </tr>
                                    <!--end::Table row-->
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="fs-6 fw-semibold text-gray-600">
                                    @if($one_data->inbody)
                                        {{--                            {{dd($one_data->inbody)}}--}}
                                        @foreach($one_data->inbody as $row)
                                            <tr>
                                                <td style="text-align: center">{{$row->date}}</td>
                                                <td style="text-align: center">{{$row->height}}.cm</td>
                                                <td style="text-align: center">{{$row->weight}}.kg</td>
                                                <td style="text-align: center">{{$row->fat_percentage}} .%</td>
                                                <td style="text-align: center">{{$row->muscle_mass_percentage}}.%</td>
                                                <td style="text-align: center">{{$row->body_status}}</td>

                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7"
                                                style="text-align: center">{{trans('messages.no_data')}}</td>
                                        </tr>
                                    @endif

                                    </tbody>
                                    <!--end::Table body-->
                                </table>

                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Statements-->
                </div>
                <!--end:::Tab pane-->
            </div>
            <!--end:::Tab content-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content container-->


@endsection

@section('js')

    <script src="{{asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js')}}"></script>
    <script src="{{asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js')}}"></script>


    <script>
/*
        function print_subscription(subscription_num) {
            var request = $.ajax({
                // print_resrv -- print_contract
                url: "{{route('admin.subscriptions.print_member_subscription')}}" ,
                type: "POST",
                data: {subscription_num: subscription_num},
            });
            request.done(function (msg) {
                var WinPrint = window.open('', '', 'width=800,height=700,toolbar=0,scrollbars=0,status=0');
                WinPrint.document.write(msg);
                WinPrint.focus();
                // WinPrint.print();

                WinPrint.onafterprint = function () {
                    WinPrint.close();
                    console.log("Printing completed...");
                    location.href = '{{route('admin.Members.index')}}';
                }

            });
            request.fail(function (jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
            });

        }
*/
function print_subscription(subscription_num) {
    var request = $.ajax({
        url: "{{route('admin.subscriptions.print_member_subscription')}}",
        type: "POST",
        data: { subscription_num: subscription_num },
    });

    request.done(function (msg) {
        var WinPrint = window.open('', '', 'width=800,height=700,toolbar=0,scrollbars=0,status=0');

        // Write the HTML content to the new window
        WinPrint.document.write(msg);

        // Trigger print after the content is fully loaded
        WinPrint.onload = function () {
            WinPrint.print();

            WinPrint.onafterprint = function () {
                WinPrint.close();
                console.log("Printing completed...");
                location.href = '{{route('admin.Members.index')}}';
            };
        };

        WinPrint.focus();
    });

    request.fail(function (jqXHR, textStatus) {
        console.log("Request failed: " + textStatus);
    });
}

    </script>



    <script>
        // Class definition
        var KTDatatablesServerSide = function () {
            // Shared variables
            var table;
            var dt;
            var filterPayment;

            // Private functions
            var initDatatable = function () {
                dt = $('#kt_table_customers_inbody').DataTable({
                    "scrollX": true,
                    "dom": "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    "buttons": [
                        {
                            extend: 'excelHtml5',
                            text: '{{trans('forms.ExportToExcel')}}',
                            title: 'inbody for {{$one_data->member_name}}', // Set your custom title here

                            exportOptions: {
                                columns: ':visible:not(.no-export)'  // Exclude columns with class 'no-export'
                            }
                        }
                    ]
                });

                table = dt.$;


            }

            // Public methods
            return {
                init: function () {
                    initDatatable();
                }
            }
        }();
        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });
    </script>
@endsection
