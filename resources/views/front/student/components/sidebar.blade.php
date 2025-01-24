@if(Route::is(['dashboard','student_wishlist','student_purchase_history',
'student.profile','student.profile.edit','student.profile.update','student.payment','my.order','student.dashboard','my.course','my.wishlists','my.package.course','subscriptions','my.notification','my.details','courses.public-forum-front',"learner_log","my_wallet"]))
<!-- sidebar -->
<div class="col-xl-3 col-md-4 theiaStickySidebar">
    <div class="settings-widget dash-profile mb-3">
        <div class="settings-menu p-0">
            <div class="profile-bg">
                <h5>Learner</h5>
                <img src="{{ asset('front/img/instructor-profile-bg.jpg') }}" alt="">
                <div class="profile-img">
                    @php
                    $imagepath = Helper::profileImage();
                    // $imagepath = ($image)? Storage::url($image) :"/front/img/user/user11.jpg";
                    @endphp
                    <a href="#">
                        <img src="{{$imagepath}}" class="imagePreview" alt="" class="img-fluid"></a>
                </div>
            </div>
            <div class="profile-group">
                <div class="profile-name text-center">
                    <h4><a href="#">{{ Auth::guard('learner')->user()->name ?? '' }}</a></h4>
                    <p>Learner</p>
                </div>
                {{-- <div class="go-dashboard text-center">
                    <a href="{{url('dashboard')}}" class="btn btn-primary">Go to Dashboard</a>
            </div> --}}
        </div>
    </div>
</div>
<div class="settings-widget account-settings">
    <div class="settings-menu">
        <h3>DASHBOARD</h3>
        <ul>
            <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
                <a class="" href="{{url('dashboard')}}#my-dashboard" class="nav-link">
                    <i class="feather-home"></i> My Dashboard
                </a>
            </li>
            <li class="nav-item {{ Request::is('my_course') || Request::is('my_package_course') || Request::is('public-forum-front')  ? 'active' : '' }}">
                <a class="" href="{{url('my_course')}}#my_courses" class="nav-link">
                    <i class="feather-book"></i> My Courses
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('my.wishlists') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('my.wishlists') }}#my-wishlist">
                    <i class="feather-heart"></i> My Wishlist
                </a>
            </li>
            <li class="nav-item {{ Request::is('my-order') ? 'active' : '' }}">
                <a class="" href="{{url('my-order')}}#my_order" class="nav-link">
                    <i class="feather-shopping-cart"></i> Purchase History
                </a>
            </li>

            <li class="nav-item {{ Request::is('learner-log') ? 'active' : '' }}">
                <a class="" href="{{url('learner-log')}}#learner_log" class="nav-link">
                    <i class="feather-activity"></i> Activity Log
                </a>
            </li>
            <li class="nav-item {{ Request::is('my-wallet') ? 'active' : '' }}">
                <a class="" href="{{url('my-wallet')}}#my_wallet" class="nav-link">
                    <i class="feather-shopping-bag"></i> My Wallet
                </a>
            </li>
            <li class="nav-item">
                <a class="{{ Request::is('my-notification') ? 'active' : '' }}" href="{{url('my-notification')}}" class="nav-link ">
                    <img src="{{ asset('front/img/icon/notification.svg') }}" alt="Notification" style="padding-right: 10px;">
                    {{-- <i class="feather-bell"></i> --}}
                    Notifications
                </a>
            </li>
            {{-- <li class="nav-item">
                    <a class="{{ Request::is('my-subscription') ? 'active' : '' }}" href="{{url('my-subscription')}}" class="nav-link ">
            <i class="feather-calendar"></i> My Subscriptions
            </a>
            </li> --}}
        <!-- </ul>
        <h3>ACCOUNT SETTINGS</h3>
        <ul> -->

            <li class="nav-item">
                <a class="{{ Request::is('profile') ? 'active' : '' }}" href="{{url('profile')}}" class="nav-link ">
                    <i class="feather-settings"></i> Edit Profile
                </a>
            </li>





            <li class="nav-item {{ Request::is('logout') ? 'active' : '' }}">
                <a class="" href="{{url('logout')}}" class="nav-link">
                    <i class="feather-power"></i> Sign Out
                </a>
            </li>
        </ul>
    </div>
</div>
</div>
<!-- /sidebar -->
@endif
