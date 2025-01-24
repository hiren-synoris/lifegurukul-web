@php
    // use App\Models\Learner;
@endphp
<!DOCTYPE html>
<html lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Invoice</title>
    <style>
        .ph-5 {
            padding-left: 5px;
            padding-right: 5px;
        }

        .ph-3 {
            padding-left: 3px;
            padding-right: 3px;
        }

        .bold {
            font-weight: 800;
        }

        .h-100 {
            height: 10%;
        }

        .nowrap {
            white-space: nowrap
        }

        .v-top {
            vertical-align: top;
        }

        table.data-table {
            width: 100%;
            border: 1px solid #000;
            font-size: 14px;
        }

        table.data-table tr th,
        table.data-table tr td {
            border: 1px solid #000;
            line-height: 1.3;
        }

        table.data-table tr th.f-12,
        table.data-table tr td.f-12 {
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .w-5 {
            width: 5%;
        }

        .w-10 {
            width: 10%;
        }

        .w-15 {
            width: 15%;
        }

        .w-20 {
            width: 20%;
        }

        .w-25 {
            width: 25%;
        }

        .w-30 {
            width: 30%;
        }

        .w-35 {
            width: 35%;
        }

        .w-40 {
            width: 40%;
        }

        .w-45 {
            width: 45%;
        }

        .w-50 {
            width: 50%;
        }

        .w-55 {
            width: 55%;
        }

        .w-60 {
            width: 60%;
        }

        .w-65 {
            width: 65%;
        }

        .w-70 {
            width: 70%;
        }

        .w-75 {
            width: 75%;
        }

        .w-80 {
            width: 80%;
        }

        .w-85 {
            width: 85%;
        }

        .w-90 {
            width: 90%;
        }

        .w-95 {
            width: 95%;
        }

        .w-100 {
            width: 100%;
        }

        table.data-table.border-none,
        table.data-table.border-none tr th,
        table.data-table.border-none tr td {
            border: none;
        }

        table.data-table tr td.border-bottom-none {
            border-top: none;
            border-bottom: none;
        }

        table.data-table tr td.border-left-none {
            border-left: none;
            border-right: none;
        }

        .v-top {
            vertical-align: top;
        }

        .v-middle {
            vertical-align: middle;
        }

        .v-bottom {
            vertical-align: bottom;
        }

        .text {
            margin: 5px 0;
            font-size: 12px;
            text-align: center;
        }

        .h-50 {
            height: 10%;
        }
    </style>
</head>

@if (isset($invoiceData))

    <body data-aos-easing="ease" data-aos-duration="1200" data-aos-delay="0"
        style="position: relative; min-height: 100%; top: 0px;">
        <div id="document">
            <div class="page-content">
                <div class="page-wrapper">
                    <h1 class="title" align="center"
                        style="display: flex;
                            padding: 10px 5px;
                            width: 100%;
                            margin: 0px 0 0 0;
                            font-size: 18px;
                            line-height: 1;
                            justify-content: center;
                            text-transform: uppercase;
                            color: #000;">
                        Tax Invoice</h1>


                    <table width="100%" align="center" cellpadding="0" cellspacing="0" class="data-table"
                        style="border-bottom: 0px;">
                        <tr>
                            <td class="ph-5 pv-3" colspan="2" style="text-align: left; width: 60%;"><b>TIMELESS
                                    EDUCATION LLP</b></td>
                            <td class="ph-5 pv-3" style="text-align: center; width: 20%;">Invoice No:
                                <br /><b>{{ $invoiceData->id }}</b>
                            </td>
                            <td class="ph-5 pv-3" style="text-align: center; width: 20%;">Dated:
                                <br /><b>{{ date('d-m-Y', strtotime($invoiceData->created_at)) }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td class="ph-5 pv-3" style="text-align: left; font-size: 14px;width: 60%;">503,SKY ENCLAVE,
                                B/H KUBER RESIDENCY, NR SCIENCE CITY, BHADAJ AHMEDABAD
                                </br>
                                GSTIN/UIN: 24AAVFT2001K1Z4
                                </br>
                                State Name: Gujarat, Code: 24
                                </br>
                                CIN: ACC-9713
                            </td>
                            <td class="ph-5 pv-3" style="text-align: center;" colspan="3">
                                <span class="logo">
                                    <!-- <img style="width:auto" src="https://staging.lifegurukul.app/storage/admin/settings/aCWUDGFVU336Snhi6bEaYMFWtwh6GcTxDDbi5DbP.png"
                                        alt="lifegurukul"> -->
                                    <img src="{{ !empty(config('settings.mail_logo')) ? storage_path('app/public/' . config('settings.mail_logo')) : logo_default() }}"
                                        alt="logo">

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="ph-5 pv-3 text-left f-12" style="border-bottom: 0;border-top:0;" colspan="4">
                                Buyer (Bill to) : </br>
                                <?php
                                echo '<b>' . strtoupper($invoiceData->learner->name) . '</b></br>';
                                echo '<b>' ."+".@$invoiceData->countryName->phonecode." ".@$invoiceData->learner->mobile . '</b></br>';
                                echo '<b>' . $invoiceData->learner->email . '</b>';
                                echo '</br>';
                                $address = '';
                                if (!empty($invoiceData->learner->city->name)) {
                                    $address .= $invoiceData->learner->city->name . ', ';
                                }
                                if (!empty($invoiceData->learner->state->name)) {
                                    $address .= $invoiceData->learner->state->name . ', ';
                                }
                                if (!empty($invoiceData->learner->country->name)) {
                                    $address .= $invoiceData->learner->country->name;
                                }
                                echo $address;
                                ?>

                            </td>
                        </tr>

                    </table>

                    <table width="100%" class="data-table" style="border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th class="ph-5 pv-3 f-12 text-center nowrap">Sr. No</th>
                                <th class="ph-5 pv-3 f-12 text-center" colspan="2">Particulars</th>
                                <th class="ph-5 pv-3 f-12 text-right">HSN/SAC</th>
                                <th class="ph-5 pv-3 f-12 text-right">Amount</th>
                            </tr>
                        </thead>

                        @php
                            // $price = 'FREE';
                            // if (isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan)) {
                            // if ($invoiceData->coursePlan->plan_type == 0) {
                            // // free
                            // $price = 'FREE';
                            // } else {
                            // // $price = $invoiceData->coursePlan->final_payable_price;
                            // $price = isset($invoiceData->price) ? $invoiceData->price: $invoiceData->price;
                            // }
                            // }

                            // $redeem_coin = 0;
                            // $redeem = App\Models\UserCoin::where("learner_id",$invoiceData->learner_id)->where("course_id",$invoiceData->course_id)->where("type",2)->orderBy('id', 'DESC')->first();
                            // if($redeem) {
                            // $redeem_coin = $redeem->coins;
                            // }
                        @endphp

                        @php

                            $igst = config()->has('settings.igst') ? config('settings.igst') : null;
                            $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
                            $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;

                            // $learner = Learner::where('id', $invoiceData->learner_id)
                            //     ->with('state')
                            //     ->first();
                            // @dd(gstCal($invoiceData, $sgst));
                            $sgst_price = 0.0;
                            $cgst_price = 0.0;
                            $igst_price = 0.0;
                            if (auth()->guard('learner')->check() == false  && request()->user_id == "null") {
                                $sgst_price = gstCal($invoiceData, $sgst)[1];
                                $cgst_price = gstCal($invoiceData, $cgst)[1];
                            } else {
                                if ($invoiceData->country_id == 1) {
                                    if ($invoiceData->state_id == 12) {
                                        $sgst_price = gstCal($invoiceData, $sgst)[1];
                                        $cgst_price = gstCal($invoiceData, $cgst)[1];
                                        //  dd($invoiceData->after_deduction_price."ok");
                                    } else {
                                        $igst_price = gstCal($invoiceData, $igst)[1];
                                    }
                                }
                            }
                            $srcntr = 1;
                        @endphp


                        <tbody>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center bold w-5">{{ $srcntr }}</td>
                                <td class="ph-5 pv-5 border-bottom-none bold text-left border-left-none w-5"
                                    style="border: 0px solid #000;"></td>
                                <td class="ph-5 pv-5 border-bottom-none bold text-left border-left-none w-50">
                                    {{ isset($invoiceData->course->title) && !empty($invoiceData->course->title) ? $invoiceData->course->title : '' }}
                                </td>
                                <td class="ph-5 pv-5 border-bottom-none bold text-right w-15">999293</td>
                                <td class="ph-5 pv-5 border-bottom-none bold text-right w-15">
                                    {{-- {{ gstCal($invoiceData)[0] . '.00' }} --}}
                                    {{ $invoiceData->price }}
                                </td>
                            </tr>
                            @if ($invoiceData->after_coupon_applied_deduction_price > 0)
                                @php
                                    $srcntr++;
                                @endphp
                                <tr>
                                    <td class="ph-5 pv-5 border-bottom-none text-center bold text-right"></td>
                                    {{-- <td class="ph-5 pv-5 border-bottom-none border-left-none text-right"><i>Less</i></td> --}}
                                    <td class="ph-5 pv-5 border-bottom-none border-none border-left-none text-right bold"><i></i></td>
                                    <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">Less Coupon
                                        Discount</td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right"></td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                         {{ $invoiceData->after_coupon_applied_deduction_price }}.00</td>
                                </tr>
                            @endif
                            @if (intval($invoiceData->user_coin) > 0)
                                @php
                                    $srcntr++;
                                @endphp
                                <tr>
                                    <td class="ph-5 pv-5 border-bottom-none text-center bold text-right"></td>
                                    {{-- <td class="ph-5 pv-5 border-bottom-none border-left-none text-right"><i>Less</i></td> --}}
                                    <td class="ph-5 pv-5 border-bottom-none border-none border-left-none text-right bold"><i></i></td>
                                    <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">Less Coins</td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right"></td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                         {{ intval($invoiceData->user_coin * $invoiceData->per_coin_price) }}.00
                                    </td>
                                </tr>
                            @endif
                            @php
                                $srcntr++;
                            @endphp
                            @if ($invoiceData->country_id == 1)
                                @if ($invoiceData->state_id == 12)
                                    <tr>
                                        <td class="ph-5 pv-5 border-bottom-none text-center bold">
                                        </td>
                                        <td class="ph-5 pv-5 border-bottom-none border-left-none text-right"><i></i></td>
                                        <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">
                                            Taxable Amount</td>
                                        <td class="ph-5 pv-5 border-bottom-none text-right"></td>
                                        <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                            {{ gstCal($invoiceData, $sgst)[0] }}
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="ph-5 pv-5 border-bottom-none text-center bold">
                                        </td>
                                        <td class="ph-5 pv-5 border-bottom-none border-left-none text-right"><i></i></td>
                                        <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">
                                            Taxable Amount</td>
                                        <td class="ph-5 pv-5 border-bottom-none text-right"></td>
                                        <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                            {{ gstCal($invoiceData, $sgst)[0] }}
                                        </td>
                                    </tr>
                                @endif
                            @elseif(auth()->guard('learner')->check() == false && request()->user_id == "null")
                                <tr>
                                    <td class="ph-5 pv-5 border-bottom-none text-center bold">
                                    </td>
                                    <td class="ph-5 pv-5 border-bottom-none border-left-none text-right"><i></i></td>
                                    <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">
                                        Taxable Amount</td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right"></td>
                                    <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                        {{ gstCal($invoiceData, $sgst)[0] }}
                                    </td>
                                </tr>
                            @endif


                            @php
                                $srcntr++;
                            @endphp
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center bold"></td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none border-none text-right"><i></i>
                                </td>
                                <td class="border-none" style="border: none !important;">
                                    <table class="w-100 border-none" style="border: none !important;">
                                        @if (auth()->guard('learner')->check() == false && request()->user_id == "null")

                                            <tr class="border-none">
                                                <td style="border: none !important;"
                                                    class="w-30 ph-5 pv-5 border-none border-bottom-none border-left-none text-right bold">

                                                </td>
                                                <td style="border: none !important;"
                                                    class="w-70 border-bottom-none border-none border-left-none text-right bold ">
                                                    CGST ({{ $cgst }}%)</td>
                                            </tr>
                                            <tr class="border-none">

                                                <td colspan="2" style="border: none !important;"
                                                    class="w-70 border-bottom-none border-none border-left-none text-right bold ">
                                                    SGST ({{ $sgst }}%)</td>
                                            </tr>
                                        @else
                                            @if ($invoiceData->country_id == 1)
                                                @if ($cgst_price > 0)

                                                    <tr class="border-none">
                                                        <td style="border: none !important;"
                                                            class="w-30 ph-5 pv-5 border-none border-bottom-none border-left-none text-right bold">

                                                        </td>
                                                        <td style="border: none !important;"
                                                            class="w-70 border-bottom-none border-none border-left-none text-right bold ">
                                                            CGST ({{ $cgst }}%)</td>
                                                    </tr>
                                                    <tr class="border-none">

                                                        <td colspan="2" style="border: none !important;"
                                                            class="w-70 border-bottom-none border-none border-left-none text-right bold ">
                                                            SGST ({{ $sgst }}%)</td>
                                                    </tr>
                                                @elseif($igst_price > 0)
                                                    <tr class="border-none">
                                                        <td style="border: none !important;"
                                                            class="w-30 ph-5 pv-5 border-none border-bottom-none border-left-none text-right bold">

                                                        </td>
                                                        <td style="border: none !important;"
                                                            class="w-70 border-bottom-none border-none border-left-none text-right bold ">
                                                            IGST ({{ $igst }}%)</td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endif
                                    </table>

                                </td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">
                                    <table class="w-100 border-none" style="border: none !important;">
                                        <tr class="border-none">
                                            <td style="border: none !important;"
                                                class="  border-none border-bottom-none border-left-none text-right  bold">

                                            </td>
                                        </tr>
                                        <tr class="border-none">
                                            <td style="border: none !important;"
                                                class="  border-none border-bottom-none border-left-none text-right bold">

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                @if (auth()->guard('learner')->check() == false && request()->user_id == "null")

                                    <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                        <table class="w-100 border-none" style="border: none !important;">
                                            <tr class="border-none">
                                                <td style="border: none !important;"
                                                    class="  border-none border-bottom-none border-left-none text-right bold">
                                                    <span>{{ $cgst_price }}</span>
                                                </td>
                                            </tr>
                                            <tr class="border-none">
                                                <td style="border: none !important;"
                                                    class="  border-none border-bottom-none border-left-none text-right bold">
                                                    <span>{{ $sgst_price }}</span>
                                                </td>
                                            </tr>

                                        </table>
                                    </td>
                                @else
                                    <td class="ph-5 pv-5 border-bottom-none text-right line bold">
                                        <table class="w-100 border-none" style="border: none !important;">

                                            @if ($cgst_price)
                                                <tr class="border-none">
                                                    <td style="border: none !important;"
                                                        class="  border-none border-bottom-none border-left-none text-right bold">
                                                        <span>{{ $cgst_price > 0.0 ? $cgst_price : '' }}</span>
                                                    </td>
                                                </tr>
                                                <tr class="border-none">
                                                    <td style="border: none !important;"
                                                        class="  border-none border-bottom-none border-left-none text-right bold">
                                                        <span>{{ $sgst_price > 0.0 ?  $sgst_price : '' }}</span>
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="border-none">
                                                    <td style="border: none !important;"
                                                        class="  border-none border-bottom-none border-left-none text-right bold">
                                                        <span>{{ $igst_price > 0.0 ?   $igst_price : '' }}</span>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                @endif
                            </tr>

                            {{-- <tr>
                            <td class="ph-5 pv-5 border-bottom-none text-center"></td>
                            <td class="ph-5 pv-5 border-bottom-none border-left-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold ">Output CGST</td>
                            <td class="ph-5 pv-5 border-bottom-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none text-right bold">762.00</td>
                        </tr> --}}
                            {{-- <tr>
                            <td class="ph-5 pv-5 border-bottom-none text-center"></td>
                            <td class="ph-5 pv-5 border-bottom-none border-left-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none border-left-none text-right bold">Output SGST</td>
                            <td class="ph-5 pv-5 border-bottom-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none text-right bold">762.00</td>
                        </tr> --}}
                            {{-- <tr>
                            <td class="ph-5 pv-5 border-bottom-none text-center"></td>
                            <td class="ph-5 pv-5 border-bottom-none border-left-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">Round Off</td>
                            <td class="ph-5 pv-5 border-bottom-none text-left"></td>
                            <td class="ph-5 pv-5 border-bottom-none text-right bold">0.27</td>
                        </tr> --}}
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-5 border-bottom-none text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none  border-left-none text-left bold">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-bottom-none text-right bold">&nbsp;</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="ph-5 pv-5 text-center">&nbsp;</td>
                                <td class="ph-5 pv-5 border-left-none text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 border-left-none text-right bold">Total</td>
                                <td class="ph-5 pv-5 text-left">&nbsp;</td>
                                <td class="ph-5 pv-5 text-right bold">Rs {{ $invoiceData->after_deduction_price }}
                                </td>
                                {{-- @if ($igst_price)
                                @else
                                <td class="ph-5 pv-5 text-right bold">Rs {{ $invoiceData->after_deduction_price + round($sgst_price) + round($cgst_price) }}
                                </td>
                                @endif --}}

                            </tr>

                            <tr>
                                <td class="ph-5 pv-3 border-bottom-none border-left-none text-left f-12"
                                    colspan="4" style="border-left: none; border-right: none;">Amount Chargeable
                                    (in words)</td>
                                <td class="ph-5 pv-3 border-bottom-none border-left-none text-right v-top"
                                    rowspan="2">E. & O.E</td>
                            </tr>
                            <tr>
                                <td class="ph-5 pv-3 border-bottom-none border-left-none text-left bold"
                                    colspan="4">INR {{ numberToWords($invoiceData->after_deduction_price) }}</td>

                            </tr>
                        </tfoot>

                    </table>

                    {{-- <table width="100%" class="data-table" style="border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th class="ph-5 pv-3 f-12 text-center" rowspan="2">HSN/SAC</th>
                            <th class="ph-5 pv-3 f-12 text-center" rowspan="2">Taxable Value</th>
                            <th class="ph-5 pv-3 f-12 text-center" colspan="2">CGST</th>
                            <th class="ph-5 pv-3 f-12 text-center" colspan="2">SGST/UTGST</th>
                            <th class="ph-5 pv-3 f-12 text-center" rowspan="2">Total Tax Amount</th>
                        </tr>
                        <tr>
                            <th class="ph-5 pv-3 f-12 text-center">Rate</th>
                            <th class="ph-5 pv-3 f-12 text-center">Amount</th>
                            <th class="ph-5 pv-3 f-12 text-center">Rate</th>
                            <th class="ph-5 pv-3 f-12 text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ph-5 pv-5 text-left w-40">999293</td>
                            <td class="ph-5 pv-5 text-right w-10">8,473.73</td>
                            <td class="ph-5 pv-5 text-right w-10">9%</td>
                            <td class="ph-5 pv-5 text-right w-10">762.00</td>
                            <td class="ph-5 pv-5 text-right w-10">9%</td>
                            <td class="ph-5 pv-5 text-right w-10">762.00</td>
                            <td class="ph-5 pv-5 text-right w-10">1,524.00</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="ph-5 pv-5 text-right bold">Total</td>
                            <td class="ph-5 pv-5 text-right bold">8,473.73</td>
                            <td class="ph-5 pv-5 text-right bold" colspan="2">762.00</td>
                            <td class="ph-5 pv-5 text-right bold" colspan="2">762.00</td>
                            <td class="ph-5 pv-5 text-right bold">1,524.00</td>
                        </tr>

                        <tr>
                            <td class="ph-5 pv-5 text-right f-12">Tax Amount (in words) :</td>
                            <td class="ph-5 pv-5 text-left bold" colspan="6">INR {{ numberToWords($price) }}</td>
                </tr>
                </tfoot>

                </table> --}}

                    <table width="100%" style="border-collapse: collapse; border: 1px solid #000;">
                        <tbody>
                            <tr>
                                <td class="ph-5 pv-5 text-left w-40">Company's PAN: AAVFT2001K</td>
                                <td class="ph-5 pv-5 text-right w-60 f-12 v-bottom" colspan="2">
                                    <b>for TIMELESS EDUCATION LLP<b>
                                            </br>
                                            </br>
                                            </br>
                                            </br>
                                            </br>
                                            Authorised Signatory
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text">This is a Computer Generated Invoice</p>
                </div>
            </div>

        </div>
    </body>

@endif

</html>
