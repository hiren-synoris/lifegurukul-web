@if (!Auth::guard('learner')->user())
    <!-- Mobile number Popup -->
    @include('auth.login-otp')
    <!-- End Mobile number Popup -->
@endif

<!-- Wishlist Popup -->
@include('front.wishlist.wishlistModal')
<!-- End Wishlist Popup -->

{{-- @php
use App\Models\Coupon;
    $my_coupens =Coupon::get();
@endphp --}}

<!-- Edit Plan Modal code start for edit popup modal-->
<div class="modal fade" id="PlanModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="PlanModalLabel" aria-hidden="true">
</div>


<!-- edit Plan Modal code end -->


@php
    // dd(Auth::guard('learner')->user());
    if (Auth::guard('learner')->user()) {
        // dump(Auth::guard('learner')->user());
        $email = Js::from(Auth::guard('learner')->user()->email);
        $name = Js::from(Auth::guard('learner')->user()->name);

        $country_id = Js::from(Auth::guard('learner')->user()->country_id);
        $state_id = Js::from(Auth::guard('learner')->user()->state_id);
    } else {
        $email = '';
        $country_id = '';
        $name = '';
        $state_id = '';
    }

@endphp

<!-- jQuery -->
<script src="{{ asset('front/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

<!-- Bootstrap Core JS -->
<script src="{{ asset('front/js/bootstrap.bundle.min.js') }}"></script>

<!-- Select2 JS -->
<script src="{{ asset('front/plugins/select2/js/select2.min.js') }}"></script>

<!-- Ckeditor JS -->
<script src="{{ asset('front/js/ckeditor.js') }}"></script>

<!-- Bootstrap Tagsinput JS -->
<script src="{{ asset('front/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js') }}"></script>

<!-- counterup JS -->
<script src="{{ asset('front/js/jquery.waypoints.js') }}"></script>
<script src="{{ asset('front/js/jquery.counterup.min.js') }}"></script>

<!-- Owl Carousel -->
<script src="{{ asset('front/js/owl.carousel.min.js') }}"></script>

<!-- Slick Slider -->
<script src="{{ asset('front/plugins/slick/slick.js') }}"></script>

<!-- Feature JS -->
<script src="{{ asset('front/plugins/feather/feather.min.js') }}"></script>

<!-- Sticky Sidebar JS -->
<script src="{{ asset('front/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
<script src="{{ asset('front/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>

<!-- Chart JS -->
<script src="{{ asset('front/plugins/apexchart/apexcharts.min.js') }}"></script>
<script src="{{ asset('front/plugins/apexchart/chart-data.js') }}"></script>

<!-- Progress JS -->
<script src="{{ asset('front/js/circle-progress.min.js') }}"></script>

<!-- Dropzone JS -->
<script src="{{ asset('front/plugins/dropzone/dropzone.min.js') }}"></script>

<!-- Validation-->
<script src="{{ asset('front/js/validation.js') }}"></script>

<!-- Aos -->
<script src="{{ asset('front/plugins/aos/aos.js') }}"></script>

<script src='{{ asset('front/plugins/summernote/summernote-bs4.min.js') }}'></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script> -->
<!-- Custom JS -->
<script src="{{ asset('front/js/custom.js') }}?var={{ time() }}"></script>
<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            // pageLanguage: 'en',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            includedLanguages: "en,hi"
        }, 'google_translate_element');
    }

    function getQueryParams() {
        var params = {};
        var queryString = window.location.search.substring(1);
        var queries = queryString.split("&");
        for (var i = 0; i < queries.length; i++) {
            var pair = queries[i].split("=");
            params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
        }
        return params;
    }


    var params = getQueryParams();

    if (params['isWebside'] !== '1') {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        document.head.appendChild(script);
    }
</script>







{{-- <script type="text/javascript" src="{{ asset('front/js/googletranslate.js') }}"></script> --}}
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>

