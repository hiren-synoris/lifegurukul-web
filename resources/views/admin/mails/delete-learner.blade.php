@extends('admin.mails.mail-layout')
@section('title', 'Learner deleted successfully.')
@section('mail-body')


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
                                                      Hi {{ isset($user) && !empty($user->name) ? $user->name : '' }},
                                                   </p>

                                                   <p>
    {{ isset($get_learner) && $get_learner->country ? $get_learner->country->phonecode : '' }}
    {{ isset($get_learner) ? $get_learner->mobile : '' }}
    Please review and inactive account from here
</p>


                                                   <p style="padding:0 15px;color:#455056; font-size:15px;line-height:24px; margin:0;font-family: 'Roboto'">Please click <a target="_blank" style="text-decoration: underline;" href="{{ isset($url) && !empty($url) ? $url : '' }}"><b>Here</b></a> to view.</p>

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
@endsection
