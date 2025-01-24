@extends('admin.mails.mail-layout')
@section('title', 'Earning Coins.')
@section('mail-body')

@if($datas)

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
                                                      Dear {{ $datas->name }}
                                                   </p>
                                                   <p>We're thrilled to inform you that Success Coins have been allocated to your account on Lifegurukul App.</p>
                                                   <p>
                                                      You've earned {{ isset($requestcoin) && !empty($requestcoin) ? $requestcoin : '0' }} Coins . These Success Coins represent our appreciation for your dedication to learning and your commitment to self-improvement.
                                                   </p>
                                                   <p>Success Coins can be redeemed for a variety of rewards, including discounts on future course purchases, exclusive content, or even special badges to showcase your achievements.
                                                   </p>
                                                   <p>
                                                   <ul>
                                                      <li><b>Success Coins Earned:</b> {{ isset($requestcoin) && !empty($requestcoin) ? $requestcoin : '0' }}
                                                      </li>
                                                      <li><b>Balance:</b>Your updated Success Coins balance is {{ isset($total) && !empty($total) ? $total : '0' }}
                                                         .</li>
                                                         <li><b>Activity:</b> {{$content}}.
                                                      </li>   
                                                   </ul>
                                                   </p>
                                                   <p>
                                                      Here's a quick overview of how you can make the most of your Success Coins:
                                                   </p>
                                                   <p><b>Redeem for discounts:</b> Use your Success Coins to unlock discounts on your next course purchase and save on your educational journey.</p>
                                                   <p><b>Access exclusive content:</b> Exchange your Success Coins for access to exclusive content, such as bonus lectures, eBooks, or webinars.</p>
                                                   <p>Your Success Coins balance has been updated accordingly, and you can view your current balance by logging into your account on Lifegurukul App.</p>
                                                   <p>Keep up the excellent work, and continue your learning journey with Lifegurukul App.</p>
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