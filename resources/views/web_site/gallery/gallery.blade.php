<?php
use Illuminate\Support\Facades\Storage;
?>
@extends('web_site.layouts.master')
@section('content')
<!--====== PAGE BANNER PART START ======-->

<section id="page-banner" class="pt-120 pb-120 bg_cover" data-overlay="8" style="background-image: url({{asset('assets_web')}}/images/page-baner/log.jpg)">
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


<section class="pt-70 pb-70 bck direction" data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
    <div class="container c-relative">
        <?php if (isset($photos) && (!empty($photos))) { ?>

            <div class="row">
                @foreach ($photos as $photo)


                @if (!empty($photo->main_image))
                    @php
                        $image_path = Storage::disk('images')->url($photo->main_image);

                        $img_url = asset((Storage::disk('images')->exists($photo->main_image)) ? $image_path :'assets/images/blank.png');
                    @endphp
                @else
                    @php $img_url = asset('assets/images/blank.png'); @endphp

                @endif
                    <div class="col-lg-4">
                        <div class="singel-blog mt-30">
                            <div class="blog-thum">
                                <img src="<?= $img_url ?>" alt="Blog">
                            </div>
                            <div class="blog-cont">
                                <a href="{{route('photosDetails',$photo->id)}}"><h3><?= $photo->title ?></h3></a>
                            </div>
                        </div>
                    </div>
                @endforeach

                    <div class="row">
                        <div class="col-lg-12">
                            {!! $photos->withQueryString()->links() !!}
                        </div>
                    </div>
            </div> <!-- row -->
        <?php } ?>
    </div> <!-- container -->
</section>
@endsection



