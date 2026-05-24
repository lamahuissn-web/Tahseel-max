     <!-- :: All Navbar -->
     <header class="all-navbar fixed-top">
        <!-- :: Navbar -->
        <nav class="nav-bar">
            <div class="container">
                <div class="content-box d-flex align-items-center justify-content-between">
                    <div class="logo">
                        <a href="index.html" class="logo-nav">
                            <img class="img-fluid one" src="{{asset('assets_web/img/logo-white1.png')}}" alt="01 Logo">
                            <img class="img-fluid two" src="{{asset('assets_web/img/logo-dark1.png')}}" alt="02 Logo">
                        </a>

                         <a href="#" class="lang dismob"> <span class="menu-icon open"><img src="{{asset('assets_web/img/ar.png')}}"> AR</span></a>
                        <a href="#" class="lang dismob"> <span class="menu-icon open"><img src="{{asset('assets_web/img/en.png')}}"> EN</span></a>

                        <a href="#open-nav-bar-menu" class="open-nav-bar">
                            <span></span>
                            <span></span>
                            <span></span>
                        </a>
                    </div>
                    <div class="nav-bar-links" id="open-nav-bar-menu">
                        <ul class="level-1">
                            <li class="item-level-1">
                                <a href="{{route('home')}}" class="link-level-1 <?= Route::currentRouteName() == 'home' || Route::currentRouteName() == '' ? "color-active" : '' ?> ">{{trans('web_site.Home')}} </a>
                             </li>
                            <li class="item-level-1">
                                <a href="{{route('about_us')}}" class="link-level-1 <?= Route::currentRouteName() == 'about_us' ? "color-active" : '' ?>">{{trans('web_site.About')}}  </a>
                            </li>

                            <li class="item-level-1">
                                <a href="" class="link-level-1 ">our projects</a>
                            </li>
                            <li class="item-level-1">
                                <a href="{{route('blogs')}}" class="link-level-1  <?= Route::currentRouteName() == 'blogs' || Route::currentRouteName() == 'one_blogs' ? "color-active" : '' ?>">{{trans('web_site.Blog')}} </a>
                            </li>

                              <li class="item-level-1">
                                <a href="{{route('contact_us')}}" class="link-level-1 <?= Route::currentRouteName() == 'contact_us' ? "color-active" : '' ?>"> {{trans('web_site.Contact_Us')}} </a>
                            </li>
                        </ul>
                    </div>
                      <ul class="nav-bar-tools d-flex align-items-center justify-content-between">

                        <li class="item phone">
                            <div class="nav-bar-contact">
                                <i class="ar-icons-phone"></i>
                                <div class="content-box">
                                    <span>Phone Number</span>
                                    <a href="tel:97159351156">97159351156</a>
                                </div>
                            </div>
                        </li>

                        <?php if (app()->getLocale() == 'en') { ?>
                        <li class="item">
                           <a href="{{ LaravelLocalization::getLocalizedURL('ar', null, [], true) }}" class="lang">
                             <span class="menu-icon open"><img src="{{asset('assets_web/img/ar.png')}}"> <?= trans('web_site.Arabic') ?></span></a>
                        </li>
                        <?php } elseif (app()->getLocale() == 'ar') { ?>
                          <li class="item">
                           <a href="{{ LaravelLocalization::getLocalizedURL('en', null, [], true) }}" class="lang">
                            <span class="menu-icon open"><img src="{{asset('assets_web/img/en.png')}}"> <?= trans('web_site.English') ?></span></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>

            </div>
        </nav>
    </header>

    <!-- :: Header -->




