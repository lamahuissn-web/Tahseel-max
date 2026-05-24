<?php
use Illuminate\Support\Facades\Storage;
?>
@extends('web_site.layouts.master')
@section('content')

    <!-- ============================ Page Title Start================================== -->
    <section id="page-banner" class="pt-120 pb-120 bg_cover" data-overlay="8"
             style="background-image: url({{asset('assets_web')}}/images/page-baner/log.jpg)">
        <div class="container">
            <div class="row direction">
                <div class="col-lg-12">
                    <div class="page-banner-cont">
                        <h2><?= trans('web_site.Our_events') ?>  </h2>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================ Page Title End ================================== -->

    <!-- ============================ Blog Start ================================== -->


    <section class="pt-70 pb-70 bck direction" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
        <div class="container c-relative">
            <?php if (isset($all) && (!empty($all))) { ?>
            <div class="row">
                <?php

                foreach ($all as $item) {
                if (!empty($item->main_image)) {
                    $image_path = Storage::disk('images')->url($item->main_image);

                    $img_url = asset((Storage::disk('images')->exists($item->main_image)) ? $image_path : 'assets/images/blank.png');
                } else {
                    $img_url = asset('assets/images/blank.png');

                }
                ?>
                <div class="col-lg-6">
                    <div class="singel-event-list mt-30">
                        <div class="event-thum">
                            <img src="<?= $img_url ?>" alt="Event">
                        </div>
                        <div class="event-cont">
                            <span><i
                                    class="fa fa-calendar"></i> <?= formatDateDayDisplay($item->date_at) ?></span>
                            <a href="{{route('one_events',$item->id)}}">
                                <h4><?= $item->title ?></h4></a>
                            <p class="fnt">{{\Illuminate\Support\Str::words(strip_tags($item->details), 30, '...')}} </p>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
            <!-- row -->
            <?php } ?>
            <div class="row">
                <div class="col-lg-12">
                    {!! $all->withQueryString()->links() !!}
{{--                                    {!! $all->withQueryString()->links('vendor/pagination/simple-default') !!}--}}
                </div>
            </div>

        </div> <!-- container -->
    </section>

    <!-- ============================ Agency List End ================================== -->



@endsection
