@extends('admin.layouts.app')
@section('styles')
    <style>
        .text-truncate {
            max-width: 250px;
        }
    </style>
    @includeIf('admin.layouts.partials.styles.style', [
        'select2CSS' => 1,
    ])
@endsection
@section('right-section')
    <?php
    $course = new App\Models\Course();
    ?>

    <!-- Add Button -->
    @can('add_courses', $course)
        @includeIf('admin.layouts.partials.buttons.add', [
            'addUrl' => url('backoffice/courses/create'),
            'addBtnText' => 'Create Course',
            'addTitleText' => 'Create Course',
        ])
    @endcan
    @if (Auth::user()->hasRole('admin'))
        @can('restore_courses')
            <!-- Show Deleted Button -->
            @includeIf('admin.layouts.partials.buttons.show-deleted')
        @endcan

        <!-- Bulk delete Button -->
        @can('delete_courses', $course)
            @includeIf('admin.layouts.partials.buttons.bulk-delete')
        @endcan

        <!-- Restore Button -->
        @can('restore_courses', $course)
            @includeIf('admin.layouts.partials.buttons.restore')
            <!-- Bulk Hard delete Button -->
            @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
        @endcan
    @endif
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
            'bulkDelURL' => url('backoffice/courses/bulk_del'),
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
            'bulkHardDelURL' => url('backoffice/courses/bulk_hard_del'),
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/courses/restore_all'),
        ])
    </div>
    @if (Session::has('msg'))
        <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif
    {{-- @if ($user->hasRole('admin')) --}}
    <div class="row mb-4">
        @if ($user->hasRole('admin'))
            <div class="col-12">
                <h5>Filter by Instructors</h5>
                <form action="javascript:void(0)" class="justify-content-between" method="get">
                    <div class="row">
                        <div class="col-3">
                            <select name="users" id="instructors" class="custom-select" onchange="$(this)">
                                <option value="">Select Instructor</option>
                                @foreach ($users as $key => $value)
                                    <option value="{{ isset($value->user) ? $value->user->id : '' }}">
                                        {{ isset($value->user) ? $value->user->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <select name="status" id="status" class="custom-select" onchange="$(this)">
                                <option value="">Select Status</option>
                                <option value="1">Published</option>
                                <option value="0">Unpublished</option>
                            </select>
                        </div>
                        <div class="col-6"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                            <a href="" class="btn btn-primary">Clear</a>
                        </div>
                        @can('courses_report')
                            <div class="col-1"><button class='btn btn-primary text-white export_course'>Export</button></div>
                        @endcan
                    </div>
                </form>
            </div>
        @endif
    </div>
    <form action="{{ url('backoffice/get-courses') }}" style="display: none;" id="frm"></form>
    <table id="tbl_courses" class="table table-bordered table-hove w-100 only_active tbl_courses">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col" data-orderable="false">Course id</th>
                <th scope="col">Title</th>
                <th scope="col">Instructor Name</th>
                <th scope="col">Active Plan</th>
                <th scope="col">Enroll Learner</th>
                <th scope="col">Status</th>
                <th scope="col">Created Date</th>
                <!-- <th scope="col">Type</th> -->
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
        'select2' => 1,
    ])

    <script>
        // var table;
        $(document).ready(function() {

            var table = $('#tbl_courses').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ url('backoffice/get-courses') }}",
                    data: function(d) {
                        d.user_id = $('#instructors').val(),
                        d.status = $('#status').val()
                    }
                },
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                "drawCallback": function(settings) {
                    if ($("#main_checkbox").is(":checked")) {
                        $("#main_checkbox").trigger("click");
                        $("#main_checkbox").prop("checked", true);
                    } else {
                        $("#main_checkbox").prop("checked", false);
                    }
                    $('#bulk_delete_frm [name^=bd]').attr("name", "");
                    $('#bulk_hard_delete_frm [name^=bd]').attr("name", "")
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: true,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        },
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'title',
                        name: 'courses.title'
                    },
                    {
                        data: 'instructor_id',
                        // name: 'user.name'
                        name: 'courses.instructor_id',
                        orderable: false,
                    },
                    {
                        data: 'course_plan_count',
                        name: 'course_plan_count',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'user_course_count',
                        name: 'user_course_count',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [],
            });

            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
                var url = state ? "{{ url('backoffice/get-courses') }}" :
                    "{{ url('backoffice/get-courses-deleted') }}";
                table.ajax.url(url).load();

                    $('.tbl_courses').removeClass('only_active');
                    $('.tbl_courses').addClass('only_deleted');
            });

            $(document).on("click", "#btn_submit", function() {
                event.preventDefault();
                table.draw();
            })
        });


        // $(document).on("click", "#btn_submit", function(event) {
        //     event.preventDefault();
        //     var test = $("#frm").attr('action');
        //     $(frm).attr('action', test + '?');
        //     console.log(test);
        // })
        $(document).ready(function() {
            $('#instructors').select2({
                allowClear: true,
                placeholder: "Select Instructor"
            });
            $('#status').select2({
                allowClear: true,
                placeholder: "Select Status"
            });
        })

        $(function() {
            $(".export_course").click(function() {

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to export Courses Report?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Export'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var allIds = [];
                        var status = $("#status").val();
                        var instructors = $("#instructors").val();
                        var ids = $("#tbl_courses input:checked").map(function() {
                            return $(this).data('id');
                        }).get().join();
                        if (ids.length === 0) {
                            var dataTable = $('#tbl_courses').DataTable();

                            var data = dataTable.rows().data();
                            data.each(function(row) {
                                allIds.push(row.id);
                            });

                            var ids = allIds.join();
                        }
                        // var url = "{{ URL::to('backoffice/pending-course-export') }}?" + $.param({
                        var url = "{{ URL::to('backoffice/cousre-export') }}?" + $.param({
                            status: status,
                            user_id: instructors
                        })
                        window.location = url;
                    }
                });

            });
        });
    </script>
@endsection
