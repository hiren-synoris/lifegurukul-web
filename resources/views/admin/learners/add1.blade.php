@extends('admin.layouts.app')


@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'dropzoneCSS' => 1,
        'select2CSS' => 1,
    ])


    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            color: #000000 !important;
        }

        .iti {
            width: 100%
        }

        .iti__flag-container,
        .iti__selected-flag {
            display: none !important;
        }

        .iti__country-name {
            display: none !important;
        }

        .iti__flag-box {
            display: none !important;
        }

        .iti__in {
            display: none !important;
        }

        .iti__flag .iti__at {
            display: none !important;
        }
    </style>
@endsection
@section('right-section')
    {!! redirect_to_back(route('learners.index')) !!}
@endsection
@section('content')
    @php
        $countriesCollection = Helper::getCountries();
    @endphp

    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" id="form_submit" action="{{ url('backoffice/learners/') }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    <h3 class="m-0">
                        {{-- @if (isset($pg_header))
                            {{ ucwords($pg_header) }}
                        @endif --}}
                    </h3>
                </div>
                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}
                {{-- @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control col-md-12 col-sm-12" name="name" id="name"
                        value="{{ old('name') }}">
                    @error('name')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    {{-- <label for="mobile">Mobile</label><span style="color: red">*</span>
                    <div class="row contry p-0 m-0">
                        <div class="d-flex countryFlagSelect">
                            <div id="country_select">
                                <ul class="countryFlagLi">
                                    @if (isset($countriesCollection) && !empty($countriesCollection))
                                        @foreach ($countriesCollection as $country)

                                            @if ($country->id == 1)
                                                <li class="{{ $country->id == 1 ? 'init' : '' }}">
                                                    <input id="checkbox_{{ $country->id }}"
                                                        {{ $country->id == 1 ? 'checked' : '' }} name="country_id"
                                                        type="checkbox" value="{{ $country->id }}"
                                                        data-pincode="{{ $country->phonecode }}" />
                                                    <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                        <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                            alt="Flag">(+{{ $country->phonecode }})</label>
                                                </li>
                                                <li>
                                                    <input id="checkbox_{{ $country->id }}" data-pincode="{{ $country->phonecode }}" name="country_id"
                                                        type="checkbox" value="{{ $country->id }}" />
                                                    <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                        <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                            alt="Flag">(+{{ $country->phonecode }})</label>
                                                </li>
                                            @else
                                                <li>
                                                    <input id="checkbox_{{ $country->id }}" data-pincode="{{ $country->phonecode }}" name="country_id"
                                                        type="checkbox" value="{{ $country->id }}" />
                                                    <label for="checkbox_{{ $country->id }}" class="country_flag">
                                                        <img src="{{ URL::asset('/front/img/' . $country->flag) }}"
                                                            alt="Flag">(+{{ $country->phonecode }})</label>
                                                </li>
                                            @endif
                                        @endforeach
                                    @else
                                        <li></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <input type="text" class="form-control col-11 mobile " name="mobile"
                            value="{{ old('mobile') }}" id="mobile">
                        <label class="text-danger" id="mobileError"></label>
                        <label class="text-danger" id="error-msg"></label>

                    </div> --}}
                    <div class="form-group">
                        <label for="mobile">Mobile</label><span style="color: red">*</span>
                        <br>
                        <input type="text" class="form-control mobile w-100" value="{{ old('mobile') }}" name="mobile"
                            id="mobile" placeholder="Enter your mobile">

                        <div class="error-new text-danger" style="display: none" id="error-msgs">Please enter a valid mobile
                            number
                        </div>
                    </div>
                    @error('mobile')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="d_o_b">Date Of Birth</label>
                    <input type="text" class="form-control d_o_b" readonly name="d_o_b" value="{{ old('d_o_b') }}"
                        id="d_o_b">
                    @error('d_o_b')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="name">Email</label><span style="color: red">*</span>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" id="email">
                    @error('email')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <div class="form-check-inline">
                        <input class="form-check-input select-type" type="radio" name="gender" id="male"
                            value="{{ App\Models\Learner::MALE }}"
                            {{ old('gender') && old('gender') == App\Models\Learner::MALE ? 'checked' : '' }}>
                        <label class="form-check-label" for="male">{{ App\Models\Learner::MALE_LABEL }}</label>

                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input select-type" type="radio" name="gender" id="female"
                            value="{{ App\Models\Learner::FEMALE }}"
                            {{ old('gender') && old('gender') == App\Models\Learner::FEMALE ? 'checked' : '' }}>
                        <label class="form-check-label" for="female">{{ App\Models\Learner::FEMALE_LABEL }}</label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input select-type" type="radio" name="gender" id="other"
                            value="{{ App\Models\Learner::OTHER }}"
                            {{ old('gender') && old('gender') == App\Models\Learner::OTHER ? 'checked' : '' }}>
                        <label class="form-check-label" for="other">{{ App\Models\Learner::OTHER_LABEL }}</label>
                    </div>
                    @error('gender')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label><small class="text-gray">(jpg, svg, jpeg,
                        png)</small>
                    <label class="block form-control">
                        <span class="sr-only">Choose File</span>
                        <input type="file" name="profile_pic" id="profile_pic" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    </label>
                    @error('profile_pic')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group" style="display:nonef">
                    <label for="name">Country</label><span style="color: red">*</span>
                    <select class="form-control country-select" id="country-dropdown" name="country_id1">
                        <option value="">Select country</option>
                        @if (isset($countriesCollection) && !empty($countriesCollection))
                            @foreach ($countriesCollection as $country)
                                <option value="{{ $country->id }}"
                                    {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No Country Found</option>
                        @endif
                    </select>
                    @error('country_id1')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                @php
                    $states = Helper::getStates(old('country_id'));
                @endphp
                <div class="form-group">
                    <label for="state">State</label><span style="color: red">*</span>
                    <select class="form-control state-select select3" id="state-dropdown" name="state_id">
                        <option value="">Select State</option>
                        @if (isset($states) && !empty($states))
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected(old('state_id') == $state->id)>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No State Found</option>
                        @endif
                    </select>
                    @error('state_id')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                @php
                    $cities = Helper::getCities(old('state_id'));
                @endphp
                <div class="form-group">
                    <label for="city">City</label>
                    <select class="form-control city-select select3" id="city-dropdown" name="city_id">
                        <option value="">Select City</option>
                        @if (isset($cities) && !empty($cities))
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No City Found</option>
                        @endif
                    </select>
                    @error('city_id')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <select class="form-control occupation-select select3" id="occupation-dropdown" name="occupation">
                        <option value="">Select Occupation</option>
                        @if (isset($occupations) && !empty($occupations))
                            @foreach ($occupations as $occupation)
                                <option value="{{ $occupation->id }}" @selected(old('occupation_id') == $occupation->id)>
                                    {{ $occupation->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No Occupation Found</option>
                        @endif
                    </select>
                    @error('occupation')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="marital_status">Marital Status</label>
                    <select class="form-control marital_status-select select3" id="marital_status-dropdown"
                        name="marital_status">
                        <option value="">Select Marital Status</option>
                        @if (isset($marital_status) && !empty($marital_status))
                            @foreach ($marital_status as $marital_status)
                                <option value="{{ $marital_status->id }}" @selected(old('marital_status') == $marital_status->id)>
                                    {{ $marital_status->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No Marital Status Found</option>
                        @endif
                    </select>
                    @error('marital_status')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="education">Education</label>
                    <select class="form-control education-select select3" id="education-dropdown" name="education">
                        <option value="">Select Education</option>
                        @if (isset($educations) && !empty($educations))
                            @foreach ($educations as $education)
                                <option value="{{ $education->id }}" @selected(old('education') == $education->id)>
                                    {{ $education->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No Education Found</option>
                        @endif
                    </select>
                    @error('education')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="your_interests">Your Interests</label><br>
                    <select class="form-control your_interests-select select3" id="your_interests-dropdown"
                        name="your_interests[]" multiple>
                        {{-- <option value="">Select Your Interests</option> --}}
                        @if (isset($your_interests) && !empty($your_interests))
                            @foreach ($your_interests as $your_interests)
                                <option value="{{ $your_interests->id }}" @selected(old('your_interests') == $your_interests->id)>
                                    {{ $your_interests->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="">No Your Interests Found</option>
                        @endif
                    </select>
                    @error('your_interests')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit"
                        class="btn btn-inline-block submit-btn btn-primary hide_show_btn">Submit</button>
                </div>
            </form>
        </div>
    </div>



@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'select2' => 1,
        'dropzone' => 1,
    ])

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.3.0/build/css/intlTelInput.css">
    <script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>

    <script src="{{ asset('admin/plugins/select2/js/select2.min.js') }}"></script>
    <script>
        // $(function() {
        // var nextDayDate = new Date();
        // nextDayDate.setDate(nextDayDate.getDate() - 1);
        // console.log(nextDayDate);
        // $(".d_o_b").datepicker({
        //     autoclose: true,
        //     format: 'dd/mm/yyyy',
        //     endDate: nextDayDate,
        // });

        // });

        document.addEventListener("DOMContentLoaded", function() {

            const input = document.querySelector(".mobile");
            const errorMsg = document.querySelector("#error-msgs");
            const hiddenCountryId = document.querySelector("#country_id");
            const submitBtn = document.querySelector("#generateOtpBtn");
            const mobileNumberDisplay = document.querySelector("#mobile-number");


            var countryData = window.intlTelInput.getCountryData();

            var uniqueCountryData = [];
            var dialCodeSet = new Set();

            countryData.forEach(function(country) {
                if (!dialCodeSet.has(country.dialCode)) {
                    uniqueCountryData.push(country);
                    dialCodeSet.add(country.dialCode);
                }
            });

            const iti = window.intlTelInput(input, {
                initialCountry: "IN",
                autoPlaceholder: "polite",
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js",
                nationalMode: false,
                formatOnDisplay: false,
                separateDialCode: true,
                onlyCountries: uniqueCountryData.map(country => country.iso2),
                customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
                    return "Enter Your Mobile Number";
                },
                i18n: {
                    searchPlaceholder: "Search dial code",
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
    </script>





    <script>
        // max_upload_size();
        var flag
        $("#profile_spic").change(function() {

            var validExtensions = ["jpg", "svg", "jpeg", "png"]
            var file = $(this).val().split('.').pop();
            if (validExtensions.indexOf(file) == -1) {
                $("#image_error").show()
                $("#image_error").html("Only formats are allowed : " + validExtensions.join(', '));
                flag = false
            } else {
                flag = true
                $("#image_error").hide()
                $(".iti__flag").css("display", "none")
            }

        });


        var number = '+' + $("input:checkbox:checked").attr("data-code")


        // $("#form_submit").on('submit',(function(e) {
        //        // alert(flag)
        //         return flag
        //         $("#image_error").hide()

        //     }))
        // max_upload_size();

        // $(function() {
        //     $('.d_o_b').datepicker({
        //         dateFormat: 'dd/mm/yy',
        //         maxDate: today.getDate() - 11 ,
        //         changeMonth: true,
        //         changeYear: true
        //     });
        // });


        $(document).ready(function() {




            var country_code = '';
            var country_id = '1';

            $('.mobile').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });

            function isValidNumber(number) {
                return new libphonenumber.parsePhoneNumber(number).isValid()
            }

            // default 1 changed states
            $("#country-dropdown").val(1).change();
            $(".countryFlagLi").on("click", ".init", function() {
                $(this).closest(".countryFlagLi").children('li:not(.init)').toggle();
            });
            var allOptions = $(".countryFlagLi").children('li:not(.init)');
            $(".countryFlagLi").on("click", "li:not(.init)", function() {

                allOptions.removeClass('selected');
                $(this).addClass('selected');
                $(".countryFlagLi").children('.init').html($(this).html());
                // chceked flag checkbox
                $(".countryFlagSelect ul li.init input").prop("checked", true);
                // trigger for selected country
                $("#country-dropdown").val($(".countryFlagSelect ul li.init input").val()).change();

                country_id = $(".countryFlagSelect ul li.init input").val()
                // alert(country_id)
                getState(country_id)
                country_code = $(".countryFlagSelect ul li.init input").data("pincode")
                allOptions.toggle();
                var number = '+' + country_code + $(".mobile").val();

                // console.log(  $(".countryFlagLi").children('.init').html($(this).html()));

                // $("#country-dropdown option").filter(function(k,v){
                //     return $(v).val() ==  $("#country-dropdown").val($(".countryFlagSelect ul li.init input").val()).change();
                // }).prop("selected",true)


                if (!isValidNumber(number)) {
                    $("#mobileError").show()
                    // $("#mobileError").html("Please enter valid mobile number")
                    // $(".hide_show_btn").attr("disabled","disabled")
                    return false
                } else {
                    $("#mobileError").hide()
                    $(".hide_show_btn").removeAttr("disabled")
                }

            });

            country_code = $(".countryFlagSelect ul li.init input").data("pincode")

            $(".mobile").change(function() {

                var number = '+' + country_code + ($(this).val());

                if (!isValidNumber(number)) {
                    $("#mobileError").show()
                    // $("#mobileError").html("Please enter valid mobile number data")

                    // $(".hide_show_btn").attr("disabled","disabled")
                    return false
                } else {
                    $("#mobileError").hide()
                    $(".hide_show_btn").removeAttr("disabled")
                }
            })



            // var TodayDate = new Date();
            // var endDate= new Date(Date.parse($("#d_o_b").val()));

            // if (endDate > TodayDate) {
            // console.log('greate');
            // }else{
            //     console.log('less');
            // }
            // $('#d_o_b').val()
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
            // }


            getState(country_id)

            function getState(country_id) {

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
                        $("#state-dropdown").empty();
                        $("#state-dropdown").append('<option value="">Select State</option>');
                        $.each(result.states, function(key, value) {
                            var option = '<option value="' + value.id + '"';
                            option += (value.id == "{{ old('state_id') }}") ? ' selected' : '';
                            option += '>' + value.name + '</option>';
                            $("#state-dropdown").append(option);
                        });
                    },
                });
            }

        });


        $("#country-dropdown").on("change", function() {
            // alert()
            var country_id = this.value;
            var curentValue = $("#city_id").val();
            // var preValue = curentValue;

            $("#state-dropdown").html('<option value="">Select State</option>');
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
                    $.each(result.states, function(key, value) {
                        var option = '<option value="' + value.id + '"';
                        option += (value.id == "{{ old('state_id') }}") ? ' selected' : '';
                        option += '>' + value.name + '</option>';
                        $("#state-dropdown").append(option);
                    });
                    // $("#city-dropdown").html(
                    //     '<option value="">Select State First</option>'
                    // );
                },
            });
        });
        $("#state-dropdown").on("change", function() {
            var state_id = this.value;
            $("#city-dropdown").html('<option value="">Select city</option>');
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
                    $.each(result.cities, function(key, value) {
                        $("#city-dropdown").append(
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
        // max_upload_size();
        $('.select3').select2();
        $(document).ready(function() {})
    </script>
    <script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>
@endsection
