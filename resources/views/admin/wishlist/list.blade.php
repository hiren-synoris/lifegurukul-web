@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'select2CSS' => 1,
    ])
@endsection
@section('right-section')
    @can('wishlist_report')
        <form action="{{ route('wishlist.export') }}" id="wishlist_export" method="post">
            @csrf
            <input type="hidden" name="user_id_hidden" id="user_id_hidden" value="">
            <input type="hidden" name="course_id_hidden" id="course_id_hidden" value="">
            <button type="button" class="btn btn-primary" id="wishlistExport">Export</button>
        </form>
    @endcan
@endsection
@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h5>Filter</h5>
            <form action="javascript:void(0)" method="get">
                <div class="row">
                    <div class="col-5">
                        {{-- <select name="users" id="users" class="custom-select " onchange="$(this)">
                        <option value="">Select User</option>
                        @foreach ($users as $key => $value)
                        <option value="{{$value->id}}">{{$value->mobile}}
                            @if (!empty($value->name))
                            ({{ $value->name }})
                            @endif
                        </option>
                        @endforeach
                    </select> --}}
                        <input type="text" class="form-control w-100 autocompleteInput sl-label" id=""
                            name="users" placeholder="Search Learner" data-id="">
                        {{-- <input type="text" class="form-control w-100 sl-id search_leaner"
                    id="users" name="users" style="display: none"> --}}

                    </div>
                    <div class="col-5">
                        {{-- <select name="courses" id="courses" class="custom-select select3">
                        <option value="">Select Course</option>
                        @foreach ($courses as $key => $value)
                        <option value="{{$value->id}}">{{$value->title}}</option>
                        @endforeach
                    </select> --}}
                        <input type="text" class="form-control w-100 autocompleteInputCourse sl-label-course"
                            id="" name="courses" placeholder="Search course" data-id="">
                    </div>
                    <div class="col-2"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                        <a href="" class="btn btn-primary">Clear</a>
                    </div>
                </div>
            </form>
            <form action="{{ url('backoffice/get-wishlist') }}" style="display: none;" id="frm"></form>
        </div>
    </div>

    <table id="tbl_wishlist" class="table table-bordered table-hove w-100">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">User</th>
                <th scope="col">Email</th>
                <th scope="col">Mobile</th>
                <th scope="col">Course</th>
                <th scope="col">Time</th>
                {{-- <th scope="col">Updated At</th> --}}
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
    ])
    <script>
        var table;
        $(document).ready(function() {

            $(".autocompleteInput").autocomplete({
                source: '/backoffice/search-learner',
                focus: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    $(".sl-label").attr("data-id", ui.item.value);
                    return false;
                }

            });

            $(".autocompleteInputCourse").autocomplete({
                source: '/backoffice/search-course',
                focus: function(event, ui) {
                    $(".sl-label-course").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label-course").val(ui.item.label);
                    $(".sl-label-course").attr("data-id", ui.item.value);
                    return false;
                }

            });

            table = $('#tbl_wishlist').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-wishlist') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'name',
                        name: 'name'
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
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    // {
                    //     data: 'updt_at',
                    //     name: 'updt_at'
                    // }
                ],
                "order": []
            });

        });

        $('#btn_submit').click(function(event) {
            event.preventDefault();
            var user_id = $(".sl-label").attr("data-id");
            var courses = $(".sl-label-course").attr("data-id");
            if ($(".autocompleteInputCourse").val() == "") {
                courses = ""
            }
            if ($(".autocompleteInput").val() == "") {

                user_id = ""
            }
            $("#user_id_hidden").attr("value", user_id);
            $("#course_id_hidden").attr('value', courses);
            table.destroy();
            table = $('#tbl_wishlist').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get-wishlist') }}" + '?user_id=' + user_id + '&courses=' +
                    courses,
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'name',
                        name: 'name'
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
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    // {
                    //     data: 'updt_at',
                    //     name: 'updt_at'
                    // }
                ],
                "order": []
            });
        });
        var status_check = 0
        $(document).on("click", "#btn_submit", function(event) {
            event.preventDefault();
            var test = $("#frm").attr('action');
            $(frm).attr('action', test + '?');
            status_check = 1
        })
        $(document).ready(function() {
            $('#users').select2({
                allowClear: true,
                placeholder: 'Select User'
            });
        })
        $(document).ready(function() {
            $('.select3').select2({
                allowClear: true,
                placeholder: 'Select Course'
            });
        });



        $('#wishlistExport').click(function() {
            if (status_check == 0) {
                Swal.fire("A filter must be applied before continuing with this action");
            } else {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to export Wishlist Report?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Export'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#wishlist_export').submit();
                    }
                });
            }
        });
    </script>
@endsection
