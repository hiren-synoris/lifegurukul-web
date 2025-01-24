@extends('admin.mails.mail-layout')
@section('title', 'Registration Completed Successfully')
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
                                                                                    Dear {{$learnerData->name??''}},</p>
                                                                                <p>Welcome to LifeGurukul App! We're thrilled to have you as a part of our learning community.</p>
                                                                                <p>Here, you'll find a wide range of courses, resources, and tools to help you achieve your learning goals.</p>
                                                                                <p>Your account has been successfully created, and you can now start exploring our platform. Here are a few things you can do to get started:</p>

                                                                                <ul>
                                                                                    <li>Complete your profile: Add a profile picture and fill in any necessary information to personalize your experience.</li>
                                                                                    <li>Explore courses: Browse through our diverse catalog of courses and discover topics that interest you</li>
                                                                                </ul>
                                                                                </p>
                                                                                <p>You can download Lifegurukul App from Play store and App Store. (Logo shown below with relevant links to store)</p>
                                                                                <p>
                                                                                    You can view your updated Success Coins balance and track your rewards by logging into your account on Lifegurukul App.
                                                                                </p>
                                                                                <ul style="padding: 0;
                                                                                    margin: 0;
                                                                                    display: flex;
                                                                                    width: 100%;
                                                                                    padding-top: 15px;
                                                                                    justify-content: space-between;
                                                                                    gap: 35px;">
                                                      <li style="list-style: none; width: 50%; text-align: right;">
                                                      <a href="https://apps.apple.com/in/app/life-gurukul/id1566107921"><img src="{{ !empty(config('settings.appstore')) ? asset(Storage::url(config('settings.appstore'))) : logo_default() }}" alt="" style="width: 40%; height: auto;"></a>
                                                      </li>
                                                      <li style="list-style: none; width: 50%; text-align: left;">
                                                      <a href="https://play.google.com/store/apps/details?id=com.snehdesai.courses&hl=en_IN&gl=US"><img src="{{ !empty(config('settings.googleplay')) ? asset(Storage::url(config('settings.googleplay'))) : logo_default() }}" alt="" style="width: 40%; height: auto;"></a>
                                                      </li>
                                                   </ul>

                                                                                <p style="line-height:1.5;">If you have any questions or need assistance at any point, feel free to reach out to our support team at <a style="text-decoration: none;" mailto:href="customerhappinessmanager@lifegurukul.app">customerhappinessmanager@lifegurukul.app.</a>
                                                                                </p>
                                                                                <p>You can also contact us on <a style="text-decoration: none;" href="">+91 7211128282.</a> (During Office hours).</p>
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
            @endsection
