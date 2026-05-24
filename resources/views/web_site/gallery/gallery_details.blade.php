<?php
use Illuminate\Support\Facades\Storage;
?>
@extends('web_site.layouts.master')
@section('content')
    <!--====== PAGE BANNER PART START ======-->

    <section id="page-banner" class="pt-120 pb-120 bg_cover" data-overlay="8"
             style="background-image: url({{asset('assets_web')}}/images/page-baner/terms.jpg)">
        <div class="container">
            <div class="row direction">
                <div class="col-lg-12">
                    <div class="page-banner-cont">
                        <h2><?= trans('web_site.gallery') ?> </h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{--{{dd($one_data)}}--}}



    <section class="pt-70 pb-70 bck direction" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
        <div class="container c-relative">
            <div class="row justify">
                <div class="col-lg-10">
                    <div class="blog-details mt-30">
                        <div class="cont">

                            <h3><?= $one_data->photoTitle ?></h3>
                        </div> <!-- cont -->
                        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" dir="rtl">
                            <ol class="carousel-indicators">
                                <?php if (isset($one_data->photoImgaes) && (!empty($one_data->photoImgaes))) {
                                foreach ($one_data->photoImgaes as $key => $img) { ?>
                                <li data-target="#carouselExampleIndicators" data-slide-to="<?= $key ?>"
                                    class="<?php
                                    if ($key == 0) {
                                        echo 'active';
                                    } ?>"></li>
                                <?php }
                                } ?>
                            </ol>
                            <div class="carousel-inner">
                                @if (isset($one_data->photoImgaes) && (!empty($one_data->photoImgaes)))
                                    @foreach ($one_data->photoImgaes as $key=>$img)

                                        {{--    @if (!empty($img->image))
                                                @php
                                                    $image_path = Storage::disk('photoImgaes')->url($img->image);

                                                    $img_url = asset((Storage::disk('photoImgaes')->exists($img->image)) ? $image_path :'assets/photoImgaes/blank.png');
                                                @endphp
                                            @else
                                                @php $img_url = asset('assets/photoImgaes/blank.png'); @endphp

                                            @endif--}}
                                        @php
                                            $img_url =$img->image
                                        @endphp

                                        <div class="carousel-item <?php
                                        if ($key == 0) {
                                            echo 'active';
                                        } ?>">
                                            <img class="d-block w-100" src="<?= $img_url ?>" alt="First slide">
                                        </div>
                                @endforeach
                            @endif
                            <!--  <div class="carousel-item active">
      <img class="d-block w-100" src="photoImgaes/web/photos/img1.jpg" alt="First slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="photoImgaes/web/photos/img2.jpg" alt="Second slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="photoImgaes/web/photos/img3.jpg" alt="Third slide">
    </div>-->
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

                    </div> <!-- blog details -->
                </div>
            </div> <!-- row -->
        </div> <!-- container -->
    </section>

@endsection


