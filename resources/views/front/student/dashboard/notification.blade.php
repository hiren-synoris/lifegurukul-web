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
                            <h3>Notifications</h3>
                        </div>
                        <div class="checkout-form personal-address secure-alert border-line">
                            @if($notifications->isNotEmpty())
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Notification List</th>
                                        {{-- <th scope="col">Message</th> --}}
                                        {{-- <th scope="col">Time</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notifications as $value)
                                    <tr class="{{ $value->isRead == 0 ? "unread" : "" }}">
                                        <td> <img src="{{ asset('front/img/icon/notification.svg') }}" alt="Notification">
                                            @if (isset($value->course))
                                            <a href="{{ route('my.details',$value->id) }}"> <span class="noti_msg" style="  display: inline"> {{ $value->title }} For</span>
                                                <span class="course-notification">
                                                    {{ $value->course->title }}
                                            </a>
                                            </span>
                                            @else
                                            <a href="{{ route('my.details', $value->id) }}">

                                                    <span class="course-notification">
                                                        @if (!empty($value->title))
                                                        {{ $value->title }}
                                                        @else
                                                        {{ $value->text }}
                                                        @endif
                                                    </span>

                                            </a>

                                            </span>
                                            @endif
                                            <br />
                                            <span class="time_notification">{{ timeAgo($value->created_at) }}</span>
                                        </td>
                                        {{-- <td>{{ $value->text }}</td> --}}
                                        {{-- <td>{{ timeAgo($value->created_at) }}</td> --}}
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="pagination lms-page">
                                        {!! $notifications->withQueryString()->links('pagination::bootstrap-4') !!}
                                    </ul>
                                </div>
                            </div>
                            {{-- <div class="">
                                {{ $notifications->links() }}
                        </div> --}}
                        @else
                        <p>No Notifications</p>
                        @endif

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
