<!DOCTYPE html>
<html lang="en">
<head>
  @yield('styles')
  @include('front.layout.partials.head')




</head>

<body class="search-page-home">

  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MNBBKJPW" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  @if(Route::is(['come-soon','error-404','error-500','under-construction']))

  <body class="error-page">
    @endif
    <!-- Main Wrapper -->
    @if(!Route::is(['login','register']))
    <div class="main-wrapper">
      @endif
      @if(Route::is(['login','register']))
      <div class="main-wrapper log-wrap">
        @endif
        {{-- @if(Route::is(['checkout.index']))
  <div class="main-wrapper log-wrap">
  @endif --}}
        @php
        // Here we get pages array for CMS pages where key = 'slug' and value = 'page-url'.
        $pagesKeyValueArray = find_cms_pages();

        @endphp


        @if(isset($_GET['app'])==false)
        @if(!Route::is(['come-soon','error-404','error-500','forgot-password','login','new-password','register-step-five','register-step-four',
        'register-step-one','register-step-three','register-step-two','register','under-construction','verification-code']))
        @include('front.layout.partials.header')
        @endif
        @endif

        @yield('content')
        @if(isset($_GET['app'])==false)
        @if(!Route::is(['come-soon','error-404','error-500','forgot-password','login','new-password','register-step-five','register-step-four',
        'register-step-one','register-step-three','register-step-two','register','under-construction','verification-code']))
        @include('front.layout.partials.footer')
        @endif
        @endif
      </div>
      <!-- /Main Wrapper -->
      <div id="loader_section">
        <div id="loader">
          <div id="spinner"></div>
        </div>
      </div>

      @include('front.layout.partials.footer-scripts')
      @yield('scripts')
  </body>

</html>
