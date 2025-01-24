<?php $page = 'instructor'; ?>
@extends('front.layout.mainlayout')
@section('content')
<style>
    .enroll-img img {
    display: block;
    height: 30px;
}
</style>
    @component('front.components.breadcrumb')
        @slot('title')
            <a href="{{ url('/') }}">Home</a>
        @endslot
        {{-- @slot('li1') Instructure @endslot --}}
        @slot('li2')
            {{ $instructor->name }}
        @endslot
    @endcomponent
    @php
        $avg_inst_rating = avg_inst_rating($instructor->id);
        $avg_inst_rating_count = avg_inst_rating_count($instructor->id);
    @endphp
    <div class="page-banner instructor-bg-blk">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                        @if($instructor->name)
                            <div class="profile-info-blk">
                                @if(getImageIfExists($instructor->profile_picture, user_img_default()))
                                <a href="javascript:;" class="profile-info-img">
                                    <img src="{{ getImageIfExists($instructor->profile_picture, user_img_default()) ? getImageIfExists($instructor->profile_picture, user_img_default()):'' }}" alt="" class="img-fluid">
                                </a>
                                @endif
                                <h4><a href="javascript:;">{{ $instructor->name }}</a></h4>
                                <p>{{ $instructor->instructure->designation??'' }}</p>
                            </div>
                        @else
                            <h1 class="mb-0">{{ $instructor->name }}</h1>
                        @endif
                        <div class="rating mb-0">
                            @for($i=1;$i<=5;$i++)
                                @if((int)$i <= (int)$avg_inst_rating)
                                <i class="fas fa-star filled"></i>
                                @else
                                <i class="fas fa-star"></i>
                                @endif

                            @endfor
                            <span class="d-inline-block average-rating"> ({{$avg_inst_rating_count??0}})</span>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Content -->
    <section class="page-content course-sec">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">

                    <!-- Overview -->
                    <div class="card overview-sec">
                        <div class="card-body">
                            <h5 class="subs-title">About Me</h5>
                            <p>{{ $instructor->instructure->bio??'' }}</p>
                        </div>
                    </div>
                    <!-- /Overview -->
                    <!-- Courses Content -->
                    <div class="card education-sec">
                        <div class="card-body">
                            <h5 class="subs-title">Courses/Package</h5>
                            <div class="row aos" data-aos="fade-up">
                                @if (isset($instructorCourse) && count($instructorCourse) > 0)
                                    @foreach ($instructorCourse as $key => $value)
                                    @php
                                        $avg_course_rating = $value->AverageRating??0;
                                    @endphp
                                        <div class="col-lg-6 col-md-6 d-flex">
                                            <div class="course-box trend-box">
                                                <div class="product trend-product">
                                                    <div class="product-img">
                                                        @if ($value->type == 2)
                                                            <div class="course-package">
                                                                Package
                                                            </div>
                                                        @endif
                                                        <a
                                                            href="{{ url(($value->type == 2 ? 'course-package' : 'course-details') . '/' . $value->slug) }}">
                                                            <img class="img-fluid" alt=""
                                                                src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                        </a>
                                                        @php
                                                            $priceArray=['plan_name'=>$value->plan_name,
                                                                'planId'=>$value->planId,
                                                                'plan_type'=>$value->plan_type,
                                                                'list_price'=>$value->list_price,
                                                                'final_payable_price'=>$value->final_payable_price,
                                                                'courseId' => $value->id,
                                                                'slug' => $value->slug,
                                                                'type' => $value->type
                                                            ];
                                                        @endphp
                                                        {{ $price = min_max_price_btn($priceArray, 1) }}
                                                    </div>
                                                    <div class="product-content">
                                                        <div class="course-group">
                                                                <div class="course-name">
                                                                    <h3 class="title"><a
                                                                        href="{{ url(($value->type == 2 ? 'course-package' : 'course-details') . '/' . $value->slug) }}">{{ $value->title }}</a>
                                                                    </h3>
                                                                    <div
                                                                    class="course-share d-flex align-items-center justify-content-center">
                                                                    @if (!empty($wishlists) && !empty($value->wishlists) && Auth::guard('learner')->check())
                                                                        <a href="javascript:void()"              class="wishlist"
                                                                        data-course_id="{{$value->id}}"
                                                                        data-wishlist_active=1
                                                                        data-wishlist_id="{{!empty($value->wishlists) ? $value->wishlists->pluck('id')->first() : '' }}">
                                                                            <i class="fa-regular fa-heart {{active_wishlist($value->id)}}"></i>
                                                                        </a>
                                                                    @else
                                                                        <a href="javascript:void(0)" class="wishlist"
                                                                            data-course_id="{{ $value->id }}"
                                                                            data-wishlist_active=0
                                                                            data-wishlist_id=0><i
                                                                                class="fa-regular fa-heart"></i></a>
                                                                    @endif
                                                                </div>

                                                                </div>

                                                            <div
                                                                class="course-share ">
                                                                <div class="rating">
                                                                @for($i=1;$i<=5;$i++)
                                                                    <i class="fas fa-star {{$i <= round($avg_course_rating) ? 'filled':''}}"></i>
                                                                @endfor
                                                                    <span
                                                                        class="d-inline-block average-rating">
                                                                        ({{$value->total_review??0}})</span>
                                                                </div>
                                                                @if(isset($value->hours) && !empty($value->hours) && $value->hours != '0' || isset($value->minutes) && !empty($value->minutes) && $value->minutes != '0')
                                                                <div class="course-view d-flex align-items-center">
                                                                    <img src="{{asset('front/img/icon/icon-02.svg')}}" alt="" />
                                                                    @if($value->hours != '0')
                                                                        <p>{{ $value->hours }}h</p>
                                                                    @endif
                                                                    @if($value->minutes != '0')
                                                                        <p>{{ $value->minutes }}m</p>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            </div>
                                                        </div>



                                                        <div class="course-info d-flex align-items-center">
                                                            <div class="rating-img d-flex align-items-center">
                                                                <img src="{{ asset('front/img/icon/icon-01.svg') }}"
                                                                    alt="" class="img-fluid">
                                                                @if ($value->type == 2)
                                                                    <p>{{ $value->packages_count ?? 0 }}+ Lesson</p>
                                                                @else
                                                                    <p>{{ $value->chapters_count ?? 0 }}+ Lesson</p>
                                                                @endif

                                                            </div>
                                                            <div class="course-view d-flex align-items-center">
                                                                <img src="{{ asset('front/img/icon/icon-19.svg') }}"
                                                                    alt="" class="img-fluid">
                                                                <p>{{ $value->lng == 1 ? 'English' : 'Hindi' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="course-instructor d-flex">
                                                            <div class="course-group-img d-flex">
                                                                <a href="{{ url('instructor/' . $instructor->id) }}">
                                                                    <img
                                                                    src="{{ getImageIfExists($instructor->profile_picture, user_img_default()) }}"
                                                                    alt="" class="img-fluid"></a>
                                                                <div class="course-name">
                                                                    <h4><a
                                                                            href="{{ url('instructor/' . $instructor->id) }}">{{ $instructor->name }}</a>
                                                                    </h4>
                                                                    <p>{{ $instructor->instructure->designation??'' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="all-btn all-category course-price">
                                                            {{check_course_is_free_or_not($priceArray,2)}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    @endforeach
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="pagination lms-page">
                                                {!! $instructorCourse->withQueryString()->links('pagination::bootstrap-4') !!}
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-lg-6 col-md-6 d-flex">No Courses

                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /Courses Content -->



                    <!-- Comment -->

                    <div class="card comment-sec">
                        {{-- <div class="card-body">
                        <h5 class="subs-title">Add a review</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Full Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="email" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" placeholder="Subject">
                            </div>
                            <div class="form-group">
                                <textarea rows="4" class="form-control" placeholder="Your Comments"></textarea>
                            </div>
                            <div class="submit-section">
                                <button class="btn submit-btn" type="submit">Submit</button>
                            </div>
                        </form>
                    </div> --}}
                    </div>
                    <!-- /Comment -->

                </div>

                <div class="col-lg-4">



                    <!-- Right Sidebar Profile Overview -->
                    <div class="card overview-sec">
                        @if (isset($instructorCourse) && count($instructorCourse) > 0)
                            <div class="card card-body">
                                <h5 class="subs-title">Profile Overview</h5>
                                {{-- <div class="rating-grp">
                                    <div class="rating">
                                        @for($i=1;$i<=5;$i++)
                                            <i class="fas fa-star {{$i <= $avg_inst_rating ? 'filled':''}}"></i>
                                        @endfor
                                            <span
                                                class="d-inline-block average-rating">
                                                ({{$avg_inst_rating}})</span>
                                        </div>
                                    <div class="course-share d-flex align-items-center justify-content-center">

                                    </div>
                                </div> --}}
                                <div class="profile-overview-list">
                                    <div class="list-grp-blk d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="{{ asset('front/img/instructor/courses-icon.png') }}" alt="Courses">
                                        </div>
                                        <div class="list-content-blk flex-grow-1 ms-3">
                                            <h5>{{ $totalCount??0 }}</h5>
                                            <p>Courses/Package</p>
                                        </div>
                                    </div>
                                    {{-- <div class="list-grp-blk d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="{{ URL::asset('front/img/instructor/ttl-stud-icon.png') }}"
                                                alt="Total Students">
                                        </div>
                                        <div class="list-content-blk flex-grow-1 ms-3">
                                            <h5>11,604</h5>
                                            <p>Total Students</p>
                                        </div>
                                    </div> --}}
                                    <div class="list-grp-blk d-flex">
                                        <div class="flex-shrink-0">
                                            <img src="{{ URL::asset('front/img/instructor/review-icon.png') }}"
                                                alt="Reviews">
                                        </div>
                                        <div class="list-content-blk flex-grow-1 ms-3">
                                            <h5> ({{ $avg_inst_rating??0 }})</h5>
                                            <p>Reviews</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    <div class="card overview-sec">
                        <div class="card-body overview-sec-body">
                            <h5 class="subs-title">Followers</h5>
                            <div class="col-6">
                                <div class="total-course d-flex align-items-top">
                                    <div class="blur-border">
                                        <div class="enroll-img ">
                                            @if (!empty(config('settings.footer_facebook_icon')))
                                                <img
                                                    src="{{ str_contains(asset(Storage::url(config('settings.footer_facebook_icon'))), 'front') ? asset(config('settings.footer_facebook_icon')) : asset(Storage::url(config('settings.footer_facebook_icon'))) }}">
                                            @else
                                                <i class="fa-brands fa-facebook"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="course-count">
                                        <h3><span class="counterUp">{{ $instructor->instructure->facebook_follower ?? 0 }}</span>
                                        </h3>
                                        <p>Followers</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="total-course d-flex align-items-top">
                                    <div class="blur-border">
                                        <div class="enroll-img ">
                                            @if (!empty(config('settings.footer_twitter_icon')))
                                                <img
                                                    src="{{ str_contains(asset(Storage::url(config('settings.footer_twitter_icon'))), 'front') ? asset(config('settings.footer_twitter_icon')) : asset(Storage::url(config('settings.footer_twitter_icon'))) }}">
                                            @else
                                                <i class="fa-brands fa-twitter"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="course-count">
                                        <h3><span class="counterUp">{{ $instructor->instructure->twitter_follower ?? 0 }}</span>
                                        </h3>
                                        <p>Followers</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="total-course d-flex align-items-top">
                                    <div class="blur-border">
                                        <div class="enroll-img ">
                                            @if (!empty(config('settings.footer_insta_icon')))
                                                <img
                                                    src="{{ str_contains(asset(Storage::url(config('settings.footer_insta_icon'))), 'front') ? asset(config('settings.footer_insta_icon')) : asset(Storage::url(config('settings.footer_insta_icon'))) }}">
                                            @else
                                                <i class="fa-brands fa-instagram"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="course-count">
                                        <h3><span class="counterUp">{{ $instructor->instructure->instagram_follower ?? 0 }}</span>
                                        </h3>
                                        <p>Followers</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="total-course d-flex align-items-top">
                                    <div class="blur-border">
                                        <div class="enroll-img ">
                                            @if (!empty(config('settings.footer_youtube_icon')))
                                                <img
                                                    src="{{ str_contains(asset(Storage::url(config('settings.footer_youtube_icon'))), 'front') ? asset(config('settings.footer_youtube_icon')) : asset(Storage::url(config('settings.footer_youtube_icon'))) }}">
                                            @else
                                                <i class="fa-brands fa-youtube"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="course-count">
                                        <h3><span class="counterUp">{{ $instructor->instructure->youtube_follower ?? 0 }}</span>
                                        </h3>
                                        <p>Subscribers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Right Sidebar Tags Label -->
                    <!-- Right Contact Details -->
                    <div class="card overview-sec">
                        <div class="card-body">
                            <h5 class="subs-title">Contact Details</h5>
                            <div class="contact-info-list">
                                <div class="edu-wrap">
                                    <div class="edu-name">
                                        <span><img src="{{ asset('front/img/instructor/email-icon.png') }}"
                                                alt="Address"></span>
                                    </div>
                                    <div class="edu-detail">
                                        <h6>Email</h6>
                                        <p><a href="mailto:{{ $instructor->email }}">{{ $instructor->email }}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Right Contact Details -->

                </div>
            </div>
        </div>
    </section>

@endsection

