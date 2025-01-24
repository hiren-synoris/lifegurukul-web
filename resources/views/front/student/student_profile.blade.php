@extends('front.layout.mainlayout')
@section('content')
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')

                @php
                    $singleCountry = auth()->guard('learner')->check()
                        ? Helper::getCountries(Auth::guard('learner')->user()->country_id)
                        : '';
                    $countriesCollection = Helper::getCountries();
                @endphp

                <!-- Profile Details -->
                <div class="col-xl-9 col-md-8">
                    <div class="settings-widget profile-details">
                        <div class="settings-menu p-0">
                            <div class="profile-heading">
                                <h3>Profile Details</h3>
                                <p>You have full control to manage your own account setting.</p>
                            </div>
                            <div class="course-group mb-0 d-flex">
                                <div class="course-group-img d-flex align-items-center">
                                    @php
                                        $imagepath = Helper::profileImage();
                                    @endphp
                                    <img src="{{ $imagepath }}" class="imagePreview" alt="" class="img-fluid">
                                    <div class="course-name">
                                        <h4><a href="javascript:void(0)" style="cursor:default;">Your avatar</a></h4>
                                        <p>PNG or JPG no bigger than 800px wide and tall.</p>
                                    </div>
                                </div>
                                <div class="profile-share d-flex align-items-center justify-content-center">
                                    <a class="inputWrapper" href="javascript:;"><input class="btn btn-success"
                                            type='file' data-url="{{ route('profile.picture.store') }}" id="imageUpload"
                                            accept=".png, .jpg, .jpeg" />Update</a>

                                    <form method="POST" action="{{ route('student.profile.delete') }}" id="profileForm"
                                        class="profileForm">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" class="btn btn-danger"
                                            onclick="openConfirmationModal()">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <div class="checkout-form personal-address add-course-info ">
                                <div class="personal-info-head">
                                    <h4>Personal Details</h4>
                                    <p>Edit your personal information and address.</p>
                                </div>

                                @includeIf('front.layout.errors.validation-failed')

                                <form method="POST" action="{{ route('student.profile.update') }}" id="updateProfileForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label class="form-control-label">Full Name <span
                                                        style="color: red">*</span></label>
                                                <input type="text" class="form-control"
                                                    placeholder="Enter your Full Name" name="name" id="name"
                                                    value="{{ isset(Auth::guard('learner')->user()->name) && !empty(Auth::guard('learner')->user()->name) ? Auth::guard('learner')->user()->name : old('name') }}">
                                            </div>
                                        </div>
                                        {{-- <div class="col-lg-6 contry-profile"> --}}
                                        <div class="col-lg-6">

                                            <div class="form-group">
                                                <label class="form-control-label w-100">Mobile <span
                                                        style="color: red;">*</span></label>
                                                <div class="mobile-number">
                                                    <input type="text" class="form-control" readonly
                                                        value="+{{ $singleCountry->phonecode }}" style="width: 72px;">
                                                    <input type="text" class="form-control"
                                                        placeholder="Enter your Phone" name="mobile" readonly
                                                        value=" {{ Auth::guard('learner')->user()->mobile ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="col-lg-3"> --}}
                                            {{-- <div class="form-group">
                                                <label class="form-label">Country <span style="color: red">*</span></label>
                                                <div id="country_select">
                                                    <ul>
                                                        @if (isset($singleCountry) && !empty($singleCountry))
                                                            <input name="country_id" id="country_id" type="hidden"
                                                                value="{{ Auth::guard('learner')->user()->country_id }}" />
                                                            <li
                                                                class="{{ Auth::guard('learner')->user()->country_id == $singleCountry->id ? 'active_country_id' : '' }}">
                                                                <label for="checkbox_{{ $singleCountry->id }}"
                                                                    class="country_flag">
                                                                    <img width="50px"
                                                                        src="{{ URL::asset('/front/img/' . $singleCountry->flag) }}"
                                                                        alt="Flag">
                                                                </label>
                                                            </li>
                                                        @else
                                                            <li>No Country Found</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div> --}}
                                            {{-- <div class="form-group">
                                                <label class="form-control-label"> <span
                                                        style="color: red"></span></label>
                                                <input type="text" class="form-control" placeholder="Enter your Phone"
                                                    name="mobile" readonly
                                                    value="+{{ $singleCountry->phonecode }} {{ Auth::guard('learner')->user()->mobile ?? '' }}">
                                            </div> --}}
                                        {{-- </div> --}}
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-control-label">Email <span
                                                        style="color: red">*</span></label>
                                                <input type="text" class="form-control" placeholder="Enter your Email"
                                                    name="email" id="email"
                                                    value="{{ Auth::guard('learner')->user()->email ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-control-label">Birthday</label>
                                                <input type="date" class="form-control" placeholder="Birth of Date"
                                                    max="{{ date('Y-m-d') }}" name="d_o_b" id="d_o_b"
                                                    value="{{ Auth::guard('learner')->user()->d_o_b ? \Carbon\Carbon::parse(Auth::guard('learner')->user()->d_o_b)->format('Y-m-d') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-2">
                                            <label for="city">Gender</label><br>
                                            <div class="form-check-inline">
                                                <input class="form-check-input select-type" type="radio" name="gender"
                                                    id="male" value="{{ App\Models\Learner::MALE }}"
                                                    {{ Auth::guard('learner')->user()->gender == App\Models\Learner::MALE || (old('gender') && old('gender') == App\Models\Learner::MALE) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="male">{{ App\Models\Learner::MALE_LABEL }}</label>
                                            </div>
                                            <div class="form-check-inline">
                                                <input class="form-check-input select-type" type="radio" name="gender"
                                                    id="female" value="{{ App\Models\Learner::FEMALE }}"
                                                    {{ Auth::guard('learner')->user()->gender == App\Models\Learner::FEMALE || (old('gender') && old('gender') == App\Models\Learner::FEMALE) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="female">{{ App\Models\Learner::FEMALE_LABEL }}</label>
                                            </div>
                                            <div class="form-check-inline">
                                                <input class="form-check-input select-type" type="radio" name="gender"
                                                    id="other" value="{{ App\Models\Learner::OTHER }}"
                                                    {{ Auth::guard('learner')->user()->gender == App\Models\Learner::OTHER || (old('gender') && old('gender') == App\Models\Learner::OTHER) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="other">{{ App\Models\Learner::OTHER_LABEL }}</label>
                                            </div>
                                        </div>
                                        <!-- <div class="col-lg-6" style="display:none">
                                                        <div class="form-group">
                                                            <label class="form-label">Country</label>
                                                            <select class="form-select select country-select" id="country-dropdown"
                                                                name="country_id">
                                                                <option value="">Select country</option>
                                                                @if (isset($countriesCollection) && !empty($countriesCollection))
    @foreach ($countriesCollection as $country)
    <option value="{{ $country->id }}"
                                                                            {{ Auth::guard('learner')->user()->country_id == $country->id ? 'selected="selected"' : '' }}>
                                                                            {{ $country->name }}
                                                                        </option>
    @endforeach
@else
    <option value="">No Country Found</option>
    @endif
                                                            </select>
                                                        </div>
                                                    </div> -->
                                        @php
                                            $states = Helper::getStates(Auth::guard('learner')->user()->country_id);
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="state">State <span style="color: red">*</span></label>
                                                <select class="form-select select state-select " id="state-dropdown"
                                                    name="state_id">
                                                    <option value="">Select State</option>
                                                    @if (isset($states) && !empty($states))
                                                        @foreach ($states as $state)
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
                                        </div>
                                        @php
                                            $cities = Helper::getCities(Auth::guard('learner')->user()->state_id);
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city">City</label>
                                                <select class="form-select select city-select" id="city-dropdown"
                                                    name="city_id">
                                                    <option value="">Select City</option>
                                                    @if (isset($cities) && !empty($cities) != null)
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                {{ Auth::guard('learner')->user()->city_id == $city->id ? 'selected="selected"' : '' }}>
                                                                {{ $city->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">No City Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="occupation">Occupation </label>
                                                <select class="form-select select occupation-select " id="occupation_id"
                                                    name="occupation">
                                                    <option value="">Select Occupation</option>
                                                    @if (isset($occupations) && !empty($occupations))
                                                        @foreach ($occupations as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ Auth::guard('learner')->user()->occupation == $value->id ? 'selected="selected"' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">No Occupation Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="marital_status">Marital Status</label>
                                                <select class="form-select select marital_status-select "
                                                    id="marital_status" name="marital_status">
                                                    <option value="">Select Marital Status</option>
                                                    @if (isset($marital_status) && !empty($marital_status))
                                                        @foreach ($marital_status as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ Auth::guard('learner')->user()->marital_status == $value->id ? 'selected="selected"' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">No Marital Status Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="education">Education </label>
                                                <select class="form-select select education-select " id="education_id"
                                                    name="education">
                                                    <option value="">Select Education</option>
                                                    @if (isset($educations) && !empty($educations))
                                                        @foreach ($educations as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ Auth::guard('learner')->user()->education == $value->id ? 'selected="selected"' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">No Education Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="your_interests">Your Interests </label>
                                                <select class="form-select select your_interests-select "
                                                    id="your_interests_id" name="your_interests[]" multiple>
                                                    <option value="">Select Your Interests</option>
                                                    @if (isset($your_interests) && !empty($your_interests))
                                                        @foreach ($your_interests as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ in_array($value->id, $user_interests) ? 'selected="selected"' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">Your Interests Not Found</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 checkout-form secure-alert">
                                            <div class="personal-info-head">
                                                <div class="form-check form-switch check-on mb-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="promotional_email" {{ $is_subscribed ? 'checked' : '' }}
                                                        value="1">
                                                    <label class="form-check-label">Promotional Emails</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="update-profile">
                                            <button type="submit" class="btn btn-primary">Update Profile</button>
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Profile Details -->

            </div>
        </div>
    </div>
    {{-- delete model start --}}
    <div class="modal fade" id="delete_conf" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="hideConfirmationModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                    <a href="javascript:void(0)" id="delete_conf_yes" type="button" class="btn btn-success">Yes</a>
                </div>
            </div>
        </div>
    </div>
    {{-- delete model end --}}

    <!-- /Dashbord Student -->
@endsection
@section('js')
    <script>
        $(document).ready(function() {
            $("#updateProfileForm").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    name: {
                        required: true,
                    },
                    state_id: {
                        required: true,
                    },
                    // gender: {
                    //     required: true,
                    // },
                    // mobile: {
                    //     required: true,
                    //     number: true,
                    //     minlength: 10,
                    //     maxlength: 10
                    // },
                    country_id: {
                        required: true
                    },
                    // state_id: {
                    //     required: {
                    //         depends: function(element) {
                    //             return $("#country-dropdown").val()
                    //         }
                    //     }
                    // }
                },
                messages: {
                    email: {
                        required: "Email field is Required.",
                        email: "Please enter a valid email address."
                    },
                    name: {
                        required: "Name field is Required.",
                    },
                    state_id: {
                        required: "State field is Required.",
                    },
                    // mobile: {
                    //     required: " Please enter a Mobile Number",
                    //     minlength: " Your Mobile Number must be consist of at least 10 Digits",
                    //     maxlength: " Your Mobile Number must be consist of at max 10 Digits",
                    //     number: " Please enter only digit"
                    // }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "gender") {
                        error.insertAfter($('#gendererror'));
                    } else {
                        error.insertAfter(element);
                    }
                },
            });
        });

        function openConfirmationModal() {


            $('#delete_conf').modal('show'); // Open the confirmation modal
            document.getElementById("delete_conf_yes").addEventListener("click", submitForm);
        }

        function hideConfirmationModal() {
            $('#delete_conf').modal('hide'); // Hide the confirmation modal
        }

        function submitForm() {

            $(".profileForm").submit();
            // document.getElementById("profileForm").submit(); // Submit the form
        }

        document.querySelector('#delete_conf .modal-footer .btn-danger').addEventListener('click', hideConfirmationModal);

        $(".select").select2({
            tags: true
        });

        $(document).on("change", "#state-dropdown", function() {
            $('#loader_section').show();
            $.ajax({
                url: "{{ route('student.profile.get_city') }}",
                type: "get",
                data: {
                    state_id: $(this).val(),
                },
                success: function(data) {
                    $('#loader_section').hide();
                    // $(".city-select").val("")
                    $(".city-select").html('').select2({
                        data: {
                            id: null,
                            text: null
                        }
                    });
                    $(".city-select").append("<option value=''>Select City</option>");
                    $.each(data, function(k, val) {
                        $(".city-select").append("<option value=" + val.id + ">" + val.name +
                            "</option>")
                        $(".city-select").trigger('change');

                    })
                }
            });
        })
    </script>
@endsection
