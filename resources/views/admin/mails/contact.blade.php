@extends('admin.mails.mail-layout')
{{-- @section('title', 'Contact Created Successfully') --}}
@section('mail-body')
    <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#fff">
        <tr>
            <td align="center" valign="middle">
                <table bgcolor="#fff" width="560" border="0" align="center" cellpadding="0" cellspacing="0"
                    class="main">
                    <tr>
                        <td style="padding:15px;">
                            <p style="color:#455056; font-size:15px;">
                                Hello {{ $contactArray['name'] ?? ''}},
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td cellpadding="0" cellspacing="0">
                            @if(isset($flag) && $flag == 1)
                                <p style="padding:15px;color:#455056; font-size:15px;line-height:24px; margin:0;font-family: 'Roboto'">{{ isset($contactArray) && isset($contactArray['reply']) && !empty($contactArray['reply'])? $contactArray['reply'] : '' }}</p>
                            @else
                                <p
                                    style="padding:15px;color:#455056; font-size:15px;line-height:24px; margin:0;font-family: 'Roboto'">
                                    You have created contact successfully to {{env('APP_NAME')}}. Your ticket number is: <b>#{{ $contactArray['id'] }}</b>.<br>
                                    Thank you for contacting us. We will get back to you shortly.

                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:15px;">
                            <p style="color:#455056; font-size:15px;">
                                Regards,
                            </p>
                            <p style="color:#455056; font-size:15px;">
                                {{env('APP_NAME')}}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span style="display:block; vertical-align:middle; border-bottom:1px solid #cecece;"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection

