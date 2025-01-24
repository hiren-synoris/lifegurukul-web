@if (!Route::is([
    'dashboard-instructor',
    'deposit-instructor-dashboard',
    'deposit-instructor',
    'transactions-instructor',
    'withdrawal-instructor',
]))
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <div class="breadcrumb-list">
                        <nav aria-label="breadcrumb" class="page-breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('/')}}">{{ $title }}</a></li>
                            @if(isset($li1))
                                <li class="breadcrumb-item" aria-current="page">{{ $li1 }}</li>
                            @endif
                            <li class="breadcrumb-item" aria-current="page">{{ $li2 }}</li>
                            @if (Route::is(['course-details', 'course-details1', 'course-details2', 'instructor-edit', 'instructor-profile']) || Request::is('instructor-profile/*'))
                            <li class="breadcrumb-item" aria-current="page">{{ $li3 }}</li>
                            @endif
                        </ol>
                    </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumb -->
@endif
@if (Route::is([
    'dashboard-instructor',
    'deposit-instructor-dashboard',
    'deposit-instructor',
    'transactions-instructor',
    'withdrawal-instructor',
]))
    <!-- Breadcrumb -->
    <div class="page-banner instructor-bg-blk">
        <div class="container">
            @if (!Route::is([
                'deposit-instructor-dashboard',
                'deposit-instructor',
                'transactions-instructor',
                'withdrawal-instructor',
            ]))
                <div class="row align-items-center">
            @endif
            @if (Route::is([
                'deposit-instructor-dashboard',
                'deposit-instructor',
                'transactions-instructor',
                'withdrawal-instructor',
            ]))
                <div class="row align-items-center student-group">
            @endif

            <div class="col-lg-6 col-md-12">
                <div class="instructor-profile-menu">
                    <ul class="nav">
                        <li>
                            <div class="d-flex align-items-center">
                                <div class="instructor-profile-menu-img">
                                    <img src="{{ URL::asset('/assets/img/icon/icon-25.svg') }}" alt="">
                                </div>
                                <div class="instructor-profile-menu-content">
                                    <h4>32</h4>
                                    <p>Courses</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="d-flex align-items-center">
                                <div class="instructor-profile-menu-img">
                                    <img src="{{ URL::asset('/assets/img/icon/icon-26.svg') }}" alt="">
                                </div>
                                <div class="instructor-profile-menu-content">
                                    <h4>11,604</h4>
                                    <p>Total Students</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="d-flex align-items-center">
                                <div class="instructor-profile-menu-img">
                                    <img src="{{ URL::asset('/assets/img/icon/icon-27.svg') }}" alt="">
                                </div>
                                <div class="instructor-profile-menu-content">
                                    <h4>12,230</h4>
                                    <p>Reviews</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (!Route::is([
                    'deposit-instructor-dashboard',
                    'deposit-instructor',
                    'transactions-instructor',
                    'withdrawal-instructor',
                ]))
                    <div class="instructor-profile-text">
                        <p>I am an Innovation designer focussing on UX/UI based in Berlin. As a creative resident at
                            Figma explored the city of the future and how new technologies like AI, voice control, and
                            augmented reality will change our interfaces.</p>
                    </div>
                @endif
                @if (Route::is([
                    'deposit-instructor-dashboard',
                    'deposit-instructor',
                    'transactions-instructor',
                    'withdrawal-instructor',
                ]))
                    <div class="my-student-list">
                        <ul>
                            <li><a class="{{ Request::is('deposit-instructor-dashboard') ? 'active' : '' }}"
                                    href="{{ url('deposit-instructor-dashboard') }}">Dashboard</a></li>
                            <li><a class="{{ Request::is('dashboard-instructor') ? 'active' : '' }}"
                                    href="{{ url('dashboard-instructor') }}">Courses</a></li>
                            <li><a class="{{ Request::is('withdrawal-instructor') ? 'active' : '' }}"
                                    href="{{ url('withdrawal-instructor') }}">Withdrawal</a></li>
                            <li><a href="#">Purchase history</a></li>
                            <li><a class="{{ Request::is('deposit-instructor') ? 'active' : '' }}"
                                    href="{{ url('dashboard-instructor') }}">Deposit</a></li>
                            <li class="mb-0"><a class="{{ Request::is('transactions-instructor') ? 'active' : '' }}"
                                    href="{{ url('transactions-instructor') }}">Transactions</a></li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
    <!-- /Breadcrumb -->
@endif
