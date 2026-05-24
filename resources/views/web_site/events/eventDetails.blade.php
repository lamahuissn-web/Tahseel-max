<?php

use Illuminate\Support\Facades\Storage;

?>
@extends('web_site.layouts.master')
@section('content')

<style>
    iframe {
        width: 100%;
        height: 300px;
    }
</style>
<!-- ============================ Page Title Start================================== -->

<section id="page-banner" class="pt-120 pb-120 bg_cover" data-overlay="8"
         style="background-image: url({{asset('assets_web')}}/images/page-baner/log.jpg)">
    <div class="container">
        <div class="row direction">
            <div class="col-lg-12">
                <div class="page-banner-cont">
                    <h2><?= trans('web_site.Event_details') ?>  </h2>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================ Page Title End ================================== -->

<!-- ============================ Agency List Start ================================== -->

<section class="pt-60 pb-60 bck direction" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
    <div class="container c-relative">
        <div class="events-area">
            <div class="row">
                <div class="col-lg-8">
                    <div class="events-left">
                        <h3><?= $one_data->eventTitle ?></h3>
                        <span><i class="fa fa-calendar"></i><?= $one_data->eventDate ?> </span>
                        <span><i
                                class="fa fa-clock-o"></i> {{$one_data->eventFromHour}} - {{$one_data->eventToHour}}</span>
                        <span><i class="fa fa-map-marker"></i> {{$one_data->eventLocation}}</span>
                        <?php
                        $img_url = $one_data->eventImage;

                        ?>
{{--                        <img src="<?= $img_url ?>" alt="Event">--}}

                        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" dir="rtl">
                            <ol class="carousel-indicators">
                                <?php if (isset($one_data->eventImgaes) && (!empty($one_data->eventImgaes))) {
                                foreach ($one_data->eventImgaes as $key => $img) { ?>
                                <li data-target="#carouselExampleIndicators" data-slide-to="<?= $key ?>"
                                    class="<?php
                                    if ($key == 0) {
                                        echo 'active';
                                    } ?>"></li>
                                <?php }
                                } ?>
                            </ol>
                            <div class="carousel-inner">
                                <?php if (isset($one_data->eventImgaes) && (!empty($one_data->eventImgaes))) {
                                foreach ($one_data->eventImgaes as $key=> $img) {
                                    $img_url =  $img->image;
                                ?>
                                <div class="carousel-item <?php
                                if ($key == 0) {
                                    echo 'active';
                                } ?>">
                                    <img class="d-block w-100" src="<?= $img_url ?>" alt="First slide">
                                </div>
                                <?php }
                                } ?>

                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button"
                               data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only"><?= trans('web_site.prev') ?></span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button"
                               data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only"><?= trans('web_site.next') ?></span>
                            </a>
                        </div>


                        <p>{!! $one_data->eventDetails !!}</p>
                    </div> <!-- events left -->
                </div>
                <div class="col-lg-4">
                    <div class="events-right">
                        <?php /* ?>
                        <div class="events-coundwon bg_cover" data-overlay="8"
                             style="background-image: url({{asset('assets_web')}}/images/page-baner/about.jpg)">
                            <div data-countdown="2024/02/12"></div>

                        </div>

                        <?php */ ?>
                        <!-- events coundwon -->
                        <div class="events-address mt-30">
                            <ul>
                                <li>
                                    <div class="singel-address">
                                        <div class="icon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <div class="cont">
                                            <h6><?= trans('web_site.Start_time') ?></h6>
                                            <span><?= $one_data->eventFromHour ?></span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="singel-address">
                                        <div class="icon">
                                            <i class="fa fa-bell-slash"></i>
                                        </div>
                                        <div class="cont">
                                            <h6><?= trans('web_site.End_time') ?></h6>
                                            <span><?= $one_data->eventToHour ?></span>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="singel-address">
                                        <div class="icon">
                                            <i class="fa fa-map"></i>
                                        </div>
                                        <div class="cont">
                                            <h6><?= trans('web_site.Location') ?></h6>
                                            <span><?= $one_data->eventLocation ?></span>
                                        </div>
                                    </div> <!-- singel address -->
                                </li>

                            </ul>
                            <?php if (isset($one_data->eventLocationMap) && (!empty($one_data->eventLocationMap))) { ?>

                                <div class="mt-25 h-100 w-100">
                                    <?= $one_data->eventLocationMap ?>
                                </div> <!-- Map -->
                            <?php } ?>

                        </div> <!-- events address -->
                    </div> <!-- events right -->
                </div>
            </div> <!-- row -->
        </div> <!-- events-area -->
    </div> <!-- container -->
</section>

<!-- ============================ Agency List End ================================== -->

@endsection
