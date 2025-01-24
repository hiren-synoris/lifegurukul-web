@extends('admin.mails.mail-layout')
@section('title', 'Payment Successfully completed..')
@section('mail-body')


{{-- @php
    dd($invoiceData);
@endphp --}}
@if($invoiceData->count() > 0)

<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
           
        </td>
    </tr>
</table>

<!-- TIMELESS content -->
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#f2f2f2" style="border-bottom: #e2e3e3 solid 1px">
                        <table width="485" border="0" align="center" cellpadding="0" cellspacing="0" class="two-left-inner">
                            <tr>
                                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">
                                    <table width="180" border="0" align="right" cellpadding="0" cellspacing="0" class="full">
                                        <tr>
                                            <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 18px;
                                      color: #000000;
                                      font-weight: bold;
                                      line-height: 28px;
                                    " mc:edit="bm12-03">
                                                <multiline style="color: #F2751F;font-size:15px"> Invoice :  <span style="color:#000;">#{{ $invoiceData->id ?? '' }} </span> 

                                                <br>
                                                Date :  <span style="color:#000;">{{ date('d-m-Y', strtotime($invoiceData->created_at)) }} </span> 
                                            </multiline>
                                            </td>
                                        </tr>

                                        <!-- <tr>
                        <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 18px;
                                      color: #000;
                                      font-weight: normal;
                                      line-height: 34px;
                                    " mc:edit="bm12-04">
                          <multiline>{{config('settings.invoice_from')}}</multiline>
                        </td>
                      </tr> -->
                                    </table>

                                    <table width="200" border="0" align="left" cellpadding="0" cellspacing="0" class="full">
                                        <tr>
                                            <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 14px;
                                      line-height:normal;
                                      color: #000;
                                      font-weight: bold;
                                    " mc:edit="bm12-05">
                                                <multiline style="color: #F2751F;font-size:15px">TIMELESS EDUCATION LLP</multiline>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td align="left" valign="top" style="font-size: 12px; line-height: 12px">
                                                &nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 14px;
                                      color: #767676;
                                      font-weight: normal;
                                      line-height: 28px;
                                    " mc:edit="bm12-06">
                                                <multiline>
                                                    503,SKY ENCLAVE,
                                                    B/HKUBER, RESIDENCY
                                                    NR SCIENCE CITY,
                                                    BHADAJ
                                                    AHMEDABAD
                                                    GSTIN/UIN: </br>
                                                    <b>24AAVFT2001K1Z4</b>
                                                    </br>
                                                    State Name : Gujarat, Code : 24
                                                    CIN: ACC-9713
                                                </multiline>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#f5f5f5" style="border-bottom: #e2e3e3 solid 1px">
                        <table width="485" border="0" align="center" cellpadding="0" cellspacing="0" class="two-left-inner">
                            <tr>
                                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">                                    
                                    <table width="200" border="0" align="left" cellpadding="0" cellspacing="0" class="full">
                                        <tr>
                                            <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 18px;
                                      color: #000;
                                      font-weight: bold;
                                    " mc:edit="bm12-05">
                                                <multiline style="color: #F2751F;font-size: 18px;">Invoice To</multiline>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td align="left" valign="top" style="font-size: 12px; line-height: 12px">
                                                &nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                      font-family: 'Open Sans', Verdana, Arial;
                                      font-size: 14px;
                                      color: #767676;
                                      font-weight: normal;
                                      line-height: 28px;
                                    " mc:edit="bm12-06">
                                                <multiline>
                                                {{ $invoiceData->learner->name ?? '' }} <Name></br>
                                                @if(!empty($invoiceData->learner->mobile)){{ $invoiceData->learner->mobile ?? '' }}, <br>@endif <Mobile Number></br>
                                                            <City and State>
                                                            @if(!empty($invoiceData->learner->city->name)){{ $invoiceData->learner->city->name ?? '' }},@endif
                            @if(!empty($invoiceData->learner->state->name)){{ $invoiceData->learner->state->name ?? '' }},@endif
                            {{ $invoiceData->learner->country->name ?? '' }}<lerner state>
                                                </multiline>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="45" align="left" valign="top" style="font-size: 45px; line-height: 45px">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#FFFFFF" style="border-bottom: #e2e3e3 solid 1pxx">
                        <table width="485" border="0" cellspacing="0" cellpadding="0" class="two-left-inner">
                            <tr>
                                <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 35px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top" style="
                              font-family: 'Open Sans', Verdana, Arial;
                              font-size: 20px;
                              color: #000000;
                              font-weight: bold;
                              line-height: 28px;
                            " mc:edit="bm12-07">
                                    <multiline style="color: #F2751F;font-size: 18px;">Purchase Items</multiline>
                                </td>
                            </tr>
                            <tr>
                                <!-- <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 15px">
                  &nbsp;
                  
                </td> -->
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@php
$price = "FREE";
if(isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan))
{
if($invoiceData->coursePlan->plan_type == 0){ // free
$price = "FREE";
}
else{
$price = $invoiceData->coursePlan->final_payable_price;
}
}

