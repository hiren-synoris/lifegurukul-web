@php
    use App\Models\SellCourseTimer;
    use App\Models\Course;
    use App\Models\Chapter;
@endphp
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .course-design {
        border: 1px solid #e9ecef;
        width: 100%;
    }

    .product {
        border-radius: 10px;
        position: relative;
        padding: 20px;
        transition: all 0.5s ease;
        -moz-transition: all 0.5s ease;
        -o-transition: all 0.5s ease;
        -ms-transition: all 0.5s ease;
        -webkit-transition: all 0.5s ease;
        background: #fff;
        backdrop-filter: blur(17px);
        -webkit-backdrop-filter: blur(17px);
    }


    .list-course .product {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        align-items: center;
    }

    .list-course .product-img {
        -ms-flex: 0 0 240px;
        flex: 0 0 240px;
        margin-right: 24px;
        width: 240px;
        position: relative;
    }

    .head-course-title {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .head-course-title .title {
        margin-bottom: 0;
        margin-right: 20px;
    }

    .list-course .course-view {
        margin-left: 30px;
    }

    .list-course .product-content {
        padding-top: 0;
    }

    .rating i {
        color: #777777;
    }

    .rating i.filled {
        color: #FFB54A;
    }

    .product-content h3 a,
    .course-name h4 a {
        color: #000000;
        text-transform: capitalize;
    }

    .product-content-title {
        max-width: 320px;
    }

    .course-price .price {
        position: relative;
        top: auto;
        right: auto;
        padding: 0;
        bottom: auto;
        margin-left: auto;
        background: transparent;
    }

    .product.trend-product {
        border: 1px solid #eee;
    }

    .course-box .product:hover .product-content .course-price .price h3,
    .course-box .product:hover .product-content .course-price .price h3 span,
    .course-box .product:hover .product-content .course-group .course-name .wishlist i {
        color: #fff;
    }

    .product.trend-product:hover {
        border: 1px solid #64A846;
    }

    .feature-icon img {
        border-radius: 100%;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .header-navbar-rht .nav-item.wish-nav a {
        width: 40px;
        height: 40px;
        background: #fde3d3;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 40px;
    }

    .blog-content p,
    .blog-content h4 {
        background-color: transparent !important;
    }

    .course-design .course-instructor {
        border-bottom: none;
    }

    .course-design .course-package,
    .course-package {
        position: absolute;
        z-index: 99;
        top: 8px;
        left: 8px;
        background: #65a847;
        padding: 2px 10px;
        border-radius: 100px;
        color: #fff;
        font-size: 12px;
        font-weight: 500;
    }

    .course-group-img img {
        max-width: 35px;
        height: 35px;
        border-radius: 25px;
        margin-right: 10px;
    }

    .course-design .price {
        min-width: auto;
        padding: 8px 15px;
        text-align: center;
    }

    .price {
        min-width: auto;
        padding: 7px 12px;
        background: #fff;
        position: absolute;
        bottom: 20px;
        right: 20px;
        border-radius: 10px;
    }

    .all-category .btn-primary {
        color: #67a94a;
        border: 3px solid #92C27D;
        backdrop-filter: blur(151.39px);
        border-radius: 46.9159px;
        background: transparent;
        border-radius: 46.9159px;
        min-width: 150px;
        padding: 10px 15px;
        font-weight: 500;
    }

    .course-info p {
        margin-bottom: 0;
        color: #685F78;
        font-size: 16px;
        margin-left: 8px;
    }

    .course-design .course-info p {
        font-size: 15px;
    }

    .head-course-title .title {
        margin-bottom: 0;
        margin-right: 20px;
    }

    .product-content h3 {
        font-size: 18px;
        color: #000000;
        font-weight: 500;
        line-height: 1.3;
    }

    .course-name h4 {
        font-size: 14px;
        margin-bottom: 2px;
        font-weight: 600;
    }

    .rating .average-rating {
        font-size: 14px;
    }

    .product .rating i {
        font-size: 12px;
    }

    .rating i {
        color: #777777;
    }

    .list-course .product-img .img-fluid {
        height: 160px;
        object-fit: cover;
    }

    .product-img img {
        width: 100%;
        border-radius: 4px 4px 0 0;
        transform: translateZ(0);
        transition: all 2000ms cubic-bezier(.19, 1, .22, 1) 0ms;
    }

    .course-design .price h3 span {
        text-decoration: line-through;
        font-size: 16px;
        color: #777777;
    }

    .hide_buy_now:hover {
        background-color: #F2751F;
        color: white;
        /* border: 1px solid #F2751F; */
    }
</style>
@if (isset($course) && !empty($course))

    @php

        $my_timer = '';
        $hide_button = '';
        $buy_sell_course_id = '';
        $timerData = collect([]);
        if (auth()->guard('learner')->user()) {
            $timerData = SellCourseTimer::where('learner_id', auth()->guard('learner')->user()->id)
            ->where('course_id', $course->id)
            ->where('chapter_id', $chapterData->chapter_id)
            ->first();

            if ($timerData) {
                $my_timer = $timerData->timer;
                $hide_button = $timerData->timer;
            } else {
                $my_timer = $chapterData->timer;
            }
            $buy_sell_course = Course::where('slug', request()->route('slug'))->first();
            if($buy_sell_course) {
                $buy_sell_course_id = $buy_sell_course->id;
            }

        }


    @endphp
    <div class="row">
        @php
            $avg_course_rating = $course->AverageRating ?? 0;
        @endphp

        <div class="col-md-12 d-flex">
            <div class="course-box course-design list-course d-flex">
                <div class="product">
                    <div class="product-img">
                        @if ($course->type == 2)
                            <div class="course-package">
                                Package
                            </div>
                        @endif
                        <a
                            href="{{ $course->type == 2 ? url('course-package/' . $course->slug) : url('course-details/' . $course->slug) }}">
                            <img class="img-fluid" alt=""
                                src="{{ getImageIfExists($course->image, course_img_default()) }}">
                        </a>
                        @php
                            $priceArray = [
                                'plan_type' => $course->plan_type,
                                'list_price' => $course->list_price,
                                'final_payable_price' => $course->final_payable_price,
                            ];
                        @endphp
                        {{-- {{ $price = min_max_price_btn($priceArray, 1) }} --}}
                        {{-- {{dd($chapter)}} --}}
                        <div class="price">

                            <h3>{{ isset($course->plans->first()->final_payable_price) && isset($course->plans->first()->final_payable_price) > 0 ? '₹' . $course->plans->first()->final_payable_price : 'FREE' }}
                                <span>{{ isset($course->plans->first()->list_price) && isset($course->plans->first()->list_price) > 0 ? '₹' . isset($course->plans->first()->list_price) : 'FREE' }}</span>
                            </h3>
                        </div>
                    </div>


                    <div class="product-content">
                        <div class="head-course-title">
                            <h3 class="title"><a
                                    href="{{ $course->type == 2 ? url('course-package/' . $course->slug) : url('course-details/' . $course->slug) }}">{{ $course->title }}</a>
                            </h3>
                            <div class="all-btn all-category d-flex align-items-center">
                                {{-- @dd($hide_button) --}}
                                @if ($hide_button !== '0:00')

                                    @if ($isShow == 1)

                                        @if (@$course->plans->first()->final_payable_price == \App\Models\CoursePlan::PLAN_FREE)
                                            <a class="btn btn-primary hide_buy_now"
                                                href='{{ route('checkout.free.plan', [
                                                    'planId' => Crypt::encrypt(@$course->plans->first()->id),
                                                    'updated_new_coin' => 0,
                                                    'coupon_id' => 0,
                                                    'advancement' => 1,
                                                    'chapter_id' => $chapter->id,
                                                    'advancement_course_id' => $chapter->course_id,
                                                ]) }}'>Buy
                                                Now</a>
                                        @else
                                            <a class="btn btn-primary hide_buy_now"
                                                href="{{ route('checkout.index', [
                                                    'planId' => Crypt::encrypt(@$course->plans->first()->id),
                                                    'updated_new_coin' => '0',
                                                    'coupon_id' => '0',
                                                    'advancement' => 1,
                                                    'chapter_id' => $chapter->id,
                                                    'advancement_course_id' => $chapter->course_id,
                                                ]) }}">Buy
                                                Now</a>
                                        @endif
                                    @else
                                        <a class="btn btn-primary hide_buy_now" href='javascript:void(0)'>Buy
                                            Now</a>
                                    @endif

                                @endif

                            </div>
                        </div>
                        {{-- @dd($chapter) --}}
                        <div class="course-info border-bottom-0 pb-0 d-flex align-items-center">
                            <div class="rating-img d-flex align-items-center">
                                <img src="{{ asset('front/img/icon/icon-01.svg') }}" alt="">
                                @if ($course->type == 2)
                                    <p>{{ $course->packages_count ?? 0 }}+ Course</p>
                                @else
                                    <p>{{ $course->chapters_count ?? 0 }}+ Lesson</p>
                                @endif
                            </div>

                            <div class="course-view d-flex align-items-center">
                                <img src="{{ asset('front/img/icon/icon-19.svg') }}" alt="">
                                <p>{{ $course->lng == 1 ? 'English' : 'Hindi' }}</p>
                            </div>


                            @if (
                                (isset($course->hours) && !empty($course->hours) && $course->hours != '0') ||
                                    (isset($course->minutes) && !empty($course->minutes) && $course->minutes != '0'))
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
                        </div>
                        <div class="rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                            @endfor
                            <span class="d-inline-block average-rating">
                                ({{ $course->total_review ?? 0 }})
                            </span>
                        </div>
                        <div class="float-right">

                            <span class="d-inline-block" id="timer">

                            </span>
                            {{-- <div id="timer"></div> --}}
                        </div>
                        <div class="course-instructor d-flex mb-0">
                            @if ($course->instructor && !empty($course->instructor) && isset($course->instructor->id))
                                {{-- @if ($course->type == 1) --}}
                                <div class="course-group-img d-flex">
                                    <a href="{{ url('instructor/' . $course->instructor->id) }}"><img
                                            src="{{ getImageIfExists($course->instructor->profile_picture, user_img_default()) }}"
                                            alt="" class="img-fluid"></a>
                                    <div class="course-name inst-name">
                                        <h4><a
                                                href="{{ url('instructor/' . $course->instructor->id) }}">{{ $course->instructor->name }}</a>
                                        </h4>
                                        <p>{{ $course->instructor->instructure->designation ?? '' }}</p>
                                    </div>
                                </div>
                                {{-- @endif --}}
                            @endif
                        </div>

                        {{-- @dd($chapterData) --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif

@php
    $node_server = env('NODE_SERVER_URL');

@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.min.js"></script>

<script>
    $(document).ready(function() {
        timer = "";
        var learner_id_timer = "{{ @Auth::guard('learner')->user()->id }}";

        var buy_sell_course_id = "{{ $buy_sell_course_id }}"

        var timeLimit = "{{ $my_timer }}";
        if (timeLimit == '') {
            timeLimit = 00 + ':' + 00
        }

        if (learner_id_timer) {
            var chapter_id_timer = "{{ $chapterData->chapter_id }}";
            var course_id_timer = "{{ $course->id }}";

            var timeParts = timeLimit.split(":");
            var minutes, seconds;

            if (timeParts.length === 2) {
                minutes = parseInt(timeParts[0]);
                seconds = parseInt(timeParts[1]);
            } else if (timeParts.length === 1) {
                minutes = parseInt(timeParts[0]);
                seconds = 0;
            } else {

                console.error("Invalid time format: " + timeLimit);
            }

            var totalTime = minutes * 60 + seconds; // Convert minutes to seconds
            var interval = setInterval(function() {
                minutes = Math.floor(totalTime / 60);
                seconds = totalTime % 60;
                $('#timer').text("You can buy the Course within " + minutes + ':' + (seconds < 10 ?
                    '0' : '') + seconds + " Minutes").css("color", "green");

                var timerText = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                updateProgressData(timerText, learner_id_timer, chapter_id_timer, course_id_timer,
                    buy_sell_course_id);
                // alert(timerText);
                // updateProgressData(timer,learner_id_timer,chapter_id_timer,course_id_timer)
                totalTime--;
                if (totalTime < 0) {
                    clearInterval(interval);
                    $('#timer').text('Your validity to buy the course has expired !').css("color",
                        "red");
                    $(".hide_buy_now").hide();

                }
            }, 1000);



            const socket_url = '<?php echo $node_server; ?>';

            const socket = io(socket_url);

            console.log(socket_url);
            socket.on('connect', function() {
                console.log('Socket is running');
            });

            function updateProgressData(timer, learner_id_timer, chapter_id_timer, course_id_timer,
                buy_sell_course_id) {

                datas = {
                    timer,
                    learner_id_timer,
                    chapter_id_timer,
                    course_id_timer,
                    buy_sell_course_id

                };
                socket.emit('timerData', datas);
            }
        }

    });
</script>
