{{-- @extends('dashbord.layouts.master')
@section('content')
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 20-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #3E97FF;background-image:url('assets/media/svg/shapes/widget-bg-1.png')">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">69</span>
                                <!--end::Amount-->
                                <!--begin::Subtitle-->
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Active Projects in April</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex align-items-end pt-0">
                            <!--begin::Progress-->
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                                    <span>43 Pending</span>
                                    <span>72%</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: 72%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <!--end::Progress-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget 20-->
                    <!--begin::List widget 26-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title text-gray-800 fw-bold">External Links</h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Menu-->
                                <button class="btn btn-icon btn-color-gray-400 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen023.svg-->
                                    <span class="svg-icon svg-icon-1 svg-icon-gray-300 me-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="4" fill="currentColor" />
																	<rect x="11" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="15" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="7" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--begin::Menu 2-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content fs-6 text-dark fw-bold px-3 py-4">Quick Actions</div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mb-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Ticket</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Customer</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                                        <!--begin::Menu item-->
                                        <a href="#" class="menu-link px-3">
                                            <span class="menu-title">New Group</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <!--end::Menu item-->
                                        <!--begin::Menu sub-->
                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Admin Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Staff Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Member Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Contact</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mt-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3 py-3">
                                            <a class="btn btn-primary btn-sm px-4" href="#">Generate Reports</a>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu 2-->
                                <!--end::Menu-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-5">
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <a href="#" class="text-primary fw-semibold fs-6 me-2">Avg. Client Rating</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <button type="button" class="btn btn-icon btn-sm h-auto btn-color-gray-400 btn-active-color-primary justify-content-end">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr095.svg-->
                                    <span class="svg-icon svg-icon-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path opacity="0.3" d="M4.7 17.3V7.7C4.7 6.59543 5.59543 5.7 6.7 5.7H9.8C10.2694 5.7 10.65 5.31944 10.65 4.85C10.65 4.38056 10.2694 4 9.8 4H5C3.89543 4 3 4.89543 3 6V19C3 20.1046 3.89543 21 5 21H18C19.1046 21 20 20.1046 20 19V14.2C20 13.7306 19.6194 13.35 19.15 13.35C18.6806 13.35 18.3 13.7306 18.3 14.2V17.3C18.3 18.4046 17.4046 19.3 16.3 19.3H6.7C5.59543 19.3 4.7 18.4046 4.7 17.3Z" fill="currentColor" />
																	<rect x="21.9497" y="3.46448" width="13" height="2" rx="1" transform="rotate(135 21.9497 3.46448)" fill="currentColor" />
																	<path d="M19.8284 4.97161L19.8284 9.93937C19.8284 10.5252 20.3033 11 20.8891 11C21.4749 11 21.9497 10.5252 21.9497 9.93937L21.9497 3.05029C21.9497 2.498 21.502 2.05028 20.9497 2.05028L14.0607 2.05027C13.4749 2.05027 13 2.52514 13 3.11094C13 3.69673 13.4749 4.17161 14.0607 4.17161L19.0284 4.17161C19.4702 4.17161 19.8284 4.52978 19.8284 4.97161Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <a href="#" class="text-primary fw-semibold fs-6 me-2">Instagram Followers</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <button type="button" class="btn btn-icon btn-sm h-auto btn-color-gray-400 btn-active-color-primary justify-content-end">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr095.svg-->
                                    <span class="svg-icon svg-icon-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path opacity="0.3" d="M4.7 17.3V7.7C4.7 6.59543 5.59543 5.7 6.7 5.7H9.8C10.2694 5.7 10.65 5.31944 10.65 4.85C10.65 4.38056 10.2694 4 9.8 4H5C3.89543 4 3 4.89543 3 6V19C3 20.1046 3.89543 21 5 21H18C19.1046 21 20 20.1046 20 19V14.2C20 13.7306 19.6194 13.35 19.15 13.35C18.6806 13.35 18.3 13.7306 18.3 14.2V17.3C18.3 18.4046 17.4046 19.3 16.3 19.3H6.7C5.59543 19.3 4.7 18.4046 4.7 17.3Z" fill="currentColor" />
																	<rect x="21.9497" y="3.46448" width="13" height="2" rx="1" transform="rotate(135 21.9497 3.46448)" fill="currentColor" />
																	<path d="M19.8284 4.97161L19.8284 9.93937C19.8284 10.5252 20.3033 11 20.8891 11C21.4749 11 21.9497 10.5252 21.9497 9.93937L21.9497 3.05029C21.9497 2.498 21.502 2.05028 20.9497 2.05028L14.0607 2.05027C13.4749 2.05027 13 2.52514 13 3.11094C13 3.69673 13.4749 4.17161 14.0607 4.17161L19.0284 4.17161C19.4702 4.17161 19.8284 4.52978 19.8284 4.97161Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <a href="#" class="text-primary fw-semibold fs-6 me-2">Google Ads CPC</a>
                                <!--end::Section-->
                                <!--begin::Action-->
                                <button type="button" class="btn btn-icon btn-sm h-auto btn-color-gray-400 btn-active-color-primary justify-content-end">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr095.svg-->
                                    <span class="svg-icon svg-icon-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path opacity="0.3" d="M4.7 17.3V7.7C4.7 6.59543 5.59543 5.7 6.7 5.7H9.8C10.2694 5.7 10.65 5.31944 10.65 4.85C10.65 4.38056 10.2694 4 9.8 4H5C3.89543 4 3 4.89543 3 6V19C3 20.1046 3.89543 21 5 21H18C19.1046 21 20 20.1046 20 19V14.2C20 13.7306 19.6194 13.35 19.15 13.35C18.6806 13.35 18.3 13.7306 18.3 14.2V17.3C18.3 18.4046 17.4046 19.3 16.3 19.3H6.7C5.59543 19.3 4.7 18.4046 4.7 17.3Z" fill="currentColor" />
																	<rect x="21.9497" y="3.46448" width="13" height="2" rx="1" transform="rotate(135 21.9497 3.46448)" fill="currentColor" />
																	<path d="M19.8284 4.97161L19.8284 9.93937C19.8284 10.5252 20.3033 11 20.8891 11C21.4749 11 21.9497 10.5252 21.9497 9.93937L21.9497 3.05029C21.9497 2.498 21.502 2.05028 20.9497 2.05028L14.0607 2.05027C13.4749 2.05027 13 2.52514 13 3.11094C13 3.69673 13.4749 4.17161 14.0607 4.17161L19.0284 4.17161C19.4702 4.17161 19.8284 4.52978 19.8284 4.97161Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--end::Action-->
                            </div>
                            <!--end::Item-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::LIst widget 26-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 17-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Currency-->
                                    <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">$</span>
                                    <!--end::Currency-->
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">69,700</span>
                                    <!--end::Amount-->
                                    <!--begin::Badge-->
                                    <span class="badge badge-light-success fs-base">
															<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
															<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																	<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																</svg>
															</span>
                                        <!--end::Svg Icon-->2.2%</span>
                                    <!--end::Badge-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Projects Earnings in April</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4 d-flex flex-wrap align-items-center">
                            <!--begin::Chart-->
                            <div class="d-flex flex-center me-5 pt-2">
                                <div id="kt_card_widget_17_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                            </div>
                            <!--end::Chart-->
                            <!--begin::Labels-->
                            <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                <!--begin::Label-->
                                <div class="d-flex fw-semibold align-items-center">
                                    <!--begin::Bullet-->
                                    <div class="bullet w-8px h-3px rounded-2 bg-success me-3"></div>
                                    <!--end::Bullet-->
                                    <!--begin::Label-->
                                    <div class="text-gray-500 flex-grow-1 me-4">Leaf CRM</div>
                                    <!--end::Label-->
                                    <!--begin::Stats-->
                                    <div class="fw-bolder text-gray-700 text-xxl-end">$7,660</div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Label-->
                                <!--begin::Label-->
                                <div class="d-flex fw-semibold align-items-center my-3">
                                    <!--begin::Bullet-->
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <!--end::Bullet-->
                                    <!--begin::Label-->
                                    <div class="text-gray-500 flex-grow-1 me-4">Mivy App</div>
                                    <!--end::Label-->
                                    <!--begin::Stats-->
                                    <div class="fw-bolder text-gray-700 text-xxl-end">$2,820</div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Label-->
                                <!--begin::Label-->
                                <div class="d-flex fw-semibold align-items-center">
                                    <!--begin::Bullet-->
                                    <div class="bullet w-8px h-3px rounded-2 me-3" style="background-color: #E4E6EF"></div>
                                    <!--end::Bullet-->
                                    <!--begin::Label-->
                                    <div class="text-gray-500 flex-grow-1 me-4">Others</div>
                                    <!--end::Label-->
                                    <!--begin::Stats-->
                                    <div class="fw-bolder text-gray-700 text-xxl-end">$45,257</div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Label-->
                            </div>
                            <!--end::Labels-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget 17-->
                    <!--begin::List widget 25-->
                    <div class="card card-flush h-lg-50">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title text-gray-800">Highlights</h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar d-none">
                                <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                <div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="left" class="btn btn-sm btn-light d-flex align-items-center px-4">
                                    <!--begin::Display range-->
                                    <div class="text-gray-600 fw-bold">Loading date range...</div>
                                    <!--end::Display range-->
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
                                    <span class="svg-icon svg-icon-1 ms-2 me-0">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path opacity="0.3" d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z" fill="currentColor" />
																	<path d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z" fill="currentColor" />
																	<path d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </div>
                                <!--end::Daterangepicker-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-5">
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <div class="text-gray-700 fw-semibold fs-6 me-2">Avg. Client Rating</div>
                                <!--end::Section-->
                                <!--begin::Statistics-->
                                <div class="d-flex align-items-senter">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr094.svg-->
                                    <span class="svg-icon svg-icon-2 svg-icon-success me-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="16.9497" y="8.46448" width="13" height="2" rx="1" transform="rotate(135 16.9497 8.46448)" fill="currentColor" />
																	<path d="M14.8284 9.97157L14.8284 15.8891C14.8284 16.4749 15.3033 16.9497 15.8891 16.9497C16.4749 16.9497 16.9497 16.4749 16.9497 15.8891L16.9497 8.05025C16.9497 7.49797 16.502 7.05025 15.9497 7.05025L8.11091 7.05025C7.52512 7.05025 7.05025 7.52513 7.05025 8.11091C7.05025 8.6967 7.52512 9.17157 8.11091 9.17157L14.0284 9.17157C14.4703 9.17157 14.8284 9.52975 14.8284 9.97157Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                    <!--begin::Number-->
                                    <span class="text-gray-900 fw-bolder fs-6">7.8</span>
                                    <!--end::Number-->
                                    <span class="text-gray-400 fw-bold fs-6">/10</span>
                                </div>
                                <!--end::Statistics-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <div class="text-gray-700 fw-semibold fs-6 me-2">Avg. Quotes</div>
                                <!--end::Section-->
                                <!--begin::Statistics-->
                                <div class="d-flex align-items-senter">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr093.svg-->
                                    <span class="svg-icon svg-icon-2 svg-icon-danger me-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="7.05026" y="15.5355" width="13" height="2" rx="1" transform="rotate(-45 7.05026 15.5355)" fill="currentColor" />
																	<path d="M9.17158 14.0284L9.17158 8.11091C9.17158 7.52513 8.6967 7.05025 8.11092 7.05025C7.52513 7.05025 7.05026 7.52512 7.05026 8.11091L7.05026 15.9497C7.05026 16.502 7.49797 16.9497 8.05026 16.9497L15.8891 16.9497C16.4749 16.9497 16.9498 16.4749 16.9498 15.8891C16.9498 15.3033 16.4749 14.8284 15.8891 14.8284L9.97158 14.8284C9.52975 14.8284 9.17158 14.4703 9.17158 14.0284Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                    <!--begin::Number-->
                                    <span class="text-gray-900 fw-bolder fs-6">730</span>
                                    <!--end::Number-->
                                </div>
                                <!--end::Statistics-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Section-->
                                <div class="text-gray-700 fw-semibold fs-6 me-2">Avg. Agent Earnings</div>
                                <!--end::Section-->
                                <!--begin::Statistics-->
                                <div class="d-flex align-items-senter">
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr094.svg-->
                                    <span class="svg-icon svg-icon-2 svg-icon-success me-2">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="16.9497" y="8.46448" width="13" height="2" rx="1" transform="rotate(135 16.9497 8.46448)" fill="currentColor" />
																	<path d="M14.8284 9.97157L14.8284 15.8891C14.8284 16.4749 15.3033 16.9497 15.8891 16.9497C16.4749 16.9497 16.9497 16.4749 16.9497 15.8891L16.9497 8.05025C16.9497 7.49797 16.502 7.05025 15.9497 7.05025L8.11091 7.05025C7.52512 7.05025 7.05025 7.52513 7.05025 8.11091C7.05025 8.6967 7.52512 9.17157 8.11091 9.17157L14.0284 9.17157C14.4703 9.17157 14.8284 9.52975 14.8284 9.97157Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                    <!--begin::Number-->
                                    <span class="text-gray-900 fw-bolder fs-6">$2,309</span>
                                    <!--end::Number-->
                                </div>
                                <!--end::Statistics-->
                            </div>
                            <!--end::Item-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::LIst widget 25-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xxl-6">
                    <!--begin::Tables widget 16-->
                    <div class="card card-flush h-md-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Authors Achievements</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Avg. 69.34% Conv. Rate</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Menu-->
                                <button class="btn btn-icon btn-color-gray-400 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen023.svg-->
                                    <span class="svg-icon svg-icon-1 svg-icon-gray-300 me-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="4" fill="currentColor" />
																	<rect x="11" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="15" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="7" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--begin::Menu 2-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content fs-6 text-dark fw-bold px-3 py-4">Quick Actions</div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mb-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Ticket</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Customer</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                                        <!--begin::Menu item-->
                                        <a href="#" class="menu-link px-3">
                                            <span class="menu-title">New Group</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <!--end::Menu item-->
                                        <!--begin::Menu sub-->
                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Admin Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Staff Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Member Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Contact</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mt-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3 py-3">
                                            <a class="btn btn-primary btn-sm px-4" href="#">Generate Reports</a>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu 2-->
                                <!--end::Menu-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Nav-->
                            <ul class="nav nav-pills nav-pills-custom mb-3">
                                <!--begin::Item-->
                                <li class="nav-item mb-3 me-3 me-lg-6">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2 active" id="kt_stats_widget_16_tab_link_1" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_1">
                                        <!--begin::Icon-->
                                        <div class="nav-icon mb-3">
                                            <i class="fonticon-drive fs-1 p-0"></i>
                                        </div>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">SaaS</span>
                                        <!--end::Title-->
                                        <!--begin::Bullet-->
                                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3 me-3 me-lg-6">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" id="kt_stats_widget_16_tab_link_2" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_2">
                                        <!--begin::Icon-->
                                        <div class="nav-icon mb-3">
                                            <i class="fonticon-bank fs-1 p-0"></i>
                                        </div>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Crypto</span>
                                        <!--end::Title-->
                                        <!--begin::Bullet-->
                                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3 me-3 me-lg-6">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" id="kt_stats_widget_16_tab_link_3" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_3">
                                        <!--begin::Icon-->
                                        <div class="nav-icon mb-3">
                                            <i class="fonticon-like-1 fs-1 p-0"></i>
                                        </div>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Social</span>
                                        <!--end::Title-->
                                        <!--begin::Bullet-->
                                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3 me-3 me-lg-6">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" id="kt_stats_widget_16_tab_link_4" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_4">
                                        <!--begin::Icon-->
                                        <div class="nav-icon mb-3">
                                            <i class="fonticon-remote-control fs-1 p-0"></i>
                                        </div>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Mobile</span>
                                        <!--end::Title-->
                                        <!--begin::Bullet-->
                                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3 me-3 me-lg-6">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-outline btn-flex btn-color-muted btn-active-color-primary flex-column overflow-hidden w-80px h-85px pt-5 pb-2" id="kt_stats_widget_16_tab_link_5" data-bs-toggle="pill" href="#kt_stats_widget_16_tab_5">
                                        <!--begin::Icon-->
                                        <div class="nav-icon mb-3">
                                            <i class="fonticon-telegram fs-1 p-0"></i>
                                        </div>
                                        <!--end::Icon-->
                                        <!--begin::Title-->
                                        <span class="nav-text text-gray-800 fw-bold fs-6 lh-1">Others</span>
                                        <!--end::Title-->
                                        <!--begin::Bullet-->
                                        <span class="bullet-custom position-absolute bottom-0 w-100 h-4px bg-primary"></span>
                                        <!--end::Bullet-->
                                    </a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                            </ul>
                            <!--end::Nav-->
                            <!--begin::Tab Content-->
                            <div class="tab-content">
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade show active" id="kt_stats_widget_16_tab_1">
                                    <!--begin::Table container-->
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                                <th class="p-0 pb-3 min-w-150px text-start">AUTHOR</th>
                                                <th class="p-0 pb-3 min-w-100px text-end pe-13">CONV.</th>
                                                <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                                <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-3.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Guy Hawkins</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Haiti</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">78.34%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_1_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-2.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jane Cooper</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Monaco</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">63.83%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_1_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-9.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jacob Jones</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Poland</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">92.56%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_1_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-7.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Cody Fishers</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Mexico</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">63.08%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_1_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_stats_widget_16_tab_2">
                                    <!--begin::Table container-->
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                                <th class="p-0 pb-3 min-w-150px text-start">AUTHOR</th>
                                                <th class="p-0 pb-3 min-w-100px text-end pe-13">CONV.</th>
                                                <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                                <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-25.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Brooklyn Simmons</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Poland</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">85.23%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_2_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-24.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Esther Howard</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Mexico</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">74.83%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_2_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-20.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Annette Black</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Haiti</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">90.06%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_2_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-17.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Marvin McKinney</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Monaco</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">54.08%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_2_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_stats_widget_16_tab_3">
                                    <!--begin::Table container-->
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                                <th class="p-0 pb-3 min-w-150px text-start">AUTHOR</th>
                                                <th class="p-0 pb-3 min-w-100px text-end pe-13">CONV.</th>
                                                <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                                <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-11.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jacob Jones</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">New York</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">52.34%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_3_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-23.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Ronald Richards</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Madrid</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">77.65%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_3_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-4.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Leslie Alexander</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Pune</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">82.47%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_3_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-1.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Courtney Henry</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Mexico</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">67.84%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_3_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_stats_widget_16_tab_4">
                                    <!--begin::Table container-->
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                                <th class="p-0 pb-3 min-w-150px text-start">AUTHOR</th>
                                                <th class="p-0 pb-3 min-w-100px text-end pe-13">CONV.</th>
                                                <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                                <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-12.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Arlene McCoy</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">London</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">53.44%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_4_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-21.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Marvin McKinneyr</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Monaco</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">74.64%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_4_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-30.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jacob Jones</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">PManila</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">88.56%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_4_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-14.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Esther Howard</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Iceland</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">63.16%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_4_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_stats_widget_16_tab_5">
                                    <!--begin::Table container-->
                                    <div class="table-responsive">
                                        <!--begin::Table-->
                                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                                <th class="p-0 pb-3 min-w-150px text-start">AUTHOR</th>
                                                <th class="p-0 pb-3 min-w-100px text-end pe-13">CONV.</th>
                                                <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                                <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-6.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jane Cooper</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Haiti</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">68.54%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_5_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-10.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Esther Howard</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Kiribati</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">55.83%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_5_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-9.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Jacob Jones</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Poland</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">93.46%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_5_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-circle symbol-50px me-3">
                                                            <img src="assets/media/avatars/300-3.jpg" class="" alt="" />
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <a href="../../demo1/dist/pages/user-profile/overview.html" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Ralph Edwards</a>
                                                            <span class="text-gray-400 fw-semibold d-block fs-7">Mexico</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-13">
                                                    <span class="text-gray-600 fw-bold fs-6">64.48%</span>
                                                </td>
                                                <td class="text-end pe-0">
                                                    <div id="kt_table_widget_16_chart_5_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                                </td>
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                        <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																							<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																							<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																						</svg>
																					</span>
                                                        <!--end::Svg Icon-->
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--end::Table container-->
                            </div>
                            <!--end::Tab Content-->
                        </div>
                        <!--end: Card Body-->
                    </div>
                    <!--end::Tables widget 16-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row gx-5 gx-xl-10">
                <!--begin::Col-->
                <div class="col-xxl-6 mb-5 mb-xl-10">
                    <!--begin::Chart widget 8-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Performance Overview</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Users from all channels</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <ul class="nav" id="kt_chart_widget_8_tabs">
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light fw-bold px-4 me-1" data-bs-toggle="tab" id="kt_chart_widget_8_week_toggle" href="#kt_chart_widget_8_week_tab">Month</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light fw-bold px-4 me-1 active" data-bs-toggle="tab" id="kt_chart_widget_8_month_toggle" href="#kt_chart_widget_8_month_tab">Week</a>
                                    </li>
                                </ul>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Tab content-->
                            <div class="tab-content">
                                <!--begin::Tab pane-->
                                <div class="tab-pane fade" id="kt_chart_widget_8_week_tab" role="tabpanel">
                                    <!--begin::Statistics-->
                                    <div class="mb-5">
                                        <!--begin::Statistics-->
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="fs-1 fw-semibold text-gray-400 me-1 mt-n1">$</span>
                                            <span class="fs-3x fw-bold text-gray-800 me-2 lh-1 ls-n2">18,89</span>
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->4,8%</span>
                                        </div>
                                        <!--end::Statistics-->
                                        <!--begin::Description-->
                                        <span class="fs-6 fw-semibold text-gray-400">Avarage cost per interaction</span>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Statistics-->
                                    <!--begin::Chart-->
                                    <div id="kt_chart_widget_8_week_chart" class="ms-n5 min-h-auto" style="height: 275px"></div>
                                    <!--end::Chart-->
                                    <!--begin::Items-->
                                    <div class="d-flex flex-wrap pt-5">
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column me-7 me-lg-16 pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-primary me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Social Campaigns</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-danger me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-&lt;gray-600 fs-6">Google Ads</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column me-7 me-lg-16 pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-success me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Email Newsletter</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-warning me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Courses</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-info me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">TV Campaign</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-success me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Radio</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                    </div>
                                    <!--ed::Items-->
                                </div>
                                <!--end::Tab pane-->
                                <!--begin::Tab pane-->
                                <div class="tab-pane fade active show" id="kt_chart_widget_8_month_tab" role="tabpanel">
                                    <!--begin::Statistics-->
                                    <div class="mb-5">
                                        <!--begin::Statistics-->
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="fs-1 fw-semibold text-gray-400 me-1 mt-n1">$</span>
                                            <span class="fs-3x fw-bold text-gray-800 me-2 lh-1 ls-n2">8,55</span>
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->2.2%</span>
                                        </div>
                                        <!--end::Statistics-->
                                        <!--begin::Description-->
                                        <span class="fs-6 fw-semibold text-gray-400">Avarage cost per interaction</span>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Statistics-->
                                    <!--begin::Chart-->
                                    <div id="kt_chart_widget_8_month_chart" class="ms-n5 min-h-auto" style="height: 275px"></div>
                                    <!--end::Chart-->
                                    <!--begin::Items-->
                                    <div class="d-flex flex-wrap pt-5">
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column me-7 me-lg-16 pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-primary me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Social Campaigns</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-danger me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Google Ads</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column me-7 me-lg-16 pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-success me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Email Newsletter</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-warning me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Courses</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-column pt-sm-3 pt-6">
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center mb-3 mb-sm-6">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-info me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">TV Campaign</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <!--begin::Bullet-->
                                                <span class="bullet bullet-dot bg-success me-2 h-10px w-10px"></span>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <span class="fw-bold text-gray-600 fs-6">Radio</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--ed::Item-->
                                        </div>
                                        <!--ed::Item-->
                                    </div>
                                    <!--ed::Items-->
                                </div>
                                <!--end::Tab pane-->
                            </div>
                            <!--end::Tab content-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Chart widget 8-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-6 mb-5 mb-xl-10">
                    <!--begin::Chart widget 36-->
                    <div class="card card-flush overflow-hidden h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Performance</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">1,046 Inbound Calls today</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                <div data-kt-daterangepicker="true" data-kt-daterangepicker-opens="left" data-kt-daterangepicker-range="today" class="btn btn-sm btn-light d-flex align-items-center px-4">
                                    <!--begin::Display range-->
                                    <div class="text-gray-600 fw-bold">Loading date range...</div>
                                    <!--end::Display range-->
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
                                    <span class="svg-icon svg-icon-1 ms-2 me-0">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path opacity="0.3" d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z" fill="currentColor" />
																	<path d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z" fill="currentColor" />
																	<path d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </div>
                                <!--end::Daterangepicker-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body d-flex align-items-end p-0">
                            <!--begin::Chart-->
                            <div id="kt_charts_widget_36" class="min-h-auto w-100 ps-4 pe-6" style="height: 300px"></div>
                            <!--end::Chart-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Chart widget 36-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row gx-5 gx-xl-10">
                <!--begin::Col-->
                <div class="col-xxl-4 mb-5 mb-xl-10">
                    <!--begin::List widget 8-->
                    <div class="card card-flush h-lg-100">
                        <!--begin::Header-->
                        <div class="card-header pt-7 mb-5">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Visits by Country</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">20 countries share 97% visits</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="../../demo1/dist/apps/ecommerce/sales/listing.html" class="btn btn-sm btn-light">View All</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-0">
                            <!--begin::Items-->
                            <div class="m-0">
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/united-states.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">United States</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Direct link clicks</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">9,763</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->2.6%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-3"></div>
                                <!--end::Separator-->
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/brazil.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Brasil</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">All Social Channels</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">4,062</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-danger fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1" transform="rotate(-90 11 18)" fill="currentColor" />
																				<path d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->0.4%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-3"></div>
                                <!--end::Separator-->
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/turkey.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Turkey</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Mailchimp Campaigns</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">1,680</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->0.2%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-3"></div>
                                <!--end::Separator-->
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/france.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">France</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Impact Radius visits</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">849</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->4.1%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-3"></div>
                                <!--end::Separator-->
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/india.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">India</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Many Sources</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">604</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-danger fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1" transform="rotate(-90 11 18)" fill="currentColor" />
																				<path d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->8.3%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed my-3"></div>
                                <!--end::Separator-->
                                <!--begin::Item-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Flag-->
                                    <img src="assets/media/flags/sweden.svg" class="me-4 w-25px" style="border-radius: 4px" alt="" />
                                    <!--end::Flag-->
                                    <!--begin::Section-->
                                    <div class="d-flex flex-stack flex-row-fluid d-grid gap-2">
                                        <!--begin::Content-->
                                        <div class="me-5">
                                            <!--begin::Title-->
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Sweden</a>
                                            <!--end::Title-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Social Network</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Content-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Number-->
                                            <span class="text-gray-800 fw-bold fs-6 me-3 d-block">237</span>
                                            <!--end::Number-->
                                            <!--begin::Label-->
                                            <div class="m-0">
                                                <!--begin::Label-->
                                                <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                    <!--end::Svg Icon-->1.9%</span>
                                                <!--end::Label-->
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::LIst widget 8-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xxl-4 mb-5 mb-xl-10">
                    <!--begin::List widget 9-->
                    <div class="card card-flush h-xl-100">
                        <!--begin::Header-->
                        <div class="card-header py-7">
                            <!--begin::Statistics-->
                            <div class="m-0">
                                <!--begin::Heading-->
                                <div class="d-flex align-items-center mb-2">
                                    <!--begin::Title-->
                                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">5,037</span>
                                    <!--end::Title-->
                                    <!--begin::Label-->
                                    <span class="badge badge-light-success fs-base">
															<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
															<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																	<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																</svg>
															</span>
                                        <!--end::Svg Icon-->2.2%</span>
                                    <!--end::Label-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Description-->
                                <span class="fs-6 fw-semibold text-gray-400">Visits by Social Networks</span>
                                <!--end::Description-->
                            </div>
                            <!--end::Statistics-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Menu-->
                                <button class="btn btn-icon btn-color-gray-400 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen023.svg-->
                                    <span class="svg-icon svg-icon-1 svg-icon-gray-300 me-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="4" fill="currentColor" />
																	<rect x="11" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="15" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="7" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--begin::Menu 2-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content fs-6 text-dark fw-bold px-3 py-4">Quick Actions</div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mb-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Ticket</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Customer</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                                        <!--begin::Menu item-->
                                        <a href="#" class="menu-link px-3">
                                            <span class="menu-title">New Group</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <!--end::Menu item-->
                                        <!--begin::Menu sub-->
                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Admin Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Staff Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Member Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Contact</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mt-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3 py-3">
                                            <a class="btn btn-primary btn-sm px-4" href="#">Generate Reports</a>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu 2-->
                                <!--end::Menu-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body card-body d-flex justify-content-between flex-column pt-3">
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Flag-->
                                <img src="assets/media/svg/brand-logos/dribbble-icon-1.svg" class="me-4 w-30px" style="border-radius: 4px" alt="" />
                                <!--end::Flag-->
                                <!--begin::Section-->
                                <div class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2">
                                    <!--begin::Content-->
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Dribbble</a>
                                        <!--end::Title-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Community</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Number-->
                                        <span class="text-gray-800 fw-bold fs-4 me-3">579</span>
                                        <!--end::Number-->
                                        <!--begin::Info-->
                                        <div class="m-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->2.6%</span>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Section-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Flag-->
                                <img src="assets/media/svg/brand-logos/linkedin-1.svg" class="me-4 w-30px" style="border-radius: 4px" alt="" />
                                <!--end::Flag-->
                                <!--begin::Section-->
                                <div class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2">
                                    <!--begin::Content-->
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Linked In</a>
                                        <!--end::Title-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Social Media</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Number-->
                                        <span class="text-gray-800 fw-bold fs-4 me-3">1,088</span>
                                        <!--end::Number-->
                                        <!--begin::Info-->
                                        <div class="m-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-danger fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1" transform="rotate(-90 11 18)" fill="currentColor" />
																			<path d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->0.4%</span>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Section-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Flag-->
                                <img src="assets/media/svg/brand-logos/slack-icon.svg" class="me-4 w-30px" style="border-radius: 4px" alt="" />
                                <!--end::Flag-->
                                <!--begin::Section-->
                                <div class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2">
                                    <!--begin::Content-->
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Slack</a>
                                        <!--end::Title-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Messanger</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Number-->
                                        <span class="text-gray-800 fw-bold fs-4 me-3">794</span>
                                        <!--end::Number-->
                                        <!--begin::Info-->
                                        <div class="m-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->0.2%</span>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Section-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Flag-->
                                <img src="assets/media/svg/brand-logos/youtube-3.svg" class="me-4 w-30px" style="border-radius: 4px" alt="" />
                                <!--end::Flag-->
                                <!--begin::Section-->
                                <div class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2">
                                    <!--begin::Content-->
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">YouTube</a>
                                        <!--end::Title-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Video Channel</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Number-->
                                        <span class="text-gray-800 fw-bold fs-4 me-3">978</span>
                                        <!--end::Number-->
                                        <!--begin::Info-->
                                        <div class="m-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->4.1%</span>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Section-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Separator-->
                            <div class="separator separator-dashed my-3"></div>
                            <!--end::Separator-->
                            <!--begin::Item-->
                            <div class="d-flex flex-stack">
                                <!--begin::Flag-->
                                <img src="assets/media/svg/brand-logos/instagram-2-1.svg" class="me-4 w-30px" style="border-radius: 4px" alt="" />
                                <!--end::Flag-->
                                <!--begin::Section-->
                                <div class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2">
                                    <!--begin::Content-->
                                    <div class="me-5">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">Instagram</a>
                                        <!--end::Title-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-400 fw-semibold fs-7 d-block text-start ps-0">Social Network</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Wrapper-->
                                    <div class="d-flex align-items-center">
                                        <!--begin::Number-->
                                        <span class="text-gray-800 fw-bold fs-4 me-3">1,458</span>
                                        <!--end::Number-->
                                        <!--begin::Info-->
                                        <div class="m-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																	<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																	<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																			<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																		</svg>
																	</span>
                                                <!--end::Svg Icon-->8.3%</span>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Info-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Section-->
                            </div>
                            <!--end::Item-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::List widget 9-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xxl-4 mb-5 mb-xl-10">
                    <!--begin::Engage widget 11-->
                    <div class="card card-flush h-xl-100" style="background-color: #202B46; background-image:url('assets/media/svg/shapes/widget-bg-2.png')" data-bs-theme="light">
                        <!--begin::Body-->
                        <div class="card-body d-flex flex-column justify-content-between mt-6 bgi-no-repeat bgi-size-cover bgi-position-x-center">
                            <!--begin::Wrapper-->
                            <div class="mb-10">
                                <!--begin::Title-->
                                <div class="fs-1 fw-bold text-white text-center mb-9">
														<span class="me-2">Analyse Your
														<br />
														<span class="position-relative d-inline-block">
															<a href="../../demo1/dist/pages/user-profile/overview.html" class="text-success opacity-75-hover">Infrastructure</a>
                                                            <!--begin::Separator-->
															<span class="position-absolute opacity-25 bottom-0 start-0 border-4 border-success border-bottom w-100"></span>
                                                            <!--end::Separator-->
														</span></span>with Keen</div>
                                <!--end::Title-->
                                <!--begin::Action-->
                                <div class="text-center">
                                    <a href='#' class="btn btn-sm btn-color-white bg-body bg-opacity-15 bg-hover-opacity-25 fw-bold fs-7" data-bs-toggle="modal" data-bs-target="#kt_modal_upgrade_plan">Get Started</a>
                                </div>
                                <!--begin::Action-->
                            </div>
                            <!--begin::Wrapper-->
                            <!--begin::Illustration-->
                            <img class="mx-auto h-150px h-lg-200px mb-11" src="assets/media/svg/illustrations/sigma/illustration-realestate.svg" alt="" />
                            <!--end::Illustration-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Engage widget 11-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-xl-4">
                    <!--begin::Chart Widget 35-->
                    <div class="card card-flush h-md-100">
                        <!--begin::Header-->
                        <div class="card-header pt-5 mb-6">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <!--begin::Statistics-->
                                <div class="d-flex align-items-center mb-2">
                                    <!--begin::Currency-->
                                    <span class="fs-3 fw-semibold text-gray-400 align-self-start me-1">$</span>
                                    <!--end::Currency-->
                                    <!--begin::Value-->
                                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2">3,274.94</span>
                                    <!--end::Value-->
                                    <!--begin::Label-->
                                    <span class="badge badge-light-success fs-base">
															<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
															<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																	<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																</svg>
															</span>
                                        <!--end::Svg Icon-->9.2%</span>
                                    <!--end::Label-->
                                </div>
                                <!--end::Statistics-->
                                <!--begin::Description-->
                                <span class="fs-6 fw-semibold text-gray-400">Avg. Agent Earnings</span>
                                <!--end::Description-->
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Menu-->
                                <button class="btn btn-icon btn-color-gray-400 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    <!--begin::Svg Icon | path: icons/duotune/general/gen023.svg-->
                                    <span class="svg-icon svg-icon-1 svg-icon-gray-300 me-n1">
																<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="4" fill="currentColor" />
																	<rect x="11" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="15" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																	<rect x="7" y="11" width="2.6" height="2.6" rx="1.3" fill="currentColor" />
																</svg>
															</span>
                                    <!--end::Svg Icon-->
                                </button>
                                <!--begin::Menu 2-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content fs-6 text-dark fw-bold px-3 py-4">Quick Actions</div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mb-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Ticket</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Customer</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                                        <!--begin::Menu item-->
                                        <a href="#" class="menu-link px-3">
                                            <span class="menu-title">New Group</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <!--end::Menu item-->
                                        <!--begin::Menu sub-->
                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Admin Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Staff Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">Member Group</a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">New Contact</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mt-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3 py-3">
                                            <a class="btn btn-primary btn-sm px-4" href="#">Generate Reports</a>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu 2-->
                                <!--end::Menu-->
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body py-0 px-0">
                            <!--begin::Nav-->
                            <ul class="nav d-flex justify-content-between mb-3 mx-9">
                                <!--begin::Item-->
                                <li class="nav-item mb-3">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-flex flex-center btn-active-danger btn-color-gray-600 btn-active-color-white rounded-2 w-45px h-35px active" data-bs-toggle="tab" id="kt_charts_widget_35_tab_1" href="#kt_charts_widget_35_tab_content_1">1d</a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-flex flex-center btn-active-danger btn-color-gray-600 btn-active-color-white rounded-2 w-45px h-35px" data-bs-toggle="tab" id="kt_charts_widget_35_tab_2" href="#kt_charts_widget_35_tab_content_2">5d</a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-flex flex-center btn-active-danger btn-color-gray-600 btn-active-color-white rounded-2 w-45px h-35px" data-bs-toggle="tab" id="kt_charts_widget_35_tab_3" href="#kt_charts_widget_35_tab_content_3">1m</a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-flex flex-center btn-active-danger btn-color-gray-600 btn-active-color-white rounded-2 w-45px h-35px" data-bs-toggle="tab" id="kt_charts_widget_35_tab_4" href="#kt_charts_widget_35_tab_content_4">6m</a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item mb-3">
                                    <!--begin::Link-->
                                    <a class="nav-link btn btn-flex flex-center btn-active-danger btn-color-gray-600 btn-active-color-white rounded-2 w-45px h-35px" data-bs-toggle="tab" id="kt_charts_widget_35_tab_5" href="#kt_charts_widget_35_tab_content_5">1y</a>
                                    <!--end::Link-->
                                </li>
                                <!--end::Item-->
                            </ul>
                            <!--end::Nav-->
                            <!--begin::Tab Content-->
                            <div class="tab-content mt-n6">
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade active show" id="kt_charts_widget_35_tab_content_1">
                                    <!--begin::Chart-->
                                    <div id="kt_charts_widget_35_chart_1" data-kt-chart-color="primary" class="min-h-auto h-200px ps-3 pe-6"></div>
                                    <!--end::Chart-->
                                    <!--begin::Table container-->
                                    <div class="table-responsive mx-9 mt-n6">
                                        <!--begin::Table-->
                                        <table class="table align-middle gs-0 gy-4">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr>
                                                <th class="min-w-100px"></th>
                                                <th class="min-w-100px text-end pe-0"></th>
                                                <th class="text-end min-w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">2:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-danger">-139.34</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">3:10 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$3,207.03</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-success">+576.24</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">3:55 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$3,274.94</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-success">+124.03</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_charts_widget_35_tab_content_2">
                                    <!--begin::Chart-->
                                    <div id="kt_charts_widget_35_chart_2" data-kt-chart-color="primary" class="min-h-auto h-200px ps-3 pe-6"></div>
                                    <!--end::Chart-->
                                    <!--begin::Table container-->
                                    <div class="table-responsive mx-9 mt-n6">
                                        <!--begin::Table-->
                                        <table class="table align-middle gs-0 gy-4">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr>
                                                <th class="min-w-100px"></th>
                                                <th class="min-w-100px text-end pe-0"></th>
                                                <th class="text-end min-w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">4:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,345.45</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-success">+134.02</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">11:35 AM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-primary">-124.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">3:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$1,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-danger">+144.04</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_charts_widget_35_tab_content_3">
                                    <!--begin::Chart-->
                                    <div id="kt_charts_widget_35_chart_3" data-kt-chart-color="primary" class="min-h-auto h-200px ps-3 pe-6"></div>
                                    <!--end::Chart-->
                                    <!--begin::Table container-->
                                    <div class="table-responsive mx-9 mt-n6">
                                        <!--begin::Table-->
                                        <table class="table align-middle gs-0 gy-4">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr>
                                                <th class="min-w-100px"></th>
                                                <th class="min-w-100px text-end pe-0"></th>
                                                <th class="text-end min-w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">3:20 AM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$3,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-primary">+185.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">12:30 AM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-danger">+124.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">4:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-success">-154.03</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_charts_widget_35_tab_content_4">
                                    <!--begin::Chart-->
                                    <div id="kt_charts_widget_35_chart_4" data-kt-chart-color="primary" class="min-h-auto h-200px ps-3 pe-6"></div>
                                    <!--end::Chart-->
                                    <!--begin::Table container-->
                                    <div class="table-responsive mx-9 mt-n6">
                                        <!--begin::Table-->
                                        <table class="table align-middle gs-0 gy-4">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr>
                                                <th class="min-w-100px"></th>
                                                <th class="min-w-100px text-end pe-0"></th>
                                                <th class="text-end min-w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">2:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-warning">+124.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">5:30 AM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$1,756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-info">+144.65</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">4:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,085.25</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-primary">+154.06</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                                <!--begin::Tap pane-->
                                <div class="tab-pane fade" id="kt_charts_widget_35_tab_content_5">
                                    <!--begin::Chart-->
                                    <div id="kt_charts_widget_35_chart_5" data-kt-chart-color="primary" class="min-h-auto h-200px ps-3 pe-6"></div>
                                    <!--end::Chart-->
                                    <!--begin::Table container-->
                                    <div class="table-responsive mx-9 mt-n6">
                                        <!--begin::Table-->
                                        <table class="table align-middle gs-0 gy-4">
                                            <!--begin::Table head-->
                                            <thead>
                                            <tr>
                                                <th class="min-w-100px"></th>
                                                <th class="min-w-100px text-end pe-0"></th>
                                                <th class="text-end min-w-50px"></th>
                                            </tr>
                                            </thead>
                                            <!--end::Table head-->
                                            <!--begin::Table body-->
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">2:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$2,045.04</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-warning">+114.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">3:30 AM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-primary">-124.03</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-gray-600 fw-bold fs-6">10:30 PM</a>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="text-gray-800 fw-bold fs-6 me-1">$1.756.26</span>
                                                </td>
                                                <td class="pe-0 text-end">
                                                    <span class="fw-bold fs-6 text-info">+165.86</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                            <!--end::Table body-->
                                        </table>
                                        <!--end::Table-->
                                    </div>
                                    <!--end::Table container-->
                                </div>
                                <!--end::Tap pane-->
                            </div>
                            <!--end::Tab Content-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Chart Widget 35-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-8">
                    <!--begin::Table widget 14-->
                    <div class="card card-flush h-md-100">
                        <!--begin::Header-->
                        <div class="card-header pt-7">
                            <!--begin::Title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Projects Stats</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Updated 37 minutes ago</span>
                            </h3>
                            <!--end::Title-->
                            <!--begin::Toolbar-->
                            <div class="card-toolbar">
                                <a href="../../demo1/dist/apps/ecommerce/catalog/add-product.html" class="btn btn-sm btn-light">History</a>
                            </div>
                            <!--end::Toolbar-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body pt-6">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <!--begin::Table head-->
                                    <thead>
                                    <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                        <th class="p-0 pb-3 min-w-175px text-start">ITEM</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">BUDGET</th>
                                        <th class="p-0 pb-3 min-w-100px text-end">PROGRESS</th>
                                        <th class="p-0 pb-3 min-w-175px text-end pe-12">STATUS</th>
                                        <th class="p-0 pb-3 w-125px text-end pe-7">CHART</th>
                                        <th class="p-0 pb-3 w-50px text-end">VIEW</th>
                                    </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="assets/media/stock/600x600/img-49.jpg" class="" alt="" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Mivy App</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">Jane Cooper</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">$32,400</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                <!--end::Svg Icon-->9.2%</span>
                                            <!--end::Label-->
                                        </td>
                                        <td class="text-end pe-12">
                                            <span class="badge py-3 px-4 fs-7 badge-light-primary">In Process</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div id="kt_table_widget_14_chart_1" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																					<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																				</svg>
																			</span>
                                                <!--end::Svg Icon-->
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="assets/media/stock/600x600/img-40.jpg" class="" alt="" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Avionica</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">Esther Howard</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">$256,910</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-danger fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1" transform="rotate(-90 11 18)" fill="currentColor" />
																				<path d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z" fill="currentColor" />
																			</svg>
																		</span>
                                                <!--end::Svg Icon-->0.4%</span>
                                            <!--end::Label-->
                                        </td>
                                        <td class="text-end pe-12">
                                            <span class="badge py-3 px-4 fs-7 badge-light-warning">On Hold</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div id="kt_table_widget_14_chart_2" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																					<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																				</svg>
																			</span>
                                                <!--end::Svg Icon-->
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="assets/media/stock/600x600/img-39.jpg" class="" alt="" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Charto CRM</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">Jenny Wilson</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">$8,220</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                <!--end::Svg Icon-->9.2%</span>
                                            <!--end::Label-->
                                        </td>
                                        <td class="text-end pe-12">
                                            <span class="badge py-3 px-4 fs-7 badge-light-primary">In Process</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div id="kt_table_widget_14_chart_3" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																					<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																				</svg>
																			</span>
                                                <!--end::Svg Icon-->
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="assets/media/stock/600x600/img-47.jpg" class="" alt="" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">Tower Hill</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">Cody Fisher</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">$74,000</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-success fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-success ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
																				<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
																			</svg>
																		</span>
                                                <!--end::Svg Icon-->9.2%</span>
                                            <!--end::Label-->
                                        </td>
                                        <td class="text-end pe-12">
                                            <span class="badge py-3 px-4 fs-7 badge-light-success">Complated</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div id="kt_table_widget_14_chart_4" class="h-50px mt-n8 pe-7" data-kt-chart-color="success"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																					<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																				</svg>
																			</span>
                                                <!--end::Svg Icon-->
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <img src="assets/media/stock/600x600/img-48.jpg" class="" alt="" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">9 Degree</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">Savannah Nguyen</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-gray-600 fw-bold fs-6">$183,300</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <!--begin::Label-->
                                            <span class="badge badge-light-danger fs-base">
																		<!--begin::Svg Icon | path: icons/duotune/arrows/arr065.svg-->
																		<span class="svg-icon svg-icon-5 svg-icon-danger ms-n1">
																			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																				<rect opacity="0.5" x="11" y="18" width="13" height="2" rx="1" transform="rotate(-90 11 18)" fill="currentColor" />
																				<path d="M11.4343 15.4343L7.25 11.25C6.83579 10.8358 6.16421 10.8358 5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75L11.2929 18.2929C11.6834 18.6834 12.3166 18.6834 12.7071 18.2929L18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25C17.8358 10.8358 17.1642 10.8358 16.75 11.25L12.5657 15.4343C12.2533 15.7467 11.7467 15.7467 11.4343 15.4343Z" fill="currentColor" />
																			</svg>
																		</span>
                                                <!--end::Svg Icon-->0.4%</span>
                                            <!--end::Label-->
                                        </td>
                                        <td class="text-end pe-12">
                                            <span class="badge py-3 px-4 fs-7 badge-light-primary">In Process</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            <div id="kt_table_widget_14_chart_5" class="h-50px mt-n8 pe-7" data-kt-chart-color="danger"></div>
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr001.svg-->
                                                <span class="svg-icon svg-icon-5 svg-icon-gray-700">
																				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M14.4 11H3C2.4 11 2 11.4 2 12C2 12.6 2.4 13 3 13H14.4V11Z" fill="currentColor" />
																					<path opacity="0.3" d="M14.4 20V4L21.7 11.3C22.1 11.7 22.1 12.3 21.7 12.7L14.4 20Z" fill="currentColor" />
																				</svg>
																			</span>
                                                <!--end::Svg Icon-->
                                            </a>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end: Card Body-->
                    </div>
                    <!--end::Table widget 14-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
@endsection --}}

