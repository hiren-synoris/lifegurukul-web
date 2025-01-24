@extends('admin.layouts.app')
@section('right-section')
{!! redirect_to_back(route('learners.index')) !!}
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Learner Details</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                @if (isset($learner) && !empty($learner))
                @php
                $singleCountry = Helper::getCountries($learner->country_id);
                @endphp

                @if (isset($learner->profile_pic) && !empty($learner->profile_pic) && Storage::exists($learner->profile_pic))
                <div class="post">
                    <div class="user-block">
                        <strong>Profile Picture</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <img src="{{ Storage::url($learner->profile_pic) }}" alt="Profile Picture" srcset="" style="width: 120px; height:120px;">
                    </div>
                </div>
                @endif
                <div class="post">
                    <div class="user-block">
                        <strong>Name</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ isset($learner->name) && !empty($learner->name) ? $learner->name : '' }}</label>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Mobile</strong>
                    </div>
                    <div class="col-md-12 col-sm-12 flags-imgno">
                    @if ((isset($singleCountry) && !empty($singleCountry)) && ($learner->country_id) )
                        <label>
                            @if ($learner->country_id == $singleCountry->id)
                            {{-- <img width="50px" src="{{ URL::asset('/front/img/' . $singleCountry->flag) }}" alt="Flag"> --}}

                             +{{ $singleCountry->phonecode }} {{ isset($learner->mobile) && !empty($learner->mobile) ? $learner->mobile : '' }}
                            @endif
                        </label>
                        @else
                        <label>
                            {{ isset($learner->mobile) && !empty($learner->mobile) ? $learner->mobile : '' }}</label>
                        @endif
                        {{-- <label>{{ isset($learner->countryCode) && !empty($learner->countryCode) ? "+".$learner->countryCode : '' }}
                        {{ isset($learner->mobile) && !empty($learner->mobile) ? $learner->mobile : '' }}</label> --}}
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Email</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ isset($learner->email) && !empty($learner->email) ? $learner->email : '' }}</label>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Gender</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::MALE ? App\Models\Learner::MALE_LABEL : '' }}</label>
                        <label>{{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::FEMALE ? App\Models\Learner::FEMALE_LABEL : '' }}</label>
                        <label>{{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::OTHER ? App\Models\Learner::OTHER_LABEL : '' }}</label>
                    </div>
                </div>
                {{-- @dd($learner->d_o_b) --}}
                <div class="post">
                    <div class="user-block">
                        <strong>Date Of Birth</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ isset($learner->d_o_b) && !empty($learner->d_o_b) ? \Carbon\Carbon::parse($learner->d_o_b)->format('d/m/Y') : '' }}</label>
                    </div>
                </div>
                {{-- <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ isset($learner->created_at) && !empty($learner->created_at) ? $learner->created_at : '' }}</label>
            </div>
        </div> --}}

        <div class="row">
            <div class="post col-4">
                <div class="user-block">
                    <strong>Country</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->countryName) && !empty($learner->countryName) ? $learner->countryName : '' }}</label>
                </div>
            </div>
            <div class="post col-4">
                <div class="user-block">
                    <strong>State</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->stateName) && !empty($learner->stateName) ? $learner->stateName : '' }}</label>
                </div>
            </div>
            <div class="post col-4">
                <div class="user-block">
                    <strong>City</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->cityName) && !empty($learner->cityName) ? $learner->cityName : '' }}</label>
                </div>
            </div>
            <div class="post col-4">
                <div class="user-block">
                    <strong>Occupation</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->occupation) && !empty($learner->occupation) ? $learner->occupation : '' }}</label>
                </div>
            </div>
            <div class="post col-4">
                <div class="user-block">
                    <strong>Marital Status</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->marital_status) && !empty($learner->marital_status) ? $learner->marital_status : '' }}</label>
                </div>
            </div>
            <div class="post col-4">
                <div class="user-block">
                    <strong>Education</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->education) && !empty($learner->education) ? $learner->education : '' }}</label>
                </div>
            </div>
            <div class="post col-6">
                <div class="user-block">
                    <strong>Your interests</strong>
                </div>
                <div class="col-md-12 col-sm-12">
                    <label>{{ isset($learner->your_interests) && !empty($learner->your_interests) ? $learner->your_interests : '' }}</label>
                </div>
            </div>
            <div class="post col-6"></div>
        </div>
        @endif
    </div>