@if (!Auth::guard('learner')->user())
    <script>
        var time = '';
        $(document).on('click', '.mobileLogin', function(event) {



            var step = 1;
            time = $("#timerSpan").html();
            if (time && time != '00 : 00') {
                step = 2;
            } else {
                step = 1;
            }

            // console.log(step);
            // return false;
            if (step == 1) {
                $('#secondStep').hide();
                $('#mobileLoginModal').on('shown.bs.modal', function() {
                    $('.mobile_auto').focus();
                });
                $('#firstStep').show();
                if ($('#mobileLoginModal')) {

                    $("label.error").hide();
                    $('#mobileError').hide();
                    $('#mobileLoginModal').modal('show');
                    $('#mobileLoginModal form')[0].reset();
                    getBrowserId();
                }
            }
            if (step == 2) {

                $("label.error").hide();
                $('#secondStep').show();
                $('#firstStep').hide();
                $('#mobileLoginModal').modal('show');
                timerbase();
            }
            //$($('#mobileLogin').data("target")).modal("show");



        });
    </script>
    <script>
        $(document).on("click", ".wallet", function() {
            alert()
        })


        function getBrowserId() {
            const userAgent = navigator.userAgent;
            const browserId = btoa(userAgent);
            $("#deviceId").val(browserId);
            //  return browserId;
        }

        $(document).ready(function() {


            $('#mobileLoginModal .modalClose1').removeClass('loginpopupclose');

            $(".countryFlagLi").on("click", ".init", function() {
                $(this).closest(".countryFlagLi").children('li:not(.init)').toggle();

            });
            var allOptions = $(".countryFlagLi").children('li:not(.init)');
            $(".countryFlagLi").on("click", "li:not(.init)", function() {
                allOptions.removeClass('selected');
                $(this).addClass('selected');
                $(".countryFlagLi").children('.init').html($(this).html());
                $(".countryFlagSelect ul li.init input").prop("checked", true);
                $("#country_id").val($(".countryFlagSelect ul li.init input").val())

                allOptions.toggle();
            });
        });

        $(document).on('click', '.loginpopupclose', function() {
            location.reload();
        });

        $(document).ready(function() {




            $('.modalClose').on('click', function() {
                $($('.modalClose').data("id")).modal('hide');
            });
            $('.modalClose1').on('click', function() {
                $($('.modalClose1').data("id")).modal('hide');
                $($('.modalClose').data("id")).modal('hide');
                location.reload();
            });
            $('#back').on('click', function() {
                $('#secondStep').hide();
                $('#firstStep').show();
                //$('#tokenLoginModal').modal('hide');
                //$('#mobileLoginModal').modal('show');
            });

            $('.modalCloseProfile').on('click', function() {
                location.reload();
            });


            // only one country code select at once
            // var CountryCodes = document.getElementById("country_select");
            // var chks = CountryCodes.getElementsByTagName("INPUT");
            // for (var i = 0; i < chks.length; i++) {
            //     chks[i].onclick = function() {
            //         for (var i = 0; i < chks.length; i++) {
            //             if (chks[i] != this && this.checked) {
            //                 chks[i].checked = false;
            //             }
            //         }
            //     };
            // } // on load of your dialog:

            $("#mobile").keyup(function() {
                $("#mobileError").hide();

                if ($(this).val() === "") {
                    $("#error-msg").hide();
                }

            })

            $(document).on("click", "#generateOtpBtn", function() {
                if ($(".mobile").val() == "") {
                    $("#error-msg").html("Please enter a mobile number").show();
                    return false
                } else {
                    $("#error-msg").hide()
                }
            })
            $(document).on("click", "#smsotpresend", function() {
                $('#is_resend_whatsapp').val(0);
            });
            $(document).on("click", "#whatsapp_resend_trigger", function() {
                $("#is_resend_whatsapp").val("1");
                $("#resendotpForm").submit();
            });

            $("#mobileLoginForm").validate({
                rules: {
                    mobile: {
                        required: true,
                        number: true
                    },
                    country_id: {
                        required: true
                    },


                },

                messages: {
                    mobile: {
                        required: " Please enter a Mobile Number",
                        // minlength: " Your Mobile Number must be consist of at least 10 Digits",
                        // maxlength: " Your Mobile Number must be consist of at max 10 Digits",
                        number: " Please enter only digit"
                    }
                },

                submitHandler: function(form) {

                    // let mobileInput = $('input[name="mobile"]').val();
                    let mobileInput = $('.mobile').val();
                    mobileInput = mobileInput.replace(/\s+/g, '');
                    // $('input[name="mobile"]').val(mobileInput);
                    $('.mobile').val(mobileInput);


                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {

                            $('#loader_section').hide();
                            if (response) {

                                if (response.error) {
                                    $('#mobileError').show();
                                    $('#mobileError').html(response.error);
                                }
                                if (response.success) {
                                    //$('#tokenLoginModal').modal("show");
                                    $('#passwordError').hide();
                                    $('#secondStep form')[0].reset();
                                    $('#secondStep').show();

                                    $('.password_auto').focus();

                                    $('#firstStep').hide();
                                    $('#otperror').hide();
                                    $('.reset-otp_section').hide();
                                    // $('#otpmessage').text(response.success);
                                    $('#expire_at').val(response.expire_at);
                                    $('#mobilenumbermsg').text("OTP sent to " + response.mobile
                                        .replace(/.(?=.{4})/g, '*'));
                                    $('.mobile').val(response.mobile);
                                    $('.country_id').val(response.country_id);
                                    // $('#otpmessage').show();
                                    $("#mobileError").hide()
                                    timerbase();
                                }
                            }
                        },
                        error: function(response) {
                            alert("Something went wrong Please try again");
                        }
                    });
                }
            });

            $("#password").keyup(function() {
                if ($(this).val() == "") {
                    $("#passwordError").hide();
                }
            });

            $("#otploginForm").validate({
                rules: {
                    mobile: {
                        required: true,
                        number: true,
                        minlength: 10,
                        maxlength: 10
                    },
                    password: {
                        required: true,
                        number: true
                    },
                    state_id: {
                        required: true
                    }
                },
                messages: {
                    mobile: {
                        required: " Please enter a Mobile Number",
                        minlength: " Your Mobile Number must be consist of at least 10 Digits",
                        maxlength: " Your Mobile Number must be consist of at max 10 Digits",
                        number: " Please enter only digit"
                    }
                },
                submitHandler: function(form) {
                    $('#passwordError').hide();

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response) {
                                $(".server_error_1").hide();
                                if (response.success == 1) {
                                    location.reload();
                                } else if (response.success == 0) {
                                    $('#firstStep').hide();
                                    $('#secondStep').hide();
                                    //    $('#thirdStep').show();
                                    $('#mobileLoginModal').hide();
                                    $("#mobileLoginModal").find('button:first').css(
                                        'display', 'none');
                                    $('#mobileLoginModal .modalClose1').addClass(
                                        'loginpopupclose');
                                    location.reload();
                                    $(".country-dropdown").val(response.country_id)
                                .change();
                                } else if (response.success) {
                                    $('.reset-otp_section').hide();
                                    $('#otperror').hide();
                                    // $('#otpmessage').text(response.success);
                                    // $('#otpmessage').show();
                                } else if (response.error) {
                                    $('#passwordError').show();
                                    $('#passwordError').text(response.error);
                                }

                            }
                        },
                        error: function(response) {
                            let errors = response.error;
                            $.each(errors, function(key, val) {
                                $(".server_error_1").show();
                                $('.server_error_1').append(
                                    '<div class="alert alert-danger errorDiv" role="alert">' +
                                    val[0] + '</div>');
                            });
                        }
                    });
                }
            });

            $("#resendotpForm").validate({
                rules: {
                    mobile: {
                        required: true,
                        number: true,
                        minlength: 10,
                        maxlength: 10
                    }
                },
                messages: {
                    mobile: {
                        required: " Please enter a Mobile Number",
                        minlength: " Your Mobile Number must be consist of at least 10 Digits",
                        maxlength: " Your Mobile Number must be consist of at max 10 Digits",
                        number: " Please enter only digit"
                    }
                },
                submitHandler: function(form) {
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        beforeSend: function() {
                            $('#loader_section').show();

                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            $(".server_error").hide();
                            if (response) {
                                if (response.error) {
                                    $('#passwordError').text(response.error);
                                } else if (response.success == 1) {
                                    location.reload();
                                } else if (response.success == 0) {
                                    $('#firstStep').hide();
                                    $('#secondStep').hide();
                                    $('#thirdStep').hide();
                                    // $('#thirdStep').show();
                                    $("#mobileLoginModal").find('button:first').css(
                                        'display', 'none');
                                    $('#mobileLoginModal .modalClose1').addClass(
                                        'loginpopupclose');
                                    $(".country-dropdown").val(response.country_id)
                                .change();
                                } else if (response.success) {
                                    $('#secondStep').show();
                                    //$('#tokenLoginModal').modal("show");
                                    $('.reset-otp_section').hide();
                                    $('#otperror').hide();
                                    $('#expire_at').val(response.expire_at);
                                    $('.mobile').val(response.mobile);
                                    $('.country_id').val(response.country_id);
                                    $('#mobilenumbermsg').text("OTP sent to " + response.mobile
                                        .replace(/.(?=.{4})/g, '*'));
                                    // $('#otpmessage').text(response.success);
                                    timerbase();
                                    // $('#otpmessage').show();

                                }
                            }
                        },
                        error: function(response) {
                            let errors = response.error;
                            $.each(errors, function(key, val) {
                                $(".server_error").show();
                                $('.server_error').append(
                                    '<div class="alert alert-danger errorDiv" role="alert">' +
                                    val[0] + '</div>');
                            });
                        }
                    });
                }
            });
            $("#profileForm").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    name: {
                        required: true,
                        lettersonly: true
                    },
                    // gender: {
                    //     required: true,
                    // },
                    state_id: {
                        required: {
                            depends: function(element) {
                                return $(".country-dropdown").val()
                            }
                        }
                    }
                },
                messages: {
                    email: {
                        required: "This field is Required.",
                        email: "Please enter a valid email address."
                    },
                    name: {
                        required: "This field is Required.",
                    },
                    // gender: {
                    //     required: "This field is Required.",
                    // },
                    // state_id: {
                    //     required: "This field is Required.",
                    // },
                },
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "gender") {
                        error.insertAfter("#genderLabel");
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {
                    alert("|");
                    return false
                    $('.errorDiv').remove();
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            // alert()
                            $('#loader_section').hide();
                            if (response.success == 1) {

                                location.reload();
                            }
                            if (response.error) {
                                $("#profileError").show();
                                let errors = response.error;
                                if (Array.isArray(errors) || errors.length > 0) {
                                    $.each(errors, function(key, val) {
                                        $("#profileError").show();
                                        $('#profileError').append(
                                            '<div class="alert alert-danger errorDiv" role="alert">' +
                                            val[0] + '</div>');
                                    });
                                } else {
                                    $("#profileError").show();
                                    $('#profileError').append(
                                        '<div class="alert alert-danger errorDiv" role="alert">' +
                                        response.error + '</div>');
                                }
                            }
                        },
                        error: function(response) {
                            let errors = response.error;
                            $.each(errors, function(key, val) {
                                $("#profileError").show();
                                $('#profileError').append(
                                    '<div class="alert alert-danger errorDiv" role="alert">' +
                                    val[0] + '</div>');
                            });
                        }
                    });
                }
            });
            $.validator.addMethod('lettersonly', function(value, element) {
                return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
            }, 'The name format is invalid.');

        });
        let timerOnBase = true;

        function timerbase() {
            $(".timerdiv").show();
            $("#otperror").hide();
            // $('#otpmessage').show();
            dt1 = new Date();
            dt2 = new Date($("#expire_at").val());
            var diff = (dt2.getTime() - dt1.getTime()) / 1000;
            var remaining = Math.abs(Math.round(diff));
            if (remaining > 0 && dt2.getTime() >= dt1.getTime()) {
                var m = Math.floor(remaining / 60);
                var s = remaining % 60;

                m = m < 10 ? '0' + m : m;
                s = s < 10 ? '0' + s : s;
                if (document.getElementById('timerSpan')) {
                    document.getElementById('timerSpan').innerHTML = m + ':' + s;
                }
                remaining -= 1;
                if (remaining >= 0 && timerOnBase) {
                    setTimeout(function() {
                        timerbase(remaining);
                    }, 1000);
                    return;
                }
                var reset = '<?php session()->put('showBtnflag', 0); ?>';
            } else {
                if (document.getElementById('timerSpan')) {
                    document.getElementById('timerSpan').innerHTML = '00 : 00';
                }
                $(".timerdiv").hide();
                // $('#otpmessage').hide();
                $("#otperror").show();
                $(".reset-otp_section").show();
                var reset = '<?php session()->put('showBtnflag', 1); ?>';
                return;
            }
        }
    </script>
