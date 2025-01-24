{{-- @php dd($course->plans->count()); @endphp --}}
<style>
    .apply-coupons .modal-body {
        background: #ffff;
        border-radius: 15px;
        border: none;
        background-color: #fff;
        background-clip: padding-box;
        border-radius: 0.3rem;
        outline: 0;
    }

    .apply-coupons .modal-content {

        background-clip: padding-box;

    }

    .wallet-apply-btn label {
        font-weight: 400;
    }

    .add_btn {
        width: 100%;
        float: left;
        text-align: right;
        margin: 0 0 20px;
        padding-top: 20px;
    }

    .final-price {
        margin-top: 5px;
    }

    .apply-heading h5,
    .apply-heading p,
    .apply-heading h1 {
        margin: 0;
    }

    .apply-heading h5 {
        color: #ff0000;
        font-size: 16px;
    }

    .apply-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 2px solid #ccc;
        margin-bottom: 10px;
        padding: 15px;
        box-sizing: border-box;
    }

    .wallet-info-bottom {
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .apply-inner {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        border-bottom: 2px solid #ccc;
    }

    .coin_manage ul li span {
        color: green;
    }

    .apply-bottom h5 {
        font-size: 16px;
        color: #ea3536;
    }

    .apply-bottom p {
        font-size: 16px;
        color: #000;
        margin-bottom: 8px;
    }

    .apply-bottom {
        margin-top: 8px;


    }


    .apply-heading p {
        font-size: 16px;
        color: #67a94a;
        margin-bottom: 8px;
    }

    .apply-heading h1 {
        font-size: 20px;
        text-transform: uppercase;
    }

    .apply-btn {
        text-transform: uppercase;
        font-weight: 600;
        color: #fff;
    }

    .modal-header .btn-close:hover,
    .modal-header .btn-close:focus {
        outline: none;
        box-shadow: 0 0 0;
    }

    /*  */
    .coin_manage ul {
        list-style: none;
        padding: 10px 0px;
        margin: 0 0 10px 0;
        width: 100%;
        float: left;
        text-align: right;
        border-bottom: 1px solid rgba(0, 0, 0, 0.2);
    }


    .wallet_remove {
        font-size: 15px;
        font-weight: 600;
        color: #000;
        margin: 5px 0 0;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: start;
    }

    .coupon_remove {
        font-size: 18px;
        font-weight: 400;
        color: #F2751F;
    }

    .wallet_remove input.wallet {
        margin-left: 5px;
    }

    .card {
        border: none;
        margin-bottom: 0;
        border-radius: 0;
    }
</style>

{{-- @php
use App\Models\Coupon;
    $my_coupens =Coupon::where("")->get();
@endphp --}}

{{-- @if (isset($course) && $course->plans->count() > 1) --}}

<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header pb-0">
            <h3 class="subs-title text-center enroll-title mb-0">Enroll {{ $course->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 16%;" scope="col">Choose Plan</th>
                                    <th scope="col">Plan Name</th>
                                    <th scope="col" class="text-center">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($course->plans as $key => $value)
                                    @php

                                        $valid_till = '';
                                        $date = new DateTime();
                                        $date1 = new DateTime();
                                        $currentDate = now();

                                        if ($value->course_limit == 1) {
                                            if (isset($value->is_fixed_date) && $value->access_value != null) {
                                                if ($value->is_fixed_date == 2) {
                                                    // add number of days

                                                    $valid_till = $value->access_value;
                                                }
                                                if ($value->is_fixed_date == 1) {
                                                    // add expiredate
                                                    // $date->modify('+'.$value->access_value.' days');
                                                    // $expiredAt = $date->format('Y-m-d');

                                                    // $your_date = strtotime($value->access_value);
                                                    // $valid_till = $your_date - time();
                                                    // $valid_till = round($valid_till / (60 * 60 * 24));
                                                    $valid_till = dateFormate($value->access_value);
                                                }
                                                $valid_till = $valid_till . ' Days';
                                            }
                                        } else {
                                            $valid_till = 'Lifetime';
                                        }
                                    @endphp
                                    @if ($valid_till != '')
                                        <tr>
                                            <td class="text-center" style="vertical-align: middle;">
                                                {{-- <input type="radio" class="form-controls plan_choose"
                                                    name="plan_choose"
                                                    data-get_plan_id="{{ Crypt::encrypt($value->id) }}"
                                                    data-get_status="{{ Crypt::encrypt(1) }}"
                                                    value="{{ $value->final_payable_price }}"
                                                    data-get_price={{ Crypt::encrypt($value->final_payable_price) }}
                                                    {{ $loop->first ? 'checked' : '' }}> --}}

                                                <div>
                                                    <label class="custom_check">
                                                        <input type="radio" id="checked_for_{{ $value->id }}"
                                                            data-get_plan_id="{{ Crypt::encrypt($value->id) }}"
                                                            data-get_status="{{ Crypt::encrypt(1) }}" name="plan_choose"
                                                            value="{{ $value->final_payable_price }}"
                                                            data-get_price={{ Crypt::encrypt($value->final_payable_price) }}
                                                            class="price price_flt plan_choose"
                                                            {{ $loop->first ? 'checked' : '' }}>
                                                        <span class="radiocheckmark"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            @if ($value->plan_type == \App\Models\CoursePlan::PLAN_FREE)
                                                <td><label
                                                        for="checked_for_{{ $value->id }}">{{ $value->plan_name ?? '' }}
                                                        (FREE)
                                                        <br />
                                                        <span><b>{{ $valid_till != '' ? 'Validity: ' : '' }}
                                                            </b>{{ $valid_till }}</span></label>
                                                </td>
                                                {{-- <td class="text-center">
                                                    <a class="btn btn-primary" href="{{route('checkout.free.plan',['planId' => Crypt::encrypt($value->id)])}}" >ADD {{ $value->plan_type!=0 ? '₹'.$value->final_payable_price:''}}</a>
                                                </td> --}}
                                                <td class="text-center">
                                                    <label>Free</label>
                                                </td>
                                            @elseif ($value->plan_type == \App\Models\CoursePlan::PLAN_RECURRING)
                                                @if (!empty(config('settings.razorpay_status')))
                                                    <td><label
                                                            for="checked_for_{{ $value->id }}">{{ $value->plan_name ?? '' }}
                                                            <br />
                                                            @php
                                                                $bill_learner_every =
                                                                    $value->bill_learner_every != 0
                                                                        ? $value->bill_learner_every
                                                                        : '';
                                                            @endphp
                                                            <span style="valid">
                                                                {{ 'Every ' . $bill_learner_every }}
                                                                {{ getBillFrequency($value->calendar) }} Subscriptions
                                                                <b>₹{{ $value->final_payable_price }}</b>
                                                            </span></label>
                                                    </td>
                                                    <td class="text-center">
                                                        {{-- <a class="btn btn-primary"
                                                            href="{{ route('checkout.index', ['planId' => Crypt::encrypt($value->id)]) }}">Subscribe</a> --}}
                                                    </td>
                                                @endif
                                            @else
                                                {{-- <td>{{ $value->plan_name ?? '' }}
                                                    <br/>
                                                    <span>
                                                        <b>{{ $valid_till !='' ? 'Validity: ' : '' }}</b>{{ $valid_till }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-primary" href="{{route('checkout.index',['planId' => Crypt::encrypt($value->id)])}}">Buy for ₹{{$value->final_payable_price}}</a>
                                                </td> --}}
                                                <td><label
                                                        for="checked_for_{{ $value->id }}">{{ $value->plan_name ?? '' }}
                                                        <br />
                                                        <span>
                                                            <b>{{ $valid_till != '' ? 'Validity: ' : '' }}</b>{{ $valid_till }}
                                                        </span>
                                                </td></label>
                                                <td class="text-center">
                                                    <label>{{ $value->final_payable_price }}</label>
                                                </td>
                                            @endif


                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>

                        <div class="wallet-info-bottom">
                            <div class="user_discount_options">
                                <div class="wallet-apply-close">
                                    @if ($coupons->count() > 0)
                                        <div class="coupon_remove">
                                            <a href="javascript:void(0)" data-bs-backdrop="static"
                                                data-bs-keyboard="false" for="coupon"
                                                class="open_coupon_pop btn btn-primary">
                                                Apply coupon
                                            </a>
                                            <span class="coupon_msg" style="font-size: 16px;"> </span> <a
                                                href="javascript:void(0)" class="cancel_coupon"
                                                style="display:none;color: #F2751F;text-transform: capitalize;font-size: 16px;text-decoration: underline;"
                                                title="Cancel coupon"> Remove </a>
                                        </div>
                                    @endif
                                </div>

                                @if ($total > 0)
                                    <div class="wallet_remove">
                                        <div class="wallet-apply-btn">
                                            <label for="wallet" class="updated_coin" data-get_discount=""
                                                data-applied_coin="0">
                                                Wallet (Available Success Coins: {{ $total }})
                                                (₹{{ config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null }}/-
                                                per coin )
                                            </label>
                                            {{-- <input type="checkbox" data-get_coin="{{ $total }}"
                                            data-get_total="{{ Crypt::encrypt($total) }}" name="wallet" class="wallet"
                                            id="wallet"
                                            data-per_price={{ config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null }}><br> --}}

                                            <div
                                                style="display: inline-block;
                                            margin: 0 0 0 5px;">
                                                <label class="custom_check wallet_hide">
                                                    <input type="checkbox" data-get_coin="{{ $total }}"
                                                        data-get_total="{{ Crypt::encrypt($total) }}"
                                                        data-per_price={{ config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null }}
                                                        id="wallet" name="wallet" class="wallet">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="coin_manage">
                                <ul>
                                    <li></li>
                                    <li></li>
                                    <li></li>
                                    <li class="fw-bold final-price"></li>
                                </ul>
                            </div>


                        </div>
                        <div class="add_btn mb-0"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal apply-coupons" id="my_coupon" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Available Coupons</h4>

                    <button type="button" class="btn-close cool" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    @foreach ($coupons as $coupons_val)
                        <div class="apply-inner" style="align-items: center;">
                            <div class="apply-heading">
                                <h1>{{ $coupons_val->code }}</h1>
                                <p>Save up to
                                    {{ $coupons_val->max_amount == null ? '₹' . $coupons_val->value : $coupons_val->value . '%' }}.
                                    {{ $coupons_val->max_amount != null ? 'Max ₹' . $coupons_val->max_amount : '' }}
                                </p>
                                <!-- <h5>Expire On {{ \Carbon\Carbon::parse($coupons_val->expiry_date)->format('d/m/y') }}
                                </h5> -->
                            </div>
                            <div class="apply-heading">
                                <a href="javascript:void(0)" data-coupon_name="{{ $coupons_val->code }}"
                                    data-type={{ $coupons_val->type }} data-get_percentage="{{ $coupons_val->value }}"
                                    data-coupon_id="{{ Crypt::encrypt($coupons_val->id) }}"
                                    data-max_amount="{{ $coupons_val->max_amount }}"
                                    class="apply-btn apply_coupon btn btn-primary">Apply</a>
                            </div>
                        </div>
                        {{-- <div class="apply-bottom">
                        <p>Use code <b>{{ $coupons_val->code }}</b> & get ₹{{  $coupons_val->max_amount==null ?$coupons_val->value :$coupons_val->max_amount }}</p>

                    </div> --}}
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    {{-- @endif --}}
    <script>
        $(document).ready(function() {

            var coupon_count = "{{ $coupons->count() }}"
            var per_coin_price =
                "{{ config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null }}"
            //   if(coupon_count == 0) {
            localStorage.clear()
            //   }

            $(document).on("click", ".open_coupon_pop", function() {

                $("#my_coupon").modal("show")
                var checkedPlan = $("input[name='plan_choose']:checked").val();
                $(".place_price").text(checkedPlan)

            })

            $(document).on("click", ".cancel_coupon", function() {
                $(this).hide()
                $(".coupon_msg").hide()
                $(".open_coupon_pop").show()
                $(".updated_coin").show()
                $(".wallet_hide").show()
                var get_total = $(".wallet").data("get_total")
                var get_coin = $(".wallet").data("get_coin")

                var price = $("input[name='plan_choose']:checked").val();;
                var planId = $("input[name='plan_choose']:checked").data("get_plan_id")
                $(".updated_coin").attr("data-get_discount", "")
                var wallet_value = $("input[name='wallet']:checked").val();
                var applied_coin = parseInt($(".updated_coin").data("applied_coin"))
                // var applied_coin
                var available_coin
                var final_price = "";
                var minus_coin
                var used_coins
                if (wallet_value) {
                    if (price > (get_coin * per_coin_price)) {

                        minus_coin = price - applied_coin
                        // alert(applied_coin)
                        // available_coin = (applied_coin/per_coin_price)
                        available_coin = 0
                        used_coins = parseFloat(applied_coin).toFixed(2)

                        $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + used_coins +
                            "</span>")

                        final_price = minus_coin
                    } else {
                        minus_coin = applied_coin - price
                        available_coin = Math.floor(minus_coin / per_coin_price)
                        used_coins = price
                        final_price = 0
                        $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + used_coins +
                            "</span>")
                    }
                } else {
                    applied_coin = 0
                    final_price = price
                    available_coin = get_coin
                }

                var coupon_id = localStorage.getItem("coupon_id");
                //  updateButton(0, 0, "");
                //  $(".updated_coin").text(" Wallet (Available Success Coins: " + get_coin + ') (' +
                //                 2 + ' ₹  per coin)')
                // $(".coin_manage li:nth-child(3)").text("Used Coins: ₹" + '0.00').hide()
                $(".coin_manage li:nth-child(2)").html("Coupon Discount: <span>₹" + '0.00</span>').hide()

                // // $(".wallet").prop("checked", false);
                // $(".updated_coin").attr("data-get_discount","")

                // if(price > applied_coin) {
                //     final_price = price - applied_coin
                // } else {
                //     final_price =  applied_coin - price
                // }
                // alert(available_coin)
                $(".updated_coin").text(" Wallet (Available Success Coins: ₹" + available_coin +
                    ' per coin)')


                $(".coin_manage li:nth-child(1)").text("Price: ₹" + parseFloat(price).toFixed(2));
                $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + parseFloat(final_price).toFixed(
                    2));
                if (price == 0.00) {

                    var checkoutRoute_free_plan_coupon =
                        "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(':planId',
                        planId);
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                        ':updated_new_coin', 0);
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                        ':coupon_id', 0);

                    buttonHtml = "<a class='btn btn-primary' href='" + checkoutRoute_free_plan_coupon +
                        "'>ADD</a>";
                    $(".add_btn").empty().append(buttonHtml);


                } else {

                    var checkout_pay_Route =
                        "{{ route('checkout.index', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                    checkout_pay_Route = checkout_pay_Route.replace(':planId', planId);
                    checkout_pay_Route = checkout_pay_Route.replace(':updated_new_coin',
                        get_total);
                    checkout_pay_Route = checkout_pay_Route.replace(':coupon_id', coupon_id);


                    buttonHtml = "<a class='btn btn-primary' href='" + checkout_pay_Route +
                        "'>Buy Now ₹" + parseInt(final_price) + '.' + '00'
                        "</a>";
                    $(".add_btn").empty().append(buttonHtml);
                }



            })



            $(".apply_coupon").click(function() {
                $(".cancel_coupon").show()
                $(".coupon_msg").show()
                $(".open_coupon_pop").hide()
                $("#my_coupon").modal("hide")
                var coupon_name = $(this).data("coupon_name").toUpperCase()
                $(".coupon_msg").html("Coupon Applied <b>" + coupon_name + "</b>").css("color", "green")

                var plan_price = $("input[name='plan_choose']:checked").val();
                var discount = "";
                var get_total = $(".wallet").data("get_total")
                var wallet_value = $("input[name='wallet']:checked").val();

                if (wallet_value != "on") {
                    get_total = 0
                }
                var planId = $(".plan_choose:checked").data('get_plan_id');
                var per_price = $(".wallet").data("per_price")
                var max_amount = $(this).data("max_amount")
                var buttonHtml = "";
                var percentage = $(this).data("get_percentage")
                var coupon_id = $(this).data("coupon_id")
                localStorage.setItem("coupon_id", coupon_id);

                var type = $(this).data("type")
                $(".updated_coin").show();
                $(".wallet_hide").show();
                if (type == 2) {

                    discount = percentage
                    if (discount >= plan_price) {
                        discount = parseInt(plan_price)
                        $(".updated_coin").hide();
                        $(".wallet_hide").hide();
                        $(".coin_manage li:nth-child(3)").hide()
                        $(".wallet").prop("checked", false);
                        get_total = 0
                    }

                } else {

                    // discount = Math.ceil(plan_price * percentage / 100)
                    discount = parseInt(plan_price * percentage / 100)

                    if (discount > max_amount) {
                        discount = parseInt(max_amount)
                    }

                    if (discount >= plan_price) {

                        discount = parseInt(plan_price)

                        $(".updated_coin").hide();
                        $(".wallet_hide").hide();
                        $(".coin_manage li:nth-child(3)").hide()
                        $(".wallet").prop("checked", false);
                        get_total = 0
                    }

                }
                // $(".updated_coin").show();
                //         $(".wallet_hide").show();

                $(".coin_manage li:nth-child(2)").html("Coupon Discount: <span>₹" + parseInt(discount) +
                    '.00</span>').show();

                var price = plan_price - discount




                get_coin = $(".wallet").data("get_coin")
                $(".updated_coin").attr("data-get_discount", discount)
                var applied_coin = parseInt($(".updated_coin").attr("data-applied_coin"))


                if ($.trim(applied_coin) !== '' && applied_coin !== '0') {

                    if (applied_coin > price) {

                        if (price > 0) {
                            var updated_coin = Math.floor(((applied_coin) - price) / per_coin_price)

                            $(".updated_coin").text(" Wallet (Available Success Coins: " + updated_coin +
                                ') (₹' +
                                per_coin_price + ' per coin)')

                            $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + price + '.' +
                                '00</span>').show();
                            $(".coin_manage li:nth-child(4)").text("Final Price: ₹ 00.00");

                            var checkoutRoute_free_plan_coupon =
                                "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':planId', planId);
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':updated_new_coin', get_total);
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':coupon_id', coupon_id);

                            buttonHtml = "<a class='btn btn-primary' href='" +
                                checkoutRoute_free_plan_coupon + "'>ADD</a>";
                            $(".add_btn").empty().append(buttonHtml);

                        } else {

                            var updated_coin = Math.floor(((applied_coin)) / per_coin_price)

                            $(".updated_coin").text(" Wallet (Available Success Coins: " + updated_coin +
                                ') (₹' +
                                per_coin_price + ' per coin)')

                            $(".coin_manage li:nth-child(4)").text("Final Price: ₹00.00");

                            var checkoutRoute_free_plan_coupon =
                                "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':planId', planId);
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':updated_new_coin', get_total);
                            checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                                ':coupon_id', coupon_id);
                            $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + price + '.' + '00');

                            buttonHtml = "<a class='btn btn-primary' href='" +
                                checkoutRoute_free_plan_coupon + "'>ADD</a>";

                            $(".add_btn").empty().append(buttonHtml);
                        }


                    } else {

                        var final_coins = isNaN(applied_coin) ? price : price - applied_coin;


                        $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + final_coins + '.' + '00');

                        var checkout_coupon_Route =
                            "{{ route('checkout.index', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                        checkout_coupon_Route = checkout_coupon_Route.replace(':planId', planId);
                        checkout_coupon_Route = checkout_coupon_Route.replace(':updated_new_coin',
                            get_total);
                        checkout_coupon_Route = checkout_coupon_Route.replace(':coupon_id', coupon_id);


                        buttonHtml = "<a class='btn btn-primary' href='" + checkout_coupon_Route +
                            "'>Buy Now ₹" + parseInt(final_coins) + '.' + '00'
                            "</a>";

                        $(".add_btn").empty().append(buttonHtml);
                    }

                } else {



                    if (price > 0) {

                        var checkout_coupon_Route =
                            "{{ route('checkout.index', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                        checkout_coupon_Route = checkout_coupon_Route.replace(':planId', planId);
                        checkout_coupon_Route = checkout_coupon_Route.replace(':updated_new_coin',
                            get_total);
                        checkout_coupon_Route = checkout_coupon_Route.replace(':coupon_id', coupon_id);

                        $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + price + '.' + '00');

                        buttonHtml = "<a class='btn btn-primary' href='" + checkout_coupon_Route +
                            "'>Buy Now ₹" + parseInt(price) + '.' + '00'
                            "</a>";
                    } else {

                        var checkoutRoute_free_plan_coupon =
                            "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                        checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(':planId',
                            planId);
                        checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                            ':updated_new_coin', get_total);
                        checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                            ':coupon_id', coupon_id);

                        $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + price + '.' + '00');

                        buttonHtml = "<a class='btn btn-primary' href='" + checkoutRoute_free_plan_coupon +
                            "'>ADD</a>";
                    }

                    $(".add_btn").empty().append(buttonHtml);

                }

            })


            updateButton(0, 0, "");

            $(document).on("click", ".plan_choose", function() {
                $(".cancel_coupon").hide();
                $(".coupon_msg").hide();
                $(".open_coupon_pop").show();
                var price = $(this).val();
                var planId = $(this).data("get_plan_id")
                var get_total = 0
                var get_coin = $(".wallet").data("get_coin")
                var coupon_id = localStorage.getItem("coupon_id");
                updateButton(0, 0, "");
                $(".updated_coin").text(" Wallet (Available Success Coins: " + get_coin + ') (₹' +
                    per_coin_price + ' per coin)')
                $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + '0.00</span>').hide()
                $(".coin_manage li:nth-child(2)").html("Coupon Discount: <span>₹" + '0.00</span>').hide()
                $(".updated_coin").attr("data-applied_coin", "")
                $(".wallet").prop("checked", false);
                $(".updated_coin").attr("data-get_discount", "")
                $(".coin_manage li:nth-child(1)").text("Price: ₹" + price);
                $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + price);
                if (price == 0.00) {

                    var checkoutRoute_free_plan_coupon =
                        "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(':planId',
                        planId);
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                        ':updated_new_coin', 0);
                    checkoutRoute_free_plan_coupon = checkoutRoute_free_plan_coupon.replace(
                        ':coupon_id', 0);

                    buttonHtml = "<a class='btn btn-primary' href='" + checkoutRoute_free_plan_coupon +
                        "'>ADD</a>";
                    $(".add_btn").empty().append(buttonHtml);


                } else {

                    var checkout_pay_Route =
                        "{{ route('checkout.index', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                    checkout_pay_Route = checkout_pay_Route.replace(':planId', planId);
                    checkout_pay_Route = checkout_pay_Route.replace(':updated_new_coin',
                        get_total);
                    checkout_pay_Route = checkout_pay_Route.replace(':coupon_id', coupon_id);


                    buttonHtml = "<a class='btn btn-primary' href='" + checkout_pay_Route +
                        "'>Buy Now ₹" + price +
                        "</a>";
                    $(".add_btn").empty().append(buttonHtml);
                }



            });


            $(document).on("click", ".wallet", function() {
                var final_totalplus = ""
                //localStorage.clear();

                var wallet_value = $("input[name='wallet']:checked").val();
                var checkedValue = $("input[name='plan_choose']:checked").val();
                var per_price = $(this).data("per_price")


                var get_price = $(this).data("get_price")

                var get_coin = $(this).data("get_coin") * per_price
                var select_plan = $("input[name='wallet']").is(":checked")
                // alert(select_plan)
                if (select_plan == true) {
                    $(".updated_coin").attr("data-applied_coin", get_coin)
                } else {

                    $(".updated_coin").attr("data-applied_coin", 0)
                }


                if (wallet_value == "on") {

                    $(".coin_manage li:nth-child(3)").show()

                    var get_discount = $(".updated_coin").attr("data-get_discount")


                    if(parseInt(checkedValue) > get_discount) {
                        checkedValue = checkedValue - get_discount

                    } else {

                        checkedValue =  get_discount - checkedValue
                    }


                    if (checkedValue > get_coin) {
                        var totalplus = 00

                        $(".updated_coin").text(" Wallet (Available Success Coins: " + totalplus + ') (₹' +
                            per_price +
                            ' per coin)')


                        updateButton($(this).data("get_coin"), $(this).data("get_total"), per_price);


                    } else {

                        var totalplus = ($(this).data("get_coin") * per_price) - checkedValue
                        final_totalplus = Math.floor(totalplus / per_price)




                        updateButton($(this).data("get_coin"), $(this).data("get_total"), per_price);
                        $(".updated_coin").text(" Wallet (Available Success Coins: " + final_totalplus +
                            ') (₹' +
                            per_price + ' per coin)')
                        // updateButton(final_totalplus, $(this).data("get_total"));
                    }
                } else {
                    // $(".updated_coin").attr("data-get_discount","")

                    var totalplus = $(this).data("get_coin")
                    $(".updated_coin").text(" Wallet (Available Success Coins: " + totalplus + ') (₹' +
                        per_price +
                        ' per coin)')
                    updateButton(0, 0, "");
                    $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + '0.00</span>').hide();
                }
            });


            function updateButton(coins, get_total, per_price) {


                var get_discount = $(".updated_coin").attr("data-get_discount")

                var checkedValue = $("input[name='plan_choose']:checked").val();

                var coupon_id = localStorage.getItem("coupon_id");
                var buttonHtml;

                if (checkedValue == 0.00) {

                    buttonHtml =
                        "<a class='btn btn-primary' href='{{ route('checkout.free.plan', ['planId' => Crypt::encrypt($value->id), 'updated_new_coin' => Crypt::encrypt(0), 'coupon_id' => 0]) }}' >ADD</a>";
                    $(".coin_manage").hide();
                    $(".wallet_remove").show();
                    $(".wallet").prop("checked", false);
                    $(".wallet").prop("checked", false);
                    $(".updated_coin").hide();
                    $(".wallet_hide").hide();
                    $(".coupon_remove").hide();


                } else {


                    $(".coupon_remove").show();
                    $(".coin_manage").show();
                    $(".wallet_remove").show();
                    $(".updated_coin").show();
                    $(".wallet_hide").show();

                    var planId = $(".plan_choose:checked").data('get_plan_id');
                    var user_status = $(".plan_choose:checked").data('get_status');

                    if ((parseFloat(coins) * $(".wallet").data("per_price")) > parseFloat(checkedValue)) {

                        checkedValue = parseFloat(checkedValue) + '.00'
                        coins = parseFloat(checkedValue) - get_discount
                        price = 0.00

                        var applied_coin = $(".wallet").attr("data-get_coin")

                        if ($.trim(get_discount) !== '' && get_discount !== '0') {

                            var updated_coin = Math.floor(((applied_coin * per_coin_price) - coins) /
                                per_coin_price)
                            $(".updated_coin").text(" Wallet (Available Success Coins: " + updated_coin + ') (₹' +
                                per_coin_price + ' per coin)')
                        }

                    } else {


                        if ($.trim(get_discount) !== '' && get_discount !== '0') {

                            var per_coin_price = $(".wallet").data("per_price")

                            var final_coin_price = parseFloat(coins) * parseFloat(per_coin_price)

                            var final_discount = parseFloat(checkedValue) - get_discount
                            if (parseFloat(final_discount) > (parseFloat(coins) * $(".wallet").data("per_price"))) {
                                var final_totalplus = parseFloat(final_discount) - parseInt(final_coin_price)
                                var price = final_totalplus;
                                coins = coins * $(".wallet").data("per_price")
                                coins = parseInt(coins)
                            } else {

                                var final_totalplus = (parseFloat(coins) * $(".wallet").data("per_price")) -
                                    parseFloat(final_discount)
                                var price = '00.00';
                                // alert(final_discount)
                                coins = final_discount
                            }


                            // alert($(".wallet").data("per_price"));


                        } else {

                            if (coins != 0) {

                                var per_coin_price = $(".wallet").data("per_price")

                                var final_coin_price = parseFloat(coins) * parseFloat(per_coin_price)

                                var final_totalplus = parseFloat(checkedValue) - parseInt(final_coin_price)

                                var price = final_totalplus;

                                coins = coins * $(".wallet").data("per_price")
                                coins = parseInt(coins)

                            } else {
                                var price = parseFloat(checkedValue)

                            }
                        }

                    }


                    var checkoutRoute =
                        "{{ route('checkout.index', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                    checkoutRoute = checkoutRoute.replace(':planId', planId);
                    checkoutRoute = checkoutRoute.replace(':updated_new_coin', get_total);
                    checkoutRoute = checkoutRoute.replace(':coupon_id', coupon_id);

                    var wallet_check = $("input[name='wallet']:checked").val();
                    if (wallet_check == "on") {
                        checkoutRoute += "?user_coin_status=" + user_status + "";
                    }

                    if (price > 0) {

                        buttonHtml = "<a class='btn btn-primary' href='" + checkoutRoute + "'>Buy Now ₹" + parseInt(price) +'.00'
                            "</a>";
                    } else {

                        var checkoutRoute_free_plan =
                            "{{ route('checkout.free.plan', ['planId' => ':planId', 'updated_new_coin' => ':updated_new_coin', 'coupon_id' => ':coupon_id']) }}";
                        checkoutRoute_free_plan = checkoutRoute_free_plan.replace(':planId', planId);
                        checkoutRoute_free_plan = checkoutRoute_free_plan.replace(':updated_new_coin', get_total);
                        checkoutRoute_free_plan = checkoutRoute_free_plan.replace(':coupon_id', coupon_id);

                        buttonHtml = "<a class='btn btn-primary' href='" + checkoutRoute_free_plan + "'>ADD</a>";
                    }

                    $(".coin_manage li:nth-child(1)").text("Price: ₹" + checkedValue);
                    if (coins != 0) {

                        $(".coin_manage li:nth-child(3)").html("Used Coins: <span>₹" + coins + '.' + '00</span>')
                            .show();
                    }
                    // else {

                    //     $(".coin_manage li:nth-child(3)").text("Available Success Coins: ₹" + '0.00');
                    // }

                    $(".coin_manage li:nth-child(4)").text("Final Price: ₹" + parseInt(price)+'.00');
                }

                $(".add_btn").empty().append(buttonHtml);
            }
        });
    </script>
