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
    @if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
    @endif
    @if(Session::has('msg'))
    <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif

    <div class="row mb-4">
        @if($user->hasRole('admin'))
            <div class="col-12">
                <h5>Filter by Instructors</h5>
                <form action="javascript:void(0)" class="justify-content-between" method="get">
                    <div class="row">
                        <div class="col-3">
                            <select name="users" id="instructors" class="custom-select" onchange="$(this)">
                                <option value="">Select Instructor</option>
                                @foreach($users as $key => $value)
                                    <option value="{{isset($value->user) ? $value->user->id:''}}">
                                        {{ isset($value->user) ? $value->user->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-2">
                            <select name="status" id="status" class="custom-select" onchange="$(this)">
                                <option value="">Select Status</option>
                                <option value="1">Published</option>
                                <option value="0">Unpublished</option>
                            </select>
                        </div> --}}
                        <div class="col-8"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                            <a href="" class="btn btn-primary">Clear</a>
                        </div>
                        <div class="col-1"><button class='btn btn-primary text-white export_pending_course'>Export</button></div>
                    </div>
                </form>
                
            </div>
        @endif
</div>
<form action="{{url('backoffice/get-pending-courses')}}" style="display: none;" id="frm"></form>
<table id="tbl_pending_courses" class="table table-bordered table-hove w-100 only_active">
    <thead class="thead-light">
        <tr class="text-center">
            <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
            <th scope="col" data-orderable="false">Image</th>
            <th scope="col">Title</th>
            <th scope="col">Instructor Name</th>
            <th scope="col">Active Plan</th>
            <th scope="col">Enroll Learner</th>
            <th scope="col">Status</th>
            <th scope="col">Created Date</th>
            <!-- <th scope="col">Type</th> -->
            {{-- <th scope="col">Action</th> --}}
        </tr>
    </thead>
</table>



