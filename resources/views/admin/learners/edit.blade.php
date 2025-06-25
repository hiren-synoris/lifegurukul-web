@extends('admin.layouts.app')
@section('right-section')
    <a href="{{ url('backoffice/learners?page=' . request('page', 1)) }}" class=" btn btn-warning px-2 py-1"><i class="fas fa-arrow-circle-left pr-2"></i> Back</a>
@endsection
@section('content')
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice{
        color: #000000 !important;
    }
</style>

            <div class="row justify-content-center">
                <div class="col-10 bg-design">
                @if (isset($learner) && !empty($learner))

                    @php
                        $singleCountry = Helper::getCountries($learner->country_id);
                        $countriesCollection = Helper::getCountries();
                    @endphp

                    <form method="POST" id="form_submit" action="{{ url('backoffice/learners/' . $learner->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="page" value="{{ request('page') }}">
                        <div class="d-flex justify-content-center mb-3">
                            <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : 'Edit ' . ucwords($learner->name) }}</h3>
                        </div>


                        {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                    <div class="form-group">
                        <label for="name">Name</label><span style="color: red">*</span>
                        <input type="text" class="form-control col-md-12 col-sm-12" name="name" id="name"
                            value="{{ isset($learner->name) && !empty($learner->name) ? $learner->name : old('name') }}"
                            >
                            @error('name')
                            <div class="text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile</label><span style="color: red">*</span>
                        <div class="row contry-too">
                            {{-- @if ($learner->country_id != null && isset($singleCountry) && !empty($singleCountry))
                                <input name="country_id" id="country_id" type="hidden" value="{{ $learner->country_id ?? '' }}" />
                                <label for="checkbox_{{ isset($singleCountry->id) ? $singleCountry->id : '' }}" class="country_flag">

                                    @if(isset($singleCountry->flag))
                                    <img width="50px" src="{{ URL::asset('/front/img/' .$singleCountry->flag ) }}"
                                        alt="Flag">
                                    @endif
                                </label>

                            @endif --}}
                            <input type="text" class="form-control col-10" readonly name="mobile"
                                value="{{ isset($learner->mobile) && !empty($learner->mobile) ? "+".@$phonecode->phonecode ." ". $learner->mobile : old('mobile') }}"

                                id="mobile" >
                        </div>
                        @error('mobile')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                    </div>
                    {{-- isset($learner->d_o_b) && !empty($learner->d_o_b) ? \Carbon\Carbon::parse($learner->d_o_b)->format('m/d/Y') : '' --}}
                    <div class="form-group">
                        <label for="d_o_b">Date Of Birth</label>
                        <input type="text" class="form-control d_o_b" name="d_o_b"
                            value="{{ isset($learner->d_o_b) && !empty($learner->d_o_b) ? \Carbon\Carbon::parse($learner->d_o_b)->format('d/m/Y') : old('d_o_b') }}"
                            id="d_o_b">
                            @error('d_o_b')
                            <div class="text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Email</label><span style="color: red">*</span>
                        <input type="email" class="form-control" name="email"
                            value="{{ isset($learner->email) && !empty($learner->email) ? $learner->email : old('email') }}"
                            id="email" >
                            @error('email')
                            <div class="text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label><br>
                        <div class="form-check-inline">
                            <input class="form-check-input select-type" type="radio" name="gender" id="male" value="{{ App\Models\Learner::MALE }}" {{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::MALE ? 'checked' : '' }}>
                            <label class="form-check-label" for="male">{{ App\Models\Learner::MALE_LABEL }}</label>
                        </div>
                        <div class="form-check-inline">
                            <input class="form-check-input select-type" type="radio" name="gender" id="female" value="{{ App\Models\Learner::FEMALE }}" {{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::FEMALE ? 'checked' : '' }}>
                            <label class="form-check-label" for="female">{{ App\Models\Learner::FEMALE_LABEL }}</label>
                        </div>
                        <div class="form-check-inline">
                            <input class="form-check-input select-type" type="radio" name="gender" id="other" value="{{ App\Models\Learner::OTHER }}" {{ isset($learner->gender) && !empty($learner->gender) && $learner->gender == App\Models\Learner::OTHER ? 'checked' : '' }}>
                            <label class="form-check-label" for="other">{{ App\Models\Learner::OTHER_LABEL }}</label>
                        </div>
                        @error('gender')
                            <div class="text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="profile_pic">Profile Picture</label><small class="text-gray">(jpg, svg, jpeg, png)</small>
                        @if (isset($learner->profile_pic) && !empty($learner->profile_pic) && Storage::exists($learner->profile_pic))
                            <a href="{{ Storage::url($learner->profile_pic) ?? 'javascript:void(0)' }}" target="_blank"><img
                                    src="{{ Storage::url($learner->profile_pic) }}" class="d-block mb-3"
                                    style="height: 120px;width: 120px;"></a>
                        @endif
                        <label class="block form-control">
                            <span class="sr-only">Choose File</span>
                            <input type="file" name="profile_pic" id="profile_pic" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </label>
                        <span id="image_error" class="" style="display: none;color:red"></span>
                        @error('profile_pic')
                                <div class="text text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group" style="display:nones">
                        <label for="name">Country <span style="color: red">*</span></label>
                        <select class="form-control country-select" disabled  id="country-dropdown" name="country_id">
                            <option value="">Select country</option>
                            @if (isset($countriesCollection) && !empty($countriesCollection))
                                @foreach ($countriesCollection as $country)
                                    <option value="{{ $country->id }}"
                                        {{ (isset($learner->country_id) && !empty($learner->country_id) ? $learner->country_id : old('country_id')) == $country->id ? 'selected="selected"' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">No Country Found</option>
                            @endif
                        </select>
                        @error('country_id')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                    </div>
                    @php
                        $states = Helper::getStates(isset($learner->country_id) && !empty($learner->country_id) ? $learner->country_id : old('country_id'));
                    @endphp
                    <div class="form-group">
                        <label for="state">State </label><span style="color: red">*</span>
                        <select class="form-control state-select" id="state-dropdown" name="state_id">
                            <option value="">Select State</option>
                            @if (isset($states) && !empty($states))
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}"
                                        {{ $learner->state_id == $state->id ? 'selected="selected"' : '' }}>
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
                        $cities = Helper::getCities($learner->state_id);
                    @endphp
                    <div class="form-group">
                        <label for="city">City</label>
                        <select class="form-control city-select" id="city-dropdown" name="city_id">
                            <option value="">Select City</option>
                            @if (isset($cities) && !empty($cities))
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}"
                                        {{ $learner->city_id == $city->id ? 'selected="selected"' : '' }}>
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
                        <select class="form-control occupation-select" id="occupation-dropdown" name="occupation">
                            <option value="">Select Occupation</option>
                            @if (isset($occupations) && !empty($occupations))
                                @foreach ($occupations as $occupation)
                                    <option value="{{ $occupation->id }}"
                                        {{ $learner->occupation == $occupation->id ? 'selected="selected"' : '' }}>
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
                        <select class="form-control marital_status-select" id="marital_status-dropdown" name="marital_status">
                            <option value="">Select Marital Status</option>
                            @if (isset($marital_status) && !empty($marital_status))
                                @foreach ($marital_status as $marital_status)
                                    <option value="{{ $marital_status->id }}"
                                        {{ $learner->marital_status == $marital_status->id ? 'selected="selected"' : '' }}>
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
                        <select class="form-control education-select" id="education-dropdown" name="education">
                            <option value="">Select Education</option>
                            @if (isset($educations) && !empty($educations))
                                @foreach ($educations as $education)
                                    <option value="{{ $education->id }}"
                                        {{ $learner->education == $education->id ? 'selected="selected"' : '' }}>
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
                        <label for="your_interests">Your Interests</label>
                        <select class="form-control form-select select your_interests-select" id="your_interests-dropdown" name="your_interests[]" multiple>
                            <option value="">Select Your Interests</option>
                            @if (isset($your_interests) && !empty($your_interests))
                                @foreach ($your_interests as $your_interests)
                                    <option value="{{ $your_interests->id }}"
                                        {{ in_array($your_interests->id, $user_interests) ? 'selected="selected"' : '' }}>
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
                        <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
                    </div>
                    </form>
                @endif
                </div>

            </div>




@endsection
@section('scripts')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
type="text/css" />
<link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
<script src="{{ asset('admin/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>

    <script>

$(".select").select2({
    tags: true
});
// $(function() {
            var nextDayDate = new Date();
            nextDayDate.setDate(nextDayDate.getDate() - 1);
            $(".d_o_b").datepicker({
                autoclose: true,
                format: 'dd/mm/yyyy',
                endDate: nextDayDate ,
            });

        // });

var flag
    $("#profile_spic").change(function() {

        var validExtensions = ["jpg", "svg", "jpeg", "png"]
        var file = $(this).val().split('.').pop();
        if (validExtensions.indexOf(file) == -1) {
            $("#image_error").show()
            $("#image_error").html("Only formats are allowed : " + validExtensions.join(', '));
            flag = false
        } else{
            flag = true
            $("#image_error").hide()
        }

        });


        // $("#form_submit").on('submit',(function(e) {
        //        // alert(flag)
        //         return flag
        //         $("#image_error").hide()
        //         max_upload_size();
        //     }))
        // max_upload_size();

        $("#country-dropdown").on("change", function() {
            var country_id = this.value;
            $("#state-dropdown").html("");
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
                        $("#state-dropdown").append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.name +
                            "</option>"
                        );
                    });
                    $("#city-dropdown").html(
                        '<option value="">Select State First</option>'
                    );
                },
            });
        });
        $("#state-dropdown").on("change", function() {
            var state_id = this.value;
            $("#city-dropdown").html("");
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
    </script>
@endsection
