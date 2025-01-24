{{-- @extends('front.layout.mainlayout') --}}
{{-- @section('content') --}}
    <!-- Cart -->


    @php

 
    use App\Models\Course;
    $redirectURL =  "";
    $countryId = @$learner->country_id;
    $stateId = @$learner->state_id ;
    $cityId = @$learner->city_id;
    $planPrice = get_course_plan_price($planId);
    $planData = get_plan_data($planId);
    $courseObj = get_course_detail_for_checkout($course->course_id, $planId);
    // $advancement_course = Course::where('id', $advancement_course_id)->select('slug')->first();

    //Course Object
    if (isset($courseObj->type) && !empty($courseObj->type)) {
        if ($courseObj->type == 1) {
            $redirectURL = route('failed_success_redirect', [
                        "courseId"=>$courseObj->id,
                        "payment_status" => 3,
                        "type"=>1,
                ]);
        } else if ($courseObj->type == 2) {
            $redirectURL = route('failed_success_redirect', [
                        "courseId"=>$courseObj->id,
                        "payment_status" => 3,
                        "type"=>2,
            ]);
        }
    }

    // if ($advisement == 1) {
    //     // $redirectURL = route('course.preview', ['slug' => $advancement_course->slug, 'chapterId' => $chapter_id]);
    // }
@endphp
{{-- @dd($planId) --}}
<section class="course-content checkout-widget" style="display:none">
    <div class="container">
        <div class="row">

            @if (!$planId)
            @php
                $redirectURL = route('failed_success_redirect', [
                        "plan_id"=>"",
                        "payment_status" => false,
                    ])
            @endphp
            @else
                <div class="col-lg-8">
                    <!-- /Payment Method -->
                    <div class="student-widget pay-method">
                        <div class="student-widget-group add-course-info">
                            <div class="cart-head">
                                <h4>Payment Method</h4>
                            </div>
                            <div class="checkout-form">
                                <div class="row">
                                    @php
                                        $key = config()->has('settings.razorpay_key')
                                            ? config('settings.razorpay_key')
                                            : null;
                                        $secret = config()->has('settings.razorpay_secret')
                                            ? config('settings.razorpay_secret')
                                            : null;
                                        if (
                                            config()->has('settings.razorpay_sandbox') &&
                                            config('settings.razorpay_sandbox') == 1
                                        ) {
                                            $key = config()->has('settings.razorpay_sandbox_key')
                                                ? config('settings.razorpay_sandbox_key')
                                                : null;
                                            $secret = config()->has('settings.razorpay_sandbox_secret')
                                                ? config('settings.razorpay_sandbox_secret')
                                                : null;
                                        }
                                    @endphp
                                    <div class="payment-btn">
                                        <button id="rzp-button" class="btn btn-primary" type="submit">Make a
                                            Payment</button>
                                    </div>
                                    <div class="payment-btn">
                                        <button id="rzp-subscription-button" class="btn btn-primary" type="submit">Make
                                            a
                                            Payment</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Payment Method -->
                </div>
            @endif
        </div>
    </div>
</section>

<!-- /Cart -->
{{-- @endsection --}}
{{-- @section('js') --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>


    $('body').on('click', '#rzp-button', function(e) {
        // alert();
        e.preventDefault();
        $('#loader_section').show();
        var planId = "{{ $planId ?? '' }}";
        var courseId = "{{ $course->course_id ?? '' }}";
        var amount = "{{ $final_price ?? '' }}";
        var advisement = "{{ @$advisement ?? '' }}";
        var coins = "{{ $coins ?? '' }}";
       // var chapter_id = "{{ @$chapter_id ?? '' }}";
        var coupon_id = "{{ @$coupon_id ?? '' }}";
        var actual_price = "{{ $actual_price ?? '' }}";
      //  var advancement_course_id = "{{ @$advancement_course_id ?? '' }}";
        var discount = "{{ $discount ?? '' }}";
        var course_title = "{{ $courseObj->title ?? '' }}";
        var key = "{{ $key ?? '' }}";http://139.59.88.254/phpmyadmin/index.php?route=/sql&db=lifegurukul_dev&table=coupon_usages&pos=0
        var total_amount = amount * 100;
        var user_id = "{{ @$learner->id }}";
        var coin_used = "{{ @$coin_used }}";
        var options = {
            "key": "{{ $key }}", // Enter the Key ID generated from the Dashboard
            "amount": total_amount, // Amount is in currency subunits. Default currency is INR. Hence, 10 refers to 1000 paise
            "currency": "INR",
            "name": "LifeGurukul",
            "description": "Rozerpay",
            // "image": "https://www.lifegurukul.app/logo.png?v=1", //when local use this temporary
            "image": "{{ logo_default() }}", //when local use this temporary
            "order_id": "", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
            "handler": function(response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: "{{ route('store_payment_api_success') }}",
                    data: {
                        razorpay_payment_id: response.razorpay_payment_id,
                        amount: amount,
                        planId: planId,
                        courseId: courseId,
                        course_title: course_title,
                        coin_used: coin_used,
                        coupon_id: coupon_id,
                        actual_price: actual_price,
                        discount: discount,
                       // chapter_id: chapter_id,
                       advisement: advisement,
                       // advancement_course_id: advancement_course_id,
                        user_id: user_id,

                    },
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(data) {
                        $('#loader_section').hide();

                        window.location = data.url;


                    }
                });
            },
            "prefill": {
                "name": "{{ @$learner->name }}",
                "email": "{{ @$learner->email }}",
                "contact": "{{ @learner_mobile_with_country_code_id($learner->id) }}"
            },
            "notes": {
                "Course Id": courseId,
                "Course Title": course_title
            },
            "theme": {
                "color": "#F37254"
            },
            "modal": {
                "ondismiss": function() {
                    window.location.replace("{{ $redirectURL ?? ''}}");
                }
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                });
            var planId = "{{ $planId ?? '' }}";
            var courseId = "{{ $course->course_id ?? '' }}";
            var amount = "{{ $final_price ?? '' }}";
            var course_title = "{{ $courseObj->title ?? '' }}";
            var key = "{{ $key ?? '' }}";
            var total_amount = amount * 100;
            $.ajax({
                type: 'POST',
                url: "{{ route('checkout.payment.store') }}",

                data: {
                    status: "fail",
                    error_msg: response.error,
                    course_id: courseId,
                    amount: amount,
                    course_title: course_title,
                    plan_id: planId,

                },
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(data) {
                    $('#loader_section').hide();
                    // Swal.fire({
                    //     icon: 'success',
                    //     title: 'Success',
                    //     text: response.error.description,
                    // }).then(function() {
                    //     if (data.url && data.url != 1) {
                    //         window.location = data.url;
                    //     } else {
                    //         location.reload();
                    //     }
                    // });
                }
            })
        });
        rzp1.open();
    });
</script>

    <script>
        $(document).ready(function() {
            $('#loader_section').show();
        });
    </script>
    <script>
        $(".main-wrapper").addClass('d-none');
    </script>

    @if (config()->has('settings.razorpay_status') && config('settings.razorpay_status') == 1)
        <script>
            $('#rzp-button').click();
        </script>
    @endif