@endif
<script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js"></script>

<script>



    // document.addEventListener("DOMContentLoaded", function() {
    //     const input = document.querySelector(".mobile");
    //     const errorMsg = document.querySelector("#error-msg");
    //     const hiddenCountryId = document.querySelector("#country_id");
    //     const submitBtn = document.querySelector("#generateOtpBtn");
    //     const mobileNumberDisplay = document.querySelector("#mobile-number");


    //     var countryData = window.intlTelInput.getCountryData();

    //     var uniqueCountryData = [];
    //     var dialCodeSet = new Set();

    //     countryData.forEach(function(country) {
    //         if (!dialCodeSet.has(country.dialCode)) {
    //             uniqueCountryData.push(country); // Keep the first country with this dial code
    //             dialCodeSet.add(country.dialCode); // Mark this dial code as added
    //         }
    //     });


    //     const iti = window.intlTelInput(input, {
    //         initialCountry: "in",
    //         autoPlaceholder: "polite",

    //         nationalMode: false,
    //         separateDialCode: true,
    //         onlyCountries: uniqueCountryData.map(country => country.iso2),
    //         utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
    //         customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
    //             return "Enter Your Mobile Number";
    //         },
    //         i18n: {
    //             searchPlaceholder:"Search dial code",
    //         }
    //     });


    //     input.addEventListener('keyup', function() {

    //         input.value = input.value.replace(/\D/g, '');

    //         if (input.value.trim()) {
    //             if (iti.isValidNumber()) {
    //                 errorMsg.style.display = 'none';
    //                 submitBtn.disabled = false;


    //                 const countryData = iti.getSelectedCountryData();

    //                 const countryCode = countryData.dialCode;
    //                 // console.log(countryData);
    //                 // const countryCode = countryData.iso2;
    //                 const mobileNumber = iti.getNumber();
    //                 hiddenCountryId.value = countryCode;
    //                 // hiddenMobile.value = mobileNumber.replace(`+${countryCode}`, '').replace(/\s+/g, '');
    //             } else {
    //                 errorMsg.style.display = 'block';
    //                 submitBtn.disabled = true;
    //                 $("#error-msg").html("Please enter a valid mobile number").show();
    //                 // countryCodeDisplay.textContent = '';
    //                 // mobileNumberDisplay.textContent = '';
    //             }
    //         } else {
    //             errorMsg.style.display = 'none';
    //             submitBtn.disabled = false;
    //             submitBtn.disabled = true;
    //             $("#error-msg").hide();
    //         }
    //     });

    //     input.addEventListener('countrychange', function() {
    //         input.value = '';
    //         hiddenCountryId.value = '';
    //         $("#error-msg").hide();
    //         $(".iti__flag").css("display","none")

    //     });
    // });
