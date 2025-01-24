@extends('admin.layouts.app')
@section('right-section')
<a href="{{ url("backoffice/dropdowns") }}" class="btn btn-warning px-2 py-1 mr-1">Back</a>
<?php
    $dropdownOption = new App\Models\DropdownOption();
    ?>
    <!-- Add Button -->
    @can('add_dropdowns', $dropdownOption)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/dropdown_options/'.$dropdown->id.'/create'),
            ])
    @endcan

    @if(Auth::user()->hasRole('admin'))
    @can('restore_dropdowns')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
        @endcan

    <!-- Bulk delete Button -->
    @can('delete_dropdowns', $dropdownOption)
    @includeIf('admin.layouts.partials.buttons.bulk-delete')

    @endcan

    <!-- Restore Button -->
    @can('restore_dropdowns', $dropdownOption)
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
        'bulkDelURL' => url('backoffice/dropdown_options/'.$dropdown->id.'/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/dropdown_options/'.$dropdown->id.'/bulk_hard_del')
            ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/dropdown_options/'.$dropdown->id.'/restore_all')
            ])
    </div>

    <table id="tbl_dropdown_options" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Name</th>
                <th scope="col">Slug</th>
                <th scope="col" data-orderable="false">image</th>
                <th scope="col">Status</th>
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
            table = $('#tbl_dropdown_options').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get_dropdown_options/'.$dropdown->id ?? '') }}",
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
                        data: 'image',
                        name: 'image'
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
        });

        $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
            $("#main_checkbox").prop("checked", false);
            $(".children_checkbox").prop("checked", false);

            if (state == true) {
                // $('#restore-btn').removeClass("d-block").addClass("d-none");
                // $('#hard-delete-btn').removeClass("d-block").addClass("d-none");
                // $('#delete-btn').removeClass("d-none").addClass("d-block");
                table.destroy();
                table = $('#tbl_dropdown_options').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get_dropdown_options/'.$dropdown->id ?? '') }}",
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
                            data: 'image',
                            name: 'image'
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
                $('#tbl_dropdown_options').removeClass('only_deleted');
                $('#tbl_dropdown_options').addClass('only_active');
            }
            else {
                // $('#delete-btn').removeClass("d-block").addClass("d-none");
                // $('#restore-btn').removeClass("d-none").addClass("d-block");
                // $('#hard-delete-btn').removeClass("d-none").addClass("d-block");
                table.destroy();
                table = $('#tbl_dropdown_options').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get_dropdown_options_deleted/'.$dropdown->id ?? '') }}",
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
                            data: 'image',
                            name: 'image'
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
                $('#tbl_dropdown_options').removeClass('only_active');
                $('#tbl_dropdown_options').addClass('only_deleted');
            }
        });
    </script>
@endsection
