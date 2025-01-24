@extends('admin.layouts.app')
@section('right-section')
    <?php
    $instructors = new App\Models\Instructors();
    // $url = $_SERVER['PHP_SELF'];
    $url = Request::url();
    if (str_contains($url, 'learners')) {
        $path = 'learners';
    } else if (str_contains($url, 'instructor')){
        $path = 'instructor';
    } else if (str_contains($url, 'subadmin')){
        $path = 'subadmin';
    } else {
        $path = 'users';
    }
    ?>
    <!-- Add Button -->
    @can('add_users')
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/'.$path.'/create'),
        ])
    @endcan

    {{-- @if(Auth::user()->hasRole('admin')) --}}
    @can('restore_users')
    <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_users')
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_users')
        @includeIf('admin.layouts.partials.buttons.restore')
    @endcan
    {{-- @endif --}}
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
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
            'bulkHardDelURL' => route('bulk_hard_user_delete')
        ])
    </div>

    <table id="tbl_users" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Roles</th>
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
        var order = '';

            columns =[
                { data: 'id', name: 'id', orderable: false, searchable: false, render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        }},
                { data: 'profile_picture', name: 'profile_picture' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'rolesName', name: 'rolesName', orderable: false, searchable: false,
                render:function(a,b,c,d) {
                    if(c.role_deleted != null){
                        return "";
                    }
                    else{
                        console.log(c);
                        return c.rolesName;
                    }
                }
            },
                { data: 'created_at', name: 'created_at'},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ];
            order = [];


        $(document).ready(function() {
            table = $('#tbl_users').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('backoffice/get_users') }}",
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
                columns: columns,
                "order": order
            });

            $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
                $("#main_checkbox").prop("checked", false);
                $(".children_checkbox").prop("checked", false);
                if (state == true) {
                    table.destroy();
                    table = $('#tbl_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('backoffice/get_users') }}",
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
                        columns: columns,
                        "order": order
                    });
                    $('#tbl_users').removeClass('only_deleted');
                    $('#tbl_users').addClass('only_active');
                } else {
                    table.destroy();
                    table = $('#tbl_users').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ url('backoffice/get_users_deleted') }}",
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
                        columns: columns,
                        "order": order
                    });
                    $('#tbl_users').removeClass('only_active');
                    $('#tbl_users').addClass('only_deleted');
                }
            });
        });
    </script>
@endsection
