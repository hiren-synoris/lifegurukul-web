@extends('admin.mails.mail-layout')
{{-- @section('title', 'Contact Created Successfully') --}}
@section('mail-body')

@if ($failures->count() > 0 )
        <table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#fff">
            <tr>
                <td align="center" valign="middle">
                    <table class="table table-danger ">
                        <tr>
                            <th>Row</th>
                            {{-- <th>Errors ( <span style="color:red">Ignore Row No 1 Validation</span> )</th> --}}
                            <th>Errors ( <span style="color:red"></span> )</th>
                        </tr>
                        {{-- @dd($failures) --}}
                        @foreach ($failures as $validation)
                            {{-- @dd($variable) --}}
                            <tr>
                                <td>{{ $validation->row() }}</td>
                                <td>
                                    <ul>
                                        @foreach ($validation->errors() as $e)
                                            <li>{{ $e }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif
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
                                {{ env('APP_NAME') }}
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
