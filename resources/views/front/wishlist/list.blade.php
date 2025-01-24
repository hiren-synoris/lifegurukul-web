<?php $page="wishlist";?>
@extends('front.layout.mainlayout')
@section('content')
<div class="page-content">
    <div class="container">
        <div class="row">

            @include('front.student.components.sidebar')

            <div class="col-xl-9 col-md-8">
                <div class="settings-widget">
                    <div class="settings-inner-blk p-0">
                        <div class="profile-heading">
                            <h3>My Wishlist ({{ isset($wishlist) && !empty($wishlist) ? $wishlist : 0 }} items)</h3>
                        </div>
                        <div class="checkout-form add-course-info" id="my-wishlist">
                            @if (isset($courses) && count($courses) > 0)
                            <div class="row">
                                @foreach ($courses as $key => $value)
                                <div class="col-lg-12 col-md-12 d-flex aos" data-aos="fade-up">
                                    <div class="course-box course-design list-course d-flex">
                                        <div class="product">
                                            <div class="product-img">
                                                @if($value->type == 2)
                                                <div class="course-package">
                                                    Package
                                                </div>
                                                @endif
                                                <a
                                                    href="{{ $value->type == 1 ? url('course-details' .'/' . $value->slug) : url('course-package/'.$value->slug)}}">
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                </a>
                                                @php
                                                $priceArray=['plan_name'=>$value->plan_name,
                                                'planId'=>$value->planId,
                                                'plan_type'=>$value->plan_type,
                                                'list_price'=>$value->list_price,
                                                'final_payable_price'=>$value->final_payable_price
                                                ];
                                                @endphp
                                                {{ $price = min_max_price_btn($priceArray, 1) }}

                                            </div>
                                            <div class="product-content">
                                                <div class="head-course-title">
                                                    <h3 class="title">
                                                        <a
                                                            href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">{{
                                                            $value->title }}</a>
                                                    </h3>
                                                    <div
                                                        class="course-share d-flex align-items-center justify-content-center">
                                                        @if (!empty($wishlists) && !empty($value->wishlists) &&
                                                        Auth::guard('learner')->check())
                                                        <a href="javascript:void(0)" class="wishlist btn btn-primary"
                                                            data-course_id="{{ $value->id }}" data-wishlist_active=1
                                                            data-wishlist_id="{{ !empty($value->wishlists) ? $value->wishlists->pluck('id')->first() : '' }}">

                                                            @else
                                                            <a href="javascript:void(0)"
                                                                class="wishlist btn btn-primary"
                                                                data-course_id="{{ $value->id }}" data-wishlist_active=0
                                                                data-wishlist_id=0>
                                                                Remove
                                                            </a>
                                                            @endif

                                                    </div>
                                                    {{-- <div class="all-btn all-category d-flex align-items-center">
                                                        <a href="{{route('front.wishlist.destory', ['wishlist' =>(!empty($value->wishlists) ? $value->wishlists->pluck('id')->first() : '') ])}}"
                                                            class="btn btn-primary">Remove</a>
                                                    </div> --}}
                                                </div>
                                                <div class="course-info border-bottom-0 pb-0 d-flex align-items-center">
                                                    <div class="rating-img d-flex align-items-center">
                                                        <img src="{{asset('front/img/icon/icon-01.svg')}}" alt="">
                                                        <p>{{ $value->chapters_count ?? 0 }}+ Lesson</p>
                                                    </div>
                                                    @if($value->type == 1)
                                                    <div class="course-view d-flex align-items-center">
                                                        <img src="{{asset('front/img/icon/icon-19.svg')}}" alt="">
                                                        <p>{{($value->lng == 1)?"English":"Hindi"}}</p>
                                                    </div>
                                                    @endif
                                                    @if(isset($value->hours) && !empty($value->hours) && $value->hours
                                                    != '0' || isset($value->minutes) && !empty($value->minutes) &&
                                                    $value->minutes != '0')
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
                                                @php
                                                $avg_course_rating = $value->AverageRating??0;
                                                @endphp
                                                <div class="rating">
                                                    @for ($i = 1; $i <= 5; $i++) <i
                                                        class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}">
                                                        </i>
                                                        @endfor
                                                        <span class="d-inline-block average-rating">
                                                            ({{ $value->total_review??0 }})
                                                        </span>
                                                </div>
                                                <div class="course-instructor d-flex mb-0">
                                                    @if($value && isset($value->instructor))
                                                    <div class="course-group-img d-flex">
                                                        <a href="{{ url('instructor/' . $value->instructor->id) }}"><img
                                                                src="{{ getImageIfExists($value->instructor->profile_picture, user_img_default()) }}"
                                                                alt="" class="img-fluid"></a>
                                                        <div class="course-name">
                                                            <h4><a
                                                                    href="{{ url('instructor/' . $value->instructor->id) }}">{{
                                                                    $value->instructor->name }}</a>
                                                            </h4>
                                                            <p>{{$value->instructor->instructure->designation??''}}</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="row">
                                <div class="col-lg-12 col-md-12 d-flex aos" data-aos="fade-up">
                                    <p>Your wishlist is empty.</p>
                                </div>
                            </div>
                            @endif
                            {!! $courses->withQueryString()->links('pagination::bootstrap-4') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