</script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(".country-dropdown").on("change", function() {
        var country_id = this.value;
        $(".state-dropdown").html("");
        $.ajax({
            url: "{{ url('get-states-by-country') }}",
            type: "POST",
            data: {
                country_id: country_id,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            beforeSend: function() {
                $('#loader_section').show();
            },
            success: function(result) {
                $('#loader_section').hide();
                $(".state-dropdown").append(
                    '<option value="">Select State</option>'
                );

                $.each(result.states, function(key, value) {
                    $(".state-dropdown").append(
                        '<option value="' +
                        value.id +
                        '">' +
                        value.name +
                        "</option>"
                    );
                });
                $(".city-dropdown").html(
                    '<option value="">Select State First</option>'
                );
            },
        });
    });
    $(".state-dropdown").on("change", function() {
        var state_id = this.value;
        $(".city-dropdown").html("");
        $.ajax({
            url: "{{ url('get-cities-by-state') }}",
            type: "POST",
            data: {
                state_id: state_id,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            beforeSend: function() {
                $('#loader_section').show();
            },
            success: function(result) {
                $('#loader_section').hide();
                $(".city-dropdown").append(
                    '<option value="">Select City</option>'
                );
                $.each(result.cities, function(key, value) {
                    $(".city-dropdown").append(
                        '<option value="' +
                        value.id +
                        '">' +
                        value.name +
                        "</option>"
                    );
                });
            },
        });
    });
    $(document).on("click", ".wishlist", function(e) {
        var loggedin = "false";
        // var element = document.getElementsByClassName("data-wishlist_active");
        // const el = document.querySelector('.wishlist');
        // console.log(el);
        loggedin = "{{ Auth::guard('learner')->check() }}";
        if (loggedin == "false" || loggedin == false) {
            $('.wishlist i').removeClass("color-active");


            // $(this).removeClass('active');
            $(".header-sign").trigger('click');
        } else {

            var course_id = $(this).data('course_id');
            $("#course_id_frm").val(course_id);

            var userid = $("#user_id").val();

            var active = $(this).data('wishlist_active');
            $("#wishlist_active_frm").val(active);

            var wishlist_id = $(this).data('wishlist_id');
            $("#wishlist_id_frm").val(wishlist_id);

            if (course_id && userid) {
                $("#wishlist_btn").trigger('click');
            }
        }
    });

    $("form#wishlist_form").submit(function(event) {
        event.preventDefault();
        var dataString = $("#wishlist_form").serialize();
        $.ajax({
            type: "POST",
            url: "{{ url('wishlist') }}",
            data: dataString,
            success: function(data) {
                //  alert('success');
                //  console.log(data.data);
                if (data.status == "success") {
                    Swal.fire({
                        icon: data.status,
                        title: data.title,
                        text: data.msg,
                    }).then(function() {
                        location.reload();
                    });

                }




            }
        });
    });
</script>
<script>
    @if (session('notification') &&
            is_array(session('notification')) &&
            count(session('notification')) > 0 &&
            session('notification')['type'] == 'sweet-alert')
        Swal.fire({
            icon: "{{ session('notification')['status'] }}",
            title: "{{ session('notification')['title'] }}",
            text: "{{ session('notification')['msg'] }}",
            @if (isset(session('notification')['denyButtonText']) && session('notification')['status'] == 'success')
                showDenyButton: true,
                denyButtonText: "{{ session('notification')['denyButtonText'] }}",
            @endif
        }).then((result) => {
            if (result.isConfirmed) {
                // Handle confirm button action
            } else if (result.isDenied) {
                var currentUrl = window.location.href;
                var baseUrl = currentUrl.split('/')[0];
                var newUrl = baseUrl + '/contact-us-new';
                window.location.href = newUrl;
            }
        });
    @endif
</script>


<!-- gdpr cookie -->

<script>
    $(document).ready(function() {
        var country_id = "{{ $country_id }}"
        $(".country-dropdown").val(country_id.replace(/"|'/g, '')).change();
        var chekck_email = "{{ $email }}";
        var name = "{{ $name }}";
        var country_id = "{{ $country_id }}";
        var state_id = "{{ $state_id }}";
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('is_dev') === '1') {
            $("#myModal").modal("hide");
        } else {
            if (chekck_email == "null" || name == "null" || country_id == "null" || state_id == "null") {
                $("#myModal").modal("show");
            } else {
                $("#myModal").modal("hide");
            }
        }

        $("#profileForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                name: {
                    required: true,
                    lettersonly: true
                },
                // gender: {
                //     required: true,
                // },
                state_id: {
                    required: {
                        depends: function(element) {
                            return $(".country-dropdown").val()
                        }
                    }
                },
                country_id: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "This field is Required.",
                    email: "Please enter a valid email address."
                },
                name: {
                    required: "This field is Required.",
                },
                // gender: {
                //     required: "This field is Required.",
                // },
                // state_id: {
                //     required: "This field is Required.",
                // },
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "gender") {
                    error.insertAfter("#genderLabel");
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                $('.errorDiv').remove();
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response) {
                        // alert()
                        $('#loader_section').hide();
                        if (response.success == 1) {

                            location.reload();
                        }
                        if (response.error) {
                            $("#profileError").show();
                            let errors = response.error;
                            if (Array.isArray(errors) || errors.length > 0) {
                                $.each(errors, function(key, val) {
                                    $("#profileError").show();
                                    $('#profileError').append(
                                        '<div class="alert alert-danger errorDiv" role="alert">' +
                                        val[0] + '</div>');
                                });
                            } else {
                                $("#profileError").show();
                                $('#profileError').append(
                                    '<div class="alert alert-danger errorDiv" role="alert">' +
                                    response.error + '</div>');
                            }
                        }
                    },
                    error: function(response) {
                        let errors = response.error;
                        $.each(errors, function(key, val) {
                            $("#profileError").show();
                            $('#profileError').append(
                                '<div class="alert alert-danger errorDiv" role="alert">' +
                                val[0] + '</div>');
                        });
                    }
                });
            }
        });
        $.validator.addMethod('lettersonly', function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, 'The name format is invalid.');


    })
