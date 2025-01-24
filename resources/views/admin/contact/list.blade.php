@extends('admin.layouts.app')
{{-- @section('right-section')
    <?php
        $contact = new App\Models\Contact();
    ?>
    @can('browse_contact', $contact)
        @includeIf('admin.layouts.partials.buttons.add',[
            'addUrl' => url('backoffice/contact/create'),
        ])
    @endcan
    @can('delete_contact', $contact)
        @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @endcan
    @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
        'bulkDelURL' => url('backoffice/contact/bulk_hard_del')
    ])

@endsection --}}
@section('content')
    <!-- Contain Bulk delete abd restore all functionalities-->
    {{-- @if(Auth::user()->hasRole('admin'))
    <div class="" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
            'bulkHardDelURL' => url('backoffice/contact/bulk_Hard_Delete')
        ])
    </div>
    @endif --}}
    <table id="tbl_contacts" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                {{-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> --}}
                <th scope="col">Ticket No</th>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Email</th>
                <th scope="col">Mobile</th>
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
            table = $('#tbl_contacts').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-contacts') }}",
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
                    // {
                    //     data: 'id',
                    //     name: 'id',
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function(data, type, row) {
                    //         return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                    //             data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                    //     },
                    // },
                    {
                        data: 'ticket_no',
                        name: 'ticket_no'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
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
        });
    </script>
@endsection
