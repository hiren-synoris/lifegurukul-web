@extends('front.layout.mainlayout')

@section('content')


<div class="main-wrapper">

    <div class="course-student-header">
        <div class="container">
            @include('front.student.student_group')
        </div>
    </div>


    <section class="course-content purchase-widget">
        <div class="container">
            <div class="student-widget">
                <div class="student-widget-group">
                    <div class="row">
                        <div class="col-lg-12 aos" data-aos="fade-up">
                            @if (isset($courses) && count($courses) > 0)
                    <div class="row">
                        @foreach ($courses as $key => $value)
                        <div class="col-lg-12 col-md-12 d-flex">
                            <div class="course-box course-design list-course d-flex">
                                <div class="product">
                                    <div class="product-img">
                                        @if($value->type == 2)
                                        <div class="course-package">
                                            Package
                                        </div>
                                        @endif
                                        <a
                                            href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">
                                            <img class="img-fluid" alt=""
                                                src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                        </a>
                                        <div></div>
                                        @if($value->is_free)
                                        <div class="price">
                                            <h3>FREE</h3>
                                        </div>
                                        @else
                                        <div class="price">
                                        <h3>&#8377;{{$value->price}}</h3>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="product-content">
                                        <div class="head-course-title">
                                            <h3 class="title"><a
                                                    href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">{{ $value->title }}</a>
                                            </h3>
                                            <div class="all-btn all-category d-flex align-items-center">
                                                <a href="{{url('view-invoice/'.Crypt::encrypt($value->user_courses_id))}}" class="btn btn-primary">Invoice</a>
                                            </div>
                                        </div>
                                        <div class="course-info border-bottom-0 pb-0 d-flex align-items-center">

                                            <div class="rating-img d-flex align-items-center">
                                                <img src="{{asset('front/img/icon/icon-01.svg')}}" alt="">
                                                @if($value->type == 2)
                                                <p>{{ $value->packages_count ?? 0 }}+ Course</p>
                                                @else
                                                <p>{{ $value->chapters_count ?? 0 }}+ Lesson</p>
                                                @endif

                                            </div>
                                            <div class="course-view d-flex align-items-center">
                                                <img src="{{asset('front/img/icon/icon-19.svg')}}" alt="">
                                                <p>{{($value->lng == 1)?"English":"Hindi"}}</p>
                                            </div>
                                            <div class="course-view d-flex align-items-center">
                                                <img src="{{asset('front/img/icon/icon-23.svg')}}" alt="" />
                                                <p>{{(isset($value->categories) && isset($value->categories->name))?$value->categories->name:""}}
                                                </p>
                                            </div>
                                        </div>
                                        @php
                                            $avg_course_rating = $value->AverageRating??0;
                                        @endphp
                                        <div class="rating">
                                            @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                            @endfor
                                            <span class="d-inline-block average-rating">
                                                ({{ $value->total_review??0 }})
                                            </span>
                                        </div>
                                        <div class="course-instructor d-flex mb-0">
                                            @if($value->instructor && !empty($value->instructor) &&
                                            isset($value->instructor->id))
                                            <div class="course-group-img d-flex">
                                                <a href="{{ url('instructor/' . $value->instructor->id) }}"><img
                                                        src="{{ getImageIfExists($value->instructor->profile_picture, user_img_default()) }}"
                                                        alt="" class="img-fluid"></a>
                                                <div class="course-name inst-name">
                                                    <h4><a
                                                            href="{{ url('instructor/' . $value->instructor->id) }}">{{ $value->instructor->name }}</a>
                                                    </h4>
                                                    <p>{{$value->instructor->instructure->designation??''}}</p>
                                                </div>
                                            </div>
                                            @endif
                                            {{-- <div class="course-share d-flex align-items-center justify-content-center">
                                                @if (!empty($wishlists) && !empty($value->wishlists) &&
                                                Auth::guard('learner')->check())
                                                <a href="javascript:void(0)" class="wishlist "
                                                    data-course_id="{{$value->id}}" data-wishlist_active=1
                                                    data-wishlist_id="{{!empty($value->wishlists) ? $value->wishlists->pluck('id')->first() : '' }}">
                                                    <i
                                                        class="fa-regular fa-heart {{active_wishlist($value->id)}}"></i></a>
                                                @else
                                                <a href="javascript:void(0)" class="wishlist"
                                                    data-course_id="{{ $value->id }}" data-wishlist_active=0
                                                    data-wishlist_id=0>
                                                    <i class="fa-regular fa-heart"></i>
                                                </a>
                                                @endif

                                            </div> --}}
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="pagination lms-page">
                                {!! $courses->withQueryString()->links('pagination::bootstrap-4') !!}
                            </ul>
                        </div>
                    </div>
                    @else
                    <h5>No record found!</h5>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



</div>



@endsection