</div>
</div>
<div class="p-4">
    <h3>Activity log</h3>
    <table id="learner_activity_log" class="table table-bordered table-hove w-100">
        <thead class="thead-light">
            <th scope="col">Device Name</th>
            <th scope="col">Course Name</th>
            <th scope="col">Description</th>
            <th scope="col">Time</th>
        </thead>
    </table>
</div>
@if (auth()->user()->can('browse_course_orders'))
<div class="p-4">
    <h3>Purchase History</h3>
    <table id="tbl_course_orders" class="table table-bordered table-hove w-100">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Order Id</th>
                <th scope="col">Title</th>
                {{-- <th scope="col">Learner</th> --}}
                <th scope="col">Status</th>
                <th scope="col">Transaction id</th>
                <th scope="col">Payment getway</th>
                <th scope="col">Actual Price</th>
                {{-- <th scope="col">GST Amount</th> --}}
                <th scope="col">Selling Price</th>
                <th scope="col">Coupon Disount Price</th>
                <th scope="col">Coupon name</th>
                <th scope="col">Coin Applied</th>
                <th scope="col">Invoice</th>
                <th scope="col">Date & Time</th>
                <th scope="col">Expire At</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
</div>
@endif
@if (auth()->user()->can('edit_courses'))
<div class="p-4">
    @if (auth()->user()->roles[0]->name == 'admin')
    <button class="btn btn-danger float-right reset_btn" data-toggle="modal" data-target="#minusCoin"> Deduct Coin - </button>
    <button class="btn btn-success float-right mr-1 reset_btn" data-toggle="modal" data-target="#addCoin">Add Coin + </button>
    @endif
    <h3>My Wallet ({{ $total }})</h3>
    <table id="learner_coins_history" class="table table-bordered table-hove w-100">
        <thead class="thead-light">
            {{-- <th scope="col">Name</th> --}}
            <th scope="col">Coins</th>
            <th scope="col">Type</th>
            <th scope="col">Course name</th>
            <th scope="col">Chapter Name</th>
            <th scope="col">Comment</th>
            <th scope="col">Created at</th>
            <th scope="col">Action</th>
        </thead>
    </table>
</div>
@endif
<div class="modal" id="minusCoin">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Deduct Coin</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form class="minus_coin">
                    @csrf
                    <div class="form-group">
                        <label for="">Enter Coins <span style="color: red">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Coins" id="coin" name="coin">
                        <span class="error_coin text-danger error"></span>
                    </div>
                    <input type="hidden" name="learner_id" value="{{ $learner->id }}">
                    <div class="form-group">
                        <label for="">Comment<span style="color: red">*</span></label>
                        <textarea class="form-control" placeholder="Enter comment" id="comment" name="comment"></textarea>
                        <span class="error_comment text-danger error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary readonly_minus">Submit</button>
                </form>
            </div>

        </div>
    </div>
</div>
<div class="modal" id="addCoin">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add Coin</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <form class="add_coin">
                    @csrf
                    <div class="form-group">
                        <label for="">Enter Coins<span style="color: red">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Coins" id="coin" name="coin">
                        <span class="error_coin text-danger error"></span>
                    </div>
                    <input type="hidden" name="learner_id" value="{{ $learner->id }}">
                    <div class="form-group">
                        <label for="">Comment<span style="color: red">*</span></label>
                        <textarea class="form-control" placeholder="Enter comment" id="comment" name="comment"></textarea>
                        <span class="error_comment text-danger error"></span>
                    </div>

                    <button type="submit" class="btn btn-primary readonly_plus">Submit</button>
                </form>
            </div>
        </div>
    </div>



