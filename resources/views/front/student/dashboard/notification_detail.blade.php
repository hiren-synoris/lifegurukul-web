<?php use Illuminate\Support\Str; ?>
@extends('front.layout.mainlayout')
@section('content')
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')


                <!-- Notifications -->
                <div class="col-xl-9 col-md-8">
                    <div class="settings-widget profile-details">
                        <div class="settings-menu p-0">
                            <div class="profile-heading">
                                <h3>Notifications <img src="{{ asset('front/img/icon/notification.svg') }}"
                                        alt="Notification"></h3>

                            </div>
                            <div class="notification-content m-4">
                                <h3>{{ $notification->title }}</h3>
                                <p> {{ $notification->text }}</p>
                                {{-- {{dd($notification)}} --}}
                                {{-- @if (!Str::contains($notification->title, 'Failed'))<p>
                            <a target="_blank" href="{{ url('view-invoice/'.Crypt::encrypt($notification->courseId)) }}" class="btn btn-success ms-2">View invoice</a></p>
                        @endif --}}
                                @php
                                    $data = App\Models\UserCourse::where('course_id', $notification->courseId)
                                        ->where('learner_id', $notification->learnerId)
                                        ->first();

                                    $course = App\Models\Course::where('id', $notification->courseId)->first();

                                @endphp
                                @if ($notification->type == 1)
                                    @if (!Str::contains($notification->title, 'Payment Failed'))
                                        <p>

                                            {{-- <a target="_blank" href="{{ url('view-invoice/'.Crypt::encrypt($notification->courseId)) }}" class="btn btn-success ms-2">View invoice</a></p> --}}
                                            @if ($data)
                                                <a target="_blank" href="{{ url('storage/' . $data->invoice) }}"
                                                    class="btn btn-success ms-2">View invoice</a>
                                        </p>
                                        @endif
                                    @endif
                                @endif


                                @if ($notification->type == 2)
                                    @if ($notification->target_link == 2)
                                        @if ($course->type == 1)
                                            <a href="{{ route('course.details', $course->slug) }}"
                                                class="btn btn-success ms-2">View course</a>
                                        @elseif($course->type == 2)
                                            <a href="{{ route('course.packages', $course->slug) }}"
                                                class="btn btn-success ms-2">View Package</a>
                                        @endif
                                    @elseif($notification->target_link == 1)
                                        <a href="{{ route('home') }}" class="btn btn-success ms-2">View home</a>
                                    @endif
                                @endif
                                @if ($notification->type == 5)
                                    @if ($notification->target_link == 2)
                                        @if ($course->type == 1)
                                            <a href="{{ route('course.details', $course->slug) }}"
                                                class="btn btn-success ms-2">View Course</a>
                                        @elseif($course->type == 2)
                                            <a href="{{ route('course.packages', $course->slug) }}"
                                                class="btn btn-success ms-2">View Package</a>
                                        @endif
                                    @elseif($notification->target_link == 1)
                                        <a href="{{ route('home') }}" class="btn btn-success ms-2">View Home</a>
                                    @endif
                                @endif
                                @if ($notification->external_link)
                                    <a href="{{ $notification->external_link }}" target="_blank"
                                        class="btn btn-success">View External link</a>
                                @endif


                                <p style="margin-top: 10px;">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->format('d F Y, g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Notifications -->
            </div>
        </div>
    </div>
    <!-- /Dashbord Student -->
@endsection

{{-- notification type --}}

{{--
type1 = purchased the course/package
type2 = for send notification by admin but only selected web push check box in messagining module
type3 =  enroll/free the course /package
type4 =  for send notification by admin but only selected mobile push check box in messagining module
type5 =   for send notification by admin but all checked checkbox in messagining module
type6 =

--}}
