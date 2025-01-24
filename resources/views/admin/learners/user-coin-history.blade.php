@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
#tbl_courses_enroll_wrapper{
    height: 510px;
}
.modal-dialog.modal-lg .modal-body {
    overflow: auto;
}
div.dataTables_wrapper div.dataTables_paginate ul.pagination{
    margin: 0 0 20px;
}

</style>

    @includeIf('admin.layouts.partials.styles.style', [
        'dropzoneCSS' => 1,
        'select2CSS' => 1,
    ])
@endsection
@section('right-section')
        @php
        $userCoin = new App\Models\UserCoin();
        $key = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;
        $id = App\Models\Settings::where("key",'onecoinprice')->first()->id
        @endphp

@if (Auth::user()->hasRole('admin'))


<!-- Bulk delete Button -->
@can('delete_learners', $userCoin)
@includeIf('admin.layouts.partials.buttons.bulk-delete')
@includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
@endcan
{{-- <h3>ok</h3> --}}

    @endif
@endsection
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
            'bulkDelURL' => url('backoffice/learners/bulk_del'),
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/learners/restore_all'),
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
            'bulkHardDelURL' => route('bulk_hard_delete'),
        ])
    </div>
    <a class="h3" href="{{ url("backoffice/settings/$id/edit") }}">Current per coin price  {{ $key }} rupees</a>
    <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                {{-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> --}}
                <th scope="col">Name</th>
                <th scope="col">Coins</th>
                <th scope="col">Type</th>
                <th scope="col">Course name</th>
                <th scope="col">Created at</th>
                {{-- <th scope="col">Action</th> --}}
            </tr>
        </thead>
    </table>

    <!-- Logged Device Modal code start-->
    <div class="modal fade" id="listLoggedDevice" tabindex="-1" aria-labelledby="listLoggedDeviceLabel"
        aria-hidden="true">
    </div>
    <!-- Logged Device Modal code end -->
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
        'switch' => 1,
    ])

    <script>


        $(document).ready(function() {
            table_learner = $('#tbl_learners')
                .DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('get_learner_history') }}",
                        data: function(d) {
                        }
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],

                    columns: [

                        // {
                        //     data: 'id',
                        //     name: 'id',
                        //     orderable: false,
                        //     searchable: false,
                        //     render: function(data, type, row) {
                        //         return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                        //             data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        //     }
                        // },
 {
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'coins',
                            name: 'coins'
                        },
                        {
                            data: 'type',
                            name: 'type'
                        },
                        {
                            data: 'course_id',
                            name: 'course_id'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },

                        //  {
                        //     data: 'action',
                        //     name: 'action',
                        //     orderable: false,
                        //     searchable: false
                        // },
                    ],
                    "order": []
                });

        });

    </script>
@endsection
