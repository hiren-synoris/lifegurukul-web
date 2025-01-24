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
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color:black !important
        }
    </style>
@endsection
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if (Session::has('msg'))
        <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif

    <div id="filter_div" class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-9">
                    <select class="custom-select select3 w-25" id="filters">
                        <option selected value="">Add Filters</option>
                        <option value="1">Enroll date</option>
                        <option value="9">Price</option>
                        <option value="2">Course/Package</option>
                        <option value="8">Instructor</option>
                        <option value="3">Email</option>
                        <option value="4">Mobile number</option>
                        <option value="5">City</option>
                        <option value="6">State</option>
                        <option value="7">Country</option>
                        <option value="10">Age</option>
                        <option value="11">Gender</option>
                        <option value="12">Payment Status</option>
                        {{-- <option value="13">Login in</option> --}}
                    </select>

                </div>
                <div class="form-check col-2 float-right">
                    {{-- <input class="form-check-input user_sales" style="margin-left: 107px" type="checkbox" id="gridCheck">
                    <label class="form-check-label" for="gridCheck">
                      Check Sales
                    </label> --}}

                </div>
                <div class=""><button class='btn btn-primary text-white export_learner'>Export</button></div>
            </div>





            @if (session()->has('failures'))
                <table class="table table-danger">
                    <tr>
                        <th>Row</th>
                        <th>Attribute</th>
                        <th>Errors</th>
                        <th>Value</th>
                    </tr>

                    @foreach (session()->get('failures') as $validation)
                        <tr>
                            <td>{{ $validation->row() }}</td>
                            <td>{{ $validation->attribute() }}</td>
                            <td>
                                <ul>
                                    @foreach ($validation->errors() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $validation->values()[$validation->attribute()] }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
            <div class="row my-5 d-none" id="filter_super_parent">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body pt-5 pl-5 pr-5 pb-2">
                            <form action="{{ url('backoffice/send_notifications') }}" method="POST">
                                @csrf
                                <div class="row d-none">
                                    <div class="col-12 d-flex justify-content-center align-items-center ">
                                        <div class="col-3">
                                            <label for="">Course Enrolled</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" id="fltr_select_course" multiple name="course[]">
                                                {{-- <option selected value="">Select Course</option> --}}
                                                @foreach ($courses as $value)
                                                    <option value="{{ $value->id }}">{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                            {{-- <input type="" id="fltr_select_course" class="form-control" name="signup_date" /> --}}
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_course','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="data">Enroll Date</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="form-control float-right" name="signup_date"
                                                id="signup_date">
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'signup_date','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="email">Email</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="email" id="email" class="form-control" name="email" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'email','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="payment_status">Payment Status</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select w-100" id="payment_status" name="payment_status">
                                                <option selected value="">Select Payment Status</option>
                                                <option value="1">Success</option>
                                                <option value="2">Failed</option>
                                                <option value="3">Free</option>
                                                <option value="4">Enrol by Admin</option>
                                                <option value="5">Zapier</option>

                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'payment_status','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="login_in">Login in</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select w-100" id="login_in"
                                                name="login_in">
                                                <option  value=""></option>
                                                <option value="1">Login in</option>

                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'login_in','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="age">Age</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" placeholder="Enter age" id="age" class="form-control"
                                                name="age" />
                                        </div>
                                        {{-- <div class="col-4">
                                            <input type="text" placeholder="To" id="ageTo" class="form-control"
                                                name="ageTo" />
                                        </div> --}}
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'age','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="price">Price</label>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" placeholder="from" id="price" class="form-control"
                                                name="price" />
                                        </div>
                                        <div class="col-4">
                                            <input type="text" placeholder="To" id="priceTo" class="form-control"
                                                name="priceTo" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'price','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center">
                                        <div class="col-3">
                                            <label for="mobile">Mobile Number</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" id="mobile" class="form-control" name="mobile"
                                                oninput="validateNumericInput(this)" />
                                        </div>
                                        <div class="col-1">
                                            <span class=""
                                                style="color: red !important; cursor: pointer !important;"
                                                onclick="remove($(this),'mobile','input')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_city">City</label>
                                        </div>
                                        <div class="col-8">

                                            <input type="text"
                                                class="form-control w-100 search_city autocompleteInput sl-label"
                                                id="fltr_select_city" name="city" placeholder="Search City">
                                            <input type="text" class="form-control w-100 search_city city_value sl-id"
                                                id="fltr_select_city" name="city" style="display: none">
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_city','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="gender">Gender</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select w-100" id="gender" name="gender">
                                                <option selected value="">Select Gender</option>
                                                <option value="1">Male</option>
                                                <option value="2">Female</option>
                                                <option value="3">Other</option>

                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'gender','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_state">State</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3 w-100" id="fltr_select_state"
                                                name="state">
                                                <option selected value="">Select State</option>
                                                @foreach ($states as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_state','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_country">Country</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_country">
                                                <option selected value="">Select Country</option>
                                                @foreach ($countries as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_country','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="instructor">Instructor</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="instructor">
                                                <option selected value="">Select Instructor</option>
                                                @foreach ($instructor as $value)
                                                    <option value="{{ $value->user->id }}">{{ $value->user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'instructor','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start align-items-center row my-2 btn_search">
                                    <div class="col-2">
                                        <input class="btn btn-primary" type="button" id="fltr_search" value="Search">
                                        <a href="" class="btn btn-primary">Clear</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="">
                {{-- <th scope="col"><input type="checkbox" id="main_learner_checkbox" --}}
                {{-- style="cursor: pointer;" /></th> --}}
                <!-- <th scope="col" data-orderable="false">Image</th> -->
                <th scope="col">Enroll Date</th>
                <th scope="col">Last login Date</th>
                {{-- <th scope="col">Last login</th> --}}
                <th scope="col">Name</th>
                <th scope="col">Plan id</th>
                <th scope="col">Course id</th>
                <th scope="col">Course</th>
                <th scope="col">Email</th>
                <th scope="col">Country code</th>
                <th scope="col">Mobile</th>
                <th>Expire at</th>
                <th scope="col">Actual Price</th>
                {{-- <th scope="col">GST Amount</th> --}}
                <th scope="col">Selling Price</th>
                <th scope="col">Coupon Disount Price</th>
                <th scope="col">Coupon name</th>
                <th scope="col">Used Coins</th>
                <th>Invoice No</th>
                <th>Payment Gateway</th>
                <th>Order_status</th>
                <th>Transaction id</th>

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

    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
    <script>
        $(document).ready(function() {



            var citys = @json($cities)

            $("#type").change(function() {
                if ($(this).val() == 2) {
                    $(".courses").show()
                } else {
                    $(".courses").hide()
                }
            })

            // $('#signup_date').daterangepicker({
            //     timePicker: true,

            //     startDate: moment().startOf('month'),
            //     endDate: moment(),
            //     locale: {
            //         format: 'DD/MM/YYYY'
            //     }
            // });

            // var firstDate = moment().startOf('month').format('DD/MM/YYYY');
            //     var currentDate = moment().format('DD/MM/YYYY');
            //     $("#signup_date").val(firstDate + ' - ' + currentDate);


            $("#main_learner_checkbox").click(function() {
                if ($(this).is(':checked')) {
                    $("#bulk-add-btn").removeClass('d-none');
                    $(this).prop('checked', true);
                    $(".children_checkbox").prop('checked', true);
                } else {
                    $("#bulk-add-btn").addClass('d-none');
                    $(this).prop('checked', false);
                    $(".children_checkbox").prop('checked', false);
                }
            });

            $(".autocompleteInput").autocomplete({
                source: '/backoffice/search-city',

                focus: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    $(".sl-id").val(ui.item.value);
                    return false;
                }


            });

            $('.platform').select2({
                placeholder: 'Select platforms',
                allowClear: true
            });

        })
    </script>



    <script>
        $('.course').select2();
        let table;

        let filter_data = {
            signup_date: "",
            course: "",
            city: "",
            state: "",
            country: "",
            email: "",
            mobile: "",
            instructor: "",
            price: 0,
            priceTo: 0,
            age: "",
            ageTo: "",
            gender: "",
            payment_status: "",
            login_in: "",
        };


        $(document).ready(function() {

            table_learner = $('#tbl_learners').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('learner_report') }}",
                    data: function(d) {
                        return $.extend(d, filter_data);
                    }
                },
                "drawCallback": function(settings) {
                    $('#loader_section').hide()

                },
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all',
                    'checkboxes': {
                        'selectRow': true,
                    },
                }],
                columns: [
                    // {
                    //     data: 'id',
                    //     name: 'id',
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function(data, type, row) {
                    //         return '<input type="checkbox" class="children_checkbox " name="checkbox_index[' +
                    //             data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                    //     },
                    // },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    // {
                    //     data: 'last_login_date',
                    //     name: 'last_login_date'
                    // },
                    {
                        data: 'last_login',
                        name: 'last_login',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'learner_id',
                        name: 'learner_id'
                    },
                    {
                        data: 'plan_id',
                        name: 'plan_id'
                    },
                    {
                        data: 'course_id',
                        name: 'course_id'
                    },
                    {
                        data: 'course_name',
                        name: 'course_name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'country_id',
                        name: 'country_id',
                        width: '100%',
                    },
                    {
                        data: 'mobile',
                        name: 'mobile',
                        width: '100%',
                    },
                    {
                        data: 'expire_at',
                        name: 'expire_at',
                        width: '100%',
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
                        data: 'invoice_id',
                        name: 'invoice_id'
                    },
                    {
                        data: 'payment_gateway',
                        name: 'payment_gateway'
                    },
                    {
                        data: 'order_status',
                        name: 'order_status'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },


                ],

            });


            $(document).on("change", "#filters", function() {
                let selected_val = parseInt($(this).find(":selected").val());
                switch (selected_val) {
                    case 1:
                        make_visible("signup_date");
                        $('#signup_date').daterangepicker({
                            timePicker: true,

                            startDate: moment().startOf('month'),
                            endDate: moment(),
                            locale: {
                                format: 'DD/MM/YYYY'
                            }
                        });
                        break;
                    case 2:
                        make_visible("fltr_select_course");
                        break;
                    case 3:
                        make_visible("email");
                        break;
                    case 4:
                        make_visible("mobile");
                        break;
                    case 5:
                        make_visible("fltr_select_city");
                        break;
                    case 6:
                        make_visible("fltr_select_state");
                        break;
                    case 7:
                        make_visible("fltr_select_country");
                        break;
                    case 8:
                        make_visible("instructor");
                        break;
                    case 9:
                        make_visible("price");
                        break;
                    case 10:
                        make_visible("age");
                        break;
                    case 11:
                        make_visible("gender");
                        break;
                    case 12:
                        make_visible("payment_status");
                        break;
                    case 13:
                        make_visible("login_in");
                        break;

                    default:
                        break;
                }
            });





            $(document).on("click", "#fltr_search", function() {
                let signup_date = $("#signup_date").val();
                // let course = $("#fltr_select_course").find(":selected").val();
                let course = $("#fltr_select_course").val();

                let city = $(".city_value").val();
                let state = $("#fltr_select_state").find(":selected").val();
                let country = $("#fltr_select_country").find(":selected").val();
                let email = $("#email").val();
                let mobile = $("#mobile").val();
                let price = $("#price").val();
                let priceTo = $("#priceTo").val();
                let instructor = $("#instructor").val();
                let age = $("#age").val();
                let ageTo = $("#ageTo").val();
                let payment_status = $("#payment_status").val();
                let gender = $("#gender").find(":selected").val();
                let login_in = $("#login_in").find(":selected").val();

                filter_data.signup_date = signup_date;
                filter_data.course = course;
                filter_data.city = city;
                filter_data.state = state;
                filter_data.country = country;
                filter_data.email = email;
                filter_data.mobile = mobile;
                filter_data.instructor = instructor;
                filter_data.price = price;
                filter_data.priceTo = priceTo;
                filter_data.age = age;
                filter_data.ageTo = ageTo;
                filter_data.gender = gender;
                filter_data.payment_status = payment_status;
                filter_data.login_in = login_in;


                table_learner.ajax.reload();
                $('#loader_section').show()
            });


            $(document).ready(function() {
                $('.select3').select2();
            })

            function validateNumericInput(input) {
                input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
            }
        })





        $(function() {
            $(".export_learner").click(function() {

                if (isFilterDataEmpty(filter_data)) {
                    Swal.fire("A filter must be applied before continuing with this action");
                } else {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to export Sales Report?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Export'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var user_seles = $(".user_sales").is(":checked");
                            var ids = $("#tbl_learners input:checked").map(function() {
                                return $(this).data('id');
                            }).get().join();
                            var url = "{{ URL::to('backoffice/learner-export') }}?" + $.param({
                                "user_seles": user_seles,
                                filter_data,
                                ids
                            })
                            window.location = url;
                        }
                    });
                }

            });
        });


        function isFilterDataEmpty(filter_data) {
            let isEmpty = true;

            $.each(filter_data, function(key, value) {

                if (value !== "" && value !== 0) {
                    isEmpty = false;
                    return false;
                }
            });

            return isEmpty;
        }


        function remove(self, id, type) {

            var select2Element = $('#filters')
            select2Element.val([""]).trigger('change');
            var ids = $("#" + id);
            ids.val([""]).trigger('change');
            self.parents().eq(2).addClass('d-none');
            filter_data[id] = "";
            let total = $("#filter_super_parent").find("form").find('.row:visible').not(".btn_search").length;
            if (total <= 0) {
                // console.log($("#filter_super_parent").find("form").find("select"));
                $("#filter_super_parent").addClass('d-none');
                $("#filters").prop('selectedIndex', "");
                $("#filter_super_parent").find("form").find("select").prop('selectedIndex', "");
                $("#filter_super_parent").find("form").find("input").not("#fltr_search").val("");
                filter_data.signup_date = "";
                filter_data.course = "";
                filter_data.city = "";
                filter_data.state = "";
                filter_data.country = "";
                filter_data.email = "";
                filter_data.mobile = "";
                filter_data.instructor = "";
                filter_data.price = "";
                filter_data.priceTo = "";
                filter_data.login_in = "";
                filter_data.payment_status = "";
                filter_data.age = "";

                table_learner.ajax.reload();
            }
        }

        function make_visible(name) {
            if ($("#filter_super_parent").hasClass('d-none')) {
                $("#filter_super_parent").removeClass('d-none');
            }
            $("#" + name).parents().closest('.row').removeClass('d-none');
            // $("#"+name).prop('required',true);
        }
    </script>
@endsection
