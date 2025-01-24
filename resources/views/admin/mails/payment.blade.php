@extends('admin.mails.mail-layout')
@section('title', 'Payment Successfully completed..')
@section('mail-body')
    @if ($invoiceData->count() > 0)

        @php
            $course = App\Models\Course::where('slug', $invoiceData->course->slug)->first();
        @endphp

        <table class="sub-table" data-group="Banner" data-module="Brand sell" data-bgcolor="Outer BG Color" width="100%"
            border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F7F7F7" style="width:100%;max-width:700px;">
            <tbody>
                <tr>
                    <td align="center" valign="top">
                        <table class="row" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td data-bgcolor="Inner BG Color" align="center" valign="top" bgcolor="#FFFFFF">
                                        <table class="row" border="0" width="100%" align="center" cellpadding="0"
                                            cellspacing="0" style="width:100%;max-width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td data-bg="Brand sell BG" align="center" bgcolor="#FFFFFF"
                                                        style="padding: 15px;border-top: 1px solid rgba(0,0,0,0.2);">
                                                        <table class="row" border="0" align="center" cellpadding="0"
                                                            cellspacing="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td data-text="Title" data-font="Primary" align="center"
                                                                        valign="middle"
                                                                        style="font-family: 'Poppins',sans-serif;
                                                            color: #000;
                                                            font-size: 12px;
                                                            font-weight: 400;
                                                            letter-spacing: 0px;
                                                            padding: 0;
                                                            padding-bottom: 10px;
                                                            text-align: left;">
                                                                        <div class="list-messges">
                                                                            <p
                                                                                style="line-height: 1.5;text-align: center;font-size:16px;">
                                                                            </p>
                                                                            <p
                                                                                style="font-size:14px; color:#008a00; font-weight: 600;">
                                                                            </p>
                                                                            <p>Hi {{ $invoiceData->learner->name ?? '' }},
                                                                            </p>
                                                                            <p style="line-height:1.5;">You are now enrolled
                                                                                in the course -
                                                                                {{ isset($invoiceData->course->title) && !empty($invoiceData->course->title) ? $invoiceData->course->title : '' }}
                                                                            </p>


                                                                            @if (isset($invoiceData->expire_at) && !empty($invoiceData->expire_at))
                                                                                <p style="line-height:1.5;">Your access to
                                                                                    the course is valid till
                                                                                    {{ $invoiceData->expire_at }}</p>
                                                                            @else
                                                                                <p>Your access to the course is valid for a
                                                                                    lifetime.</p>
                                                                            @endif

                                                                            <!-- <p>You can access the course from
                                                                                {{ date('d-m-Y', strtotime($invoiceData->created_at)) }}
                                                                            </p> -->

                                                                            <p>You can access the course from <a
                                                                                    href="{{ route('my.course') }}"
                                                                                    alt=""
                                                                                    style="text-decoration: underline;">Here</a>
                                                                            </p>

                                                                            @if ($invoiceData->after_deduction_price !== '0')
                                                                                @if ($device_type != 2)
                                                                                    <p>Invoice pdf is attached</p>
                                                                                @endif
                                                                            @endif
                                                                            <p>• You can download Lifegurukul App from Play
                                                                                store and App Store. </p>
                                                                            <ul
                                                                                style="padding:15px 0 10px;
                                                                  margin: 0;
                                                                  display: flex;
                                                                  width: 100%;
                                                                  padding-top: 15px;
                                                                  justify-content: space-between;
                                                                  gap: 35px;">
                                                                                <li
                                                                                    style="list-style: none; width: 50%; text-align: right;">
                                                                                    <a
                                                                                        href="https://apps.apple.com/in/app/life-gurukul/id1566107921"><img
                                                                                            src="{{ !empty(config('settings.appstore')) ? asset(Storage::url(config('settings.appstore'))) : logo_default() }}"
                                                                                            alt=""
                                                                                            style="width: 40%; height: auto;"></a>
                                                                                </li>
                                                                                <li
                                                                                    style="list-style: none; width: 50%; text-align: left;">
                                                                                    <a
                                                                                        href="https://play.google.com/store/apps/details?id=com.snehdesai.courses&hl=en_IN&gl=US"><img
                                                                                            src="{{ !empty(config('settings.googleplay')) ? asset(Storage::url(config('settings.googleplay'))) : logo_default() }}"
                                                                                            alt=""
                                                                                            style="width: 40%; height: auto;"></a>
                                                                                </li>
                                                                            </ul>
                                                                            <p style="margin-top: 0px;">If you have any
                                                                                questions or need assistance at any point,
                                                                                feel free to reach out to our support team
                                                                                at <a
                                                                                    href="#">customerhappinessmanager@lifegurukul.app.</a>
                                                                            </p>
                                                                            <p>You can also contact us on
                                                                                <a href="tel:+91 7211128282">+91
                                                                                    7211128282</a> (During Office hours).
                                                                            </p>
                                                                            <p>Keep Learning, <br>Team Lifegurukul</p>
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
