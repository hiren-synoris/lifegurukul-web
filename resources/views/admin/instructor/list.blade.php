@extends('admin.layouts.app')
@section('right-section')
    <?php
    $path = 'instructors';
    ?>
    <!-- Add Button -->
    @can('add_users')
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/'.$path.'/create'),
        ])
    @endcan

    @if(Auth::user()->hasRole('admin'))
    <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endif

    <!-- Bulk delete Button -->
    @can('delete_users')
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_users')
        @includeIf('admin.layouts.partials.buttons.restore')
    @endcan

    @can('restore_users')
        @includeIf('admin.layouts.partials.buttons.restore')
            <!-- Bulk Hard delete Button -->
    @endcan


@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
            'bulkDelURL' => url('backoffice/'.$path.'/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/'.$path.'/restore_all')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => route('instructors.bulk_hard_del')
        ])
    </div>

    <table id="tbl_users" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                {{-- <th scope="col">Roles</th> --}}
                <th scope="col">Designation</th>
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
        var columns = '';


            columns =[
                { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        }},
                { data: 'profile_picture', name: 'users.profile_picture' },
                { data: 'name', name: 'users.name' },
                { data: 'email', name: 'users.email' },
                // { data: 'rolesName', name: 'rolesName' },
                { data: 'designation', name: 'instructors.designation' },
                { data: 'created_at', name: 'users.created_at'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ];
            // order = [[4, "desc"]];


        $(document).ready(function() {
            table = $('#tbl_users').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('backoffice/get_instructor') }}",
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
                    $('#bulk_delete_frm [name^=bd]').attr("name", "");
                $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                },
                columns:[
                        { data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                         render: function(data, type, row) {
                                    return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                        data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        }},
                        { data: 'profile_picture', name: 'users.profile_picture' },
                        { data: 'name', name: 'users.name' },
                        { data: 'email', name: 'users.email' },
                        // { data: 'rolesName', name: 'rolesName' },
                        { data: 'designation', name: 'instructors.designation' },
                        { data: 'created_at', name: 'users.created_at'},
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                        "order":[]
                    });

            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
                $("#main_checkbox").prop("checked", false);
                $(".children_checkbox").prop("checked", false);
                if (state == true) {
                    table.destroy();
                    table = $('#tbl_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('backoffice/get_instructor') }}",
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
                                $(".children_checkbox").prop("checked", false);
                            }
                            $('#bulk_delete_frm [name^=bd]').attr("name", "");
                            $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                        },
                        columns: columns,
                        "order": []
                    });
                    $('#tbl_users').removeClass('only_deleted');
                    $('#tbl_users').addClass('only_active');
                } else {
                    table.destroy();
                    table = $('#tbl_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('backoffice/get_instructor_deleted') }}",
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
                            $('#bulk_delete_frm [name^=bd]').attr("name", "");
                            $('#bulk_hard_delete_frm [name^=bd]').attr("name", "")
                        },
                        columns: columns,
                        "order": []
                    });
                    $('#tbl_users').removeClass('only_active');
                    $('#tbl_users').addClass('only_deleted');
                }
            });
        });



    </script>
@endsection
