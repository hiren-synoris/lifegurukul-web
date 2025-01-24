@extends('front.layout.mainlayout')

@section('content')

    @component('front.components.breadcrumb')
        @slot('title')
            <a href="{{ url('/') }}">Home</a>
        @endslot
        {{-- @slot('li1') Pages @endslot --}}
        @slot('li2')
            Search List
        @endslot
    @endcomponent
    @php
        $course = 0;
        $blog = 0;
        $category = 0;
        $instruc = 0;
    @endphp
    <div class="page-content">
        <div class="container">
            <div class="row">

                @forelse($searchData as $key => $search)
                    <!-- Course Section start -->
                    @if ($search->flag == 'a')
                        @if ($course == 0)
                            <div class="col-lg-12 col-md-12 d-flex">
                                <h1 class="search-title">Course/Package</h1>
                            </div>
                        @endif
                        @php $course++; @endphp
                                <div class="col-lg-12 col-md-12 d-flex aos" data-aos="fade-up">
                                    <div class="course-box course-design list-course d-flex">
                                        <div class="product">
                                            <div class="product-img">
                                                @if($search->type == 2)
                                                    <div class="course-package">
                                                        Package
                                                    </div>
                                                @endif
                                                <a href="{{ $search->type == 2 ? url('course-package/' . $search->slug) : url('course-details/' . $search->slug) }}">
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($search->path, course_img_default()) }}">
                                                </a>
                                                @php
                                                    $avg_course_rating = avg_course_rating($search->id);
                                                @endphp
                                                @php
                                                $priceArray=['plan_type'=>$search->plan_type,
                                                             'planId' =>$search->planId,
                                                            'list_price'=>$search->list_price,
                                                            'final_payable_price'=>$search->final_payable_price,
                                                            'courseId' => $search->id,
                                                            'slug' => $search->slug,
                                                            'type' => $search->type
                                                            ];
                                                @endphp
                                                {{ $price = min_max_price_btn($priceArray, 1) }}
                                            </div>
                                            <div class="product-content">
                                                <div class="head-course-title">
                                                    <h3 class="title"><a
                                                        href="{{ $search->type == 2 ? url('course-package/' . $search->slug) : url('course-details/' . $search->slug) }}">{{ $search->title }}</a>
                                                    </h3>
                                                    <div class="all-btn all-category d-flex align-items-center">
                                                        {{check_course_is_free_or_not($priceArray,2)}}
                                                    </div>
                                                </div>
                                                <div class="course-info border-bottom-0 pb-0 d-flex align-items-center">
                                                    <div class="rating-img d-flex align-items-center">
                                                        <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt="">
                                                    @if($search->type == 2)
                                                        <p>{{ count($search->packages) ?? 0 }} + Course</p>
                                                    @else
                                                        <p>{{ count($search->chapters) ?? 0 }} + Lesson</p>
                                                    @endif
                                                    </div>
                                                    @if($search->type == 1)
                                                    <div class="course-view d-flex align-items-center">
                                                        <img src="{{ asset('front/img/icon/icon-19.svg') }}" alt="">
                                                        <p>{{ $search->lng == 1 ? 'English' : 'Hindi' }}</p>
                                                    </div>
                                                    @endif
                                                    <!-- @if($search->type == 1)
                                                    <div class="course-view d-flex align-items-center">
                                                        <img src="{{ asset('front/img/icon/icon-23.svg') }}" alt="">
                                                         @foreach($search->categories as $categories_key=>$categories_value)

                                                        <p>{{(isset($categories_value) && isset($categories_value->name))?$categories_value->name:""}}</p>
                                                        @endforeach
                                                    </div>
                                                    @endif -->

                                                    @if(isset($search->hours) && !empty($search->hours) && $search->hours != '0' || isset($search->minutes) && !empty($search->minutes) && $search->minutes != '0')
                                                        <div class="course-view d-flex align-items-center">
                                                            <img src="{{asset('front/img/icon/icon-02.svg')}}" alt="" />
                                                            @if($search->hours != '0')
                                                                <p>{{ $search->hours }}h</p>
                                                            @endif
                                                            @if($search->minutes != '0')
                                                                <p>{{ $search->minutes }}m</p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="rating">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                    <i
                                                        class="fas fa-star {{ $i <= $avg_course_rating ? 'filled' : '' }}"></i>
                                                    @endfor
                                                    <span class="d-inline-block average-rating">
                                                        ({{ count($search->rating_reviews) }})
                                                    </span>
                                                </div>

                                                @if (isset($search->instructor_id) && $search->instructor_id > 0)
                                                    @php
                                                        $instructor = Helper::getInstructure($search->instructor_id);
                                                        $instructor = $search->instructor;
                                                    @endphp
                                                    @if (isset($instructor))
                                                        <div class="course-instructor d-flex mb-0">
                                                            {{-- @if($search->type == 1) --}}
                                                            <div class="course-group-img d-flex">
                                                                <a href="{{ url('instructor/' . $instructor->id) }}"><img
                                                                        src="{{ getImageIfExists($instructor->profile_picture, user_img_default()) }}"
                                                                        alt="" class="img-fluid"></a>
                                                                <div class="course-name">
                                                                    <h4><a
                                                                            href="{{ url('instructor/' . $instructor->id) }}">{{ $instructor->name }}</a>
                                                                    </h4>
                                                                    <p>{{$instructor->instructure->designation??''}}</p>
                                                                </div>
                                                            </div>
                                                            {{-- @endif --}}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                    <!-- Course End -->

                    <!-- Blogs Section start -->
                    @if ($search->flag == 'c')

                        @if ($blog == 0)
                            <div class="col-lg-12 col-md-12 d-flex">
                                <h1 class="search-title">blog</h1>
                            </div>
                        @endif
                        @php
                            $blog++;
                        @endphp
                        <div class="col-lg-4 col-md-6 aos" data-aos="fade-up">
                            <div class="blog grid-blog">
                                <div class="blog-image">
                                    <a href="{{ url('blogs/' . $search->slug) }}"><img class="img-fluid"
                                            src="{{getImageIfExists($search->path,blog_img_default())}}"
                                            alt="Post Image"></a>
                                </div>
                                <div class="blog-grid-box masonry-box">
                                    <div class="blog-info clearfix">
                                        <div class="post-left">
                                            <ul>
                                                <li><img class="img-fluid"
                                                        src="{{ URL::asset('/front/img/icon/icon-22.svg') }}"
                                                        alt="">{{ date('F d, Y', strtotime($search->created_at)) }}
                                                </li>
                                                <li><img class="img-fluid" src="{{ URL::asset('/front/img/icon/icon-23.svg') }}" alt="">
                                                    {{ (isset($search->tags) && isset($search->tags))?$search->tags:'' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <h3 class="blog-title"><a href="{{ url('blogs/' . $search->slug) }}">
                                            {{ $search->title }}
                                        </a>
                                    </h3>
                                    <div class="blog-content blog-read">
                                        <p>{!! substr(strip_tags($search->content,'...'), 0, 150).'...' !!}</p>
                                        <a href="{{ url('blogs/' . $search->slug) }}"
                                            class="read-more btn btn-primary">Read
                                            More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Blogs End -->

                    <!-- Instructure Section start -->
                    @if ($search->flag == 'b')
                        @if ($instruc == 0)
                            <div class="col-lg-12 col-md-12 d-flex">
                                <h1 class="search-title">Instructor</h1>
                            </div>
                        @endif
                        @php
                            $instruc++;
                        @endphp
                        <div class="col-lg-4 col-md-6 aos" data-aos="fade-up">
                            <div class="search-instructor-box text-center ">
                                <div class="search-instructor-header">
                                    <div class="search-instructor-icon">
                                        <img src="{{ !empty($search->path) ? Helper::asseturl($search->path, true) : user_img_default() }}"
                                            alt="">
                                    </div>
                                    <a href="{{ url('instructor/' . $search->id) }}">
                                        <div class="instructor-cont">
                                            <div class="instructor-text">{{ $search->title }}</div>
                                            <p> {{ $search->tags??'' }}</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Instructure End -->

                    <!-- Category Section start -->
                    @if ($search->flag == 'd')
                        @if ($category == 0)
                            <div class="col-lg-12 col-md-12 d-flex">
                                <h1 class="search-title">category</h1>
                            </div>
                        @endif
                        @php
                            $category++;
                        @endphp
                        <div class="col-lg-4 col-md-6 aos" data-aos="fade-up">
                            <div class="category-box">
                                <div class="category-title">
                                    <div class="category-img">
                                        <img src="{{ !str_contains($search->path, 'front') ? getImageIfExists($search->path, default_category_img())  : asset($search->path) }}"
                                            alt="">
                                    </div>
                                    <a href="{{ url('course?categorylist=' . $search->id) }}">
                                        <h5>{{ $search->title }}</h5>
                                    </a>
                                </div>
                                {{-- <div class="cat-count">
                                    <span>{{ $search->courses_count??'' }}</span>
                                </div> --}}
                            </div>
                        </div>
                    @endif
                @empty
                    <p>No Search Results Found..</p>
                @endforelse
                <div class="load-more text-center">
                    <div class="col-md-12">
                        {!! $searchData->appends($_GET)->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
