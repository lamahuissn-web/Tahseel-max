<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <!--begin::Page title-->
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <!--begin::Title-->
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Careers Apply</h1>
                <!--end::Title-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="../../demo1/dist/index.html"
                           class="text-muted text-hover-primary">Home</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">Careers</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Filter menu-->
                <div class="d-flex">
                    <select name="campaign-type" data-control="select2" data-hide-search="true"
                            class="form-select form-select-sm bg-body border-body w-175px">
                        <option value="Twitter" selected="selected">Select Campaign</option>
                        <option value="Twitter">Twitter Campaign</option>
                        <option value="Twitter">Facebook Campaign</option>
                        <option value="Twitter">Adword Campaign</option>
                        <option value="Twitter">Carbon Campaign</option>
                    </select>
                    <a href="#" class="btn btn-icon btn-sm btn-success flex-shrink-0 ms-4"
                       data-bs-toggle="modal" data-bs-target="#kt_modal_create_campaign">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                        <span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<rect opacity="0.5" x="11.364" y="20.364" width="16" height="2"
                                                              rx="1" transform="rotate(-90 11.364 20.364)"
                                                              fill="currentColor"/>
														<rect x="4.36396" y="11.364" width="16" height="2" rx="1"
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
    </div>
    <!--end::Toolbar-->
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Careers main-->
            <div class="d-flex flex-column flex-xl-row">
                <!--begin::Content-->
                <div class="card bg-body me-xl-9 mb-9 mb-xl-0">
                    <div class="card-body">
                        <!--begin::Blog-->
                        <div class="mb-11">
                            <!--begin::Title-->
                            <div class="mb-9">
                                <h3 class="fs-2qx fw-bold text-dark">Join Our Team</h3>
                                <span class="fs-5 fw-semibold text-gray-400">Save thousands to millions of bucks by using tool great skills</span>
                            </div>
                            <!--end::Title-->
                            <!--begin::Wrapper-->
                            <div class="overlay mb-11">
                                <!--begin::Image-->
                                <div
                                    class="bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-325px"
                                    style="background-image:url('assets/media/stock/1600x800/img-4.jpg')"></div>
                                <!--end::Image-->
                                <!--begin::Links-->
                                <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                                    <a href="../../demo1/dist/pages/about.html" class="btn btn-primary">About
                                        Us</a>
                                    <a href="../../demo1/dist/pages/careers/apply.html"
                                       class="btn btn-light-primary ms-3">Join Us</a>
                                </div>
                                <!--end::Links-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Blog-->
                        <!--begin::Form-->
                        <form action="" class="form mb-15" method="post" id="kt_careers_form">
                            <!--begin::Description-->
                            <div class="mb-7">
                                <!--begin::Title-->
                                <h4 class="fs-1 text-gray-800 w-bolder mb-6">Apply Now</h4>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <p class="fw-semibold fs-4 text-gray-600 mb-2">First, a disclaimer – the
                                    entire process of writing a blog post often takes more than a couple
                                    of hours, even if you can type eighty words as per minute.</p>
                                <!--end::Text-->
                            </div>
                            <!--end::Description-->
                            <!--begin::Input group-->
                            <div class="row mb-5">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">First Name</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="first_name"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--end::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">Last Name</label>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="last_name"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-5">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">Email</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input class="form-control form-control-solid" placeholder=""
                                           name="email"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--end::Label-->
                                    <label class="fs-5 fw-semibold mb-2">Mobile No</label>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="mobileno"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-5">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">Age</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="age"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--end::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">City</label>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="city"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="d-flex flex-column mb-5 fv-row">
                                <!--begin::Label-->
                                <label class="d-flex align-items-center fs-5 fw-semibold mb-2">
                                    <span class="required">Position</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7"
                                       data-bs-toggle="tooltip"
                                       title="Your payment statements may very based on selected position"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="position" data-control="select2"
                                        data-placeholder="Select a position..."
                                        class="form-select form-select-solid">
                                    <option value="Web Developer">Web Developer</option>
                                    <option value="Web Designer">Web Designer</option>
                                    <option value="Art Director">Art Director</option>
                                    <option value="Finance Manager">Finance Manager</option>
                                    <option value="Project Manager">Project Manager</option>
                                    <option value="System Administrator">System Administrator</option>
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-5">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">Expected
                                        Salary</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="salary"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--end::Label-->
                                    <label class="required fs-5 fw-semibold mb-2">Srart Date</label>
                                    <!--end::Label-->
                                    <!--end::Input-->
                                    <input type="text" class="form-control form-control-solid"
                                           placeholder="" name="start_date"/>
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="d-flex flex-column mb-5 fv-row">
                                <!--begin::Label-->
                                <label class="fs-5 fw-semibold mb-2">Website (If Any)</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input class="form-control form-control-solid" placeholder=""
                                       name="website"/>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="d-flex flex-column mb-5">
                                <label class="fs-6 fw-semibold mb-2">Experience (Optional)</label>
                                <textarea class="form-control form-control-solid" rows="2"
                                          name="experience" placeholder=""></textarea>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="d-flex flex-column mb-8">
                                <label class="fs-6 fw-semibold mb-2">Application</label>
                                <textarea class="form-control form-control-solid" rows="4"
                                          name="application" placeholder=""></textarea>
                            </div>
                            <!--end::Input group-->
                            <!--begin::Separator-->
                            <div class="separator mb-8"></div>
                            <!--end::Separator-->
                            <!--begin::Submit-->
                            <button type="submit" class="btn btn-primary" id="kt_careers_submit_button">
                                <!--begin::Indicator label-->
                                <span class="indicator-label">Apply Now</span>
                                <!--end::Indicator label-->
                                <!--begin::Indicator progress-->
                                <span class="indicator-progress">Please wait...
														<span
                                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                <!--end::Indicator progress-->
                            </button>
                            <!--end::Submit-->
                        </form>
                        <!--end::Form-->
                        <!--begin::Job-->
                        <div class="mb-10 mb-lg-0">
                            <!--begin::Description-->
                            <div class="m-0">
                                <!--begin::Title-->
                                <h4 class="fs-1 text-gray-800 w-bolder mb-6">Job Details</h4>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <p class="fw-semibold fs-4 text-gray-600 mb-2">First, a disclaimer – the
                                    entire process of writing a blog post often takes more than a couple
                                    of hours, even if you can type eighty words as per minute and your
                                    writing skills are sharp.</p>
                                <!--end::Text-->
                            </div>
                            <!--end::Description-->
                            <!--begin::Accordion-->
                            <!--begin::Section-->
                            <div class="m-0">
                                <!--begin::Heading-->
                                <div class="d-flex align-items-center collapsible py-3 toggle mb-0"
                                     data-bs-toggle="collapse" data-bs-target="#kt_job_3_1">
                                    <!--begin::Icon-->
                                    <div
                                        class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen036.svg-->
                                        <span class="svg-icon toggle-on svg-icon-primary svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="6.0104" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen035.svg-->
                                        <span class="svg-icon toggle-off svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="10.8891" y="17.8033" width="12"
                                                                              height="2" rx="1"
                                                                              transform="rotate(-90 10.8891 17.8033)"
                                                                              fill="currentColor"/>
																		<rect x="6.01041" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                        Requirements</h4>
                                    <!--end::Title-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Body-->
                                <div id="kt_job_3_1" class="collapse show fs-6 ms-1">
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                HTML / CSS
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                REST API
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Git knowledge is
                                                a plus
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Content-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed"></div>
                                <!--end::Separator-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Section-->
                            <div class="m-0">
                                <!--begin::Heading-->
                                <div
                                    class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                    data-bs-toggle="collapse" data-bs-target="#kt_job_3_2">
                                    <!--begin::Icon-->
                                    <div
                                        class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen036.svg-->
                                        <span class="svg-icon toggle-on svg-icon-primary svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="6.0104" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen035.svg-->
                                        <span class="svg-icon toggle-off svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="10.8891" y="17.8033" width="12"
                                                                              height="2" rx="1"
                                                                              transform="rotate(-90 10.8891 17.8033)"
                                                                              fill="currentColor"/>
																		<rect x="6.01041" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">What is your
                                        job role?</h4>
                                    <!--end::Title-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Body-->
                                <div id="kt_job_3_2" class="collapse fs-6 ms-1">
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                HTML / CSS
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                REST API
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Git knowledge is
                                                a plus
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Content-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed"></div>
                                <!--end::Separator-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Section-->
                            <div class="m-0">
                                <!--begin::Heading-->
                                <div
                                    class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                    data-bs-toggle="collapse" data-bs-target="#kt_job_3_3">
                                    <!--begin::Icon-->
                                    <div
                                        class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen036.svg-->
                                        <span class="svg-icon toggle-on svg-icon-primary svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="6.0104" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen035.svg-->
                                        <span class="svg-icon toggle-off svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="10.8891" y="17.8033" width="12"
                                                                              height="2" rx="1"
                                                                              transform="rotate(-90 10.8891 17.8033)"
                                                                              fill="currentColor"/>
																		<rect x="6.01041" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">Job Candidate
                                        Benefits</h4>
                                    <!--end::Title-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Body-->
                                <div id="kt_job_3_3" class="collapse fs-6 ms-1">
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                HTML / CSS
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                REST API
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Git knowledge is
                                                a plus
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Content-->
                                <!--begin::Separator-->
                                <div class="separator separator-dashed"></div>
                                <!--end::Separator-->
                            </div>
                            <!--end::Section-->
                            <!--begin::Section-->
                            <div class="m-0">
                                <!--begin::Heading-->
                                <div
                                    class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                    data-bs-toggle="collapse" data-bs-target="#kt_job_3_4">
                                    <!--begin::Icon-->
                                    <div
                                        class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen036.svg-->
                                        <span class="svg-icon toggle-on svg-icon-primary svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="6.0104" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen035.svg-->
                                        <span class="svg-icon toggle-off svg-icon-1">
																	<svg width="24" height="24" viewBox="0 0 24 24"
                                                                         fill="none" xmlns="http://www.w3.org/2000/svg">
																		<rect opacity="0.3" x="2" y="2" width="20"
                                                                              height="20" rx="5" fill="currentColor"/>
																		<rect x="10.8891" y="17.8033" width="12"
                                                                              height="2" rx="1"
                                                                              transform="rotate(-90 10.8891 17.8033)"
                                                                              fill="currentColor"/>
																		<rect x="6.01041" y="10.9247" width="12"
                                                                              height="2" rx="1" fill="currentColor"/>
																	</svg>
																</span>
                                        <!--end::Svg Icon-->
                                    </div>
                                    <!--end::Icon-->
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">Application
                                        Terms</h4>
                                    <!--end::Title-->
                                </div>
                                <!--end::Heading-->
                                <!--begin::Body-->
                                <div id="kt_job_3_4" class="collapse fs-6 ms-1">
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                HTML / CSS
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10 mb-n1">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                REST API
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <div class="mb-4">
                                        <!--begin::Item-->
                                        <div class="d-flex align-items-center ps-10">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Git knowledge is
                                                a plus
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Section-->
                            <!--end::Accordion-->
                        </div>
                        <!--end::Job-->
                    </div>
                </div>
                <!--end::Content-->
                <!--begin::Sidebar-->
                <div class="flex-column flex-lg-row-auto w-100 w-xl-350px">
                    <!--begin::Careers about-->
                    <div class="card card-flush bg-body mb-9">
                        <!--begin::Body-->
                        <div class="card-body">
                            <!--begin::Top-->
                            <div class="mb-7">
                                <!--begin::Title-->
                                <h2 class="fs-1 text-gray-800 w-bolder mb-6">About Us</h2>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <p class="fw-semibold fs-6 text-gray-600">First, a disclaimer – the
                                    entire process of writing a blog post often takes more than a couple
                                    of hours, even if you can type eighty words as per minute and your
                                    writing skills are sharp.</p>
                                <!--end::Text-->
                            </div>
                            <!--end::Top-->
                            <!--begin::Items-->
                            <div class="mb-0">
                                <!--begin::Item-->
                                <div class="mb-8">
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 w-bolder mb-0">Requirements</h4>
                                    <!--end::Title-->
                                    <!--begin::Section-->
                                    <div class="my-2">
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center mb-3">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center mb-3">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center mb-3">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                HTML / CSS
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <div class="mb-8">
                                    <!--begin::Title-->
                                    <h4 class="text-gray-700 w-bolder mb-0">Our Achievements</h4>
                                    <!--end::Title-->
                                    <!--begin::Section-->
                                    <div class="my-2">
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center mb-3">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                JavaScript
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center mb-3">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Good
                                                time-management skills
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                        <!--begin::Row-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Bullet-->
                                            <span class="bullet me-3"></span>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-600 fw-semibold fs-6">Experience with
                                                React
                                            </div>
                                            <!--end::Label-->
                                        </div>
                                        <!--end::Row-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Item-->
                            </div>
                            <!--end::Items-->
                            <!--begin::Link-->
                            <a href="../../demo1/dist/pages/about.html"
                               class="link-primary fs-6 fw-semibold">Explore More</a>
                            <!--end::Link-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Careers about-->
                    <!--begin::Recent posts-->
                    <div class="card card-flush bg-body mb-9">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <h4 class="card-title fw-bold text-gray-800 fs-2">Recent from Blog</h4>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2">
                            <!--begin::Item-->
                            <div class="d-flex align-items-center mb-7">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-60px symbol-2by3 me-4">
                                    <div class="symbol-label"
                                         style="background-image: url('assets/media/stock/600x400/img-79.jpg')"></div>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <div class="m-0">
                                    <a href="#" class="text-dark fw-bold text-hover-primary fs-6">About
                                        Bootstrap Admin</a>
                                    <span class="text-gray-600 fw-semibold d-block pt-1 fs-8">We’ve been a focused on making a the sky</span>
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <div class="d-flex align-items-center mb-7">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-60px symbol-2by3 me-4">
                                    <div class="symbol-label"
                                         style="background-image: url('assets/media/stock/600x400/img-19.jpg')"></div>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <div class="m-0">
                                    <a href="#" class="text-dark fw-bold text-hover-primary fs-6">A
                                        yellow sofa</a>
                                    <span class="text-gray-600 fw-semibold d-block pt-1 fs-8">We’ve been a focused on making a the sky</span>
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <div class="d-flex align-items-center mb-7">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-60px symbol-2by3 me-4">
                                    <div class="symbol-label"
                                         style="background-image: url('assets/media/stock/600x400/img-72.jpg')"></div>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <div class="m-0">
                                    <a href="#" class="text-dark fw-bold text-hover-primary fs-6">Our
                                        Camra Mega Set</a>
                                    <span class="text-gray-600 fw-semibold d-block pt-1 fs-8">We’ve been a focused on making a the sky</span>
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <div class="d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-60px symbol-2by3 me-4">
                                    <div class="symbol-label"
                                         style="background-image: url('assets/media/stock/600x400/img-50.jpg')"></div>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Title-->
                                <div class="m-0">
                                    <a href="#" class="text-dark fw-bold text-hover-primary fs-6">Time
                                        to cook and eat?</a>
                                    <span class="text-gray-600 fw-semibold d-block pt-1 fs-8">We’ve been a focused on making a the sky</span>
                                </div>
                                <!--end::Title-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Link-->
                            <a href="../../demo1/dist/pages/blog/home.html"
                               class="text-primary opacity-75-hover pt-8 fs-6 fw-semibold d-block">Explore
                                More</a>
                            <!--end::Link-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Recent posts-->
                    <!--begin::Connected-->
                    <div class="card card-flush bg-body">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <h4 class="card-title fw-bold text-text-gray-800 fs-2">Stay Connected</h4>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2">
                            <!--begin::Items-->
                            <div class="mb-6">
                                <!--begin::Link-->
                                <a href="#" class="mb-6">
                                    <img src="assets/media/svg/brand-logos/facebook-4.svg"
                                         class="h-20px me-2" alt=""/>
                                    <span
                                        class="text-gray-700 text-hover-primary fs-5 mb-6">Facebook</span>
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--begin::Items-->
                            <!--begin::Items-->
                            <div class="mb-6">
                                <!--begin::Link-->
                                <a href="#" class="mb-6">
                                    <img src="assets/media/svg/brand-logos/github.svg"
                                         class="h-20px me-2" alt=""/>
                                    <span
                                        class="text-gray-700 text-hover-primary fs-5 mb-6">Github</span>
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--begin::Items-->
                            <!--begin::Items-->
                            <div class="mb-6">
                                <!--begin::Link-->
                                <a href="#" class="mb-6">
                                    <img src="assets/media/svg/brand-logos/twitter.svg"
                                         class="h-20px me-2" alt=""/>
                                    <span
                                        class="text-gray-700 text-hover-primary fs-5 mb-6">Twitter</span>
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--begin::Items-->
                            <!--begin::Items-->
                            <div class="mb-6">
                                <!--begin::Link-->
                                <a href="#" class="mb-6">
                                    <img src="assets/media/svg/brand-logos/dribbble-icon-1.svg"
                                         class="h-20px me-2" alt=""/>
                                    <span
                                        class="text-gray-700 text-hover-primary fs-5 mb-6">Dribbble</span>
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--begin::Items-->
                            <!--begin::Items-->
                            <div class="">
                                <!--begin::Link-->
                                <a href="#" class="mb-6">
                                    <img src="assets/media/svg/brand-logos/instagram-2016.svg"
                                         class="h-20px me-2" alt=""/>
                                    <span
                                        class="text-gray-700 text-hover-primary fs-5 mb-6">Instagram</span>
                                </a>
                                <!--end::Link-->
                            </div>
                            <!--begin::Items-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Connected-->
                </div>
                <!--end::Sidebar-->
            </div>
            <!--end::Careers main-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</div>
