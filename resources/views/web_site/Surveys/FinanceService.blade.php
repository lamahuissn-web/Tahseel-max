<?php
use Illuminate\Support\Facades\Storage;
?>
@extends('web_site.layouts.master')
@section('content')
    <!-- ============================ Page Title Start================================== -->
    <nav class="breadcrumb-section  backimg pt-110 pb-110 hero-areain direction"
         style="background-image: url({{asset('assets_web')}}/images/page-baner/news.jpg)">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title text-center">
                        <h2 class="title pb-2 text-white text-capitalize">
                         {{trans('web_site.FinanceService')}}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- ============================ Page Title End ================================== -->

    <!-- table -->
    <div class="product-tab bg-white pt-80 pb-50 direction bck-section">
        <div class="container c-relative" data-aos="fade-down" data-aos-easing="linear" data-aos-duration="1000">
            <div class="row">
                <!-- filter -->
                <div class="col-lg-3 mb-30">
                    <aside class="left-sidebar">
                        <!-- search-filter start -->
                        <div class="search-filter">
                            <div class="sidbar-widget pt-0">
                                <h4 class="title">التصنيفات</h4>
                            </div>
                        </div>

                        <ul id="offcanvas-menu2" class="blog-ctry-menu">
                          {{--  <li>
                                <a href="javascript:void(0)" class="drobbranch"><i class="fas fa-arrow-left  ml5"></i>
                                    التصنيف الرئيسى <span class="spanno">11</span></a>
                                <ul class="category-sub-menu">
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                    <li><a href="#">التصنيف الفرعى</a></li>
                                </ul>
                            </li>--}}
                            <li><a href="{{route('TrainingCenters')}}" class="mainbranch"><i class="fas fa-arrow-left  ml5"></i>{{trans('web_site.TrainingCenters')}} </a>
                            </li>
                            <li><a href="{{route('FinanceService')}}" class="mainbranch"><i class="fas fa-arrow-left  ml5"></i>{{trans('web_site.FinanceService')}} </a>
                            </li>

                        </ul>


                    </aside>
                </div>
                @if (isset($all) && (!empty($all)))

                    <div class="col-lg-9 mb-30">
                        <div class="row grid-view-list theme1">
                            @foreach ($all as $item)

                                       @php $img_url = $item->image_url; @endphp

                            <div class="col-md-12 mb-30">
                                <div class="card  carden">
                                    <div class="card-body">
                                        <div
                                            class="media flex-column flex-md-row align-items-center  justify-content-between">
                                            <div class="product-thumbnail position-relative">
                                                <a href="{{route('FinanceServiceDetails',$item->id)}}">
                                                    <img class="first-img" src="{{$img_url}}" alt=""/>
                                                </a>
                                            </div>
                                            <div class="media-body ps-md-4">
                                                <div class="product-desc">
                                                    <h3 class="title">
                                                        <span class="titlecard">{{$item->name}}</span>
                                                    </h3>
                                                    <h3 class="title">
                                                        <i class="fas fa-map-marker-alt icard"></i> <span class="ncard">{{$item->city->name}}-{{$item->district->name}}</span>
                                                    </h3>

                                                    <p class="parfnt">
                                                        {{$item->description}}</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>


                                    <div class="card-footer  align-items-center justify-content-between dropdown">
                                        <div class="dropdown">
                                            <a href="#" class="ml-card "><i class="fas fa-phone iconcard"></i> <span>  </span></a>
                                            <div class="dropdown-content">
{{--                                                <p> رقم التليفون</p>--}}
                                                <a href="tel:{{$item->phone}}">{{$item->phone}} </a>
{{--                                                <a href="tel:010000000">010000000</a>--}}
{{--                                                <a href="tel:010000000">010000000</a>--}}
                                            </div>
                                        </div>

                                        <a href="{{route('FinanceServiceDetails',$item->id)}}" class="ml-card"><i class="fas fa-globe-africa iconcard"></i> <span></span>
                                        </a>

                                        <a href="{{route('FinanceServiceDetails',$item->id)}}" class="ml-card"><i
                                                class="fas fa-envelope iconcard"></i> <span>  </span></a>

                                        <a href="{{route('FinanceServiceDetails',$item->id)}}" class="ml-card"><i
                                                class="fas fa-map-marker-alt iconcard"></i><span>   </span></a>

                                        <a href="{{route('FinanceServiceDetails',$item->id)}}" class="ml-card"><i class="fab fa-whatsapp iconwhats"></i> <span>  </span></a>

                                    </div>

                                </div>
                            </div>
                            @endforeach


                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>


@endsection
