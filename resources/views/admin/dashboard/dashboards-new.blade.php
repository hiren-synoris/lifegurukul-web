@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $user = new App\Models\User();
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
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
        @can('browse_instructors')
        <div class="col-md-3 col-sm-6 col-12">
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
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
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
                            <span class="text-success"> <i class="fas fa-arrow-up"></i> 12.5% </span>
                            <span class="text-muted">Since last week</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->

                    <div class="position-relative">
                        <div id="lineChart" style="height: 160;"></div>
                        {{-- <canvas id="visitors-chart" height="200"></canvas> --}}
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2"> <i class="fas fa-square text-primary"></i> This Week </span>

                        <span> <i class="fas fa-square text-gray"></i> Last Week </span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title">Latest Orders</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($lastCourses) @foreach ($lastCourses as $key => $course )
                                <tr>
                                    <td><a href="{{ route('courses.show', $course->id) }}">#{{ str_pad($course->id, 5, '0', STR_PAD_LEFT) }}</a></td>
                                    <td>{{ $course->title }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($course->status ?? 'pending');
                                            $badgeClass = match ($status) {
                                                'active', 'published' => 'success',
                                                'pending' => 'warning',
                                                'draft' => 'secondary',
                                                'cancelled' => 'danger',
                                                default => 'info',
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>
                                        <div class="sparkbar" data-color="#00a65a" data-height="20">₹ {{$course->default_web_price}}</div>
                                    </td>
                                </tr>
                                @endforeach @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    <a href="javascript:void(0)" class="btn btn-sm btn-info float-left">Place New Order</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right">View All Orders</a>
                </div>
                <!-- /.card-footer -->
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
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
                            <span class="text-success"> <i class="fas fa-arrow-up"></i> 33.1% </span>
                            <span class="text-muted">Since last month</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->

                    <div class="position-relative mb-4">
                        <canvas id="courseChart" height="200"></canvas>
                        {{-- <canvas id="sales-chart" height="200"></canvas> --}}
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2"> <i class="fas fa-square text-primary"></i> This year </span>

                        <span> <i class="fas fa-square text-gray"></i> Last year </span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Online Store Overview</h3>
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
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                        <p class="text-success text-xl">
                            <i class="ion ion-ios-refresh-empty"></i>
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="font-weight-bold"> <i class="ion ion-android-arrow-up text-success"></i> 12% </span>
                            <span class="text-muted">CONVERSION RATE</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                        <p class="text-warning text-xl">
                            <i class="ion ion-ios-cart-outline"></i>
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="font-weight-bold"> <i class="ion ion-android-arrow-up text-warning"></i> 0.8% </span>
                            <span class="text-muted">SALES RATE</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <p class="text-danger text-xl">
                            <i class="ion ion-ios-people-outline"></i>
                        </p>
                        <p class="d-flex flex-column text-right">
                            <span class="font-weight-bold"> <i class="ion ion-android-arrow-down text-danger"></i> 1% </span>
                            <span class="text-muted">REGISTRATION RATE</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('admin/plugins/chart.js/Chart.min.js')}}"></script>
<script src="{{ asset('admin/dist/js/demo.js')}}"></script>
<script src="{{ asset('admin/dist/js/pages/dashboard2.js')}}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('courseChart').getContext('2d');

    const courseChart = new Chart(ctx, {
        type: 'bar', // or 'line', 'doughnut', etc.
        data: {
            labels: ['Laravel', 'Vue.js', 'React', 'Node.js', 'Python'],
            datasets: [{
                label: 'Course Enrollments',
                data: [120, 90, 75, 60, 110],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var options = {
        chart: {
            type: 'line',
            height: 300
        },
        series: [{
            name: 'Enrollments',
            data: [45, 52, 38, 45, 19, 23, 30] // ← Your dynamic data
        }],
        xaxis: {
            categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] // ← Your dynamic labels
        },
        colors: ['#007bff']
    };

    var chart = new ApexCharts(document.querySelector("#lineChart"), options);
    chart.render();
});
</script>
