<?php
use Illuminate\Support\Facades\Storage;
?>
@extends('web_site.layouts.master')
@section('content')
<!-- breadcrumb-section start -->
<nav class="breadcrumb-section  bckhed pt-110 pb-110 hero-areain direction">
    <div class="container zindex">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center">
                    <h2 class="title pb-2 text-capitalize">
                        {{$pageTitle}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- breadcrumb-section end -->


<!-- table -->
<div class="product-tab bg-white pt-80 pb-50 direction bck-section">
    <div class="container c-relative" data-aos="fade-down" data-aos-easing="linear" data-aos-duration="1000">
        <div class="row justify-content">
            <div class="col-lg-10 col-12 mb-30">
                <div class="tab-content" id="myaccountContent">
                    <!-- الرئيسية -->
                    <div class="card mb-4">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="user-profile-header-banner">
                                    <img src="{{asset('assets_web/images')}}/profile.png" alt="Banner image"
                                         class="rounded-top">
                                </div>
                                <div
                                    class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                                    <div class="flex-shrink-0 mb-sm-5 mx-sm-0 mx-auto">
                                        <img src="{{$one_data->image_url}}" alt="user image"
                                             class="ms-0 ms-sm-4  user-profile-img">
                                    </div>
                                    <div class="flex-grow-1 mt-3 mt-sm-5">
                                        <div class="d-flex align-items-md-end align-items-sm-start   mx-4  gap-4">
                                            <div class="user-profile-info text-right">
                                                <h4 class="user-fnt ffnnt"><i
                                                        class="fas fa-university marleft1"></i>{{$one_data->name}}</h4>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-4 mb-4">
                                <div class="user-profile-info text-right">
                                    <h4 class="user-fnt clr-fnt"><i
                                            class="fas fa-map marleft"></i> {{trans('web_site.address')}} </h4>
                                    <ul class="list-inline mb-0 align-items-center mtop ">
                                        <li class=" fw-semibold">
                                            <i class="fas fa-map-marker-alt marleft"></i>{{$one_data->city->name}}
                                            -{{$one_data->district->name}}-{{$one_data->address}}
                                        </li>
                                        <li class=" fw-semibold">
                                            <i class="fas fa-phone marleft"></i> {{$one_data->phone}}
                                        </li>
                                        <li class=" fw-semibold">
                                            <i class="fas fa-user marleft"></i>{{$one_data->manger_name}}
                                        </li>
                                        <li class="fw-semibold">
                                            <i class="fas fa-briefcase marleft"></i>{{$one_data->manger_job}}
                                        </li>
                                        <li class="fw-semibold">
                                            <i class="fas fa-mobile-alt marleft"></i> {{$one_data->manger_phone}}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- <div class="col-md-4 mb-4">
                                 <div class="user-profile-info text-right">
                                     <h4 class="user-fnt clr-fnt"><i class="fas fa-map marleft"></i> العنوان الثانى</h4>
                                     <ul class="list-inline mb-0 align-items-center mtop ">
                                         <li class=" fw-semibold">
                                             <i class="fas fa-map-marker-alt marleft"></i> القرية - المدينة - المحافظة
                                         </li>
                                         <li class=" fw-semibold">
                                             <i class="fas fa-phone marleft"></i> 01000000000
                                         </li>
                                         <li class=" fw-semibold">
                                             <i class="fas fa-user marleft"></i> اسم المسئول
                                         </li>
                                         <li class="fw-semibold">
                                             <i class="fas fa-briefcase marleft"></i> رئيس مجلس الادارة
                                         </li>
                                         <li class="fw-semibold">
                                             <i class="fas fa-mobile-alt marleft"></i> 01000000000
                                         </li>
                                     </ul>
                                 </div>
                             </div>

                             <div class="col-md-4 mb-4">
                                 <div class="user-profile-info text-right">
                                     <h4 class="user-fnt clr-fnt"><i class="fas fa-map marleft"></i> العنوان الثالث</h4>
                                     <ul class="list-inline mb-0 align-items-center mtop ">
                                         <li class=" fw-semibold">
                                             <i class="fas fa-map-marker-alt marleft"></i> القرية - المدينة - المحافظة
                                         </li>
                                         <li class=" fw-semibold">
                                             <i class="fas fa-phone marleft"></i> 01000000000
                                         </li>
                                         <li class=" fw-semibold">
                                             <i class="fas fa-user marleft"></i> اسم المسئول
                                         </li>
                                         <li class="fw-semibold">
                                             <i class="fas fa-briefcase marleft"></i> رئيس مجلس الادارة
                                         </li>
                                         <li class="fw-semibold">
                                             <i class="fas fa-mobile-alt marleft"></i> 01000000000
                                         </li>
                                     </ul>
                                 </div>
                             </div>--}}
                            <div class="col-md-12 mb-4">
                                <div class="quest">
                                    <h3 class="titlemodll1">• {{trans('Surveys/TrainingCenters.question1')}}</h3>
                                    <p class="pargraphmodal"> {{$one_data->question1}} </p>
                                </div>
                                <div class="quest">
                                    <h3 class="titlemodll1">• {{trans('Surveys/TrainingCenters.question2')}}</h3>
                                    <p class="pargraphmodal"> {{$one_data->question2}} </p>
                                </div>
                                <div class="quest">
                                    <h3 class="titlemodll1">• {{trans('Surveys/TrainingCenters.question3')}}</h3>
                                    <p class="pargraphmodal"> {{$one_data->question3}} </p>
                                </div>
                                <div class="quest">
                                    <h3 class="titlemodll1">• {{trans('Surveys/TrainingCenters.question4')}}</h3>
                                    <p class="pargraphmodal"> {{$one_data->question4}} </p>
                                </div>
                                <div class="quest">
                                    <h3 class="titlemodll1">• {{trans('Surveys/TrainingCenters.question5')}}</h3>
                                    <p class="pargraphmodal"> {{$one_data->question5}} </p>
                                </div>


                            </div>
                            <div class="col-md-12 mb-4">
                                <div class="google-mapq">
                                    <iframe width="100%" height="350" id="gmap_canvas"
                                            src="https://maps.google.com/maps?q=nasrcity&t=&z=13&ie=UTF8&iwloc=&output=embed"
                                            frameborder="0" scrolling="no" marginheight="0" marginwidth="0">
                                    </iframe>
                                </div>
                            </div>


                        </div>
                    </div>


                </div>
            </div>
        </div>


    </div>

</div>

@endsection
