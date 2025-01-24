@extends('admin.layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

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
            color: #000000 !important;
            margin-top: 2px !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__rendered {
            display: flow !important;
        }

        li.select2-selection__choice {
            padding-left: 30px !important;
            padding-right: 10px !important;
        }
    </style>
    <style>
        /* .input-list {
                                                                                                                                display: flex;
                                                                                                                                align-items: center;
                                                                                                                                gap: 10px;
                                                                                                                                margin: 10px 0 10px;
                                                                                                                            } */
    </style>
@endsection
@section('content')

    <div id="filter_div" class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-2">
                    <select class="custom-select select3" id="filters">
                        <option selected value="">Add Filters</option>
                        <option value="2">Course enrolled</option>

                        @if (Auth::user()->hasRole('admin'))
                            <option value="8">Instructor</option>
                        @endif
                        <option value="3">Email</option>
                        <option value="4">Mobile number</option>
                        <option value="5">City</option>
                        <option value="6">State</option>
                        <option value="7">Country</option>
                        <option value="9">Occupation</option>
                        <option value="10">Marital Status</option>
                        <option value="11">Education</option>
                        <option value="12">Your Interests</option>
                    </select>
                </div>


            </div>

            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif



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
                                            <label for="signed_up_date">Course Enrolled</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3 course-categories" id="fltr_select_course"
                                                name="course[]" multiple>
                                                <!-- <option selected value="">Select Course</option> -->
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
                                    <div class="col-12 d-flex align-items-center">
                                        <div class="col-3">
                                            <label for="mobile">Mobile Number</label>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" id="mobile" class="form-control" name="mobile"
                                                oninput="validateNumericInput(this)" />
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important; cursor: pointer !important;"
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
                                            {{-- <select class="custom-select select2 w-100 search_city"
                                            id="fltr_select_city" name="city"> --}}
                                            {{-- <option selected value="">Select City</option> --}}
                                            {{-- @foreach ($cities->chunk(10000) as $value)
                                            @foreach ($value as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                            @endforeach --}}
                                            {{--
                                        </select> --}}
                                            {{-- <input type="text" id="fltr_select_city" name="city"
                                            placeholder="Search City" class="form-control search_city" />
                                        <div class="card p-3">
                                            <div class="append_city"></div>
                                        </div> --}}
                                            <input type="text"
                                                class="form-control w-100 search_city autocompleteInput sl-label"
                                                id="fltr_select_city" name="city" placeholder="Search City">
                                            <input type="text" class="form-control w-100 search_city city_value sl-id"
                                                id="fltr_select_city" name="city" style="display: none">
                                            <input type="text" id="selectedCityNameInput" style="display: none">
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
                                                onclick="remove($(this),
                                        'fltr_select_state','select')"><i
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
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_occupation">Occupation</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_occupation">
                                                <option selected value="">Select Occupation</option>
                                                @foreach ($occupations as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_occupation','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_marital_status">Marital Status</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_marital_status">
                                                <option selected value="">Select Marital Status</option>
                                                @foreach ($marital_status as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_marital_status','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_educations">Education</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_educations">
                                                <option selected value="">Select Education</option>
                                                @foreach ($educations as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_educations','select')"><i
                                                    class="far fa-times-circle"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none my-2">
                                    <div class="col-12 d-flex align-items-center ">
                                        <div class="col-3">
                                            <label for="fltr_select_your_interests">Your Interests</label>
                                        </div>
                                        <div class="col-8">
                                            <select class="custom-select select3" style="width: 100%!important"
                                                id="fltr_select_your_interests" multiple>
                                                {{-- <option selected value="">Select Your Interests</option> --}}
                                                @foreach ($your_interests as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <span class="" style="color: red !important;cursor: pointer !important;"
                                                onclick="remove($(this),'fltr_select_your_interests','select')"><i
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
    {{-- <div class="form-check">
        <input class="form-check-input mt-2 check_all" type="checkbox" value="" id="check_all">
        <label class="form-check-label mt-2" for="check_all">
            Send All
        </label>
      </div> --}}

    <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                {{-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> --}}
                <th scope="col" data-orderable="false">Image</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Mobile</th>
                <th scope="col">Time</th>
            </tr>
        </thead>
    </table>



    <div class="card mt-5">
        {{-- <div class="card-header w-25 alert alert-secondary">
            WhatsApp Notification
        </div> --}}
        <div class="card-body w-50">
            <form method="POST" id="notify_wp_modal">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group campaigns_show">
                            {{-- <label for="content">Campaigns</label><br> --}}
                            {{-- <select class="custom-select campaigns_hide" style="width:300px !important;">
                                <option selected value="">Select Campaigns</option>
                            </select> --}}
                            <label for="content">Campaigns</label><br>
                            <select class="form-control get_template" name="campaigns" id="campaigns"">
                                <option selected value="">Select Campaigns</option>
                                @foreach ($data as $val)
                                    <option value="{{ $val->campaign_id }}">{{ $val->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="button mt-5" style="margin-top: 36px;"
                            class="btn btn-success fetch_campaigns">Fetch Latest Campaigns</button>
                    </div>

                </div>



                <div class="row">
                    <div class="form-group mt-3 col-md-12">
                        <label for="content">Content <span class='text-danger'>*</span></label>
                        <textarea class="form-control temp_content" readonly id="" name="content" rows="5"></textarea>
                    </div>
                </div>
                <div class="row media_var_hide" style="display:none">
                    <div class="form-group mt-3 col-md-12">
                        <label for="content">Media (Docs And Pdf) <span class='text-danger'>*</span></label>
                        <input type="file" name="media_var" class="form-control media_var" accept=".pdf,.docx"
                            style="margin-top: 3px;">

                        <input type="hidden" class="form-control type_media_var" data-type="" accept=".pdf,.docx"
                            style="margin-top: 3px;">
                        <label class="text-danger error_image" style="display: none">This field is required</label>
                    </div>
                </div>
                <p class="mt-2 below_var" style="display:none ">Below variables can be used for dynamic values
                </p>

                <div class="input_parameter">
                </div>
                <button type="submit" id="sendMessageButton" class="btn btn-primary">Send Whatsapp Message</button>

        </div>


        </form>
    </div>

    <br />

    <h4>History</h4>

    <table id="tbl_wp_notify" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Name</th>
                <th scope="col">Campaigns</th>
                <th scope="col">Description</th>
                <th scope="col">Filters</th>
                <th scope="col">Date</th>
                {{-- <th scope="col">View Learners</th> --}}
            </tr>
        </thead>
    </table>

    <div class="modal fade" id="notify_learner" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h5 class="modal-title" id="exampleModalLabel">Enroll Learners {{ isset($course->title) && !empty($course->title) ? "For ".ucfirst($course->title) : "" }}</h5> --}}
                    <h5 class="modal-title" id="exampleModalLabel">Notify Learners</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-end">
                            @includeIf('admin.layouts.partials.buttons.bulk-add')
                        </div>
                        <div class="col-12">
                            <table id="addable_learners" class="table table-bordered table-hove w-100 only_active">
                                <thead class="thead-light">
                                    <th scope="col">Mobiles</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Time</th>

                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'select2' => 1,
        'validateJS' => 1,
    ])
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script>
        let has_error = false
        $(document).ready(function() {
            var citys = @json($cities)

            $("#type").change(function() {
                if ($(this).val() == 2) {
                    var select2Element = $('.course_data')
                    select2Element.val([""]).trigger('change');
                    $(".courses").show()
                    $(".e_link").hide()
                    $("#external_link").val(" ")
                } else if ($(this).val() == 3) {
                    $(".e_link").show()
                    $("#external_link").val(" ")
                    $(".courses").hide()
                } else if ($(this).val() == 1) {
                    var select2Element = $('.course_data')
                    select2Element.val([""]).trigger('change');
                    $(".courses").hide()
                    $(".e_link").hide()
                    $("#external_link").val(" ")


                }
            })


            $(".autocompleteInput").autocomplete({
                source: '/backoffice/search-city',
                //     { label: "India", value: "IND" },
                //         { label: "America", value: "USA" },
                //           { label: "Pakistan", value: "PAK" },
                //             { label: "Iceland", value: "ICE" },
                //       { label: "Australia", value: "AUS" }
                // ]
                focus: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    $(".sl-id").val(ui.item.value);
                    $("#selectedCityNameInput").val(ui.item.label);
                    return false;
                }


            });

            // $("body").on('autocompleteselect', 'input.autocompleteInput', function (event,ui)     {
            //         // alert(ui.item.label);
            //         //alert(ui.item.value);
            //         console.log(ui.item);

            //     });


            $('.platform').select2({
                placeholder: 'Select platforms',
                allowClear: true
            });

        })


        $(function() {
            $('#signup_date').datepicker({
                dateFormat: 'dd/mm/yy',
                // startDate: '-3d'
            });
        });
    </script>


    <script>
        $('.course').select2();
        let table;
        var check_validation = true

        let filter_data = {
            signup_date: "",
            course: "",
            city: "",
            state: "",
            country: "",
            email: "",
            mobile: "",
            instructor: "",
            course_name: "",
            city_name: "",
            state_name: "",
            country_name: "",
            instructor_name: "",
            occupation: "",
            marital_status: "",
            education: "",
            your_interests: "",
            occupation_name: "",
            marital_status_name: "",
            education_name: "",
            your_interests_name: "",
        };


        $(document).ready(function() {
            table_learner = $('#tbl_learners')
                .DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "searching": false,
                    ajax: {
                        url: "{{ url('backoffice/get-learner') }}",
                        data: function(d) {
                            return $.extend(d, filter_data);

                        },
                        dataSrc: function(json) {
                            // Check the recordsTotal value and disable the button if it is 0
                            if (json.recordsTotal == 0) {
                                $('#sendMessageButton').hide();
                            } else {
                                $('#sendMessageButton').show();
                            }
                            return json.data;
                        },

                    },

                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    "drawCallback": function(settings) {

                        $('#loader_section').hide()

                    },
                    columns: [

                        {
                            data: 'profile_pic',
                            name: 'profile_pic'
                        }, {
                            data: 'name',
                            name: 'name'
                        }, {
                            data: 'email',
                            name: 'email'
                        }, {
                            data: 'mobile',
                            name: 'mobile'
                        }, {
                            data: 'created_at',
                            name: 'created_at'
                        }
                    ],
                    "order": []
                });



            $.validator.addMethod("extension", function(value, element, param) {
                param = typeof param === "string" ? param.replace(/,/g, '|') : "svg|png|jpe?g|gif";
                return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
            }, $.validator.format("The file must be required png|jpeg|jpg|svg."));


            // input_parameter
            $(".fetch_campaigns").click(function() {
                $(".get_template").empty()
                $.ajax({
                    url: "{{ route('fetch_campaigns') }}",
                    type: "get",
                    success: function(data) {
                        $(".campaigns_show").empty()
                        // $(".campaigns_hide").hide();
                        $(".campaigns_show").append(data);
                    }
                });
            })

            // $(".course_data").change(function() {
            $(document).on("change", ".course_data", function() {
                var course_indexId = $(this).attr('data-course_indexId')
                // alert(course_indexId)
                $.ajax({
                    url: "{{ route('get_course') }}",
                    type: "get",
                    data: {
                        course_id: $(this).val(),
                    },
                    success: function(data) {
                        // alert(course_indexId)
                        $(".course_plan" + course_indexId).empty()
                        $(".course_plan" + course_indexId).append(
                            "<option value=''>Select Plan</option>")
                        $.each(data, function(k, v) {
                            $(".course_plan" + course_indexId).append("<option value=" +
                                v.id + ">" + v
                                .plan_name + "</option>")
                        })
                    }
                });
            })

            $(document).on("change", ".get_template", function() {
                if ($(this).val() == "") {
                    $('.temp_content').val("")
                    $('.below_var').hide()
                    $('.input_parameter').empty()
                    $(".media_var_hide").hide()
                    $(".type_media_var").attr("data-type", "")
                    $(".error_image").hide();
                    return false
                }
                $(".media_var_hide").hide();
                $.ajax({
                    url: "{{ route('get_template') }}",
                    type: "get",
                    data: {
                        camp_id: $(this).val(),
                    },
                    success: function(data) {
                        $('.input_parameter').empty()
                        $('.temp_content').val(" ")
                        $('.below_var').show()
                        $(".temp_content").val(data.template.text)

                        if (data.template.type == "FILE") {
                            $(".media_var_hide").show()
                            $(".type_media_var").attr("data-type", data.template.type)
                        } else {
                            $(".media_var_hide").hide()
                            $(".type_media_var").attr("data-type", "")
                            $(".error_image").hide();
                        }


                        $('.input_parameter').append(
                            '<input type="hidden" name="assistant_name" value="' + data
                            .template
                            .assistant_name +
                            '" class="form-control mt-1 w-100" id="textbox0" />');
                        $('.input_parameter').append(data.data);

                    }
                });
            })


            $(document).on("change", ".manu_list", function() {
                var selectedOption = $(this).find('option:selected');
                var dataId = selectedOption.data('id');
                var other = selectedOption.data('other');

                var value = $(this).val();

                $(".label_" + dataId).text(selectedOption.text()).append(
                    "<span class='text-danger'>*</span>");


                $("." + dataId).hide()

                $(".media_var_" + other).hide()
                $(".courses_wp_" + other).hide();
                $(".plan_wp_" + other).hide();
                $(".days_wp_" + other).hide();
                $(".coupons_" + other).hide();

                switch (value) {
                    case "amount":
                        $(".courses_wp_" + other).show();
                        $(".plan_wp_" + other).show();
                        break;
                    case "course_name":
                        $(".courses_wp_" + other).show();
                        break;
                    case "pre_validity":
                        $(".courses_wp_" + other).show();
                        $(".plan_wp_" + other).show();
                        break;
                    case "expired_date":
                        $(".courses_wp_" + other).show();
                        $(".days_wp_" + other).show();
                        break;
                    case "coupon_code":
                        $(".coupons_" + other).show();
                        break;
                    case "metting_id":
                        $("." + dataId).show();
                        break;
                    case "metting_url":
                        $("." + dataId).show();
                        break;
                    case "metting_pass":
                        $("." + dataId).show();
                        break;
                    case "media_var":
                        $(".media_var_" + other).show();
                        break;
                    case "regi_link":
                        $("." + dataId).show();
                        break;
                    case "event_name":
                        $("." + dataId).show();
                        break;
                    case "event_date":
                        $("." + dataId).show();
                        break;
                    case "event_time":
                        $("." + dataId).show();
                        break;
                    case "var_link":
                        $("." + dataId).show();
                        break;
                    case "date":
                        $("." + dataId).show();
                        break;
                    case "time":
                        $("." + dataId).show();
                        break;
                    default:
                        $(".media_var_" + other).hide()
                        $(".courses_wp_" + other).hide();
                        $(".plan_wp_" + other).hide();
                        $(".days_wp_" + other).hide();
                        $(".coupons_" + other).hide();
                        $("." + dataId).hide()

                }
            });


            // $(document).on("change", '.campaigns', function() {
            //     var key = $(this).val()
            //     var selectedOption = $(this).find('option:selected');
            //     var empty = selectedOption.data('other');

            //     if (key=="") {
            //         $(".courses_wp_" + empty).hide()
            //         $(".coupons_" + empty).hide()
            //         $(".days_wp_" + empty).hide()
            //         $(".plan_wp_" + empty).hide()
            //         $(".common_input" + empty).hide()
            //     } else {
            //         $(".courses_wp_" + empty).show()
            //         $(".coupons_" + empty).show()
            //         $(".days_wp_" + empty).show()
            //         $(".plan_wp_" + empty).show()
            //         $(".common_input" + empty).show()
            //     }
            // })



            $(document).on("click", "#sendMessageButton", function() {


                if (isFilterDataEmpty(filter_data)) {
                    Swal.fire("A filter must be applied before continuing with this action");
                } else {





                    has_error = true
                    for (var j = 1; j <= $(".total_parameters_").val(); j++) {

                        if ($(".campaigns_" + j).val() == "") {
                            $(".var_error_" + j).show();
                            has_error = false
                        } else {
                            $(".var_error_" + j).hide();

                            if ($(".campaigns_" + j).val() == "course_name") {
                                if ($(".course" + j).val() == "") {
                                    $(".course_error_" + j).show();

                                    has_error = false
                                } else {
                                    $(".course_error_" + j).hide();
                                    // has_error = true
                                }
                            }

                            if ($(".campaigns_" + j).val() == "coupon_code") {
                                if ($(".coupons" + j).val() == "") {
                                    $(".coupon_error_" + j).show()
                                    has_error = false
                                } else {
                                    $(".coupon_error_" + j).hide();
                                    // has_error = true
                                }
                            }

                            if ($(".campaigns_" + j).val() == "amount") {
                                if ($(".course" + j).val() == "") {
                                    $(".course_error_" + j).show();
                                    has_error = false
                                } else {
                                    $(".course_error_" + j).hide();
                                    // has_error = true
                                }
                                if ($(".course_plan" + j).val() == "") {
                                    $(".plan_error_" + j).show()
                                    has_error = false
                                } else {
                                    $(".plan_error_" + j).hide();
                                    // has_error = true
                                }
                            }


                            if ($(".campaigns_" + j).val() == "expired_date") {
                                if ($(".days" + j).val() == "") {
                                    $(".days_error_" + j).show()
                                    has_error = false
                                } else {
                                    $(".days_error_" + j).hide();
                                    // has_error = true
                                }

                                if ($(".course" + j).val() == "") {
                                    $(".course_error_" + j).show();
                                    has_error = false
                                } else {
                                    $(".course_error_" + j).hide();
                                    // has_error = true
                                }
                            }

                            if ($(".campaigns_" + j).val() == "pre_validity") {
                                if ($(".course" + j).val() == "") {
                                    $(".course_error_" + j).show();
                                    has_error = false
                                } else {
                                    $(".course_error_" + j).hide();
                                    // has_error = true
                                }
                                if ($(".course_plan" + j).val() == "") {
                                    $(".plan_error_" + j).show()
                                    has_error = false
                                } else {
                                    $(".plan_error_" + j).hide();
                                    // has_error = true
                                }
                            }


                            var campaignsValue = $(".campaigns_" + j).val();
                            if ((campaignsValue !== "name") && (campaignsValue !== "mobile") && (
                                    campaignsValue !== "email") &&
                                (campaignsValue !== "coin_redeem") &&
                                (campaignsValue !== "coin_earn") &&
                                (campaignsValue !== "course_name") &&
                                (campaignsValue !== "amount") &&
                                (campaignsValue !== "pre_validity") &&
                                (campaignsValue !== "expired_date") &&
                                (campaignsValue !== "coupon_code") &&
                                (campaignsValue !== "expired_date")

                            ) {
                                if ($(".common_input" + j).val() == "") {
                                    $(".common_input_error_" + j).show();
                                    has_error = false;

                                } else {
                                    $(".common_input_error_" + j).hide();
                                    // has_error = true;
                                }
                            } else {
                                // has_error = true
                            }

                        }
                    }
                }

                if ($(".type_media_var").attr("data-type") === "FILE") {
                    $(".media_var_hide").show();
                    var fileInput = $('.media_var');
                    var file = fileInput[0].files[0];
                    // alert(file)
                    if (file === undefined) {
                        $(".error_image").show();
                        return false;
                    } else {
                        $(".error_image").hide();
                        // has_error = true;
                    }
                } else {
                    $(".error_image").hide();
                }

            })


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






            $("#notify_wp_modal").validate({
                rules: {

                    campaigns: {
                        required: true,
                    },
                    media_var: {
                        extension: "pdf|docx",
                    }

                },
                messages: {
                    media_var: {
                        extension: "Only Pdf or Docs files are allowed.",
                    },
                },

                submitHandler: function(form, event) {

                    // alert(has_error)
                    // return false
                    if (has_error == false) {
                        return false
                    }

                    event.preventDefault();
                    var formData = new FormData($("#notify_wp_modal")[0]);
                    var campaigns = $('#campaigns option:selected');
                    var campaigns_id = campaigns.val();
                    var campaigns_name = campaigns.text();

                    // $('.media_var').each(function() {
                    //     var index = $(this).data('index');
                    //     var files = $(this).prop('files');

                    //     for (var i = 0; i < files.length; i++) {
                    //         formData.append('media_var[' + index + ']', files[i]);
                    //     }
                    // });

                    var imageFile = $('.media_var').prop('files')[0];
                    formData.append('media_var', imageFile);

                    formData.append('campaigns_id', campaigns_id);
                    formData.append('campaigns_name', campaigns_name);

                    Object.keys(filter_data).forEach(function(key) {
                        formData.append(key, filter_data[key]);
                    });

                    $.ajax({
                        url: "{{ route('send_wp_mssage') }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(data) {
                            $('#loader_section').hide();
                            $('.hide_variable').hide();
                            Swal.fire(data.status);
                            table_learner.ajax.reload();
                            $("#notify_wp_modal")[0].reset()
                            $('#tbl_wp_notify').DataTable().ajax.reload();
                            $(".error_image").hide();
                            $(".media_var_hide").hide();
                        }
                    });
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href");
                $('.tab-pane form').each(function() {
                    this.reset();
                });
                $(target).find('form')[0].reset();
            });





            $(document).on("click", ".notify_learner", function() {
                ids = $(this).attr("data-id")
                $('#addable_learners').DataTable({
                    "bDestroy": true,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('notify_learners') }}",
                        data: function(d) {
                            d.manual_notify_id = ids
                        }
                    },

                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],

                    columns: [{
                            data: 'mobile',
                            name: 'mobile'
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
                            data: 'created_at',
                            name: 'created_at'
                        },

                    ],
                    "order": [],

                })
            })




            var table = $('#tbl_wp_notify').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('wp_notify_history') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'title',
                        name: 'title',
                    },

                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'filters',
                        name: 'filters'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     width: "17%"
                    // },
                ],
                order: [
                    [4, 'desc']
                ],
            });

        });

        $(document).on("change", "#filters", function() {
            let selected_val = parseInt($(this).find(":selected").val());
            switch (selected_val) {
                case 1:
                    make_visible("signup_date");
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
                    make_visible("fltr_select_occupation");
                    break;
                case 10:
                    make_visible("fltr_select_marital_status");
                    break;
                case 11:
                    make_visible("fltr_select_educations");
                    break;
                case 12:
                    make_visible("fltr_select_your_interests");
                    break;

                default:
                    break;
            }
        });


        $(document).on("click", "#fltr_search", function() {
            let signup_date = $("#signup_date").val();
            let course = $("#fltr_select_course").val();
            let city = $(".city_value").val();
            let state = $("#fltr_select_state").find(":selected").val();
            let country = $("#fltr_select_country").find(":selected").val();
            let occupation = $("#fltr_select_occupation").find(":selected").val();
            let marital_status = $("#fltr_select_marital_status").find(":selected").val();
            let education = $("#fltr_select_educations").find(":selected").val();
            let your_interests = $("#fltr_select_your_interests").val();
            let email = $("#email").val();
            let mobile = $("#mobile").val();
            let instructor = $("#instructor").val();

            let course_name = $("#fltr_select_course").find(":selected").text();
            //let city_name =$("#test_city").val();
            let state_name = $("#fltr_select_state").find(":selected").text();
            let country_name = $("#fltr_select_country").find(":selected").text();
            let occupation_name = $("#fltr_select_occupation").find(":selected").text();
            let marital_status_name = $("#fltr_select_marital_status").find(":selected").text();
            let education_name = $("#fltr_select_educations").find(":selected").text();
            let your_interests_name = $("#fltr_select_your_interests").find(":selected").map(function() {
                return $(this).text();
            }).get();
            let instructor_name = $("#instructor").find(":selected").text();
            let city_name = $("#selectedCityNameInput").val();

            filter_data.signup_date = signup_date;
            filter_data.course = course;
            filter_data.city = city;
            filter_data.state = state;
            filter_data.country = country;
            filter_data.email = email;
            filter_data.mobile = mobile;
            filter_data.instructor = instructor;
            filter_data.course_name = course_name;
            filter_data.city_name = city_name;
            filter_data.state_name = state_name;
            filter_data.country_name = country_name;
            filter_data.instructor_name = instructor_name;
            filter_data.occupation = occupation;
            filter_data.marital_status = marital_status;
            filter_data.education = education;
            filter_data.your_interests = your_interests;
            filter_data.occupation_name = occupation_name;
            filter_data.marital_status_name = marital_status_name;
            filter_data.education_name = education_name;
            filter_data.your_interests_name = your_interests_name;

            $('#loader_section').show()
            table_learner.ajax.reload();
        });

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
                filter_data.occupation = "";
                filter_data.marital_status = "";
                filter_data.education = "";
                filter_data.your_interests = "";
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

        $(document).ready(function() {
            $('.select3').select2();
        })
        $(".course_data").select2({
            // dropdownParent: $("#notify_frm")
        });

        function validateNumericInput(input) {
            input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        }
    </script>
@endsection
