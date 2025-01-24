@extends('admin.layouts.ajax_content')
@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        a.course-tag {
            background-color: rgb(189 23 23 / 10%);
            margin: 4px;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
            border-radius: 10px;
        }
    </style>
@endsection
@section('content')
<span class="d-flex p-2 text-center font-weight-bold "></span>
<table id="tbl_customers_credits"  class="table table-striped table-valign-middle">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Title</th>
                <th scope="col">Mobile</th>
                <th scope="col">Email</th>
                <th scope="col">Total Course</th>
                <th scope="col">Course List</th>
                {{-- <th scope="col">Action</th> --}}
            </tr>
        </thead>
</table>
@endsection
@section('js')

    {{-- <script src="{{ asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script> --}}
    <script>
        var table1;
        $(document).ready(function() {

            table1 = $('#tbl_customers_credits').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                bPaginate: false, // pagination
                bInfo : false, // Showing 1 to 10 of 10 entries
                ajax: "{{ url('backoffice/userWiseCourse/') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                "drawCallback": function(settings) {
                },
                columns: [
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'course_count',
                        name: 'course_count'
                    },
                    {
                        data: 'courses',
                        name: 'courses'
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false
                    // },
                ],
                "order": [
                    [3, "desc"]
                ]
            });
  });
    </script>
@endsection
