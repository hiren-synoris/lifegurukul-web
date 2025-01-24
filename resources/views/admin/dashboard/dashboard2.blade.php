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
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Paid Learners</span>
                            <span class="info-box-number">{{ $paid_learner ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div> --}}
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Free Learners</span>

                            <span class="info-box-number">{{ $free_learner ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div> --}}
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
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <a href="  @can('browse_learners', $user) @endcan">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Completed the payment Learners</span>

                            <span class="info-box-number">{{ $complate_payment ?? 0 }}</span>
                        </div>
                    </div>
                    </a>
                </div> --}}
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                           <select class="form-control course_sold">
                            <option >Sold course</option>
                            <option value="1">Daily</option>
                            <option value="7">week</option>
                            <option value="30">Month</option>
                           </select>
                           <span class="info-box-number sold_course">Sold course 0</span>
                        </div>
                    </div>

                </div> --}}
                {{-- <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
                        <div class="info-box-content">
                            <select class="form-control filter_global">
                                <option value="7">Week</option>
                                <option value="30">Month</option>
                                <option value="date_range">Date Range</option>
                            </select>
                        </div>
                    </div>
                </div> --}}
                {{-- <div class="col-md-3 date_range_hide" style="display: none">
                    <div class="info-box">
                        <div class="info-box-content">
                        <span class="info-box-text">Date Range</span>
                        <input type="text" class="form-control float-right date_range" name="date_range" placeholder="Enter Date" id="date_range">
                        </div>
                    </div>
                </div> --}}

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        {{-- <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span> --}}
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

                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <div class="info-box-content">
                            <input type="text"
                            class="form-control search_course autocompleteInput sl-label"
                            id="fltr_select_course" name="course" placeholder="Search Course" style="margin-top: 4px;">
                            <input type="text" class="form-control w-100 search_course course_value sl-id course_id"
                            id="fltr_select_course" name="course" style="display: none;" >
                            <span>Percentage   <span id="rangeValue">0</span>%</span>
                            <input type="range" id="rangeInput" min="0" max="100" value="0">
                            <span>Learners <span id="percentage_count">0</span></span>
                            <button class="btn btn-primary btn-sm percentage_submit"> submit</button>
                        </div>
                    </div>

                </div>


                {{-- <div class="col-md-3" style="">
                    <div class="">
                        <label>Courses</label>
                        <input type="text"
                        class="form-control w-100 search_course autocompleteInput sl-label"
                        id="fltr_select_course" name="course" placeholder="Search Course">
                        <input type="text" class="form-control w-100 search_course course_value sl-id course_id"
                        id="fltr_select_course" name="course" style="display: none;" >
                    </div>
                </div> --}}
                {{-- <div class="col-md-1 search_btn date_range"  style="display: none">
                    <div class="">
                        <button class="btn btn-success">Search</button>
                    </div>
                </div> --}}



            </div>


        </div>

        @php

        @endphp
            <div class="row">
                <div class="col-md-12 mt-5">
                    <div class="row ">
                    <div class="col-md-2 mt-4">
                        <select class="form-control filter_global">
                            <option >Select Days</option>
                            <option value="7">7 Days</option>
                            <option value="30">31 Days</option>
                            <option value="date_range">Date Range</option>
                        </select>
                    </div>
                    <div class="col-md-3 date_range_hide w-25 float-left" style="display:none">
                        <span class="">Date Range</span>
                            <input type="text" class="form-control float-right date_range" name="date_range" placeholder="Enter Date" id="date_range">
                    </div>
                </div>
                <div class="row mt-3 p-2">
                    <div class="col-md-2 border p-2">
                        <h4>New Learner</h4>
                        <h5 class="learner_count">0</h5>
                    </div>
                    <div class="col-md-1">

                    </div>
                    <div class="col-md-2 border p-2">
                        <h4>Enrollment</h4>
                        <h5 class="enrol_count">0</h5>
                    </div>
                    <div class="col-md-1">

                    </div>
                    <div class="col-md-2 border p-2">
                        <h4>Most View</h4>
                        <h5 class="mostFrequentView"></h5>
                    </div>
                    <div class="col-md-1">

                    </div>
                    <div class="col-md-2 border p-2">
                        <h4>Most purchased</h4>
                        <h5 class="mostFrequentBuy"></h5>
                    </div>
                </div>
                    <canvas id="FreePaidCourse" class="mt-4"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3 mt-2" >
                            <label class="h4">Sold Courses</label>
                            <input type="text"
                            class="form-control search_course autocompleteInput sl-label"
                            id="fltr_select_course" name="course" placeholder="Search Course" style="margin-top: 4px;">
                            <input type="text" class="form-control w-100 search_course course_value sl-id course_id"
                            id="fltr_select_course" name="course" style="display: none;" >
                        </div>
                        <div class="col-md-3 mt-5 show_course_filter" style="display:noned">
                            {{-- <label class="h4"></label> --}}
                            <select class="form-control filter_global2" >
                                <option value="7">7 Days</option>
                                <option value="30">31 Days</option>
                                <option value="date_range">Date Range</option>
                            </select>
                        </div>
                        <div class="col-md-4 date_range_course_hide w-25 float-left mt-4" style="display:none">
                            <span class="">Date Range</span>
                                <input type="text" class="form-control float-right date_range_course_sold" name="date_range_course_sold" placeholder="Enter Date" id="date_range_course_sold">
                        </div>
                        <div class="col-md-2 mt-5 show_course_filter" style="display:nonef">
                            {{-- <label class="h4"></label><br> --}}
                            <button class="btn btn-success btn_search_course_sold">Search</button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6 courses_hide mt-5" style="display: none">
                    <canvas id="CourseChart"></canvas>
                </div>
                <div class="col-md-6 courses_hide mt-5" style="display: none">
                    <label class="h4">People completed the course</label>
                    <div></div>
                    <canvas id="user_course_completed_"></canvas>
                </div>
            </div>
            <div class="row instructor_hide mt-5" style="">
                <div class="col-md-12">
                    <div class="row">
                    <div class="col-md-3 mt-2" >
                        <label class="h4">Instructor</label>
                        <input type="text"
                        class="form-control search_instructor autocompleteInput_ sl_in-label"
                        id="fltr_select_instructor" name="instructor" placeholder="Search instructor" style="margin-top: 4px;">
                        <input type="text" class="form-control w-100 search_instructor instructor_value sl_in-id instructor_id"
                        id="fltr_select_instructor" name="instructor" style="display: none;" >
                    </div>
                    <div class="col-md-3 mt-5 show_instructor_filter" style="display:noned">
                        <select class="form-control filter_global3" >
                            <option value="7">7 Days</option>
                            <option value="30">31 Days</option>
                            <option value="date_range">Date Range</option>
                        </select>
                    </div>
                    <div class="col-md-4 date_range_instructor_hide w-25 float-left mt-4" style="display:none">
                        <span class="">Date Range</span>
                            <input type="text" class="form-control float-right date_range_instructor_sold" name="date_range_instructor_sold" placeholder="Enter Date" id="date_range_instructor_sold">
                    </div>
                    <div class="col-md-2 mt-5" style="display:nonef">
                        <button class="btn btn-success btn_search_instructor">Search</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <canvas id="InstructorChart"></canvas>
        </div>
        </div>

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
                        'select2' => 1,
                        'dateRangePicker' => 1,
                    ])
                    <script>
                        $(document).ready(function() {
                            $('#rangeValue').text($('#rangeInput').val());

                            $('#rangeInput').on('input', function() {
                                var value = $(this).val();
                                $('#rangeValue').text(value);

                            });

                            $(".percentage_submit").click(function() {

                                $.ajax({
                                    url: "{{ route('get_percentage_count') }}",
                                    type: "get",
                                    data: {
                                        percentage_count: $('#rangeInput').val(),
                                        course_id: $(".course_id").val()
                                    },
                                    success: function(data) {
                                        $("#percentage_count").text(data)
                                    }
                                })
                            })
                        });


                        $(function() {

                            let soldCourseChart = null;
                            let CourseChart = null;
                            let freePaidCourse = null;
                            let paidCourse = null;
                            let user_course_completed = null;
                            let soldInstructor = null;
                            var start_date_course = null;
                            var end_date_course = null;
                            var start_date_instructor = null;
                            var end_date_instructor = null;


                            $('.date_range').daterangepicker({
                                timePicker: true,
                                startDate: moment().startOf('month'),
                                endDate: moment(),
                                locale: {
                                    format: 'DD/MM/YYYY',
                                }
                            }, function(start, end, label) {
                                getDate(start, end);
                            });

                            $('.date_range_course_sold').daterangepicker({
                                timePicker: true,
                                startDate: moment().startOf('month'),
                                endDate: moment(),
                                locale: {
                                    format: 'DD/MM/YYYY',
                                }
                            }, function(start, end, label) {


                                start_date_course = start.format('YYYY-MM-DD');
                                end_date_course = end.format('YYYY-MM-DD');
                            });

                            $('.date_range_instructor_sold').daterangepicker({
                                timePicker: true,
                                startDate: moment().startOf('month'),
                                endDate: moment(),
                                locale: {
                                    format: 'DD/MM/YYYY',
                                }
                            }, function(start, end, label) {


                                start_date_instructor = start.format('YYYY-MM-DD');
                                end_date_instructor = end.format('YYYY-MM-DD');
                            });

                            function getDate(start_date, end_date) {
                                $.ajax({
                                    url: "{{ route('get_course_sold') }}",
                                    type: "get",
                                    data: {
                                        start_date: start_date.format('YYYY-MM-DD'),
                                        end_date: end_date.format('YYYY-MM-DD')
                                    },
                                    success: function(data) {
                                        renderChartFreePaidCourse(data.freePaidcourses)
                                        $(".learner_count").text(data.new_learner)
                                        $(".enrol_count").text(data.new_enrol)
                                        $(".mostFrequentBuy").text(data.mostFrequentBuy)
                                        $(".mostFrequentView").text(data.mostFrequentView)

                                    },
                                    error: function(xhr, status, error) {
                                        console.error("AJAX Error: ", status, error);
                                    }
                                });
                            }



                            function user_course_completed_(soldCourseData) {
                                var dates = soldCourseData.user_course_completed.map(function(item) {
                                    return moment(item.date).format('DD/MM/YYYY');
                                });
                                var counts = soldCourseData.user_course_completed.map(function(item) {
                                    return item.count;
                                });

                                var ctx = document.getElementById('user_course_completed_').getContext('2d');

                                if (user_course_completed !== null) {
                                    user_course_completed.destroy();
                                }

                                user_course_completed = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: dates,
                                        datasets: [{
                                            label: 'Course completed',
                                            data: counts,
                                            backgroundColor: 'rgba(75, 192, 192, 1)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }

                            function renderChartCourse(soldCourseData) {

                                var dates = soldCourseData.sold_courses.map(function(item) {
                                    return moment(item.date).format('DD/MM/YYYY');
                                });
                                var counts = soldCourseData.sold_courses.map(function(item) {
                                    return item.count;
                                });


                                var ctx = document.getElementById('CourseChart').getContext('2d');

                                if (CourseChart !== null) {
                                    CourseChart.destroy();
                                }

                                CourseChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: dates,
                                        datasets: [{
                                            label: "Courses",
                                            data: counts,
                                            backgroundColor: 'rgba(75, 192, 192, 1)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }


                            function renderChartFreePaidCourse(soldCourseData) {

                                var dates = soldCourseData.map(function(item) {
                                    return moment(item.date).format('DD/MM/YYYY');
                                });
                                var paid_count = soldCourseData.map(function(item) {
                                    return item.paid_count;
                                });
                                var free_count = soldCourseData.map(function(item) {
                                    return item.free_count;
                                });
                                var fail_count = soldCourseData.map(function(item) {
                                    return item.fail_count;
                                });
                                var enrol_by_admin = soldCourseData.map(function(item) {
                                    return item.enrol_by_admin;
                                });


                                var ctx = document.getElementById('FreePaidCourse').getContext('2d');

                                if (freePaidCourse !== null) {
                                    freePaidCourse.destroy();
                                }

                                freePaidCourse = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: dates,
                                        datasets: [{
                                                label: 'Paid Courses',
                                                data: paid_count,
                                                backgroundColor: 'green',
                                                borderColor: 'green',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Free Courses',
                                                data: free_count,
                                                backgroundColor: 'yellow',
                                                borderColor: 'yellow',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Enrol by admin',
                                                data: enrol_by_admin,
                                                backgroundColor: 'orange',
                                                borderColor: 'orange',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Fail Payment',
                                                data: fail_count,
                                                backgroundColor: 'red',
                                                borderColor: 'red',
                                                borderWidth: 1
                                            }
                                        ]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }

                            function renderChartInstructor(soldInstructorData) {

                                var dates = soldInstructorData.map(function(item) {
                                    return moment(item.date).format('DD/MM/YYYY');
                                });
                                var paid_count = soldInstructorData.map(function(item) {
                                    return item.paid_count;
                                });
                                var free_count = soldInstructorData.map(function(item) {
                                    return item.free_count;
                                });
                                var fail_count = soldInstructorData.map(function(item) {
                                    return item.fail_count;
                                });
                                var enrol_by_admin = soldInstructorData.map(function(item) {
                                    return item.enrol_by_admin;
                                });


                                var ctx = document.getElementById('InstructorChart').getContext('2d');

                                if (soldInstructor !== null) {
                                    soldInstructor.destroy();
                                }

                                soldInstructor = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: dates,
                                        datasets: [{
                                                label: 'Paid Courses',
                                                data: paid_count,
                                                backgroundColor: 'green',
                                                borderColor: 'green',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Free Courses',
                                                data: free_count,
                                                backgroundColor: 'yellow',
                                                borderColor: 'yellow',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Enrol by admin',
                                                data: enrol_by_admin,
                                                backgroundColor: 'orange',
                                                borderColor: 'orange',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Fail Payment',
                                                data: fail_count,
                                                backgroundColor: 'red',
                                                borderColor: 'red',
                                                borderWidth: 1
                                            }
                                        ]
                                    },
                                    options: {
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            }


                            $("#date_range").attr("placeholder", "Date range");


                            // filtersData($(".filter_global option").val())



                            $(document).on("change", ".filter_global", function() {
                                if ($(this).val() == "date_range") {
                                    $(".date_range_hide").show();

                                } else {

                                    $(".date_range_hide").hide();
                                    filtersData($(this).val())
                                }

                            })

                            $(document).on("change", ".filter_global2", function() {
                                if ($(this).val() == "date_range") {

                                    $(".date_range_course_hide").show();

                                } else {
                                    $(".date_range_course_hide").hide();

                                }

                            })
                            $(document).on("change", ".filter_global3", function() {
                                // alert($(this).val())
                                if ($(this).val() == "date_range") {
                                    $(".date_range_instructor_hide").show();
                                } else {
                                    $(".date_range_instructor_hide").hide();

                                }

                            })

                            function filtersData(days) {
                                $.ajax({
                                    url: "{{ route('get_course_sold') }}",
                                    type: "get",
                                    data: {
                                        days: days
                                    },
                                    success: function(data) {
                                        renderChartFreePaidCourse(data.freePaidcourses)
                                        $(".learner_count").text(data.new_learner)
                                        $(".enrol_count").text(data.new_enrol)
                                        $(".mostFrequentView").text(data.mostFrequentView)
                                        $(".mostFrequentBuy").text(data.mostFrequentBuy)
                                    }
                                })
                            }

                            $(".autocompleteInput").autocomplete({
                                source: '/backoffice/search-course_dashboard',
                                focus: function(event, ui) {
                                    $(".sl-label").val(ui.item.label);
                                    return false;
                                },
                                select: function(event, ui) {
                                    $(".sl-label").val(ui.item.label);
                                    $(".sl-id").val(ui.item.value);
                                    return false;
                                }

                            });
                            $(".autocompleteInput_").autocomplete({
                                source: '/backoffice/search-instructor_dashboard',
                                focus: function(event, ui) {
                                    $(".sl_in-label").val(ui.item.label);
                                    return false;
                                },
                                select: function(event, ui) {
                                    $(".sl_in-label").val(ui.item.label);
                                    $(".sl_in-id").val(ui.item.value);

                                    return false;
                                }

                            });



                            $(document).on("click", ".btn_search_course_sold", function() {

                                if ($("#fltr_select_course").val() == "") {
                                    alert("Please Enter course")
                                    return false
                                }
                                if ($(".filter_global2").val() != "date_range") {
                                    filtersDataCourse($(".course_id").val(), $(".filter_global2").val())
                                } else {

                                    filtersDataCourse($(".course_id").val(), "", start_date_course, end_date_course)
                                }

                            })

                            $(document).on("click", ".btn_search_instructor", function() {
                                // filtersDataInstructor($(".instructor_id").val())

                                if ($("#fltr_select_instructor").val() == "") {
                                    alert("Please Enter instructor")
                                    return false
                                }
                                if ($(".filter_global3").val() != "date_range") {
                                    filtersDataInstructor($(".instructor_id").val(), $(".filter_global2").val())
                                } else {

                                    filtersDataInstructor($(".instructor_id").val(), "", start_date_instructor,
                                        end_date_instructor)
                                }

                            })


                            function filtersDataInstructor(id, days, start_date_instructor, end_date_instructor) {
                                $.ajax({
                                    url: "{{ route('get_instructor') }}",
                                    type: "get",
                                    data: {
                                        id: id,
                                        days: days,
                                        start_date_instructor: start_date_instructor,
                                        end_date_instructor: end_date_instructor
                                    },
                                    success: function(data) {
                                        renderChartInstructor(data.instructor_wise_courses)
                                    }
                                })
                            }

                            function filtersDataCourse(id, days, start_date_course, end_date_course) {
                                $.ajax({
                                    url: "{{ route('get_user_course') }}",
                                    type: "get",
                                    data: {
                                        id: id,
                                        days: days,
                                        start_date_course: start_date_course,
                                        end_date_course: end_date_course
                                    },
                                    success: function(data) {
                                        $(".courses_hide").show()
                                        renderChartCourse(data)
                                        user_course_completed_(data)
                                    }
                                })
                            }

                        });


                        $(document).ready(function() {


                            $(".payment_status_opt").change(function() {
                                $.ajax({
                                    url: "{{ route('get_payment_status') }}",
                                    type: "get",
                                    data: {
                                        payment_status: $(this).val()
                                    },
                                    success: function(data) {
                                        $(".payment_status").html("Payment status " + data)
                                    }
                                })

                            })

                            $('#welcomeModal').modal('show');
                        });
                    </script>




@endsection
