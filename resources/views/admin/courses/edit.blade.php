@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'select2CSS' => 1,
        'summerNoteCSS' => 1,
    ])
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-rowreorder/css/rowReorder.bootstrap4.min.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        #showAdvancedOptions>[aria-expanded="true"] i {
            -webkit-transform: rotate(180deg);
            -moz-transform: rotate(180deg);
            transform: rotate(180deg);
        }
    </style>
@endsection
@section('right-section')
    {!! redirect_to_back(route('courses.index')) !!}
@endsection
@section('content')
    @php
        use App\Models\Course;
        use App\Models\User;


        $id = request()->route("course");

        $videoId = Course::where("id",$id)->first()->videoId;
        // dd($videoId)
        $responseObj = getVideoTokenData($videoId);
        $otp ="";
        $playbackInfo ="";
        // dump()
        if(isset($responseObj->otp) != false) {
            $otp = $responseObj->otp;
        }
        if(isset($responseObj->playbackInfo) != false) {
            $otp = $responseObj->playbackInfo;
        }

        $node_servers = env('NODE_SERVER_URL');

    @endphp


        <div class="col-10 bg-design">
            <div class="d-flex justify-content-center mb-3">
                <h3>{{ isset($course->title) && !empty($course->title) ? ucfirst($course->title) : '' }} Edit</h3>
            </div>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    {{-- <a class="nav-link" id="details-tab" data-toggle="tab" href="#details" role="tab"
                        aria-controls="details"
                        aria-selected="{{ $errors->any() && $errors->course_page_title ? 'false' : 'true' }}">Details</a> --}}
                    <a class="nav-link active {{ $errors->any() == true ? 'active' : 'active' }}" id="details-tab"
                        data-toggle="tab" href="#details" role="tab" aria-controls="details"
                        aria-selected="false">Details</a>
                </li>


                <li class="nav-item">
                    <a class="nav-link" id="pricing-tab" data-toggle="tab" href="#pricing" role="tab"
                        aria-controls="pricing"
                        aria-selected="{{ $errors->any() && $errors->course_page_title ? 'false' : 'true' }}">Pricing</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="seo"
                        aria-selected="false">SEO</a>
                </li>
                {{-- <li class="nav-item">
                <a class="nav-link" id="advanced-tab" data-toggle="tab" href="#advanced" role="tab" aria-controls="advanced" aria-selected="false">Advanced</a>
            </li> --}}
            </ul>
            <div class="row justify-content-center mt-5">
                <div class="col-12">
                    <div class="tab-content" id="myTabContent">
                        <!-- Details Tab -->
                        {{-- <div class="tab-pane fade {{ $errors->any() && $errors->course_page_title ? '' : 'show active' }}" --}}
                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">

                            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                            @if (isset($course) && !empty($course) && !empty($course->id))
                                <form method="POST" action="{{ route('courses.update', ['course' => $course->id]) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="course_id" id="course_id" value="{{ $course->id }}">
                                    <input class="form-check-input select-type" type="hidden" name="type"
                                        id="courseType" value="{{ Course::COURSE }}"
                                        {{ isset($course->type) && !empty($course->type) && $course->type == Course::COURSE ? 'checked' : '' }}>
                                    {{-- <div class="row">
                                        <div class="col-2">
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input select-type" type="radio"
                                                        name="type" id="courseType" value="{{ Course::COURSE }}"
                                                        {{ isset($course->type) && !empty($course->type) && $course->type == Course::COURSE ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="courseType">Course</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <div class="form-check">
                                                    <input class="form-check-input select-type" type="radio"
                                                        name="type" id="packageType" value="{{ Course::PACKAGE }}"
                                                        {{ isset($course->type) && !empty($course->type) && $course->type == Course::PACKAGE ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="packageType">Package</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <input type="hidden" name="details_tab" value="1">
                                    <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                        <label for="title">Title</label><span style="color: red">*</span>
                                        <input type="text" class="form-control"
                                            value="{{ isset($course->title) && !empty($course->title) ? $course->title : old('title') }}"
                                            name="title" id="title">
                                        <span class="text-danger">{{ $errors->first('title') }}</span>

                                    </div>
                                    <div class="form-group">
                                        <label for="slug">Slug</label><span style="color: red">*</span>
                                        <input type="text" class="form-control"
                                            value="{{ isset($course->slug) && !empty($course->slug) ? $course->slug : old('slug') }}"
                                            name="slug" id="slug" readonly>
                                        @error('slug')
                                            <div class="text text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if (auth()->user()->roles[0]->name != 'instructor')
                                        <div class="form-group">
                                            <label for="status">Display Priority</label>
                                            <input type="text" name="order" id="order"
                                                value="{{ isset($course->order) && !empty($course->order) ? $course->order : old('order') }}"
                                                class="form-control" maxlength="10"
                                                min="0"onkeypress="return isNumericKey(event)"
                                                oninput="restrictToTwoDigits(event)" onclick="this.select()">
                                            @error('order')
                                                <div class="text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    @php
                                        $tags = [];
                                        if (isset($course) && isset($course->tags) && !empty($course->tags)) {
                                            $tags = explode(',', $course->tags);
                                        }
                                    @endphp

                                    <div class="form-group">
                                        <label for="tags">Tags</label> <small>comma separated for multiple tags</small>
                                        <select class="form-control tags" name="tags[]" id="tags" multiple="multiple"
                                            style="width:100%;">
                                            @forelse($tags as $key => $value)
                                                <option value="{{ $value ?? old('meta_keywords') }}" selected>
                                                    {{ $value }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </select>

                                    </div>

                                    {{-- <div class="form-group">
                                        <label for="instructor_name">Instructor Display Name</label>
                                        <input type="text" class="form-control" value="{{ isset($course->instructor_name) && !empty($course->instructor_name) ? $course->instructor_name : old('instructor_name') }}" name="instructor_name"
                                            id="instructor_name">
                                    </div> --}}

                                    @if (isset($instructors) && !empty($instructors) && count($instructors) > 0)
                                        <div class="form-group">
                                            <label for="instructor_id">Instructor</label><span style="color: red">*</span>
                                            <select name="instructor_id" class="form-control" id="instructor_id">
                                                <option value="">Select Instructor</option>
                                                @foreach ($instructors as $key => $value)
                                                    @if (auth()->user()->hasRole(User::INSTRUCTOR) && $value->id != Auth::id())
                                                        @php continue; @endphp
                                                    @endif
                                                    <option @if ($value->id == $course->instructor_id) selected @endif
                                                        value="{{ $value->id }}">
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('instructor_id')
                                                <div class="text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                    {{-- @php
                                        $id = \App\Models\Dropdown::whereSlug('course_category')->first()->id;
                                    @endphp --}}

                                    @php
                                        $arr = [];
                                        foreach ($course->categories as $key => $value) {
                                            $arr[] .= $value->id;
                                        }
                                    @endphp


                                    @if (isset($courseCategories) && !empty($courseCategories) && count($courseCategories) > 0)
                                        <div class="form-group">
                                            <label for=category_id>Course Categories</label><span
                                                style="color: red">*</span>
                                            @can("browse_dropdown_options"){!! add_new_category_button('course_category') !!}@endcan
                                            <select name="category_id[]" class="form-control course-categories"
                                                id="category_id" multiple="multiple" style="width: 100%">
                                                @foreach ($courseCategories as $key => $value)
                                                    {{-- <option @if ($value->id == $course->instructor_id) selected @endif value="{{ $value->id }}">
                                                {{ $value->name }}
                                            </option> --}}
                                                    <option @if (in_array($value->id, $arr)) selected @endif
                                                        value="{{ $value->id }}">
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="banner_image">Banner Image<small class="text-gray"> (Recommended Size
                                                1366px * 300px) Accept only jpg And png</small></label>
                                        <div class="image-upload-container">
                                            @if (isset($course->banner_image) && $course->banner_image)
                                                <a href="{{ getImageIfExists($course->banner_image, URL::asset('/front/img/user-love.jpg')) }}"
                                                    target="_blank">
                                                    <img class="img-fluid" style="height: 120px;width: 120px;"
                                                        alt=""
                                                        src="{{ getImageIfExists($course->banner_image, URL::asset('/front/img/user-love.jpg')) }}">
                                                </a>
                                                <button class="remove-image" type="button"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            @endif
                                            <label class="block form-control">
                                                <span class="sr-only">Choose File</span>
                                                <input type="file" name="banner_image" id=""
                                                    accept="image/*" />

                                                <input type="hidden" name="remove_banner_image" id="remove_banner_image"
                                                    value="0">

                                            </label>
                                        </div>
                                        @error('banner_image')
                                            <div class="text text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="intro_video">Introduction Video <small class="text-gray"> Recommended
                                                Video type ( mp4 )</small></label>
                                        <label class="block form-control">
                                            <span class="sr-only">Choose File</span>
                                            <input type="file" name="intro_videod" id="intro_video"
                                                accept="video/mp4,video/x-m4v,video/*,video/x-msvideo" />
                                        </label>

                                        @if (!empty($course->intro_video))
                                            <video class="video-thumbnail" width="40%" height="auto"
                                                controls="controls">
                                                <source src="{{ getImageIfExists($course->intro_video, '') }}"
                                                    type="video/{{ pathinfo($course->intro_video, PATHINFO_EXTENSION) }}">
                                            </video>
                                            <div class="remove-video">
                                                <button class="remove-video-button" type="button"><i
                                                        class="fas fa-trash-alt"></i></button>
                                                <input type="hidden" name="remove_intro_video" id="remove_intro_video"
                                                    value="0">
                                            </div>
                                        @endif
                                            @error('intro_video')
                                            <div class="text text-danger">{{ $message }}</div>
                                            @enderror
                                        <p class="success_msg text-success" style="display:none "></p>
                                        <div  style="display: none" class="progress mt-3" style="height: 25px">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%; height: 100%">75%</div>
                                        </div>
                                        @if( $videoId)
                                        <iframe id="videoPlayer"
                                            src="https://player.vdocipher.com/v2/?otp={{ @$responseObj->otp }}&playbackInfo={{ @$responseObj->playbackInfo }}"
                                            style="border:0;max-width:100%;top:0;left:0;height:250px;width:25%;" allowfullscreen="true"
                                            allow="encrypted-media">
                                        </iframe>
                                        @endif
                                    </div>


                                    <div class="form-group">
                                        <label for=how-to-use>Course Tag line</label>
                                        <textarea class="form-control" id="tagline" name="tagline">{{ isset($course->tagline) && !empty($course->tagline) ? $course->tagline : old('tagline') }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for=description>Description</label>
                                        <textarea class="form-control summernote-editor" id="description" name="description">
                                            {{ isset($course->description) && !empty($course->description) ? $course->description : old('description') }}
                                        </textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for=how-to-use>How to Use</label>
                                        <textarea class="form-control summernote-editor" id="how_to_use" name="how_to_use">
                                            {{ isset($course->how_to_use) && !empty($course->how_to_use) ? $course->how_to_use : old('how_to_use') }}
                                        </textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="lng">Language</label>
                                        <select class="form-control" name="lng" id="lng" style="width: 100%">

                                            <option value="1"
                                                @if ($course->lng == '1') <?php echo 'selected'; ?> @endif>English
                                            </option>
                                            <option value="2"
                                                @if ($course->lng == '2') <?php echo 'selected'; ?> @endif>Hindi</option>
                                                <option value="3"
                                                @if ($course->lng == '3') <?php echo 'selected'; ?> @endif>Hindi and English</option>

                                        </select>
                                    </div>

                                    <label for="course_duration">Course Duration</label>
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <input type="text" class="form-control hour_start"
                                                value="{{ isset($course->hours) && !empty($course->hours) ? $course->hours : '00' }}"
                                                name="hours" id="hours" placeholder="Hours"
                                                onkeypress="return isNumericKey(event)"
                                                oninput="restrictToTwoDigits(event)" onclick="this.select()">
                                            @error('hours')
                                                <div class="text text-danger hide_error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            <input type="text" class="form-control  hour_end"
                                                value="{{ isset($course->minutes) && !empty($course->minutes) ? $course->minutes : '00' }}"
                                                name="minutes" id="minutes" onkeypress="return isNumericKey(event)"
                                                oninput="restrictToTwoDigits(event)" placeholder="Minutes" min="0"
                                                oninput="validity.valid||(value='');">
                                            @error('minutes')
                                                <div class="text text-danger hide_error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    @if (auth()->user()->roles[0]->name == 'admin')

                                    <div class="coin_section">
                                        <h3>Success Coin Settings</h3>
                                        <div class="form-group">
                                            <label for="course_coin">Give coin when this course purchase (enter coin)</label>
                                            <input type="text" class="form-control"
                                                value="{{ isset($course->course_coin) && !empty($course->course_coin) ? $course->course_coin : old('course_coin') }}"
                                                onkeypress="return isNumericKey(event)" name="course_coin" id="course_coin">
                                            @error('course_coin')
                                                <div class="text text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group row">
                                            {{-- <div class="col-md-4">
                                                <label for="completely_watch"> Add coin when video completely watch</label>
                                                <input type="text" class="form-control" onkeypress="return isNumericKey(event)"
                                                    value="{{ isset($course->completely_watch) && !empty($course->completely_watch) ? $course->completely_watch : old('completely_watch') }}" name="completely_watch" id="completely_watch">
                                            </div> --}}

                                            <div class="col-md-6">
                                                <label for="course_finished"> Give coin when learner finished this course (enter coin)</label>
                                                <input type="text" class="form-control" onkeypress="return isNumericKey(event)"
                                                    value="{{ isset($course->course_finished) && !empty($course->course_finished) ? $course->course_finished : old('course_finished') }}" name="course_finished" id="course_finished">
                                                    @error('course_finished')
                                                    <div class="text text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-6">
                                            <p>Extra coin</p>
                                                <label for="course_finished_day">When learner finished this course in specific time (enter coin)</label>
                                                <input type="text" class="form-control" onkeypress="return isNumericKey(event)"
                                                    value="{{ isset($course->course_finished_day) && !empty($course->course_finished_day) ? $course->course_finished_day : old('course_finished_day') }}" name="course_finished_day" id=" course_finished_day">
                                                    @error('course_finished_day')
                                                    <div class="text text-danger">{{ $message }}</div>
                                                @enderror

                                            </div>
                                            <div class="col-md-6 extra-grap">

                                                <label for="days"> When course finished within specific days (enter days) </label>
                                                <input type="text" class="form-control" onkeypress="return isNumericKey(event)"
                                                    value="{{ isset($course->days) && !empty($course->days) ? $course->days : old('days') }}" name="days" id="days">
                                                    @error('days')
                                                    <div class="text text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                    </div>

                                    @endif

                                    {{--
                                    <div id="accordion">
                                        <div class="card"> --}}
                                    <h5 class="mb-0" id="showAdvancedOptions">
                                        {{-- <button type="button" class="btn btn-link collapsed"
                                                    data-toggle="collapse" data-target="#showAdvanced"
                                                    aria-expanded="false" aria-controls="showAdvanced">
                                                    Show Advanced Options <i class="fas fa-chevron-down"></i></i>
                                                </button> --}}
                                    </h5>
                                    {{-- <div id="showAdvanced" class="collapse" aria-labelledby="showAdvancedOptions"
                                                data-parent="#accordion">
                                                <div class="card-body"> --}}
                                    <div class="form-group">
                                        @include('admin.layouts.partials.buttons.toggle-button', [
                                            'dataValue' => $course->public_forum_status,
                                            'id' => 'public_forum_status',
                                            'name' => 'public_forum_status',
                                            'class' => 'd-inline',
                                            'toggleBtnText' => 'Enable Discussion',
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.layouts.partials.buttons.toggle-button', [
                                            'dataValue' => $course->show_learner_cnt,
                                            'id' => 'show_learner_cnt',
                                            'name' => 'show_learner_cnt',
                                            'class' => 'd-inline',
                                            'toggleBtnText' => 'Show Learners Count on Landing Page',
                                        ])
                                    </div>
                                    {{-- <div class="form-group">
                                                        @include(
                                                            'admin.layouts.partials.buttons.toggle-button',
                                                            [
                                                                'dataValue' => $course->allow_offline_data,
                                                                'id' => 'allow_offline_data',
                                                                'name' => 'allow_offline_data',
                                                                'class' => 'd-inline',
                                                                'toggleBtnText' =>
                                                                    'Allow offline usage on Mobile Apps',
                                                            ]
                                                        )
                                                    </div> --}}

                                    {{-- <div class="form-group">
                                                        @include(
                                                            'admin.layouts.partials.buttons.toggle-button',
                                                            [
                                                                'dataValue' => $course->allow_bookmark,
                                                                'id' => 'allow_bookmark',
                                                                'name' => 'allow_bookmark',
                                                                'class' => 'd-inline',
                                                                'toggleBtnText' => 'Allow bookmark Course Items',
                                                            ]
                                                        )
                                                    </div> --}}
                                    {{-- </div> --}}
                                    {{-- </div>
                                        </div> --}}
                                    {{-- </div> --}}
                                    @if (auth()->user()->hasRole('admin'))
                                        <div class="switch_section">
                                            <div class="form-group row">
                                                <div class="col-3 swich-area">
                                                    @include(
                                                        'admin.layouts.partials.buttons.toggle-button',
                                                        [
                                                            'dataValue' => $course->is_featured,
                                                            'id' => 'featured',
                                                            'name' => 'featured',
                                                            'toggleBtnText' => 'Featured',
                                                        ]
                                                    )
                                                </div>
                                                <div class="col-3 swich-area">
                                                    @include(
                                                        'admin.layouts.partials.buttons.toggle-button',
                                                        [
                                                            'dataValue' => $course->is_free,
                                                            'id' => 'is_free',
                                                            'name' => 'is_free',
                                                            'toggleBtnText' => 'Home Free Section',
                                                        ]
                                                    )

                                                </div>
                                                <div class="col-3 swich-area">
                                                    @include(
                                                        'admin.layouts.partials.buttons.toggle-button',
                                                        [
                                                            'dataValue' => $course->status,
                                                            'id' => 'status',
                                                            'name' => 'status',
                                                            'toggleBtnText' => 'Published',
                                                        ]
                                                    )
                                                </div>
                                            </div>

                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="">Course can be accessed through</label>
                                        <div class="form-group row p-0 m-0 py-3">
                                            @php
                                                $coursePlatformArray = [];
                                                if (isset($course)) {
                                                    if (isset($course->course_platform) && !empty($course->course_platform)) {
                                                        $coursePlatformArray = explode(',', $course->course_platform);
                                                    }
                                                }
                                            @endphp
                                            {{-- <div class="form-check form-check-inline">
                                                <input class="form-check-input list-course-checkbox" type="checkbox"
                                                    name="course_platform[]" id="all"
                                                    value="{{ Course::COURSE_ALL }}"
                                                    {{ in_array(Course::COURSE_ALL, $coursePlatformArray) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="all">All</label>
                                            </div> --}}
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input list-course-checkbox" type="checkbox"
                                                    name="course_platform[]" id="website"
                                                    value="{{ Course::COURSE_WEBSITE }}"
                                                    {{ in_array(Course::COURSE_WEBSITE, $coursePlatformArray) ? 'checked' : '' }} />
                                                <label class="form-check-label" for="website">Website</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input list-course-checkbox" type="checkbox"
                                                    name="course_platform[]" id="android"
                                                    value="{{ Course::COURSE_ANDROID }}"
                                                    {{ in_array(Course::COURSE_ANDROID, $coursePlatformArray) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="android">Android</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input list-course-checkbox" type="checkbox"
                                                    name="course_platform[]" id="ios"
                                                    value="{{ Course::COURSE_IOS }}"
                                                    {{ in_array(Course::COURSE_IOS, $coursePlatformArray) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ios">iOS</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-inline-block submit-btn btn-primary disableds">Submit</button>
                                </form>
                            @endif
                        </div>
                        <!-- Pricing Tab -->
                        <div class="tab-pane fade" id="pricing" role="tabpanel" aria-labelledby="pricing-tab">
                            @include('admin.courses.info.course-pricing')
                        </div>

                        <!-- Pages Tab -->
                        {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}
                        {{-- <div class="tab-pane fade {{ $errors->any() && $errors->course_page_title ? '' : 'show active' }}" --}}
                        <div class="tab-pane fade {{ $errors->any() && $errors->course_page_title ? '' : '' }}"
                            id="seo" role="tabpanel" aria-labelledby="seo-tab">
                            @if (isset($course) && !empty($course) && !empty($course->id))
                                <form method="POST" action="{{ route('seo.update', ['id' => $course->id]) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="pages_tab" value="3">
                                    {{-- <div class="form-group">
                                        <label for="course_page_url">Course Page URL</label>
                                        <input type="text" class="form-control" value="{{ old('course_page_url') }}" name="course_page_url" id="course_page_url">
                                        <small class="text-muted">Only hyphen, alphabets and numbers allowed.</small><br>
                                        <small>Please make sure this is SEO Friendly. Best Practice is to include Course Name and Instructor in the URL. For Example: Test-MSP-course-from-TestMsp.</small>
                                    </div> --}}
                                    <div class="form-group">
                                        <label for="meta_title">Meta Title</label>
                                        {{-- <span style="color: red">*</span> --}}
                                        <input type="text" class="form-control"
                                            value="{{ old('meta_title') ?? $course->meta_title }}" name="meta_title"
                                            id="meta_title">
                                        @error('meta_title')
                                            <div class="text text-danger hide_error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="meta_keywords">Meta Keywords</label>
                                        @php
                                            if (!empty($course->meta_keywords)) {
                                                $keywords = explode(',', $course->meta_keywords);
                                            }
                                        @endphp
                                        <select class="form-control metaKeywords" name="meta_keywords[]"
                                            id="meta_keywords" multiple="multiple" style="width:100%;">
                                            <option value=""></option>
                                            @if (!empty($course->meta_keywords) && isset($keywords) && count($keywords) > 0)
                                                @foreach ($keywords as $keywordkey => $keywordvalue)
                                                    <option value="{{ $keywordvalue }}" selected>{{ $keywordvalue }}
                                                    </option>
                                                @endforeach
                                            @endif

                                        </select>
                                        {{-- <small>This is the deprecated method of using Google Analytics. If you are setting
                                            analytics first time, we would recommend using Google Analytics 4. </small> --}}
                                        @error('meta_keywords')
                                            <div class="text text-danger hide_error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="meta_description">Meta Description</label>
                                        <textarea name="meta_description" cols="57" rows="5" id="meta_description" class="form-control">{{ old('meta_description') ?? $course->meta_description }}</textarea>
                                        {{-- <small>This is copied to metadata html tag of the course detail page. This is used
                                            on Google search result page. </small> --}}
                                        @error('meta_description')
                                            <div class="text text-danger hide_error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- <div class="form-group">
                                        <label for="course_category">Course Category</label>
                                        <input type="text" class="form-control" value="{{ old('course_category') }}"
                                            name="course_category" id="course_category">
                                    </div> --}}
                                    <button type="submit"
                                        class="btn btn-inline-block submit-btn btn-primary">Submit</button>
                                </form>
                            @endif
                        </div>

                        <!-- Advanced Tab -->
                        {{-- <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">

                    </div> --}}
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!-- Please add modal code here -->

@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
        'select2' => 1,
        'summerNote' => 1,
        'dateRangePicker' => 1,
        'validateJS' => 1,
    ])

    <script src="{{ asset('admin/plugins/datatables-rowreorder/js/dataTables.rowReorder.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>


    <script type="text/javascript">
        let browseFile = $('#intro_video');
        let resumable = new Resumable({
            target: '{{ route('intro_video') }}',
            query: {
                _token: '{{ csrf_token() }}',
                course_id:"{{ request()->route('course') }}"
            },
            fileType: ['mp4'],
            chunkSize: 100 * 1024 * 1024,
            headers: {
                'Accept': 'application/json'
            },
            testChunks: false,
            throttleProgressCallbacks: 1,
        });

        resumable.assignBrowse(browseFile[0]);

        resumable.on('fileAdded', function(file) {
            progress.show();
            $('.disableds').attr('disabled','disabled');

            $(".success_msg").hide();
            resumable.upload()
        });

        resumable.on('fileProgress', function (file) { // trigger when file progress update
            updateProgress(Math.floor(file.progress() * 100));
         });


        resumable.on('fileSuccess', function(file, response) {
            progress.hide();

            $(".success_msg").show();
            $(".success_msg").html("Video has been uploded")
            $('.disableds').removeAttr('disabled');
        });

        resumable.on('fileError', function(file, response) {
            alert('file uploading error.')
        });

        let progress = $('.progress');
        function showProgress() {
            progress.find('.progress-bar').css('width', '0%');
            progress.find('.progress-bar').html('0%');
            progress.find('.progress-bar').removeClass('bg-success');
            progress.show();
        }

        function updateProgress(value) {
            progress.find('.progress-bar').css('width', `${value}%`)
            progress.find('.progress-bar').html(`${value}%`)
        }

    </script>



    <script>
        $(document).ready(function() {
            $(".btns").hide();

            function hideSelected(value) {
                if (value && !value.selected) {
                    return $('<span>' + value.text + '</span>');
                }
            }

            $("#instructor_id").select2({
                selectOnClose: false,
                tags: true,
                tokenSeparators: [',', ' '],
                templateResult: hideSelected,
            });

            $(".cover_btn").click(function() {
                // $(".form_reset")[0].reset();
                // $('#configform')[0].reset();
                $("#image").val('')
                $(".img_err").remove();
            })


            var recurring = "{{ config('settings.instamojo_status') }}"

            $("#one-time-payment-plan").click(function() {
                $(".btns").show();
            })
            $("#free-plan").click(function() {

                $(".btns").show();
            })
            $("#recurring-plan").click(function() {
                // alert(recurring)
                $(".btns").hide();
                if (recurring == 1) {
                    $(".btns").hide();
                } else {
                    $(".btns").show();
                }
            })

        });
        // $(".payment_show").hide()
        // console.log("ok");
        // var flag
        // $("#list_price").keyup(function(){
        //     if($(this).val().length >= 6) {
        //         $("#max_number").show()
        //         flag =false
        //         $("#max_number").html("The must be required 6 digit.")
        //     } else {
        //         flag = true
        //         $("#max_number").hide()
        //     }
        // })

        // $("#final_payable_price").keyup(function(){
        //     if($(this).val().length >= 6) {
        //         $("#payable_number").show()
        //         flag =false
        //         $("#payable_number").html("The must be required 6 digit.")
        //     } else {
        //         flag = true
        //         $("#payable_number").hide()
        //     }
        // })


        // var start_duration
        // var end_duration
        // $(".hour_start").keyup(function() {
        //     start_duration = $(this).val().length
        //     if ($(".hour_start").val().length > 2) {

        //         $("#start_duration").show();
        //         $(".hide_error").hide();

        //     } else {
        //         $("#start_duration").hide();
        //         $(".hide_error").show();

        //     }
        //     //console.log(start_duration);
        // })
        // $(".hour_end").keyup(function() {
        //     end_duration = $(this).val()
        //     // console.log(end_duration,$(".hourse_start").val());
        //     if ($(".hour_end").val().length > 2) {
        //         $("#end_duration").show();
        //         $(".hide_error").hide();

        //     } else {
        //         $("#end_duration").hide();
        //         $(".hide_error").show();

        //     }
        // })





        //$("#myTab li a").removeClass('active');
        if ("{{ Session::has('admin_add_plan') && session('admin_add_plan') }}") {
            // $("#pricing-tab").addClass('active');
            $("#pricing-tab").trigger('click');
        } else if ("{{ !$errors->any() && $errors->course_page_title->count() == 0 }}") {
            "{{ Session::forget('admin_add_plan') }}";
            // $("#details-tab").addClass('active');
            $("#details-tab").trigger('click');
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // keywords
        $('.tags').select2({
            theme: "classic",
            selectOnClose: false,
            allowClear: true,
            minimumResultsForSearch: -1,
            tags: true,
            tokenSeparators: [',', ' ']
        });

        // reset form of price Plan
        $(document).on("click", ".addPrice", function() {
            $('label.error').hide();
            $('#pricingPlanForm')[0].reset();
            $(".btns").hide();
        });

        // document.getElementById('intro_video').addEventListener('change', function() {
        //     // alert(1245);
        //     var fileInput = this;
        //     var allowedTypes = ['video/mp4', 'video/x-m4v', 'video/*'];
        //     var file = fileInput.files[0];
        //     var fileType = file.type;

        //     if (!allowedTypes.includes(fileType)) {
        //         // Clear the selected file
        //         fileInput.value = '';
        //         // alert(12345678);
        //     }
        // });
        //Validate Add Pricing plan modal


        if ($("#pricingPlanForm").length > 0) {


            $('#pricingPlanForm').validate({
                // errorElement: 'div',
                rules: {
                    selectedPlan: {
                        required: true,
                    },
                    plan_name: {
                        required: true
                    },
                    list_price: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=selectedPlan]:checked").val() === 'one-time'
                            }
                        },
                        min: 9,
                        // greaterThanEqual: '#final_payable_price'
                    },
                    final_payable_price: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=selectedPlan]:checked").val() === 'one-time'
                            }
                        },
                        min: 9,
                        // lessThanEqual: '#list_price'
                    },
                    fixed_date: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=limit_course]:checked").val() === 'on'
                            }
                        }
                    },
                    price: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=selectedPlan]:checked").val() === 'recurring'
                            }
                        },
                        min: 1,
                    },
                    bill_learner_every: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=selectedPlan]:checked").val() === 'recurring'
                            }
                        },
                        digits: true,
                        min: 1,
                    },
                    fixed_date_add: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=fixed_date]:checked").val() === 'fixed_date'
                            }
                        }
                    },
                    fixed_days: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=fixed_date]:checked").val() === 'fixed_days'
                            }
                        }
                    },

                },
                messages: {
                    selectedPlan: {
                        required: "Please select plan",
                    },
                    plan_name: {
                        required: "Please enter the plan name"
                    },
                    list_price: {
                        required: function(element) {
                            if ($("input[name=selectedPlan]:checked").val() === 'one-time') {
                                return "Please enter list price";
                            } else {
                                return false;
                            }
                        }
                    },
                    final_payable_price: {
                        required: function(element) {
                            if ($("input[name=selectedPlan]:checked").val() === 'one-time') {
                                return "Please enter final payable price";
                            } else {
                                return false;
                            }
                        }
                    },
                    edit_price: {
                        required: function(element) {
                            if ($("input[name=selectedPlan]:checked").val() === 'recurring') {
                                return "Please enter price";
                            } else {
                                return false;
                            }
                        },
                        min: "Price always greater than zero."
                    },
                    fixed_date: {
                        required: function(element) {
                            if ($("input[name=limit_course]:checked").val() === 'on') {
                                return "Please choose one Option";
                            } else {
                                return false;
                            }
                        },
                        require_from_group: [1, '.mygroup']
                    },
                    fixed_date_add: {
                        required: function(element) {
                            if ($("input[name=fixed_date]:checked").val() === 'fixed_date') {
                                return "Please enter Date For Expire Plan";
                            } else {
                                return false;
                            }
                        },
                    },
                    fixed_days: {
                        required: function(element) {
                            if ($("input[name=fixed_date]:checked").val() === 'fixed_days') {
                                return "Please enter Number of Days For Expire Plan";
                            } else {
                                return false;
                            }
                        },
                    },
                    bill_learner_every: {
                        min: "Please enter greater than zero.."
                    }
                },
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "fixed_date" || element.attr("name") == "list_price" || element
                        .attr("name") == "final_payable_price" || element.attr("name") == "edit_price" ||
                        element.attr("name") == "fixed_date_add") {
                        error.insertAfter(element.parent("div"));
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {
                    event.preventDefault();

                    // if(flag == false) {

                    //     return false
                    // }

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            // If plan created successfully then this if() will work.
                            $('#loader_section').hide();
                            $('#pricingPlanForm')[0].reset();
                            if (response.code == 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'success',
                                    text: 'Plan added successfully!',
                                }).then(function() {
                                    location.reload();
                                });
                            }
                        },
                        error: function(response) {
                            // Code for backend error like spell mistake, variable not define and etc.
                            if (response.responseJSON.code == 'error') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.responseJSON.errors,
                                });
                            }

                            // Code for backend/Server side validation error
                            var errorMessage = '';
                            if (response.responseJSON.code == "server-error") {
                                $.each(response.responseJSON.errors, function(i, v) {
                                    errorMessage += v[0] + "<br>";
                                });
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    html: errorMessage,
                                });
                            }
                        },
                    });
                }
            });
        }

        $(document).on("click", ".editPlanModal", function() {
            var formAction = $(this).attr('data-url');
            var id = $(this).attr('data-id');

            $.ajax({
                url: "{{ route('course-prices.get-plan') }}",
                type: "POST",
                data: {
                    id: id,
                },
                dataType: "json",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(result) {
                    $('#loader_section').hide();

                    if (result.status == 'success') {
                        $('#editFreePlanModal').html(result.content).modal('show');
                    }

                    var plan = $("input[name='selectedPlan']:checked").val();
                    console.log(plan);
                    if (plan == "one-time") {
                        $(".payment_plan").show();
                        $(".free_plan").hide();
                    }
                    if (plan == "recurring") {
                        $(".payment_plan").hide();
                        $(".free_plan").hide();
                    }
                    if (plan == "free") {
                        $(".free_plan").show();
                        $(".payment_plan").hide();
                    }

                },
            });
        });


        var table;
        $(document).ready(function() {
            // List course for sale
            $(".list-course-checkbox").on('click', function() {
                if ($(this).is(':checked') && $(this).val() == '4') {
                    $('.list-course-checkbox').not(this).prop('checked', false);
                    $(this).prop('checked', true);
                } else {
                    $("#all").prop('checked', false);
                }
            });

            $('.customers').select2({
                theme: "classic",
                selectOnClose: false,
                allowClear: true,
                minimumResultsForSearch: -1,
                tags: true,
                dropdownAutoWidth: true
            });

            $('.metaKeywords').select2({
                theme: "classic",
                selectOnClose: false,
                allowClear: true,
                minimumResultsForSearch: -1,
                tags: true,
                tokenSeparators: [',', '']
            });

            function hideSelected(value) {
                if (value && !value.selected) {
                    return $('<span>' + value.text + '</span>');
                }
            }

            $(".course-categories").select2({
                theme: "classic",
                // tags: false,
                selectOnClose: false,
                allowClear: true,
                // multiple: true,
                minimumResultsForSearch: -1,
                tokenSeparators: [','],
                templateResult: hideSelected,
                // tags: true,
                tokenSeparators: [',', ' ']
            });
            var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
            $('#fixed_date').datetimepicker({
                format: 'YYYY-MM-DD',
                minDate: today,
            });

            // Code for Course & Package type selection
            $(".select-type").change(function() {
                var selectedType = $(this).val();
                var courseID = "{{ $course->id }}";
                if (selectedType != '' || selectedType != null || selectedType != undefined) {
                    $.ajax({
                        url: "{{ route('update.type', ['id' => $course->id]) }}",
                        method: "POST",
                        data: {
                            courseID: courseID,
                            selectedType: selectedType
                        },
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            setTimeout(function() {
                                $('<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><h5><i class="icon fas fa-check"></i> Success!</h5>' +
                                        response['text-message'] + '</div>')
                                    .insertBefore("#course_id").fadeOut(3000);
                            }, 1);
                            if (response['type'] == 1) {
                                $(".package_menu").hide();
                                $(".course_menu").show();
                            } else {
                                $(".course_menu").hide();
                                $(".package_menu").show();
                            }
                        },
                    })
                }
            });

            // Show hide element based on plan selection
            $('input[name="selectedPlan"]').change(function() {

                $("#pricingPlanForm").validate().resetForm();
                $(".fixed_date_add").hide()

                $(".radio_hide").hide()
                if ($(this).is(':checked')) {
                    $("#limit_course").prop("checked", false)
                    $('#allPlan').show();
                    $(".add_plan").empty()
                    $(".add_status").empty()
                    $('#submitPrice').removeAttr('disabled');
                    var inputValue = $(this).attr("value");
                    if (inputValue != null || inputValue != undefined) {
                        var targetBox = $("." + inputValue);
                        // $(".plan-modal").not(targetBox).find('input').val('');
                        $(".plan-modal").not(targetBox).find('input').prop("disabled", true);
                        $(targetBox).find('input').prop("disabled", false);
                        $(".plan-modal").not(targetBox).hide();
                        $(targetBox).show();
                    }


                } else {
                    $('#submitPrice').attr('disabled', 'disabled');
                    $('#allPlan').hide();
                }
            }).trigger('change');



            $(document).on("change", "#one-time-payment-plan", function() {

                $(".add_plan").empty()
                $(".add_status").empty()
                $(".fixed_date_add").hide()
                $(".radio_hide").hide()
                $(".renewing_subscriptions").show().val("")
                var plan = '<div class="form-group plan-modal free one-time recurring mt-2" >\
                                    <label for="plan_name">Plan name</label><span style="color: red">*</span>\
                                    <input type="text" class="form-control empty-data" value="{{ old('plan_name') }}" name="plan_name"\ id="plan_name" placeholder="Plan name">\
                                </div>';
                var status = '<div class="custom-control custom-switch ">\
                                <input type="checkbox" class="custom-control-input" name="status"\ id="plan_status" checked="">\
                                <label class="custom-control-label" for="plan_status">Status</label>\
                                </div>';

                $(".add_plan").append(plan)
                $(".add_status").append(status)
            })
            $(document).on("change", "#free-plan", function() {


                $(".add_plan").empty()
                $(".add_status").empty()
                $(".renewing_subscriptions").hide()

                $(".radio_hide").hide()
                var plan = '<div class="form-group plan-modal free one-time recurring mt-2" >\
                                    <label for="plan_name">Plan name</label><span style="color: red">*</span>\
                                    <input type="text" class="form-control empty-data" value="{{ old('plan_name') }}" name="plan_name"\ id="plan_name" placeholder="Plan name">\
                                </div>';
                var status = '<div class="custom-control custom-switch ">\
                    <input type="checkbox" class="custom-control-input" name="status"\ id="plan_status" checked="">\
                    <label class="custom-control-label" for="plan_status">Status</label>\
                    </div>';

                $(".add_plan").append(plan)
                $(".add_status").append(status)
            })
            $(document).on("change", "#recurring-plan", function() {

                $(".add_plan").empty()
                $(".add_status").empty()
                $(".fixeddays").hide()
                $(".radio_hide").hide()
            })

            table = $('#tbl_course_plans').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: "3",
                lengthChange: false,
                rowReorder: true,
                paging: false,
                ajax: "{{ url('backoffice/get-course-plans/' . $course->id) }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<span class="course_plan_box" data-id=' + data +
                                '><i class="fas fa-arrows-alt"></i></span>';
                        },
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name'
                    },
                    {
                        data: 'access_value',
                        name: 'access_value'
                    },
                    {
                        data: 'plan_type',
                        name: 'plan_type'
                    },
                    {
                        data: 'list_price',
                        name: 'list_price'
                    },
                    {
                        data: 'final_payable_price',
                        name: 'final_payable_price'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'barcode_checkout_url',
                        name: 'barcode_checkout_url',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'checkout_url',
                        name: 'checkout_url',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'default_web_price',
                        name: 'default_web_price',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'default_iphone_price',
                        name: 'default_iphone_price',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'default_android_price',
                        name: 'default_android_price',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [],
            });

            //Sorting plas order code start
            var order = [];
            table.on('row-reorder', function(e, diff, edit) {
                var order = [];
                $('#tbl_course_plan_body tr').each(function(index, element) {
                    order.push({
                        id: $(this).find(".course_plan_box").attr('data-id'),
                        position: index + 1
                    });
                });
                if (order.length > 0) {
                    sendOrderToServer(order);
                }
            });

            function sendOrderToServer(order) {
                var token = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: "{{ url('backoffice/course-plan-sortable') }}",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    type: "POST",
                    data: {
                        order: order,
                        _token: token
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response) {
                        if (response == 1) {
                            $('#loader_section').hide();
                        }
                        $('#loader_section').hide();
                    },
                    error: function(jqxhr, error, errorThrown) {

                        if (jqxhr.status == 403) {
                            Swal.fire(
                                'Unauthorized',
                                '',
                                'error'
                            );
                        }
                    },
                });
            }
        });
        // $(".limit_course").on('click', function() {
        //         if ($(this).is(':checked')) {
        //             $(".fixed-date").removeClass('d-none');
        //             $(".fixed-days").removeClass('d-none');
        //         } else {
        //             $(".fixed-date").addClass('d-none');
        //             $(".fixed-days").addClass('d-none');
        //             $("#until_fixed_date").val('');
        //         }
        //     });

        $(document).ready(function() {
            $(".limit_course").on('click', function() {
                if ($(this).is(':checked')) {
                    $(".fixed-date").removeClass('d-none');
                    $(".fixed-days").removeClass('d-none');
                    // show
                    // $("#until_fixed_date").trigger('click');
                    // $("#fixed_days").trigger('click');
                    $("#until_fixed_date").prop("checked", false)
                    $("#fixed_days").prop("checked", false)

                    $(".fixed_date_add").show()
                    $(".radio_hide").show()

                    $(".fixeddays").hide();
                    $(".fixeddays123").hide();
                } else {
                    $(".fixed-date").addClass('d-none');
                    $(".fixed-days").addClass('d-none');
                    $("#until_fixed_date").val('');
                    // hide
                    $(".fixeddays").addClass('d-none');
                    $(".fixeddate").addClass('d-none');

                    $("#until_fixed_date").prop("checked", false)
                    $("#fixed_days").prop("checked", false)

                    $(".radio_hide").hide()
                    // $("#fixed_days").hide()
                }
            });


            $(".fixed_days").click(function() {
                $(".fixeddays").show();
                $(".fixeddays123").hide();
            })
            $(".fixed_date").click(function() {
                $(".fixeddays123").show();
                $(".fixeddays").hide();
            })
        });
        $("#until_fixed_date").on('click', function() {
            if ($(this).is(':checked')) {
                $(".fixeddate").removeClass('d-none');
                $(".fixeddays").addClass('d-none');
                $("#fixed_days_input").val('');
            } else {
                $(".fixeddays").removeClass('d-none');
                $("#fixed_days_input").val('');
                $(".fixeddate").addClass('d-none');
                // $("#date_time_picker").val('');
            }
        });
        $("#fixed_days").on('click', function() {
            if ($(this).is(':checked')) {
                $(".fixeddays").removeClass('d-none');
                $(".fixeddate").addClass('d-none');
                // $("#date_time_picker").val('');
            } else {
                $(".fixeddays").addClass('d-none');
                $("#fixed_days_input").val('');
                $(".fixeddate").removeClass('d-none');
                // $("#date_time_picker").val('');
            }
        });
        $(document).on("click", ".priceSet", function() {
            var status = $(this).attr("checked");
            if (status == undefined) {
                var id = $(this).data('id');
                var type = $(this).data('type');
                var courseId = $(this).data('courseid');
                var token = "{{ csrf_token() }}";
                var data = {
                    id: id,
                    type: type,
                    courseId: courseId
                };
                var origin = "{{ url('backoffice/set-plan-for-course') }}";
                $.ajax({
                    url: origin,
                    type: "POST",
                    data: data,
                    headers: {
                        "X-CSRF-TOKEN": token
                    },
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response) {
                        $('#loader_section').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'success',
                            text: 'Plan Set successfully!',
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(jqxhr, error, errorThrown) {
                        if (jqxhr.status == 403) {
                            Swal.fire(
                                'Unauthorized',
                                '',
                                'error'
                            );
                        }
                    },
                })
            }
        });
    </script>
    <script>
        $('#addPlanModal').on('hidden.bs.modal', function(event) {
            $("#allPlan").hide();
        });

        function isNumericKey(event) {
            const charCode = (event.which) ? event.which : event.keyCode;
            return !(charCode > 31 && (charCode < 48 || charCode > 57));
        }

        function restrictToTwoDigits(event) {
            const inputValue = event.target.value;
            if (inputValue.length > 2) {
                event.target.value = inputValue.slice(0, 2);
            }
        }
        jQuery(document).ready(function($) {
            $('.remove-image').click(function() {
                var container = $(this).closest('.image-upload-container');
                container.find('img').remove();
                container.find('img').attr('src', '');
                container.find('input[type="file"]').val('');
                $('#remove_banner_image').val('1');
                $(this).remove(); // Set the flag to indicate image removal
            });
        });

        jQuery(document).ready(function($) {
            $('.remove-video-button').click(function() {
                var container = $(this).closest('.form-group');
                container.find('video').remove(); // Remove the video element
                $('#remove_intro_video').val('1'); // Set the flag to indicate video removal
                $(this).remove(); // Remove the remove button
            });
        });
    </script>
@endsection
