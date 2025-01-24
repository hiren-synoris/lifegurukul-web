@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">

    @includeIf('admin.layouts.partials.styles.style', [
        'dropzoneCSS' => 1,
        'select2CSS' => 1,
    ])
    <style>
        label.error {
            display: block;
            width: 100%;

        }
    </style>
@endsection


@section('content')
    @if (Session::has('msg'))
        <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <div class=""><button class='btn btn-primary text-white export_learner float-right'>Export</button></div>
    <div class="col-12 d-flex align-items-center ">
        <div class="col-4">

        </div>
        <div class="col-4">
            <label for="data">Created Date</label>
            <input type="text" class="form-control float-right" name="date_range" id="date_range">
        </div>
        <div class="col-4 mt-4">
            <button class='btn btn-primary text-white search_btn' style=" margin-top: 9px;">Search</button>
            <button class='btn btn-primary text-white clear_btn' style=" margin-top: 9px;">Clear</button>
        </div>

    </div>

    <table id="tbl_activity_logs" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="">

                <th scope="col">Name</th>
                <th scope="col">Course Name</th>
                <th scope="col">Chapter Name</th>
                <th scope="col">Device Name</th>
                {{-- <th scope="col">Device id</th> --}}
                <th scope="col">Description</th>
                <th scope="col">Created At</th>

            </tr>
        </thead>
    </table>
@endsection

@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
        'dateRangePicker' => 1,
    ])

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    </script>



    <script>
        var table;
        var status_check = 0
        $('#date_range').daterangepicker({
            timePicker: true,
            startDate: moment().startOf('month'),
            endDate: moment(),
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        $("#date_range").val(" ")

        $(document).on("click", ".search_btn", function() {
            table.draw();
            if($("#date_range").val()) {
                status_check = 1
            }
        })
        $(document).on("click", ".clear_btn", function() {
            $("#date_range").val(" ")
            table.draw();
            status_check = 0
        })

        $(document).ready(function() {

            table = $('#tbl_activity_logs').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                // "order": [[ 4, 'desc' ]],
                ajax: {
                    url: "{{ route('user-activity-logs') }}",
                    data: function(d) {
                        d.date_range = $('#date_range').val()
                    }
                },

                columnDefs: [{
                    className: 'text-center',
                    targets: '_all',
                    'checkboxes': {
                        'selectRow': true,
                    },
                }],
                columns: [

                    {
                        data: 'learner_id',
                        name: 'learner_id'
                    },
                    {
                        data: 'course_id',
                        name: 'course_id'
                    },
                    {
                        data: 'chapter_id',
                        name: 'chapter_id'
                    },
                    {
                        data: 'device_name',
                        name: 'device_name',
                        orderable: false,
                    },
                    {
                        data: 'description',
                        name: 'description',
                        orderable: false,
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },


                ],
                "order": [],

            });

        })




        $(function() {
            $(".export_learner").click(function() {
                if (status_check == 0) {
                    Swal.fire("A filter must be applied before continuing with this action");
                } else {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to export Learner Activity Report?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Export'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var url = "{{ URL::to('backoffice/user-logs-activity-export') }}?" + $
                                .param({
                                    "date_range": $('#date_range').val()
                                })
                            window.location = url;
                        }
                    });
                }


            });
        });
    </script>
@endsection
