@php
    use App\Models\RatingReview;
@endphp
@extends('front.layout.mainlayout')
@section('content')
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
    @php
        $avg_course_rating = $course->AverageRating ?? 0;
        if ($course && isset($course->instructor)) {
            $avg_inst_rating = avg_inst_rating($course->instructor->id);
        }
    @endphp

    @php
        use App\Models\Course;
        use App\Models\User;
        $slug = request()->route('slug');

        $videoId = Course::where('slug', $slug)->first()->videoId;
        $responseObj = getVideoTokenData($videoId);
        $otp = '';
        $playbackInfo = '';
        // dump()
        if (isset($responseObj->otp) != false) {
            $otp = $responseObj->otp;
        }
        if (isset($responseObj->playbackInfo) != false) {
            $otp = $responseObj->playbackInfo;
        }

        $node_servers = env('NODE_SERVER_URL');
    @endphp

    <div class="main-wrapper">

        @component('front.components.breadcrumb')
            @slot('title')
                <a href="{{ url('/') }}">Home</a>
            @endslot
            @slot('li1')
                <a href="{{ url('course') }}">Courses</a>
            @endslot
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
                        <h1 class="hedding-title">{{ $course->title }}</h1>

                        <p class="course_tagline" style="color:white !important;">{!! html_entity_decode($course->tagline, ENT_QUOTES, 'UTF-8') !!}</p>

                        <div class="course-info d-flex align-items-center border-bottom-0 m-0 p-0">
                            <div class="cou-info">
                                <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt="">
                                <p>{{ $course->chapters_count ?? 0 }} Lesson</p>
                            </div>
                            <div class="cou-info">
                                <img src="{{ asset('front/img/icon/icon-19.svg') }}" alt="">
                                <p>{{ $course->lng == 1 ? 'English' : 'Hindi' }}</p>
                            </div>
                            @if ($course->show_learner_cnt == 1)
                                <div class="cou-info">
                                    <img src="{{ asset('front/img/icon/people.svg') }}" alt="">
                                    {{-- <p>{{$course->total_enroll}} Learners Enrolled</p> --}}
                                    @php $collection = collect($course->userCourseCount); @endphp

                                    <p>{{ $collection->sum('user_course_count') }} Learners Enrolled</p>
                                </div>
                            @endif
                            {{-- @if (isset($course->categories) && !empty($course->categories)) --}}
                            <span>
                                @if (
                                    (isset($course->hours) && !empty($course->hours) && $course->hours != '0') ||
                                        (isset($course->minutes) && !empty($value->minutes) && $value->minutes != '0'))
                                    <div class="course-view d-flex align-items-center">
                                        <img src="{{ asset('front/img/icon/icon-02.svg') }}" alt="" />
                                        @if ($course->hours != '0')
                                            <p>{{ $course->hours }}h</p>
                                        @endif
                                        @if ($course->minutes != '0')
                                            <p>{{ $course->minutes }}m</p>
                                        @endif
                                    </div>
                                @endif
                            </span>
                            {{-- @endif --}}
                            <div class="rating mb-0">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star { $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                @endfor
                                <span class="d-inline-block average-rating">
                                    ({{ $total_rating_based_on_course ?? 0 }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="page-content course-sec">
            <div class="container">
                <div class="row aos" data-aos="fade-up">

                    <div class="col-lg-12">
                        <div class="sidebar-sec ">

                            <div class="video-sec vid-bg ">
                                <div class="card ">
                                    <div class="card-body ">

                                        @if (!$course->videoId)
                                            <img src="{{ getImageIfExists($course->image, '') }}"
                                                type="image/{{ pathinfo(isset($course->image) && !empty($course->image) ? Storage::url($course->image) : '', PATHINFO_EXTENSION) }}">
                                        @else
                                            {{-- <video class="video-thumbnail" width="100%" height="auto" controls="controls" poster="{{getImageIfExists($course->image,'')}}" />
                            <source src="{{getImageIfExists($course->intro_video,'')}}" type="video/{{pathinfo(isset($course->intro_video) && !empty($course->intro_video) ? Storage::url($course->intro_video) : '' , PATHINFO_EXTENSION )}}">
                                        </video> --}}


                                            <iframe id="videoPlayer"
                                                src="https://player.vdocipher.com/v2/?otp={{ @$responseObj->otp }}&playbackInfo={{ @$responseObj->playbackInfo }}"
                                                style="border:0;max-width:100%;top:0;left:0;height:350px;width:100%;"
                                                allowfullscreen="true" allow="encrypted-media">
                                            </iframe>
                                        @endif
                                        <div class="video-details ">
                                            @php
                                                $priceArray = [
                                                    'plan_name' => $course->plan_name,
                                                    'planId' => $course->planId,
                                                    'plan_type' => $course->plan_type,
                                                    'list_price' => $course->list_price,
                                                    'final_payable_price' => $course->final_payable_price,
                                                    'courseId' => $course->id,
                                                    'slug' => $course->slug,
                                                    'type' => $course->type,
                                                ];
                                            @endphp
                                            {{ $coursePlan = min_max_price_btn($priceArray, 2) }}
                                            @php
                                                $valid_till = '';
                                                // $date = new DateTime();
                                                // $date1 = new DateTime();
                                                // $currentDate = now();
                                                // dd($course)
                                                if ($course->course_limit == 1) {
                                                    if (
                                                        isset($course->is_fixed_date) &&
                                                        $course->access_value != null
                                                    ) {
                                                        if ($course->is_fixed_date == 2) {
                                                            // add number of days

                                                            $valid_till = $course->access_value;
                                                        }
                                                        if ($course->is_fixed_date == 1) {
                                                            // add expiredate
                                                            // $date->modify('+'.$course->access_value.' days');
                                                            // $expiredAt = $date->format('Y-m-d');

                                                            // $your_date = strtotime($course->access_value);
                                                            // $valid_till = $your_date - time();
                                                            // $valid_till = round($valid_till / (60 * 60 * 24));
                                                            $valid_till = dateFormate($course->access_value);
                                                            // dd($valid_till);
                                                        }
                                                        $days = $valid_till == 1 ? ' Day' : ' Days';
                                                        $valid_till = $valid_till . $days;
                                                    }
                                                } else {
                                                    $valid_till = 'Lifetime';
                                                }

                                            @endphp
                                            @if ($course->plans->count() == 1)
                                                <div class="course-fee"><label for=""
                                                        class="text-center fw-bold">Validity: {{ $valid_till }}</label>
                                                </div>
                                            @endif
                                            {{-- <div class="course-fee ">
                                                @if (count($course->plans) == 1)
                                                    <h2><span style="text-decoration:none;">{{($course->plans->first()->final_payable_price > 0)?"₹".$course->plans->first()->final_payable_price:"Free"}}</span></h2>
                                                    @if ($course->plans->first()->list_price != $course->plans->first()->final_payable_price)
                                                        <p><span>&#8377; {{$course->plans->first()->list_price}}</span>@if ((int) $course->plans->first()->list_price > (int) $course->plans->first()->final_payable_price) @endif</p>
                                                    @endif
                                                @endif
                                        </div> --}}
                                            <div class="price-btn">
                                                @if (!empty($wishlists) && $course->wishlists)
                                                    @php
                                                        $vl = $course->wishlists->filter(function ($value, $key) use (
                                                            $wishlists,
                                                        ) {
                                                            return !empty($wishlists->toArray()) &&
                                                                $wishlists->first()->id == $value->id;
                                                        });
                                                        if ($vl->count() <= 0) {
                                                            $vl = $course->wishlists()->pluck('id')->first();
                                                        } else {
                                                            $vl = $vl->first()->id;
                                                        }

                                                    @endphp

                                                    <a href="javascript:void(0)" class="btn btn-wish w-50 wishlist "
                                                        data-course_id = "{{ $course->id }}"
                                                        data-wishlist_active = "{{ active_wishlist($course->id) == '' ? false : true }}"
                                                        data-wishlist_id = "{{ $vl }}">
                                                        <i class="fa-regular fa-heart {{ active_wishlist($course->id) }}">
                                                        </i>
                                                        {{ active_wishlist($course->id) == '' ? 'Add to Wishlist' : 'Remove from Wishlist' }}</a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-wish w-50 wishlist "
                                                        data-course_id = "{{ $course->id }}" data-wishlist_active=0
                                                        data-wishlist_id=0 style=""><i
                                                            class="fa-regular fa-heart"></i> Add to Wishlist</a>
                                                @endif

                                                <!-- Purchase button -->
                                                {{ check_course_is_free_or_not($priceArray, 3, 'course-details') }}

                                            </div>
                                            @php
                                                $valid_till = '';
                                                $date = new DateTime();
                                                $date1 = new DateTime();
                                                $currentDate = now();
                                                if ($course->course_limit == 1) {
                                                    if (
                                                        isset($course->is_fixed_date) &&
                                                        $course->access_value != null
                                                    ) {
                                                        if ($course->is_fixed_date == 2) {
                                                            // add number of days
                                                            $date->modify('+' . $course->access_value . ' days');
                                                            $expiredAt = $date->format('Y-m-d');
                                                        }
                                                        if ($course->is_fixed_date == 1) {
                                                            // add expiredate
                                                            $expiredAt = $course->access_value;
                                                        }

                                                        $valid_till = date('d-m-Y', strtotime($expiredAt));
                                                    }
                                                } else {
                                                    $valid_till = 'Lifetime Validity Plans';
                                                }

                                            @endphp

                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-lg-12">

                        <div class="card overview-sec">
                            <div class="card-body">
                                <h5 class="subs-title">Description</h5>
                                <p class="course_desc">{!! html_entity_decode($course->description, ENT_QUOTES, 'UTF-8') !!}</p>
                            </div>
                        </div>

                        <div class="card overview-sec">
                            <div class="card-body">
                                <h5 class="subs-title">How To Use</h5>
                                <p class="course_how_to_use">{!! html_entity_decode($course->how_to_use, ENT_QUOTES, 'UTF-8') !!}</p>
                            </div>
                        </div>
                        <div class="card content-sec">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h5 class="subs-title">Course Curriculum</h5>
                                    </div>
                                    {{-- <div class="col-sm-6 text-sm-end">
                                    <h6>{{count($course->chapters)}} Lectures</h6>
                                </div> --}}
                                </div>
                                @php
                                    $cls = '';
                                @endphp
                                @if (count($course->chapters) > 0)
                                    @foreach ($course->chapters as $key => $value)
                                        @if ($value->chapter_items->count() != 0)
                                            @php
                                                $cls = 'add_new';
                                            @endphp
                                        @else
                                            @php
                                                $cls = '';
                                            @endphp
                                        @endif
                                        <div class="course-card">
                                            <h6 class="cou-title">
                                                <a class="collapsed {{ isset($cls) ? $cls : '' }}" data-bs-toggle="collapse"
                                                    href="#collapse{{ $value->id }}"
                                                    aria-expanded="false">{{ Helper::setTypeWiseIcon(trim($value->asset_type)) }}
                                                    {{ $value->title }}</a>
                                            </h6>
                                            <div id="collapse{{ $value->id }}" class="card-collapse collapse"
                                                style="">
                                                {{-- @if ($value->chapter_items->count() > 0)
                                                @endif --}}

                                                <ul>
                                                    @forelse ($value->chapter_items->sortBy('order') as $key1 => $value1)
                                                        @if ($value1->asset_type != App\Models\Chapter::SELL_BUY)
                                                            <li>

                                                                <p>{{ Helper::setTypeWiseIcon(trim($value1->asset_type),1) }}
                                                                {{-- Lecture{{$key1+1}} --}}
                                                                {{ $value1->title }}</p>
                                                                <div>
                                                                    @if ($value1->asset_type == App\Models\Chapter::FLAG_VIDEO)
                                                                        <span>{{ Helper::duration($value1->id) }}</span>
                                                                    @endif
                                                                    @if ($value1->asset_type == App\Models\Chapter::FLAG_AUDIO)
                                                                        <span>{{ Helper::duration($value1->id) }}</span>
                                                                    @endif
                                                                    {{-- @if ($value1->asset_type == App\Models\Chapter::FLAG_PDF)
                                                            <span>{{Helper::countPdfPage($value1->id)}}</span>
                                                            @endif --}}
                                                                    @if ($value1->asset_type == App\Models\Chapter::FLAG_PDF)
                                                                        <span>

                                                                            {{ isset($value1->chapterInfo) && isset($value1->chapterInfo->pdf_page_count) ? round($value1->chapterInfo->pdf_page_count) . ' Pages' : '0  Page' }}</span>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        @endif
                                                    @empty
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div>No Content</div>
                                @endif
                            </div>
                        </div>

                        <div id="top"></div>
                        <div class="card instructor-sec" id="instructor_section">
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
                                            <!-- <i class="fas fa-star filled"></i>
                                        <i class="fas fa-star"></i> -->
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $avg_inst_rating ? 'filled' : '' }}"></i>
                                            @endfor
                                            {{-- <span class="d-inline-block average-rating">{{$avg_inst_rating}}</span> --}}
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
                                    <img src="{{asset('front/img/icon/people.svg')}}"
                                        alt="">
                                    <p>270,866 students enrolled</p>
                                </div> --}}
                                    </div>
                                    <p>{{ $course->instructor->bio ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                        @if (!empty($course->is_purchased))
                            @auth('learner')
                                @php
                                    $review = RatingReview::where('learner_id', @auth()->guard('learner')->user()->id)
                                        ->where('course_id', $course->id)
                                        ->first();

                                        // dd($review);
                                @endphp
                                    @if ($review==null)
                                    <div id="reviews" class="card comment-sec">
                                        <div class="card-body">
                                            @if ($errors->any())
                                                <div class="alert alert-danger text-white d-flex" role="alert">
                                                    <ul class="mb-0">
                                                        @foreach ($errors->all() as $key => $value)
                                                            <li class="float-left" style="color: #FFFFFF;">
                                                                {{ $value }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <form method="POST" action="{{ url('rating_review') }}" id="review_frm">
                                                @csrf
                                                <h5 class="subs-title">Post A Review</h5>
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
                                                                    <button class="btn submit-btn disabled_submit"
                                                                        type="submit">Submit</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="course_id" value="{{ $course->id }}" />
                                                <input type="hidden" name="rating" id="rating_val" value="0" />


                                            </form>
                                        </div>
                                    @endif
                                    </div>
                                    <div id="reviews" class="card comment-sec update_review" style="display:none">
                                    <div class="card-body " >
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
                                                                    required pattern=["/^\s+$/g"]></textarea>
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
                        <div class="card review-sec" id="reviews">

                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <h5 class="subs-title">Reviews</h5>
                                    </div>
                                    @if (count($course->rating_reviews) > 0)
                                    <div class="col-md-3">
                                        <label for="sortOption">Sort By:</label>
                                        <select id="sortOption" class="form-control">
                                            <option value="mostrecent"
                                                {{ $sortOption === 'mostrecent' ? 'selected' : '' }}>Most Recent</option>
                                            <option value="higheststar"
                                                {{ $sortOption === 'higheststar' ? 'selected' : '' }}>Highest</option>
                                            <option value="loweststar"
                                                {{ $sortOption === 'loweststar' ? 'selected' : '' }}>Lowest</option>
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                @if (count($course->rating_reviews) > 0)
                                    @foreach ($course->rating_reviews as $key => $value)
                                        {{-- {{dd($value)}} --}}
                                        <div class="review-parent">
                                            <div class="instructor-wrap">
                                                <div class="about-instructor">
                                                    <div class="abt-instructor-img">
                                                        <img src="{{ getImageIfExists($value->learner?->profile_pic, user_img_default()) }}"
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
                                                        {{-- <span class="d-inline-block average-rating">{{$value->rating}} </span> --}}
                                                    </div>
                                                @endif

                                            </div>
                                            <div class="justify-content-between align-items-center">
                                                <p class="rev-info col-md-12" style="margin: 0;"><q class="comment"
                                                        style="margin: 0;">{!! $value->comment !!}</q></p>
                                                <p class="formatted_date" style="margin: 0;text-align:right;">
                                                    {{ date('jS M Y h:i A', strtotime($value->created_at)) }}</p>
                                            </div>

                                            @if ($value->learner_id == @Auth::guard('learner')->user()->id)
                                                <div class="d-flex review-btn">
                                                    <a class="btn btn-primary updateReview mx-2"
                                                        data-value="{{ $value->id }}"><i
                                                            class="fa-solid fa-pen-to-square"></i></a>
                                                    <a href="javascript:void(0)" class="btn btn-danger deleteReview mx-2"
                                                        data-value="{{ $value->id }}"
                                                        onclick="delete_review({{ $value->id }})"><i
                                                            class="fa-solid fa-trash"></i></a>
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
        </section>
    @endsection
    @section('js')
        @php $ratingjs=true; @endphp
        <script>
            $(document).ready(function() {
                $("#sortOption").change(function() {
                    var selectedOption = $(this).val();
                    var currentUrl = window.location.href;
                    var baseUrl = currentUrl.split('?')[0];
                    var newUrl = baseUrl + '?sort_option=' + selectedOption;
                    window.location.href = newUrl;
                });
            });
            var id = parseInt("{{ $course->rating_reviews->pluck('id')->min() }}");
            var total_ratings = "{{ $course->rr_count }}";

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
                $("#review").attr("style", "display: block !important");
            });

            $(document).on("submit", "#review_frm", function(event) {

                $("#rating_err").remove();
                $("#my_text_area_err").remove();
                $(".disabled_submit").attr("disabled","disabled")
                let x = parseInt($("#rating_val").val());
                if ($.trim($('#myTextArea').val()) == '') {
                    event.preventDefault();
                    $("<p style='color: red' id='my_text_area_err'>Please provide valid comments</p>").insertAfter(
                        "#myTextArea");
                    $(".disabled_submit").removeAttr("disabled");
                } else if (x <= 0) {
                    event.preventDefault();
                    $("#rating").append("<p style='color: red' id='rating_err'>Rating is required</p>");
                    $(".disabled_submit").removeAttr("disabled");
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
                    location.href = "#";
                    location.href = "#top";
                    $("#updatereview_form").show();
                    $(".update_review").show(   )
                    $("#review_frm").hide();
                    edit_review($(this));

                });

            });

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
                    console.log(self, x, y);
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

            function delete_review(id) {
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
                        $("#deletereview_form").attr("action", "{{ env('APP_URL') }}" + '/rating_review/' + id);
                        $("#delete_rev_btn").trigger('click');
                    }
                });
            }

            function updatefrm() {
                location.href = "#";
                location.href = "#updatereview_form";
            }
        </script>
    @endsection
