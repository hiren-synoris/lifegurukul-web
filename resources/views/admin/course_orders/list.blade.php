@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">

    @includeIf('admin.layouts.partials.styles.style', [
        'select2CSS' => 1,
    ])
@endsection
@section('right-section')
    <form action="{{ route('course_orders.export') }}" method="post">
        @csrf
        <input type="hidden" name="user_id_hidden" id="user_id_hidden" value="">
        <input type="hidden" name="course_id_hidden" id="course_id_hidden" value="">
        <input type="hidden" name="course_date_range_hidden" id="course_date_range_hidden" value="">
        <input type="hidden" name="payment_status_hidden" id="payment_status_hidden" value="">
        {{-- <button type="submit" class="btn btn-primary">Export</button> --}}
    </form>
@endsection
@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h5>Filter</h5>
            <!-- <form action="javascript:void(0)" method="get"> -->
                <div class="row">
                    <div class="col-3">
                        {{-- <select name="users" id="users" class="custom-select" onchange="$(this)">
                        <option value="">Select User</option>
                        @foreach ($users as $key => $value)
                        <option value="{{$value->id}}">{{$value->mobile}}
                            @if (!empty($value->name))
                            ({{ $value->name }})
                            @endif
                        </option>
                        @endforeach
                    </select> --}}
                        <input type="text" class="form-control w-100 autocompleteInput sl-label" id="users"
                            name="users" placeholder="Search Learner" data-id="">
                    </div>
                    <div class="col-3">
                        {{-- <select name="courses" id="courses" class="custom-select select3">
                        <option value="">Select Course</option>
                        @foreach ($courses as $key => $value)
                        <option value="{{$value->id}}">{{$value->title}}</option>
                        @endforeach
                    </select> --}}
                        <input type="text" class="form-control w-100 autocompleteInputCourse sl-label-course"
                            id="courses" name="courses" placeholder="Search course" data-id="">
                    </div>
                    <div class="col-3">
                        <select name="payment_status" id="payment_status" class="custom-select" style="    height: 43px;">
                            <option value="">Type</option>
                            <option value="1">Success</option>
                            <option value="4">Enrol by admin</option>
                            <option value="3">Free</option>
                            <option value="2">Failed</option>
                            <option value="5">Zapier</option>
                        </select>

                    </div>
                    <div class="col-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control float-right" name="courseorderrange"
                                id="course_date_range">
                        </div>

                    </div>

                    <div class="col-3 mt-2"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                        <button class="btn btn-primary" id="clear_filter">Clear</button>
                    </div>

                </div>
            <!-- </form> -->
            <form action="{{ url('backoffice/get-course-orders') }}" style="display: none;" id="frm"></form>
        </div>
    </div>

    <table id="tbl_course_orders" class="table table-bordered table-hove w-100">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Order Id</th>
                <th scope="col">Course/Package</th>
                <th scope="col">Name</th>
                <th scope="col">Learner Mobile</th>
                <!-- <th scope="col">Learner Email</th> -->
                <th scope="col">Status</th>
                <th scope="col">Transaction id</th>
                <th scope="col">Payment getway</th>
                <th scope="col">Invoice</th>
                <th scope="col">Actual Price</th>
                <th scope="col">Selling Price</th>
                <th scope="col">Coupon Disount Price</th>
                <th scope="col">Coupon name</th>
                <th scope="col">Coin Applied</th>
                <th scope="col">Created At</th>
                <th scope="col">Expire At</th>
                <th scope="col">Action</th>
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


            var firstDate = ""
            var lastDate = ""
            // $(document).on("click", "#course_date_range", function () {

            $('#course_date_range').daterangepicker({
                timePicker: true,
                startDate: moment().startOf('month'),
                endDate: moment(),
                locale: {
                    format: 'DD/MM/YYYY'
                }
            }, function(start, end, label) {
                firstDate = start.format('DD/MM/YYYY');
                lastDate = end.format('DD/MM/YYYY');
                $("#course_date_range").val(firstDate + ' - ' + lastDate);
            });
            // firstDate = moment().startOf('month').format('DD/MM/YYYY');
            // lastDate = moment().format('DD/MM/YYYY');
            // $("#course_date_range").val(firstDate + ' - ' + lastDate);
            // $("#course_date_range_hidden").val(firstDate + ' - ' + lastDate);
            // });

            var table = $('#tbl_course_orders').DataTable({
                "order": [
                    [0, 'desc']
                ],
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ url('backoffice/get-course-orders') }}",
                    data: function(data) {
                        data.user_id = $("#users").attr("data-id");
                        data.courses = $("#courses").attr("data-id");
                        data.daterange = $('#course_date_range_hidden').val();
                        data.payment_status = $('select#payment_status option:selected').val();
                    }
                },
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'title',
                        name: 'course.title'
                    },
                    {
                        data: 'name',
                        name: 'learner.name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile',
                        orderable: false,
                        searchable: false
                    },
                    // {
                    //     data: 'email',
                    //     name: 'email',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    {
                        data: 'status',
                        name: 'order_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },
                    {
                        data: 'payment_gateway',
                        name: 'payment_gateway'
                    },
                    {
                        data: 'invoice',
                        name: 'invoice'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    // {
                    //     data: 'text',
                    //     name: 'text'
                    // },
                    {
                        data: 'after_deduction_price',
                        name: 'after_deduction_price'
                    },

                    {
                        data: 'after_coupon_applied_deduction_price',
                        name: 'after_coupon_applied_deduction_price'
                    },
                    {
                        data: 'coupon_id',
                        name: 'coupon_id'
                    },
                    {
                        data: 'user_coin',
                        name: 'user_coin'
                    },

                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row) {

                            if (type === 'display') {
                                return moment(data).format('DD/MM/YYYY HH:mm:ss');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'expire_at',
                        name: 'expire_at',
                        render: function(data, type, row) {
                            if (data == '') {

                                return data;
                            } else {
                                if (type === 'display') {
                                    return moment(data).format('DD/MM/YYYY HH:mm:ss');
                                }
                                return data;
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],

            });


            $('#btn_submit').click(function(event) {
                event.preventDefault();
                $("#course_id_hidden").val($("#courses").attr("data-id"));
                $("#user_id_hidden").val($("#users").attr("data-id"));
                $("#payment_status_hidden").val($('select#payment_status option:selected').val());
                $("#course_date_range_hidden").val(firstDate + ' - ' + lastDate);
                table.ajax.reload();
            });

            $('#clear_filter').click(function(event) {
               location.reload();
            });

        });
        $(document).on("click", "#btn_submit", function(event) {
            event.preventDefault();
            var test = $("#frm").attr('action');
            $(frm).attr('action', test + '?');

        })
        // $(document).ready(function() {
        //     $('#users').select2();
        // })
        // $(document).ready(function() {
        //     $('.select3').select2();
        // })
    </script>
@endsection
