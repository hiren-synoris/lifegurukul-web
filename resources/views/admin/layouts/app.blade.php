<!DOCTYPE html>
@php
    use Carbon\Carbon;
@endphp
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} @isset($title)
            | {{ 'title' }}
        @endisset
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(env('APP_ENV')=="development")
        <meta name="robots" content="noindex">
    @endif
    <!-- AdminLTE default styles-->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Font Awesome -->
    {{-- <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}"> --}}
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('front/img/favicon.png') }}">
    <!-- Custom CSS-->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/custom.css') }}?var={{ time() }}">

    <link rel="stylesheet" href="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <!-- Customs CSS section -->
    @yield('styles')

</head>
@php
    //dd(request()->route()->getName());
@endphp
@if (request()->routeIs('courses.builder'))

    <body class="hold-transition sidebar-mini course-builder-sidebar">
    @else

        <body class="hold-transition sidebar-mini">
@endif

<div class="wrapper">

    <!-- Header file -->
    @includeIf('admin.layouts.partials.header')
    <!-- Header file -->
    {{-- @includeIf('admin.layouts.partials.sidebar') --}}


    {{-- @if (request()->routeIs('courses.*') && !request()->routeIs('courses.index') && !request()->routeIs('courses.create'))
            @if (request()->routeIs('courses.builder'))
                @includeIf('admin.layouts.partials.sidebar.course_builder')
            @elseif(request()->routeIs('courses.preview'))
                @includeIf('admin.layouts.partials.sidebar.course_preview')
            @else
                @includeIf('admin.layouts.partials.sidebar.course_general')
            @endif
        @else
            @includeIf('admin.layouts.partials.sidebar.common')
        @endif --}}

    @if (
        (request()->routeIs('courses.*') &&
            !request()->routeIs('courses.index') &&
            !request()->routeIs('courses.create')) ||
            (request()->routeIs('packages.*') &&
                !request()->routeIs('packages.index') &&
                !request()->routeIs('packages.create')) ||
            request()->routeIs('courses.public-forum'))
        @if (request()->routeIs('courses.builder'))
            @includeIf('admin.layouts.partials.sidebar.course_builder')
        @elseif(request()->routeIs('courses.preview'))
            @includeIf('admin.layouts.partials.sidebar.course_preview_sidebar')
            <!--
