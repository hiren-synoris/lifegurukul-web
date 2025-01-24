<?php
use App\Models\Course;
$page = 'index'; ?>
@extends('front.layout.mainlayout')
@section('content')

    <!-- Top Categories -->
    @php
        $forslider = true;
        if (!is_null(config('settings.home_slider_visibility'))) {
            $forslider = in_array(Course::COURSE_WEBSITE, stringToArray(config('settings.home_slider_visibility')));
        }
    @endphp
    @if (isset($slider) && count($slider->slides) > 0 && $forslider)
        <section class="section home-banner-section">
            <div class="container-fluid px-0">
                <div class='home' id='home_slider'>
                    @foreach ($slider->slides as $key => $value)
                        <a href="{{ empty($value->action_url) || $value->action_url == '#' ? 'javascript:void(0)' : $value->action_url }}"
                            {{ $value->new_window && $value->action_url != '#' ? 'target="_blank"' : '' }}>
                            <div class="item ">
                                <div class="banner-item img-fluid aos" data-aos="fade-up"><img
                                        src="{{ str_contains(asset(Storage::url($value->img)), 'front') ? asset($value->img) : asset(Storage::url($value->img)) }}"
                                        alt="">
                                    {{-- @if (!str_contains(asset(Storage::url($value->img)), 'front')) --}}
                                    <!-- <div class="container">
                                                    <div class="caption_class"
                                                        style="{{ $value->direction == 0 ? 'left:10%' : 'right:10%' }}">
                                                        @if (!empty($value->caption1))
    <div class="banner-inner-text">
                                                                <figcaption id="figcaption" style="{{ !empty($value->caption1_text_color) ? 'color:' . $value->caption1_text_color : '' }}">{{ $value->caption1 }}</figcaption>
                                                            </div>
    @endif
                                                        @if (!empty($value->caption2))
    <h2 class="banner-title">
                                                                <figcaption id="figcaption" style="{{ !empty($value->caption2_text_color) ? 'color:' . $value->caption2_text_color : '' }}">{{ $value->caption2 }}</figcaption>
                                                            </h2>
    @endif
                                                        @if (!empty($value->action_text) && !empty($value->action_url))
    <a href="{{ $value->action_url }}" class="btn btn-primary"
                                                                @if ($value->new_window) target="_blank" @endif  style="{{ !empty($value->button_bg_color) ? 'background-color:' . $value->button_bg_color : '' }}"><span style="{{ !empty($value->button_text_color) ? 'color:' . $value->button_text_color : '' }}">{{ $value->action_text }}</span>
                                                                <i class="fa-solid fa-arrow-right"></i></a>
    @endif
                                                    </div>
                                                </div> -->
                                    {{-- @endif --}}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- /Top Categories -->

    <!-- Feature Course -->
    @php
        $forfeatured = true;
        if (!is_null(config('settings.home_featured_courses_visibility'))) {
            $forfeatured = in_array('1', stringToArray(config('settings.home_featured_courses_visibility')));
        }
    @endphp
    @if (isset($topFeatureCourse) && count($topFeatureCourse) > 0 && $forfeatured)
        <section class="section new-course">
            <div class="container">
                <div class="section-header aos" data-aos="fade-up">
                    <div class="section-sub-head">
                        <span>What’s News</span>
                        <h2>Featured Courses</h2>
                    </div>
                    <div class="all-btn all-category d-flex align-items-center justify-content-between">
                        <a href="{{ url('course') }}" class="btn btn-primary">All Courses</a>
                    </div>
                </div>
                <div class="section-text aos" data-aos="fade-up">
                    <p class="mb-0">
                        {{ config('settings.homepage_feature_course') ??
                            'Lorem ipsum dolor sit amet, consectetur adipiscing
                                                                elit. Eget aenean accumsan bibendum
                                                                gravida maecenas augue elementum et neque. Suspendisse imperdiet.' }}
                    </p>
                </div>
                <div class="course-feature">

                    <div class="owl-carousel trending-course owl-theme aos" data-aos="fade-up">
                        @forelse ($topFeatureCourse as $key => $topCourse)
                            @php
                                $avg_course_rating = $topCourse->AverageRating ?? 0;
                            @endphp
                            <div class="course-box trend-box">
                                <div class="product trend-product">
                                    <div class="product-img">
                                        @if ($topCourse->type == 2)
                                            <div class="course-package">
                                                Package
                                            </div>
                                        @endif
                                        <a
                                            href="{{ $topCourse->type == 2 ? url('course-package/' . $topCourse->slug) : url('course-details/' . $topCourse->slug) }}">
                                            <img class="img-fluid" alt=""
                                                src="{{ getImageIfExists($topCourse->image, course_img_default()) }}">
                                        </a>

                                    </div>
                                    <div class="product-content">
                                        <div class="course-group">
                                            <div class="course-name">
                                                <h3 class="title"><a
                                                        href="{{ $topCourse->type == 2 ? url('course-package/' . $topCourse->slug) : url('course-details/' . $topCourse->slug) }}">{{ $topCourse->title }}</a>
                                                </h3>

                                                <div class="course-share d-flex align-items-center justify-content-center">
                                                    @if (!empty($topCourse) && !empty($topCourse->wishlists) && Auth::guard('learner')->check())
                                                        <a href="javascript:void(0)" class="wishlist "
                                                            data-course_id="{{ $topCourse->id }}" data-wishlist_active=1
                                                            data-wishlist_id="{{ !empty($topCourse->wishlists) ? $topCourse->wishlists->pluck('id')->first() : '' }}">
                                                            <i
                                                                class="fa-regular fa-heart {{ active_wishlist($topCourse->id) }}"></i></a>
                                                    @else
                                                        <a href="javascript:void(0)" class="wishlist"
                                                            data-course_id="{{ $topCourse->id }}" data-wishlist_active=0
                                                            data-wishlist_id=0>
                                                            <i class="fa-regular fa-heart"></i>
                                                        </a>
                                                    @endif

                                                </div>
                                            </div>
                                            <div class="rating-cat">
                                                <div class="course-share">
                                                    <div class="rating">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}">
                                                            </i>
                                                        @endfor
                                                        <span class="d-inline-block average-rating">
                                                            ({{ $topCourse->total_review ?? 0 }})
                                                        </span>
                                                    </div>

                                                    @if (
                                                        (isset($topCourse->hours) && !empty($topCourse->hours) && $topCourse->hours != '0') ||
                                                            (isset($topCourse->minutes) && !empty($topCourse->minutes) && $topCourse->minutes != '0'))
                                                        <div class="course-view d-flex align-items-center">
                                                            <img src="{{ asset('front/img/icon/icon-02.svg') }}"
                                                                alt="" />
                                                            @if ($topCourse->hours != '0')
                                                                <p>{{ $topCourse->hours }}h</p>
                                                            @endif
                                                            @if ($topCourse->minutes != '0')
                                                                <p>{{ $topCourse->minutes }}m</p>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    {{-- <img src="{{ asset('front/img/icon/icon-23.svg') }}" alt=""
                                            class="img-fluid"> --}}
                                                    <!-- @if ($topCourse->type == 1)
    -->
                                                    <!-- @foreach ($topCourse->categories as $categories_key => $categories_value)
    <p>{{ isset($categories_value) && isset($categories_value->name) ? $categories_value->name : '' }}</p>
    @endforeach -->
                                                    <!--
    @endif -->
                                                </div>
                                            </div>
                                        </div>




                                        <div class="course-info d-flex align-items-center">
                                            <div class="rating-img d-flex align-items-center">
                                                <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt=""
                                                    class="img-fluid">
                                                @if ($topCourse->type == 2)
                                                    <p>{{ $topCourse->packages_count ?? '0' }}+ Course</p>
                                                @else
                                                    <p>{{ $topCourse->chapters_count ?? '0' }}+ Lesson</p>
                                                @endif
                                            </div>
                                            {{-- @if ($topCourse->type == 1) --}}
                                            <div class="course-view d-flex align-items-center">
                                                <img src="{{ asset('front/img/icon/icon-19.svg') }}" alt=""
                                                    class="img-fluid">
                                                <p>{{ $topCourse->lng == 1 ? 'English' : 'Hindi' }}</p>
                                            </div>

                                            {{-- @endif --}}
                                        </div>
                                        {{-- @if ($topCourse->type == 1) --}}
                                        <div class="course-instructor d-flex">
                                            @if ($topCourse->instructor && !empty($topCourse->instructor) && isset($topCourse->instructor->id))
                                                <div class="course-group-img d-flex">
                                                    <a href="{{ url('instructor/' . $topCourse->instructor->id) }}"><img
                                                            src="{{ getImageIfExists($topCourse->instructor->profile_picture, user_img_default()) }}"
                                                            alt="" class="img-fluid"></a>
                                                    <div class="course-name">
                                                        <h4><a
                                                                href="{{ url('instructor/' . $topCourse->instructor->id) }}">{{ $topCourse->instructor->name }}</a>
                                                        </h4>
                                                        <p>{{ $topCourse->instructor->instructure->designation ?? '' }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        {{-- @endif --}}

                                        <div class="all-btn all-category course-price">
                                            @php
                                                $priceArray = [
                                                    'plan_name' => $topCourse->plan_name,
                                                    'planId' => $topCourse->planId,
                                                    'plan_type' => $topCourse->plan_type,
                                                    'list_price' => $topCourse->list_price,
                                                    'final_payable_price' => $topCourse->final_payable_price,
                                                    'courseId' => $topCourse->id,
                                                    'slug' => $topCourse->slug,
                                                    'type' => $topCourse->type,
                                                ];
                                            @endphp
                                            {{ check_course_is_free_or_not($priceArray, 1) }}
                                            {{ $price = min_max_price_btn($priceArray, 1) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse
                    </div>

                </div>
            </div>
        </section>
    @endif

    <!-- /Feature Course -->
    @php
        $fortopfree = true;
        if (!is_null(config('settings.home_top_free_visibility'))) {
            $fortopfree = in_array(Course::COURSE_WEBSITE, stringToArray(config('settings.home_top_free_visibility')));
        }
    @endphp
    @if (isset($topFreeCourse) && count($topFreeCourse) > 0 && $fortopfree)
        <section class="section trend-course">
            <div class="container">
                <div class="section-header aos" data-aos="fade-up">
                    <div class="section-sub-head text-center w-100">
                        <span>TOP FREE </span>
                        <h2>Video Trainings From Life Gurukul</h2>
                    </div>
                </div>
                <div class="owl-carousel trending-course owl-theme aos" data-aos="fade-up">
                    @forelse ($topFreeCourse as $topCourse)
                        {{-- {{dd($topCourse->hours)}} --}}
                        @php
                            $avg_course_rating = $topCourse->AverageRating ?? 0;
                        @endphp
                        <div class="course-box trend-box">
                            <div class="product trend-product">
                                <div class="product-img">
                                    @if ($topCourse->type == 2)
                                        <div class="course-package">
                                            Package
                                        </div>
                                    @endif
                                    <a
                                        href="{{ $topCourse->type == 2 ? url('course-package/' . $topCourse->slug) : url('course-details/' . $topCourse->slug) }}">
                                        <img class="img-fluid" alt=""
                                            src="{{ getImageIfExists($topCourse->image, course_img_default()) }}">
                                    </a>
                                </div>
                                <div class="product-content">
                                    <div class="course-group">
                                        <div class="course-name">
                                            <h3 class="title"><a
                                                    href="{{ $topCourse->type == 2 ? url('course-package/' . $topCourse->slug) : url('course-details/' . $topCourse->slug) }}">{{ $topCourse->title }}</a>
                                            </h3>

                                            {{-- @if (!empty($wishlist))
                                <a href="javascript:void(0)" class="wishlist " data-course_id="{{$topCourse->id}}"
                                    data-wishlist_active=1
                                    data-wishlist_id="{{!empty($topCourse->wishlists) ? $topCourse->wishlists->pluck('id')->first() : '' }}">
                                    <i
                                        class="fa-regular fa-heart {{ !empty($topCourse->wishlists) ? in_array($topCourse->wishlists->pluck('id')->first(), $wishlist->pluck('id')->toArray()) ? 'color-active' : '' : '' }}"></i>
                                </a>
                                @else
                                <a href="javascript:void(0)" class="wishlist" data-course_id="{{ $topCourse->id }}"
                                    data-wishlist_active=0 data-wishlist_id=0><i class="fa-regular fa-heart"></i></a>
                                @endif --}}
                                            <div class="course-share d-flex align-items-center justify-content-center">
                                                @if (!empty($topCourse) && !empty($topCourse->wishlists) && Auth::guard('learner')->check())
                                                    <a href="javascript:void(0)" class="wishlist "
                                                        data-course_id="{{ $topCourse->id }}" data-wishlist_active=1
                                                        data-wishlist_id="{{ !empty($topCourse->wishlists) ? $topCourse->wishlists->pluck('id')->first() : '' }}">
                                                        <i
                                                            class="fa-regular fa-heart {{ active_wishlist($topCourse->id) }}"></i></a>
                                                @else
                                                    <a href="javascript:void(0)" class="wishlist"
                                                        data-course_id="{{ $topCourse->id }}" data-wishlist_active=0
                                                        data-wishlist_id=0>
                                                        <i class="fa-regular fa-heart"></i>
                                                    </a>
                                                @endif

                                            </div>
                                        </div>

                                        <div class="rating-cat">
                                            <div class="course-share">
                                                <div class="rating">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                                    @endfor
                                                    <span class="d-inline-block average-rating">
                                                        ({{ $topCourse->total_review ?? 0 }})
                                                    </span>
                                                </div>
                                                @if ($topCourse->type == 1)
                                                    <!-- @foreach ($topCourse->categories as $categories_key => $categories_value)
    <p>{{ isset($categories_value) && isset($categories_value->name) ? $categories_value->name : '' }}</p>
    @endforeach -->
                                                    <!-- <p>{{ isset($topCourse->categories) && isset($topCourse->categories->name) ? $topCourse->categories->name : '' }}</p> -->
                                                @endif

                                                @if (
                                                    (isset($topCourse->hours) && !empty($topCourse->hours) && $topCourse->hours != '0') ||
                                                        (isset($topCourse->minutes) && !empty($topCourse->minutes) && $topCourse->minutes != '0'))
                                                    <div class="course-view d-flex align-items-center">
                                                        <img src="{{ asset('front/img/icon/icon-02.svg') }}"
                                                            alt="" />
                                                        @if ($topCourse->hours != '0')
                                                            <p>{{ $topCourse->hours }}h</p>
                                                        @endif
                                                        @if ($topCourse->minutes != '0')
                                                            <p>{{ $topCourse->minutes }}m</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="course-info d-flex align-items-center">
                                        <div class="rating-img d-flex align-items-center">
                                            <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt=""
                                                class="img-fluid">
                                            @if ($topCourse->type == 2)
                                                <p>{{ $topCourse->packages_count ?? '0' }}+ Course</p>
                                            @else
                                                <p>{{ $topCourse->chapters_count ?? '0' }}+ Lesson</p>
                                            @endif

                                        </div>
                                        @if ($topCourse->type == 1)
                                            <div class="course-view d-flex align-items-center">
                                                <img src="{{ asset('front/img/icon/icon-19.svg') }}" alt=""
                                                    class="img-fluid">
                                                <p>{{ $topCourse->lng == 1 ? 'English' : 'Hindi' }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- @if ($topCourse->type == 1) --}}
                                    <div class="course-instructor d-flex">
                                        {{-- {{dd($topCourse->instructor)}} --}}
                                        @if ($topCourse->instructor && !empty($topCourse->instructor) && isset($topCourse->instructor->id))
                                            <div class="course-group-img d-flex top-free-training-section">
                                                {{-- {{dd($topCourse->instructor->profile_picture)}} --}}
                                                <a href="{{ url('instructor/' . $topCourse->instructor->id) }}"><img
                                                        src="{{ getImageIfExists($topCourse->instructor->profile_picture, user_img_default()) }}"
                                                        alt="" class="img-fluid"></a>
                                                <div class="course-name">
                                                    <h4><a
                                                            href="{{ url('instructor/' . $topCourse->instructor->id) }}">{{ $topCourse->instructor->name }}</a>
                                                    </h4>
                                                    <p>{{ $topCourse->instructor->instructure->designation ?? '' }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- @endif --}}
                                    <div class="all-btn all-category course-price">
                                        @php

                                            $priceArray = [
                                                'plan_name' => $topCourse->plan_name,
                                                'planId' => $topCourse->planId,
                                                'plan_type' => $topCourse->plan_type,
                                                'list_price' => $topCourse->list_price,
                                                'final_payable_price' => $topCourse->final_payable_price,
                                                'courseId' => $topCourse->id,
                                                'slug' => $topCourse->slug,
                                                'type' => $topCourse->type,
                                            ];
                                        @endphp
                                        {{ check_course_is_free_or_not($priceArray, 1) }}

                                        <div class="price">
                                            <h3>Free <span></span></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="course-box trend-box">
                            No course Found
                        </div>
                    @endforelse
                </div>
            </div>



        </section>
    @endif

    <!-- Top categories -->
    @php
        $forcategories = true;
        if (!is_null(config('settings.home_course_category_visibility'))) {
            $forcategories = in_array('1', stringToArray(config('settings.home_course_category_visibility')));
        }
    @endphp
    @if (count($categories) > 0 && $forcategories)
        <section class="section how-it-works">
            <div class="container">
                <div class="section-header aos" data-aos="fade-up">
                    <div class="section-sub-head">
                        <span>Course Category</span>
                        <h2>{{ !empty(config('settings.home_top_category_title'))
                            ? config('settings.home_top_category_title')
                            : 'Top Category' }}
                        </h2>
                    </div>
                    <div class="all-btn all-category d-flex align-items-center justify-content-between">
                        <a href="{{ url('categories') }}" class="btn btn-primary">All Categories</a>
                    </div>
                </div>
                <div class="section-text aos" data-aos="fade-up">
                    <p>{{ !empty(config('settings.home_top_category_description'))
                        ? config('settings.home_top_category_description')
                        : "Lorem ipsum dolor sit amet, consectetur adipiscing
                                                        elit. Eget aenean accumsan bibendum gravida maecenas augue elementum et neque. Suspendisse imperdiet." }}
                    </p>
                </div>
                <div class="owl-carousel mentoring-course owl-theme aos top-categories-slider" data-aos="fade-up">
                    @if (isset($categories) && !empty($categories) && count($categories) > 0)
                        @foreach ($categories->sortByDesc('id') as $category)
                            <a href="{{ url('course?categorylist=' . $category->id) }}">
                                <div class="feature-box text-center ">
                                    <div class="feature-bg">
                                        <div class="feature-header">
                                            <div class="feature-icon">
                                                <img src="{{ !str_contains($category->image, 'front') ? getImageIfExists($category->image, default_category_img()) : asset($category->image) }}"
                                                    alt="">
                                            </div>
                                            <div class="feature-cont">
                                                <div class="feature-text">{{ $category->name }}</div>
                                            </div>
                                        </div>
                                        {{-- <p>{{ isset($category->total_course) && !empty($category->total_course)
                                            ? $category->total_course . ' Courses'
                                            : '0 Course' }}
                                        </p> --}}
                                        <p>{{ Helper::countCategory($category->id) . ' Courses' }}

                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

            </div>
        </section>
    @endif
    <!-- /Top Categories -->
    <!-- Leading Companies -->
    @php
        $fortrustedby = false;
        if (!is_null(config('settings.home_trusted_by_visibility'))) {
            $fortrustedby = in_array('1', stringToArray(config('settings.home_trusted_by_visibility')));
        }
    @endphp
    @if (!empty($trusted_by) && count($trusted_by->dropdownOptions) > 0 && $fortrustedby)
        <section class="section lead-companies">
            <div class="container">
                <div class="section-header aos" data-aos="fade-up">
                    <div class="section-sub-head feature-head text-center">
                        <span>Trusted By</span>
                        <h2>{{ !empty(config('settings.home_trusted_by_subheading'))
                            ? config('settings.home_trusted_by_subheading')
                            : '100+ Leading Universities And Companies' }}
                        </h2>
                    </div>
                </div>
                <div class="lead-group aos" data-aos="fade-up">
                    <div class="lead-group-slider owl-carousel owl-theme">
                        @foreach ($trusted_by->dropdownOptions as $key => $value)
                            @if (!empty($value->image))
                                <div class="item">
                                    <div class="lead-img">
                                        <img class="img-fluid"
                                            src="{{ str_contains(asset(Storage::url($value->image)), 'front') ? asset($value->image) : asset(Storage::url($value->image)) }}">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- /Leading Companies -->


    <!-- Users Love -->
    @php
        $foruserslove = true;
        if (!is_null(config('settings.home_user_love_visibility'))) {
            $foruserslove = in_array('1', stringToArray(config('settings.home_user_love_visibility')));
        }
    @endphp
    @if ($foruserslove)
        <section class="section user-love"
            style="background-image:url({{ asset('front/img/woman-g522250506_1920.jpg') }}) !important">
            <div class="container">
                <div class="section-header white-header aos" data-aos="fade-up">
                    <div class="section-sub-head feature-head text-center">
                        <span>Check out these real reviews</span>
                        <h2>Learners love "LifeGurukul"</h2>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- /Users Love -->

    <!-- Say testimonial Four -->
    @php
        $fortestimonial = true;
        if (!is_null(config('settings.home_testimonial_visibility'))) {
            $fortestimonial = in_array('1', stringToArray(config('settings.home_testimonial_visibility')));
        }
    @endphp
    @if ($fortestimonial)
        <section class="testimonial-four">
            <div class="review">
                <div class="container">
                    <!-- <div class="testi-quotes">
                                    <img src="{{ Storage::url('front/img/qute.png') }}" alt="">
                                </div> -->
                    <div class="mentor-testimonial lazy slider aos" data-aos="fade-up" data-sizes="50vw ">
                        @foreach ($review as $review_val)
                            <div class="d-flex justify-content-center ">
                                <div class="testimonial-all d-flex justify-content-center" style="width:100%;">
                                    <div class="testimonial-two-head text-center align-items-center d-flex">
                                        <div class="testimonial-four-saying testimonial-four-min-height">
                                            <!-- <div class="testi-right">
                                                        <img src="{{ Storage::url('front/img/qute-01.png') }}" alt="">
                                                    </div> -->
                                            {{-- <p>{{ $review_val->comment }}. --}}
                                            <p>{{ Str::limit($review_val->comment, 300, '...') }}.
                                            </p>
                                            <div class="four-testimonial-founder">
                                                <div class="fount-about-img">
                                                    {{-- <a href="javascript:void(0)"><img --}}
                                                    {{-- src="{{ asset('front/img/user/user.svg') }}" alt=""
                                                        class="img-fluid"></a> --}}
                                                    @if ($review_val->learner->profile_pic != null)
                                                        <a href="javascript:void(0)"><img
                                                                src='{{ url('storage/' . $review_val->learner->profile_pic) }}'
                                                                alt="" class="img-fluid"></a>
                                                    @else
                                                        <a href="javascript:void(0)"><img
                                                                src="{{ asset('front/img/user/user.svg') }}"
                                                                alt="" class="img-fluid"></a>
                                                    @endif

                                                </div>
                                                <h3><a href="javascript:void(0)">{{ $review_val->learner?->name }}</a>
                                                </h3>
                                                <span>{{ $review_val->course->title }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{-- <div class="d-flex justify-content-center">
                            <div class="testimonial-all d-flex justify-content-center">
                                <div class="testimonial-two-head text-center align-items-center d-flex">
                                    <div class="testimonial-four-saying ">
                                        <!-- <div class="testi-right">
                                                    <img src="{{ Storage::url('front/img/qute-01.png') }}" alt="">
                                                </div> -->
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem
                                            Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                                            unknown printer took a galley of type and scrambled it to make a type specimen
                                            book.
                                        </p>
                                        <div class="four-testimonial-founder">
                                            <div class="fount-about-img">
                                                <a href="javascript:void(0)"><img
                                                        src="{{ asset('front/img/user/user.svg') }}" alt=""
                                                        class="img-fluid"></a>
                                            </div>
                                            <h3><a href="javascript:void(0)">john smith</a></h3>
                                            <span>Founder of Awesomeux Technology</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="testimonial-all d-flex justify-content-center">
                                <div class="testimonial-two-head text-center align-items-center d-flex">
                                    <div class="testimonial-four-saying ">
                                        <!-- <div class="testi-right">
                                                    <img src="{{ Storage::url('front/img/qute-01.png') }}" alt="">
                                                </div> -->
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem
                                            Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                                            unknown printer took a galley of type and scrambled it to make a type specimen
                                            book.
                                        </p>
                                        <div class="four-testimonial-founder">
                                            <div class="fount-about-img">
                                                <a href="javascript:void(0)"><img
                                                        src="{{ asset('front/img/user/user.svg') }}" alt=""
                                                        class="img-fluid"></a>
                                            </div>
                                            <h3><a href="javascript:void(0)">David Lee</a></h3>
                                            <span>Founder of Awesomeux Technology</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- /Say testimonial Four -->


    <!-- Share Knowledge -->
    @php
        $fortestimonial = true;
        if (!is_null(config('settings.home_about_us_visibility'))) {
            $fortestimonial = in_array('1', stringToArray(config('settings.home_about_us_visibility')));
        }
        $about = App\Models\Page::where('slug', 'about-us')->first();
    @endphp
    @if ($fortestimonial && $about->status)
        <section class="section become-instructors">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="knowledge-img aos" data-aos="fade-up">
                            <img src="{{ asset('front/img/vector-3.svg') }}" alt="" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="join-mentor aos" data-aos="fade-up">
                            <h2>{{ !empty(config('settings.home_about_title'))
                                ? config('settings.home_about_title')
                                : "Life
                                                                                Gurukul is an application that includes lessons" }}
                            </h2>
                            <p>{{ !empty(config('settings.home_about_description'))
                                ? config('settings.home_about_description')
                                : "On career, business, self-improvement, yoga & fitness, relationship management, and much more.
                                                                                It
                                                                                is an initiative by Sneh Desai who is a Life and Business coach for the last 25 Years. And is
                                                                                now working along with his team for the betterment of society. Programs are designed in such a
                                                                                manner that it covers all the age groups without any prerequisites. All you need is a strong
                                                                                zest to learn new skills for your betterment." }}
                            </p>
                            <ul class="course-list">
                                <li><i class="fa-solid fa-circle-check"></i>Best Courses</li>
                                <!-- <li><i class="fa-solid fa-circle-check"></i>Top rated Instructors</li> -->
                            </ul>
                            <div class="all-btn all-category d-flex align-items-center justify-content-between">
                                <a href="{{ url('page/about-us') }}" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- /Share Knowledge -->

    <!-- Latest Blog -->
    @php
        $forblogs = true;
        $forconnect = true;
        if (!is_null(config('settings.home_top_blogs_visibility'))) {
            $forblogs =
                !empty(config('settings.home_top_blogs_visibility')) &&
                in_array(Course::COURSE_WEBSITE, stringToArray(config('settings.home_top_blogs_visibility')));
        }
        if (!is_null(config('settings.home_connect_visibility'))) {
            $forconnect =
                !empty(config('settings.home_connect_visibility')) &&
                in_array(Course::COURSE_WEBSITE, stringToArray(config('settings.home_connect_visibility')));
        }
    @endphp
    @if ($forblogs || $forconnect)
        <section class="section latest-blog">
            @if (count($blogs) > 0 && $forblogs)
                <div class="container">
                    <div class="section-header aos" data-aos="fade-up">
                        <div class="section-sub-head feature-head m-0 w-100">
                            <div class="row align-items-center">
                                <div class="col-12 d-flex align-items-center">
                                    <h2>Latest Blogs</h2>
                                    <div class="all-btn all-category d-flex align-items-center justify-content-between">
                                        <a href="{{ url('blogs') }}" class="btn btn-primary">View All Blogs</a>
                                    </div>

                                </div>
                                <div class="col-12">
                                    <div class="section-text aos" data-aos="fade-up">
                                        <p class="mb-0">
                                            {{ !empty(config('settings.blogs')) ? config('settings.blogs') : '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="owl-carousel blogs-slide owl-theme aos aos" data-aos="fade-up" id="blogslide">
                        @foreach ($blogs as $key => $value)
                            <div class="instructors-widget blog-widget">
                                <div class="instructors-img">
                                    <a href="{{ url('blogs/' . $value->slug) }}">
                                        <img class="img-fluid" alt=""
                                            src="{{ getImageIfExists($value->cover, course_img_default()) }}">
                                    </a>
                                </div>
                                <div class="instructors-content text-center">
                                    <h5><a href="{{ url('blogs/' . $value->slug) }}">{{ $value->title }}</a></h5>
                                    <!-- <p>Marketing</p> -->
                                    <div class="student-count d-flex justify-content-center">
                                        <i class="fa-solid fa-calendar-days"></i>
                                        <span>{{ $value->date }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($forconnect)
                <div class="container">
                    <div class="section-header aos" data-aos="fade-up">
                        <div class="section-sub-head mt-5 feature-head text-center">
                            <span>Connect With</span>
                            <h2 class="mb-0">Life Gurukul </h2>
                        </div>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                    <div class="enroll-group social-followers aos" data-aos="fade-up">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="total-course d-flex align-items-center">
                                                    <div class="blur-border">
                                                        <div class="enroll-img ">
                                                            <a href="javascript:;">
                                                                @if (!empty(config('settings.footer_insta_icon')))
                                                                    <img
                                                                        src="
                                                                {{ str_contains(asset(Storage::url(config('settings.footer_insta_icon'))), 'front') ? asset(config('settings.footer_insta_icon')) : asset(Storage::url(config('settings.footer_insta_icon'))) }}">
                                                                @else
                                                                    <i class="fa-brands fa-instagram insta-gram"></i>
                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="course-count">
                                                        <h3><span
                                                                class="counterUp">{{ !empty(config('settings.footer_insta_followers')) ? config('settings.footer_insta_followers') : 0 }}</span>
                                                        </h3>
                                                        <p>Followers</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="total-course d-flex align-items-center">
                                                    <div class="blur-border">
                                                        <div class="enroll-img ">
                                                            <a href="javascript:;">
                                                                @if (!empty(config('settings.footer_facebook_icon')))
                                                                    <img
                                                                        src="{{ str_contains(asset(Storage::url(config('settings.footer_facebook_icon'))), 'front') ? asset(config('settings.footer_facebook_icon')) : asset(Storage::url(config('settings.footer_facebook_icon'))) }}">
                                                                @else
                                                                    <i class="fa-brands fa-facebook face-book"></i>
                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="course-count">
                                                        <h3><span
                                                                class="counterUp">{{ !empty(config('settings.footer_facebook_followers')) ? config('settings.footer_facebook_followers') : 0 }}</span>
                                                        </h3>
                                                        <p>Followers</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="total-course d-flex align-items-center">
                                                    <div class="blur-border">
                                                        <div class="enroll-img ">
                                                            <a href="javascript:;">
                                                                @if (!empty(config('settings.footer_youtube_icon')))
                                                                    <img
                                                                        src="{{ str_contains(asset(Storage::url(config('settings.footer_youtube_icon'))), 'front') ? asset(config('settings.footer_youtube_icon')) : asset(Storage::url(config('settings.footer_youtube_icon'))) }}">
                                                                @else
                                                                    <i class="fa-brands fa-youtube you-tube"></i>
                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="course-count">
                                                        <h3><span
                                                                class="counterUp">{{ !empty(config('settings.footer_youtube_followers')) ? config('settings.footer_youtube_followers') : 0 }}</span>
                                                        </h3>
                                                        <p>Subscribers</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="total-course d-flex align-items-center">
                                                    <div class="blur-border">
                                                        <div class="enroll-img ">
                                                            <a href="javascript:;">
                                                                @if (!empty(config('settings.footer_linkedin_icon')))
                                                                    <img
                                                                        src="{{ str_contains(asset(Storage::url(config('settings.footer_linkedin_icon'))), 'front') ? asset(config('settings.footer_linkedin_icon')) : asset(Storage::url(config('settings.footer_linkedin_icon'))) }}">
                                                                @else
                                                                    <i class="fa-brands fa-linkedin linked-in"></i>
                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="course-count">
                                                        <h3><span
                                                                class="counterUp">{{ !empty(config('settings.footer_linkedin_followers')) ? config('settings.footer_linkedin_followers') : 0 }}</span>
                                                        </h3>
                                                        <p>Followers</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="come-soon-box">
                                        <h5 class="h4 font-weight-normal">Subscribe to our mailing list to get latest
                                            updates</h5>
                                        <p id="newsletter_msg"></p>
                                        <div class="subscribe-soon">
                                            <form method="POST" action="{{ url('newsletter') }}" id="newsletterForm">
                                                @csrf
                                                <div class="form-group">
                                                    <input type="text" id="name" class="form-control w-100"
                                                        placeholder="Enter Your Name" name='name'>
                                                </div>
                                                <div class="form-group">
                                                    <input type="email" id="email" class="form-control w-100"
                                                        placeholder="Enter Your Email Address" name='email'>
                                                </div>
                                                <div class="form-check home-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="agree"
                                                        name="agree">
                                                    <label class="form-check-label" for="agree">I agree to the <a
                                                            style='color: #F66962;'
                                                            href="{{ url('page/term-condition') }}">Terms of
                                                            Service</a> and <a href="{{ url('page/privacy-policy') }}"
                                                            style='color: #F66962;'>Privacy Policy</a></label>
                                                </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-danger">
                                                        Subscribe
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                @php
                    // dd(Auth::guard('learner')->user());
                    if (Auth::guard('learner')->user()) {
                        // dump(Auth::guard('learner')->user());
                        $email = Js::from(Auth::guard('learner')->user()->email);
                        $country_id = Js::from(Auth::guard('learner')->user()->country_id);
                    } else {
                        $email = '';
                        $country_id = '';
                    }

                @endphp
            @endif
        </section>
    @endif

@endsection

@section('js')
    @if (isset($slider))
        <script>



            $(document).ready(function() {
                // console.log("{{ $country_id }}");
                // var country_id = "{{ $country_id }}"

                // $(".country-dropdown").val(country_id.replace(/"|'/g,'')).change();

                // var chekck_email = "{{ $email }}";

                // if((chekck_email=="null")) {

                //     $("#myModal").modal("show")
                // } else {

                //     $("#myModal").modal("hide")

                // }

                $(".country-dropdown").on("change", function() {
                    var country_id = this.value;
                    $(".state-dropdown").html("");
                    $.ajax({
                        url: "{{ url('get-states-by-country') }}",
                        type: "POST",
                        data: {
                            country_id: country_id,
                            _token: "{{ csrf_token() }}",
                        },
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(result) {
                            (".state-dropdown").empty();
                            $('#loader_section').hide();
                            $(".state-dropdown").append(
                                '<option value="">Select State</option>'
                            );

                            $.each(result.states, function(key, value) {
                                $(".state-dropdown").append(
                                    '<option value="' +
                                    value.id +
                                    '">' +
                                    value.name +
                                    "</option>"
                                );
                            });
                            $(".city-dropdown").html(
                                '<option value="">Select State First</option>'
                            );
                        },
                    });
                });
                $(".state-dropdown").on("change", function() {
                    var state_id = this.value;


                    $(".city-dropdown").html("");
                    $.ajax({
                        url: "{{ url('get-cities-by-state') }}",
                        type: "POST",
                        data: {
                            state_id: state_id,
                            _token: "{{ csrf_token() }}",
                        },
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(result) {
                            // (".city-dropdown").empty();
                            $('#loader_section').hide();
                            $(".city-dropdown").append(
                                '<option value="">Select City</option>'
                            );

                            $.each(result.cities, function(key, value) {
                                $(".city-dropdown").append(
                                    '<option value="' +
                                    value.id +
                                    '">' +
                                    value.name +
                                    "</option>"
                                );
                            });
                        },
                    });
                });

                // $("#myModal").modal('show')
            })
            // $.ajax({
            //     url: "https://www.youtube.com/watch?v=iKd4z1tkKbI",
            //     type: 'GET',
            //     beforeSend: function(xhr){
            //         xhr.setRequestHeader('Authorization', 'Bearer AIzaSyCnCakGjSgKw0kJdzHLgj9S65YWZ9yj3Dw')
            //     },
            //     success: function(response){
            //         console.log("success");
            //         console.log("response");
            //     },
            //     error: function(jqxhr, textStatus, error) {
            //         console.log(jqxhr);
            //         console.log(jqxhr.status);
            //         console.log(textStatus);
            //         console.log(error);
            //         // AIzaSyCnCakGjSgKw0kJdzHLgj9S65YWZ9yj3Dw
            //     }
            // });
            var loggedin = "false";
            loggedin = "{{ Auth::guard('learner')->check() }}";
            var autoplay = "{{ $slider->autoplay }}" == true;
            var obj = {
                speed: 750,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true,
                fade: true,
                dots: true,
                prevArrow: '<button class="slide-arrow prev-arrow"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>',
                nextArrow: '<button class="slide-arrow next-arrow"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>'
            };
            if (autoplay == false) {
                obj.arrows = false;
                obj.dots = false;
                obj.prevArrow = null;
                obj.nextArrow = null;
                obj.speed = 0,
                    obj.autoplay = false,
                    obj.autoplaySpeed = 0,
                    obj.fade = false,
                    $("#home_slider").find(".banner-item > img").addClass('img-fluid').css('display', 'inline-block');
            } else {
                $("#home_slider").find(".banner-item > img").removeClass('img-fluid').css('display', 'block');
            }
            $('.home').slick(obj);
            // Treand Course

            if ($('.owl-carousel.trending-course').length > 0) {
                var owl = $('.owl-carousel.trending-course');
                owl.owlCarousel({
                    margin: 24,
                    nav: false,
                    nav: true,
                    loop: true,
                    responsive: {
                        0: {
                            items: 1
                        },
                        768: {
                            items: 2
                        },
                        1170: {
                            items: 3
                        }
                    }
                });
            }

            if (window.performance) {

                var navEntries = window.performance.getEntriesByType('navigation');
                if (navEntries.length > 0 && navEntries[0].type === 'back_forward') {
                    //  alert ("back");
                } else if (window.performance.navigation && window.performance.navigation.type == window.performance.navigation
                    .TYPE_BACK_FORWARD) {
                    // alert ("back forward");
                } else {

                    @if (session('notification') &&
                            is_array(session('notification')) &&
                            count(session('notification')) > 0 &&
                            session('notification')['type'] == 'sweet-alert')
                        Swal.fire({
                            icon: "{{ session('notification')['status'] }}",
                            title: "{{ session('notification')['title'] }}",
                            text: "{{ session('notification')['msg'] }}",
                        });
                    @endif
                }
            }
        </script>
    @endif
@endsection
