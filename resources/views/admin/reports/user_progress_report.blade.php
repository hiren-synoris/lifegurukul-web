@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
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
@endsection
@section('content')
    @if (Session::has('msg'))
        <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif

    <div id="filter_div" class="row">
        <div class="col-12">
            <div class="row">
                {{-- <div class="col-3">
                    <div class="form-group">
                        <input type="text" class="form-control float-right" name="created_date" id="created_date" placeholder="Select Date">
                    </div>
                    <div id="validation-errors1" style="color: red;"></div>
                </div> --}}

                {{-- <div class="col-2">
                    <select name="instructors" id="instructors" class="custom-select select3" required>
                        <option value="">Select Instructor</option>
                        @foreach ($instructor as $key => $value)
                            <option value="{{isset($value->user) ? $value->user->id:''}}">
                                {{ isset($value->user) ? $value->user->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="col-2">
                    <select name="course" id="course" class="custom-select select3" required>
                        <option value="">Select Course</option>
                        @foreach ($courses as $key => $value)
                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2">
                    <input class="btn btn-primary" type="button" id="fltr_search" value="Search">
                    <a href="" class="btn btn-primary">Clear</a>
                </div>
                <div class="col-8">
                    <button class="btn btn-primary float-right export_learner" style="display: nones">Export</button>
                </div>
            </div>
            <div class="row">
                <div class="col-3"></div>
                <div class="col-4" id="validation-errors" style="color: red;font-size: larger;"></div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
            <thead class="thead-light">
                <tr class="text-center">
                    <th scope="col">Mobile</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Enroll Date</th>
                    <th scope="col">Expire Date</th>
                    <th scope="col">Course Progress</th>
                    {{-- <th scope="col">Assigned Through</th> --}}
                    <th scope="col">Status</th>
                    {{-- <th scope="col">Expiry</th> --}}

                </tr>
            </thead>
        </table>

    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
        'switch' => 1,
        'dateRangePicker' => 1,
    ])
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.css">
    <script>
        $(document).ready(function() {

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

            $('#created_date').daterangepicker({
                startDate: moment().startOf('month'),
                endDate: moment(),
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });
            //$("#created_date").val('');

            $('#instructors').change(function() {
                $('#loader_section').show();
                var selectedValue = $(this).val();

                if (selectedValue !== '') {
                    $('#validation-errors').html('');
                }

                $.ajax({
                    url: "{{ route('get_instructor_courses') }}",
                    type: 'POST',
                    data: {
                        instructor_id: selectedValue
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#loader_section').hide();
                        console.log(response);
                        var courseDropdown = $('#course');
                        courseDropdown.empty();
                        courseDropdown.append('<option value="">Select Course</option>');

                        $.each(response, function(index, course) {
                            courseDropdown.append('<option value="' + course.id + '">' +
                                course.title + '</option>');
                        });
                    },
                    error: function(xhr) {
                        $('#loader_section').hide();
                        console.log('Error:', xhr.responseText);
                    }
                });
            });

            $('#course').change(function() {
                if ($(this).val() !== '') {
                    $('#validation-errors').html('');
                }
            });



            $(function() {
                $(".export_learner").click(function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to export Learner Activity Report?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Export'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var url =
                                "{{ URL::to('backoffice/learner-allCourse-progress') }}?" +
                                $.param({
                                    "course_id": $('#course').val()
                                })
                            window.location = url;
                        }
                    });

                });
            });


            $("#fltr_search").click(function() {
                $('#validation-errors1').html('');
                $('#validation-errors').html('');
                // var course = $('#course').val();

                table = $('#tbl_learners').DataTable({
                    "bDestroy": true,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('user_progress_report') }}",
                        data: function(d) {

                            d.course_id = $('#course').val();
                        },
                    },
                    dataSrc: function(json) {
                        if (json.recordsTotal == 0) {
                            $('.export_learner').hide();
                        } else {
                            $('.export_learner').show();
                        }
                        return json.data;
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    columns: [{
                            data: 'mobile',
                            name: 'mobile'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'expire_at',
                            name: 'expire_at'
                        },
                        {
                            data: 'course_progress',
                            name: 'course_progress'
                        },
                        {
                            data: 'order_status',
                            name: 'order_status'
                        }

                    ],
                    "order": [],
                });

            });
        });

        $(document).ready(function() {
            $('#instructors').select2();
        });

        $(document).ready(function() {
            $('#course').select2();
        });
    </script>


    <script></script>
@endsection
