@extends('admin.layouts.app')
@section('right-section')
    <?php
    $slide = new App\Models\Slider();
    ?>
@endsection
@section('content')
    <a  href="{{ url('backoffice/slider/create') }}" class="btn btn-primary">Add New</a>
    <!-- Contain Bulk delete abd restore all functionalities-->
    @if(Auth::user()->hasRole('admin'))
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
            'bulkDelURL' => url('backoffice/slider/bulk_del')
        ])
    </div>
    @endif
    <table id="tbl_slider" class="table table-bordered table-hover w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Id</th>
                <th scope="col">Name</th>
                 <th scope="col">Slug</th>
                <th scope="col">Created</th>
                <th scope="col">Action</th>
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
            table = $('#tbl_slider').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get_slider') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'id'
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
                        data: 'created_at',
                        name: 'created_at'
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
