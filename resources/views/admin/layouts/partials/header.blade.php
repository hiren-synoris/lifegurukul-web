@php
use App\Models\Support;
use App\Models\Contact;
use Carbon\Carbon;
@endphp
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        @if(!request()->routeIs('courses.builder'))
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="javascript:void(0)" role="button"><i class="fas fa-bars"></i></a>
        </li>
        @endif
        {{-- <li class="nav-item d-none d-sm-inline-block">
            <a href="index3.html" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="javascript:void(0)" class="nav-link">Contact</a>
        </li> --}}
    </ul>
    <ul class="navbar-nav ml-auto">


    @if(auth()->user()->roles[0]->name =="admin")
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">
                <i class="far fa-bell"></i>
                @php
                if (auth()->user()->roles[0]->name == "admin") {
                $supportCount = Support::where("is_read", 0)
                ->where("created_by", '<>', auth()->user()->id)
                    ->count();

                    $contactCount = Contact::where("is_read", 0)
                    ->count();

                    $totalCount = $supportCount + $contactCount;
                    } else {
                    $supportCount = Support::where("is_read", 0)
                    ->where("created_by", '<>', auth()->user()->id)
                        ->count();

                        $totalCount = $supportCount;
                        }
                        @endphp



                        <span class="badge badge-warning navbar-badge">{{ $totalCount}}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notification-admin-list">
                <span class="dropdown-header">{{ $totalCount }} Notifications</span>
                <div class="dropdown-divider"></div>
                @php

                if (auth()->user()->roles[0]->name == "admin") {
                    $support = Support::select('id as supportId', 'subject', 'created_at as supportCreated')->where("created_by", '<>', auth()->user()->id)->where("is_read", 0)->orderBy('created_at', 'DESC')->get();
                $contact = Contact::select('id as contactId', 'name', 'created_at as contactCreated')->where("is_read", 0)->orderBy('created_at', 'DESC')->get();
                $mergedData = collect($support)->merge(collect($contact));
                }else {
                    $support = Support::select('id as supportId', 'subject', 'created_at as supportCreated')->where("created_by", '<>', auth()->user()->id)->where("created_by", '<>', auth()->user()->id)->where("is_read", 0)->orderBy('created_at', 'DESC')->get();
                
                $mergedData = collect($support);
                }
              
                $currentTimestamp = Carbon::now();
                @endphp

                @foreach($mergedData as $value)
                @php
                $createdAt = new Carbon($value->supportCreated ?? $value->contactCreated);
                $timeTaken = $currentTimestamp->diff($createdAt);

                $days = $timeTaken->days;
                $hours = $timeTaken->h;
                $minutes = $timeTaken->i;
                $seconds = $timeTaken->s;
                $time = "";
                if($days > 0) {
                $time = $days." Day";
                } else if($hours > 0) {
                $time = $hours." Hour";
                } else if($minutes > 0) {
                $time = $minutes." Minute";
                } else if($seconds > 0) {
                $time = $seconds." seconds";
                }
                @endphp

                @if(isset($value->supportId))
                <a href="{{ route("read_notifications", $value->supportId) }}" class="dropdown-item" style="white-space:normal;">
                    <div class="row">
                        <div class="col-md-1">
                            <i class="fas fa-envelope mr-2"></i>
                        </div>
                        <div class="col-md-11">
                            {{ $value->subject }}
                            <span class="float-right text-muted text-sm">{{ $time }}</span>
                        </div>
                    </div>
                </a>
                @elseif(isset($value->contactId))
                <a href="{{ route("read_contact", $value->contactId) }}" class="dropdown-item" style="white-space:normal;">
                    <div class="row">
                        <div class="col-md-1">
                            <i class="fas fa-envelope mr-2"></i>
                        </div>
                        <div class="col-md-11">
                            {{ $value->name }}
                            <span class="float-right text-muted text-sm">{{ $time }}</span>
                        </div>
                    </div>
                </a>
                @endif
                @endforeach



                {{-- <div class="dropdown-divider"></div>
                <a href="javascript:void(0)" class="dropdown-item">
                    <i class="fas fa-users mr-2"></i> 8 friend requests
                    <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="javascript:void(0)" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> 3 new reports
                    <span class="float-right text-muted text-sm">2 days</span>
                </a> --}}
                <div class="dropdown-divider"></div>
                <!-- <a href="{{ url("backoffice/read-all-support-ticket") }}" class="dropdown-item dropdown-footer">See All Notifications</a> -->
            </div>
        </li>
        @endif

        {{-- <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="javascript:void(0)" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="javascript:void(0)" role="button">
                <i class="fas fa-th-large"></i>
            </a>
        </li> --}}

        <!--Profile -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">
                <i class="fa fa-user"></i>
            </a>
            {{-- @dd(auth()->user()->roles[0]->toArray()) --}}
            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                @if(auth()->user()->roles[0]->name!="instructor")
                <a href="{{ route('profile.edit',auth()->id()) ?? "javascript:void(0)" }}" class="dropdown-item">
                    <i class="fas fa-user-circle mr-2"></i> Profiles
                </a>
                @else
                <a href="{{ url('backoffice/instructors/'.auth()->id().'/edit') ?? "javascript:void(0)" }}" class="dropdown-item">
                    <i class="fas fa-user-circle mr-2"></i> Profiles
                </a>
                @endif
                <div class="dropdown-divider"></div>
                <a href="{{ url('backoffice/logout') ?? "javascript:void(0)" }}" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="javascript:void(0)" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>