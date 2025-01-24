@extends('front.layout.mainlayout')
@section('content')
    <!-- Cart -->
    @php
        use App\Models\Course;
        $redirectURL = '';
        $countryId = Auth::guard('learner')->check() ? Auth::guard('learner')->user()->country_id : '';
        $stateId = Auth::guard('learner')->check() ? Auth::guard('learner')->user()->state_id : '';
        $cityId = Auth::guard('learner')->check() ? Auth::guard('learner')->user()->city_id : '';
        $planPrice = get_course_plan_price($planId);
        $planData = get_plan_data($planId);
        $courseObj = get_course_detail_for_checkout($course->course_id, $planId);
        $advancement_course = Course::where('id', $advancement_course_id)->select('slug')->first();

        //Course Object
        if (isset($courseObj->type) && !empty($courseObj->type)) {
            if ($courseObj->type == 1) {
                $redirectURL = route('course.details', ['slug' => $courseObj->slug]);
            } elseif ($courseObj->type == 2) {
                $redirectURL = route('course.packages', ['slug' => $courseObj->slug]);
            }
        }

        if ($advancement == 1) {
            $redirectURL = route('course.preview', ['slug' => $advancement_course->slug, 'chapterId' => $chapter_id]);
        }
    @endphp

    <section class="course-content checkout-widget" style="display:none">
        <div class="container">
            <div class="row">
                @if (session()->has('message'))
                    <div class="alert alert-success" role="alert">
                        {{ session()->get('message') }}
                    </div>
                @endif
                @if (!$planId)
                    <div class="col-lg-12 d-flex justify-content-center">
                        <a href="{{ route('course.list') }}" class="btn btn-primary" type="submit">Continue Shopping</a>
                    </div>
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
                                        <form action="{{ route('checkout.payment.instamojo.store') }}"
                                            style="display: none;" method="post" id="instamojo-pay-btn">
                                            @csrf
                                            <div class="payment-btn">
                                                <button class="btn btn-primary" type="submit">Make a Payment</button>
                                            </div>
                                            <input type="hidden" name="planId" class="planId" value="{{ $planId }}">
                                            <input type="hidden" name="final_price" class="final_price"
                                                value="{{ $final_price }}">
                                            <input type="hidden" name="courseId" class="courseId"
                                                value="{{ $course->course_id }}">
                                            <input type="hidden" name="coins" class="coins" value="{{ $coins }}">
                                            <input type="hidden" name="coupon_id" class="coupon_id"
                                                value="{{ $coupon_id_new }}">
                                            <input type="hidden" name="actual_price" class="actual_price"
                                                value="{{ $actual_price }}">
                                            <input type="hidden" name="advancement_course_id" class="advancement_course_id"
                                                value="{{ $advancement_course_id }}">

                                            <input type="hidden" name="advancement" class="advancement"
                                                value="{{ $advancement }}">
                                            <input type="hidden" name="chapter_id" class="chapter_id"
                                                value="{{ $chapter_id }}">
                                            <input type="hidden" name="discount" class="discount"
                                                value="{{ $discount }}">
                                        </form>
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
@endsection
@section('js')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        $('body').on('click', '#rzp-button', function(e) {
            // alert();
            e.preventDefault();
            $('#loader_section').show();
            var planId = "{{ $planId ?? '' }}";
            var courseId = "{{ $course->course_id ?? '' }}";
            var amount = "{{ $final_price ?? '' }}";
            var advancement = "{{ $advancement ?? '' }}";
            var coins = "{{ $coins ?? '' }}";
            var chapter_id = "{{ $chapter_id ?? '' }}";
            var coupon_id_new = "{{ $coupon_id_new ?? '' }}";
            var actual_price = "{{ $actual_price ?? '' }}";
            var advancement_course_id = "{{ $advancement_course_id ?? '' }}";
            var discount = "{{ $discount ?? '' }}";
            var course_title = "{{ $courseObj->title ?? '' }}";
            var key = "{{ $key ?? '' }}";
            var total_amount = amount * 100;
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
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('checkout.payment.store') }}",
                        data: {
                            razorpay_payment_id: response.razorpay_payment_id,
                            amount: amount,
                            planId: planId,
                            courseId: courseId,
                            course_title: course_title,
                            coins: coins,
                            coupon_id: coupon_id_new,
                            actual_price: actual_price,
                            discount: discount,
                            chapter_id: chapter_id,
                            advancement: advancement,
                            advancement_course_id: advancement_course_id,

                        },
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(data) {
                            $('#loader_section').hide();
                            if (data.success == false) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'warning',
                                    text: data.success,
                                }).then(function() {
                                    if (data.url && data.url != 1) {
                                        window.location = data.url;
                                    } else {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Payment successfully Completed',
                                }).then(function() {
                                    if (data.url && data.url != 1) {
                                        window.location = data.url;

                                        ! function(f, b, e, v, n, t, s) {
                                            if (f.fbq) return;
                                            n = f.fbq = function() {
                                                n.callMethod ?
                                                    n.callMethod.apply(n,
                                                    arguments) : n.queue.push(
                                                        arguments)
                                            };
                                            if (!f._fbq) f._fbq = n;
                                            n.push = n;
                                            n.loaded = !0;
                                            n.version = '2.0';
                                            n.queue = [];
                                            t = b.createElement(e);
                                            t.async = !0;
                                            t.src = v;
                                            s = b.getElementsByTagName(e)[0];
                                            s.parentNode.insertBefore(t, s)
                                        }(window, document, 'script',
                                            'https://connect.facebook.net/en_US/fbevents.js'
                                            );
                                        fbq('init', '478648434106722');
                                        fbq('track', 'PageView');
                                        fbq('track', 'Purchase', {
                                            value: 1490.00,
                                            currency: 'INR'
                                        });

                                    } else {
                                        location.reload();
                                    }
                                });
                            }

                        }
                    });
                },
                "prefill": {
                    "name": "{{ Auth::guard('learner')->user()->name ?? null }}",
                    "email": "{{ Auth::guard('learner')->user()->email ?? null }}",
                    "contact": "{{ learner_mobile_with_country_code() }}"
                },
                "notes": {
                    "CourseId": courseId,
                    "CourseTitle": course_title,
                    "PlanId": planId,
                    "Platform": "Web"
                },
                "theme": {
                    "color": "#F37254"
                },
                "modal": {
                    "ondismiss": function() {
                        window.location.replace("{{ $redirectURL ?? url('/') }}");
                    }
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function(response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var planId = "{{ $planId ?? '' }}";
                var courseId = "{{ $course->course_id ?? '' }}";
                var amount = "{{ $final_price ?? '' }}";
                var course_title = "{{ $courseObj->title ?? '' }}";
                var key = "{{ $key ?? '' }}";
                var advancement = "{{ $advancement ?? '' }}";
                var advancement_course_id = "{{ $advancement_course_id ?? '' }}";
                var chapter_id = "{{ $chapter_id ?? '' }}";
                var total_amount = amount * 100;
                $.ajax({
                    type: 'POST',
                    url: "{{ route('checkout.payment.store') }}",

                    data: {
                        status: "failed",
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
    {{-- Razor PAY Subscription Payment --}}
    <script>
        function createSubscription() {

            $('#loader_section').show();
            // Replace the 'plan_id' variable with the actual plan ID from your Razorpay account
            var planId = '{{ $planData->plan_id }}';
            // Make an AJAX request to create a subscription

            $.ajax({
                url: "{{ route('create.subscription') }}",
                type: 'POST',
                data: {
                    plan_id: planId,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        submitRazorpaySubscription(response.subscription_id)
                    }
                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.errors,
                        }).then(function() {
                            window.location = "{{ route('course.list') }}";
                        });
                    }
                },
                error: function(jqxhr, error, errorThrown) {
                    if (jqxhr.status == 403) {
                        Swal.fire(
                            'Unauthorized',
                            '',
                            'error'
                        ).then(function() {
                            window.location = "{{ route('course.list') }}";
                        });
                    }
                },
            });
        }

        function submitRazorpaySubscription(subscription_id) {
            $('#loader_section').show();
            var planId = "{{ $planId ?? '' }}";
            var subscription_id = subscription_id;
            var courseId = "{{ $course->course_id ?? '' }}";
            var course_title = "{{ $courseObj->title ?? '' }}";
            var amount = "{{ $final_price ?? '' }}";
            var discount = "{{ $discount ?? '' }}";
            var key = "{{ $key ?? '' }}";
            var chapter_id = "{{ $chapter_id ?? '' }}";
            var advancement = "{{ $advancement ?? '' }}";
            var coins = "{{ $coins ?? '' }}";
            var actual_price = "{{ $actual_price ?? '' }}";
            var coupon_id_new = "{{ $coupon_id_new ?? '' }}";
            var advancement_course_id = "{{ $advancement_course_id ?? '' }}";
            var total_amount = amount * 100;
            var options = {
                "key": "{{ $key ?? '' }}", // Enter the Key ID generated from the Dashboard
                "subscription_id": subscription_id,
                "amount": total_amount, // Amount is in currency subunits. Default currency is INR. Hence, 10 refers to 1000 paise
                "currency": "INR",
                "name": "LifeGurukul",
                "description": "Rozerpay",
                // "image": "https://www.lifegurukul.app/logo.png?v=1",
                "image": "{{ logo_default() }}",
                "order_id": "", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                "handler": function(response) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('checkout.payment.store') }}",
                        data: {
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_subscription_id: response.razorpay_subscription_id,
                            razorpay_signature: response.razorpay_signature,
                            amount: amount,
                            planId: planId,
                            subscription_id: subscription_id,
                            courseId: courseId,
                            course_title: course_title,
                            coins: coins,
                            coupon_id: coupon_id_new,
                            actual_price: actual_price,
                            discount: discount,
                            chapter_id: chapter_id,
                            advancement: advancement,
                            advancement_course_id: advancement_course_id
                        },
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(data) {
                            $('#loader_section').hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Subscription Payment successfully Completed',
                            }).then(function() {
                                if (data.url && data.url != 1) {
                                    window.location = data.url;
                                } else {
                                    location.reload();
                                }
                            });
                        }
                    });
                },
                "prefill": {
                    "name": "{{ Auth::guard('learner')->user()->name ?? null }}",
                    "email": "{{ Auth::guard('learner')->user()->email ?? null }}",
                    "contact": "{{ learner_mobile_with_country_code() }}"
                },
                // "notes": {
                // "note_key_1": "Subscription payment"
                // },
                "theme": {
                    "color": "#F37254"
                },
                "modal": {
                    "ondismiss": function() {
                        window.location.replace("{{ $redirectURL }}");
                    }
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function(response) {


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: "{{ route('checkout.payment.store') }}",
                    data: {
                        "status": "fail",
                        error_msg: response.error.description,
                        course_id: '{{ $course->course_id ?? '' }}'
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
        }
    </script>
    {{-- If subscription package for plan --}}
    {{-- && config()->has('settings.razorpay_status') && config('settings.razorpay_status') == 1 --}}
    @if ($planData->plan_type == \App\Models\CoursePlan::PLAN_RECURRING && empty(config('settings.razorpay_status')))
        <script>
            var url = "{{ $redirectURL ?? url('/') }}"
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Payment gateway not enabled for recurring subscription courses.",
            }).then(function() {
                window.location.href = url
            });
        </script>
    @elseif(!Auth::guard('learner')->check())
        <script>
            new bootstrap.Modal(document.getElementById('mobileLoginModal')).show();
        </script>
    @else
        <script>
            $(document).ready(function() {
                $('#loader_section').show();
            });
        </script>
        <script>
            $(".main-wrapper").addClass('d-none');
        </script>
        @if ($planData->plan_type == \App\Models\CoursePlan::PLAN_RECURRING && $planData->plan_id != null)
            <script>
                $(document).ready(function() {
                    createSubscription();
                });
                // $('#rzp-subscription-button').click();
            </script>
            {{-- If both are enabled Razorpay submit --}}
        @elseif (config()->has('settings.razorpay_status') &&
                config('settings.razorpay_status') == 1 &&
                (config()->has('settings.instamojo_status') && config('settings.instamojo_status') == 1))
            <script>
                $('#rzp-button').click();
            </script>
            {{-- Only enabled InstaMOJO submit --}}
        @elseif (config()->has('settings.instamojo_status') && config('settings.instamojo_status') == 1)
            <script>
                $('#loader_section').show();
                $('#instamojo-pay-btn').submit();
            </script>
        @elseif (config()->has('settings.razorpay_status') && config('settings.razorpay_status') == 1)
            <script>
                $('#rzp-button').click();
            </script>
        @endif
    @endif



@endsection