</div>
</div>
<!-- /.card-body -->
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


                $(".reset_btn").click(function(){
                    $('.add_coin')[0].reset();
                    $('.minus_coin')[0].reset();
                    $(".readonly_minus").removeAttr("disabled");
                    $(".error").hide();
                })

                var table = $('#tbl_course_orders').DataTable({
                    "order": [
                        [0, 'desc']
                    ],
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ url('backoffice/get-learner-course-orders') }}",
                        data: function(data) {
                            data.learner_id = "{{ $learner->id }}";
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
                        // {
                        //     data: 'name',
                        //     name: 'learner.name'
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
                            data: 'invoice',
                            name: 'invoice'
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
                                        return moment(data).format('DD/MM/YYYY');
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


                table_learner = $('#learner_coins_history')
                    .DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('get_learner_history') }}",
                            data: function(d) {
                                d.learner_id = "{{ $learner->id }}";
                            }
                        },
                        columnDefs: [{
                            className: 'text-center',
                            targets: '_all'
                        }],

                        columns: [
                            {
                                data: 'coins',
                                name: 'coins'
                            },
                            {
                                data: 'type',
                                name: 'type'
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
                                data: 'comment',
                                name: 'comment'
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
                        "order": []
                    });

                    table_learner_log = $('#learner_activity_log')
                    .DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('get_learner_activity_log') }}",
                            data: function(d) {
                                d.learner_id = "{{ $learner->id }}";
                            }
                        },
                        columnDefs: [{
                            className: 'text-center',
                            targets: '_all'
                        }],

                        columns: [
                            {
                                data: 'device_name',
                                name: 'device_name'
                            }, {
                                data: 'course_name',
                                name: 'course_name'
                            },
                            {
                                data: 'description',
                                name: 'description'
                            },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            }


                        ],
                        "order": []
                    });

            });


            $('.add_coin').submit(function(e) {

                // $(".readonly_btn").attr("disabled","disabled")
                $(".error").hide();

                e.preventDefault();
                var formData = $(this).serialize();
                $(".readonly_plus").attr("disabled","disabled")
                $.ajax({
                    url: '{{ route('add_coins') }}',
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $(".readonly_plus").removeAttr("disabled")
                        if(data.status==1) {
                            Swal.fire({
                                title: "Success",
                                text: "Coins added successfully",
                                icon: "success"
                            });
                            $('.add_coin')[0].reset();
                            $("#addCoin").modal("hide")
                        } else {
                            $.each(data.error, function(k, v) {
                                $(".error_" + k).show();
                                $(".error_" + k).html(v)
                            })
                        }
                        table_learner.ajax.reload();
                    },
                });
            });

            $('.minus_coin').submit(function(e) {
                e.preventDefault();
                $(".error").hide()
                $(".readonly_minus").attr("disabled","disabled")
                var formData = $(this).serialize();
                $.ajax({
                    url: '{{ route('minus_coin') }}',
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $(".readonly_minus").removeAttr("disabled")
                        if(data.status==1) {
                            Swal.fire({
                                title: "Success",
                                text: "Coins updated successfully",
                                icon: "success"
                            });

                            $("#minusCoin").modal("hide")
                            $('.minus_coin')[0].reset();

                        } else if(data.status==2) {
                            Swal.fire({
                                title: "Warning",
                                text: "Learner has ("+data.user_count+") coins. Coin deductions are not sufficient",
                                icon: "Warning"
                            });
                        } else {



                            $.each(data.error, function(k, v) {
                                $(".error_" + k).show();
                                $(".error_" + k).html(v)
                            })
                        }




                        table_learner.ajax.reload();
                    },


                });
            });
        </script>

@endsection
