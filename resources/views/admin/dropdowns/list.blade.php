@extends('admin.layouts.app')
@section('styles')
    <style>
        .text-truncate{
            max-width: 250px;
        }
    </style>
@endsection
@section('right-section')
    <?php
    $dropdown = new App\Models\Dropdown();
    ?>

    <!-- Add Button -->
    @can('add_dropdowns', $dropdown)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/dropdowns/create'),
        ])
    @endcan
    @if(Auth::user()->hasRole('admin'))
    @can('restore_dropdowns')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_dropdowns', $dropdown)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_dropdowns', $dropdown)
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
            'bulkDelURL' => url('backoffice/dropdowns/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/dropdowns/bulk_hard_del')
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/dropdowns/restore_all')
        ])
    </div>

    <table id="tbl_dropdowns" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Name</th>
                <th scope="col">Slug</th>
                <th scope="col">Created At</th>
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
            table = $('#tbl_dropdowns').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-dropdowns') }}",
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
                        data: 'slug',
                        name: 'slug'
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


        });

        $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
            $("#main_checkbox").prop("checked", false);
            $(".children_checkbox").prop("checked", false);

            if (state == true) {
                table.destroy();
                table = $('#tbl_dropdowns').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get-dropdowns') }}",
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
                            data: 'slug',
                            name: 'slug'
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
                $('#tbl_dropdowns').removeClass('only_deleted');
                $('#tbl_dropdowns').addClass('only_active');
            }
            else {
                table.destroy();
                table = $('#tbl_dropdowns').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get-dropdowns-deleted') }}",
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
                            data: 'slug',
                            name: 'slug'
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
                $('#tbl_dropdowns').removeClass('only_active');
                $('#tbl_dropdowns').addClass('only_deleted');
            }
        });
    </script>
@endsection
