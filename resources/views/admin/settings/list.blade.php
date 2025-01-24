@extends('admin.layouts.app')
@section('right-section')
 <?php
    $settings = new App\Models\Settings;
        ?>

   <!-- Add Button -->
    @can('add_settings', $settings)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/settings/create'),
        ])
    @endcan


    @if(Auth::user()->hasRole('admin'))
    @can('restore_settings')
        <!-- Show Deleted Button -->
            @includeIf('admin.layouts.partials.buttons.show-deleted')
            <!-- Bulk Hard delete Button -->
         @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_settings', $settings)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')

    @endcan

    <!-- Restore Button -->
    @can('restore_settings', $settings)
        @includeIf('admin.layouts.partials.buttons.restore')
    @endcan
    @endif
@endsection

@section('content')

<div style="display: none;" id="blk_del_frm">
    @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
        'bulkDelURL' => url('backoffice/settings/bulk_del')
    ])
    @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
        'bulkHardDelURL' => url('backoffice/settings/bulk_hard_del')
    ])
    @includeIf('admin.layouts.partials.actions.restore-all-data', [
        'restoreAllURL' => url('backoffice/settings/restore_all')
    ])
</div>
<div class="row mb-4">
    <div class="col-12">
        <h5>Filter by Type</h5>
    <form action="javascript:void(0)" class="justify-content-between" method="get">
        <div class="row">
            <div class="col-3">
            <select name="type" id="type_fltr" class="custom-select" onchange="$(this)">
            <option value="" selected>Select Type</option>
            <option value="1">General</option>
            <option value="2">Payment</option>
            <option value="3">Third Party Apps</option>
        </select>
            </div>
            <div class="col-2"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                <a href="" class="btn btn-primary" >Clear</a>
            </div>
        </div>
    </form>
    <form action="{{url('backoffice/get-courses')}}" style="display: none;" id="frm"></form>
    </div>
</div>
<table id="tbl_settings" class="table table-bordered table-hove w-100 only_active">
    <thead class="thead-light">
        <tr class="text-center">
            <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>

            <!-- <th scope="col">Key</th> -->
            <th scope="col">Display Name</th>
            <th scope="col">Key</th>
            <th scope="col">Value</th>
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
$("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
})
$(document).ready(function() {
    table = $('#tbl_settings').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ url('backoffice/get_settings') }}",
        columnDefs: [{
            className: 'text-center',
            targets: []
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
                data: 'display_name',
                name: 'display_name'
            },
            {
                data: 'key',
                name: 'key'
            },
            {
                data: 'value',
                name: 'value',
                "render": function ( data, type, row, meta ) {
                     if(row.setting_type == "boolean" && data == "1") {
                        return 'Yes';
                    }
                    else if(row.setting_type == "boolean" && data == "0"){
                        return 'No';
                    }
                    else if(row.setting_type == "checkbox"){
                        if(data != null && data != undefined && data != '' && data.length > 0){
                            return data.replace("1", "Website").replace("2", " Android").replace("3", " Ios");
                        }
                        else{
                            return "Not visible on any platforms";
                        }
                    }
                    else {
                        return data;
                    }
                }
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
            [4, "desc"]
        ]
    });


});

$('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
    $("#main_checkbox").prop("checked", false);
    $(".children_checkbox").prop("checked", false);
    if (state == true) {

        table.destroy();
        table = $('#tbl_settings').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ url('backoffice/get_settings') }}",
            columnDefs: [{
                className: 'text-center',
                targets: []
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
                }, {
                    data: 'key',
                    name: 'key'
                },
                {
                    data: 'display_name',
                    name: 'display_name'
                },
                {
                    data: 'value',
                    name: 'value'
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
                [4, "desc"]
            ]
        });
        $('#tbl_settings').removeClass('only_deleted');
        $('#tbl_settings').addClass('only_active');
    } else {

        table.destroy();
        table = $('#tbl_settings').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ url('backoffice/get_settings_deleted') }}",
            columnDefs: [{
                className: 'text-center',
                targets: []
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
                }, {
                    data: 'key',
                    name: 'key'
                },
                {
                    data: 'display_name',
                    name: 'display_name'
                },
                {
                    data: 'value',
                    name: 'value'
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
                [4, "desc"]
            ]
        });
        $('#tbl_settings').removeClass('only_active');
        $('#tbl_settings').addClass('only_deleted');
    }
});
$('#btn_submit').click(function (event) {
        event.preventDefault();
        var type = $("#type_fltr").val();
        // console.log(user_id);
        // var courses = $("#courses").val();
        table.destroy();
        table = $('#tbl_settings').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ url('backoffice/get_settings') }}"+'?type='+type,
        columnDefs: [{
            className: 'text-center',
            targets: []
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
                data: 'display_name',
                name: 'display_name'
            },
            {
                data: 'key',
                name: 'key'
            },
            {
                data: 'value',
                name: 'value',
                "render": function ( data, type, row, meta ) {
                     if(row.setting_type == "boolean" && data == "1") {
                        return 'Yes';
                    }
                    else if(row.setting_type == "boolean" && data == "0"){
                        return 'No';
                    }
                    else if(row.setting_type == "checkbox"){
                        if(data != null && data != undefined && data != '' && data.length > 0){
                            return data.replace("1", "Website").replace("2", " Android").replace("3", " Ios");
                        }
                        else{
                            return "Not visible on any platforms";
                        }
                    }
                    else {
                        return data;
                    }
                }
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
            [4, "desc"]
        ]
    });
    });
</script>
@endsection
