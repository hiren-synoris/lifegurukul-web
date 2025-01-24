@extends('admin.mails.mail-layout')
@section('title', 'Manual notification send successfully.')
@section('mail-body')


<table class="sub-table" data-group="Banner" data-module="Brand sell" data-bgcolor="Outer BG Color" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F7F7F7" style="width:100%;max-width:700px;">
  <tbody>
    <tr>
      <td align="center" valign="top">
        <table class="row" border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
          <tbody>
            <tr>
              <td data-bgcolor="Inner BG Color" align="center" valign="top" bgcolor="#FFFFFF">
                <table class="row" border="0" width="100%" align="center" cellpadding="0" cellspacing="0" style="width:100%;max-width:100%;">
                  <tbody>
                    <tr>
                      <td data-bg="Brand sell BG" align="center" bgcolor="#FFFFFF" style="padding: 15px;">
                        <table class="row" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
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
                                    Dear {{ $name ?? '' }},
                                  </p>

                                  <p>{{ $content ?? '' }}
                                  </p>


                                  <img class="logo-image" src="{{ $image ?? '' }}" alt="" style="width:50px; ">

                                  @if($external_link)
                                  <div style="display: flex;
                            justify-content: center; padding-top: 15px;">
                                    <a href=" {{ $external_link ?? '' }}" style="text-decoration: none;
                                      background-color:#ce5925;;
                                      color:white;
                                      padding: 11px 10px;">View</a>
                                  </div>
                                  @endif

                                  @php
                                  $course = App\Models\Course::where("slug",$slug)->first()->type;
                                  @endphp

                                  <div style="display: flex;
                 justify-content: center; padding-top: 15px;">
                                    @if($course==1 && $redirect_type==2)

                                    <a href="{{ route("course.details",$slug) }}" style="text-decoration: none;
                        background-color:#ce5925;;
                        color:white;
                        padding: 11px 10px;">View</a>
                                    @elseif($course==2 && $redirect_type==2)

                                    <a href="{{ route("course.packages",$slug) }}" style="text-decoration: none;
                        background-color: #ce5925;;
                        color:white;
                        padding: 11px 10px;">View</a>
                                    @elseif($redirect_type==1)
                                    <a href="{{ route("home") }}" style="text-decoration: none;
                        background-color: #ce5925;
                        color: white;
                        padding: 11px 10px;" class="button">View</a>
                                    @endif
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