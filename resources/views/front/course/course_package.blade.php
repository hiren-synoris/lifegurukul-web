    @extends('front.layout.mainlayout')
    @php
    use App\Models\RatingReview;
@endphp
@push('meta')
    <meta name="title" content="{{ isset($course->meta_title) && !empty($course->meta_title) ? $course->meta_title : '' }}">
    <meta name="description"
        content="{{ isset($course->meta_description) && !empty($course->meta_description) ? $course->meta_description : '' }}">
    <meta name="keywords"
        content="{{ isset($course->meta_keywords) && !empty($course->meta_keywords) ? $course->meta_keywords : '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $course->meta_title ?: $course->title }}">
    <meta property="og:description"
        content="{{ isset($course->meta_description) && !empty($course->meta_description) ? $course->meta_description : '' }}">
    <meta property="og:locale" content="{{ app()->getLocale() }}">
@endpush
@section('content')
    @php
        $avg_course_rating = $course->AverageRating ?? 0;
        if ($course && isset($course->instructor)) {
            $avg_inst_rating = avg_inst_rating($course->instructor->id);
        }

        use App\Models\Course;
        $slug = request()->route("slug");

        $videoId = Course::where("slug",$slug)->first()->videoId;
        $responseObj = getVideoTokenData($videoId);
        $otp ="";
        $playbackInfo ="";
        // dump()
        if(isset($responseObj->otp) != false) {
            $otp = $responseObj->otp;
        }
        if(isset($responseObj->playbackInfo) != false) {
            $otp = $responseObj->playbackInfo;
        }

        $node_servers = env('NODE_SERVER_URL');
    @endphp
    <div class="main-wrapper">
        @component('front.components.breadcrumb')
            @slot('title')
                <a href="{{ url('/') }}">Home</a>
            @endslot
            {{-- @slot('li1') <a href="{{url('course')}}">Courses</a> @endslot --}}
            @slot('li2')
                {{ isset($course) && !empty($course->title) ? $course->title : '' }}
            @endslot
        @endcomponent
        {{-- <a href="" style="dispay:none;" id="checkout"></a> --}}
        <div class="inner-banner"
            style="background:url('{{ getImageIfExists($course->banner_image, URL::asset('/front/img/booking.jpg')) }}')">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h1>{{ $course->title }}</h1>
                        <p class="course_tagline" style="color:white !important;">{!! html_entity_decode($course->tagline, ENT_QUOTES, 'UTF-8') !!}</p>

                        <div class="course-info d-flex align-items-center border-bottom-0 m-0 p-0">
                            <div class="cou-info">
                                <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt="">
                                <p>{{ $course->packages_count ?? 0 }} Courses</p>
                            </div>
                            <div class="cou-info">
                            <img src="{{asset('front/img/icon/icon-19.svg')}}" alt="">
                            <p>{{($course->lng == 1)?"English":"Hindi"}}</p>
                        </div>
                            @if ($course->show_learner_cnt == 1)
                                <div class="cou-info">
                                    <img src="{{ asset('front/img/icon/people.svg') }}" alt="">
                                    {{-- <p>{{ $course->total_enroll }} Learners Enrolled</p> --}}
                                    @php $collection = collect($course->userCourseCount); @endphp
                                    <p>{{ $collection->sum('user_course_count')}} Learners Enrolled</p>
                                </div>
                            @endif
                            @if (isset($course->categories) && !empty($course->categories))
                                <span >
                                    @if(isset($course->hours) && !empty($course->hours) && $course->hours != '0' || isset($course->minutes) && !empty($value->minutes) && $value->minutes != '0')
                                        <div class="course-view d-flex align-items-center">
                                            <img src="{{asset('front/img/icon/icon-02.svg')}}" alt="" />
                                            @if($course->hours != '')
                                                <p>{{ $course->hours }}h</p>
                                            @endif
                                            @if($course->minutes != '')
                                                <p>{{ $course->minutes }}m</p>
                                            @endif
                                        </div>
                                    @endif
                                </span>
                            @endif
                            <div class="rating mb-0">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                @endfor
                                <span class="d-inline-block average-rating">
                                    ({{ $course->total_review ?? 0 }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="page-content course-sec">
            <div class="container">
                <div class="row">


                    <!-- old -->

                    <div class="col-lg-12">
                        <div class="sidebar-sec ">

                            <div class="video-sec vid-bg ">
                                <div class="card ">
                                    <div class="card-body ">

                                        @if($course->videoId!='')
{{--
                                            <video class="video-thumbnail" width="100%" height="auto" controls="controls"
                                                poster="{{ getImageIfExists($course->image, '') }}" />
                                                    <source src="{{ getImageIfExists($course->intro_video, '') }}"
                                                    type="video/{{ pathinfo(isset($course->intro_video) && !empty($course->intro_video) ? Storage::url($course->intro_video) : '', PATHINFO_EXTENSION) }}">
                                            </video> --}}
                                        <iframe id="videoPlayer"
                                            src="https://player.vdocipher.com/v2/?otp={{ @$responseObj->otp }}&playbackInfo={{ @$responseObj->playbackInfo }}"
                                            style="border:0;max-width:100%;top:0;left:0;height:350px;width:100%;" allowfullscreen="true"
                                            allow="encrypted-media">
                                        </iframe>
                                        @else
                                        <img src="{{ getImageIfExists($course->image, '') }}" class="video-thumbnail" width="100%" height="auto" controls="controls"/>
                                        @endif
                                        <div class="video-details ">
                                            @php
                                                $priceArray = ['plan_name' => $course->plan_name, 'planId' => $course->planId, 'plan_type' => $course->plan_type, 'list_price' => $course->list_price, 'final_payable_price' => $course->final_payable_price, 'courseId' => $course->id, 'slug' => $course->slug, 'type' => $course->type];
                                            @endphp
                                            {{ $coursePlan = min_max_price_btn($priceArray, 2) }}
                                            @php
                                            $valid_till = '';
                                            // $date = new DateTime();
                                            // $date1 = new DateTime();
                                            // $currentDate = now();
                                            // dd($course->toArray());
                                            if($course->course_limit == 1)
                                            {
                                                if (isset($course->is_fixed_date) && $course->access_value != NULL) {
                                                    if ($course->is_fixed_date == 2) { // add number of days

                                                        $valid_till = $course->access_value;
                                                    }
                                                    if ($course->is_fixed_date == 1) { // add expiredate
                                                        // $date->modify('+'.$course->access_value.' days');
                                                        // $expiredAt = $date->format('Y-m-d');

                                                        // $your_date = strtotime($course->access_value);
                                                        // $valid_till = $your_date - time();
                                                        // $valid_till = round($valid_till / (60 * 60 * 24));
                                                        $valid_till = dateFormate($course->access_value);
                                                    }
                                                    $days = $valid_till==1 ? ' Day' : ' Days';
                                                    $valid_till = $valid_till. $days;
                                                }
                                            }else{
                                                $valid_till = "Lifetime";
                                            }
                                            @endphp
                                            @if($course->plans->count() == 1)
                                                <div class="course-fee"><label for="" class="text-center fw-bold">Validity: {{ $valid_till }}</label></div>
                                            @endif
                                            <div class="price-btn">
                                                @if (!empty($wishlists) && $course->wishlists)
                                                    @php
                                                        $vl = $course->wishlists->filter(function ($value, $key) use ($wishlists) {
                                                            return !empty($wishlists->toArray()) && $wishlists->first()->id == $value->id;
                                                        });
                                                        if ($vl->count() <= 0) {
                                                            $vl = $course
                                                                ->wishlists()
                                                                ->pluck('id')
                                                                ->first();
                                                        } else {
                                                            $vl = $vl->first()->id;
                                                        }
                                                    @endphp
                                                    <a href="javascript:void(0)" class="btn btn-wish w-50 wishlist"
                                                        data-course_id="{{ $course->id }}"
                                                        data-wishlist_active="{{ active_wishlist($course->id) == '' ? false : true }}"
                                                        data-wishlist_id="{{ $vl }}">
                                                        <i
                                                            class="fa-regular fa-heart {{ active_wishlist($course->id) }}"></i>
                                                        {{ active_wishlist($course->id) == '' ? 'Add to Wishlist' : 'Remove from Wishlist' }}</a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-wish w-50 wishlist"
                                                        data-course_id="{{ $course->id }}" data-wishlist_active=0
                                                        data-wishlist_id=0>
                                                        <i class="fa-regular fa-heart"></i> Add to Wishlist</a>
                                                @endif

                                                <!-- Purchase button -->
                                                {{ check_course_is_free_or_not($priceArray, 3) }}

                                                {{-- @if ($course->plans->count() > 0)
                                            <a href="javascript:void(0)" class="btn btn-enroll w-50" data-bs-toggle="modal" data-bs-target="#price_modal">Enroll Now</a>
                                        @else
                                            <a href="javascript:void(0)" class="btn btn-enroll w-50">Enroll Now</a>
                                        @endif --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-lg-12">

                        <div class="card overview-sec aos" data-aos="fade-up">
                            <div class="card-body">
                                <h5 class="subs-title">Description</h5>
                                <p class="course_desc">{!! html_entity_decode($course->description, ENT_QUOTES, 'UTF-8') !!}</p>
                            </div>
                        </div>

                        <div class="card overview-sec aos" data-aos="fade-up">
                            <div class="card-body">
                                <h5 class="subs-title">How To Use</h5>
                                <p class="course_how_to_use">{!! html_entity_decode($course->how_to_use, ENT_QUOTES, 'UTF-8') !!}</p>
                            </div>
                        </div>
                        <div class="card overview-sec aos" data-aos="fade-up">
                            <div class="card-body">
                                <h5 class="subs-title">Courses</h5>

                                @if (count($course->packages) > 0)
                                    @foreach ($course->packages as $key => $value)
                                        @if (isset($value->package))
                                            @php
                                                $avg_course_rating = $value->AverageRating ?? 0;
                                            @endphp
                                            <!-- new -->
                                            {{-- @if (isset($value->plans) && $value->plans->count() > 0) --}}
                                            <div class="col-lg-12 col-md-12 d-flex">
                                                <div class="course-box course-design list-course d-flex">
                                                    <div class="product">
                                                        <div class="product-img">
                                                            <a
                                                                href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->package->slug) }}">
                                                                <img class="img-fluid" alt=""
                                                                    src="{{ getImageIfExists($value->package->image, course_img_default()) }}">
                                                            </a>
                                                            @php
                                                                $packagePriceArray = ['plan_name' => $value->package->plan_name, 'planId' => $value->package->planId, 'plan_type' => $value->package->plan_type, 'list_price' => $value->package->list_price, 'final_payable_price' => $value->package->final_payable_price];
                                                            @endphp
                                                            {{ $coursePackagePlan = min_max_price_btn($packagePriceArray, 1) }}
                                                        </div>
                                                        <div class="product-content">
                                                            <div class="head-course-title">
                                                                <h3 class="title"><a
                                                                        href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->package->slug) }}">{{ $value->package->title }}</a>
                                                                </h3>
                                                            </div>
                                                            <div
                                                                class="course-info border-bottom-0 pb-0 d-flex align-items-center">
                                                                <div class="rating-img d-flex align-items-center">
                                                                    <img src="{{ asset('front/img/icon/icon-01.svg') }}"
                                                                        alt="">
                                                                    <p>{{ $value->packages_count ?? 0 }}+ Course
                                                                    </p>
                                                                </div>
                                                                <div class="course-view d-flex align-items-center">
                                                                    <img src="{{ asset('front/img/icon/icon-19.svg') }}"
                                                                        alt="">
                                                                    <p>{{ $value->lng == 1 ? 'English' : 'Hindi' }}
                                                                    </p>
                                                                </div>

                                                                <div class="course-view d-flex align-items-center">
                                                                    <img src="{{ asset('front/img/icon/icon-02.svg') }}"
                                                                        alt="" />
                                                                    @if ($value->package->hours != '0')
                                                                        <p>{{ $value->package->hours }}h</p>
                                                                    @endif
                                                                    @if ($value->package->minutes != '0')
                                                                        <p>{{ $value->package->minutes }}m</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="rating">
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    <i
                                                                        class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                                                @endfor
                                                                <span class="d-inline-block average-rating">
                                                                    ({{ $value->total_review ?? 0 }})
                                                                </span>

                                                            </div>
                                                            <div class="course-instructor d-flex mb-0">
                                                                @if ($value->package->instructor && !empty($value->package->instructor) && isset($value->package->instructor->id))
                                                                    <div class="course-group-img d-flex">
                                                                        <a
                                                                            href="{{ url('instructor/' . $value->package->instructor->id) }}"><img
                                                                                src="{{ getImageIfExists($value->package->instructor->profile_picture, user_img_default()) }}"
                                                                                alt="" class="img-fluid"></a>
                                                                        <div class="course-name">
                                                                            <h4><a
                                                                                    href="{{ url('instructor/' . $value->package->instructor->id) }}">{{ $value->package->instructor->name }}</a>
                                                                            </h4>
                                                                            <p>{{ $value->package->instructor->instructure->designation ?? '' }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- old -->
                                            {{-- @endif --}}
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div id="top"></div>
                        <div class="card instructor-sec aos" data-aos="fade-up" id="instructor_section">
                            <div class="card-body">
                                @if ($course && isset($course->instructor))
                                    <h5 class="subs-title">About the instructor</h5>
                                @endif
                                @if ($course->instructor && !empty($course->instructor) && isset($course->instructor->id))
                                    <div class="instructor-wrap">
                                        <div class="about-instructor">
                                            <div class="abt-instructor-img">
                                                <a href="{{ url('instructor/' . $course->instructor->id) }}"><img
                                                        src="{{ getImageIfExists($course->instructor->profile_picture, user_img_default()) }}"
                                                        alt="img" class="img-fluid"></a>
                                            </div>
                                            <div class="instructor-detail">
                                                <h5><a
                                                        href="{{ url('instructor/' . $course->instructor->id) }}">{{ $course->instructor->name ?? '' }}</a>
                                                </h5>
                                                <p>{{ $course->instructor->instructure->designation ?? '' }}</p>
                                            </div>
                                        </div>
                                        <div class="rating">
                                            {{-- <i class="fas fa-star filled"></i>
                        <i class="fas fa-star"></i> --}}
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $avg_inst_rating ? 'filled' : '' }}"></i>
                                            @endfor
                                            {{-- <span class="d-inline-block average-rating">{{ $avg_inst_rating }} Instructor
                                                Rating</span> --}}
                                            <span class="d-inline-block average-rating">{{$avg_inst_rating }}</span>
                                        </div>
                                    </div>
                                    <div class="course-info d-flex align-items-center">
                                        <div class="cou-info">
                                            <img src="{{ asset('front/img/icon/play.svg') }}" alt="">
                                            <p>{{ $course->inst_total_course ?? '' }} Courses</p>
                                        </div>
                                        {{-- <div class="cou-info">
                                    <img src="{{asset('front/img/icon/icon-01.svg')}}"
                                        alt="">
                                    <p>12+ Lesson</p>
                                </div> --}}
                                        {{-- <div class="cou-info">
                                    <img src="{{asset('front/img/icon/icon-02.svg')}}"
                                        alt="">
                                    <p>9hr 30min</p>
                                </div> --}}
                                        {{-- <div class="cou-info">
                        <img src="{{ asset('front/img/icon/people.svg') }}" alt="">
                        <p>270,866 students enrolled</p>
                    </div> --}}
                                    </div>
                                    <p>{{ $course->instructor->bio ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                        {{-- <div class="card review-sec">
                        <div class="card-body">
                            <h5 class="subs-title">Reviews</h5>
                            <div class="instructor-wrap">
                                <div class="about-instructor">
                                    <div class="abt-instructor-img">
                                        <a href="{{url('instructor/'.$course->instructor->id)}}"><img
                                                src="{{!empty($course->instructor->profile_picture) ? Helper::asseturl($course->instructor->profile_picture,true) : user_img_default()}}"
                                                alt="img" class="img-fluid"></a>
                                    </div>
                                    <div class="instructor-detail">
                                        <h5><a
                                                href="{{url('instructor/'.$course->instructor->id)}}">{{$course->instructor->name??''}}</a></h5>
                                        <p>{{$course->instructor->designation??''}}</p>
                                    </div>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star filled"></i>
                                    <i class="fas fa-star"></i>
                                    <span class="d-inline-block average-rating">4.5 Instructor Rating</span>
                                </div>
                            </div>
                            <p class="rev-info">“ This is the second Photoshop course I have completed with Cristian.
                                Worth every penny and recommend it highly. To get the most out of this course, its best
                                to to take the Beginner to Advanced course first. The sound and video quality is of a
                                good standard. Thank you Cristian. “</p>
                            <a href="javascript:;" class="btn btn-reply"><i class="feather-corner-up-left"></i>
                                Reply</a>
                        </div>
                    </div> --}}

                        @if (!empty($course->is_purchased))
                            @auth('learner')

                            @php
                            $review = RatingReview::where('learner_id', @auth()->guard('learner')->user()->id)
                                ->where('course_id', $course->id)
                                ->first();

                                        // dd($review);
                                @endphp
                                    @if ($review==null)

                                <div id="review" class="card comment-sec"name="">
                                    <div class="card-body">
                                        @if ($errors->any())
                                            <div class="alert alert-danger text-white d-flex" role="alert">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $key => $value)
                                                        <li class="float-left" style="color: #FFFFFF;">{{ $value }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form method="POST" action="{{ url('rating_review') }}" id="review_frm">
                                            @csrf
                                            <h5 class="subs-title">Post A Review</h5>
                                            <div class="row">
                                                <div class="col-2">
                                                    <img class="img-fluid rounded-circle"
                                                        src="{{ getImageIfExists(Auth::guard('learner')->user()->profile_pic, user_img_default()) }}"
                                                        alt="">
                                                </div>
                                                <div class="col-10">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <textarea rows="4" class="form-control" id="myTextArea" name="comment" placeholder="Your Reviews" required
                                                                    pattern=["/^\s+$/g"]></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div id="rating" name="rating"></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="submit-section float-end">
                                                                <button class="btn submit-btn" type="submit">Submit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="course_id" value="{{ $course->id }}" />
                                            <input type="hidden" name="rating" id="rating_val" value="0" />


                                        </form>


                                        <form method="POST" action="" id="deletereview_form" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="submit" id="delete_rev_btn">
                                        </form>
                                    </div>
                                </div>
                                @endif
                                <div id="review" class="card comment-sec update_review"name="" style="display:none">
                                    <div class="card-body">
                                        @if ($errors->any())
                                            <div class="alert alert-danger text-white d-flex" role="alert">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $key => $value)
                                                        <li class="float-left" style="color: #FFFFFF;">{{ $value }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form method="POST" action="" id="updatereview_form" style="display:none;">
                                            @method('PUT')
                                            @csrf
                                            <h5 class="subs-title">Update A Review</h5>
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <img class="img-fluid rounded-circle"
                                                        src="{{ getImageIfExists(Auth::guard('learner')->user()->profile_pic, user_img_default()) }}"
                                                        alt="">
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <textarea rows="4" class="form-control" id="update_comment" name="comment" placeholder="Your Reviews"
                                                                    pattern=["/^\s+$/g"]></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div id="update_rating" name="update_rating"></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="submit-section float-end">
                                                                <button class="btn submit-btn" type="submit">Update</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="update_rating" id="update_rating_val"
                                                value="0" />
                                            {{-- <input type="hidden" name="course_id" value="{{$course->id}}" />
                                    <input type="hidden" name="rating" id="rating_val" value="0"/> --}}


                                        </form>

                                        <form method="POST" action="" id="deletereview_form" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="submit" id="delete_rev_btn">
                                        </form>
                                    </div>
                                </div>

                            @endauth
                        @endif
                        <div id="review" class="card review-sec"   >
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <h5 class="subs-title">Reviews</h5>
                                    </div>
                                    @if (count($course->rating_reviews) > 0)
                                    <div class="col-md-3">
                                        <label for="sortOption">Sort By:</label>
                                        <select id="sortOption" class="form-control">
                                            <option value="mostrecent" {{ $sortOption === 'mostrecent' ? 'selected' : '' }}>Most Recent</option>
                                            <option value="higheststar" {{ $sortOption === 'higheststar' ? 'selected' : '' }}>Highest</option>
                                            <option value="loweststar" {{ $sortOption === 'loweststar' ? 'selected' : '' }}>Lowest</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                {{-- <h5 class="subs-title">Reviews</h5> --}}
                                @if (count($course->rating_reviews) > 0)
                                    @foreach ($course->rating_reviews as $key => $value)
                                        <div class="review-parent">
                                            <div class="instructor-wrap mt-5">
                                                <div class="about-instructor">
                                                    <div class="abt-instructor-img">
                                                        <img src="{{ getImageIfExists($value->learner->profile_pic, user_img_default()) }}"
                                                            alt="img" class="img-fluid">
                                                    </div>
                                                    <div class="instructor-detail">
                                                        <h5>{{ $value->learner->name ?? '' }}</h5>
                                                    </div>
                                                    <small class="badge badge-success ms-2"> <i
                                                            class="fa-solid fa-check"></i> Verified</small>
                                                </div>

                                                @if ((int) $value->rating > 0)
                                                    <div class="rating">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="fas fa-star {{ $i <= (int) $value->rating ? 'filled' : '' }}"></i>
                                                        @endfor
                                                        <span
                                                            class="d-inline-block average-rating">{{ $value->rating }}</span>
                                                    </div>
                                                @endif

                                            </div>
                                            <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center;">
                                                <p class="rev-info" style="margin: 0;"><q class="comment" style="margin: 0;">{!! $value->comment !!}</q></p>
                                                <p class="formatted_date" style="margin: 0;">{{ date('jS M Y h:iA', strtotime($value->created_at)) }}</p>
                                            </div>
                                            @if ($value->learner_id == @Auth::guard('learner')->user()->id)
                                                <div class="d-flex review-btn">
                                                    <a class="btn btn-primary updateReview mx-2"
                                                        data-value="{{ $value->id }}"><i class="fa-solid fa-pen-to-square"></i></a>
                                                    <a href="javascript:void(0)"
                                                        class="btn btn-danger deleteReview mx-2"
                                                        data-value="{{ $value->id }}"
                                                        onclick='delete_review("{{ $value->id }}")'><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    <div class="pagination justify-content-center">
                                        {{ $course->rating_reviews->links() }}
                                    </div>
                                @else
                                    <p>No Reviews</p>
                                @endif
                                @if ($course->rr_count > 5)
                                    <button class="btn btn-primary" type="button" id="load_reviews">LOAD MORE
                                        REVIEWS</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    </div>
    </div>
    </div>
    </div>
    </section>
@endsection
@section('js')
    @php $ratingjs=true; @endphp
    <script>
        var id = parseInt("{{ $course->rating_reviews->pluck('id')->min() }}");
        var total_ratings = "{{ $course->rr_count }}";
        $(document).ready(function() {
            $("#sortOption").change(function() {
                var selectedOption = $(this).val();
            var currentUrl = window.location.href;
            var baseUrl = currentUrl.split('?')[0];
            var newUrl = baseUrl + '?sort_option=' + selectedOption;
            window.location.href = newUrl;
            });
        });

        $("#rating").rating({
            "stars": 5,
            "click": function(e) {
                $('#rating_val').val(e.stars);
            }
        });
        // $("#instructor_rating").rating({
        //     "stars": parseInt(instr_rating),
        //     'half': true,
        // });
        // $('#comment').hide();
        $("#reply").click(function() {
            $("#review").attr("style", "display: block !important");;
        });
        // $(document).on("click", ".plan_row", function(){
        //     let temp = $(this).find(".col-6").eq(1).text();
        //     $("#buy_course").text("Buy Course for "+temp);
        // });
        $(document).on("submit", "#review_frm", function(event) {
            $("#rating_err").remove();
            $("#my_text_area_err").remove();
            let x = parseInt($("#rating_val").val());
            if ($.trim($('#myTextArea').val()) == '') {
                event.preventDefault();
                $("<p style='color: red' id='my_text_area_err'>Please provide valid comments</p>").insertAfter(
                    "#myTextArea")
            } else if (x <= 0) {
                event.preventDefault();
                $("#rating").append("<p style='color: red' id='rating_err'>Rating is required</p>")
            } else {
                $("#rating_err").remove();
            }
        });

        $(document).on("click", ".plan_row", function() {
            let temp = $(this).find(".col-6").eq(1).text();
            $("#buy_course").val("Buy Course for " + temp);
        });

        $(document).on("click", "#load_reviews", function() {
            let origin = "{{ env('APP_URL') }}";
            let y = [];
            if (id != null && id != undefined && id != 0 && id != "") {
                $.ajax({
                    url: "{{ env('APP_URL') }}" + '/load_reviews/' + id,
                    type: "GET",
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(data) {
                        $('#loader_section').hide();
                        if (data && data.length > 0) {
                            $(data).each(function(key, value) {
                                y.push(value.id);
                                if (!value.learner.profile_pic.includes(origin)) {
                                    if (!value.learner.profile_pic.includes('front')) {
                                        value.learner.profile_pic = (origin + '/storage/' +
                                                value.learner.profile_pic).replace('//', '/')
                                            .replace(':/', '://');
                                    }

                                }
                                let total_star = parseInt(value.rating);
                                let start_html = "";
                                if (total_star > 0) {
                                    for (let index = 1; index <= 5; index++) {
                                        if (index <= total_star) {
                                            start_html += "<i class='fas fa-star filled'></i>";
                                        } else {
                                            start_html += "<i class='fas fa-star'></i>";
                                        }
                                    }
                                }
                                let temp =
                                    '<div class="instructor-wrap"><div class="about-instructor"><div class="abt-instructor-img"><img src="' +
                                    value.learner.profile_pic +
                                    '"alt="img" class="img-fluid"></div><div class="instructor-detail"><h5>' +
                                    value.learner.name +
                                    '</h5></div></div><div class="rating">' + start_html + ' ' +
                                    value.rating + ' ' +
                                    '<span class="d-inline-block average-rating"></span></div></div><p class="rev-info">' +
                                    value.comment + '</p>';
                                $(temp).insertAfter($("#reviews").find('.card-body').find(
                                    'p:last'));
                            });
                            if (y.length > 0) {
                                id = Math.min(...y);
                            }
                            if (id <= 1) {
                                $("#load_reviews").remove();
                            }
                        }
                    },
                    error: function(jqxhr) {
                        console.log(jqxhr.status);
                    }
                });

            }
        });
        $(document).ready(function() {
            var instr_rating = parseInt("{{ $course->inst_rating }}");
            if (instr_rating && instr_rating > 0) {
                $("#instructor_rating").find('i').slice(0, instr_rating).addClass('filled');
                // console.log($("#instructor_rating").children());
            }

            if ("{{ request()->has('q') }}" == true) {
                if ("{{ request()->q }}" != "" && "{{ request()->q }}" != undefined) {
                    edit_review(null, parseInt("{{ request()->q }}"));
                }
            }

            $(".updateReview").click(function(e) {
                edit_review($(this));
                location.href = "#";
                location.href = "#top";
                $(".update_review").show()
            });

        });

        function delete_review(id) {
            id = parseInt(id);
            // $("#deletereview_form").attr("action", "{{ env('APP_URL') }}" + '/rating_review/' + id);
            // $("#delete_rev_btn").trigger('click');
            Swal.fire({
                        title: 'Are you sure want to delete?',
                        // text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $("#deletereview_form").attr("action", "{{ env('APP_URL') }}"+'/rating_review/'+id);
                            $("#delete_rev_btn").trigger('click');
                        }
                    });
        }

        function edit_review(self = null, id = null) {
            let x, y = null;
            if (id == null) {
                id = self.attr('data-value');
            }
            var token = $('meta[name="csrf-token"]').attr('content');
            let url = "{{ env('APP_URL') }}" + '/rating_review/' + id;
            $("#updatereview_form").attr('action', url);

            if (self != null) {
                x = self.parents().closest(".review-parent").find(".comment").text().trim();
                y = self.parents().closest(".review-parent").find(".rating").find(".filled").length;
            } else {
                let u = $('.review-parent').filter((index, element) => {
                    return id == parseInt($(element).find('.updateReview').data('value'));
                });
                x = $(u).find(".comment").text().trim();
                y = $(u).find(".rating").find(".filled").length;
            }
            $(".updateReview").on("click", function(e) {
                    e.preventDefault();
                    $("#updatereview_form").show();
                    $("#review_frm").hide();
                });
            $("#update_comment").val(x);

            $("#update_rating").rating({
                "stars": 5,
                "value": parseInt(y),
                "click": function(e) {
                    $('#update_rating_val').val(e.stars);
                }
            });
        }
    </script>
@endsection
