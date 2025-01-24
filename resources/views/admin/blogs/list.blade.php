@extends('admin.layouts.app')
@section('right-section')
<?php
    $blog = new App\Models\Blog;
?>

@can('add_blog', $blog)
<!-- Add Button -->
    @includeIf('admin.layouts.partials.buttons.add',[
        'addUrl' => url('backoffice/blogs/create'),
        'addBtnText' => 'Create Blog',
        'addTitleText' => 'Create Blog',
    ])
@endcan

@if(Auth::user()->hasRole('admin'))
@can('restore_blog')
<!-- Show Deleted Button -->
    @includeIf('admin.layouts.partials.buttons.show-deleted')
@endcan

<!-- Bulk delete Button -->
@can('delete_blog', $blog)
    @includeIf('admin.layouts.partials.buttons.bulk-delete')
@endcan

<!-- Restore Button -->
@can('restore_blog', $blog)
    @includeIf('admin.layouts.partials.buttons.restore')
    <!-- Bulk Hard delete Button -->
    @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
@endcan
@endif
@endsection
@section('content')
<div style="display: none;" id="blk_del_frm">
    @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
        'bulkDelURL' => url('backoffice/blogs/bulk_del')
    ])
    @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
        'bulkHardDelURL' => url('backoffice/blogs/bulk_hard_del')
    ])
    @includeIf('admin.layouts.partials.actions.restore-all-data', [
        'restoreAllURL' => url('backoffice/blogs/restore_all')
    ])
</div>
<table id="tbl_blogs" class="table table-bordered table-hove w-100 only_active">
    <thead class="thead-light">
        <tr class="text-center">
            <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
            <th scope="col">Title</th>
            <th scope="col">Slug</th>
            <th scope="col">Category Name</th>
            <th scope="col">Status</th>
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

        table = $('#tbl_blogs').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ url('backoffice/get-blogs') }}",
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
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'category_id',
                    name: 'category_id'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'date',
                    name: 'date'
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            "order": []
        });

        $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
            $("#main_checkbox").prop("checked", false);
            $(".children_checkbox").prop("checked", false);
            if (state == true) {
                table.destroy();
                table = $('#tbl_blogs').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get-blogs') }}",
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
                                data + ']" data-id=' + data +
                                ' style="cursor: pointer;"/>';
                            },
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'slug',
                            name: 'slug'
                        },
                        {
                            data: 'category_id',
                            name: 'category_id'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'date',
                            name: 'date'
                        }, {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },],
                    "order": []
                });
                $('#tbl_blogs').removeClass('only_deleted');
                $('#tbl_blogs').addClass('only_active');
            } else {
                table.destroy();
                table = $('#tbl_blogs').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: "{{ url('backoffice/get-blogs-deleted') }}",
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
                                    data + ']" data-id=' + data +
                                    ' style="cursor: pointer;"/>';
                            },
                        },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'category_id',
                    name: 'category_id'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'date',
                    name: 'date'
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                    ],
                    "order": []
                });
                $('#tbl_blogs').removeClass('only_active');
                $('#tbl_blogs').addClass('only_deleted');
            }
        });
    });
    </script>

@endsection
