@php
    $countriesCollection = Helper::getCountries();
@endphp
<div class="modal fade login-modal " id="mobileLoginModal" tabindex="-1" role="dialog"
    aria-labelledby="mobileLoginModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close modalClose1" data-id="#mobileLoginModal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">


                    <div class="col-md-5 popup-left">
                        <img width="250"
                            src="{{ !empty(config('settings.login_logo')) ? Storage::url(config('settings.login_logo')) : asset('front/img/login_logo.png') }}"
                            class="img-fluid" alt="{{ env('APP_NAME') }}">
                    </div>
                    <!--First Step-->
                    @php
                        $mobile = '';
                        $country_id = '';
                    @endphp
                    @php
                        $mobile = session()->get('mobile');
                        $country_id = session()->get('country_id');
                    @endphp
                    <div class="col-md-7 popup-right" id="firstStep">
                        @if (session()->has('mobile'))
                            @php
                                $mobile = session()->get('mobile');
                            @endphp
                        @endif
                        <div id="server_error_1" style="display:none"></div>
                        <form method="POST" id="mobileLoginForm" action="{{ route('learner.login-view') }}">
                            @csrf
                            <div class="form-group flex-wrap login-form">
                                <h2 class="modal-title">{{ __('Login') }}</h2>
                                <label for="mobile"
                                    class=" col-form-label text-md-right w-100">{{ __('Enter Mobile') }}</label>
                                <div class="d-flex countryFlagSelect">
                                    <div id="country_select">
                                        <ul class="countryFlagLi">
                                            @if (isset($countriesCollection) && !empty($countriesCollection))
                                                @foreach ($countriesCollection as $country)
                                                    @if ($country->id == 1)
                                                        <li class="{{ $country->id == 1 ? 'init' : '' }}">
                                                            <input id="checkbox_{{ $country->id }}"
                                                                {{ $country->id == 1 ? 'checked' : '' }}
                                                                data-code="{{ $country->phonecode }}" name="country_id"
                                                                type="checkbox" value="{{ $country->id }}" />
                                                            <label for="checkbox_{{ $country->id }}"
                                                                class="country_flag">
                                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                                    alt="Flag">{{$country->name}} (+{{ $country->phonecode }}) </label>
                                                        </li>
                                                        <li>
                                                            <input id="checkbox_{{ $country->id }}" name="country_id"
                                                                type="checkbox" value="{{ $country->id }}"
                                                                data-code="{{ $country->phonecode }}" />
                                                            <label for="checkbox_{{ $country->id }}"
                                                                class="country_flag">
                                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                                    alt="Flag">{{$country->name  }} (+{{ $country->phonecode }}) </label>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <input id="checkbox_{{ $country->id }}" name="country_id"
                                                                type="checkbox" value="{{ $country->id }}"
                                                                data-code="{{ $country->phonecode }}"
                                                                class="country_flag123" />
                                                            <label for="checkbox_{{ $country->id }}"
                                                                class="country_flag">
                                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                                    alt="Flag">{{$country->name  }} (+{{ $country->phonecode }})  </label>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            @else
                                                <li></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap flex-fill countryFlagInput">
                                    <input id="mobile" type="text" class="form-control mobile w-100 mobile_auto"
                                        name="mobile" value="" autocomplete="mobile"
                                        placeholder="Enter Your Mobile Number">
                                    <span class="invalid-feedback" style="display:block;" role="alert"
                                        id="mobileError">
                                    </span>
                                    <div class="error-new" id="error-msg"></div>
                                </div>
                                {{-- <input type="tel" id="mobile" name="mobile" value=""
                                    class="iti__tel-input form-control mobile">
                                <div class="error-new" id="error-msg"></div>
                                <span class="invalid-feedback" style="display:block;" role="alert" id="mobileError">
                                    <strong></strong>
                                </span> --}}
                                <input type="hidden" name="deviceId" value="" id="deviceId">
                                <input type="hidden" name="country_id" value="1" id="country_id">

                                <div class="form-check home-checkbox whatsapp_chkbox text-center" style="">
                                    <input type="checkbox" class="form-check-input" id="send_wa_otp"
                                        name="is_whatsapp" value="1" data-gtm-form-interact-field-id="0">
                                    <label class="form-check-label" for="send_wa_otp"><i
                                            class="fa-brands fa-whatsapp"></i> Send OTP on Whatsapp</label>
                                </div>

                            </div>

                            <div class="form-group otp-btn">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary" id="generateOtpBtn">
                                        {{ __('Generate OTP') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- First Step End -->
                    <!--Second Step-->

                    <div class="col-md-7 popup-right" style="display:none;" id="secondStep">
                        <div class="form-group d-flex flex-wrap login-form step2">
                            <h2 class="modal-title">{{ __('Verify OTP') }}</h2>
                            @php
                                $message = '';
                            @endphp

                            @if (session()->has('mobile'))
                                @php
                                    $message =
                                        'OTP sent to ' . str_repeat('*', strlen($mobile) - 4) . substr($mobile, -4);
                                @endphp
                            @endif


                            <div id="server_error" style="display:none"></div>
                            <div class="alert alert-danger" id="otperror" role="alert" style="display:none;">OTP
                                Timeout. Please Resend OTP.</div>
                            <!-- @if (env('APP_ENV') != 'production')
<div class="alert alert-success" role="alert" id="otpmessage" style="display:none"></div>
@endif -->
                            <div class="alert alert-warning" role="alert" id="mobilenumbermsg">{{ $message }}
                            </div>
                            <div class="alert alert-danger" role="alert" style="display:none"></div>

                            <form method="POST" action="{{ route('learner.getlogin') }}" id="otploginForm">
                                @csrf

                                <input type="hidden" name="mobile" class="mobile" id="mobile"
                                    value="{{ $mobile }}" />
                                <input type="hidden" name="country_id" class="country_id" id="country_id"
                                    value="{{ $country_id }}" />
                                <div class="mb-3">
                                    <label for="password" class="">{{ __('OTP') }}</label>

                                    <div class="">
                                        <input id="password" type="text"
                                            class="form-control @error('password') is-invalid @enderror password_auto"
                                            name="password" value="{{ old('password') }}" required
                                            autocomplete="password" placeholder="Enter OTP">

                                        <span class="invalid-feedback" style="display:block;" role="alert"
                                            id="passwordError">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="timerdiv mb-3">Expire In <span id="timerSpan"></span></div>


                                <div class="mb-2">
                                    <div class="d-flex gap-3 justify-content-center ">
                                        <button type="button" id="back" class="btn btn-success">
                                            {{ __('Back') }}
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="reset-otp_section" style="display:none">
                                <input id="expire_at" name="expire_at" type="hidden"
                                    value="{{ session()->has('expire_at') ? session()->get('expire_at') : '' }}">
                                <form id="resendotpForm" action="{{ route('learner.resendOtp') }}" method="POST">
                                    {!! csrf_field() !!}
                                    <input id="mobile" class="mobile" name="mobile" type="hidden"
                                        value="{{ $mobile }}">
                                    <input type="hidden" name="country_id" id="country_id"
                                        value="{{ $country_id }}" />
                                    <input type="hidden" name="is_whatsapp" id="is_resend_whatsapp"
                                        value="0" />
                                    <div class="mb-0">
                                        <div class="reset-otp-btn">
                                            <button id="smsotpresend" type="submit"
                                                class="btn btn-success resend_otp">
                                                {{ __('Resend OTP') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-0" style="margin-top:10px;">
                                        <div class="reset-otp-btn">
                                            <button type="button" id="whatsapp_resend_trigger"
                                                class="btn btn-success resendwa_otp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                                {{ __('Resend OTP on Whatsapp') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- second Step -->

                    <!--Third Step -->
                    <div class="col-md-7 popup-right" style="display:none;" id="thirdStep">
                        <div class="form-group d-flex flex-wrap login-form step3">
                            <h2 class="modal-title">{{ __('Profile') }}</h2>
                            <div id="profileError" style="display:none">
                            </div>
                            <form method="POST" action="{{ route('student.profile') }}" id="profileForm">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-12 mb-2">
                                        <label for="name">Full NameS <span style="color: red">*</span></label>
                                        <input type="text" class="form-control" placeholder="Enter your Full Name"
                                            name="name" id="name" autofocus value="">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="email">Email <span style="color: red">*</span></label>
                                        <input id="email" type="email" class="form-control email"
                                            name="email" autocomplete="email" autofocus
                                            placeholder="Enter Your Email">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="gender" id="genderLabel">Gender</label></label><br>
                                        <div class="form-check">
                                            <input class="form-check-input select-type" type="radio" name="gender"
                                                id="male" value="{{ App\Models\Learner::MALE }}">
                                            <label class="form-check-label"
                                                for="male">{{ App\Models\Learner::MALE_LABEL }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input select-type" type="radio" name="gender"
                                                id="female" value="{{ App\Models\Learner::FEMALE }}">
                                            <label class="form-check-label"
                                                for="female">{{ App\Models\Learner::FEMALE_LABEL }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input select-type" type="radio" name="gender"
                                                id="other" value="{{ App\Models\Learner::OTHER }}">
                                            <label class="form-check-label"
                                                for="other">{{ App\Models\Learner::OTHER_LABEL }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="d_o_b">Date Of birth</label>
                                        <input id="d_o_b" type="date" class="form-control d_o_b"
                                            name="d_o_b" max="{{ date('Y-m-d') }}" autocomplete="d_o_b" autofocus
                                            placeholder="Enter Your Dateof birth">
                                    </div>
                                    <div class="col-md-6 mb-2" style="display:none">
                                        <label for="country">Country</label>
                                        <select class="form-control country-dropdown" id="country-dropdown"
                                            name="country_id">
                                            <option value="">Select Country</option>
                                            @if (isset($countriesCollection) && !empty($countriesCollection))
                                                @foreach ($countriesCollection as $country)
                                                    <option value="{{ $country->id }}">
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="">No Country Found</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2 popup-select">
                                        <label for="state">State</label>
                                        <select class="form-select select state-dropdown" id="state-dropdown"
                                            name="state_id">
                                            <option value="">Select State</option>
                                            @if (Helper::getStates() != null)
                                                @foreach (Helper::getStates() as $state)
                                                    <option value="{{ $state->id }}"
                                                        {{ Auth::guard('learner')->user()->state_id == $state->id ? 'selected="selected"' : '' }}>
                                                        {{ $state->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="">No State Found</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2 popup-select">
                                        <label for="city">City</label>
                                        <select class="form-select select city-dropdown" id="city-dropdown"
                                            name="city_id">
                                        </select>
                                    </div>
                                </div>


                                <div class="mb-0">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--Third Step End -->
                </div>
            </div>
        </div>
    </div>
</div>


