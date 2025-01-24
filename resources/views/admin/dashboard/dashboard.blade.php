@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')

    @php
        $user = new App\Models\User();
    @endphp


    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="far fa-user"></i></span>
                        <a href="{{ url('backoffice/learners') }}">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Paid Users</span>
                            <span class="info-box-number">{{ $totalPaidUser ?? 0 }}</span>
                        </div>
                    </a>

                    </div>

                </div> --}}

                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="far fa-flag"></i></span>
                        <a href="{{ url('backoffice/learners') }}">
                        <div class="info-box-content">
                            <span class="info-box-text">Total Free Users</span>
                            <span class="info-box-number">{{ $totalFreeUser ?? 0 }}</span>
                        </div>
                    </a>

                    </div>
                </div> --}}

                <div class="col-md-3 col-sm-6 col-12">
                    {{-- <a href="{{url('backoffice/courses')}}"> --}}
                    <a href=" @canany(['browse_courses']) {{ url('backoffice/courses') }}  @endcan ">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="far fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">
                                    Courses
                                </span>
                                <span class="info-box-number">{{ $totalCourses ?? 0 }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    {{-- <a href=""> --}}
                    <a href="@can('browse_packages') {{ url('backoffice/packages') }} @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="far fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">
                                Packages
                                </span>
                                <span class="info-box-number">{{ $totalPackages ?? 0 }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                {{-- <a href="{{ url('backoffice/learners') }}"> --}}
                    <a href="  @can('browse_learners', $user) {{ url('backoffice/learners') }} @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Learners</span>

                            <span class="info-box-number">{{ $totalLearner ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Paid Learners</span>
                            <span class="info-box-number">{{ $paid_learner ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Free Learners</span>

                            <span class="info-box-number">{{ $free_learner ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Completed the payment Learners</span>

                            <span class="info-box-number">{{ $complate_payment ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    {{-- <a href=""> --}}
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                           <select class="form-control course_sold">
                            <option value="1">Daily</option>
                            <option value="7">week</option>
                            <option value="30">Month</option>
                           </select>
                           <span class="info-box-number sold_course">Sold course {{ $daily }}</span>
                        </div>
                    </div>
                    {{-- </a> --}}
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    {{-- <a href=""> --}}
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                           <select class="form-control payment_status_opt">
                            <option value="">Select Payment Status</option>
                            <option value="1">Success</option>
                            <option value="3">Free</option>
                            <option value="2">Failed</option>
                           </select>
                           <span class="info-box-number payment_status">Payment Status 0 </span>
                        </div>
                    </div>
                    {{-- </a> --}}
                </div>
                @can('browse_instructors')
                <div class="col-md-3 col-sm-6 col-12">
                {{-- <a href="{{ url('backoffice/instructors') }}"> --}}
                    <a href="  @can('browse_instructors', $user) {{ url('backoffice/instructors') }} @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Instructors</span>

                            <span class="info-box-number">{{ $instructors ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div>
                @endcan


            </div>


        </div>
            <!-- Small boxes (Stat box) -->
            {{-- <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalPaidUser ?? 0 }}</h3>

                            <p>Total Paid Users</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>

                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $totalFreeUser ?? 0 }}
                            </h3>
                            <p>Total Free Users</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>

                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>44</h3>

                            <p>User Registrations</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>65</h3>

                            <p>Unique Visitors</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div> --}}
            <!-- /.row -->

            <div class="row">
                <div class="col-lg-6">
                    {{-- <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Online Store Visitors</h3>
                  <a href="javascript:void(0);">View Report</a>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex">
                  <p class="d-flex flex-column">
                    <span class="text-bold text-lg">820</span>
                    <span>Visitors Over Time</span>
                  </p>
                  <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> 12.5%
                    </span>
                    <span class="text-muted">Since last week</span>
                  </p>
                </div>

                <div class="position-relative mb-4">
                  <canvas id="visitors-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> This Week
                  </span>

                  <span>
                    <i class="fas fa-square text-gray"></i> Last Week
                  </span>
                </div>
              </div>
            </div> --}}
                    <!-- /.card -->

                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">Purchase Courses by User</h3>
                            <div class="card-tools">

                                <a href="#" class="btn btn-tool btn-sm">
                                    {{-- <i class="fas fa-bars"></i> --}}
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <!-- ajax put table of userCourseSection data -->
                            <div id="userCourseSection" class="w-100"></div>
                            <!-- ajax put table of userCourseSection data -->
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                @can('browse_instructors')
                <div class="col-md-3">

                    <div class="card card-widget widget-user-2 solid-course-sec">

                        <div class="info-box bg-warning">
                            <span class="info-box-icon bg-warning"><i class="far fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Instructors</span>
                                <span class="info-box-number">Sold Courses </span>
                            </div>
                        </div>
                        {{-- <div class="widget-user-header bg-warning">
                            <span class="img-circle elevation-2"><i class="far fa-user"></i></span>
                            <h3 class="widget-user-username">Instructure</h3>
                            <h5 class="widget-user-desc">Sold Courses</h5>
                        </div> --}}

                        <div class="card-body">
                            @if (count($courseWiseUser) > 0)
                                @foreach ($courseWiseUser->sortByDesc('usercourseCount') as $key => $value)
                                    {{-- @dd($value->user->groupBy("name")) --}}
                                    {{-- @foreach ($value->courses as $val) --}}
                                    {{-- @if ($userCourse->userCourseCount->count() > 0) --}}
                                    {{-- @php
                                                    $count_course = App\models\UserCourse::whereIn("course_id",explode(",",$val->instructor_course_id))->count();
                                                    @endphp --}}
                                                @if ($value->usercourseCount != 0)

                                                <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                                <p class="text-success text-xl">
                                                    <i class="ion ion-ios-refresh-empty"></i>
                                                    <a href="{{ url('instructor/' . $value->id) }}"><img
                                                            src="{{ getImageIfExists($value->user->profile_picture, user_img_default()) }}"
                                                            alt="" class="direct-chat-img">
                                                    </a>
                                                </p>
                                                <p class="d-flex flex-column text-right">
                                                    <span class="font-weight-bold">
                                                        <i class="ion ion-android-arrow-up text-success"></i>
                                                        {{-- {{ count(array_unique(stringToArray($value->courses))) }} --}}
                                                        {{ $value->usercourseCount }}
                                                    </span>
                                                    <span class="text-muted"><a
                                                            href="{{ url('instructor/' . $value->user_id) }}">{{ $value->user->name ?? '' }}</a></span>
                                                </p>
                                            </div>
                                                 @endif
                                                {{-- @endforeach --}}
                                @endforeach
                                {{-- {{ $courseWiseUser->links }} --}}
                            @endif



                            <!-- ajax put table of instructureCourseSection data -->
                            {{-- <div id="instructureCourseSection" class="w-100"></div> --}}
                            <!-- ajax put table of instructureCourseSection data -->

                        </div>
                    </div>

                </div>
                @endcan
                <div class="col-md-3">
                    @if (isset($learnerDevice) && !empty($learnerDevice))
                                @php
                                    $device_count = array_sum(array_column($learnerDevice,'device_count'));
                                @endphp
                                @foreach ($learnerDevice as $key => $value)
                                @php
                                    $devicePercentage = $value->device_count*100/$device_count;
                                @endphp
                                    <div class="info-box
                                    @if ($value->device_type == 3) bg-blue @elseif($value->device_type == 1) bg-green @elseif($value->device_type == 2) bg-red @endif">

                                        <span class="info-box-icon">
                                            @if ($value->device_type == 3)
                                                <i class="fa fa-desktop"></i>
                                            @elseif($value->device_type == 1)
                                                <i class="fa fa-mobile"></i>
                                            @elseif($value->device_type == 2)
                                                <i class="fa fa-mobile"></i>
                                            @endif
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ getDeviceType($value->device_type) }}</span>
                                            <span class="info-box-number">{{ $value->device_count }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ round($devicePercentage, 2) }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                {{ round($devicePercentage, 2) }}%
                                            </span>
                                        </div>

                                    </div>
                                @endforeach
                    @endif




                    {{-- <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="ion ion-ios-heart-outline"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Android</span>
                            <span class="info-box-number">92,050</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 20%"></div>
                            </div>
                            <span class="progress-description">
                                20% Increase in 30 Days
                            </span>
                        </div>

                    </div>

                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="ion ion-ios-cloud-download-outline"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ios</span>
                            <span class="info-box-number">114,381</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 70%"></div>
                            </div>
                            <span class="progress-description">
                                70% Increase in 30 Days
                            </span>
                        </div>

                    </div> --}}





                </div>
                <!-- /.col-md-6 -->
                {{-- <div class="col-lg-6"> --}}
                {{-- <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Sales</h3>
                  <a href="javascript:void(0);">View Report</a>
                </div>
              </div>
              <div class="card-body">
                <div class="d-flex">
                  <p class="d-flex flex-column">
                    <span class="text-bold text-lg">$18,230.00</span>
                    <span>Sales Over Time</span>
                  </p>
                  <p class="ml-auto d-flex flex-column text-right">
                    <span class="text-success">
                      <i class="fas fa-arrow-up"></i> 33.1%
                    </span>
                    <span class="text-muted">Since last month</span>
                  </p>
                </div>

                <div class="position-relative mb-4">
                  <canvas id="sales-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                  <span class="mr-2">
                    <i class="fas fa-square text-primary"></i> This year
                  </span>

                  <span>
                    <i class="fas fa-square text-gray"></i> Last year
                  </span>
                </div>
              </div>
            </div> --}}
                <!-- /.card -->

                {{-- <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">Trainer Sold Courses</h3>
                            <div class="card-tools">
                                <a href="#" class="btn btn-sm btn-tool">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-tool">
                                    <i class="fas fa-bars"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- ajax put table of instructureCourseSection data -->
                            <div id="instructureCourseSection" class="w-100"></div>
                            <!-- ajax put table of instructureCourseSection data -->
                        </div>
                    </div>
                </div> --}}
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content -->

    @if ($firstLogin)
    <!-- Add your welcome popup modal code here -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Learn platform</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Welcome message -->
                    Please click below button to learn how to create courses, manage learners etc.
                </div>
                <div class="modal-footer justify-content-center">
                    {{-- <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button> --}}
                        <a href="{{ route('tutorial.index') }}" class="btn btn-primary">Video Tutorial</a>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
@section('scripts')

        @includeIf('admin.layouts.partials.scripts.script-list', [
            'dataTableJS' => 1,
            'switch' => 1,
            'select2' => 1,
            'summerNote' => 1,
            'dateRangePicker' => 1,
            'validateJS' => 1,
            'customScript' => 1,
            'dashboard' => 1,
            'demo' => 1,
            'chart' => 1,
        ])
        <script>
            $(document).ready(function() {

                $(".course_sold").change(function() {
                    $.ajax({
                        url: "{{ route('get_course_sold') }}",
                        type: "get",
                        data: {
                            days: $(this).val()
                        },
                        success: function(data) {
                            $(".sold_course").html("Sold course "+data)
                        }
                    })

                })

                $(".payment_status_opt").change(function() {
                    $.ajax({
                        url: "{{ route('get_payment_status') }}",
                        type: "get",
                        data: {
                            payment_status: $(this).val()
                        },
                        success: function(data) {
                            $(".payment_status").html("Payment status "+data)
                        }
                    })

                })


                $('#welcomeModal').modal('show');
            });
        </script>
        <script>
            $(document).ready(function() {
                $.ajax({
                    url: "{{ url('backoffice/userWiseCourse-index/') }}",
                    success: function(result) {
                        $("#userCourseSection").html(result);
                    }
                });
            })
        </script>



@endsection
