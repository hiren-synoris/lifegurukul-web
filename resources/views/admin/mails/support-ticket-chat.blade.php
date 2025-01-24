@extends('admin.mails.mail-layout')
@section('title', 'Support ticket created successfully.')
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
                                                      Dear {{ isset($user) && !empty($user->name) ? $user->name : '' }},
                                                   </p>
                                                   
                                                   <p>You received a new message from <b>{{ isset($sender) && !empty($sender->name) ? ucfirst($sender->name) : '' }}</b> for support ticket number <b>#{{ isset($support) && !empty($support->id) ? $support->id : '' }}
                                                   </p>
                                                   
                                                   <p>
                                                   <!-- <ul>
                                                      <li><b>Message:</b>  {!! isset($supportChat) && !empty($supportChat->message) ? $supportChat->message : '' !!}
                                                      </li>                                                      
                                                   </ul> -->
                                                   </p>
                                                   <div class="btn-center" style="text-align: center;">
                                                      <a href="{{ isset($url) && !empty($url) ? $url : '' }}" style="text-decoration: none;
                                                            background-color: #ce5925;
                                                            color: white;
                                                            padding: 11px 10px;" class="button">View</a>
                                                   </div>
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