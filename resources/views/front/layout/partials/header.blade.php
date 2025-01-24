@php
use App\Models\Notification;
@endphp
<!-- Header -->
@if (!Route::is(['index']))
<header class="header header-page">
    @endif
    @if (Route::is(['index']))
    <header class="header">
        @endif
        <style>
            .noti-nav {
                position: relative;
            }

            .count-3 {

                background: #67a94a;
                border-radius: 50%;
                position: absolute;
                top: -10px;
                right: 0px;
                padding: 2px 7px;
                border-radius: 50%;
                color: white;
                font-size: 16px;
            }
        </style>
        <div class="header-fixed">
            <div class="header-top">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-4 col-sm-12">
                            @if (!empty(config('settings.contact_no')))
                            <p class="mb-0">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                                <a href="tel:{{ config('settings.contact_no') ?? '' }}"> {{ config('settings.contact_no') ?? '' }}</a>
                            </p>
                            @endif
                        </div>
                        <div class="col-md-8 col-sm-12">
                            @php
                            $whatsappmobile = !empty(config('settings.whatsapp_contact_no')) ? config('settings.whatsapp_contact_no') : "";
                            $whatsappquerymsg = !empty(config('settings.whatsappquerymsg')) ? config('settings.whatsappquerymsg') : "";
                            @endphp

                            <a aria-label="Chat on WhatsApp" target="_blank" id="whatsappWidgetContainer" href="https://wa.me/{{ substr($whatsappmobile, 4); }}?text={{ $whatsappquerymsg }}"><img src="{{asset("front/logo/whatsapp-logo-png-2280.png") }}" style="" width=50> <span>Contact us</span></img></a>

                            <ul class="top-link">
                                @if (auth()->guard('learner')->user())
                                <li class="d-flex">
                                    <a class="link" href="{{ url('my_course') }}">Access My Course</a>
                                </li>
                                @else
                                <li class="d-flex">
                                    <a class="link mobileLogin" style="cursor: pointer" data-toggle="modal" data-step="{{ session()->has('mobile') ? '2' : '1' }}">Access My Course</a>
                                </li>
                                @endguest

                                <li class="d-flex">
                                    <a class="link" href="{{ url('faq') }}">Help </a>
                                </li>
                                <li id="google_translate_element"></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <nav class="navbar navbar-expand-lg header-nav scroll-sticky">
                <div class="container">
                    <div class="flex-fill navbar-header">
                        <a id="mobile_btn" href="javascript:void(0);">
                            <span class="bar-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </a>

                        <a href="{{ route('home') }}" class="navbar-brand logo">
                            <img src="{{ !empty(config('settings.logo')) ? Storage::url(config('settings.logo')) : logo_default() }}" class="img-fluid" alt="{{ env('APP_NAME') }}">
                        </a>
                    </div>
                    <div class="main-menu-wrapper">
                        <div class="menu-header">
                            <a href="{{ route('home') }}" class="menu-logo">
                                <img src="{{ !empty(config('settings.logo')) ? Storage::url(config('settings.logo')) : logo_default() }}" class="img-fluid" alt="{{ env('APP_NAME') }}">
                            </a>
                            <a id="menu_close" class="menu-close" href="javascript:void(0);">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <ul class="main-nav">
                            <li class="{{ Request::is('/') ? 'active' : '' }}">
                                <a href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="{{ Request::is('course.list') || Request::is('course') || Request::is('course-details/*') ? 'active' : '' }}">
                                <a href="{{ url('course') }}">Course </a>
                            </li>
                            {{-- $pagesKeyValueArray comes from 'mainlayout.blade.php file.' --}}
                            @if (is_menu_enable('about-us', $pagesKeyValueArray))
                            <li class="has {{ Request::is('page/about-us') ? 'active' : '' }}">
                                <a href="{{ cms_menu_url('about-us', $pagesKeyValueArray) }}">About Us </a>
                            </li>
                            @endif
                            <li class="{{ Request::is('blogs/*') || Request::is('blogs') ? 'active' : '' }}">
                                <a href="{{ route('blogs') }}">Blog</a>
                            </li>
                            <li class="{{ Request::is('faq') ? 'active' : '' }}">
                                <a href="{{ url('faq') }}">FAQ </a>
                            </li>
                            <li class="{{ Request::is('contact-us') ? 'active' : '' }}">
                                <a href="{{ route('contact-us') ?? 'javascript:void(0)' }}">Contact Us</a>
                            </li>
                            {{-- <li class="nav-item">
                    <a class="link header-sign mobileLogin"  data-step="{{ session()->has('mobile') ? '2' : '1' }} style="cursor: pointer" data-bs-toggle="modal" data-bs-target="#myModal">Login</a>
                            </li> --}}

                            @if (!auth()->guard('learner')->user())
                            <li class="nav-item">
                                <a class="link header-sign mobileLogin" style="cursor: pointer" data-toggle="modal" data-step="{{ session()->has('mobile') ? '2' : '1' }}">Login</a>
                            </li>
                            @else
                            @php
                            $imagepath = Helper::profileImage();

                            // $imagepath = $image ? Storage::url($image) : '/front/img/user/user11.jpg';
                            @endphp
                            <li class="nav-item user-nav">
                                <a href="javascript:void(0)" class="dropdown-toggle" data-bs-toggle="dropdown">
                                    <span class="user-img">
                                        <img src="{{ $imagepath }}" class="imagePreview" alt="">
                                        <span class="status online"></span>
                                    </span>
                                </a>
                                <div class="users dropdown-menu dropdown-menu-right user_profile_dd" data-popper-placement="bottom-end">
                                    <div class="user-header">
                                        <div class="avatar avatar-sm">
                                            <img src="{{ $imagepath }}" class="imagePreview" alt="" alt="User Image" class="avatar-img rounded-circle">
                                        </div>
                                        <div class="user-text">
                                            <h6>{{ Auth::guard('learner')->user()->name ?? ' User ' }} </h6>
                                            {{-- <p class="text-muted mb-0">Learner</p> --}}
                                        </div>
                                    </div>
                                    <a class="dropdown-item" href="{{ url('profile') }}"><i class="feather-user me-1"></i>
                                        Profile</a>
                                    <a class="dropdown-item" href="{{ url('dashboard') }}"><i class="feather-home me-1"></i> Dashboard</a>
                                    <a class="dropdown-item" href="{{ url('my_course') }}"><i class="feather-book me-1"></i> My Courses</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"><i class="feather-log-out me-1"></i> Logout</a>
                                </div>
                            </li>
                            @endguest
                        </ul>
                    </div>
                    <button type="button" class="buttonsearch" id="buttonsearch">
                        <i class="fa-solid fa-magnifying-glass openclosesearch" style="{{ isset($_GET['s']) && !empty($_GET['s']) ? 'display:none;' : '' }}"></i><i class="fa-solid fa-remove openclosesearch" style="{{ isset($_GET['s']) && !empty($_GET['s']) ? '' : 'display:none;' }}"></i>
                    </button>

                    <ul class="nav header-navbar-rht">


                        @auth('learner')
                        <li class="nav-item wish-nav">
                            <a href="{{ url('wishlist') }}" class="dropdown-toggle">
                                <img src="{{ asset('front/img/icon/wish.svg') }}" alt="img">
                            </a>
                            {{-- <a href="#" class="dropdown-toggle" id="notifications_stud">
                        <img src="{{ asset('front/img/icon/notification.svg') }}" alt="img">
                            </a> --}}

                            <div class="wishes-list dropdown-menu dropdown-menu-right">
                                <div class="wish-content">
                                    <ul>
                                        <li>
                                            <div class="media">
                                                <div class="d-flex media-wide">
                                                    <div class="avatar">
                                                        <a href="{{ url('course-details') }}">
                                                            <img alt="" src="{{ asset('front/img/course/course-04.jpg') }}">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h6><a href="{{ url('course-details') }}">Learn Angular...</a></h6>
                                                        <p>By Dave Franco</p>
                                                        <h5>$200 <span>$99.00</span></h5>
                                                        <div class="remove-btn">
                                                            <a href="javascript:void(0)" class="btn">Add to cart</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <div class="d-flex media-wide">
                                                    <div class="avatar">
                                                        <a href="{{ url('course-details') }}">
                                                            <img alt="" src="{{ asset('front/img/course/course-14.jpg') }}">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h6><a href="{{ url('course-details') }}">Build Responsive Real...</a>
                                                        </h6>
                                                        <p>Jenis R.</p>
                                                        <h5>$200 <span>$99.00</span></h5>
                                                        <div class="remove-btn">
                                                            <a href="javascript:void(0)" class="btn">Add to cart</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <div class="d-flex media-wide">
                                                    <div class="avatar">
                                                        <a href="{{ url('course-details') }}">
                                                            <img alt="" src="{{ asset('front/img/course/course-15.jpg') }}">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h6><a href="{{ url('course-details') }}">C# Developers Double ...</a>
                                                        </h6>
                                                        <p>Jesse Stevens</p>
                                                        <h5>$200 <span>$99.00</span></h5>
                                                        <div class="remove-btn">
                                                            <a href="javascript:void(0)" class="btn">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item noti-nav">

                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                @php
                                $notifications = getNotificationData();
                                $learner_id = Auth::guard('learner')->id();
                                $notifications = Notification::with('course')
                                ->where('learnerId', $learner_id)
                                ->where('isRead', 0)
                                ->where("type","!=",4)
                                ->orderBy('id', 'DESC')
                                ->get();
                                $count = count($notifications);
                                @endphp
                                <img src="{{ asset('front/img/icon/notification.svg') }}" alt="img">
                                @if($count > 0)
                                <span class="count-3">{{ $count }}</span>
                                @endif
                            </a>


                            <div class="notifications dropdown-menu dropdown-menu-right">

                                {{-- <div class="topnav-dropdown-header">
                        <span class="notification-title">Notifications
                            <select name="notify_filter">
                                <option value="all" selected>All</option>
                                <option value="unread">Unread</option>
                            </select>
                        </span>
                        <a href="javascript:void(0)" class="clear-noti">Mark all as read <i
                                class="feather-solid feather-check-circle"></i></a>
                    </div> --}}

                                <div class="noti-content">
                                    <div class="notification-btn">
                                        <h3>Notification</h3>

                                        @php
                                        $notifications = Notification::with('course')
                                                            ->where('learnerId', $learner_id)
                                                            ->where('isRead', 0)
                                                            ->where("type","!=",4)
                                                            ->orderBy('id', 'DESC')
                                                            ->get();


                                        @endphp

                                        @if ($notifications && $notifications->isNotEmpty())
                                        <a href="{{ route('read_notification') }}" class="h5 read-all-btn">Read all</a>
                                        @endif

                                    </div>
                                    <ul class="notification-list">
                                        @if (isset($notifications) && $notifications->isNotEmpty())
                                        @foreach ($notifications->where("isRead",0) as $value)
                                        <li class="notification-message">
                                            <div class="media d-flex">
                                                <div class="media-body">
                                                    <img src="{{ asset('front/img/icon/notification.svg') }}" alt="Notification">
                                                    <h6 class="mb-0">
                                                        <a href="{{ route('my.details', $value->id) }}">
                                                            {{ $value->title }}

                                                            @if (isset($value->course))
                                                            <span class="course-notification">
                                                                {{-- <a
                                                                href="{{ $value->course->type == 2 ? url('course-package/' . $value->course->slug) : url('course-details/' . $value->course->slug) }}"> --}}
                                                                {{ $value->course->title }}

                                                                {{-- </a> --}}
                                                            </span>
                                                            @endif
                                                        </a>
                                                        <br />
                                                        <span>{{ timeAgo($value->created_at) }}</span>
                                                    </h6>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach

                                        @else
                                        <div class="d-flex justify-content-center align-items-center">
                                            <h6>No New Notifications</h6>
                                        </div>
                                        @endif

                                    </ul>
                                </div>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>


                <div class="container searchbardiv" id="formsearch" style="{{ isset($_GET['s']) && !empty($_GET['s']) ? '' : 'display:none;' }}">
                    <form role="search" method="get" id="searchform" action="{{ url('search') }}">
                        <div class="input-group">
                            <input type="search" id="searchbox" placeholder="Type to search" class="form-control" name="s" id="s" value="{{ isset($_GET['s']) && !empty($_GET['s']) ? $_GET['s'] : '' }}">
                            <div class="input-group-btn">
                                <button class="btn btn-default" id="searchsubmit" type="submit" disabled="disabled">
                                    <strong>Search</strong>
                                </button>

                            </div>
                        </div>
                    </form>

                </div>
            </nav>
        </div>




        @php

        $countriesCollection = Helper::getCountries();
        @endphp
        <div class="modal fade login-modal " id="myModal" tabindex="-1" role="dialog" aria-labelledby="mobileLoginModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        {{-- <button type="button" class="close modalClose1" data-id="#mobileLoginModal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button> --}}
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5 popup-left">
                                <img width="250" src="{{ !empty(config('settings.login_logo')) ? Storage::url(config('settings.login_logo')) : asset('front/img/login_logo.png') }}" class="img-fluid" alt="{{ env('APP_NAME') }}">
                            </div>
                            <!--First Step-->
                            @php
                            $mobile = '';
                            $country_id = '';
                            @endphp
                            @php
                            $mobile = session()->get('mobile');
                            $country_id = session()->get('country_id');
                            @endphp


                            <!--Third Step -->
                            <div class="col-md-7 popup-right" style="display:nonee;" id="">
                                <div class="form-group d-flex flex-wrap login-form step3">
                                    <h2 class="modal-title">{{ __('Profile') }}</h2>
                                    <div id="profileError" style="display:nonee">
                                    </div>
                                    <form method="POST" action="{{ route('student.profile') }}" id="profileForm">
                                        @csrf
                                        <div class="row mb-3">
                                            <div class="col-md-12 mb-2">
                                                <label for="name">Full Name <span style="color: red">*</span></label>
                                                <input type="text" class="form-control" placeholder="Enter your Full Name" name="name" id="name" autofocus value="{{auth()->guard('learner')->check() ? auth()->guard('learner')->user()->name : ""}}">
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label for="email">Email <span style="color: red">*</span></label>
                                                <input id="email" type="email" class="form-control email" name="email" autocomplete="email" autofocus placeholder="Enter Your Email" value="{{auth()->guard('learner')->check() ? auth()->guard('learner')->user()->email : "" }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label for="gender" id="genderLabel">Gender </label></label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="gender" id="male" value="{{ App\Models\Learner::MALE }}" {{ (auth()->guard('learner')->check() ? (auth()->guard('learner')->user()->gender==1 ? "checked" :"") :"" ) }} >
                                                    <label class="form-check-label" for="male">{{ App\Models\Learner::MALE_LABEL }}</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" {{ (auth()->guard('learner')->check() ? (auth()->guard('learner')->user()->gender==2 ? "checked" :"") :"" ) }} name="gender" id="female" value="{{ App\Models\Learner::FEMALE }}">
                                                    <label class="form-check-label" for="female">{{ App\Models\Learner::FEMALE_LABEL }}</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" {{ (auth()->guard('learner')->check() ? (auth()->guard('learner')->user()->gender==3 ? "checked" :"") :"" ) }} type="radio" name="gender" id="other" value="{{ App\Models\Learner::OTHER }}">
                                                    <label class="form-check-label" for="other">{{ App\Models\Learner::OTHER_LABEL }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label for="d_o_b">Date Of birth</label>
                                                <input id="d_o_b" type="date" class="form-control d_o_b" name="d_o_b" max="{{ date('Y-m-d') }}" autocomplete="d_o_b" autofocus placeholder="Enter Your Dateof birth">
                                            </div>
                                            <div class="col-md-6 mb-2" style="display:none">
                                                <label for="country">Country</label><span style="color: red"> *</span>
                                                <select class="form-control country-dropdown" id="country-dropdown" name="country_id">
                                                    <option value="">Select Country</option>
                                                    @if (isset($countriesCollection) && !empty($countriesCollection))
                                                    @foreach ($countriesCollection as $country)
                                                    <option value="{{ $country->id }}">
                                                        {{ $country->name }}
                                                    </option>
                                                    @endforeach
                                                    @else
                                                    <option value="">No Country Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2 popup-select">
                                                <label for="state">State</label> <span style="color: red"> *</span>
                                                {{-- <select class="form-control state-dropdown" id="state-dropdown" name="state_id"> --}}
                                                <select class="form-select form-control state-dropdown" aria-label="Default select example" id="state-dropdown" name="state_id">
                                                    <option value="">Select State</option>
                                                    @if (Helper::getStates() != null)
                                                    @foreach (Helper::getStates() as $state)
                                                    <option value="{{ $state->id }}" {{ Auth::guard('learner')->user()->state_id == $state->id ? 'selected="selected"' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                    @endforeach
                                                    @else
                                                    <option value="">No State Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2 popup-select">
                                                <label for="city">City</label>
                                                <select class="form-select select city-dropdown" id="city-dropdown" name="city_id">
                                                </select>
                                            </div>
                                        </div>


                                        <div class="mb-0">
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Submit') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!--Third Step End -->
                        </div>
                    </div>
                </div>
            </div>

    </header>
