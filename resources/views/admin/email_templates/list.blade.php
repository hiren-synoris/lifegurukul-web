@extends('admin.layouts.app')
@section('right-section')

    <!-- Add Button -->
    @can('add_email_templates')
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/email_templates/create'),
        ])
    @endcan

    @if(Auth::user()->hasRole('admin'))
    @can('restore_email_templates')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_email_templates')
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_email_templates')
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
            'bulkDelURL' => url('backoffice/email-templates/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/email-templates/bulk_hard_del')
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/email-templates/restore_all')
        ])
    </div>

    <table id="tbl_email_template" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Name</th>
                <th scope="col">Created By</th>
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
            table = $('#tbl_email_template').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-email-templates') }}",
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
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        }
                    },
                    {
                        data: 'slug',
                        name: 'slug'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [
                    [4, "desc"]
                ]
            });

            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
                $("#main_checkbox").prop("checked", false);
                $(".children_checkbox").prop("checked", false);
                if (state == true) {
                    table.destroy();
                    table = $('#tbl_email_template').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-email-templates') }}",
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
                                        data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                                }
                            },
                            {
                                data: 'slug',
                                name: 'slug'
                            },
                            {
                                data: 'user',
                                name: 'user'
                            },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            }, {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ],
                        "order": [
                            [4, "desc"]
                        ]
                    });
                    $('#tbl_email_template').removeClass('only_deleted');
                    $('#tbl_email_template').addClass('only_active');
                } else {
                    table.destroy();
                    table = $('#tbl_email_template').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-email-templates-deleted') }}",
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
                                        data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                                }
                            },
                            {
                                data: 'slug',
                                name: 'slug'
                            },
                            {
                                data: 'user',
                                name: 'user'
                            },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            }, {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ],
                        "order": [
                            [4, "desc"]
                        ]
                    });
                    $('#tbl_email_template').removeClass('only_active');
                    $('#tbl_email_template').addClass('only_deleted');
                }
            });
        });
    </script>
@endsection
