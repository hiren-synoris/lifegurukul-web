@extends('admin.mails.mail-layout')
@section('title', 'Reddeem Coins.')
@section('mail-body')
@if($datas->count() > 0)
<table class="sub-table" data-group="Banner" data-module="Brand sell" data-bgcolor="Outer BG Color" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F7F7F7" style="width:100%;max-width:700px;">
   <tbody>
      <tr>
         <td align="center" valign="top">
            <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
               <tbody>
                  <tr>
                     <td data-bgcolor="Inner BG Color" align="center" valign="top" bgcolor="#FFFFFF">
                        <table class="row" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;">
                           <tbody>
                              <tr>
                                 <td data-bg="Brand sell BG" align="center" bgcolor="#FFFFFF" style="padding: 15px;">
                                    <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
                                       <tbody>
                                          <tr>
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif;
                                                color: #000;
                                                font-size: 12px;
                                                font-weight: 400;
                                                letter-spacing: 0px;
                                                padding: 0;
                                                padding-bottom: 10px;
                                                text-align: left;">
                                                <div class="list-messges">
                                                   <p style="line-height: 1.5;text-align: center;font-size:16px;">
                                                   </p>
                                                  
                                                   <p style="color:#F2751F;font-weight: 600;">
                                                      Dear {{ $datas->name }},
                                                   </p>
                                                   <p>We're excited to inform you that you've successfully redeemed your Success Coins on Lifegurukul App!</p>
                                                   <p>Here's a summary of your recent redemption:</p>
                                                   <ul>
                                                      <li><b>Success Coins Redeemed:</b> {{ isset($requestcoin) && !empty($requestcoin) ? $requestcoin : '0' }} </li>
                                                      <li><b>Remaining Balance:</b>Your updated Success Coins balance is {{ isset($total) && !empty($total) ? $total : '0' }}.</li>
                                                      <li><b>Activity:</b> {{$content}}.
                                                      </li> 
                                                   </ul>
                                                   </p>
                                                   <p>
                                                      You can view your updated Success Coins balance and track your rewards by logging into your account on Lifegurukul App.
                                                   </p>
                                                   <p style="line-height:1.5;">If you have any questions or need assistance at any point, feel free to reach out to our support team at <a style="text-decoration: none;" mailto:href="">customerhappinessmanager@lifegurukul.app.</a>
                                                   </p>
                                                   <p>You can also contact us on <a style="text-decoration: none;" href="">+91 7211128282.</a></p>
                                                   <p style="line-height:1.5;">Keep Learning, <br> Team Lifegurukul
                                                   </p>
                                                </div>
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
         </td>
      </tr>
   </tbody>
</table>
@endif
@endsection