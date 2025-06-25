<div class="modal-dialog modal-dialog-centered" style="max-width:640px!important;">
    <div class="modal-content">
        @if(isset($coursePlan) && !empty($coursePlan))
        <form action="{{ route('course-prices.update',['course_price' => $coursePlan->id]) }}" method="post" id="editFreePlanForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_plan_id" id="edit_plan_id" value="{{ isset($coursePlan->id) && !empty($coursePlan->id) ? $coursePlan->id : '' }}">
            <input type="hidden" name="edit_course_id" id="edit_course_id" value="{{ isset($coursePlan->course_id) && !empty($coursePlan->course_id) ? $coursePlan->course_id : NULL }}">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit pricing plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2  {{ $coursePlan->plan_type == '0' ? '' : 'd-none' }}">
                        <div class=" form-check">
                            <input class="form-check-input" type="radio" name="selectedPlan" id="free-plan" checked value="free" {{  !empty($coursePlan->plan_type) && $coursePlan->plan_type == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="free-plan">Free</label>
                        </div>
                    </div>
                    <div class="col-md-5 {{ $coursePlan->plan_type == '1' ? '' : 'd-none' }}">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selectedPlan" id="one-time-payment-plan" value="one-time" {{  !empty($coursePlan->plan_type) && $coursePlan->plan_type == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="one-time-payment-plan">One time payment</label>
                        </div>
                    </div>
                    {{-- <div class="col-md-5 {{ $coursePlan->plan_type == '2' ? '' : 'd-none' }}">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="selectedPlan" id="recurring-plan" value="recurring" {{  !empty($coursePlan->plan_type) && $coursePlan->plan_type == '2' ? 'checked' : '' }}>
                            <label class="form-check-label" for="recurring-plan">Recurring subscription</label>
                        </div>
                    </div> --}}
                </div>
                <div class="row">
                    <div class="col-12">
                        <!-- Free plan text -->
                        <small class="text-muted plan-modal py-2 free free_plan" style="display: none">Learner can access the course without any payment.</small>
                        <!-- One time payment plan text -->

                        <small style="display: none" class="payment_plan text-muted plan-modal py-2 one-time {{  $coursePlan->plan_type == '1' ? '' : 'd-none' }}">Learner can access the course by making one time payment.</small>
                        <!-- Recurring subscription plan text -->
                        <!-- <p class="text-muted plan-modal py-2 recurring {{  $coursePlan->plan_type == '2' ? '' : 'd-none' }}">Recurring payments are only supported with Stripe, Razorpay and our default Graphy payment gateway.</p> -->

                        <!-- Display only when Free/One time payment -->
                        <div class="form-group plan-modal free one-time recurring">
                            <label for="plan_name">Plan name</label><span style="color: red">*</span>
                            <input type="text" class="form-control empty-data" value="{{ isset($coursePlan->plan_name) && !empty($coursePlan->plan_name) ? $coursePlan->plan_name : '' }}" name="plan_name" id="plan_name" placeholder="Plan name">
                        </div>
                        <div class="row plan-modal recurring {{  $coursePlan->plan_type == '2' ? '' : 'd-none' }}">
                            <div class="col-12 col-md-3">
                                <label for="edit_price">Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">&#8377;</span>
                                    </div>
                                    {{-- <label class="font-weight-bold">{{ $coursePlan->final_payable_price }}</label> --}}
                                    <input type="text" class="form-control float-number" name="edit_price" id="edit_price" value="{{isset($coursePlan->final_payable_price) && !empty($coursePlan->final_payable_price) ? $coursePlan->final_payable_price : '0.00' }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="bill_learner_every">Bill learner every</label>
                                <div class="input-group">
                                    {{-- <label class="font-weight-bold">{{ $coursePlan->bill_learner_every }}</label> --}}
                                    {{-- @if( $coursePlan->calendar == '1')
                                    <label class="font-weight-bold">Week - {{ $coursePlan->bill_learner_every }}</label>
                                    @endif
                                    @if( $coursePlan->calendar == '2')
                                    <label class="font-weight-bold">Month - {{ $coursePlan->bill_learner_every }}</label>
                                    @endif
                                    @if( $coursePlan->calendar == '3')
                                    <label class="font-weight-bold">Year - {{ $coursePlan->bill_learner_every }}</label>
                                    @endif --}}
                                    <input type="text" readonly style="width: 125px" class="form-control numeric" name="bill_learner_every" id="bill_learner_every" value="{{ isset($coursePlan->bill_learner_every) && !empty($coursePlan->bill_learner_every) ? $coursePlan->bill_learner_every : '0.00' }}" min="0" oninput="validity.valid||(value='');" step="0.01">
                                    {{-- <select class="custom-select form-control" id="inputGroupSelect01" name="calendar">
                                        <!-- <option >Choose...</option> -->
                                        <option value="1" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '1' ? 'selected' : '' }}>Week</option>
                                        <option value="2" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '2' ? 'selected' : '' }}>Month</option>
                                        <option value="3" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '3' ? 'selected' : '' }}>Year</option>
                                    </select> --}}
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="inputGroupSelect01">&nbsp;</label>
                                <div class="input-group">
                                    {{-- @if( $coursePlan->calendar == '1')
                                    <label class="font-weight-bold">Week</label>
                                    @endif
                                    @if( $coursePlan->calendar == '2')
                                    <label class="font-weight-bold">Month</label>
                                    @endif
                                    @if( $coursePlan->calendar == '3')
                                    <label class="font-weight-bold">Year</label>
                                    @endif --}}
                                    <select class="custom-select form-control" id="inputGroupSelect01" name="calendar" disabled>
                                        <!-- <option >Choose...</option> -->
                                        <option value="1" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '1' ? 'selected' : '' }}>Week</option>
                                        <option value="2" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '2' ? 'selected' : '' }}>Month</option>
                                        <option value="3" {{ isset($coursePlan->calendar) && $coursePlan->calendar == '3' ? 'selected' : '' }}>Year</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="row plan-modal recurring">
                                <div class="col-12 col-md-6">
                                    <label for="setup_fee">Setup Fee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">&#8377;</span>
                                        </div>
                                        <input type="number" class="form-control" name="setup_fee" id="setup_fee" value="{{ isset($coursePlan->setup_fee) && !empty($coursePlan->setup_fee) ? $coursePlan->setup_fee : '0' }}" min="0" oninput="validity.valid||(value='');" step="1">
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        {{-- @dd($coursePlan) --}}
                        <label>Apple In-App Purchase</label>
                        <input type="text" class="form-control" name="name" value="{{ @$renewingSubscriptions->name }}">
                      @php
                    //    || ($coursePlan->course_limit==1) || ($coursePlan->is_fixed_date==1)
                    // ($course->default_web_price)|| ($course->default_web_price) || ($course->default_web_price)||
                          $course = App\Models\Course::where("id",$coursePlan->course_id)->first();
                        //   dd(Carbon\Carbon::now()->format('Y-m-d'));
                      @endphp
                      {{-- @if(($coursePlan->access_value >= Carbon\Carbon::now()->format('Y-m-d') || ($coursePlan->status==1 )) || ($coursePlan->course_limit!=1 && $coursePlan->is_fixed_date!=1))--}}
                        @if(($coursePlan->access_value > Carbon\Carbon::now()->format('Y-m-d') && $coursePlan->is_fixed_date==1))
                        @else
                            <div class="form-group m-2 mt-3">
                                @include('admin.layouts.partials.buttons.toggle-button',[
                                'dataValue' => $coursePlan->status,
                                'id' => 'pstatus',
                                'name' => 'status',
                                'toggleBtnText' => 'Status',
                                ])
                            </div>
                        @endif

                        <!-- Display only when One time payment is selected -->
                        <!-- &#8377; is the Indian Currency (Rs) symbol -->
                        <div class="row plan-modal one-time {{  $coursePlan->plan_type == '1' ? '' : 'd-none' }}">
                            <div class="col-12 col-md-6">
                                <label for="edit_list_price">List price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">&#8377;</span>
                                    </div>
                                    <input type="text" class="form-control float-number" name="edit_list_price" readonly id="edit_list_price" value="{{ isset($coursePlan->list_price) && !empty($coursePlan->list_price) ? $coursePlan->list_price : '0.00' }}" min="0" oninput="validity.valid||(value='');" step="0.01">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="edit_final_payable_price">Final payable prices</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">&#8377;</span>
                                    </div>
                                    <input type="text" class="form-control float-number" name="edit_final_payable_price" readonly id="edit_final_payable_price" value="{{ isset($coursePlan->final_payable_price) && !empty($coursePlan->final_payable_price) ? $coursePlan->final_payable_price : '0.00' }}" min="0" oninput="validity.valid||(value='');" step="0.01">
                                </div>
                            </div>

                        </div>

                        <!-- <div class="mt-3 plan-modal recurring  {{  $coursePlan->plan_type == '2' ? '' : 'd-none' }}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input is_free_trial_included" id="is_free_trial_included" name="is_free_trial_included" {{ isset($coursePlan->is_trial_fee_included) && $coursePlan->is_trial_fee_included==1 ? 'checked="checked"' : '' }}>
                                <label class="form-check-label" for="is_free_trial_included">Include a free trial</label>
                            </div>
                        </div> -->
                        <div class="mt-3 plan-modal free one-time  {{  $coursePlan->plan_type == '1' || $coursePlan->plan_type == '0' ? '' : 'd-none' }}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input limit_course"  id="limit_courses" name="limit_course" {{ isset($coursePlan->course_limit) && $coursePlan->course_limit==1 ? 'checked="checked"' : '' }}>
                                <label class="form-check-label" for="limit_courses">Limit course access duration</label>
                            </div>
                        </div>

                    </div>



                    <div class="row form-group col-12 free one-time">
                        {{-- <div class="col-md-6 d-inline-flex p-2">
                            <div class="form-check fixed-date-picker {{$coursePlan->course_limit == 1 ? '' : 'd-none'}} fixed-date">
                                <input type="radio" class="form-check-input fixed_date" id="until_fixed_date" name="fixed_date" {{ isset($coursePlan->is_fixed_date) && $coursePlan->is_fixed_date==1 ? 'checked="checked"' : '' }} value="fixed_date">
                                <label class="form-check-label" for="until_fixed_date">Until fixed date</label>
                            </div>
                        </div>
                        <div class="col-md-6 free one-time">
                            <div class=" fixed-date-picker fixeddate {{  $coursePlan->is_fixed_date == 1 ? '' : 'd-none' }}">
                                <div class="input-group-append" data-target="#edit_fixed_date" data-toggle="datetimepicker">
                                    <input type="text" name="edit_fixed_date" id="edit_fixed_date" placeholder="YYYY-MM-DD" class="form-control datetimepicker-input" data-target="#edit_fixed_date" value="{{ $coursePlan->is_fixed_date == 1 && $coursePlan->access_value && !empty($coursePlan->access_value) ? $coursePlan->access_value : ''}}" />
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div> --}}
                        <br>
                        <div class="col-md-6 d-inline-flex p-2 mt-5">
                            <div class="form-check fixed-date-picker {{$coursePlan->course_limit == 1 ? '' : 'd-none'}} fixed-date">
                                <input type="radio" class="form-check-input fixed_date" id="until_fixed_dates" name="fixed_date" {{ isset($coursePlan->is_fixed_date) && $coursePlan->is_fixed_date==1 ? 'checked="checked"' : '' }}  value="fixed_date">
                                <div class="div123">
                                    <label class="form-check-label" for="until_fixed_dates">Until fixed date</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 free one-time">
                            <div class=" fixed-date-picker fixeddate {{  $coursePlan->is_fixed_date == 1 ? '' : 'd-none' }}">
                                <div class="input-group-append">
                                    <input type="text" name="edit_fixed_date" id="edit_fixed_date" placeholder="YYYY-MM-DD" readonly class="form-control" value="{{ $coursePlan->is_fixed_date == 1 && $coursePlan->access_value && !empty($coursePlan->access_value) ? date('d-m-Y', strtotime($coursePlan->access_value)) : ''}}" />
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-md-6 d-inline-flex p-2 ">
                            <div class="form-check fixed-date-picker fixed-days {{$coursePlan->course_limit == 1 ? '' : 'd-none'}}">
                                <input type="radio" class="form-check-input fixed_days" id="fixed_days" name="fixed_date" {{ isset($coursePlan->is_fixed_date) && $coursePlan->is_fixed_date==2 ? 'checked="checked"' : '' }} value="fixed_day">
                                <label class="form-check-label" for="edit_fixed_days">Until Specific number of days</label>
                            </div>
                        </div>
                        <div class="col-md-6 free one-time">
                            <div class=" fixed-date-picker fixeddays {{  $coursePlan->is_fixed_date == 2 ? '' : 'd-none' }}">
                                <input type="number" name="edit_fixed_days" id="edit_fixed_days" placeholder="days" class="form-control" value="{{ isset($coursePlan->access_value)  && $coursePlan->is_fixed_date == 2 && !empty($coursePlan->access_value) ? $coursePlan->access_value : ''}}" />

                            </div>
                        </div> --}}
                        <div class="col-md-6 d-inline-flex p-2 ">
                            <div class="form-check fixed-date-picker fixed-days {{$coursePlan->course_limit == 1 ? '' : 'd-none'}}">
                                <input type="radio" class="form-check-input fixed_days" id="fixed_dayss" name="fixed_date" {{ isset($coursePlan->is_fixed_date) && $coursePlan->is_fixed_date==2 ? 'checked="checked"' : '' }}  value="fixed_day">
                                <label class="form-check-label" for="fixed_dayss">Until Specific number of days</label>
                            </div>
                        </div>
                        <div class="col-md-6 free one-time">
                            <div class=" fixed-date-picker fixeddays {{  $coursePlan->is_fixed_date == 2 ? '' : 'd-none' }}">
                                <input type="text" name="edit_fixed_days" id="edit_fixed_days" placeholder="days" class="form-control numeric" readonly value="{{ isset($coursePlan->access_value)  && $coursePlan->is_fixed_date == 2 && !empty($coursePlan->access_value) ? $coursePlan->access_value : ''}}" />

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update Plan</button>
            </div>
        </form>
        @endif
    </div>
</div>

<script>
    //Validate Edit Pricing plan modal
    if ($("#editFreePlanForm").length > 0) {
        $('#editFreePlanForm').validate({
            rules: {
                selectedPlan: {
                    required: true,
                },
                plan_name: {
                    required: true,
                },
                // edit_list_price: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=selectedPlan]:checked").val() === 'one-time'
                //         }
                //     },
                //     min: 1,
                //     greaterThanEqual: '#edit_final_payable_price'
                // },
                // edit_final_payable_price: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=selectedPlan]:checked").val() === 'one-time'
                //         }
                //     },
                //     min: 1,
                //     lessThanEqual: '#edit_list_price'
                // },
                // edit_price: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=selectedPlan]:checked").val() === 'recurring'
                //         }
                //     },
                //     min: 1
                // },
                // bill_learner_every: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=selectedPlan]:checked").val() === 'recurring'
                //         }
                //     },
                //     min: 1
                // },
                // fixed_date: {
                //         required: function(element) {
                //             if ($("input[name=limit_course]:checked").val() === 'on') {
                //                 return true;
                //             }
                //         },
                //     },
                // edit_fixed_date: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=fixed_date]:checked").val() === 'fixed_date'
                //         }
                //     },
                // },
                // edit_fixed_days: {
                //     required: {
                //         depends: function(elem) {
                //             return $("input[name=fixed_date]:checked").val() === 'fixed_day'
                //         }
                //     },
                // }
            },
            messages: {
                selectedPlan: {
                    required: "Please select plan here",
                },
                plan_name: {
                    required: "Please enter the plan name"
                },
                // edit_list_price: {
                //     required: function(element) {
                //         if ($("input[name=selectedPlan]:checked").val() === 'one-time') {
                //             return "Please enter list price";
                //         } else {
                //             return false;
                //         }
                //     }
                // },
                // edit_final_payable_price: {
                //     required: function(element) {
                //         if ($("input[name=selectedPlan]:checked").val() === 'one-time') {
                //             return "Please enter final payable price";
                //         } else {
                //             return false;
                //         }
                //     }
                // },
                // edit_price: {
                //     required: function(element) {
                //         if ($("input[name=selectedPlan]:checked").val() === 'recurring') {
                //             return "Please enter price";
                //         } else {
                //             return false;
                //         }
                //     }
                // },
                // fixed_date: {
                //     required: function(element) {
                //         if($("input[name=limit_course]:checked").val() === 'on'){
                //             return "Please choose one Option";
                //         }
                //         else {
                //             return false;
                //         }
                //     },
                // },
                // edit_fixed_date: {
                //     required:function(element) {
                //             if($("input[name=fixed_date]:checked").val() === 'fixed_date'){
                //                 return "Please enter Date For Expire Plan";
                //             }
                //             else {
                //                 return false;
                //             }
                //     },
                // },
                // edit_fixed_days: {
                //     required:function(element) {
                //             if($("input[name=fixed_date]:checked").val() === 'fixed_day'){
                //                 return "Please enter Number of Days For Expire Plan";
                //             } else {
                //                 return false;
                //             }
                //     },
                // }
            },
            errorPlacement: function(error, element) {
                // if (element.attr("name") == "edit_list_price" || element.attr("name") == "edit_final_payable_price" || element.attr("name") == "edit_price" || element.attr("name") == "fixed_date" || element.attr("name") == "edit_fixed_date") {
                //     error.insertAfter(element.parent("div"));
                // } else {
                    error.insertAfter(element);
                // }
            },
            submitHandler: function(form) {
                event.preventDefault();
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    dataType: "json",
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response) {
                        $('#loader_section').hide();
                        console.log(response);
                        // If plan updated successfully then this if() will work.
                        $('#editFreePlanForm')[0].reset();
                        if (response.code == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'success',
                                text: 'Plan updated successfully!',
                            }).then(function() {
                                location.reload();
                            });
                        }
                    },
                    error: function(response) {
                        // Code for backend error like spell mistake, variable not define and etc.
                        if (response.responseJSON.code == 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.responseJSON.errors,
                            });
                        }

                        // Code for backend/Server side validation error
                        // var errorMessage = '';
                        // if(response.responseJSON.code == "server-error"){
                        //     $.each(response.responseJSON.errors, function (i, v) {
                        //         errorMessage += v[0] +"<br>";
                        //     });
                        //     Swal.fire({
                        //         icon: 'error',
                        //         title: 'Error',
                        //         html: errorMessage,
                        //     });
                        // }
                    },
                });
            }
        });
    }


    $(document).ready(function() {
        // var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
        // $("#edit_fixed_date").datetimepicker({
        //     format: 'YYYY-MM-DD',
        //   //  minDate: today
        // });


        // $("#free-plan").click(function(){
        // alert()
        // })

        // $(".limit_course").on('click', function() {
        //     if ($(this).is(':checked')) {
        //         $(".fixed-date").removeClass('d-none');
        //         $(".fixed-days").removeClass('d-none');
        //         // show
        //         // $("#edit_fixed_date").val();

        //         $(".fixed-date").trigger('click');
        //         $(".fixed-days").trigger('click');
        //     } else {
        //         $(".fixed-date input[type='radio']").prop('checked', false);
        //         $(".fixed-date").addClass('d-none');
        //         $(".fixed-days").addClass('d-none');
        //         // $("#edit_fixed_date").val('');
        //         // hide
        //         $(".fixeddays").addClass('d-none');
        //         $(".fixeddate").addClass('d-none');
        //     }
        // });
        // $(".fixed-date").on('click', function() {
        //     if ($(".fixed_date").is(':checked')) {
        //         $("#fixed_date").val('');
        //         $(".fixeddate").removeClass('d-none');
        //         $(".fixeddays").addClass('d-none');
        //         $("#fixed_days").val('');
        //     } else {
        //         $(".fixeddays").addClass('d-none');
        //         $("#fixed_days").val('');
        //         $(".fixeddate").addClass('d-none');
        //         // $("#date_time_picker").val('');
        //     }
        // });
        // $(".fixed-days").on('click', function() {
        //     if ($(".fixed_days").is(':checked')) {
        //         $(".fixeddays").removeClass('d-none');
        //         $(".fixeddate").addClass('d-none');
        //         //$(".datetimepicker-input").val('');
        //     } else {
        //         $(".fixeddays").addClass('d-none');
        //         $("#fixed_days").val('');
        //         $(".fixeddate").addClass('d-none');
        //        // $(".datetimepicker-input").val('');
        //     }
        // });
    });
</script>
