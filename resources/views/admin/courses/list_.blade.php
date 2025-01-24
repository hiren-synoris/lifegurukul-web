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
                <th scope="col">Enroll Learner</th>

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
                },
                columns: [{
                    data: 'user_course_count',
                    name: 'user_course_count',
                    orderable: true, // Allow sorting on this column
                }],
                order: [], // No default order
                // Add a custom sort function for user_course_count
                columnDefs: [{
                    targets: 0, // Index of user_course_count column
                    render: function(data, type, row) {
                        // Ensure data is returned in a sortable format
                        return parseInt(data) || 0; // Return the integer value for sorting
                    }
                }]
            });

            // Custom sorting for user_course_count
            $.fn.dataTable.ext.order['user_course_count'] = function(settings, col) {
                return this.api().column(col, {
                    order: 'index'
                }).nodes().map(function(td, i) {
                    return parseInt($(td).text()) || 0; // Parse integer values for sorting
                });
            };

            // Attach the custom sorting function to the order event
            table.on('order.dt', function() {
                var order = table.order();
                if (order !== '') {


                    if (order[0][0] === 0) { // Check if sorting by user_course_count
                        table.column(0).data().sort(); // Custom sorting
                    }
                }
            });
        });
    </script>
@endsection
