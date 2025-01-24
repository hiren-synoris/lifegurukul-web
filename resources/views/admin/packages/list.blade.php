@extends('admin.layouts.app')
@section('styles')
    <style>
        .text-truncate{
            max-width: 250px;
        }
    </style>
@endsection
@section('right-section')
    <!-- Add Button -->
    @can('add_packages')
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/packages/create'),
            'addBtnText' => 'Create Package',
            'addTitleText' => 'Create Package',
        ])
    @endcan
    @if(Auth::user()->hasRole('admin'))
    @can('restore_packages')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_packages')
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_packages')
        @includeIf('admin.layouts.partials.buttons.restore')
        <!-- Bulk Hard delete Button -->
        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan
    @endif
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
            'bulkDelURL' => url('backoffice/packages/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/packages/bulk_hard_del')
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/packages/restore_all')
        ])
    </div>

    <table id="tbl_courses" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col" data-orderable="false">Package id</th>
                <th scope="col">Title</th>
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
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'dataTableJS' => 1,
        'switch' => 1
    ])

    <script>
        var table;
        $(document).ready(function() {
            table = $('#tbl_courses').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-packages') }}",
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
                    $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                },
                columns: [
                    {
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
                        name: 'title'
                    },
                    {
                        data: 'course_plan_count',
                        name: 'course_plan_count'
                    },
                    {
                        data: 'user_course_count',
                        name: 'user_course_count'
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


            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
                var url = state ? "{{ url('backoffice/get-packages') }}" :
                    "{{ url('backoffice/get-packages-deleted') }}";
                table.ajax.url(url).load();
            });

        });

    </script>
@endsection
