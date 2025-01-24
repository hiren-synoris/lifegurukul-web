@extends('front.layout.mainlayout')
@section('content')
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')
                <!-- My Subscriptions -->
                <div class="col-xl-9 col-md-8">
                    <div class="settings-widget profile-details">
                        <div class="settings-menu p-0">
                            <div class="profile-heading subscription-group d-flex align-items-center">
                                <div class="subscription-name">
                                    <h3>My Subscriptions</h3>
                                    <p>Here is list of course/package that you have subscribed.</p>
                                </div>
                                {{-- <div class="upgrade-now grad-border hvr-sweep-to-right">
                                <a href="{{url('pricing-plan')}}" class="btn btn-primary">Upgrade Now — Go Pro $50.00</a>
                            </div> --}}
                            </div>
                            <div class="row">
                                @if (isset($subscriptionData) && count($subscriptionData) > 0)

                                @foreach ($subscriptionData as $key => $value)
                                @php
                                    $expireFlag = is_expired($value->expire_at);
                                @endphp
                                    <div class="col-xl-4 col-md-6 d-flex dashboard-course" >
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
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                </a>
                                                @else
                                                <a
                                                    href="{{ $value->type == 2 ? url('my_package_course/'. $value->userCourse->id) : url('course-view/'. $value->slug) }}">
                                                    <img class="img-fluid" alt=""
                                                        src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                </a>
                                                @endif
                                            </div>
                                            <div class="product-content">
                                                <h3 class="title">
                                                    @if($expireFlag == 0)
                                                        <a href="{{$value->type == 2 ? url('course-package/'. $value->slug) : url('course-details/'. $value->slug)}}">{{ $value->title }}</a>
                                                       @else
                                                        <a
                                                        href="{{ $value->type == 2 ? url('my_package_course/'. $value->userCourse->id) : url('course-view/'. $value->slug) }}">{{ $value->title }}</a>
                                                       @endif
                                                    </h3>

                                                <div class="student-percent valid-till">
                                                    <p><b>Price : </b>
                                                        <span class="">{{ $value->price }}</span>
                                                    </p>
                                                </div>
                                                <div class="student-percent valid-till">
                                                    <p><b>Next billing on : </b>
                                                            <span class="text">-</span>
                                                            {{-- <span class="text-danger">{{ $value->expire_at }}</span> --}}
                                                    </p>
                                                </div>
                                                <div class="start-leason d-flex align-items-center">
                                                    @if($expireFlag == 0)
                                                        {{-- <a href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}"
                                                        class="btn btn-primary">Expired</a> --}}
                                                        <button class="btn btn-primary" type="button">Expired</button>
                                                    @elseif($expireFlag == 2 || $expireFlag == 1)
                                                        <a href="{{route('subscription.cancel', ['id' => $value->subscription_id])}}"
                                                        class="btn btn-primary">Cancel</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                @endforeach

                            @else
                            <h5>No record found!</h5>
                            @endif

						</div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- My Subscriptions -->

            </div>
        </div>
    </div>
    <!-- /Dashbord Student -->
@endsection
