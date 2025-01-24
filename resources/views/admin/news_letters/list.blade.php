@extends('admin.layouts.app')
@section('right-section')
    <?php
        $news_letter = new App\Models\NewsLetter();
    ?>
    @can('browse_news_letters', $news_letter)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/news_letters/create'),
        ])
    @endcan
    @can('delete_news_letters', $news_letter)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan
    @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
        'bulkDelURL' => url('backoffice/news_letters/bulk_del')
    ])
    <!-- Restore Button -->
    @can('restore_news_letters', $news_letter)
        @includeIf('admin.layouts.partials.buttons.restore')
        <!-- Bulk Hard delete Button -->
        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
    @endcan
    {{-- @if(Auth::user()->hasRole('admin'))
    @can('restore_news_letters')
        <!-- Show Deleted Button -->
        @includeIf('admin.layouts.partials.buttons.show-deleted')
    @endcan

    <!-- Bulk delete Button -->
    @can('delete_news_letters', $news_letter)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
        {{-- @includeIf('admin.layouts.partials.buttons.bulk-hard-delete') --}}
    {{-- @endcan
    @endif --}}
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    @if(Auth::user()->hasRole('admin'))
    <div class="" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/news_letters/bulk_Hard_Delete')
        ])
    </div>
    @endif
    <table id="tbl_news_letters" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Name</th>
                <th scope="col">email</th>
                <th scope="col">Created at</th>
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

            table = $('#tbl_news_letters').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-news-letters') }}",
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
                        data: 'email',
                        name: 'email'
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
                    table = $('#tbl_news_letters').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-news-letters') }}",
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
                            }, {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'email',
                                name: 'email'
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
                    $('#tbl_news_letters').removeClass('only_deleted');
                    $('#tbl_news_letters').addClass('only_active');
                } else {
                    table.destroy();
                    table = $('#tbl_news_letters').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-news-letters-deleted') }}",
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
                                data: 'name',
                                name: 'name'
                            },

                            {
                                data: 'email',
                                name: 'email'
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
                    $('#tbl_news_letters').removeClass('only_active');
                    $('#tbl_news_letters').addClass('only_deleted');
                }
            });
        });
    </script>
@endsection
