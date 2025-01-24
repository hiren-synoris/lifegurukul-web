{{-- @dd($temp->message_payload->template->total_parameters) --}}
<div class="row form-group hide_variable">
    <input type="hidden" class="total_parameters_" value="{{ $temp->total_parameters }}">
    @for ($i = 1; $i <= $temp->total_parameters; $i++)
        <div class="col-md-4">
            <label class="mt-1 font-weight-bold">Var {{ $i }} <span class='text-danger'>*</span></label>
            <select class="form-control manu_list campaigns campaigns_{{ $i }}" name="campaigns[{{ $i }}]" id="campaigns" style="width: 215px">
                <option selected value="" data-id="input_{{ $i }}" data-other="{{ $i }}">Select Template Variable</option>
                <option value="name" data-other="{{ $i }}" data-id="input_{{ $i }}">Name</option>
                <option value="mobile" data-other="{{ $i }}" data-id="input_{{ $i }}"> Mobile</option>
                <option value="email" data-other="{{ $i }}" data-id="input_{{ $i }}"> Email</option>
                <option value="course_name" data-other="{{ $i }}" data-id="input_{{ $i }}"> Course name </option>
                <option value="var_link" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Variable link </option>
                {{-- <option value="media_var" data-other="{{ $i }}" data-id="input_{{ $i }}"> Media Variable </option> --}}
                {{-- <option value="image" data-id="{{ $i }}"> Image Variable</option> --}}
                <option value="amount" data-other="{{ $i }}" data-id="input_{{ $i }}"> Amount </option>
                <option value="date" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Date </option>
                <option value="time" data-id="input_{{ $i }}"  data-other="{{ $i }}">Time </option>
                <option value="pre_validity" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Pre purchase Validity</option>
                <option value="expired_date" data-id="input_{{ $i }}"  data-other="{{ $i }}"> After purchase Validity </option>
                <option value="coupon_code" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Coupon code </option>
                <option value="metting_id" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Meeting id </option>
                <option value="metting_pass" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Meeting Password </option>
                <option value="metting_url" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Meeting url </option>
                <option value="regi_link" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Registration link</option>
                <option value="event_name" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Event name</option>
                <option value="event_date" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Event date </option>
                <option value="event_time" data-id="input_{{ $i }}"  data-other="{{ $i }}"> Event time</option>
                {{-- <option value="coin_balance" data-other="{{ $i }}" data-id="input_{{ $i }}" > Success Coin balance </option> --}}
                <option value="coin_redeem" data-other="{{ $i }}" data-id="input_{{ $i }}"> Success coin redemption </option>
                <option value="coin_earn" data-other="{{ $i }}" data-id="input_{{ $i }}"> Success coin earned </option>
            </select>
            <label class="text-danger var_error_{{ $i }}" style="display: none">Var {{ $i }} is required</label>
        </div>
        <div class="col-md-4">
            <div class="form-group input_{{ $i }}" style="display:none">
                <label class="label_input_{{ $i }}"><span class='text-danger'>*</span> </label>
                <input type="text" name="input_value[{{ $i }}]" class="form-control input_value common_input{{ $i }}" style="margin-top: 3px;">
                <label class="text-danger common_input_error_{{ $i }}" style="display: none">This field is required</label>
            </div>
            <div class="courses_wp_{{ $i }}" style="display:none">
                <label for="platform" class="">Course <span class='text-danger'>*</span></label><br>
                <select class="custom-select course_data select2 course{{ $i }}" name="course_wp[{{ $i }}]" data-course_indexId="{{ $i }}" id="course[{{ $i }}]" style="width:200px;margin-top: 3px;height: 44px;">
                    <option selected value="">Select course</option>
                    @foreach ($courses as $val)
                        <option value="{{ $val->id }}">{{ $val->title }}
                        </option>
                    @endforeach
                </select>
                <label class="text-danger course_error_{{ $i }}" style="display: none">This field is required</label>
            </div>
            <div class="coupons_{{ $i }}" style="display:none">
                <label for="platform">Coupon code <span class='text-danger'>*</span></label><br>
                <select class="custom-select coupons coupons{{ $i }}" name="coupons[{{ $i }}]" id="" style="width:200px;margin-top: 3px;height: 44px;">
                    <option selected value="">Select codes</option>
                    @foreach ($coupons as $val)
                        <option value="{{ $val->code }}">{{ $val->code }}
                        </option>
                    @endforeach
                </select>
                <label class="text-danger coupon_error_{{ $i }}" style="display: none">This field is required</label>
            </div>
            {{-- <div class="media_var_{{ $i }}" style="display:none">
                <div class="form-group" >
                    <label>Media</label>
                    <input type="file" name="media_var[{{ $i }}]" class="form-control media_var" style="margin-top: 3px;" data-index="{{ $i }}">

                </div>
            </div> --}}
        </div>
        <div class="col-md-4">
            <div class="plan_wp_{{ $i }}" style="display:none">
                <label for="platform" style="width: 200px;">Course Plan <span class='text-danger'>*</span></label><br>
                <select class="custom-select course_plan course_plan{{ $i }}" name="course_plan[{{ $i }}]" id="course_plan" style="width:200px;margin-top: 3px;height: 44px;">
                    <option value="">Select Plan</option>
                </select>
                <label class="text-danger plan_error_{{ $i }}" style="display: none">This field is required</label>
            </div>
            <div class="days_wp_{{ $i }}" style="display:none">
                <label for="" style="width: 200px;">Days <span class='text-danger'>*</span></label><br>
                <select class="custom-select days{{ $i }}" name="days" id="" style="width:200px;margin-top: 3px;height: 44px;">
                    <option value="">Select Days</option>
                    @for($days=1;$days<=31;$days++)
                    <option value="{{ $days }}">{{ $days }}</option>
                    @endfor
                </select>
                <label class="text-danger days_error_{{ $i }}" style="display: none">This field is required</label>
            </div>
        </div>
    @endfor
</div>
