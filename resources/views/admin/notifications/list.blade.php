@extends('admin.layouts.app')
@section('right-section')
    <?php
        $notifications = new App\Models\Notification();
    ?>
    @can('browse_notifications', $notifications)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/notifications/create'),
        ])
    @endcan

    @can('restore_notifications')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_notifications', $notifications)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan

    <!-- Restore Button -->
    @can('restore_notifications', $notifications)
        @includeIf('admin.layouts.partials.buttons.restore')
        <!-- Bulk Hard delete Button -->
        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
            'bulkDelURL' => url('backoffice/notifications/bulk_del')
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/notifications/bulk_hard_del')
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/notifications/restore_all')
        ])
    </div>

    <table id="tbl_notifications" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Notification Title</th>
                <th scope="col">Notification Text</th>
                <th scope="col">Time</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
    ])
    <script>
        var table;
        $(document).ready(function() {

            table = $('#tbl_notifications').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-notifications') }}",
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
                        data: 'notificationTitle',
                        name: 'notificationTitle'
                    },
                    {
                        data: 'notificationText',
                        name: 'notificationText'
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
                    table = $('#tbl_notifications').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-notifications') }}",
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
                                        data + ']" data-id=' + data +
                                        ' style="cursor: pointer;"/>';
                                },
                            },
                            {
                                data: 'notificationTitle',
                                name: 'notificationTitle'
                            },
                            {
                                data: 'notificationText',
                                name: 'notificationText'
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
                    $('#tbl_notifications').removeClass('only_deleted');
                    $('#tbl_notifications').addClass('only_active');
                } else {
                    table.destroy();
                    table = $('#tbl_notifications').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-notifications-deleted') }}",
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
                                        data + ']" data-id=' + data +
                                        ' style="cursor: pointer;"/>';
                                },
                            },
                            {
                                data: 'notificationTitle',
                                name: 'notificationTitle'
                            },
                            {
                                data: 'notificationText',
                                name: 'notificationText'
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
                    $('#tbl_notifications').removeClass('only_active');
                    $('#tbl_notifications').addClass('only_deleted');
                }
            });
        });
    </script>
@endsection