</script>
<div id="app"></div>
<script src="{{ asset('front/js/gdpr_cookie/orejime.js') }}"></script>
<script>
    // see the annotated config in the README for details on how everything works
    window.orejimeConfig = {
        appElement: "#app",
        privacyPolicy: "#privacyPolicy",
        translations: {
            en: {
                consentModal: {
                    description: "Here is an example of Orejime. View the source code to see how everything is done.",
                },
                "inline-tracker": {
                    description: "Example of an inline tracking script that sets a dummy cookie",
                },
                "external-tracker": {
                    description: "Example of an external tracking script that sets a dummy cookie",
                },
                "always-on": {
                    description: "This <a href=\"http://example.com\">example</a> app will not set any cookie",
                },
                "disabled-by-default": {
                    description: "This example app will not set any cookie",
                },
                purposes: {
                    analytics: "Analytics",
                    security: "Security",
                    ads: "Ads"
                },
                categories: {
                    example: {
                        description: 'Applications can be grouped into categories to help users better understand their use.'
                    },
                    "third-party": {
                        description: 'These are fake third-party applications.'
                    }
                }
            },
        },
        apps: [{
                name: "inline-tracker",
                title: "Inline Tracker",
                purposes: ["analytics"],
                cookies: ["inline-tracker"]
            },
            {
                name: "external-tracker",
                title: "External Tracker",
                purposes: ["analytics", "security"],
                cookies: ["external-tracker"],
            },
            {
                name: "disabled-by-default",
                title: "Something disabled by default",
                purposes: ["ads"],
                default: false
            },
            {
                name: "always-on",
                title: "Required app",
                purposes: [],
                required: true
            }
        ],
        // categories: [{
        //         name: 'example',
        //         title: 'Example category',
        //         apps: ['inline-tracker', 'disabled-by-default']
        //     },
        //     {
        //         name: 'third-party',
        //         title: 'Third-party applications',
        //         apps: ['external-tracker', 'always-on']
        //     }
        // ]
    }
