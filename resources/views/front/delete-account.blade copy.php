@extends('front.layout.mainlayout')
@section('content')
@php
$countriesCollection = Helper::getCountries();
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8" id="cardSection">
            <div class="card" style="padding-top: 10px;margin-top: 40px;">
                <div class="card-header">
                    <h3>{{ __('Delete Account') }}</h3>
                </div>

                <div class="card-body">
                    <form id="deleteAccountForm">
                        @csrf

                        <div class="form-group d-flex flex-wrap login-form">
                            <label for="mobile" class=" col-form-label text-md-right w-100">{{ __('Enter Mobile') }}</label>
                            <div class="d-flex countryFlagSelect">
                                <div id="country_select">
                                    <ul class="countryFlagLi">
                                        @if (isset($countriesCollection) && !empty($countriesCollection))
                                        @foreach ($countriesCollection as $country)
                                        @if ($country->id == 1)
                                        <li class="{{ $country->id == 1 ? 'init' : '' }}">
                                            <input id="checkbox_{{ $country->id }}" {{ $country->id == 1 ? 'checked' : '' }} data-code="{{ $country->phonecode }}" name="country_id" type="checkbox" value="{{ $country->id }}" />
                                            <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}" alt="Flag"></label>
                                        </li>
                                        <li>
                                            <input id="checkbox_{{ $country->id }}" name="country_id" type="checkbox" value="{{ $country->id }}" data-code="{{ $country->phonecode }}" />
                                            <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}" alt="Flag"></label>
                                        </li>
                                        @else
                                        <li>
                                            <input id="checkbox_{{ $country->id }}" name="country_id" type="checkbox" value="{{ $country->id }}" data-code="{{ $country->phonecode }}" class="country_flag123" />
                                            <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                <img src="{{ URL::asset('/front/img/' . $country->flag) }}" alt="Flag"></label>
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
                                <input id="mobiles" type="number" class="form-control mobiles w-100 mobile_autos" name="mobile" value="" autocomplete="mobile" placeholder="Enter Your Mobile Number">
                                <span class="invalid-feedback" style="display:block;" role="alert" id="mobileError">
                                    <strong></strong>
                                </span>
                            </div>
                            <input type="hidden" name="deviceId" value="" id="deviceId">
                        </div>
                        <div class="form-group otp-btn">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('submit') }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8 text-center" id="messageDisplay" style="display: none;">
            <div class="card" style="padding-top: 10px;margin-top: 40px;">
                <div class="card-body" id="msg"></div>
            </div>
            <!-- Message will be displayed here -->
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $("#deleteAccountForm").validate({
        rules: {
            mobile: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 10
            },
            country_id: {
                required: true
            },
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

            function isValidNumber(number) {
                return new libphonenumber.parsePhoneNumber(number).isValid()
            }

            var number = '+' + $("input:checkbox:checked").attr("data-code") + ($(".mobile_auto").val());

            var learner_number = $(".mobile_auto").val();

            if (!isValidNumber(number)) {
                $("#mobileError").show()
                $("#mobileError").html("Please enter valid mobile number")
                return false
            }
            $("#mobileError").hide();

            $.ajax({
                url:"{{ route('mail.delete.learner')}}",
                type:"get",
                data:{
                    learner_number:learner_number,
                },success:function(data){
                    displayMessage();
                }
            });

        }
    });

    function displayMessage() {
        var cardSection = document.getElementById("cardSection");
        var messageDisplay = document.getElementById("messageDisplay");
        var messageBody = document.getElementById("msg");
        var mobileInput = document.getElementById("mobile").value;

        // Hide card section
        cardSection.style.display = "none";

        // Display message section
        messageDisplay.style.display = "block";
        // Set message
        messageBody.innerHTML = "<h4 style='color:#52c95a;'>Your account will be removed in 7 working days for mobile number: " + mobileInput + "</h4>";
    }
</script>
@endsection
