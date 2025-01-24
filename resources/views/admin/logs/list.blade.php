@extends('admin.layouts.app')
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
        ])
    </div>

    <table id="tbl_logs" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                {{-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> --}}
                <th scope="col">Id</th>
                <th scope="col">Message</th>
                <th scope="col">Logged in at</th>
                {{-- <th scope="col">Action</th> --}}
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'dataTableJS' => 1,
    ])

    <script>
        var table;
        $(document).ready(function() {
            table = $('#tbl_logs').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get_logs') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'message',
                        name: 'message'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                ],
                "order": [
                    [2, "desc"]
                ]
            });
        });
    </script>
@endsection