@endphp
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#FFFFFF" style="border-bottom: #e2e3e3 solid 1px">
                        <table width="485" border="0" cellspacing="0" cellpadding="0" class="two-left-inner">
                            <tr>
                                <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 35px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">
                                    <table width="250" border="0" align="left" cellpadding="0" cellspacing="0" class="two-left-inner">
                                        <tr>
                                            <td align="left" valign="top" style="
                                    element.style {
    font-family: 'Open Sans', Verdana, Arial;
    font-size: 14px;
    color: #000000;
    font-weight: normal;
    line-height: 1.5;        " mc:edit="bm12-08">
                                                <multiline><a style="text-decoration: none; color: #000000" href="{Test Affirmations - Hindi & English">

                                                        <h4 style="    line-height: 1.5;font-size: 15px;">ManKey Mastery Workshop
                                                            HSN/SAC (999293)</h4>
                                                        <p style="line-height: 21px;"><span style="font-size: 15px;"> CGST (9%)</span> ₹. 18.00 For Gujarat State)</p>
                                                        <p style="line-height: 21px;">
                                                            <span style="font-size: 15px;"> SGST (9%)</span> ₹. 18.00 For Gujarat State)
                                                        </p>
                                                        <p style="line-height: 21px;">
                                                            <span style="font-size: 15px;"> IGST (18%)</span> ₹. 36.00 Only if state is other then Gujarat
                                                        </p>
                                                    </a></multiline>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 16px;
                                    color: #000;
                                    font-weight: normal;
                                    line-height: 28px;
                                  " mc:edit="bm12-09">
                                                {{-- <multiline> {{ isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan) ? "Quantity : 1 x ". $price  : '' }}</multiline> --}}
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100" border="0" align="right" cellpadding="0" cellspacing="0" class="full">
                                        <tr>
                                            <td height="30" align="left" valign="top" style="font-size: 30px; line-height: 30px">
                                                &nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 16px;
                                    color: #000000;
                                    font-weight: bold;
                                    line-height: 1.5;
                                  " mc:edit="bm12-10">
                                                <multiline>{{ $price }}</multiline>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 35px">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#FFFFFF">
                        <table width="485" border="0" cellspacing="0" cellpadding="0" class="two-left-inner">
                            <tr>
                                <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 35px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">
                                    <table width="250" border="0" align="left" cellpadding="0" cellspacing="0" class="two-left-inner">
                                        <tr>
                                            <td align="left" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 22px;
                                    color: #000000;
                                    font-weight: bold;
                                    line-height: 34px;
                                  " mc:edit="bm12-14">
                                                <multiline>Total: </multiline>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 14px;
                                    color: #000000;
                                    font-weight: normal;
                                    line-height: 1.5;
                                  " mc:edit="bm12-15">
                                                <multiline>
                                                    Payment Method:
                                                    Payment gateway</multiline>
                                            </td>
                                        </tr>
                                    </table>

                                    <table width="100" border="0" align="right" cellpadding="0" cellspacing="0" class="full">
                                        <tr>
                                            <td height="30" align="left" valign="top" style="font-size: 30px; line-height: 30px">
                                                &nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 24px;
                                    color: #000;
                                    font-weight: bold;
                                    line-height: 28px;
                                  " mc:edit="bm12-16">
                                                <multiline style="font-size:20px;">
                                                    {{-- {{ isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan) ? $invoiceData->coursePlan->final_payable_price : '-' }} --}}
                                                    {{ $price }}
                                                </multiline>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="35" align="left" valign="top" style="font-size: 35px; line-height: 35px">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- last content -->
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle">
            <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" class="main">
                <tr>
                    <td align="center" valign="top" bgcolor="#e3dfdf">
                        <table width="485" border="0" cellspacing="0" cellpadding="0" class="two-left-inner">
                        <tr>
                                <td height="20" align="left" valign="top" style="">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top">


                                    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="full">

                                        <tr>
                                            <td align="center" valign="top" style="
                                    font-family: 'Open Sans', Verdana, Arial;
                                    font-size: 16px;
                                    color: #000;
                                    font-weight: bold;
                                    line-height: 28px;
                                  " mc:edit="bm12-16">
                                                <h3 style="text-align: center;font-size:16px;font-weight:400;"> For any Change in invoice, </h3>
                                                <p style="font-size:16px;font-weight:400;color:#000;">contact <a href="" style="color: #F2751F;">account@lifegurukul.app </a> within 7 working days
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20" align="left" valign="top" style="">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

@endsection