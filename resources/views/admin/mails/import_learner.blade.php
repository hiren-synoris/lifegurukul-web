@extends('admin.mails.mail-layout')
@section('mail-body')
    <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#fff">
        <tr>
            <td align="center" valign="middle">
                <table bgcolor="#fff" width="560" border="0" align="center" cellpadding="0" cellspacing="0"
                    class="main">

                        @yield('mail-content')

                    <tr>
                        <td style="padding:15px;">
                            <p style="color:#455056; font-size:15px;">
                                Learners have been successfully imported.
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

