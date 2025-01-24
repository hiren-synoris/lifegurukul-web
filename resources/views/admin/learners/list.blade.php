@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <style>
        #tbl_courses_enroll_wrapper {
            height: 510px;
        }

        .modal-dialog.modal-lg .modal-body {
            overflow: auto;
        }

        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            margin: 0 0 20px;
        }

        #exampleModal .modal-content {
            width: 130%;
        }
    </style>

    @includeIf('admin.layouts.partials.styles.style', [
        'dropzoneCSS' => 1,
        'select2CSS' => 1,
    ])
@endsection
@section('right-section')
    @php
        $learner = new App\Models\Learner();
    @endphp

    <!-- Contain Bulk Add functionalities-->
    <div style="display: none;" id="blk_import_frm">
        @includeIf('admin.layouts.partials.actions.bulk-import-data', [
            'bulkImportURL' => url('backoffice/courses/' . $learner->id . '/package/bulk_add'),
        ])
    </div>

    @if (Auth::user()->hasRole('admin'))
        @includeIf('admin.layouts.partials.buttons.bulk-import-sample', [
            'fileUrl' => asset('admin\sampleFiles\learner.xlsx'),
        ])
    @endif
    <!-- Bulk Add Button -->
    @if (Auth::user()->hasRole('admin'))
        @includeIf('admin.layouts.partials.buttons.bulk-import', [
            'importUrl' => url('backoffice/learners/import'),
        ])
    @endif
    <!-- Add Button -->
    @can('add_learners', $learner)
        @includeIf('admin.layouts.partials.buttons.add', [
            'addUrl' => url('backoffice/learners/create'),
        ])
    @endcan

    @if (Auth::user()->hasRole('admin'))
        @can('restore_learners')
            <!-- Show Deleted Button -->
            @includeIf('admin.layouts.partials.buttons.show-deleted')
        @endcan

        <!-- Bulk delete Button -->
        @can('delete_learners', $learner)
            @includeIf('admin.layouts.partials.buttons.bulk-delete')
            @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
        @endcan

        <!-- Restore Button -->
        @can('restore_learners', $learner)
            @includeIf('admin.layouts.partials.buttons.restore')
        @endcan
    @endif
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
            'bulkDelURL' => url('backoffice/learners/bulk_del'),
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/learners/restore_all'),
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
            'bulkHardDelURL' => route('bulk_hard_delete'),
        ])
    </div>

    <div id="filter_div" class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-2">
                    <select class="custom-select select3" id="filters">
                        <option selected value="">Add Filters</option>
                        <option value="1">Created date</option>
                        <option value="22">Gender</option>
                        <option value="2">Course enrolled</option>
                        <option value="3">Email</option>
                        <option value="4">Mobile number</option>
                        <option value="5">City</option>
                        <option value="6">State</option>
                        <option value="7">Country</option>
                        <option value="8">Age</option>
                        <option value="9">Device Count</option>
                    </select>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif



            @if (session()->has('failures'))
                <table class="table table-danger">
                    <tr>
                        <th>Row</th>
                        <th>Attribute</th>
                        <th>Errors</th>
                        <th>Value</th>
                    </tr>

                    @foreach (session()->get('failures') as $validation)
                        <tr>
                            <td>{{ $validation->row() }}</td>
                            <td>{{ $validation->attribute() }}</td>
                            <td>
                                <ul>
                                    @foreach ($validation->errors() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $validation->values()[$validation->attribute()] }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
            <div class="row my-5 d-none" id="filter_super_parent">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body pt-5 pl-5 pr-5 pb-2">
                            <form action="{{ url('backoffice/learners/search') }}" method="POST">
                                @csrf
                                <div class="row d-none">
                                    <div class="col-12 d-flex justify-content-center align-items-center ">
                                        <div class="col-3">
                                            <label for="signup_date">Created date </label>
                                        </div>
                                        <div class="col-8">
                                            <input type="" id="signup_date" class="form-control"
                                                name="signup_date" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'signup_date','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_gender">Gender</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select" style="width: 100%!important"
                                                id="fltr_select_gender">
                                                <option value="">Select Gender</option>
                                                <option value="1">Male</option>
                                                <option value="2">Female</option>
                                                <option value="3">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_gender','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $maximum_user_device = config()->has('settings.maximum_user_device')
                                        ? config('settings.maximum_user_device')
                                        : null;

                                @endphp
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_gender">Device Count</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select" style="width: 100%!important"
                                                id="fltr_select_device">
                                                <option value="">Select count</option>
                                                @for ($i = 1; $i <= $maximum_user_device; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                                {{-- <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option> --}}
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_device','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none mt-2">
                                    <div class="col-12 d-flex justify-content-center align-items-center ">
                                        <div class="col-3">
                                            <label for="signed_up_date">Course Enrolled</label>
                                        </div>
                                        <div class="col-8">
                                            {{-- <select class="custom-select select3" id="fltr_select_course" name="course"> --}}
                                            <select class="form-control custom-select select3" id="fltr_select_course"
                                                name="course[]" multiple>
                                                {{-- <option selected value="">Select Course</option> --}}
                                                @foreach ($courses as $value)
                                                    <option value="{{ $value->id }}">{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                            {{-- <input type="" id="fltr_select_course" class="form-control" name="signup_date" /> --}}
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_course','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="row d-none my-2">
                                <div class="col-12 d-flex align-items-center ">
                                    <div class="col-3">
                                        <label for="signed_up_date">Course Enrolled</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="custom-select select3" id="fltr_select_course" name="course">
                                            <option selected value="">Select Course</option>
                                            @foreach ($courses as $value)
                                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-1">
                                        <span class="" style="color: red !important;cursor: pointer !important;"
                                            onclick="remove($(this),'fltr_select_course','select')"><i
                                                class="far fa-times-circle"></i></span>
                                    </div>
                                </div>
                            </div> --}}
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="age">Age</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" placeholder="from" id="age" class="form-control"
                                                name="age" />
                                        </div>
                                        <div class="col-4">
                                            <input type="text" placeholder="To" id="ageTo" class="form-control"
                                                name="ageTo" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'age','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="email">Email</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="email" id="email" class="form-control" name="email" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'email','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center">
                                        <div class="col-3">
                                            <label for="mobile">Mobile Number</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" id="mobile" class="form-control" name="mobile"
                                                oninput="validateNumericInput(this)" />
                                        </div>
                                        <div class="col-1">
                                            <span class=""
                                                style="color: red !important; cursor: pointer !important;"
                                                onclick="remove($(this),'mobile','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_city">City</label>
                                        </div>
                                        <div class="col-8">
                                            {{-- <select class="custom-select select2 w-100 search_city"
                                            id="fltr_select_city" name="city"> --}}
                                            {{-- <option selected value="">Select City</option> --}}
                                            {{-- @foreach ($cities->chunk(10000) as $value)
                                            @foreach ($value as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                            @endforeach --}}
                                            {{--
                                        </select> --}}
                                            {{-- <input type="text" id="fltr_select_city" name="city"
                                            placeholder="Search City" class="form-control search_city" />
                                        <div class="card p-3">
                                            <div class="append_city"></div>
                                        </div> --}}
                                            <input type="text"
                                                class="form-control w-100 search_city autocompleteInput sl-label"
                                                id="fltr_select_city" name="city" placeholder="Search City">
                                            <input type="text" class="form-control w-100 search_city city_value sl-id"
                                                id="fltr_select_city" name="city" style="display: none;">
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'signup_date','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_state">State</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3 w-100" id="fltr_select_state"
                                                name="state">
                                                <option selected value="">Select State</option>
                                                @foreach ($states as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),
                                        'state','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_country">Country</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_country">
                                                <option selected value="">Select Country</option>
                                                @foreach ($countries as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'country','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start align-items-center row my-2 btn_search">
                                    <div class="col-2">
                                        <input class="btn btn-primary" type="button" id="fltr_search" value="Search">
                                        <a href="" class="btn btn-primary">Clear</a>
                                    </div>


                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Manage Course/Package</h5>
                    <h3 class="learner_id_" style="display: none"></h3>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <button class="btn btn-success float-right" data-toggle="modal" data-target="#enroll_course_">Enrol
                        Course/Package</button>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Coures name</th>
                                <th scope="col">Enroll Date</th>
                                <th scope="col">Expire Date</th>
                                <th scope="col">Progress</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="enroll_course_data">

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="enroll_course_" tabindex="-1" data-backdrop="static" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Enrol Course/Package</h5>

                    <button type="button" class="close close_tabel" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="tbl_courses_enroll" class="table table-bordered table-hove w-100 only_active">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th scope="col" data-orderable="false">Image</th>
                                <th scope="col">Title</th>
                                <th scope="col">Instructor Name</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created Date</th>
                                <!-- <th scope="col">Type</th> -->
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="enroll_course_plan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title coure_name" id=""></h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Select Plan</th>
                                <th scope="col">Plan name</th>
                                <th scope="col">Price</th>
                            </tr>
                        </thead>
                        <tbody id="enroll_course_manual_data">

                        </tbody>
                    </table>
                    <button class="btn btn-success float-right assign_plan">Submit</button>
                </div>

            </div>
        </div>
    </div>

    <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">learner Id</th>
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Mobile</th>
                {{-- <th scope="col">Manage Course/Package</th> --}}
                <th scope="col">Created at</th>
                {{-- <th scope="col">Login URL</th> --}}
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>

    <!-- Logged Device Modal code start-->
    <div class="modal fade" id="listLoggedDevice" tabindex="-1" aria-labelledby="listLoggedDeviceLabel"
        aria-hidden="true">
    </div>


    <div class="modal fade" id="logs_learner" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="exampleModalLabel">Enroll Learners {{ isset($course->title) && !empty($course->title) ? "For ".ucfirst($course->title) : "" }}</h5> --}}
                    <h5 class="modal-title" id="exampleModalLabel">Learner Logs</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-end">
                            @includeIf('admin.layouts.partials.buttons.bulk-add')
                        </div>
                        <div class="col-12">
                            <table id="logs_learners" class="table table-bordered table-hove w-100 only_active">
                                <thead class="thead-light">
                                    <th scope="col">Name</th>
                                    <th scope="col">Device name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">created_at</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="coins_learner" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="exampleModalLabel">Enroll Learners {{ isset($course->title) && !empty($course->title) ? "For ".ucfirst($course->title) : "" }}</h5> --}}
                    <h5 class="modal-title" id="exampleModalLabel">Coin History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-end">
                            @includeIf('admin.layouts.partials.buttons.bulk-add')
                        </div>
                        <div class="col-12">
                            <table id="coin_learners" class="table table-bordered table-hove w-100 only_active">
                                <thead class="thead-light">
                                    <th scope="col">Name</th>
                                    <th scope="col">Coins</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Course Name</th>
                                    <th scope="col">created_at</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>


    <!-- Logged Device Modal code end -->
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
        'switch' => 1,
    ])
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.css">
    <script>
        $(document).ready(function() {

            $(document).on("change", ".block_device", function() {
                $.ajax({
                    type: "get",
                    url: '{{ route('logged_device_block') }}',
                    data: {
                        id: $(this).attr("data-id"),
                        lerner_id: $(this).attr("data-lerner_id"),
                        status: $(this).is(":checked")

                    },
                    success: function(data) {
                        Swal.fire({
                            title: "Success",
                            text: data.content,
                            icon: "success"
                        });
                    }
                })
            })


            $(document).on("click", ".coin_learners_get", function() {
                ids = $(this).attr("data-id")

                $('#coin_learners').DataTable({
                    "bDestroy": true,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('get_coin') }}",
                        data: function(d) {
                            d.learner_id = ids
                        }
                    },

                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],

                    columns: [

                        {
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'coins',
                            name: 'coins'
                        },
                        {
                            data: 'type',
                            name: 'type'
                        },
                        {
                            data: 'course_id',
                            name: 'course_id'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },


                    ],
                    "order": [],


                })
            })
            $(document).on("click", ".logs_learners_get", function() {
                ids = $(this).attr("data-id")

                $('#logs_learners').DataTable({
                    "bDestroy": true,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('learner_logs') }}",
                        data: function(d) {
                            d.learner_id = ids
                        }
                    },

                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],

                    columns: [

                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'device_name',
                            name: 'device_name'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },

                    ],
                    "order": [],


                })
            })




        })
        $(document).ready(function() {
            tables = ""
            table_learner = ""

            $(".assign_plan").click(function() {

                $('#loader_section').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: "{{ route('learner_manual_plan_assign') }}",
                    type: "post",
                    data: {
                        learner_id: $(".learner_id_").text(),
                        plan_id: $("input[name='active_plan']:checked").val()
                    },
                    success: function(data) {
                        $('#loader_section').hide();
                        $("#exampleModal").modal("hide")
                        Swal.fire({
                            title: data.type,
                            text: "Do you want to enrol in another course?",
                            icon: "success",
                            showCancelButton: true,
                            confirmButtonColor: "##28a745",
                            cancelButtonColor: "#d7300a",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No"
                        }).then((result) => {
                            if (!result.isConfirmed) {
                                Swal.fire({
                                    title: "Alright!",
                                    // text: "",
                                    icon: "success"
                                });
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            } else {
                                $("#enroll_course_plan").modal("hide")
                                table.ajax.reload()
                            }
                        });

                    }
                })
            })

            $(document).on("click", ".close_tabel", function() {
                table_learner.ajax.reload()
                tables.ajax.reload()
                $("#tbl_courses_enroll").dataTable().fnDestroy();

            })

            $(document).on("click", ".login_url", function() {
                $(this).hide();
                var id = $(this).data("id");
                $('.spinner_' + id).show();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: "{{ route('learner_generate_url') }}",
                    type: "post",
                    data: {
                        learner_id: id,
                    },
                    success: function(data) {
                        $('.spinner_' + id).hide();
                        $('.show_text_' + id).hide();
                        $('.show_url_' + id).show();
                    }
                })


            })
            $(document).on("click", ".learner_course_", function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: "{{ route('learner_manual_enroll_course') }}",
                    type: "post",
                    data: {
                        learner_id: $(".learner_id_").text(),
                        course_id: $(this).data("course_id")
                    },
                    success: function(data) {
                        $("#enroll_course_manual_data").empty()
                        $("#enroll_course_plan").modal("show")
                        $(".coure_name").html(data.course)

                        if (data.plan.length != 0) {
                            $.each(data.plan, function(key, val) {
                                $("#enroll_course_manual_data").append(
                                    "<tr><td><input type='radio' name='active_plan' value=" +
                                    val.id + " checked ></td><td>" + val.plan_name +
                                    "</td> <td>" + val.final_payable_price +
                                    "</td></tr>")
                            })
                        } else {
                            $("#enroll_course_manual_data").append(
                                "<h5 class='text-center mt-2' style='margin-right: -210px;'>Not found</h5>"
                            )
                        }
                    }
                })
            })

            $('#enroll_course_').on('shown.bs.modal', function() {

                tables = $('#tbl_courses_enroll').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // ajax: "{{ url('backoffice/get-courses') }}",
                    ajax: {
                        url: "{{ url('backoffice/get-courses') }}",
                        data: function(d) {
                            d.enroll_course = true;
                            d.learner_id = $(".learner_id_").text();
                        },
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],

                    columns: [

                        {
                            data: 'image',
                            name: 'image'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'instructor_id',
                            name: 'instructor_id'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        // {
                        //     data: 'type',
                        //     name: 'type'
                        // },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    "order": [],
                });

            });







            var citys = @json($cities)

            $(".autocompleteInput").autocomplete({
                source: '/backoffice/search-city',
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


            // $("body").on('autocompleteselect', 'input.autocompleteInput', function (event,ui)     {
            //         // alert(ui.item.label);
            //         //alert(ui.item.value);
            //         console.log(ui.item);

            //     });



        })

        $(function() {
            $('#signup_date').datepicker({
                dateFormat: 'dd/mm/yy',
                // startDate: '-3d'
            });
        });
    </script>


    <script>
        //  $('.search_city').select2();
        let table;

        let filter_data = {
            signup_date: "",
            course: "",
            city: "",
            state: "",
            country: "",
            email: "",
            mobile: "",
            gender: "",
            age: "",
            ageTo: "",
            device_count: ""
        };

        $(document).ready(function() {

            function getQueryParameter(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }


            const mobileNo = getQueryParameter('mobile_no');

            console.log("Mobile No: " + mobileNo);

            table_learner = $('#tbl_learners')
                .DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ url('backoffice/get-learner') }}",
                        data: function(d) {

                            d.mobile_no = mobileNo;

                            $("#main_checkbox").prop("checked", false);
                            $(".children_checkbox").prop("checked", false);
                            $('#delete-btn, #bulk-add-btn').addClass('d-none')

                            $('#bulk_delete_frm [name^=bd]').attr("name", "");
                            $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");

                            return $.extend(d, filter_data);
                        },
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    "drawCallback": function(settings) {
                        // if ($("#main_checkbox").is(":checked")) {
                        //     $("#main_checkbox").trigger("click");
                        //     $("#main_checkbox").prop("checked", true);
                        // } else {
                        //     $("#main_checkbox").prop("checked", false);
                        // }
                        $('#loader_section').hide();
                    },
                    columns: [

                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                    data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                            }
                        },
                        {
                            data: 'id',
                            name: 'id',
                        },
                        {
                            data: 'profile_pic',
                            name: 'profile_pic'
                        }, {
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'mobile',
                            name: 'mobile'
                        },
                        // {
                        //     data: 'enroll_course',
                        //     name: 'enroll_course'
                        // },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        // {
                        //     data: 'login_url',
                        //     name: 'login_url',
                        //     orderable: false,
                        //     searchable: false
                        // },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    "order": []
                });
            table = $('#tbl_learners_failures')
                .DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ url('backoffice/learners/import') }}",
                        data: function(d) {
                            return $.extend(d, filter_data);
                        }
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    // "drawCallback": function( settings ) {
                    //     if ($("#main_checkbox").is(":checked")) {
                    //         $("#main_checkbox").trigger("click");
                    //         $("#main_checkbox").prop("checked", true);
                    //     }
                    //     else{
                    //         $("#main_checkbox").prop("checked", false);
                    //     }
                    // },
                    columns: [{
                            data: 'failure',
                            name: 'row'

                        },
                        // {
                        //     data: '$failure->attribute()',
                        //     name: 'attribute'
                        // },
                        // {
                        //     data: '$failure->errors()',
                        //     name: 'errors'
                        // }, {
                        //     data: '$failure->values()',
                        //     name: 'values',
                        //     orderable: false,
                        //     searchable: false
                        // },

                    ],
                    "order": []
                });

            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {

                $("#main_checkbox").prop("checked", false);
                $(".children_checkbox").prop("checked", false);
                if (state == true) {
                    table_learner.destroy();
                    $("#main_checkbox").prop("checked", false);
                    $('#hard-delete-btn').addClass('d-none');
                    table_learner = $('#tbl_learners').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-learner') }}",
                        columnDefs: [{
                            className: 'text-center',
                            targets: '_all'
                        }],
                        "drawCallback": function(settings) {
                            // if ($("#main_checkbox").is(":checked")) {
                            //     $("#main_checkbox").trigger("click");
                            //     $("#main_checkbox").prop("checked", true);
                            // } else {
                            //     $("#main_checkbox").prop("checked", false);
                            // }
                            $('#bulk_delete_frm [name^=bd]').attr("name", "");
                            $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                        },
                        columns: [{
                                data: 'id',
                                name: 'id',
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                        data + ']" data-id=' + data +
                                        ' style="cursor: pointer;"/>';
                                }
                            },

                            {
                                data: 'id',
                                name: 'id',
                            },

                            {
                                data: 'profile_pic',
                                name: 'profile_pic'
                            }, {
                                data: 'name',
                                name: 'name'
                            }, {
                                data: 'email',
                                name: 'email'
                            }, {
                                data: 'mobile',
                                name: 'mobile'
                            },
                            // {
                            //     data: 'enroll_course',
                            //     name: 'enroll_course'
                            // },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            },
                            // {
                            //     data: 'login_url',
                            //     name: 'login_url',
                            //     orderable: false,
                            //     searchable: false
                            // },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ],
                        "order": []
                    });
                    $('#tbl_learners').removeClass('only_deleted');
                    $('#tbl_learners').addClass('only_active');
                } else {
                    table_learner.destroy();
                    // alert()
                    table_learner = $('#tbl_learners').DataTable({
                        "bDestroy": true,
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        // ajax: "{{ url('backoffice/get-learner-deleted') }}",
                        ajax: {
                            url: "{{ url('backoffice/get-learner-deleted') }}",
                            data: function(d) {
                                $("#main_checkbox").prop("checked", false);
                                $('#hard-delete-btn').addClass('d-none');
                                $('#restore-btn').addClass('d-none');
                                $('#bulk_delete_frm [name^=bd]').attr("name", "");
                                $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                                return $.extend(d, filter_data);
                            }
                        },
                        columnDefs: [{
                            className: 'text-center',
                            targets: '_all'
                        }],
                        "drawCallback": function(settings) {
                            // if ($("#main_checkbox").is(":checked")) {
                            //     $("#main_checkbox").trigger("click");
                            //     $("#main_checkbox").prop("checked", true);
                            // } else {
                            //     $("#main_checkbox").prop("checked", false);
                            // }
                        },
                        columns: [{
                                data: 'id',
                                name: 'id',
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                        data + ']" data-id=' + data +
                                        ' style="cursor: pointer;"/>';
                                }
                            },
                            {
                                data: 'id',
                                name: 'id',
                            },
                            {
                                data: 'profile_pic',
                                name: 'profile_pic'
                            }, {
                                data: 'name',
                                name: 'name'
                            }, {
                                data: 'email',
                                name: 'email'
                            }, {
                                data: 'mobile',
                                name: 'mobile'
                            },
                            // {
                            //     data: 'enroll_course',
                            //     name: 'enroll_course'
                            // },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            },
                            // {
                            //     data: 'login_url',
                            //     name: 'login_url',
                            //     orderable: false,
                            //     searchable: false
                            // },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ],
                        "order": []
                    });
                    $('#tbl_learners').removeClass('only_active');
                    $('#tbl_learners').addClass('only_deleted');
                }
            });
        });

        $(document).on("change", "#filters", function() {
            let selected_val = parseInt($(this).find(":selected").val());
            switch (selected_val) {
                case 1:
                    make_visible("signup_date");
                    break;
                case 2:
                    make_visible("fltr_select_course");
                    break;
                case 3:
                    make_visible("email");
                    break;
                case 4:
                    make_visible("mobile");
                    break;
                case 5:
                    make_visible("fltr_select_city");
                    break;
                case 6:
                    make_visible("fltr_select_state");
                    break;
                case 7:
                    make_visible("fltr_select_country");
                    break;
                case 22:
                    make_visible("fltr_select_gender");
                    break;
                case 8:
                    make_visible("age");
                    break;
                case 9:
                    make_visible("fltr_select_device");
                    break;

                default:
                    break;
            }
        });

        $(document).on("change", ".learner_status_cls", function() {


            // return false;
            var origin = "{{ route('learner_status') }}";

            $.ajax({
                url: origin,
                type: "get",
                data: {
                    status: $(this).is(":checked") == true ? 1 : 0,
                    id: $(this).data('id')
                },
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(response) {
                    $('#loader_section').hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'success',
                        text: 'Learner status updated successfully',
                    });
                },
                error: function(jqxhr, error, errorThrown) {
                    if (jqxhr.status == 403) {
                        Swal.fire(
                            'Unauthorized',
                            '',
                            'error'
                        );
                    }
                },
            })
        });

        function remove(self, id, type) {

            var select2Element = $('#filters')
            select2Element.val([""]).trigger('change');
            $("#" + id).val("")
            if (id == "age") {
                $("#ageTo").val("")
            }
            self.parents().eq(2).addClass('d-none');
            filter_data[id] = "";
            let total = $("#filter_super_parent").find("form").find('.row:visible').not(".btn_search").length;
            // alert(total)
            if (total <= 0) {
                // console.log($("#filter_super_parent").find("form").find("select"));
                $("#filter_super_parent").addClass('d-none');
                $("#filter_super_parent").find("form").find("select").prop('selectedIndex', "");
                $("#filter_super_parent").find("form").find("input").not("#fltr_search").val("");
                filter_data.gender = ""
                filter_data.device_count = ""
                table_learner.ajax.reload();

            }

        }

        function make_visible(name) {
            if ($("#filter_super_parent").hasClass('d-none')) {
                $("#filter_super_parent").removeClass('d-none');
            }
            $("#" + name).parents().closest('.row').removeClass('d-none');
            // $("#"+name).prop('required',true);
        }

        $(document).on("click", "#fltr_search", function() {
            let signup_date = $("#signup_date").val();
            let course = $("#fltr_select_course").val();
            let city = $(".city_value").val();
            // alert(city)
            let state = $("#fltr_select_state").find(":selected").val();
            let country = $("#fltr_select_country").find(":selected").val();
            let gender = $("#fltr_select_gender").find(":selected").val();
            let device_count = $("#fltr_select_device").find(":selected").val();
            let email = $("#email").val();
            let mobile = $("#mobile").val();
            let age = $("#age").val();
            let ageTo = $("#ageTo").val();

            filter_data.signup_date = signup_date;
            filter_data.course = course;
            filter_data.city = city;
            filter_data.state = state;
            filter_data.country = country;
            filter_data.email = email;
            filter_data.mobile = mobile;
            filter_data.gender = gender;
            filter_data.age = age;
            filter_data.ageTo = ageTo;
            filter_data.device_count = device_count;


            table_learner.ajax.reload();
        });

        $(document).on("click", ".loggedDevice", function() {
            var formAction = $(this).attr('data-url');
            var id = $(this).attr('data-id');
            $.ajax({
                url: formAction,
                type: "GET",
                dataType: "json",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(result) {
                    $('#loader_section').hide();
                    if (result.status == 'success') {
                        $('#listLoggedDevice').html(result.content).modal('show');
                    }
                },
            });
        });
        $(document).ready(function() {
            $('.select3').select2();
        })

        function validateNumericInput(input) {
            input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        }

        $(document).on("click", ".delete_courses", function() {


            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, uneroll it!"
            }).then((result) => {

                if (result.isConfirmed) {

                    var leaner_id = $(this).data("leaner_id");
                    var course_id = $(this).data("course_id");
                    var id = $(this).data("user_id");

                    var url = "{{ route('learners.courses.delete', ['id' => ':id']) }}".replace(':id', leaner_id);
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    $.ajax({
                        url: url,
                        type: "delete",
                        data:{
                            course_id:course_id
                        },

                        success: function(data) {
                            Swal.fire({
                                title: data,
                                icon: "success"
                            });
                            $(".hide_btn_" + id).hide()
                        }
                    })
                }
            });



        });

        $(document).on("click", ".reset_learner", function() {

            var learnerId = $(this).data("leaner_id");
            var courseId = $(this).data("course_id");
            var id = $(this).attr("data-usercourse_id");


            var url =
                "{{ route('reset.learner', ['courseId' => ':courseId', 'learnerId' => ':learnerId']) }}"
                .replace(':courseId', courseId)
                .replace(':learnerId', learnerId);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            $.ajax({
                url: url,
                type: "get",

                success: function(data) {
                    Swal.fire({
                        title: data,
                        icon: "success"
                    });


                    $(".progress_reset_" + id).html("0%")
                }

            })
        });
        $(document).on("click", ".enroll_course", function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            $.ajax({
                url: "{{ route('learner_enroll_course') }}",
                type: "post",
                data: {
                    learner_id: $(this).data("id")
                },
                success: function(data) {
                    $("#enroll_course_data").empty()
                    $(".learner_id_").html(data.learner_id)
                    if (data.userCourse.length !== 0) {
                        $.each(data.userCourse, function(key, val) {
                            var actions = '';
                            if (val.actions) {
                                actions = '<div class="d-flex justify-content-center">' + val
                                    .actions + '</div>';
                            }
                            $("#enroll_course_data").append("<tr class='hide_btn_" + val.id +
                                "'><td>" + val.course_title +
                                "</td><td>" + val.formatted_created_at + "</td><td>" + val
                                .formatted_expire_at + "</td><td class='progress_reset_" +
                                val.id +
                                "'>" + val.progress +
                                "</td><td>" + actions + "</td></tr>");
                        });
                    } else {
                        $("#enroll_course_data").append("<h5 class='text-center mt-2'>Not found</h5>");
                    }
                }
            })
        });

        function copyUrl(url) {
            console.time('time1');
            var temp = $("<input>");
            $("body").append(temp);
            temp.val(url).select();
            document.execCommand("copy");
            alert("URL copied!");
            temp.remove();
            console.timeEnd('time1');
        }
    </script>
@endsection