</script>

<!-- since there is a orejimeConfig global variable in index.js, a window.orejime instance will be initiated when including the lib -->
<script>
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
</script>

<!-- <script type="opt-in" data-src="{{asset('front/js/gdpr_cookie/external-tracker.js')}}" data-type="application/javascript" data-name="external-tracker"></script>

<script type="opt-in" data-type="application/javascript" data-name="inline-tracker">
    console.log("This is an example of an inline tracking script.")
			setCookie("inline-tracker", "foo", 120)
		</script> -->

<script>
    // since there is a orejimeConfig global variable in index.js,
    // a window.orejime instance was created when including the lib
    if (document.querySelector('.consent-modal-button')) {
        document.querySelector('.consent-modal-button').addEventListener('click', function() {
            orejime.show();
        }, false);
    }

    if (document.querySelector('.reset-button')) {
        document.querySelector('.reset-button').addEventListener('click', function() {
            orejime.internals.manager.resetConsent();
            location.reload();
        }, false);
    }

    $(document).ready(function() {
        $('.summernote-editor').summernote({
            toolbar: [
                // ['style', ['style']],
                ['font', ['bold', 'underline']],
                ['insert', ['link']],
                // ['view', ['fullscreen', 'codeview', 'undo', 'redo','help']],
            ],
            minHeight: 200,
        });

        $(document).on('click', 'button.close', function() {
            location.reload();
        });

        $(document).on('click', 'button.swal2-deny', function() {
            alert("New Ticket button clicked");
        });

        $(document).on('click', '.note-btn', function() {
            $(".custom-checkbox").css("display", "none");
        });


    });
</script>

@if (isset($ratingjs) && $ratingjs == true)
    <script src="{{ asset('front/js/rating.js') }}"></script>
@endif
@yield('js')
