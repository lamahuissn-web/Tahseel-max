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
              <?= trans('web_site.Our_Blog') ?>
            </h2>
          </div>
        </div>
       </div>
    </div>
  </nav>
<!-- ============================ Page Title End ================================== -->
<!-- ============================ Agency List Start ================================== -->
<section class="blog-section pb-50 pt-80 bckabout direction">
    <div class="container c-relative">
      <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
          <div class="blog-posts">
            <div class="single-blog-post blog-grid-post">
                @if (isset($one_data->blogImgaes) && (!empty($one_data->blogImgaes)))
               <div class="blog-detail-init slick-nav" dir="rtl">
              @foreach ($one_data->blogImgaes as $key => $img)
           <?php   $img_url =  $img->image;   ?>
                <!------- item  --------->
             <div class="single1-blog">
                  <div class="img-blog" style="background-image: url({{$img_url}})"> </div>
                </div>
               @endforeach


                  </div>

                @endif

                <div class="blog-post-content-inner">
                <h4 class="blog-title">{{$one_data->blogTitle}}</h4>
                <ul class="blog-page-meta">
                   <li>
                     <i class="ion-calendar"></i>{{$one_data->blogDate}}
                  </li>
                </ul>
                <p> {!! $one_data->blogDetails !!}</p>
              </div>

            </div>
           </div>
         </div>
      </div>
    </div>
  </section>


<!-- ============================ Agency List End ================================== -->

@endsection
