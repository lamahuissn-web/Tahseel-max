@extends('web_site.layouts.master')
@section('content')
    @php
        $mainData=getMainData();
    @endphp

      <!-- :: Header -->
        <section class="header" id="page">
            <div class="header-carousel owl-carousel owl-theme">
                <div class="sec-hero display-table" style="background-image: url({{asset('assets_web/img/slider/img1.jpg')}})">
                    <div class="table-cell">
                        <div class="overlay"></div>
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="banner">
                                        <div class="top-handline">Royal Contact Real Estate</div>
                                        <h1 class="handline">Turning Real Estate
Dreams into Reality</h1>


                                        <div class="btn-box">
                                            <a class="btn-1 move-section" href="projects.html"><span>view projects</span></a>
                                            <a class="btn-1 btn-2 ml-30" href="contact.html"><span>Contact Us</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sec-hero display-table" style="background-image: url({{asset('assets_web/img/slider/img2.webp')}})">
                    <div class="table-cell">
                        <div class="overlay"></div>
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-9">
                                     <div class="banner">
                                        <div class="top-handline">Royal Contact Real Estate</div>
                                        <h1 class="handline">Turning Real Estate
Dreams into Reality</h1>


                                        <div class="btn-box">
                                            <a class="btn-1 move-section" href="projects.html"><span>view projects</span></a>
                                            <a class="btn-1 btn-2 ml-30" href="contact.html"><span>Contact Us</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </section>

        		<!-- :: About Us -->
        <section class="about-us home-3 bck1">
             <div class="container c-relative">
                <div class="row align-items-center column-reverse">

                     <div class="col-lg-6 col-md-6"  data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
                        <div class="about-us-text-box">
                            <div class="sec-title home-3">
                                <h2>About Us</h2>
                                <h3>Royal Contact Real Estate</h3>
                               <p class="sec-explain">This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted</p>

                                <p class="sec-explain">This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted This text is experimental and can be deleted</p>
                            </div>

							<div class="about-btn">
                                <a href="about.html" class="btn-1 btn-3"><span>More About Us</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
                        <div class="about-us-img-box">
                            <div class="img-box" style="background-image: url({{asset('assets_web/img/about.webp')}})"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

         <!-- :: New developments -->
        <div class="our-services bck">
			<div class="container c-relative"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
                  <div class="sec-title">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2>projects</h2>
                            <h3>New developments</h3>
                        </div>

                    </div>
                </div>

                 <div class="row">
                <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img1.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>Damac Lagoon Views</h3></a>
                        <span class="span-pro">Damac Lagoons</span>
                         </div>
                       </div>

                       <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img2.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>Damac Riverside</h3></a>
                        <span class="span-pro">Dubai Investment Park</span>
                         </div>
                       </div>

                     <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img3.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>the legends</h3></a>
                        <span class="span-pro">Dubai, UAE</span>
                         </div>
                       </div>

                      <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img4.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>Damac Lagoon Views</h3></a>
                        <span class="span-pro">Damac Lagoons</span>
                         </div>
                       </div>

                       <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img5.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>Damac Riverside</h3></a>
                        <span class="span-pro">Dubai Investment Park</span>
                         </div>
                       </div>

                     <div class="col-md-4 mb-30">
                    <div class="serv-box">
                    <div class="back-serv" style="background-image: url({{asset('assets_web/img/development/img6.webp')}})">
                          </div>
                         </div>
                    <div class="div-pro">
                        <a href="projects-details.html" class="pro-title"> <h3>the legends</h3></a>
                        <span class="span-pro">Dubai, UAE</span>
                         </div>
                       </div>

                     <div class="col-md-12 text-center ">
                                <a href="projects.html" class="btn-1 btn-3"><span>view all projects</span> </a>
                            </div>
                </div>
 			</div>
		</div>

           	<!-- :: why choose us -->
        <section class="services home-2 home-3 py-100-70 back1">
			<div class="container"  data-aos="fade-up" data-aos-easing="linear" data-aos-duration="900">
                <div class="sec-title">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2>why choose us</h2>
                            <h3>Where happiness lives</h3>
                        </div>

                    </div>
                </div>

				<div class="row">
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fas fa-building"></i>
							<div class="content-box">
								<h4>High quality products</h4>
								<p>The luxurious and exquisite design harmonious with the surrounding architecture provide the best living.</p>
 							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fas fa-store-alt"></i>
							<div class="content-box">
								<h4>Comprehensive amenities</h4>
								<p>The landscape infrastructures of streets are arranged in harmony with the common amenities for residents.</p>
 							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fas fa-headset"></i>
							<div class="content-box">
								<h4>Professional services</h4>
								<p>The customer service center is ready to serve 24/7, support the residents to provide information.</p>
 							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fas fa-lock"></i>
							<div class="content-box">
								<h4>Absolute security</h4>
								<p>Advanced security system with modern equipments, professional 24/7 security staff.</p>
 							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fab fa-envira"></i>
							<div class="content-box">
								<h4>Green and clean Environment</h4>
								<p>Each urban area of Rehomes is built on the basis of "A place that living is in harmony"</p>
 							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-4">
						<div class="item-box">
							<span></span>
							<i class="fas fa-users"></i>
							<div class="content-box">
								<h4>Humanitarian community</h4>
								<p>Family members, as well as building the sense of affection for the neighbors.</p>
 							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

               <!-- counter- -->
    <section class="counter-style-three"  style="background-image: url({{asset('assets_web/img/Cover.png')}});">
        <div class="overlayc overlay-counter"></div>
          <div class="container">
              <div class="sec-title">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2>awards & recognition</h2>
                            <h3>Merits we have earned</h3>
                        </div>

                    </div>
                </div>
             <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 counter-block">
                    <div class="inner-box" data-aos="zoom-in" data-aos-easing="linear" data-aos-duration="300">
                        <div class="layer-bg" style="background-image: url({{asset('assets_web/img/pattern-25.png')}});"></div>
                        <div class="count-outer count-box">
                             <span class="count-text counter statistic-counter">150</span>
                        </div>
                        <div class="text">Completed Projects</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 counter-block">
                    <div class="inner-box" data-aos="zoom-in" data-aos-easing="linear" data-aos-duration="600">
                        <div class="layer-bg" style="background-image: url({{asset('assets_web/img/pattern-26.png')}});"></div>
                        <div class="count-outer count-box">
                             <span class="count-text counter statistic-counter">80</span>
                        </div>
                        <div class="text">  Projects underconstruction</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 counter-block">
                    <div class="inner-box" data-aos="zoom-in" data-aos-easing="linear" data-aos-duration="900">
                        <div class="layer-bg" style="background-image: url({{asset('assets_web/img/pattern-27.png')}});"></div>
                        <div class="count-outer count-box">
                             <span class="count-text counter statistic-counter">25000</span>
                        </div>
                        <div class="text">number of stuff</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 counter-block">
                    <div class="inner-box" data-aos="zoom-in" data-aos-easing="linear" data-aos-duration="1200">
                        <div class="layer-bg" style="background-image: url({{asset('assets_web/img/pattern-28.png')}});"></div>
                        <div class="count-outer count-box">
                             <span class="count-text counter statistic-counter">11</span>
                        </div>
                        <div class="text">  Decades of experience</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

          <!-- :: News -->
        <section class="blog back1">
            <div class="container"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
                <div class="sec-title">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2>from our blog</h2>
                            <h3>News & Events</h3>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item">
                            <div class="img-box">
                                <a href="#" class="open-post">
                                    <div class="Blog-bck img-fluid" style="background-image: url({{asset('assets_web/img/development/img1.webp')}})"></div>
                                 </a>
                                <ul>
                                    <li><i class="fas fa-calendar-alt"></i> 16-10-2023</li>
                                  </ul>
                            </div>
                            <div class="text-box">
                                <a href="news-details.html" class="title-blog">
                                    <h5>Qingling Motors Investieren </h5>
                                </a>
                                 <p>details details details details details details details details details details details details details details details details details details </p>
                                <a href="news-details.html" class="link-more"><span>Read More</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item">
                            <div class="img-box">
                                <a href="news-details.html" class="open-post">
                                    <div class="Blog-bck img-fluid" style="background-image: url({{asset('assets_web/img/development/img2.webp')}})"></div>
                                </a>
                                <ul>
                                    <li><i class="fas fa-calendar-alt"></i> 16-10-2023</li>
                                  </ul>
                            </div>
                            <div class="text-box">
                                <a href="news-details.html" class="title-blog">
                                    <h5>Qingling Motors Investieren </h5>
                                </a>
                                 <p>details details details details details details details details details details details details details details details details details details </p>
                                 <a href="news-details.html" class="link-more"><span>Read More</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item">
                            <div class="img-box">
                                <a href="news-details.html" class="open-post">
                                    <div class="Blog-bck img-fluid" style="background-image: url({{asset('assets_web/img/development/img3.webp')}})"></div>
                                </a>
                                <ul>
                                    <li><i class="fas fa-calendar-alt"></i> 16-10-2023</li>
                                  </ul>
                            </div>
                            <div class="text-box">
                                <a href="news-details.html" class="title-blog">
                                    <h5>Qingling Motors Investieren</h5>
                                </a>
                                 <p>details details details details details details details details details details details details details details details details details details </p>
                                <a href="news-details.html" class="link-more"><span>Read More</span></a>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-12 text-center ">
                                <a href="news.html" class="btn-1 btn-3"><span>view all news</span> </a>
                            </div>
                </div>
            </div>
        </section>

    <!-- :: feedback -->
        <section class="testimonial py-100 bck1">
            <div class="container c-relative"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
                <div class="sec-title text-center">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>impression</h2>
                            <h3>Customer feedback</h3>
                        </div>

                    </div>
                </div>
                <div class="row">
                       <div class="testimonial-carousel owl-carousel owl-theme">
                            <div class="item-box inner-box">
                                <img src="{{asset('assets_web/img/testimonial/img1.jpg')}}" class="img-says">
                                <div class="text-box">A good company that provides good services, and the company is distinguished by the fact that it includes a distinguished group</div>
                                <div class="item-name text-center">
                                    <i class="ar-icons-right-quote"></i>
                                    <h5>mohamed hassan </h5>
                                    <span>engineer</span>
                                </div>
                            </div>
                            <div class="item-box inner-box">
                                <img src="{{asset('assets_web/img/testimonial/img2.png')}}" class="img-says">
                                <div class="text-box">A good company that provides good services, and the company is distinguished by the fact that it includes a distinguished group</div>
                                <div class="item-name text-center">
                                    <i class="ar-icons-right-quote"></i>
                                    <h5>ahmed ali</h5>
                                    <span>accountant</span>
                                </div>
                            </div>
                            <div class="item-box inner-box">
                                <img src="{{asset('assets_web/img/testimonial/img4.jpg')}}" class="img-says">
                                <div class="text-box">A good company that provides good services, and the company is distinguished by the fact that it includes a distinguished group</div>
                                <div class="item-name text-center">
                                    <i class="ar-icons-right-quote"></i>
                                    <h5>anwar emad </h5>
                                    <span>sales manager</span>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </section>

        <!-- :: clients -->
        <div class="sponsors" style="background-image: url({{asset('assets_web/img/bg-7.png')}})">
			<div class="container"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
                  <div class="sec-title">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h2>trust us</h2>
                            <h3>our clients</h3>
                        </div>
                     </div>
                </div>
				<div class="sponsors-carousel owl-carousel owl-theme">
					<div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img1.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img2.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img3.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img4.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img5.webp')}})" alt="clients"></div>
                         </a>
					</div>
                     <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img6.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    <div class="sponsors-box-item client-box">
						<a href="#">
                         <div class="img-fluid client-bck" style="background-image: url({{asset('assets_web/img/client/img3.webp')}})" alt="clients"></div>
                         </a>
					</div>
                    </div>
			</div>
		</div>

         <section class="lastsec"  data-aos="fade-down" data-aos-easing="linear" data-aos-duration="900">
             <div class="container">
                 <!-- :: Get Update -->
                <div class="get-update">
                    <div class="row">
                        <div class="col-lg-4">
                            <h5>Get Your <span>Free</span>   Real Estate business</h5>
                        </div>
                        <div class="col-lg-4 d-flex justify-content-center align-items-center">
                            <div class="phone">
                                <a href="tel:97159351156" class="pulse">
                                    <i class="ar-icons-phone"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex align-items-center justify-content-between">
                            <div>
                                <a class="phone-mail" href="tel:97159351156">97159351156</a>
                                <a class="phone-mail" href="mailto:info@mangmark.com">info@mangmark.com</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



@endsection