@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list',[
    'dataTableJS' => 1,
    'select2' => 1,
    'dateRangePicker'=>1,
    ])

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script>
        $(document).ready(function() {



            
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
        // $('.course').select2();
        // let table;

        // let filter_data = {
        //     signup_date: "",
        //     course: "",
        //     city: "",
        //     state: "",
        //     country: "",
        //     email: "",
        //     mobile: "",
        //     instructor: "",
        //     price: 0,
        //     priceTo: 0,
        //     age: "",
        //     gender: "",
        //     payment_status: "",
        // };


        $(document).ready(function() {

            table = $('#tbl_pending_courses').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                responsive: true,
                ajax: "{{ url('backoffice/get-pending-courses') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                "drawCallback": function(settings) {
                    
                },
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: true,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        },
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'instructor_id',
                        name: 'instructor_id'
                    },
                    {
                        data: 'course_plan_count',
                        name: 'course_plan_count'
                    },
                    {
                            data: 'user_course_count',
                            name: 'user_course_count'
                        },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                    // {
                    //     data: 'type',
                    //     name: 'type'
                    // },
                   
                ],
                "order": [],
            });
       
            $('#btn_submit').click(function (event) {
                    event.preventDefault();
                    var user_id = $("#instructors").val();
                    // var status = $("#status").val();
                    // console.log(user_id);
                    // var courses = $("#courses").val();
                    table.destroy();
                    table = $('#tbl_pending_courses').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: false,
                        responsive: true,
                        ajax: "{{ url('backoffice/get-pending-courses') }}"+'?user_id='+user_id,
                        columnDefs: [{
                            className: 'text-center',
                            targets: '_all',
                            'checkboxes': {
                                'selectRow': true,
                            },
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
                                        },
                                    },
                                    {
                                        data: 'image',
                                        name: 'image'
                                    },
                                    {
                                        data: 'title',
                                        name: 'title'
                                    },
                                    {
                                        data: 'instructor_id',
                                        name: 'instructor_id'
                                    },
                                    {
                                        data: 'course_plan_count',
                                        name: 'course_plan_count'
                                    },
                                    {
                                        data: 'user_course_count',
                                        name: 'user_course_count'
                                    },
                                    {
                                        data: 'status',
                                        name: 'status'
                                    },
                                    {
                                        data: 'created_at',
                                        name: 'created_at'
                                    }

                                    // {
                                    //     data: 'action',
                                    //     name: 'action',
                                    //     orderable: false,
                                    //     searchable: false
                                    // },
                                ],
                        "order": []
                    });
                });

                $(document).on("click", "#btn_submit", function(event){
                    event.preventDefault();
                    var test = $("#frm").attr('action');
                    $(frm).attr('action', test+'?');
                    console.log(test);
                })
                $(document).ready(function(){
                    $('#instructors').select2({ allowClear: true, placeholder: "Select Instructor" });
                    // $('#status').select2({ allowClear: true, placeholder: "Select Status" });
                })
        });


            // $(document).on("change", "#filters", function() {
            //     let selected_val = parseInt($(this).find(":selected").val());
            //     switch (selected_val) {
            //         case 1:
            //             make_visible("signup_date");
            //             $('#signup_date').daterangepicker({
            //                 timePicker: true,

            //                 startDate: moment().startOf('month'),
            //                 endDate: moment(),
            //                 locale: {
            //                     format: 'DD/MM/YYYY'
            //                 }
            //             });
            //             break;
            //         case 2:
            //             make_visible("fltr_select_course");
            //             break;
            //         case 3:
            //             make_visible("email");
            //             break;
            //         case 4:
            //             make_visible("mobile");
            //             break;
            //         case 5:
            //             make_visible("fltr_select_city");
            //             break;
            //         case 6:
            //             make_visible("fltr_select_state");
            //             break;
            //         case 7:
            //             make_visible("fltr_select_country");
            //             break;
            //         case 8:
            //             make_visible("instructor");
            //             break;
            //         case 9:
            //             make_visible("price");
            //             break;
            //         case 10:
            //             make_visible("age");
            //             break;
            //         case 11:
            //             make_visible("gender");
            //             break;
            //         case 12:
            //             make_visible("payment_status");
            //             break;

            //         default:
            //             break;
            //     }
            // });





            // $(document).on("click", "#fltr_search", function() {
            //     let signup_date = $("#signup_date").val();
            //     let course = $("#fltr_select_course").find(":selected").val();
            //     let city = $(".city_value").val();
            //     let state = $("#fltr_select_state").find(":selected").val();
            //     let country = $("#fltr_select_country").find(":selected").val();
            //     let email = $("#email").val();
            //     let mobile = $("#mobile").val();
            //     let price = $("#price").val();
            //     let priceTo = $("#priceTo").val();
            //     let instructor = $("#instructor").val();
            //     let age = $("#age").val();
            //     let payment_status = $("#payment_status").val();
            //     let gender = $("#gender").find(":selected").val();

            //     filter_data.signup_date = signup_date;
            //     filter_data.course = course;
            //     filter_data.city = city;
            //     filter_data.state = state;
            //     filter_data.country = country;
            //     filter_data.email = email;
            //     filter_data.mobile = mobile;
            //     filter_data.instructor = instructor;
            //     filter_data.price = price;
            //     filter_data.priceTo = priceTo;
            //     filter_data.age = age;
            //     filter_data.gender = gender;
            //     filter_data.payment_status = payment_status;


            //     table_learner.ajax.reload();
            //     $('#loader_section').show()
            // });


        //     $(document).ready(function() {
        //         $('.select3').select2();
        //     })

        //     function validateNumericInput(input) {
        //         input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        //     }
        // })

        $(function () {
            $(".export_pending_course").click(function () {
                var user_seles = $(".user_sales").is(":checked");
                var ids = $("#tbl_pending_courses input:checked").map(function(){return $(this).data('id');}).get().join();
                // console.log(ids);
                // return;
                var url = "{{URL::to('backoffice/pending-course-export')}}?"+$.param({ids})
                window.location = url;

            });
        });



        // function remove(self, id, type) {

        //         var select2Element = $('#filters')
        //         select2Element.val([""]).trigger('change');
        //         var ids = $("#"+id);
        //         ids.val([""]).trigger('change');
        //         self.parents().eq(2).addClass('d-none');
        //         filter_data[id] = "";
        //         let total = $("#filter_super_parent").find("form").find('.row:visible').not(".btn_search").length;
        //         if (total <= 0) {
        //             // console.log($("#filter_super_parent").find("form").find("select"));
        //             $("#filter_super_parent").addClass('d-none');
        //             $("#filters").prop('selectedIndex', "");
        //             $("#filter_super_parent").find("form").find("select").prop('selectedIndex', "");
        //             $("#filter_super_parent").find("form").find("input").not("#fltr_search").val("");
        //             filter_data.signup_date = "";
        //             filter_data.course ="";
        //             filter_data.city = "";
        //             filter_data.state = "";
        //             filter_data.country = "";
        //             filter_data.email = "";
        //             filter_data.mobile = "";
        //             filter_data.instructor = "";
        //             filter_data.price = "";
        //             filter_data.priceTo = "";

        //             table_learner.ajax.reload();
        //         }
        //     }

        //     function make_visible(name) {
        //         if ($("#filter_super_parent").hasClass('d-none')) {
        //             $("#filter_super_parent").removeClass('d-none');
        //         }
        //         $("#" + name).parents().closest('.row').removeClass('d-none');
        //         // $("#"+name).prop('required',true);
        //     }


    </script>
@endsection
