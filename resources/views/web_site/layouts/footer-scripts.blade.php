
      <!-- :: jQuery JS -->
	  <script src="{{asset('assets_web/js/jquery-3.6.0.min.js')}}"></script>
	  <!-- :: Bootstrap JS Bundle With Popper JS -->
	  <script src="{{asset('assets_web/js/bootstrap.bundle.min.js')}}"></script>
	  <!-- :: Owl Carousel JS -->
	  <script src="{{asset('assets_web/js/owl.carousel.min.js')}}"></script>
	   <script src="{{asset('assets_web/js/jquery.nice-select.min.js')}}"></script>
	  <!-- :: Waypoints -->
	  <script src="{{asset('assets_web/js/jquery.waypoints.min.js')}}"></script>
	  <!-- :: CounterUp -->
	  <script src="{{asset('assets_web/js/jquery.counterup.min.js')}}"></script>
	  <!-- :: Magnific Popup -->
	  <script src="{{asset('assets_web/js/jquery.magnific-popup.min.js')}}"></script>
	  <!-- :: MixitUp -->
	  <script src="{{asset('assets_web/js/mixitup.min.js')}}"></script>
	   <!-- animation -->
   <script  src="{{asset('assets_web/js/aos.js')}}" ></script>
  <script>
AOS.init();
</script>
	  <!-- :: Main JS -->
	  <script src="{{asset('assets_web/js/main.js')}}">  </script>
<script>
    $(document).ready(function (){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })



</script>

@yield('js')