{{-- @extends('dashbord.layouts.master')
@section('content')
<div class="row">
    <!-- Employees Card -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #3E97FF;background-image:url('assets/media/svg/shapes/widget-bg-1.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">150</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Employees</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients Card -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #28A745;background-image:url('assets/media/svg/shapes/widget-bg-2.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">300</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Clients</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paid Invoices -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #17A2B8;background-image:url('assets/media/svg/shapes/widget-bg-3.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">120</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Paid Invoices</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 80%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unpaid Invoices -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #FFC107;background-image:url('assets/media/svg/shapes/widget-bg-4.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">30</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Unpaid Invoices</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 20%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenues -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #DC3545;background-image:url('assets/media/svg/shapes/widget-bg-5.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">$50,000</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Revenues</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 90%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses (Masrofat) -->
    <div class="col-md-4">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5"
            style="background-color: #6610F2;background-image:url('assets/media/svg/shapes/widget-bg-6.png')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">$10,000</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Expenses</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                        <div class="bg-white rounded h-8px" role="progressbar" style="width: 40%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection --}}

@can('view_dashboard')
    @extends('dashbord.layouts.master')
    @section('content')
        {{-- <div class="container mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Employees</div>
                        <div class="card-body">
                            <h5 class="card-title">100</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Clients</div>
                        <div class="card-body">
                            <h5 class="card-title">250</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Paid Invoices</div>
                        <div class="card-body">
                            <h5 class="card-title">$50,000</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-header">Unpaid Invoices</div>
                        <div class="card-body">
                            <h5 class="card-title">$15,000</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Revenues</div>
                        <div class="card-body">
                            <h5 class="card-title">$75,000</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white mb-3" style="background-color: #44b4b0;">
                        <div class="card-header">Masrofat</div>
                        <div class="card-body">
                            <h5 class="card-title">$20,000</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div> --}}
@php
$dashboardData = get_dashboard_data();
@endphp

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="container-fluid px-4 mt-4">
    <!-- Statistics Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي المستخدمين</p>
                            <h3 class="stat-value text-white mb-2">{{ $dashboardData['users_count'] }}</h3>
                            <div class="stat-details">
                                <span class="badge bg-white bg-opacity-25 text-white me-1">نشط: {{ $dashboardData['active_users_count'] }}</span>
                                <span class="badge bg-white bg-opacity-25 text-white">غير نشط: {{ $dashboardData['inactive_users_count'] }}</span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي الموظفين</p>
                            <h3 class="stat-value text-white mb-2">{{ $dashboardData['employees_count'] }}</h3>
                            <div class="stat-details">
                                <span class="badge bg-white bg-opacity-25 text-white me-1">نشط: {{ $dashboardData['active_employees_count'] }}</span>
                                <span class="badge bg-white bg-opacity-25 text-white">غير نشط: {{ $dashboardData['inactive_employees_count'] }}</span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Clients -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي العملاء</p>
                            <h3 class="stat-value text-white mb-2">{{ $dashboardData['total_clients_count'] }}</h3>
                            <div class="stat-details">
                                <span class="badge bg-white bg-opacity-25 text-white me-1">نشط: {{ $dashboardData['active_clients_count'] }}</span>
                                <span class="badge bg-white bg-opacity-25 text-white">غير نشط: {{ $dashboardData['inactive_clients_count'] }}</span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Clients -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">العملاء الجدد</p>
                            <h3 class="stat-value text-white mb-2">{{ $dashboardData['new_clients_count'] }}</h3>
                            <div class="stat-details">
                                <span class="badge bg-white bg-opacity-25 text-white">آخر 30 يوم</span>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Types -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6 col-md-6">
            <div class="stat-card stat-card-success-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">عملاء الإنترنت</p>
                            <h3 class="stat-value text-white mb-0">{{ $dashboardData['internet_clients_count'] }}</h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-wifi"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6">
            <div class="stat-card stat-card-primary-secondary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">عملاء الساتلايت</p>
                            <h3 class="stat-value text-white mb-0">{{ $dashboardData['satellite_clients_count'] }}</h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-satellite"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-dark h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي الفواتير</p>
                            <h3 class="stat-value text-white mb-1">{{ $dashboardData['total_invoices_count'] }}</h3>
                            <p class="stat-amount text-white mb-0">${{ number_format($dashboardData['total_invoices_amount'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">الفواتير المدفوعة</p>
                            <h3 class="stat-value text-white mb-1">{{ $dashboardData['paid_invoices_count'] }}</h3>
                            <p class="stat-amount text-white mb-0">${{ number_format($dashboardData['paid_invoices_amount'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">الفواتير غير المدفوعة</p>
                            <h3 class="stat-value text-white mb-1">{{ $dashboardData['unpaid_invoices_count'] }}</h3>
                            <p class="stat-amount text-white mb-0">${{ number_format($dashboardData['unpaid_invoices_amount'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">الفواتير المدفوعة جزئياً</p>
                            <h3 class="stat-value text-white mb-1">{{ $dashboardData['partial_invoices_count'] }}</h3>
                            <p class="stat-amount text-white mb-0">${{ number_format($dashboardData['partial_invoices_amount'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي المدفوع</p>
                            <h3 class="stat-value text-white mb-0">${{ number_format($dashboardData['total_paid'], 2) }}</h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي المتبقي</p>
                            <h3 class="stat-value text-white mb-0">${{ number_format($dashboardData['total_remaining'], 2) }}</h3>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي الإيرادات</p>
                            <h3 class="stat-value text-white mb-1">${{ number_format($dashboardData['total_revenues'], 2) }}</h3>
                            <p class="stat-subtitle text-white-50 mb-0">هذا الشهر: ${{ number_format($dashboardData['monthly_revenues'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label text-white-50 mb-2">إجمالي المصروفات</p>
                            <h3 class="stat-value text-white mb-1">${{ number_format($dashboardData['total_expenses'], 2) }}</h3>
                            <p class="stat-subtitle text-white-50 mb-0">هذا الشهر: ${{ number_format($dashboardData['monthly_expenses'], 2) }}</p>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-graph-down-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Monthly Revenues vs Expenses Chart -->
        <div class="col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">الإيرادات والمصروفات الشهرية (آخر 6 أشهر)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Invoice Status Distribution -->
        <div class="col-xl-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">توزيع حالة الفواتير</h5>
                </div>
                <div class="card-body">
                    <canvas id="invoiceStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Client Type Distribution -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">توزيع أنواع العملاء</h5>
                </div>
                <div class="card-body">
                    <canvas id="clientTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Invoices Chart -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">الفواتير الشهرية (آخر 6 أشهر)</h5>
                </div>
                <div class="card-body">
                    <canvas id="invoicesChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Activities Row -->
    <div class="row g-4 mb-4">
        <!-- Today's Logs -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        آخر السجلات اليوم
                    </h5>
                    <span class="badge bg-primary">{{ $dashboardData['today_logs']->count() }}</span>
                </div>
                <div class="card-body p-10">
                    @if($dashboardData['today_logs']->count() > 0)
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-start" style="width: 20%;">الوقت</th>
                                        <th class="text-start" style="width: 25%;">الإجراء</th>
                                        <th class="text-start" style="width: 40%;">الوصف</th>
                                        <th class="text-start" style="width: 15%;">المستخدم</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['today_logs'] as $log)
                                        <tr>
                                            <td class="text-nowrap">
                                                <small class="text-muted">{{ $log->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $actionClass = match($log->action) {
                                                        'invoice_paid' => 'badge bg-success',
                                                        'invoice_created' => 'badge bg-info',
                                                        'invoice_deleted' => 'badge bg-danger',
                                                        'client_created' => 'badge bg-success',
                                                        'client_updated' => 'badge bg-primary',
                                                        'client_deleted' => 'badge bg-danger',
                                                        'user_login' => 'badge bg-secondary',
                                                        'financial_transaction_created' => 'badge bg-success',
                                                        default => 'badge bg-secondary'
                                                    };
                                                @endphp
                                                <span class="{{ $actionClass }} badge-sm">
                                                    {{ trans('logs.' . $log->action, [], 'ar') ?? $log->action }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-dark">{{ mb_substr($log->description, 0, 50) }}{{ mb_strlen($log->description) > 50 ? '...' : '' }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $log->user ? $log->user->name : 'System' }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">لا توجد سجلات اليوم</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>
                        عرض جميع السجلات
                    </a>
                </div>
            </div>
        </div>

        <!-- Today's Paid Invoices -->
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-receipt-check text-success me-2"></i>
                        الفواتير المدفوعة اليوم
                    </h5>
                    <span class="badge bg-success">{{ $dashboardData['today_paid_invoices']->count() }}</span>
                </div>
                <div class="card-body p-10">
                    @if($dashboardData['today_paid_invoices']->count() > 0)
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-start" style="width: 15%;">رقم الفاتورة</th>
                                        <th class="text-start" style="width: 25%;">العميل</th>
                                        <th class="text-end" style="width: 15%;">المبلغ</th>
                                        <th class="text-end" style="width: 15%;">المدفوع</th>
                                        <th class="text-end" style="width: 15%;">المتبقي</th>
                                        <th class="text-center" style="width: 15%;">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['today_paid_invoices'] as $invoice)
                                        <tr>
                                            <td>
                                                @php
                                                    $prefix = $invoice->client && $invoice->client->client_type == 'satellite' ? 'SA-' : 'IN-';
                                                @endphp
                                                <small class="fw-bold">{{ $prefix }}{{ $invoice->invoice_number }}</small>
                                            </td>
                                            <td>
                                                <small class="text-dark">
                                                    {{ $invoice->client ? $invoice->client->name : 'N/A' }}
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <small class="fw-bold">${{ number_format($invoice->amount, 2) }}</small>
                                            </td>
                                            <td class="text-end">
                                                <small class="text-success fw-bold">${{ number_format($invoice->paid_amount, 2) }}</small>
                                            </td>
                                            <td class="text-end">
                                                <small class="{{ $invoice->remaining_amount > 0 ? 'text-warning' : 'text-success' }} fw-bold">
                                                    ${{ number_format($invoice->remaining_amount, 2) }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                @if($invoice->status == 'paid')
                                                    <span class="badge bg-success">مدفوعة</span>
                                                @elseif($invoice->status == 'partial')
                                                    <span class="badge bg-warning text-dark">جزئية</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-success">
                                    المجموع: ${{ number_format($dashboardData['today_paid_invoices']->sum('paid_amount'), 2) }}
                                </strong>
                                <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-eye me-1"></i>
                                    عرض جميع الفواتير
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">لا توجد فواتير مدفوعة اليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Users Invoices Today Row -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people text-info me-2"></i>
                        إجمالي الفواتير لكل مستخدم اليوم
                    </h5>
                    <span class="badge bg-info">{{ $dashboardData['today_users_invoices']->count() }}</span>
                </div>
                <div class="card-body p-10">
                    @if($dashboardData['today_users_invoices']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start" style="width: 5%;">#</th>
                                        <th class="text-start" style="width: 35%;">المستخدم</th>
                                        <th class="text-center" style="width: 20%;">عدد الفواتير</th>
                                        <th class="text-end" style="width: 25%;">إجمالي المبلغ</th>
                                        <th class="text-center" style="width: 15%;">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['today_users_invoices'] as $index => $userData)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-circle symbol-40px me-3">
                                                        <div class="symbol-label bg-primary bg-opacity-10 text-primary fw-bold">
                                                            {{ mb_substr($userData['user_name'], 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-dark">{{ $userData['user_name'] }}</span>
                                                        @if($userData['is_employee'] ?? false)
                                                            <br>
                                                            <small class="text-muted">موظف</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6 px-3 py-2">
                                                    {{ $userData['invoices_count'] }}
                                                    <small class="ms-1">فاتورة</small>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success fs-5">
                                                    ${{ number_format($userData['total_amount'], 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $totalAmount = $dashboardData['today_users_invoices']->sum(function($item) { return $item['total_amount']; });
                                                    $percentage = $totalAmount > 0 
                                                        ? ($userData['total_amount'] / $totalAmount) * 100 
                                                        : 0;
                                                @endphp
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge bg-success mb-1">{{ number_format($percentage, 1) }}%</span>
                                                    <div class="progress" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: {{ min($percentage, 100) }}%" 
                                                             aria-valuenow="{{ $percentage }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2" class="text-end">المجموع:</th>
                                        <th class="text-center">
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                {{ $dashboardData['today_users_invoices']->sum(function($item) { return $item['invoices_count']; }) }}
                                                <small class="ms-1">فاتورة</small>
                                            </span>
                                        </th>
                                        <th class="text-end">
                                            <span class="fw-bold text-success fs-5">
                                                ${{ number_format($dashboardData['today_users_invoices']->sum(function($item) { return $item['total_amount']; }), 2) }}
                                            </span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">لا يوجد مستخدمين جمعوا فواتير اليوم</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Stat Cards Base Styles */
    .stat-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-card .card-body {
        padding: 1.5rem;
        position: relative;
    }

    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .stat-amount {
        font-size: 1rem;
        font-weight: 600;
    }

    .stat-subtitle {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .stat-details {
        margin-top: 0.5rem;
    }

    .stat-details .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.6rem;
        border-radius: 0.5rem;
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .stat-icon i {
        font-size: 2.5rem;
        color: rgba(255, 255, 255, 0.9);
    }

    /* Primary Color - Blue */
    .stat-card-primary {
        background-color: #3B82F6 !important;
    }

    /* Success Color - Green */
    .stat-card-success {
        background-color: #10B981 !important;
    }

    .stat-card-success-secondary {
        background-color: #059669 !important;
    }

    /* Warning Color - Orange/Yellow */
    .stat-card-warning {
        background-color: #F59E0B !important;
    }

    /* Danger Color - Red */
    .stat-card-danger {
        background-color: #EF4444 !important;
    }

    /* Info Color - Cyan */
    .stat-card-info {
        background-color: #06B6D4 !important;
    }

    /* Dark Color - Gray */
    .stat-card-dark {
        background-color: #374151 !important;
    }

    /* Primary Secondary - Purple */
    .stat-card-primary-secondary {
        background-color: #8B5CF6 !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-value {
            font-size: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
        }

        .stat-icon i {
            font-size: 2rem;
        }
    }

    /* Today's Activities Tables */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f7fafc;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    .badge-sm {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
    }

    .table th.sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }
</style>

<script>
    // Monthly Revenues vs Expenses Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = @json($dashboardData['monthly_data']);
    const monthlyLabels = Object.keys(monthlyData);
    const revenuesData = monthlyLabels.map(month => monthlyData[month].revenues);
    const expensesData = monthlyLabels.map(month => monthlyData[month].expenses);

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'الإيرادات',
                data: revenuesData,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'المصروفات',
                data: expensesData,
                borderColor: 'rgb(255, 193, 7)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Invoice Status Distribution Chart
    const invoiceStatusCtx = document.getElementById('invoiceStatusChart').getContext('2d');
    const invoiceStatusData = @json($dashboardData['invoice_status_distribution']);
    
    new Chart(invoiceStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['مدفوعة', 'غير مدفوعة', 'مدفوعة جزئياً'],
            datasets: [{
                data: [
                    invoiceStatusData.paid,
                    invoiceStatusData.unpaid,
                    invoiceStatusData.partial
                ],
                backgroundColor: [
                    'rgb(40, 167, 69)',
                    'rgb(220, 53, 69)',
                    'rgb(255, 193, 7)'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Client Type Distribution Chart
    const clientTypeCtx = document.getElementById('clientTypeChart').getContext('2d');
    const clientTypeData = @json($dashboardData['client_type_distribution']);
    
    new Chart(clientTypeCtx, {
        type: 'pie',
        data: {
            labels: ['إنترنت', 'ساتلايت'],
            datasets: [{
                data: [
                    clientTypeData.internet,
                    clientTypeData.satellite
                ],
                backgroundColor: [
                    'rgb(40, 167, 69)',
                    'rgb(0, 123, 255)'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Monthly Invoices Chart
    const invoicesCtx = document.getElementById('invoicesChart').getContext('2d');
    const invoicesData = monthlyLabels.map(month => monthlyData[month].invoices);

    new Chart(invoicesCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'قيمة الفواتير',
                data: invoicesData,
                backgroundColor: 'rgba(0, 123, 255, 0.6)',
                borderColor: 'rgb(0, 123, 255)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
    @endsection
@endcan
