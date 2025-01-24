@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection
@section('right-section')
 <?php
    $permission = new App\Models\Permission;
        ?>
 @can('add_permissions', $permission)
<a href="{{ url('backoffice/permissions/create') }}" class="btn btn-success px-2 py-1 mr-1" title="Add Permission">
    <i class="fas fa-plus"></i>
    <span class="pl-1">Add New</span>
</a>
@endcan
@if(Auth::user()->hasRole('admin'))
@can('restore_permissions', $permission)
<div class="mr-1">
    <input type="checkbox" name="my-checkbox" id="swWeather" data-size="normal" data-handle-width="120"
        data-label-width="1" data-on-text="<i class='fas fa-trash-restore'></i> Show Deleted"
        data-off-text="<i class='fas fa-trash-alt'></i> Hide Deleted" checked data-bootstrap-switch>
</div>
@endcan
@can('delete_permissions', $permission)
{{-- <a id="delete-btn" class="btn btn-danger px-2 py-1 mr-1" href="javascript:void(0)" onclick="myFunction()">
    <i class="fas fa-trash-alt"></i>
    <span class="pl-1">Bulk Delete</span>
</a> --}}
@includeIf('admin.layouts.partials.buttons.bulk-delete')
@endcan
@can('restore_permissions', $permission)
<a id="restore-btn" class="btn btn-success px-2 py-1 mr-1" href="javascript:void(0)" onclick="restore_all()"
    style="display: none;">
    <span class="d-flex flex-start align-items-center">
        <i class="fas fa-trash-restore"></i>
        <span class="pl-1">Restore</span>
    </span>
</a>
@endif
<!-- Bulk Hard delete Button -->
@includeIf('admin.layouts.partials.buttons.bulk-hard-delete')

@endcan
@endsection
@section('content')

<div style="display: none;" id="blk_del_frm">
    <form action="{{ url('backoffice/permissions/bulk_del') }}" id="bd_frm" method="POST">
        @csrf
    </form>
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/permissions/bulk_hard_del')
        ])
    <form action="{{ url('backoffice/permissions/restore_all') }}" id="restore_frm" method="POST">
        @csrf
    </form>
</div>
<table id="tbl_permissions" class="table table-bordered table-hove w-100 only_active">
    <thead class="thead-light">
        <tr class="text-center">
            <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>

            <th scope="col">Name</th>
            <th scope="col">Module Name</th>
            <th scope="col">Created at</th>
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
        table = $('#tbl_permissions').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ url('backoffice/get_permissions') }}",
            "drawCallback": function( settings ) {
                if ($("#main_checkbox").is(":checked")) {
                    $("#main_checkbox").trigger("click");
                    $("#main_checkbox").prop("checked", true);
                }
                else{
                    $("#main_checkbox").prop("checked", false);
                }
            },
            columnDefs: [{
                className: 'text-center',
                targets: '_all'
            }],
            columns: [{
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                            data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                    },
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'module_name',
                    name: 'module_name'
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
            "order": [
                [3, "desc"]
            ]
        });

        $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
            $("#main_checkbox").prop("checked", false);
            $(".children_checkbox").prop("checked", false);

            if (state == true) {
                table.destroy();
                table = $('#tbl_permissions').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get_permissions') }}",
                    columnDefs: [{
                        className: 'text-center',
                        targets: [0]
                    }],
                    "drawCallback": function( settings ) {
                        if ($("#main_checkbox").is(":checked")) {
                            $("#main_checkbox").trigger("click");
                            $("#main_checkbox").prop("checked", true);
                        }
                        else{
                            $("#main_checkbox").prop("checked", false);
                        }
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
                            },
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'module_name',
                            name: 'module_name'
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
                    "order": [
                        [3, "desc"]
                    ]
                });
                $('#tbl_permissions').removeClass('only_deleted');
                $('#tbl_permissions').addClass('only_active');
            } else {
                table.destroy();
                table = $('#tbl_permissions').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get_permissions_deleted') }}",
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    "drawCallback": function( settings ) {
                        if ($("#main_checkbox").is(":checked")) {
                            $("#main_checkbox").trigger("click");
                            $("#main_checkbox").prop("checked", true);
                        }
                        else{
                            $("#main_checkbox").prop("checked", false);
                        }
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
                            },
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'module_name',
                            name: 'module_name'
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
                    "order": [
                        [3, "desc"]
                    ]
                });
                $('#tbl_permissions').removeClass('only_active');
                $('#tbl_permissions').addClass('only_deleted');
            }
        });

    });
    </script>
@endsection
