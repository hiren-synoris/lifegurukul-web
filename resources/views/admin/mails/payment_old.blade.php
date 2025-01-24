@extends('admin.mails.mail-layout')
@section('title', 'Payment Successfully completed..')
@section('mail-body')
@if($invoiceData->count() > 0)
<table class="sub-table" data-group="Banner" data-module="Brand sell" data-bgcolor="Outer BG Color" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F7F7F7" style="width:100%;max-width:700px;">
   <tbody>
      <tr>
         <td align="center" valign="top">
            <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
               <tbody>
                  <tr>
                     <td data-bgcolor="Inner BG Color" align="center" valign="top" bgcolor="#f2f2f2">
                        <table class="row" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;">
                           <tbody>
                              <tr>
                                 <td data-bg="Brand sell BG" align="left" bgcolor="#f2f2f2" style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
                                    <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="left" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #F2751F;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 600;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;">
                                                TIMELESS EDUCATION LLP
                                             </td>
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;">
                                                <b style="color: #F2751F; font-weight: 600;">Invoice
                                                :</b> #{{ $invoiceData->id ?? '' }}
                                             </td>
                                          </tr>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="left" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.4;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;vertical-align: top;">
                                                503,SKY ENCLAVE, <br> B/HKUBER, RESIDENCY
                                                NR<br> SCIENCE CITY, BHADAJ<br> AHMEDABAD
                                                GSTIN/UIN:<br> 24AAVFT2001K1Z4
                                                <br> State Name: Gujarat, Code: 24<br> CIN:
                                                ACC-9713<br>
                                             </td>
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;vertical-align: top;">
                                                <b style="color: #F2751F; font-weight: 600;">Date:</b>
                                                {{ date('d-m-Y', strtotime($invoiceData->created_at)) }}
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!--[if (gte mso 9)|(IE)]></v:textbox></v:rect><![endif]-->
                                 </td>
                              </tr>
                              <tr>
                                 <td data-bg="Brand sell BG" align="left" bgcolor="#f5f5f5" style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
                                    <table class="row" border="0" align="left" cellpadding="0" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="left" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #F2751F;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 600;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;">
                                                Invoice To
                                             </td>
                                          </tr>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="left" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.4;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;vertical-align: top;">
                                                <multiline>
                                                   {{ $invoiceData->learner->name ?? '' }}
                                                   <Name>
                                                   </br>
                                                   @if(!empty($invoiceData->learner->mobile)){{ $invoiceData->learner->mobile ?? '' }}, <br>@endif 
                                                   <Mobile Number>
                                                   </br>
                                                   <City and State>
                                                   @if(!empty($invoiceData->learner->city->name)){{ $invoiceData->learner->city->name ?? '' }},@endif
                                                   @if(!empty($invoiceData->learner->state->name)){{ $invoiceData->learner->state->name ?? '' }},@endif
                                                   {{ $invoiceData->learner->country->name ?? '' }}
                                                   <lerner state>
                                                </multiline>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!--[if (gte mso 9)|(IE)]></v:textbox></v:rect><![endif]-->
                                 </td>
                              </tr>
                              <tr>
                                 <td data-bg="Brand sell BG" align="center" bgcolor="#fbfbfb" style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
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
                                    <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #F2751F;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 600;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;
                                                ">
                                                <b>Purchase Items</b>
                                             </td>
                                             <td rowspan="2" data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 600;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: center;
                                                ">
                                                <b><multiline>{{ $price }}</multiline></b>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.4;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;vertical-align: top;word-break: break-all;">
                                                <b style="font-weight: 600;"><multiline> {{ isset($invoiceData->course->title) && !empty($invoiceData->course->title) ? $invoiceData->course->title : '' }}</multiline><br>HSN/SAC (999293)</b>
                                                <p style="width: 240px;">CGST (9%). 18.00
                                                   For Gujarat State) SGST (9%). 18.00 For
                                                   Gujarat State) IGST (18%) *. 36.00 Only
                                                   if state is other then Gujarat
                                                </p>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!--[if (gte mso 9)|(IE)]></v:textbox></v:rect><![endif]-->
                                 </td>
                              </tr>
                              <tr>
                                 <td data-bg="Brand sell BG" align="left" bgcolor="#fbfbfb" style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
                                    <table class="row" border="0" align="left" cellpadding="0" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="left" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.4;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;vertical-align: top;">
                                                <b style="font-weight: 600; font-size: 20px; ">Total:</b>
                                                <p style="width: 240px;">Payment Method:
                                                   Payment gateway
                                                </p>
                                             </td>
                                             <td rowspan="2" data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #22100D;
                                                font-size: 12px;
                                                line-height: 1.1;
                                                font-weight: 600;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: center;
                                                ">
                                                <b><multiline style="font-size:20px;">
                                                    {{-- {{ isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan) ? $invoiceData->coursePlan->final_payable_price : '-' }} --}}
                                                    {{ $price }}
                                                </multiline></b>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!--[if (gte mso 9)|(IE)]></v:textbox></v:rect><![endif]-->
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>
@endif
@endsection