@extends('front.layout.mainlayout')
@section('content')
<!--Dashbord Student -->
<div class="page-content">
    <div class="container">
        <div class="row">

            @include('front.student.components.sidebar')

            <!-- Instructor Dashboard -->
            <div class="col-xl-9 col-md-8" id="my_courses">
                <div class="showing-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="show-filter choose-search-blk">
                                <form action="#">
                                    <div class="mycourse-student align-items-center">
                                        <div class="student-search">
                                            <div class="search-group">
                                                <input type="text" value="{{ isset($_GET['searchTxt']) && $_GET['searchTxt'] != '' ? $_GET['searchTxt'] : '' }}" class="form-control" id="searchTxt" name="searchTxt" placeholder="Search courses">
                                                <button id="searchButton" type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                                            </div>
                                        </div>
                                        {{-- <div class="student-filter">
                                                        <div class="form-group select-form mb-0">
                                                            <select class="form-select select" name="sellist1">
                                                                <option>Newly published </option>
                                                                <option>Angular</option>
                                                                <option>React</option>
                                                                <option>Node</option>
                                                            </select>
                                                        </div>
                                                    </div> --}}
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @if (isset($courses) && count($courses) > 0)
                <div class="row">
                    @foreach ($courses as $key => $value)
                    @php
                    // if($value->id == 40){
                    // dd($value);
                    // }
                    // @dd($courses);
                    $expireFlag = is_expired($value->userCourse->expire_at);
                    // dd($expireFlag);
                    @endphp
                    @php
                    // if($key == 1) dd($value);
                    try {
                    //code...
                    $rr_count = $value->rr->rating_reviews->sortDesc()->values()->first()->rating;
                    } catch (\Throwable $th) {
                    //throw $th;
                    $rr_count = 0;
                    }

                    @endphp
                    <div class="col-xl-4 col-md-6 d-flex dashboard-course">
                        <div class="course-box course-design d-flex ">
                            <div class="product">
                                <div class="product-img">
                                    @if($value->type == 2)
                                    <div class="course-package">
                                        Package
                                    </div>
                                    @endif
                                    @if($expireFlag == 0)
                                    <a href="{{$value->type == 2 ? url('course-package/'. $value->slug) : url('course-details/'. $value->slug)}}">
                                        <img class="img-fluid" alt="" src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                    </a>
                                    @else
                                    <a href="{{ $value->type == 2 ? url('my_package_course/'. $value->user_courses_id) : url('course-view/'. $value->slug) }}">
                                        <img class="img-fluid" alt="" src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                    </a>
                                    @endif
                                </div>
                                <div class="product-content">
                                    <h3 class="title">
                                        @if($expireFlag == 0)
                                        <a href="{{$value->type == 2 ? url('course-package/'. $value->slug) : url('course-details/'. $value->slug)}}">{{ $value->title }}</a>
                                        @else
                                        <a href="{{ $value->type == 2 ? url('my_package_course/'. $value->user_courses_id) : url('course-view/'. $value->slug) }}">{{ $value->title }}</a>
                                        @endif
                                    </h3>

                                    <div class="rating-student">
                                        @php
                                        $avg_course_rating = $value->AverageRating??0;
                                        @endphp
                                        {{-- @if($value->type == 1) --}}
                                        <div class="rating">
                                            @for ($i = 1; $i <= 5; $i++) <i class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                                @endfor
                                                <span class="d-inline-block average-rating">
                                                    ({{ $value->total_review??0 }})
                                                </span>
                                        </div>
                                        {{-- @endif --}}
                                        {{-- @if($value->type == 1) --}}
                                        <div class="edit-rate">
                                            @php
                                            $count=$rr_count;
                                            $text = $count > 0 ? 'Edit' : 'Add';
                                            $url="javascript:void(0)";
                                            if($count > 0){
                                            $url = '/course-package/'.$value->slug.'?q='.$value->rr->rating_reviews->pluck('id')->max().'#review';
                                            }
                                            else{
                                            $url = '/course-details/'.$value->slug.'#instructor_section';

                                            }
                                            @endphp
                                            <a href="{{ url($url) }}"> {{ $text }} Review</a>
                                        </div>
                                        {{-- @endif --}}
                                    </div>
                                    @if($value->type != 2)
                                    <div class="progress-stip">
                                        {{-- //active-stip --}}
                                        <div class="progress-bar bg-success progress-bar-striped " style="width:{{$value->totalProgress??0}}%">
                                        </div>
                                    </div>
                                    {{-- {{$value->totalDuration}}
                                    {{$value->totalCompletedDuration}} --}}
                                    <div class="student-percent">
                                        {{-- {{dd($value->totalProgress)}} --}}
                                        <p>{{$value->totalProgress??0}}% Completed</p>
                                    </div>
                                    @endif

                                    <div class="student-percent valid-till">
                                        <p><b>Validity: </b>
                                            @if($expireFlag == 0)
                                            <span class="text-danger">Expired</span>
                                            @elseif($expireFlag == 1)

                                            {{-- style="color:#fd7e14" --}}
                                            <span style="color:#fc0c0c"> {{/*dateFormate($value->userCourse->expire_at)==1 ? dateFormate($value->userCourse->expire_at).' Days' :*/ dateFormate($value?->userCourse?->expire_at) > 1 ? dateFormate($value?->userCourse?->expire_at).' Days' : dateFormate($value->userCourse->expire_at).' Day'}} </span>
                                            @elseif($expireFlag == 2)
                                            <span class="text-success">LifeTime</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($value->type == 2)
                                    <div class="start-leason d-flex align-items-center">
                                        <a href="{{ $expireFlag == 0 ? url('course-package/'. $value->slug) : route('my.package.course', ['userCourseId' => $value->userCourse->id])}}" class="btn btn-primary">View Courses</a>
                                    </div>
                                    @else
                                    <div class="start-leason d-flex align-items-center">
                                        @if($expireFlag == 0)
                                        {{-- <a href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}"
                                        class="btn btn-primary">Expired</a> --}}
                                        <a target="_blank" href="{{$value->type == 2 ? url('course-package/'. $value->slug) : url('course-details/'. $value->slug)}}" class="btn btn-primary">Expired</a>
                                        @elseif($expireFlag == 2 || $expireFlag == 1)
                                        <a target="_blank" href="{{url('course-view/'. $value->slug)}}" class="btn btn-primary">Start Lesson</a>
                                        @endif
                                    </div>

                                    @endif

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
            <!-- /Instructor Dashboard -->
        </div>
    </div>
</div>
<!-- /Dashbord Student -->
<form action="" method="GET" id="search_frm" style="display:none;">
    <input type="hidden" value="{{ isset($_GET['searchTxt']) && $_GET['searchTxt'] != '' ? $_GET['searchTxt'] : '' }}" name="searchTxt" id="searchTxt">
    <div class="clear-filter d-flex align-items-center">
        <h4>
            <button type="submit" class="btn btn-primary" id="search_frm_btn">
                <i class="feather-filter"></i>Filters</button>
        </h4>
        <div class="clear-text">
            <a class="btn btn-warning" href="{{ url('course') }}" style="cursor:pointer;">RESET</a>
        </div>
    </div>
</form>
@endsection
@section('js')
@php $ratingjs=true; @endphp
<script>
    $(document).on("keyup", "#srch", function(event) {
        let data = $('input[name=searchTxt]').val();
        $("#searchTxt").val(data);
    });

    $('#searchButton').click(function(e) {
        $('#search_frm_btn').click(); //Trigger search button click event
    });
</script>
@endsection
