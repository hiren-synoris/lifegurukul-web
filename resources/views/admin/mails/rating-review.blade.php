@extends('admin.mails.mail-layout')
@section('title', 'Learner Review created successfully.')
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
                                             <td data-text="Title" data-font="Primary" align="center" valign="middle" style="font-family: 'Poppins',sans-serif; color: #000; font-size: 12px; font-weight: 400; letter-spacing: 0px; padding: 0; padding-bottom: 10px; text-align: left;">
                                                <div class="list-messages">
                                                   <p style="line-height: 1.5;text-align: center;font-size:16px;"></p>
                                                   <p style="color:#F2751F;font-weight: 600;">
                                                      Dear {{ isset($user) && !empty($user->name) ? $user->name : '' }},
                                                   </p>
                                                   <p>You received a new review from <b>{{ isset($sender) && !empty($sender->name) ? ucfirst($sender->name) : '' }}</b> for course <b>{{ isset($course) && !empty($course->title) ? $course->title : '' }} . Approve Review from admin</b>
                                                   </p>
                                                   <p>
                                                      Review : {{ isset($rating) && !empty($rating->comment) ? $rating->comment : '' }}
                                                   </p>
                                                   
   <div style="display: flex;">
      <span>Rating : </span>
         @for ($i = 1; $i <= 5; $i++)
            @if (isset($rating) && $i <= $rating->rating)
                  <span><img src="{{ !empty(config('settings.filledstar')) ? asset(Storage::url(config('settings.filledstar'))) : 'default-filled-star-path' }}" alt="Filled Star" style="width: 19px;"></span>
            @else
                  <span><img src="{{ !empty(config('settings.unfilledstar')) ? asset(Storage::url(config('settings.unfilledstar'))) : 'default-unfilled-star-path' }}" alt="UnFilled Star" style="width: 19px;"></span>
            @endif
         @endfor
   </div>



                                                   </div>
                                                   <p>
                                                      <!-- <ul>
                                                         <li><b>Message:</b>  {!! isset($supportChat) && !empty($supportChat->message) ? $supportChat->message : '' !!}
                                                         </li>                                                      
                                                         </ul> -->
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
@endsection