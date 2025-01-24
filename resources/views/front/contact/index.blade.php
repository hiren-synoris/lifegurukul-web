@extends('front.layout.mainlayout')
@section('content')
    @component('front.components.breadcrumb')
        @slot('title')
            <a href="{{ url('/') }}">Home</a>
        @endslot
        @slot('li2')
            Contact Us
        @endslot
    @endcomponent

    @component('front.components.pagebanner')
        @slot('title')
            Contact Us
        @endslot
    @endcomponent


    <div class="page-content">
        <div class="container">
            <div class="row aos" data-aos="fade-up">
                <div class="col-lg-8">
                    <div class="support-wrap">
                        <h5>Submit a Request</h5>
                        <form action="{{ route('contact-us.store') }}" method="POST" id="contact-us-store">
                            @csrf
                            <input type="hidden" name="status" value="{{ request()->is('contact-us-new') }}">
                            <div class="form-group">
                                <label for="name">Name</label><span style="color: red">*</span>
                                <input type="text" class="form-control" placeholder="Enter your Name" id="name"
                                    name="name" value="{{ auth()->guard('learner')->user()?->name }}">
                                @error('name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label><span style="color: red">*</span>
                                <input type="email" class="form-control" placeholder="Enter your email address"
                                    id="email" name="email" value="{{ auth()->guard('learner')->user()?->email }}">
                                @error('email')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile</label><span style="color: red">*</span>
                                {{-- <input type="text"  class="form-control mobiles" name="mobile" id="mobile" value="{{ auth()->guard("learner")->user()?->mobile }}" placeholder="Enter your mobile"> --}}
                                <input type="text" class="form-control mobiles" name="mobile" id="mobile"
                                    value="9664957351" placeholder="Enter your mobile">
                                @error('mobile')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                                <div class="error-new" id="error-msg">Invalid number</div>
                                {{-- <div class="error-new" id="error-msg"></div> --}}
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label><span style="color: red">*</span>
                                <textarea class="form-control" placeholder="Write down here" rows="4" id="description" name="description">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                            <input type="hidden" value="{{ $country?->phonecode }}" name="countryCode"
                                class="countryCode">
                            {{-- <div class="form-group">
                                {!! NoCaptcha::renderJs() !!}
                                {!! NoCaptcha::display() !!}
                            </div> --}}

                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">



                            <button class="btn btn-submit btn_hide_show" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="support-wrap">
                        <h5>Contact Information</h5>
                        <p>{{ !empty(config('settings.contactinformation')) ? config('settings.contactinformation') : '' }} </p>

                        @if (!empty(config('settings.contact_no')))
                            <div class="contct_row">

                                <div class="number-text">
                                    <i class="fa fa-phone" aria-hidden="true"></i>
                                    <a href="tel:<?php echo config('settings.contact_no'); ?>">
                                        <?php echo config('settings.contact_no'); ?>
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if (!empty(config('settings.whatsapp_contact_no')))
                            <div class="contct_row">
                                <div class="number-text">
                                    <i class="fa-brands fa-whatsapp" style="font-size:23px" aria-hidden="true"></i>
                                    <a href="tel:<?php echo config('settings.whatsapp_contact_no'); ?>">
                                        <?php echo config('settings.whatsapp_contact_no'); ?>
                                    </a>
                                </div>

                            </div>
                        @endif
                        @if (!empty(config('settings.footer_contact_email')))
                            <div class="contct_row">
                                <div class="number-text">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                    <a href="mailto:<?php echo config('settings.footer_contact_email'); ?>">
                                        <?php echo config('settings.footer_contact_email'); ?>
                                    </a>
                                </div>

                            </div>
                        @endif
                        <div class="social-incon">
                            <ul>
                                <li><a
                                        href="{{ !empty(config('settings.facebook_url')) ? config('settings.facebook_url') : 'javascript:void(0)' }}"><i
                                            class="fa-brands fa-facebook-f"></i></a></li>
                                <li>
                                    <a
                                        href="{{ !empty(config('settings.instagram_url')) ? config('settings.instagram_url') : 'javascript:void(0)' }}"><i
                                            class="fa-brands fa-instagram"></i></a>
                                </li>
                                <li>
                                    <a
                                        href="{{ !empty(config('settings.youtube_url')) ? config('settings.youtube_url') : 'javascript:void(0)' }}"><i
                                            class="fa-brands fa-youtube"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('nocaptcha.sitekey') }}"></script>

    <script>
        // grecaptcha.ready(function() {
        //     $('#contact-us-store').on('submit', function(e) {
        //         e.preventDefault();
        //         grecaptcha.execute('{{ config('nocaptcha.sitekey') }}', {
        //             action: 'submit'
        //         }).then(function(token) {
        //             $('#g-recaptcha-response').val(token);
        //             $('#contact-us-store').unbind('submit').submit();
        //         });
        //     });
        // });


        $(document).ready(function() {




            // $('.mobiles').keyup(function() {
            //     this.value = this.value.replace(/[^0-9\.]/g, '');
            // });

            $("#contact-us-store").validate({
                rules: {
                    name: {
                        required: true,
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: true,
                        number: true,
                        // minlength: 10,
                        // maxlength: 10
                    },
                    description: {
                        required: true
                    }

                },
                messages: {
                    name: {
                        required: "Name is required."
                    },
                    email: {
                        required: "Email is required.",
                        email: "Please enter a valid email address."
                    },
                    mobile: {
                        required: "Mobile Number is required.",
                        minlength: " Your Mobile Number must be consist of at least 10 Digits.",
                        maxlength: " Your Mobile Number must be consist of at max 10 Digits.",
                        number: " Please enter only digit."
                    },
                    description: {
                        required: "Description is required."
                    }
                },
                submitHandler: function(form) {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ config('nocaptcha.sitekey') }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                            form.submit();
                        });
                    });
                }
            });




            // @if (session('notification'))
            // Swal.fire({
            //     title: "{{ session('notification.title') }}",
            //     text: "{{ session('notification.msg') }}",
            //     icon: "{{ session('notification.status') }}",
            //     showCancelButton: true,
            //     confirmButtonText: 'OK',
            //     // Optional: Customize the additional button
            //     showDenyButton: "{{ session('notification.denyButtonText') ? 'true' : 'false' }}",
            //     denyButtonText: "{{ session('notification.denyButtonText') }}",
            // }).then((result) => {
            //     if (result.isConfirmed) {
            //         // Handle confirm button action
            //     } else if (result.isDenied) {
            //         var currentUrl = window.location.href;
            //         var baseUrl = currentUrl.split('/')[0];
            //         var newUrl = baseUrl + '/contact-us-new';
            //         window.location.href = newUrl;

            //     }
            // });
            // @endif


        });
    </script>

    <script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const input = document.querySelector(".mobiles");
            const errorMsg = document.querySelector("#error-msg");
            const hiddenCountryId = document.querySelector("#country_id");
            const submitBtn = document.querySelector("#generateOtpBtn");
            const mobileNumberDisplay = document.querySelector("#mobile-number");

            var code = "{{ $country?->code }}"
            // alert(mobileNumberDisplay)
            const iti = window.intlTelInput(input, {
                initialCountry: code !== "" ? code : "IN",
                autoPlaceholder: "polite",
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
                nationalMode: false,
                formatOnDisplay: false,
                customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                    return "Enter Your Mobile Number";
                }
            });



            input.addEventListener('keyup', function() {
                input.value = input.value.replace(/\D/g, '');
                if (input.value.trim()) {
                    if (iti.isValidNumber()) {
                        errorMsg.style.display = 'none';

                        const countryData = iti.getSelectedCountryData();
                        const countryCode = countryData.dialCode;
                        const mobileNumber = iti.getNumber();
                        $(".countryCode").val(countryCode)
                        $(".btn_hide_show").prop("disabled", false);
                    } else {

                        $(".btn_hide_show").prop("disabled", true);
                        errorMsg.style.display = 'block';
                    }
                } else {
                    errorMsg.style.display = 'none';


                }
            });
            input.addEventListener('countrychange', function() {
                input.value = '';
            });
        });



        $(document).ready(function() {
            var login_mo = "{{ auth()->guard('learner')->user()?->mobile }}"
            $(".mobiles").val(login_mo)
        })
    </script>
@endsection
