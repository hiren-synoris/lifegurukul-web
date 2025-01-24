<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="csrf-token" content="{{ csrf_token() }}" />





<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!-- Meta tags for this particular pages available on frontend side. -->
@stack('meta')
<!-- Meta end -->

<title>{{ config('app.name') }} @isset($title)
        | {{ 'title' }}
    @endisset
</title>

<!-- HTML Meta Tags -->
<meta name="description" content="{{ config()->has('settings.description') ? config('settings.description') : null }}">
<meta name="keywords" content="{{ config()->has('settings.keywords') ? config('settings.keywords') : null }}">
@if (env('APP_ENV') == 'development')
    <meta name="robots" content="noindex">
@endif
<link rel="canonical" href="{{ url()->current() }}" />

<!-- Facebook Meta Tags -->
<meta property="og:url" content="{{ env('APP_URL') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ config()->has('settings.ogtitle') ? config('settings.ogtitle') : null }}">
<meta property="og:description"
    content="{{ config()->has('settings.ogdescription') ? config('settings.ogdescription') : null }}">
<meta property="og:image"
    content="{{ config()->has('settings.ogimage') ? asset(Storage::url(config('settings.ogimage'))) : null }}">

<!-- Twitter Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta property="twitter:domain" content="{{ env('APP_URL') }}">
<meta property="twitter:url" content="{{ env('APP_URL') }}">
<meta name="twitter:title"
    content="{{ config()->has('settings.twittertitle') ? config('settings.twittertitle') : null }} ">
<meta name="twitter:description"
    content="{{ config()->has('settings.ogdescription') ? config('settings.ogdescription') : null }}">
<meta name="twitter:image"
    content="{{ config()->has('settings.ogimage') ? asset(Storage::url(config('settings.ogimage'))) : null }}">

<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('front/img/favicon.png') }}">

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">

<!-- Fontawesome CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">




<!-- Feather CSS -->
<link rel="stylesheet" href="{{ asset('front/css/feather.css') }}">

<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/select2/css/select2.min.css') }}">

<!-- Datatable CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css') }}">

<!-- Owl Carousel CSS -->
<link rel="stylesheet" href="{{ asset('front/css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('front/css/owl.theme.default.min.css') }}">

<!-- Slick CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/slick/slick.css') }}">
<link rel="stylesheet" href="{{ asset('front/plugins/slick/slick-theme.css') }}">

<!-- Feathericon CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/feather/feather.css') }}">

<!-- Dropzone -->
<link rel="stylesheet" href="{{ asset('front/plugins/dropzone/dropzone.min.css') }}">

<!-- Aos CSS -->
<link rel="stylesheet" href="{{ asset('front/plugins/aos/aos.css') }}">

<!-- Main CSS -->
<link rel="stylesheet" href="{{ asset('front/css/style.css') }}">

<!-- Custom CSS -->
<link rel="stylesheet" href="{{ asset('front/css/custom.css') }}?var={{ time() }}">
@if (!empty(config('settings.favicon')))
    <link rel="icon" type="image/x-icon" href="{{ Storage::url('public/' . config('settings.favicon')) }}">
@endif

<!-- gdpr cookie -->
<link rel="stylesheet" href="{{ asset('front/css/gdpr_cookie/orejime.css') }}" />
<!-- <link rel="stylesheet" href="{{ asset('front/plugins/summernote/summernote-bs4.min.css') }}"> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
<!-- Meta Pixel Code -->
{{-- <script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '182890234843110');
fbq('track', 'PageView');
</script> --}}
<noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=182890234843110&ev=PageView&noscript=1" /></noscript>
<!-- End Meta Pixel Code -->

<!-- Google Tag Manager -->
<script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-MNBBKJPW');
</script>


<script type="text/javascript">
    (function(c, l, a, r, i, t, y) {
        c[a] = c[a] || function() {
            (c[a].q = c[a].q || []).push(arguments)
        };
        t = l.createElement(r);
        t.async = 1;
        t.src = "https://www.clarity.ms/tag/" + i;
        y = l.getElementsByTagName(r)[0];
        y.parentNode.insertBefore(t, y);
    })(window, document, "clarity", "script", "muxe1vllz5");
</script>



<script>
    ! function(f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function() {
            n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
    }(window, document, 'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '478648434106722');
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=478648434106722&ev=PageView&noscript=1" /></noscript>
<!-- End Google Tag Manager -->
