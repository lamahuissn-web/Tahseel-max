<?php use Illuminate\Support\Facades\Route;
?>
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{route('admin.dashboard')}}">
            <img alt="Logo"
                 src="{{asset((!empty($mainData->image)) ? $mainData->image : 'assets/media/logos/default-dark.svg')}}"
                 class="h-50px app-sidebar-logo-default"/>
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-sm h-30px w-30px rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
            <span class="svg-icon svg-icon-2 rotate-180">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
										<path opacity="0.5"
                                              d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                                              fill="currentColor"/>
										<path
                                            d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                                            fill="currentColor"/>
									</svg>
								</span>
            <!--end::Svg Icon-->
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
             data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
             data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold px-3"
                 id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

            {{--site management--}}
            @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_user','list_roles'])))

                <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span
                                class="menu-heading fw-bold text-uppercase fs-7">{{trans('sidebar.site_setting')}}</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion     @if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('users', 'roles','permission'))) {{'show'}} @endif ">
                        <!--begin:Menu link-->
                        <span class="menu-link">
											<span class="menu-icon">
												<span class="svg-icon svg-icon-2">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<path
                                                            d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                                                            fill="currentColor"/>
														<path opacity="0.3"
                                                              d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                                                              fill="currentColor"/>
													</svg>
												</span>
                                                <!--end::Svg Icon-->
											</span>
											<span class="menu-title">{{trans('sidebar.Users_Settings')}}</span>
											<span class="menu-arrow"></span>
                     </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->


                        <div
                            class="menu-sub menu-sub-accordion <?php  if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('users', 'roles', 'permission'))) {
                                echo 'show';
                            } ?>">
                        @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_user'])))

                            <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'users') {
                                        echo 'active';
                                    } ?>" href="{{ route('admin.UserManagement.users.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                        <span class="menu-title">{{trans('sidebar.Users')}}</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                            @endif

                            @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->can('list_roles')))
                                <div class="menu-item">

                                    <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'roles') {
                                        echo 'active';
                                    } ?>" href="{{ route('admin.UserManagement.roles.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                        <span class="menu-title">{{trans('sidebar.roles')}}</span>
                                    </a>

                                </div>
                            @endif
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
            @endif

            <!--begin:Menu item-->
                <div data-kt-menu-trigger="click"
                     class="menu-item menu-accordion <?php  if (in_array(optional(explode('.', Route::currentRouteName()))[1], array('mdata', 'about', 'teacher'))) {
                         echo 'show';
                     } ?>">
                    <!--begin:Menu link-->
                    <span class="menu-link">
<!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen055.svg-->
<span class="menu-icon">
												<!--begin::Svg Icon | path: icons/duotune/art/art002.svg-->
												<span class="svg-icon svg-icon-2">
													<svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
														<path opacity="0.3"
                                                              d="M8.9 21L7.19999 22.6999C6.79999 23.0999 6.2 23.0999 5.8 22.6999L4.1 21H8.9ZM4 16.0999L2.3 17.8C1.9 18.2 1.9 18.7999 2.3 19.1999L4 20.9V16.0999ZM19.3 9.1999L15.8 5.6999C15.4 5.2999 14.8 5.2999 14.4 5.6999L9 11.0999V21L19.3 10.6999C19.7 10.2999 19.7 9.5999 19.3 9.1999Z"
                                                              fill="currentColor"></path>
														<path
                                                            d="M21 15V20C21 20.6 20.6 21 20 21H11.8L18.8 14H20C20.6 14 21 14.4 21 15ZM10 21V4C10 3.4 9.6 3 9 3H4C3.4 3 3 3.4 3 4V21C3 21.6 3.4 22 4 22H9C9.6 22 10 21.6 10 21ZM7.5 18.5C7.5 19.1 7.1 19.5 6.5 19.5C5.9 19.5 5.5 19.1 5.5 18.5C5.5 17.9 5.9 17.5 6.5 17.5C7.1 17.5 7.5 17.9 7.5 18.5Z"
                                                            fill="currentColor"></path>
													</svg>
												</span>
    <!--end::Svg Icon-->
											</span>                        <!--end::Svg Icon-->											<span
                            class="menu-title">{{trans('sidebar.site_setting')}}</span>
											<span class="menu-arrow"></span>
										</span>
                    <!--end:Menu link-->

                    <!--begin:Menu sub site data-->
                    <div
                        class="menu-sub menu-sub-accordion  <?php  if (in_array(optional(explode('.', Route::currentRouteName()))[1], array('mdata', 'about', 'contact', 'terms'))) {
                            echo 'show';
                        } ?>">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[1] == 'mdata') {
                                echo 'active';
                            } ?>" href="{{ route('admin.mdata.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                <span class="menu-title">{{trans('sidebar.mdata')}}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[1] == 'about') {
                                echo 'active';
                            } ?>" href="{{ route('admin.about.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                <span class="menu-title">{{trans('sidebar.about')}}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[1] == 'terms') {
                                echo 'active';
                            } ?>" href="{{ route('admin.terms.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                <span class="menu-title">{{trans('sidebar.terms')}}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>
                        <!--end:Menu item-->
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[1] == 'contact') {
                                echo 'active';
                            } ?>" href="{{ route('admin.contact.index') }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                <span class="menu-title">{{trans('sidebar.contact')}}</span>
                            </a>
                            <!--end:Menu link-->
                        </div>

                    </div>
                    <!--end:Menu sub-->
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion     @if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('mainsetting', 'typesetting'))) {{'show'}} @endif ">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                                           <span class="menu-icon">
                                               <span class="svg-icon svg-icon-2">
                                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                       <path
                                                           d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                                                           fill="currentColor"/>
                                                       <path opacity="0.3"
                                                             d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                                                             fill="currentColor"/>
                                                   </svg>
                                               </span>
                                               <!--end::Svg Icon-->
                                           </span>
                                           <span class="menu-title">{{trans('sidebar.hr_Settings')}}</span>
                                           <span class="menu-arrow"></span>
                    </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->


                        <div
                            class="menu-sub menu-sub-accordion <?php  if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('typesetting', 'mainsetting', 'holiday_type'))) {
                                echo 'show';
                            } ?>">
                        @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_typesetting'])))

                            <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'typesetting') {
                                        echo 'active';
                                    } ?>" href="{{ route('admin.hr.typesetting.index') }}">
                                                   <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span>
                                        <span class="menu-title">{{trans('sidebar.hr_typesetting')}}</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                            @endif
                            @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_mainsetting'])))

                                <div class="menu-item">

                                    <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'mainsetting') {
                                        echo 'active';
                                    } ?>" href="{{ route('admin.hr.mainsetting.index') }}">
                                                   <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span>
                                        <span class="menu-title">{{trans('sidebar.hr_mainsetting')}}</span>
                                    </a>

                                </div>
                            @endif
                            {{-- @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_holiday_type'])))

                                 <div class="menu-item">

                                     <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'holiday_type') {
                                         echo 'active';
                                     } ?>" href="{{ route('admin.hr.holiday_type.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                         <span class="menu-title">{{trans('sidebar.holiday_type')}}</span>
                                     </a>

                                 </div>
                             @endif--}}
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <div data-kt-menu-trigger="click"
                         class="menu-item menu-accordion {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('settings_data'))) ? 'show' : ''  }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
											<span class="menu-icon">
												<span class="svg-icon svg-icon-2">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path
                                                            d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                                                            fill="currentColor"></path>
														<path opacity="0.3"
                                                              d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                                                              fill="currentColor"></path>
													</svg>												</span>
                                                <!--end::Svg Icon-->
											</span>
											<span class="menu-title">{{trans('sidebar.sub_settings')}}</span>
											<span class="menu-arrow"></span>
										</span>
                        <!--end:Menu link-->

                        <!--begin:Menu sub site data-->
                        <div
                            class="menu-sub menu-sub-accordion {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('settings_data'))) ? 'show' : ''  }}">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data'])) @if(Route::current()->parameters['type']=='exercise_type') active  @endif @endif"
                                   href="{{ route('admin.subscriptions.settings_data',['exercise_type']) }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                    <span class="menu-title">{{trans('sidebar.exercise_type')}}</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data']))  @if(Route::current()->parameters['type']=='exercise_level') active  @endif @endif"
                                   href="{{ route('admin.subscriptions.settings_data',['exercise_level']) }}">

													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                    <span class="menu-title">{{trans('sidebar.exercise_level')}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data'])) @if(Route::current()->parameters['type']=='car_type') active  @endif @endif"
                                   href="{{ route('admin.subscriptions.settings_data',['car_type']) }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                    <span class="menu-title">{{trans('sidebar.car_type')}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->

  <!--begin:Menu item-->
  <div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data']))  @if(Route::current()->parameters['type']=='exercise_devices') active  @endif @endif"
       href="{{ route('admin.subscriptions.settings_data',['exercise_devices']) }}">

                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
        <span class="menu-title">{{trans('sidebar.exercise_devices')}}</span>
    </a>
    <!--end:Menu link-->
</div>
<!--end:Menu item-->
  <!--begin:Menu item-->
  <div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data']))  @if(Route::current()->parameters['type']=='devices') active  @endif @endif"
       href="{{ route('admin.subscriptions.settings_data',['devices']) }}">

                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
        <span class="menu-title">{{trans('sidebar.devices')}}</span>
    </a>
    <!--end:Menu link-->
</div>
<!--end:Menu item-->
  <!--begin:Menu item-->
  <div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data']))  @if(Route::current()->parameters['type']=='task_management') active  @endif @endif"
       href="{{ route('admin.subscriptions.settings_data',['task_management']) }}">

                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
        <span class="menu-title">{{trans('sidebar.task_management')}}</span>
    </a>
    <!--end:Menu link-->
</div>
<!--end:Menu item-->

                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data'])) @if(Route::current()->parameters['type']=='goals') active  @endif @endif"
                                   href="{{ route('admin.subscriptions.settings_data',['goals']) }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                    <span class="menu-title">{{trans('sidebar.goals')}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->

                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link @if( request()->routeIs(['admin.subscriptions.settings_data'])) @if(Route::current()->parameters['type']=='health_status') active  @endif @endif"
                                   href="{{ route('admin.subscriptions.settings_data',['health_status']) }}">
													<span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span>
                                    <span class="menu-title">{{trans('sidebar.health_status')}}</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->


                        </div>
                        <!--end:Menu sub-->

                    </div>


                </div>
                <!--end:Menu item-->
                {{-- end site management--}}


                {{--gym management--}}

                <div class="menu-item pt-5">
                    <!--begin:Menu content-->
                    <div class="menu-content">
                        <span
                            class="menu-heading fw-bold text-uppercase fs-7">{{trans('sidebar.subscriptions_management')}}</span>
                    </div>
                    <!--end:Menu content-->
                </div>
                <div data-kt-menu-trigger="click"
                     class="menu-item menu-accordion {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('transportation','special_subscriptions','main_subscriptions','offers'))) ? 'show' : ''  }}">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                                <span class="menu-icon">
                                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen055.svg-->
                                    <span class="svg-icon svg-icon-2">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd"
                                          d="M2 4.63158C2 3.1782 3.1782 2 4.63158 2H13.47C14.0155 2 14.278 2.66919 13.8778 3.04006L12.4556 4.35821C11.9009 4.87228 11.1726 5.15789 10.4163 5.15789H7.1579C6.05333 5.15789 5.15789 6.05333 5.15789 7.1579V16.8421C5.15789 17.9467 6.05333 18.8421 7.1579 18.8421H16.8421C17.9467 18.8421 18.8421 17.9467 18.8421 16.8421V13.7518C18.8421 12.927 19.1817 12.1387 19.7809 11.572L20.9878 10.4308C21.3703 10.0691 22 10.3403 22 10.8668V19.3684C22 20.8218 20.8218 22 19.3684 22H4.63158C3.1782 22 2 20.8218 2 19.3684V4.63158Z"
                                          fill="currentColor"/>
                                    <path
                                        d="M10.9256 11.1882C10.5351 10.7977 10.5351 10.1645 10.9256 9.77397L18.0669 2.6327C18.8479 1.85165 20.1143 1.85165 20.8953 2.6327L21.3665 3.10391C22.1476 3.88496 22.1476 5.15129 21.3665 5.93234L14.2252 13.0736C13.8347 13.4641 13.2016 13.4641 12.811 13.0736L10.9256 11.1882Z"
                                        fill="currentColor"/>
                                    <path
                                        d="M8.82343 12.0064L8.08852 14.3348C7.8655 15.0414 8.46151 15.7366 9.19388 15.6242L11.8974 15.2092C12.4642 15.1222 12.6916 14.4278 12.2861 14.0223L9.98595 11.7221C9.61452 11.3507 8.98154 11.5055 8.82343 12.0064Z"
                                        fill="currentColor"/>
                                    </svg>
                                    </span>
                                                <!--end::Svg Icon-->
                                                <!--end::Svg Icon-->
											</span>
											<span class="menu-title">{{trans('sidebar.subscriptionsDefined')}}</span>
											<span class="menu-arrow"></span>
										</span>
                    <!--end:Menu link-->

                    <!--begin:Menu sub site data-->
                    <div class="menu-sub menu-sub-accordion
                            {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('transportation','special_subscriptions','main_subscriptions','offers'))) ? 'show' : ''  }}">


                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[2] == 'main_subscriptions')  active   @endif"
                               href="{{ route('admin.subscriptions.main_subscriptions.index') }}">
                                         <span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span> <span
                                    class="menu-title">{{ trans('sidebar.main_subscriptions') }}</span>
                            </a>
                        </div>


                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[2] == 'special_subscriptions')  active   @endif"
                               href="{{ route('admin.subscriptions.special_subscriptions.index') }}">
                                 <span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span> <span
                                    class="menu-title">{{ trans('sidebar.special_subscriptions') }}</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[2] == 'offers')  active   @endif"
                               href="{{ route('admin.subscriptions.offers.index') }}">
                                <span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span> <span
                                    class="menu-title">{{ trans('sidebar.offers') }}</span>
                            </a>
                        </div>


                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[2] == 'transportation')  active   @endif"
                               href="{{ route('admin.subscriptions.transportation.index') }}">
                            <span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span> <span
                                    class="menu-title">{{ trans('sidebar.transportation') }}</span>
                            </a>
                        </div>

                    </div>
                    <!--end:Menu sub-->

                </div>
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[1] == 'Trainers')  active   @endif"
                       href="{{ route('admin.Trainers.index') }}">
                        <span class="menu-icon">
                                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/communication/com005.svg-->
                                    <span class="svg-icon svg-icon-2"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                           xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                                        fill="currentColor"/>
                                    <path opacity="0.3"
                                          d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                                          fill="currentColor"/>
                                    </svg>
                                    </span>
                            <!--end::Svg Icon-->                        </span>
                        <span class="menu-title">{{ trans('sidebar.Trainers') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[1] == 'Members')  active   @endif"
                       href="{{ route('admin.Members.index') }}">
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/communication/com014.svg-->
                            <span class="svg-icon svg-icon-2"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                   xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16.0173 9H15.3945C14.2833 9 13.263 9.61425 12.7431 10.5963L12.154 11.7091C12.0645 11.8781 12.1072 12.0868 12.2559 12.2071L12.6402 12.5183C13.2631 13.0225 13.7556 13.6691 14.0764 14.4035L14.2321 14.7601C14.2957 14.9058 14.4396 15 14.5987 15H18.6747C19.7297 15 20.4057 13.8774 19.912 12.945L18.6686 10.5963C18.1487 9.61425 17.1285 9 16.0173 9Z"
                                fill="currentColor"/>
                            <rect opacity="0.3" x="14" y="4" width="4" height="4" rx="2" fill="currentColor"/>
                            <path
                                d="M4.65486 14.8559C5.40389 13.1224 7.11161 12 9 12C10.8884 12 12.5961 13.1224 13.3451 14.8559L14.793 18.2067C15.3636 19.5271 14.3955 21 12.9571 21H5.04292C3.60453 21 2.63644 19.5271 3.20698 18.2067L4.65486 14.8559Z"
                                fill="currentColor"/>
                            <rect opacity="0.3" x="6" y="5" width="6" height="6" rx="3" fill="currentColor"/>
                            </svg>
                            </span>
                            <!--end::Svg Icon-->                        </span>
                        <span class="menu-title">{{ trans('sidebar.Members') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[2] == 'member-subscriptions')  active   @endif"
                       href="{{ route('admin.subscriptions.member-subscriptions.index') }}">
                        <span class="menu-icon">
                                <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen035.svg-->
                                <span class="svg-icon svg-icon-2"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                       xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="5" fill="currentColor"/>
                                <rect x="10.8891" y="17.8033" width="12" height="2" rx="1" transform="rotate(-90 10.8891 17.8033)" fill="currentColor"/>
                                <rect x="6.01041" y="10.9247" width="12" height="2" rx="1" fill="currentColor"/>
                                </svg>
                                </span>
                                                            <!--end::Svg Icon-->
                                </span>
                                                        <!--end::Svg Icon-->                        </span>
                        <span class="menu-title">{{ trans('sidebar.member_subscriptions') }}</span>
                    </a>
                </div>
                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[1] == 'Members-Inbody')  active   @endif"
                       href="{{ route('admin.Members-Inbody.index') }}">
                        <span class="menu-icon">
                                <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen035.svg-->
                                <span class="svg-icon svg-icon-2"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                       xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="5" fill="currentColor"/>
                                <rect x="10.8891" y="17.8033" width="12" height="2" rx="1" transform="rotate(-90 10.8891 17.8033)" fill="currentColor"/>
                                <rect x="6.01041" y="10.9247" width="12" height="2" rx="1" fill="currentColor"/>
                                </svg>
                                </span>
                                                            <!--end::Svg Icon-->
                                </span>
                                                        <!--end::Svg Icon-->                        </span>
                        <span class="menu-title">{{ trans('sidebar.Members-Inbody') }}</span>
                    </a>
                </div>

                <div data-kt-menu-trigger="click"
                     class="menu-item menu-accordion {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('Reports'))) ? 'show' : ''  }}">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                                <span class="menu-icon">
                                    <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen055.svg-->
                                    <span class="svg-icon svg-icon-2">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd"
                                          d="M2 4.63158C2 3.1782 3.1782 2 4.63158 2H13.47C14.0155 2 14.278 2.66919 13.8778 3.04006L12.4556 4.35821C11.9009 4.87228 11.1726 5.15789 10.4163 5.15789H7.1579C6.05333 5.15789 5.15789 6.05333 5.15789 7.1579V16.8421C5.15789 17.9467 6.05333 18.8421 7.1579 18.8421H16.8421C17.9467 18.8421 18.8421 17.9467 18.8421 16.8421V13.7518C18.8421 12.927 19.1817 12.1387 19.7809 11.572L20.9878 10.4308C21.3703 10.0691 22 10.3403 22 10.8668V19.3684C22 20.8218 20.8218 22 19.3684 22H4.63158C3.1782 22 2 20.8218 2 19.3684V4.63158Z"
                                          fill="currentColor"/>
                                    <path
                                        d="M10.9256 11.1882C10.5351 10.7977 10.5351 10.1645 10.9256 9.77397L18.0669 2.6327C18.8479 1.85165 20.1143 1.85165 20.8953 2.6327L21.3665 3.10391C22.1476 3.88496 22.1476 5.15129 21.3665 5.93234L14.2252 13.0736C13.8347 13.4641 13.2016 13.4641 12.811 13.0736L10.9256 11.1882Z"
                                        fill="currentColor"/>
                                    <path
                                        d="M8.82343 12.0064L8.08852 14.3348C7.8655 15.0414 8.46151 15.7366 9.19388 15.6242L11.8974 15.2092C12.4642 15.1222 12.6916 14.4278 12.2861 14.0223L9.98595 11.7221C9.61452 11.3507 8.98154 11.5055 8.82343 12.0064Z"
                                        fill="currentColor"/>
                                    </svg>
                                    </span>
                                    <!--end::Svg Icon-->
                                    <!--end::Svg Icon-->
											</span>
											<span class="menu-title">{{trans('sidebar.subscriptionsReports')}}</span>
											<span class="menu-arrow"></span>
										</span>
                    <!--end:Menu link-->

                    <!--begin:Menu sub site data-->
                    <div class="menu-sub menu-sub-accordion
                            {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('Reports'))) ? 'show' : ''  }}">


                        <div class="menu-item">
                            <!--begin:Menu link-->
                            <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[3] == 'MembersSubscriptions')  active   @endif"
                               href="{{ route('admin.subscriptions.Reports.MembersSubscriptions') }}">
                                         <span class="menu-bullet">
														<span class="bullet bullet-dot"></span>
													</span> <span
                                    class="menu-title">{{ trans('sidebar.ReportsMembersSubscriptions') }}</span>
                            </a>
                        </div>


                    </div>
                    <!--end:Menu sub-->

                </div>



                {{--gym management--}}

                {{--hr management--}}

                @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_employee','list_holiday_type'])))

                <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                           <span
                               class="menu-heading fw-bold text-uppercase fs-7">{{trans('sidebar.hr_management')}}</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->

                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link   @if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('employee'))) {{'active'}} @endif"
                           href="{{ route('admin.hr.employee.index') }}">
                                           <span class="menu-icon">
                                               <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
                                               <span class="svg-icon svg-icon-2">
                                                   <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                       <path opacity="0.3"
                                                             d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z"
                                                             fill="currentColor"></path>
                                                       <path
                                                           d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z"
                                                           fill="currentColor"></path>
                                                       <path
                                                           d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z"
                                                           fill="currentColor"></path>
                                                   </svg>
                                               </span>
                                               <!--end::Svg Icon-->
                                           </span>
                            <span class="menu-title">{{trans('sidebar.employee')}}</span>
                        </a>
                        <!--end:Menu link-->
                    </div>

                    <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('Hr'))) ? 'show' : ''  }}">
                   <!--begin:Menu link-->
                   <span class="menu-link">
                               <span class="menu-icon">
                                   <!--begin::Svg Icon | path: /var/www/preview.keenthemes.com/keenthemes/keen/docs/core/html/src/media/icons/duotune/general/gen055.svg-->
                                   <span class="svg-icon svg-icon-2">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                   <path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd"
                                         d="M2 4.63158C2 3.1782 3.1782 2 4.63158 2H13.47C14.0155 2 14.278 2.66919 13.8778 3.04006L12.4556 4.35821C11.9009 4.87228 11.1726 5.15789 10.4163 5.15789H7.1579C6.05333 5.15789 5.15789 6.05333 5.15789 7.1579V16.8421C5.15789 17.9467 6.05333 18.8421 7.1579 18.8421H16.8421C17.9467 18.8421 18.8421 17.9467 18.8421 16.8421V13.7518C18.8421 12.927 19.1817 12.1387 19.7809 11.572L20.9878 10.4308C21.3703 10.0691 22 10.3403 22 10.8668V19.3684C22 20.8218 20.8218 22 19.3684 22H4.63158C3.1782 22 2 20.8218 2 19.3684V4.63158Z"
                                         fill="currentColor"/>
                                   <path
                                       d="M10.9256 11.1882C10.5351 10.7977 10.5351 10.1645 10.9256 9.77397L18.0669 2.6327C18.8479 1.85165 20.1143 1.85165 20.8953 2.6327L21.3665 3.10391C22.1476 3.88496 22.1476 5.15129 21.3665 5.93234L14.2252 13.0736C13.8347 13.4641 13.2016 13.4641 12.811 13.0736L10.9256 11.1882Z"
                                       fill="currentColor"/>
                                   <path
                                       d="M8.82343 12.0064L8.08852 14.3348C7.8655 15.0414 8.46151 15.7366 9.19388 15.6242L11.8974 15.2092C12.4642 15.1222 12.6916 14.4278 12.2861 14.0223L9.98595 11.7221C9.61452 11.3507 8.98154 11.5055 8.82343 12.0064Z"
                                       fill="currentColor"/>
                                   </svg>
                                   </span>
                                   <!--end::Svg Icon-->
                                   <!--end::Svg Icon-->
                                           </span>
                                           <span class="menu-title">{{trans('sidebar.Employee_Operations')}}</span>
                                           <span class="menu-arrow"></span>
                                       </span>
                   <!--end:Menu link-->

                   <!--begin:Menu sub site data-->
            <div class="menu-sub menu-sub-accordion
                           {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('hr'))) ? 'show' : ''  }}">


                       <div class="menu-item">
                           <!--begin:Menu link-->
                           <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[3] == 'hr')  active   @endif"
                              href="{{ route('admin.hr.hr_permission.index') }}">
                                        <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span> <span
                                   class="menu-title">{{ trans('sidebar.hr_permission') }}</span>
                           </a>
                       </div>


                   </div>
                   <!--end:Menu sub-->
                   <!--begin:Menu sub site data-->
                   <div class="menu-sub menu-sub-accordion
                           {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('hr'))) ? 'show' : ''  }}">


                       <div class="menu-item">
                           <!--begin:Menu link-->
                           <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[3] == 'hr_loan')  active   @endif"
                              href="{{ route('admin.hr.hr_loan.index') }}">
                                        <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span> <span
                                   class="menu-title">{{ trans('sidebar.hr_loan') }}</span>
                           </a>
                       </div>


                   </div>
                   <!--end:Menu sub-->

                   <div class="menu-sub menu-sub-accordion
                           {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('hr'))) ? 'show' : ''  }}">


                       <div class="menu-item">
                           <!--begin:Menu link-->
                           <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[3] == 'hr_bonuses')  active   @endif"
                              href="{{ route('admin.hr.hr_bonuses.index') }}">
                                        <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span> <span
                                   class="menu-title">{{ trans('sidebar.hr_bonuses') }}</span>
                           </a>
                       </div>


                   </div>
                   <!--end:Menu sub-->
                   <div class="menu-sub menu-sub-accordion
                           {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('hr'))) ? 'show' : ''  }}">


                       <div class="menu-item">
                           <!--begin:Menu link-->
                           <a class="menu-link @if(optional(explode('.', Route::currentRouteName()))[3] == 'hr_deductions')  active   @endif"
                              href="{{ route('admin.hr.hr_deductions.index') }}">
                                        <span class="menu-bullet">
                                                       <span class="bullet bullet-dot"></span>
                                                   </span> <span
                                   class="menu-title">{{ trans('sidebar.hr_deductions') }}</span>
                           </a>
                       </div>


                   </div>
                   <!--end:Menu sub-->

                   <div class="menu-sub menu-sub-accordion
                   {{ (in_array(optional(explode('.', Route::currentRouteName()))[2], array('hr'))) ? 'show' : ''  }}">
                  
                   <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link   @if (in_array(optional(explode('.', Route::currentRouteName()))[7], array('hr_reports'))) {{'active'}} @endif"
                       href="{{ route('admin.hr.hr_reports.index') }}">
                       <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span> <span
                class="menu-title">{{ trans('sidebar.hr_reports') }}</span>        
                    </a>

                    <!--end:Menu link-->
                </div>
              
                
                
                </div>

               </div>


             
                    {{--
                                                <!--begin:Menu item-->
                                                <div data-kt-menu-trigger="click"
                                                     class="menu-item menu-accordion     @if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('reqholiday'))) {{'show'}} @endif ">
                                                    <!--begin:Menu link-->
                                                    <span class="menu-link">
                                                               <span class="menu-icon">
                                                                   <span class="svg-icon svg-icon-2">
                                                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                           <path
                                                                               d="M20 14H18V10H20C20.6 10 21 10.4 21 11V13C21 13.6 20.6 14 20 14ZM21 19V17C21 16.4 20.6 16 20 16H18V20H20C20.6 20 21 19.6 21 19ZM21 7V5C21 4.4 20.6 4 20 4H18V8H20C20.6 8 21 7.6 21 7Z"
                                                                               fill="currentColor"/>
                                                                           <path opacity="0.3"
                                                                                 d="M17 22H3C2.4 22 2 21.6 2 21V3C2 2.4 2.4 2 3 2H17C17.6 2 18 2.4 18 3V21C18 21.6 17.6 22 17 22ZM10 7C8.9 7 8 7.9 8 9C8 10.1 8.9 11 10 11C11.1 11 12 10.1 12 9C12 7.9 11.1 7 10 7ZM13.3 16C14 16 14.5 15.3 14.3 14.7C13.7 13.2 12 12 10.1 12C8.10001 12 6.49999 13.1 5.89999 14.7C5.59999 15.3 6.19999 16 7.39999 16H13.3Z"
                                                                                 fill="currentColor"/>
                                                                       </svg>
                                                                   </span>
                                                                   <!--end::Svg Icon-->
                                                               </span>
                                                               <span class="menu-title">{{trans('sidebar.hr_emp_operation')}}</span>
                                                               <span class="menu-arrow"></span>
                                        </span>
                                                    <!--end:Menu link-->
                                                    <!--begin:Menu sub-->


                                                    <div
                                                        class="menu-sub menu-sub-accordion <?php  if (in_array(optional(explode('.', Route::currentRouteName()))[2], array('reqholiday'))) {
                                                            echo 'show';
                                                        } ?>">
                                                    @if(auth()->user()->hasRole('Super-Admin')||(auth()->user()->canAny(['list_reqholiday'])))

                                                        <!--begin:Menu item-->
                                                            <div class="menu-item">
                                                                <!--begin:Menu link-->
                                                                <a class="menu-link <?php  if (optional(explode('.', Route::currentRouteName()))[2] == 'reqholiday') {
                                                                    echo 'active';
                                                                } ?>" href="{{ route('admin.hr.reqholiday.index') }}">
                                                                       <span class="menu-bullet">
                                                                           <span class="bullet bullet-dot"></span>
                                                                       </span>
                                                                    <span class="menu-title">{{trans('sidebar.reqholiday')}}</span>
                                                                </a>
                                                                <!--end:Menu link-->
                                                            </div>
                                                        @endif

                                                    </div>
                                                    <!--end:Menu sub-->
                                                </div>
                                                <!--end:Menu item-->--}}
                @endif


                {{--<div class="menu-item pt-5">
                    <!--begin:Menu content-->
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">{{trans('sidebar.Members')}}</span>
                    </div>
                    <!--end:Menu content-->
                </div>--}}

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->

</div>
