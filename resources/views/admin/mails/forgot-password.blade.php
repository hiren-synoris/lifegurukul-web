@extends('admin.mails.mail-layout')
@section('title', 'Your Password Reset Link')
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
                                            <td data-bg="Brand sell BG" align="center" bgcolor="#FFFFFF" style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
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
                                                                    <p style="line-height: 1.5;text-align: center;font-size:16px;"></p>

                                                                    <p style="color:#F2751F;font-weight: 600;">Your password reset link!</p>
                                                                    <p>Click on the link below to reset your password.</p>
                                                                    <p style="line-height:1.5;font-weight: 600;"><a style="text-decoration: none;" href="{{ $url }}">Reset password</a></p>
                                                                    <p style="line-height:1.5;">If clicking the link doesn't work, copy and paste the above URL in your browser.</p>
                                                                    <p style="line-height:1.5;">If you have any questions or need assistance at any point, feel free to reach out to our support team at <a style="text-decoration: none;" href="mailto:customerhappinessmanager@lifegurukul.app">customerhappinessmanager@lifegurukul.app</a>.</p>
                                                                    <p>You can also contact us on <a style="text-decoration: none;" href="">+91 7211128282.</a></p>
                                                                    <p style="line-height:1.5;">Keep Learning, <br> Team Lifegurukul</p>
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