<div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px!important;">
        <div class="modal-content">
            <form action="{{ route('course-prices.store') }}" class="pricingPlanForm" method="post" id="pricingPlanForm">
                @csrf
                <input type="hidden" name="course_id"
                    value="{{ isset($course) && !empty($course->id) ? $course->id : null }}">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="exampleModalLabel">Pricing Plan Add For
                            {{ isset($course->title) && !empty($course->title) ? ucfirst($course->title) : '' }}</h5>
                    </div>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selectedPlan" id="free-plan"
                                    value="free">
                                <label class="form-check-label" for="free-plan">Free</label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selectedPlan"
                                    id="one-time-payment-plan" value="one-time">
                                <label class="form-check-label" for="one-time-payment-plan">One time payment</label>
                            </div>
                        </div>
                        {{-- <div class="col-md-5">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="selectedPlan" id="recurring-plan"
                                    value="recurring">
                                <label class="form-check-label" for="recurring-plan">Recurring subscription</label>
                            </div>
                        </div> --}}
                    </div>
                    <div class="row" id="allPlan">
                        <div class="col-12">
                            <!-- Free plan text -->
                            <small class="text-muted plan-modal py-2 free">Learner can access the course without any
                                payment.</small>

                            <!-- One time payment plan text -->
                            <small class="text-muted plan-modal py-2 one-time">Learner can access the course by making
                                one time payment.</small>
                            <!-- Recurring subscription plan text -->
                            <!-- <p class="text-muted plan-modal py-2 recurring">Recurring payments are only supported with Stripe, Razorpay and our default Graphy payment gateway.</p> -->

                            <!-- Display only when Free/One time payment -->
                            @if (config('settings.razorpay_status') == '1')
                                <div class="form-group plan-modal free one-time recurring mt-2">
                                    <label for="plan_name">Plan name</label><span style="color: red">*</span>
                                    <input type="text" class="form-control empty-data" value="{{ old('plan_name') }}"
                                        name="plan_name" id="plan_name" placeholder="Plan name">
                                </div>
                            @endif
                            @if (!config('settings.razorpay_status') == '1')
                                <div class="add_plan">
                                </div>
                            @endif
                            <div class="renewing_subscriptions">
                                <label class="renewing_subscriptions">Apple In-App Purchase</label><br>
                                <select class="custom-select form-control renewing_subscriptions w-50"
                                    id="renewing_subscriptions" name="renewing_subscriptions">
                                    <option value="">Select Renewing Subscriptions</option>
                                    @foreach ($renewingSubscriptions as $renewingSubscriptionss)
                                        <option value="{{ $renewingSubscriptionss->productId }}">
                                            {{ $renewingSubscriptionss->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success fetch_subscriptions">Fetch Subscriptions</button>
                            </div>

                            {{-- <div class="form-group m-2 mt-3">
                                @include('admin.layouts.partials.buttons.toggle-button',[
                                'dataValue' => 1,
                                'id' => 'pstatus',
                                'name' => 'status',
                                'toggleBtnText' => 'Status',
                                ])
                            </div> --}}


                            <!-- Display only when One time payment is selected -->
                            <!-- &#8377; is the Indian Currency (Rs) symbol -->
                            @if (config('settings.razorpay_status') == '1')
                                <div class="form-group mt-3">
                                    @include('admin.layouts.partials.buttons.toggle-button', [
                                        'id' => 'plan_status',
                                        'name' => 'status',
                                        'toggleBtnText' => 'Status',
                                        'dataValue' => 1,
                                    ])
                                </div>
                            @endif
                            @if (!config('settings.razorpay_status') == '1')
                                <div class="form-group mt-3 add_status">
                                    {{-- @include('admin.layouts.partials.buttons.toggle-button', [
                                        'id' => 'plan_status',
                                        'name' => 'status',
                                        'toggleBtnText' => 'Status',
                                        'dataValue' => 1,
                                    ]) --}}
                                </div>
                            @endif
                            <div class="row plan-modal one-time">
                                <div class="col-12 col-md-6">

                                    <label for="list_price">List price</label><span style="color: red">*</span>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">&#8377;</span>
                                        </div>
                                        <input type="text" class="form-control float-number" name="list_price"
                                            id="list_price" value="{{ old('list_price') ?? 0 }}" />
                                    </div>
                                    <span id="max_number" style="color:red"> </span>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="final_payable_price">Final payable price</label><span
                                        style="color: red">*</span>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">&#8377;</span>
                                        </div>
                                        <input type="text" class="form-control float-number"
                                            name="final_payable_price" id="final_payable_price"
                                            value="{{ old('final_payable_price') ?? 0 }}" />
                                    </div>
                                    <span id="payable_number" style="color:red"> </span>
                                </div>


                            </div>
                            <div class="row plan-modal recurring">
                                @if (config('settings.instamojo_status') == '1')
                                    <div class="col-12 col-md-12 p-5">
                                        <h5>Instamojo does not support recurring subscription.</h5>
                                        <h5>Enable Razorpay to use this feature.</h5>
                                    </div>
                                @else
                                    <div class="col-12 col-md-6">
                                        <label for="price">Price</label><span style="color: red">*</span>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">&#8377;</span>
                                            </div>
                                            <input type="text" class="form-control float-number" name="price"
                                                id="price" value="{{ old('price') ?? 0 }}" min="0" />
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="bill_learner_every">Bill learner every</label>
                                        <div class="input-group">
                                            <input type="text" style="width: 125px" class="form-control numeric"
                                                name="bill_learner_every" id="bill_learner_every"
                                                value="{{ old('bill_learner_every') ?? 0 }}" min="0">
                                            {{-- <select class="custom-select form-control" id="inputGroupSelect01" name="calendar">
                                                <!-- <option >Choose...</option> -->
                                                <option value="1" selected>Week</option>
                                                <option value="2">Month</option>
                                                <option value="3">Year</option>
                                            </select> --}}
                                        </div>

                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="inputGroupSelect01">&nbsp;</label>
                                        <div class="input-group">
                                            {{-- <input type="number" class="form-control" name="bill_learner_every" id="bill_learner_every" value="{{ old('bill_learner_every') ?? 0 }}" min="0" oninput="validity.valid||(value='');" step="1"> --}}
                                            <select class="custom-select form-control" id="inputGroupSelect01"
                                                name="calendar">
                                                <!-- <option >Choose...</option> -->
                                                <option value="1" selected>Week</option>
                                                <option value="2">Month</option>
                                                <option value="3">Year</option>
                                            </select>
                                        </div>

                                    </div>
                                @endif
                            </div>
                            <!-- <div class="row plan-modal recurring">
                                <div class="col-12 col-md-6">
                                    <label for="setup_fee">Setup Fee</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">&#8377;</span>
                                        </div>
                                        <input type="number" class="form-control" name="setup_fee" id="setup_fee" value="{{ old('setup_fee') ?? 0 }}" min="0" oninput="validity.valid||(value='');" step="1">
                                    </div>
                                </div>
                            </div> -->


                            <!-- <div class="mt-2 plan-modal recurring">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input is_free_trial_included" id="is_free_trial_included" name="is_free_trial_included" >
                                    <label class="form-check-label" for="is_free_trial_included">Include a free trial</label>
                                </div>

                            </div> -->
                            <div class="mt-3 plan-modal free one-time">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input limit_course" id="limit_course"
                                        name="limit_course">
                                    <label class="form-check-label" for="limit_course">Limit course access
                                        duration</label>
                                </div>

                            </div>

                            <div class="row form-group col-12 free one-time radio_hide mt-4" style="">
                                <div class="col-md-6 d-inline-flex p-2 date_hide">
                                    <div class="form-check fixed-date-picker d-none fixed-date "
                                        style="position: relative;">
                                        <input type="radio" class="form-check-input fixed_date "
                                            id="until_fixed_date" name="fixed_date" value="fixed_date">
                                        <label class="form-check-label" for="until_fixed_date">Until fixed
                                            date</label>
                                        {{-- <div class="div">

                                    </div> --}}
                                    </div>
                                </div>
                                <div class="col-md-6 fixed-date-picker d-none fixeddate fixeddays123"
                                    style="display: none">
                                    {{-- <div class=" "> --}}
                                    <div class="input-group-append fixed_date_add" data-target="#fixed_date"
                                        data-toggle="datetimepicker">
                                        <input type="text" name="fixed_date_add" id="fixed_date"
                                            placeholder="YYYY-MM-DD" class="form-control datetimepicker-input"
                                            data-target="#fixed_date" />
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                {{-- </div> --}}
                            </div>
                            <div class="row form-group col-12 free one-time radio_hide">
                                <div class="col-md-6 d-inline-flex p-2 ">
                                    <div class="form-check fixed-date-picker d-none  fixed-days fixed_date_add">
                                        <input type="radio" class="form-check-input fixed_days" id="fixed_days"
                                            name="fixed_date" value="fixed_days">
                                        <label class="form-check-label" for="fixed_days">Until Specific number of
                                            days</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" fixed-date-picker d-none fixeddays" style="display: none">
                                        <input type="text" name="fixed_days" id="fixed_days" placeholder="days"
                                            class="form-control numeric error" data-target="#fixed_date"
                                            aria-invalid="true">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer ">
                    <button type="button" style="" class="btn btn-secondary btns"
                        data-dismiss="modal">Close</button>
                    <button type="submit" style="" class="btn btn-primary btns" id="submitPrice">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(".fetch_subscriptions").on("click", function() {
        $.ajax({
            url: "{{ route('renewing_subscriptions') }}",
            type: "get",
            success: function(data) {

                // $(".renewing_subscriptions").hide()
                $(".renewing_subscriptions").empty()
                $(".renewing_subscriptions").append(data);
            }
        });
    })
</script>
