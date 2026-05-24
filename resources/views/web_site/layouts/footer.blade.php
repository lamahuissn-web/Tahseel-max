@php
    $mainData=getMainData();
@endphp

   <!-- :: Footer -->
   <footer class="footer" style="background-image: url({{asset('assets_web/img/footer.webp')}})">
    <div class="overlay overlay-3"></div>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-3">
                <div class="logo">
                    <img class="img-fluid" src="{{asset('assets_web/img/logo-white1.png')}}" alt="Footer Logo">
                    <p>Royal Contact Real Estate has a wide experience in the field of real estate</p>
                 </div>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-3">
                <div class="footer-title">
                    <h4>Quick Links</h4>
                </div>
                <ul class="links">
                    <li><a href="{{route('home')}}"><i class="fas fa-long-arrow-alt-right"></i> Home </a></li>
                    <li><a href="{{route('about_us')}}"><i class="fas fa-long-arrow-alt-right"></i> About Us </a></li>
                    <li><a href="projects.html"><i class="fas fa-long-arrow-alt-right"></i> Our projects </a></li>
                     <li><a href="{{route('blogs')}}"><i class="fas fa-long-arrow-alt-right"></i> Our News </a></li>
                     <li><a href="{{route('contact_us')}}"><i class="fas fa-long-arrow-alt-right"></i> Contact Us </a></li>
                </ul>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-3">
                <div class="footer-title">
                    <h4>contact us</h4>
                </div>
                 <ul class="links">
                    <li> <a><i class="fas fa-map-marked-alt icon"></i> uae , dubai , nad al-sheba meydan-fz   </a></li>
                      <li><a href="tel:97159351156"> <i class="fas fa-phone-volume icon"></i>   97159351156  </a></li>
                      <li><a href="mailto:info@mangmark.com"><i class="fas fa-envelope icon"></i> info@mangmark.com  </a></li>
                 </ul>
            </div>


            <div class="col-sm-12 col-md-6 col-lg-3">
                <div class="footer-title">
                    <h4>newsletter </h4>
                </div>
                <div class="newsletter">
                    <p>Get latest news & update</p>
                    <form>
                        <input type="email" name="email" placeholder="Your Email Address" required>
                        <button type="submit"><i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
                <ul class="icon">
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
<path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="copyright">
        <div class="container text-center">
            <p>@ 2024 all right reserved to Royal Contact Real Estate</p>

        </div>
    </div>
</footer>