@elseif(request()->routeIs('public-forum.*'))
@includeIf('admin.layouts.partials.sidebar.public_forum_sidebar') -->
        @else
            @includeIf('admin.layouts.partials.sidebar.course_general')
        @endif
    @else
        @includeIf('admin.layouts.partials.sidebar.common')
    @endif

    <!-- Main content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-3">
                        <h4 class="m-0">
                            @if (isset($pg_header))
                                {{ ucwords($pg_header) }}
                            @endif
                        </h4>
                    </div>
                    <div class="col-sm-9 d-flex flex-end justify-content-end align-items-center">

                        @yield('right-section')

                        {{-- @if (strpos($_SERVER['REQUEST_URI'], 'create') || strpos($_SERVER['REQUEST_URI'], 'edit'))
                            <a class="btn btn-warning px-2 py-1" href="{{ url()->previous() }}"><i class="fas fa-arrow-circle-left pr-2"></i> Back</a>
                                @endif --}}

                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row pb-5">
                    <div class="col-12">
                        <!--Notification Area-->
                        @if (Session::has('notification') && is_array(session('notification')) && session('notification')['type'] == 'bs4-alert')
                            <div class="alert alert-{{ $notification['status'] }} alert-dismissible fade show"
                                role="alert">
                                {{ $notification['msg'] }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!--Content goes here-->
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer file -->
    @includeIf('admin.layouts.partials.footer')

</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_conf" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <a href="javascript:void(0)" id="delete_conf_yes" type="button" class="btn btn-primary">Yes</a>
            </div>
        </div>
    </div>
</div>

<!-- Bulk delete Modal -->
<div class="modal fade" id="bulk_delete_conf" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <a href="javascript:void(0)" id="bluk_delete_conf_yes" type="button"
                    class="btn btn-primary">Yes</a>
            </div>
        </div>
    </div>
</div>
<!-- Delete form-->
<form action="" method="POST" id="delete_frm" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="submit">
</form>

<!-- Bulk Delete form-->
<form action="" method="POST" id="bulk_delete_frm" style="display: none;">
    @csrf
    @method('POST')
    <input type="submit">
</form>

<!-- Permanent Delete form-->
<form action="" method="POST" id="permanent_delete_frm" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="submit">
</form>

@if (request()->routeIs('learners.index'))
    {{-- @php dd(request()->route()->getName()); @endphp --}}
    <!-- Import Modal -->
    <div class="modal fade" id="import_conf" data-backdrop="static" tabindex="-1"
        aria-labelledby="import_conf_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_conf_title">Import Learner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="learners/importlearner" method="POST" id="import_frm" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        @method('POST')
                        <input type="file" name="import" class="import" id="import">
                        <input type="hidden" name="learnerId" id="learnerId"><br>
                        <small class="text-gray">(Upload files with .xls or .xlsx extensions only)</small><br>
                        <span class="error_image" style="color:red;display:none">The file is required </span>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <a href="javascript:void(0)" id="import_conf_yes" type="submit"
                            class="btn btn-primary">Submit</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    @if (isset($course->id) && isset($course->plans) && $course->plans->count() > 0)
        <!-- Import Modal -->
        <div class="modal fade" id="import_conf" data-backdrop="static" tabindex="-1"
            aria-labelledby="import_conf_title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="delete_conf_title">Import Learner ABC
                            {{ isset($course->title) && !empty($course->title) ? 'For ' . ucfirst($course->title) : '' }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="learners/import/{{ $course->id ?? '' }}" method="POST" id="import_frm"
                        enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            @method('POST')
                            <input type="file" name="import" class="import" id="import">
                            <div>
                                <span class="error_image" style="color:red;display:none"> </span>
                            </div>
                        </div>


                        <div class="modal-body p-0">
                            <div class="card mb-0">
                                <div class="card-body">
                                    <input type="hidden" name="learnerId" id="learnerId">

                                    <!-- <h4 class="subs-title text-center">Select Plan</h4> -->
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col"></th>
                                                    <th scope="col">Plan Name</th>
                                                    <th scope="col" class="text-center">Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($course->plans as $key => $value)
                                                    @php
                                                        $valid_till = '';
                                                        $date = new DateTime();
                                                        $date1 = new DateTime();
                                                        $currentDate = now();
                                                        // if ($value->access_value === null || $value->access_value === '' || $value->access_value >= Carbon::now()) {
                                                        if ($value->course_limit == 1) {
                                                            if (
                                                                isset($value->is_fixed_date) &&
                                                                $value->access_value != null
                                                            ) {
                                                                if ($value->is_fixed_date == 2) {
                                                                    // add number of days
                                                                    $date->modify('+' . $value->access_value . ' days');
                                                                    $expiredAt = $date->format('Y-m-d');
                                                                }
                                                                if ($value->is_fixed_date == 1) {
                                                                    // add expiredate
                                                                    $expiredAt = $value->access_value;
                                                                }

                                                                if ($currentDate->format('Y-m-d') <= $expiredAt) {
                                                                    $valid_till = $expiredAt;
                                                                }
                                                            }
                                                        } else {
                                                            $valid_till = 'LifeTime';
                                                    } // } // dump($expiredAt); @endphp @if ($valid_till != '')
                                                        <tr>
                                                            <td><input type="radio" class="multi_import_checkbox"
                                                                    name="planId" id="plan_{{ $value->id }}"
                                                                    value="{{ $value->id }}"
                                                                    {{ $key == 0 ? 'checked' : '' }}></td>
                                                            <td>{{ $value->plan_name ?? '' }}</td>
                                                            <td class="text-center">
                                                                {{-- data-plan-number="{{ $checkoutArray['plan_number'] }}" --}}
                                                                <p>&#8377;{{ $value->final_payable_price }}</p>
                                                                {{-- <a class="btn btn-primary checkout-btn plans_cls" href="javascript:void(0)" onclick="add_multi({{ $value->id }})" data-plan-id="{{ $value->id }}" data-url={{ route('checkout.payment.session.store') }}>Buy for &#8377;{{ $value->final_payable_price }}</a> --}}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <a href="javascript:void(0)" id="import_conf_yes" type="submit"
                                class="btn btn-primary">Submit</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="modal fade" id="import_conf" data-backdrop="static" tabindex="-1"
            aria-labelledby="import_conf_title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="delete_conf_title">Alert</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Please add pricing first</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endif

<!-- Permanent Delete Modal -->
<div class="modal fade" id="permanent_delete_conf" data-backdrop="static" tabindex="-1"
    aria-labelledby="delete_conf_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <a href="javascript:void(0)" id="permanent_delete_conf_yes" type="button"
                    class="btn btn-primary">Yes</a>
            </div>
        </div>
    </div>
</div>
<!-- Bulk Permanent Delete form-->
<form action="" method="POST" id="bulk_hard_delete_frm" style="display: none;">
    @csrf
    @method('POST')
    <input type="submit">
</form>

<!-- Bulk Permanent delete Modal -->
<div class="modal fade" id="bulk_hard_delete_conf" data-backdrop="static" tabindex="-1"
    aria-labelledby="delete_conf_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete Permanently?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <a href="javascript:void(0)" id="bluk_hard_delete_conf_yes" type="button"
                    class="btn btn-primary">Yes</a>
            </div>
        </div>
    </div>
</div>
<!-- Delete form-->



<!-- AdminLTE default JS -->
<!-- jQuery -->
<script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
{{-- <script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script> --}}
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="{{ asset('admin/plugins/sortable-draggable/scripts/script.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>

<script>
    $(document).ready(function() {
        $('#import_conf').on('show.bs.modal', function() {
            // Find all radio buttons of type "radio" within the modal and set "checked" to false
            // $('#import_conf input[type="radio"]').prop('checked', false);
        });
    });

    var nextDayDate = new Date();
        nextDayDate.setDate(nextDayDate.getDate() - 1);
        console.log(nextDayDate);
        $(".d_o_b").datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
            endDate: nextDayDate,
        });
    $("#import_frm").on('submit', (function(e) {
        // e.preventDefault();
        var fileName = $(".import").val();

        if ($(".import").val() == '') {
            $(".error_image").text("The file is required").show()
            return false
        } else if (fileName.endsWith('.xls') || fileName.endsWith('.xlsx') || fileName.endsWith('.csv')  ) {

            $(".error_image").hide();
            return true
        } else {
            $(".error_image").text("Please select a valid Excel file with .xls or .xlsx extension.").show();
            $(".import").val('');
            return false
        }

    }));

    $(document).ready(function() {
        $(document).ajaxStart(function() {
            $('#loader_section').show();
        }).ajaxStop(function() {
            $('#loader_section').hide();
        });



        $(document).ajaxError(function(event, jqxhr, settings, exception) {
            // if (exception == 'Unauthorized') {
            //     bootbox.confirm(
            //         "Your session has expired. Would you like to be redirected to the login page?",
            //         function(result) {
            //             if (result) {
            //             }
            //         });
            //     }

                // window.location = '/backoffice/login';
        });
        $.fn.dataTable.ext.errMode = 'none';
    });

    $(document).on("click", "#bulk-import-btn", function() {
        $(".error_image").hide();
    })

    function delete_confirmation(path) {
        $("#delete_frm").attr('action', path);
        $("#delete_conf").modal('show');
    }
    $(document).on("click", "#delete_conf_yes", function() {
        console.log($("#delete_frm").attr('action'));
        $("#delete_frm").submit();
    });

    function permanent_delete_confirmation(path) {
        $("#permanent_delete_frm").attr('action', path);
        $("#permanent_delete_conf").modal('show');
    }
    $(document).on("click", "#permanent_delete_conf_yes", function() {
        console.log($("#permanent_delete_frm").attr('action'));
        $("#permanent_delete_frm").submit();
    });


    function bulk_delete_confirmation(path) {
        $("#bulk_delete_frm").attr('action', path);
        $("#bulk_delete_conf").modal('show');
    }

    $(document).on("click", "#bluk_delete_conf_yes", function() {
        $("#bulk_delete_frm").submit();
    });

    function bulk_hard_delete_confirmation(path) {
        $("#bulk_hard_delete_frm").attr('action', path);
        $("#bulk_hard_delete_conf").modal('show');
    }

    $(document).on("click", "#bluk_hard_delete_conf_yes", function() {
        $("#bulk_hard_delete_frm").submit();
    });

    function import_learner(path) {


        $('#import_conf input[type="file"]').val(''); // Clear selected file
        $("#import_conf").modal('show');
        $("#import_frm").attr('action', path);

    }

    $(document).on("click", "#import_conf_yes", function(e) {
        $("#import_frm").attr('action');
        $("#import_frm").submit();
    });
</script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $("#file_error").remove();

    if (window.performance) {
        var navEntries = window.performance.getEntriesByType('navigation');
        if (navEntries.length > 0 && navEntries[0].type === 'back_forward') {
            //  alert ("back");
        } else if (window.performance.navigation && window.performance.navigation.type == window.performance.navigation
            .TYPE_BACK_FORWARD) {
            // alert ("back forward");
        } else {
            @if (session('notification') &&
                    is_array(session('notification')) &&
                    count(session('notification')) > 0 &&
                    session('notification')['type'] == 'sweet-alert')
                Swal.fire({
                    icon: "{{ session('notification')['status'] }}",
                    title: "{{ session('notification')['title'] }}",
                    text: "{{ session('notification')['msg'] }}",
                });
            @endif
        }
    }

    function max_upload_size(size = 500, unit = "kb") {
        let t1 = size;
        let t2 = unit;
        $('form').submit(function(e) {
            var img_size = $('form').find('input[type="file"]')[0].files[0].size;
            if (img_size > 500000) {
                e.preventDefault();
                $("#file_error").remove();
                $('form').find('input[type="file"]').closest(".form-group").append(
                    "<p id='file_error' style='color:red'>File size can not be greater than " + t1 + t2 +
                    "</p>");
            }
        });
    }

    function delete_question(path) {
        $("#bulk_delete_conf").modal('show');
        ("#bulk_delete_frm").attr('action', path);
    }
</script>

<!-- Custom JS section-->
@yield('scripts')
</body>

</html>
