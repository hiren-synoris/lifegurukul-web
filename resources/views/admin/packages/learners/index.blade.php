@extends('admin.layouts.app')
@section('content')
    <h3>Learners{{-- ({{ isset($learners) && !empty($learners) ? $learners : 0 }}) --}}</h3>
    @can('browse_enroll')
        @can('browse_learners')
            @includeIf('admin.layouts.partials.buttons.enroll')
        @endcan
    @endcan
    <!-- Bulk Add Button -->
    @if ($course->packages->count() > 0)
        @includeIf('admin.layouts.partials.buttons.bulk-import', [
            'importUrl' => url('backoffice/learners/import-package/' . $courseId),
        ])
    @endif
    <!-- Contain Bulk Add functionalities-->
    @includeIf('admin.layouts.partials.buttons.bulk-import-sample', [
        'fileUrl' => asset('admin\sampleFiles\learner.xlsx'),
    ])

    <!-- Bulk delete Button -->
    @includeIf('admin.layouts.partials.buttons.bulk-delete')
    @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
        'bulkDelURL' => url('backoffice/usercourses/bulk_del'),
    ])
 <button class="btn btn-warning float-right sales_learner px-2 py-1 mr-1" id="import-sample"><i class="fa fa-download" aria-hidden="true"></i> Sales Report</button>

 @if(Session::has('msg'))
 <h2 class="text text-success text-center w-50">{{ Session::get('msg') }}</h2>
 @endif
 @if(Session::has('msg_error'))
 <h2 class="text text-center w-50">{{ Session::get('msg') }}</h2>
 @endif

    <table id="tbl_learners" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Mobile</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Enroll Date</th>
                <th scope="col">Expire Date</th>
                {{-- <th scope="col">Assigned Through</th> --}}
                <th scope="col">Status</th>
                {{-- <th scope="col">Expiry</th> --}}
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
    <div class="modal fade" id="enroll" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Enroll Learners
                        {{ isset($course->title) && !empty($course->title) ? 'For ' . ucfirst($course->title) : '' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="reset()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2 d-flex justify-content-end">
                            @includeIf('admin.layouts.partials.buttons.bulk-add')
                            <!-- <a id="delete-enroll-btn" class="btn btn-danger px-2 py-1 mr-1 d-none" href="javascript:void(0)" onclick="bulk_delete_enroll_learners()">
                                <i class="fas fa-trash-alt"></i>
                                <span class="pl-1">Bulk Delete</span>
                            </a> -->

                        </div>
                        <div class="col-12">
                            <table id="addable_learners" class="table table-bordered table-hove w-100 only_active"
                                style="display: none;">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th scope="col"><input type="checkbox" id="main_learner_checkbox"
                                                style="cursor: pointer;" /></th>
                                        <th scope="col">Mobile</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                            </table>
                            <div id="warning_msg" class="alert alert-warning" style="display:none;">
                                <strong>Warning!</strong> Kindly please add course in this package first!!
                            </div>
                        </div>

                    </div>

                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div> -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="price_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="price_modal_title" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="price_modal_title">Select Plan</h3>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="card mb-0">
                        <div class="card-body">
                            <input type="hidden" name="learnerId" id="learnerId">
                            <!-- <h4 class="subs-title text-center">Select Plan</h4> -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col"></th>
                                            <th scope="col">Plan Name</th>
                                            <th scope="col" class="text-center">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            use Carbon\Carbon;
                                            $filteredPlans = $plans->reject(function ($q) {
                                                return $q->is_fixed_date == 1 && $q->access_value <= Carbon::now();
                                            });
                                        @endphp
                                        @foreach ($filteredPlans as $key => $value)
                                            <tr>
                                                <td><input type="radio" name="plan_id" id="plan_{{ $value->id }}"
                                                        value="{{ $value->id }}" {{ $key == 0 ? 'checked' : '' }}></td>
                                                <td>{{ $value->plan_name ?? '' }}</td>
                                                <td class="text-center">
                                                    {{-- data-plan-number="{{ $checkoutArray['plan_number'] }}" --}}
                                                    <p>&#8377;{{ $value->final_payable_price }}</p>
                                                    {{-- <a class="btn btn-primary checkout-btn plans_cls" href="javascript:void(0)" onclick="add_multi({{ $value->id }})" data-plan-id="{{ $value->id }}"  data-url={{ route('checkout.payment.session.store') }}>Buy for &#8377;{{ $value->final_payable_price }}</a> --}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="add_multi($(this))">Enroll</button>
                </div>
            </div>
        </div>
        <div style="display: none !important;">
            <form action="" method="get" id="frm_enroll">
                <input type="submit" />
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
    ])

    <script>
        var table;
        var table1;

        $(function() {
            $(".sales_learner").click(function() {

                var ids = $("#tbl_learners input:checked").map(function() {
                    return $(this).data('id');
                }).get().join();
                // console.log(ids);
                var course_id = "{{ request()->route('id') }}"
                var url = "{{ URL::to('backoffice/sales-package-learner-report') }}?" + $.param({
                    course_id,
                    ids
                });
                // console.log(url);
                window.location = url;

            });
        });



        $(document).ready(function() {
            table = $('#tbl_learners').DataTable({
                "bDestroy": true,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get_learners_package/' . $courseId) }}",
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
                    $('#bulk_delete_frm [name^=bd]').attr("name", "");
                    $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                },
                columns: [{
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
                    {
                        data: 'expire_at',
                        name: 'expire_at'
                    },
                    {
                        data: 'order_status',
                        name: 'order_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [],
            });
        });
        $("#enroll").on('shown.bs.modal', function() {

            $.ajax({
                url: "{{ url('backoffice/check_coursePackage/' . $courseId) }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    console.log(response);
                    // return;
                    if (response === "true") {
                        //    alert('sdsad');
                        $('#addable_learners').show();
                        table1 = $('#addable_learners').DataTable({
                            "bDestroy": true,
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: "{{ url('backoffice/get_addable_learners/' . $courseId) }}",
                            columnDefs: [{
                                className: 'text-center',
                                targets: '_all'
                            }],
                            "drawCallback": function(settings) {
                                if ($("#main_learner_checkbox").is(":checked")) {
                                    $("#main_learner_checkbox").trigger("click");
                                    $("#main_learner_checkbox").prop("checked", true);
                                } else {
                                    $("#main_learner_checkbox").prop("checked", false);
                                }
                                $('#bulk_delete_frm [name^=bd]').attr("name", "");
                                $('#bulk_hard_delete_frm [name^=bd]').attr("name", "");
                            },
                            columns: [{
                                    data: 'id',
                                    name: 'id',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        return '<input type="checkbox" class="children_learner_checkbox" name="checkbox_index[' +
                                            data + ']" data-id=' + data +
                                            ' style="cursor: pointer;"/>';
                                    },
                                },
                                {
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
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                },
                            ],
                            "order": [],
                        });
                    } else {
                        // Response is not true, display warning message
                        // displayWarningMessage(response.warning_message);
                        // $('#addable_learners').hide();
                        $('#warning_msg').show();
                        // alert("Warning: Kindaly add corse in this package first!!");
                    }
                }
            });
        });

        // function show_plan(event,packageid,learnerid){
        //     event.preventDefault();
        //     let is_free = "{{ $course->is_free }}";
        //     let plans=parseInt("{{ $plans->count() }}");
        //     console.log(plans,is_free);
        //     if(is_free != '1' && plans > 0){
        //         $("#enroll").modal('hide');
        //         // $("#price_modal").modal('show');
        //     }
        //     else{
        //         add_multi($(this));
        //     }
        // }
        function show_plan(event, courseid, learnerid, flg = 'false') {
            event.preventDefault();
            // let free="{{ $course->is_free }}";
            // let plans="{{ $course->plans->count() }}";
            // // if(free || plans <=0){
            //     $("#enroll").modal('hide');
            //     $("#price_modal").modal('show');
            //     $("#learnerId").val(learnerid);
            // // }
            let free = "{{ $course->is_free }}";
            let plan_count = "{{ $course->plans->count() }}";
            if (plan_count <= 0) {
                console.log("1");
                add_multi(null, plan_count, learnerid);
            } else {
                console.log("2");
                // $("#enroll").modal('hide');
                $("#price_modal").modal('show');
                if (flg == 'true' && learnerid !== '') {
                    $(".children_learner_checkbox").prop('checked', false);
                    let y = $(".children_learner_checkbox").each(function() {
                        if ($(this).data('id') == learnerid) {
                            $(this).trigger('click');
                            return false;
                        }
                    });
                }
            }
        }

        function myFunctionForAdd() {
            // let courseid = "{{ $course->id }}";
            // // let is_free = "{{ $course->is_free }}" == 0;
            // // console.log(is_free);
            // let temp = $(".children_learner_checkbox:checked").map(function(){
            //     return courseid.concat(String($(this).data('id')));
            // }).get();
            // learnerid = temp.toString();
            // if("{{ $course->plans->isNotEmpty() }}"){
            //     show_plan(event,courseid,learnerid);
            // }
            // // add_multi();
            let courseid = "{{ $courseId }}";
            let is_free = "{{ $course->is_free }}";
            learnerid = '';
            let temp = $(".children_learner_checkbox:checked").map(function() {
                return learnerid.concat(String($(this).data('id')));
            }).get();
            learnerid = temp.toString();
            let plan_count = "{{ $course->plans->count() }}";
            if (plan_count > 0) {
                show_plan(event, courseid, learnerid);
            } else {
                add_multi(null, plan_count, learnerid);
            }
        }
        // function add_multi(self=null){
        //     let packageid = "{{ $courseId }}";
        //     learnerid = '';
        //     if(self){
        //         learnerid = $("#learnerId").val();// self.data(learnerid);
        //     }
        //     else{
        //         let temp = $(".children_learner_checkbox:checked").map(function(){
        //             return learnerid.concat(String($(this).data('id')));
        //         }).get();
        //         learnerid = temp.toString();
        //      }
        //     let plan_id = $("input[name='plan_id']:checked").val();
        //     let path = "{{ env('APP_URL') }}"+'/backoffice/package/addable/learners/'+packageid+'/'+learnerid+'/'+plan_id;
        //     $("#frm_enroll").attr('action',path);
        //     $("#frm_enroll").find('input[type="submit"]').trigger('click');
        // }
        function add_multi(self = null, plan_count = 0, learnerid = null) {
            console.log("3");
            let packageid = "{{ $courseId }}";
            // console.log(packageid);
            // let is_free = "{{ $course->is_free }}";
            if (learnerid == null) {
                learnerid = '';
                let temp = $(".children_learner_checkbox:checked").map(function() {
                    console.log(String($(this).data('id')));
                    return learnerid.concat(String($(this).data('id')));
                }).get();

                learnerid = temp.toString();
            }
            let plan_id = $("input[name='plan_id']:checked").val();
            let path = "{{ env('APP_URL') }}" + '/backoffice/package/addable/learners/' + packageid + '/' + learnerid +
                '/' + plan_id;
            console.log("path " + path);
            $("#frm_enroll").attr('action', path);
            $("#frm_enroll").find('input[type="submit"]').trigger('click');
        }
        $("#main_learner_checkbox").click(function() {
            if ($(this).is(':checked')) {
                $("#bulk-add-btn").removeClass('d-none');
                // $("#delete-enroll-btn").removeClass('d-none');
                $(this).prop('checked', true);
                $(".children_learner_checkbox").prop('checked', true);
                // $("#delete-enroll-btn").removeClass('d-none');
            } else {
                $("#bulk-add-btn").addClass('d-none');
                $(this).prop('checked', false);
                $(".children_learner_checkbox").prop('checked', false);
                // $("#delete-enroll-btn").addClass('d-none');
            }
        });

        $(document).on("click", ".children_learner_checkbox", function() {
            if ($(this).is(':checked')) {
                $("#bulk-add-btn").removeClass('d-none');
                // $("#delete-enroll-btn").removeClass('d-none');
                $(this).prop('checked', true);
                if ($(".children_learner_checkbox").is(":checked").length > 0) {
                    $("#bulk-add-btn").removeClass('d-none');
                    // $("#delete-enroll-btn").removeClass('d-none');
                }
            } else {
                $(this).prop('checked', false);
                if ($(".children_learner_checkbox:checked").length <= 0) {
                    $("#bulk-add-btn").addClass('d-none');
                    // $("#delete-enroll-btn").addClass('d-none');
                }
            }
        });

        function reset() {
            $("#main_learner_checkbox").prop("checked", false);
            $(".children_learner_checkbox").prop("checked", false);
        }
    </script>
@endsection
