@extends('front.layout.mainlayout')
@section('content')
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')

                <!-- Instructor Dashboard -->
                <div class="col-xl-9 col-lg-8 col-md-12">
                    <div class="showing-list">
                        <div class="row">
                                    <h3 class="text text-success">{{ @$userCourse->newCourse->title }}</h3>
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

                        @if(isset($userCourse) && !empty($userCourse))
                            @php
                                $expireFlag = is_expired($userCourse->expire_at);
                                @endphp
                            @if(isset($userCourse->coursePackages) && !empty($userCourse->coursePackages))

                            {{-- Course Packages Collection  --}}
                                <div class="row">
                                    @foreach($userCourse->coursePackages as $coursePackage)
                                        {{-- @dd($userCourse->coursePackages) --}}
                                        @if(isset($coursePackage->original_course) && !empty($coursePackage->original_course))
                                        {{-- @dd($coursePackage->original_course) --}}
                                      <div class="col-xl-4 col-lg-4 col-md-6 d-flex dashboard-course">
                                                <div class="course-box course-design d-flex ">
                                                    <div class="product">
                                                        <div class="product-img">
                                                            @if($coursePackage->original_course->type == 2)
                                                                <div class="course-package"> Package </div>
                                                            @endif
                                                            @if($expireFlag == 0)

                                                                <a href="javascript:void(0)">
                                                                    <img class="img-fluid" alt="" src="{{ getImageIfExists($coursePackage->original_course->image, course_img_default()) }}">
                                                                </a>
                                                            @else
                                                            {{-- @dump("ok") --}}
                                                                <a
                                                                    href="{{ $coursePackage->original_course->type == 2 ? url('my_package_course/'. $userCourse->id) : url('course-view/'. $coursePackage->original_course->slug) }}">
                                                                    <img class="img-fluid" alt=""
                                                                        src="{{ getImageIfExists($coursePackage->original_course->image, course_img_default()) }}">
                                                                </a>
                                                            @endif
                                                        </div>
                                                        <!-- product-img end-->

                                                        <div class="product-content">
                                                            <h3 class="title">
                                                                @if($expireFlag == 0)
                                                                    <a href="javascript:void(0)">{{ $coursePackage->original_course->title }}</a>
                                                                @else
                                                                    <a
                                                                    href="{{ $coursePackage->original_course->type == 2 ? url('my_package_course/'. $userCourse->id) : url('course-view/'. $coursePackage->original_course->slug) }}">{{ $coursePackage->original_course->title }}</a>
                                                                @endif
                                                            </h3>
                                                            <div class="rating-student">
                                                                <div class="rating">
                                                                    @for($i=1;$i<=5;$i++)
                                                                        <i class="fas fa-star {{ $coursePackage->original_course->rating > 0 && $i <= $coursePackage->original_course->rating  ? 'filled':''}}"></i>
                                                                    @endfor
                                                                    <span
                                                                        class="d-inline-block average-rating"><span>({{ $coursePackage->original_course->total_review }})</span></span>
                                                                </div>
                                                                <div class="edit-rate">
                                                                    @php
                                                                        $count = 0;
                                                                        if(isset($coursePackage->original_course->max_review_id) && !empty($coursePackage->original_course->max_review_id)){
                                                                            $count = $coursePackage->original_course->max_review_id;
                                                                        }
                                                                        $text = $count > 0 ? 'Edit' : 'Add';
                                                                        $url="javascript:void(0)";
                                                                        if($count > 0){
                                                                            $url = env('APP_URL').'/course-details/'.$coursePackage->original_course->slug.'?q='.$count.'#review';
                                                                        } else{
                                                                            $url = env('APP_URL').'/course-details/'.$coursePackage->original_course->slug.'#instructor_section';
                                                                        }
                                                                    @endphp
                                                                    <a href="{{ $url }}"> {{ $text }} Review</a>
                                                                </div>
                                                            </div>
                                                            @if($coursePackage->original_course->type != 2)
                                                                <div class="progress-stip">
                                                                    <div
                                                                        class="progress-bar bg-success progress-bar-striped active-stip" style="width:{{ $coursePackage->original_course->total_video_completed_percentage }}%">
                                                                    </div>
                                                                </div>

                                                                <div class="student-percent">
                                                                    <p>{{ $coursePackage->original_course->total_video_completed_percentage }}% Completed</p>
                                                                </div>
                                                            @endif

                                                            <div class="student-percent valid-till">
                                                                <p><b>Valid Till : </b>
                                                                    @if($expireFlag == 0)
                                                                        <span class="text-danger">Expired</span>
                                                                    @elseif($expireFlag == 1)
                                                                        <span class="text-danger">{{ dateFormate($userCourse->expire_at) ." Days" }}</span>
                                                                    @elseif($expireFlag == 2)
                                                                        <span class="text-success">LifeTime</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            @if($coursePackage->original_course->type == 2)
                                                                <div class="start-leason d-flex align-items-center">
                                                                        <a href="{{ url('my_package_course/'. $userCourse->id) }}"
                                                                        class="btn btn-primary">View Course</a>
                                                                </div>
                                                            @else
                                                                <div class="start-leason d-flex align-items-center">
                                                                    @if($expireFlag == 0)
                                                                        <a  target="_blank" href="{{url('course-details/'. $coursePackage->original_course->slug)}}"
                                                                        class="btn btn-primary">Expired</a>
                                                                    @elseif($expireFlag == 2 || $expireFlag == 1)
                                                                        <a target="_blank" href="{{url('course-view/'. $coursePackage->original_course->slug)}}"
                                                                        class="btn btn-primary">Start Lesson</a>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <h5>No record found!</h5>
                        @endif


                        {{-- @if (isset($courses) && count($courses) > 0)
                            <div class="row">
                                @foreach ($courses as $key => $value)
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
                                    @php
                                        $expireFlag = is_expired($value->expire_at);
                                    @endphp
                                    <div class="col-xl-4 col-lg-4 col-md-6 d-flex dashboard-course" >
                                    <div class="course-box course-design d-flex ">
                                        <div class="product">
                                            <div class="product-img">
                                                @if($value->type == 2)
                                                    <div class="course-package">
                                                        Package
                                                    </div>
                                                @endif
                                                @if($expireFlag == 0)
                                                <a href="javascript:void(0)">
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                </a>
                                                @else
                                                <a
                                                    href="{{ $value->type == 2 ? url('my_package_course/'. $value->userCourse->id) : url('course-preview/'. $value->slug) }}">
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                </a>
                                                @endif
                                            </div>
                                            <div class="product-content">
                                                <h3 class="title">
                                                    @if($expireFlag == 0)
                                                        <a href="javascript:void(0)">{{ $value->title }}</a>
                                                    @else
                                                        <a
                                                        href="{{ $value->type == 2 ? url('my_package_course/'. $value->userCourse->id) : url('course-preview/'. $value->slug) }}">{{ $value->title }}</a>
                                                    @endif
                                                </h3>

                                                <div class="rating-student">
                                                    <div class="rating">
                                                        @for($i=1;$i<=5;$i++)

                                                        <i class="fas fa-star {{$rr_count > 0 && $i <= $rr_count  ? 'filled':''}}"></i>
                                                        @endfor
                                                        <span
                                                            class="d-inline-block average-rating"><span>{{$rr_count}}</span></span>
                                                    </div>
                                                    <div class="edit-rate">
                                                        @php
                                                            $count=$rr_count;
                                                            $text = $count > 0 ? 'Edit' : 'Add';
                                                            $url="javascript:void(0)";
                                                            if($count > 0){
                                                                $url = env('APP_URL').'/course-details/'.$value->slug.'?q='.$value->rr->rating_reviews->pluck('id')->max().'#review';
                                                            }
                                                            else{
                                                                $url = env('APP_URL').'/course-details/'.$value->slug.'#instructor_section';

                                                            }
                                                        @endphp
                                                        <a href="{{ $url }}"> {{ $text }} Review</a>
                                                    </div>
                                                </div>
                                                @if($value->type != 2)
                                                <div class="progress-stip">
                                                    <div
                                                        class="progress-bar bg-success progress-bar-striped active-stip">
                                                    </div>
                                                </div>

                                                <div class="student-percent">
                                                    <p>35% Completed</p>
                                                </div>
                                                @endif

                                                <div class="student-percent valid-till">
                                                    <p><b>Valid Till : </b>
                                                        @if($expireFlag == 0)
                                                            <span class="text-danger">Expired</span>
                                                        @elseif($expireFlag == 1)
                                                            <span style="color:#fd7e14">{{dateFormate($value->expire_at)}}</span>
                                                        @elseif($expireFlag == 2)
                                                            <span class="text-success">LifeTime</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                @if($value->type == 2)
                                                    <div class="start-leason d-flex align-items-center">
                                                            <a href="{{route('my.course', ['packageId' => $value->id])}}"
                                                            class="btn btn-primary">View Course</a>
                                                    </div>
                                                @else
                                                    <div class="start-leason d-flex align-items-center">
                                                        @if($expireFlag == 0)
                                                            <a href="javascript:void(0)"
                                                            class="btn btn-primary">Expired</a>
                                                        @elseif($expireFlag == 2 || $expireFlag == 1)
                                                            <a target="_blank" href="{{url('course-preview/'. $value->slug)}}"
                                                            class="btn btn-primary">Start Lesson</a>
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

						</div> --}}
						<!-- /Instructor Dashboard -->
            </div>
        </div>
    </div>
    <!-- /Dashbord Student -->
    <form action="" method="GET" id="search_frm" style="display:none;">
        <input type="hidden"
            value="{{ isset($_GET['searchTxt']) && $_GET['searchTxt'] != '' ? $_GET['searchTxt'] : '' }}"
            name="searchTxt" id="searchTxt">
        <div class="clear-filter d-flex align-items-center">
            <h4>
                <button type="submit" class="btn btn-primary" id="search_frm_btn">
                    <i class="feather-filter"></i>Filters</button>
            </h4>
            <div class="clear-text">
                <a class="btn btn-warning" href="{{ url('course') }}"
                    style="cursor:pointer;">RESET</a>
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
