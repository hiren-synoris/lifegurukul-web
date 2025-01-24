@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'multiSelectCSS' => 1,
        'select2CSS' => 1,
        'summerNoteCSS' => 1,
    ])
@endsection
@section('content')
    @if (isset($course) && !empty($course))
        <div class="d-flex justify-content-between align-items-center">
            <h3>{{ $course->title ?? '' }}</h3>
        </div>
    @endif
    @if (!$chapterData )
        <div class="row justify-content-center align-items-center mt-5 p-5">
            <h3 class="text text-primary">No record found</h3>
        </div>
    @else

        {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}
        <div class="row d-flex justify-content-center">
            <div class="col-10 bg-design">
                <form method="POST" name="chapterInfoForm" id="chapterInfoForm"
                    action="{{ route('courses.builder.update', ['id' => $course->id, 'chapterId' => $chapterData->id]) }}">
                    @csrf
                    @method('PUT')

                    @if (!$chapterData->timer)
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="mx-1"><button type="submit" name="action" value="save"
                                    class="btn btn-primary border"> <i class="far fa-save px-1"></i><span
                                        class="px-1">Save</span></button></div>
                        </div>
                    @endif
                    @php
                        $typeTextbox = Helper::setTypeWiseTextBox($chapterData->chapter_id);
                        $type = Helper::getTypeBasedOnChapterId($chapterData->chapter_id);
                        // dd($chapterData);
                        $upload_type = $chapterData->upload_type;
                    @endphp
                    <input name="upload_type" type="hidden" id="upload_type" value="{{ $upload_type }}">
                    @if ($type == App\Models\Chapter::SELL_BUY)
                        <div class="form-group">
                            <input type="hidden" name="title" value="{{ $chapterData->title }}">
                            <label for="title">Select Course/Package</label><span style="color: red">*</span>
                            <select class="form-control select2" id="course-dropdown" name="title"
                                {{ $chapterData->title ? 'disabled' : '' }}>
                                <option value="">Select Course/Package</option>
                                @if (isset($allCourses))
                                    @foreach ($allCourses as $key => $value)
                                        <option value="{{ $value['value'] }}"
                                            {{ $value['value'] == $chapterData->title ? 'selected="selected"' : '' }}>
                                            {{ $value['text'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="hidden" name="plan_id" value="{{ $chapterData->plan_id }}">
                            <label for="plan_id">Select Plan</label><span style="color: red">*</span>
                            <select class="form-control" id="plan_dropdown" name="plan_id"
                                {{ $chapterData->plan_id ? 'disabled' : '' }}>
                                @if (Helper::getCoursePlan($chapterData->title) != null)
                                    @foreach (Helper::getCoursePlan($chapterData->title) as $plan)
                                        <option value="{{ $plan->id }}"
                                            {{ $chapterData->plan_id == $plan->id ? 'selected="selected"' : '' }}>
                                            {{ $plan->plan_name }} ( ₹{{ $plan->final_payable_price }} )
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No Plan Found</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="timer">Minutes</label><span style="color: red">*</span>
                            <input type="text" class="form-control" {{ $chapterData->timer != null ? 'readonly' : '' }}
                                value="{{ isset($chapterData->timer) && !empty($chapterData->timer) ? $chapterData->timer : old('timer') }}"
                                name="timer" id="timer">
                            @error('timer')
                                <div class="text text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        @includeIf('front.course.sellbuy', [
                            'course' => get_course_detail_for_checkout($chapterData->title, $chapterData->plan_id),
                            'plan_id' => $chapterData->plan_id,
                            'isShow' => 0,
                        ])
                    @else
                        <div class="form-group">
                            <label for="title">Title</label><span style="color: red">*</span>
                            <input type="text" class="form-control"
                                value="{{ isset($chapterData->title) && !empty($chapterData->title) ? allowWhiteSpace($chapterData->title) : old('title') }}"
                                name="title" id="title" required>
                            @error('title')
                                <div class="text text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                    @if ($type == App\Models\Chapter::FLAG_IMAGE)
                        <img src="{{ isset($chapter->media) && !empty($chapter->media) && isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '' }}"
                            style="height: 50%;width: 50%;">
                    @endif

                    @if (
                        $type == App\Models\Chapter::FLAG_TEXT ||
                            $type == App\Models\Chapter::FLAG_LINK ||
                            $type == App\Models\Chapter::FLAG_PDF ||
                            $type == App\Models\Chapter::FLAG_VIDEO ||
                            $type == App\Models\Chapter::FLAG_AUDIO ||
                            $type == App\Models\Chapter::FLAG_FILE)
                        @php
                            // $tags = [];
                            // if (isset($chapterData) && isset($chapterData->tags) && !empty($chapterData->tags)) {
                            //     $tags = explode(',', $chapterData->tags);
                            // }
                        @endphp

                        {{-- <div class="form-group">
                            <label for="tags">Tags</label> <small>comma separated for multiple tags</small>
                            <select class="form-control metaKeywords" name="tags[]" id="tags" multiple="multiple"
                                style="width:100%;">
                                @forelse($tags as $key => $value)
                                    <option value="{{ $value ?? old('meta_keywords') }}" selected>{{ $value }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div> --}}
                        {{-- @if ($type != App\Models\Chapter::FLAG_AUDIO || $upload_type != App\Models\Chapter::YOUTUBE)
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input" type="checkbox"
                                    value="{{ old('enable_sharing') ?? $chapterData->enable_sharing }}"
                                    {{ old('enable_sharing') ?? $chapterData->enable_sharing == 1 ? 'checked' : '' }}
                                    id="enable_sharingchk">
                                <input type="hidden" value="{{ old('enable_sharing') ?? $chapterData->enable_sharing }}"
                                    id="enable_sharing" name="enable_sharing" />
                                <label class="form-check-label" for="enable_sharingchk">
                                    Enable Sharing
                                </label>
                            </div>
                        @endif --}}
                        {{-- <div class="form-check d-flex align-items-center">
                            <input class="form-check-input" type="checkbox"
                                value="{{ old('enable_watermark') ?? $chapterData->enable_watermark }}"
                                {{ old('enable_watermark') ?? $chapterData->enable_watermark == 1 ? 'checked' : '' }}
                                id="enable_watermarkchk">
                            <input type="hidden" value="{{ old('enable_watermark') ?? $chapterData->enable_watermark }}"
                                id="enable_watermark" name="enable_watermark" />
                            <label class="form-check-label" for="enable_watermark">
                                Enable Dynamic Watermark
                            </label>
                        </div> --}}

                        @php
                            // dd(explode(":",$chapterData->duration)[0]);
                        @endphp

                        @if ($type == App\Models\Chapter::FLAG_VIDEO || $type == App\Models\Chapter::FLAG_AUDIO)
                            @if ($type == App\Models\Chapter::FLAG_VIDEO)
                                <div class="form-group">
                                    <label for="duration"> Add coin when video completely watch</label>
                                    <input type="text" class="form-control" name="whole_video_coin"
                                        onkeypress="return isNumericKey(event)" oninput="restrictToTwoDigits(event)"
                                        onclick="this.select()" value="{{ $chapterData->whole_video_coin }}">
                                </div>
                            @endif
                            <label for="duration">Duration</label><br>
                            <div class="form-group row">
                                {{-- <input type="number" class="form-control"
                                    value="{{ isset($chapterData->duration) && !empty($chapterData->duration) ? $chapterData->duration : old('duration') }}"
                                    name="duration" id="duration"> --}}
                                {{-- <input type="text" class="form-control"
                                    value="{{ isset($chapterData->duration) && !empty($chapterData->duration) ? $chapterData->duration : old('duration') }}"
                                    name="duration" id="duration" readonly>
                                <input type="hidden" name="duration_in_seconds" id="duration_in_seconds"> --}}


                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control hour_start"
                                        value="{{ isset($chapterData->duration) && !empty($chapterData->duration) ? explode(':', $chapterData->duration)[0] : old('duration') }}"
                                        name="hours" id="hours" placeholder="Minutes"
                                        onkeypress="return isNumericKey(event)" oninput="restrictToTwoDigits(event)"
                                        onclick="this.select()">
                                </div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control hour_end"
                                        value="{{ isset($chapterData->duration) && !empty($chapterData->duration) ? explode(':', $chapterData->duration)[1] : old('duration') }}"
                                        name="minutes" id="minutes" placeholder="Seconds"
                                        onkeypress="return isNumericKey(event)" oninput="restrictToTwoDigits(event)"
                                        onclick="this.select()">
                                </div>
                            </div>

                            @if ($upload_type == App\Models\Chapter::VIMEO)

                                <div class="form-group">
                                    <label for="file_url">Vimeo Link</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"
                                        name="file_url" id="file_url">
                                    <small class="text-muted">eg. https://vimeo.com/336812686</small>
                                </div>
                            @endif

                            @if ($upload_type == App\Models\Chapter::YOUTUBE)
                                <div class="form-group">
                                    <label for="file_url">Youtube Link</label>
                                    <input type="text" class="form-control"
                                        value="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"
                                        name="file_url" id="file_url">
                                    <small class="text-muted">eg. https://www.youtube.com/watch?v=cO8b0hB9wzA</small>
                                </div>
                            @endif
                            @php
                                $thumbnail = Helper::thumbnailImage($chapterData->id);
                            @endphp
                            {{-- <div class="form-group">
                                <label for="available_till">Autoplay:</label><span style="color: red">*</span><br>
                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input autoplay" type="radio" name="autoplay"
                                            id="autoplay_1"
                                            {{ (old('autoplay') ?? $chapterData->autoplay) == 1 ? 'checked' : '' }}
                                            value="1">
                                        <label class="form-check-label" for="autoplay_1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input autoplay" type="radio" name="autoplay"
                                            id="autoplay_2"
                                            {{ (old('autoplay') ?? $chapterData->autoplay) == 0 ? 'checked' : '' }}
                                            value="0">
                                        <label class="form-check-label" for="autoplay_2">No</label>
                                    </div>
                                </div>
                            </div> --}}
                            {{--
                            <div class="form-group">
                                <label for="thumbnail">Thumbnail</label><small class="text-muted"> Recommended Size ( 1200px * 500px )  Accept only jpg And png</small>
                                <div class="form-group" data-target-input="nearest">
                                    @php
                                        $thumbnail = Helper::thumbnailImage($chapterData->id);
                                    @endphp
                                    <img style="width: 250px;" src="{{ $thumbnail }}" alt=""
                                        class="imagePreview img-fluid">
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="thumbnail" id="thumbnail"
                                        value="{{ isset($chapterData->thumbnail) && !empty($chapterData->thumbnail) ? $chapterData->thumbnail : old('thumbnail') }}"
                                        class="form-control" />

                                    <input class="btn btn-success" data-url="{{ route('course.thumbnail') }}"
                                        type='file' data-id="{{ $chapterData->id }}" id="thumbnailUpload"
                                        accepts=".png, .jpg, .jpeg" /><br>

                                    </div>
                                    <spna style="color:red" class="image_error"></spna>
                            </div> --}}
                        @endif

                        {{-- <div class="form-group">
                            <label for="availability_setting">Availibility settings:</label><span
                                style="color: red">*</span>
                            <div class="form-group">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input availability_setting" type="radio"
                                        name="availability_setting" id="availability__1"
                                        {{ (old('availability_setting') ?? $chapterData->availability_setting) == 0 ? 'checked' : '' }}
                                        value="0">
                                    <label class="form-check-label" for="availability__1">Always Available</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input availability_setting" type="radio"
                                        name="availability_setting" id="availability__2"
                                        {{ (old('availability_setting') ?? $chapterData->availability_setting) == 1 ? 'checked' : '' }}
                                        value="1">
                                    <label class="form-check-label" for="availability__2">Time Based</label>
                                </div>
                            </div>
                        </div> --}}

                        {{-- <div class="form-group" id="availableDate"
                            style="{{ (old('availability_setting') ?? $chapterData->availability_setting) == 1 ? 'display:block' : 'display:none' }}">
                            <div class="form-group">
                                <label for="available_from">Available From:</label><span style="color: red">*</span><br>
                                <div class="input-group date" data-target-input="nearest">
                                    <input type="text" name="available_from" id="available_from"
                                        value="{{ old('available_from') ?? $chapterData->available_from }}"
                                        class="form-control datetimepicker-input" data-target="#available_from" />
                                    <div class="input-group-append" data-target="#available_from"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="available_till">Available Till:</label><span style="color: red">*</span><br>
                                <div class="input-group date" data-target-input="nearest">
                                    <input type="text" name="available_till" id="available_till"
                                        value="{{ old('available_till') ?? $chapterData->available_till }}"
                                        class="form-control datetimepicker-input" data-target="#available_till" />
                                    <div class="input-group-append" data-target="#available_till"
                                        data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        @if ($type == App\Models\Chapter::FLAG_TEXT || $type == App\Models\Chapter::FLAG_FILE)
                            <div class="form-group">
                                <label for="description">Description <span style="color: red">*</span></label>

                                <textarea class="form-control summernote-editor-new" id="description" name="description">{{ isset($chapterData->description) && !empty($chapterData->description) ? $chapterData->description : old('description') }}</textarea>
                                <span style="color: red" id="desc_err"></span>
                            </div>
                        @endif
                        {{-- @dd(App\Models\Chapter::FLAG_FILE) --}}
                        @if ($type == App\Models\Chapter::FLAG_FILE)
                            @if (isset($chapter->media_id) && @$chapter->media->path && $chapter->media_id != null)
                                <a href="{{ isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '' }}"
                                    download><i class="fa fa-download" aria-hidden="true"></i></a>
                            @endif
                        @endif

                        @if ($type == App\Models\Chapter::FLAG_LINK)
                            <div class="form-group">
                                <label for="file_url">URL</label><span style="color: red">*</span>
                                <div class="input-group" data-target-input="nearest">
                                    <input type="text" name="file_url_view" id="file_url_view"
                                        value="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"
                                        class="form-control" />
                                </div>
                            </div>
                            @if ($chapter->image)
                                <div class="form-group">
                                    <label for="file_url">Image</label><span style="color: red">*</span><br>
                                    <img src='{{ url("storage/chapter_link/$chapter->image") }}' width="150">
                                </div>
            </div>
    @endif
    <!DOCTYPE html>
    <html>

    <body>
        <object width="100%" height="500px"
            data="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : '' }}"></object>
    </body>

    </html>
    {{-- <a target="_blank"
                                    href="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? Storage::url($chapter->file_url) : '' }}"><i
                                        class="fa fa-download" aria-hidden="true"></i></a> --}}
    @endif
    @if ($type == App\Models\Chapter::FLAG_PDF)
        @if (isset($chapter->media_id) && @$chapter->media->path && $chapter->media_id != null)
            <!DOCTYPE html>
            <html>

            <body>
                <object width="100%" height="500px"
                    data="{{ isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '' }}"></object>

            </body>

            </html>
        @else
            <!DOCTYPE html>
            <html>

            <body>
                <object width="100%" height="500px"
                    data="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"></object>
            </body>

            </html>
        @endif
    @elseif ($type == App\Models\Chapter::FLAG_VIDEO)
        @if ((isset($upload_type) && $upload_type == App\Models\Chapter::YOUTUBE) || $upload_type == App\Models\Chapter::VIMEO)
            <iframe id="videoObject" type="text/html" width="100%" height="500px" frameborder="0" allow="fullscreen"
                allowfullscreen></iframe>
        @else
            @if (isset($chapter->media->videoId) && @$chapter->media->videoId != null)
                @includeIf('admin.layouts.partials.video.iframe', [
                    'videoId' => $chapter->media->videoId,
                ])
            @elseif (isset($chapter->media_id) && @$chapter->media->path && $chapter->media_id != null)
                <video width="100%" height="500px" controls="controls" poster="{{ $thumbnail }}" />
                <source
                    src="{{ isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '' }}"
                    type="video/{{ pathinfo(isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '', PATHINFO_EXTENSION) }}">
                </video>
            @else
                <video width="100%" height="500px" controls="controls" poster="{{ $thumbnail }}" />
                <source
                    src="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"
                    type="video/{{ pathinfo(isset($chapter->file_url) && !empty($chapter->file_url) ? Storage::url($chapter->file_url) : '', PATHINFO_EXTENSION) }}">
                </video>
            @endif
        @endif
    @elseif ($type == App\Models\Chapter::FLAG_AUDIO)
        @if (isset($chapter->media->videoId) && @$chapter->media->videoId != null)
            @includeIf('admin.layouts.partials.video.iframe', [
                'videoId' => @$chapter->media->videoId,
            ])
        @elseif (isset($chapter->media_id) && @$chapter->media->path && $chapter->media_id != null)
            <audio controls data-autoplay="0" style="width:100% !important" width="100%" height="500px">
                <source
                    src="{{ isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url(@$chapter->media->path) : '' }}"
                    type="audio/mpeg">
            </audio>
        @else
            <audio controls data-autoplay="0" width="100%" height="500px" style="width:100% !important">
                <source
                    src="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : old('file_url') }}"
                    type="audio/mpeg">
            </audio>
        @endif

    @endif
    <div class="row">
        <div class="col-md-4">
            @if (
                $type == App\Models\Chapter::FLAG_PDF ||
                    $type == App\Models\Chapter::FLAG_AUDIO ||
                    ($type == App\Models\Chapter::FLAG_VIDEO && $upload_type != App\Models\Chapter::YOUTUBE))
                <div class="form-group mt-2">
                    <label for="allow_download">Allow download</label>
                    <div class="d-flex">
                        <input id="allow_download" name="allow_download" type="checkbox" class="form-control"
                            style="height: 26px;width: 27%;"
                            {{ isset($chapterData->allow_download) ? ($chapterData->allow_download == 1 ? 'checked' : '') : '' }}>
                        @if ($upload_type == App\Models\Chapter::VIMEO)
                            <span class="ml-2">Allow download permission from vimeo privacy </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-3">
            @if ($type == App\Models\Chapter::FLAG_AUDIO)
                <div class="form-group mt-2">
                    <label for="allow_sleep">Allow sleep time</label>
                    <div class="d-flex">
                        <input id="allow_download" name="allow_sleep" type="checkbox" value="true"
                            class="form-control" style="height: 26px;width: 27%;"
                            {{ isset($chapterData->allow_sleep) ? ($chapterData->allow_sleep == 'true' ? 'checked' : '') : '' }}>

                    </div>
                </div>
            @endif

        </div>
    </div>


    @endif
    </div>
    </form>
    </div>
    @endif

    <!-- Main Modal for course builder -->
    <div class="modal fade" id="addNewChapterBtn" tabindex="-1" role="dialog" aria-labelledby="addNewChapterBtnTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:1050px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ isset($course->title) && !empty($course->title) ? ucfirst($course->title) : '' }}</h5>
                    <button type="button" class="close reset_data" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-12 border-right">
                            <h5 class="text-center">Upload New Item</h5>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createPDF"
                                    id="createPDF">
                                <label class="form-check-label" for="createPDF"><b>PDF:</b> Add a PDF file in the
                                    course.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createVideo"
                                    id="createVideo">
                                <label class="form-check-label" for="createVideo"><b>Video:</b> All uploaded videos are
                                    completely secure and non downloadable. It can also be used to embed youtube and Vimeo
                                    videos.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createAudio"
                                    id="createAudio">
                                <label class="form-check-label" for="createAudio"><b>Audio</b></label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createFile"
                                    id="createFile">
                                <label class="form-check-label" for="createFile"><b>File:</b> Add any file type for
                                    learners to download.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createImage"
                                    id="createImage">
                                <label class="form-check-label" for="createImage"><b>Image</b></label>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 border-right">
                            <h5 class="text-center">Create New Item</h5>
                            <div class="form-check my-2 d-none">
                                <input class="form-check-input" type="radio" name="itemType" value="createLabel"
                                    id="createLabel">
                                <label class="form-check-label" for="createLabel"><b>Heading:</b> Define your chapter or
                                    section headings.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createArticle"
                                    id="createArticle">
                                <label class="form-check-label" for="createArticle"><b>Text:</b> Create your textual
                                    lessons in the course. It can also be used to embed iFrame, add HTML code through the
                                    Source option.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createLink"
                                    id="createLink">
                                <label class="form-check-label" for="createLink"><b>Link:</b> Add Link of any
                                    website.</label>
                            </div>
                            <div class="form-check my-2">
                                <input class="form-check-input" type="radio" name="itemType" value="createBuySell"
                                    id="createBuySell">
                                <label class="form-check-label" for="createBuySell"><b>Buy & Sell:</b> Promote course with
                                    plan.</label>
                            </div>
                        </div>
                        @can('browse_media')
                            <div class="col-md-4 col-sm-12 d-flex fw align-items-center justify-content-center flex-column">
                                <h1><i class="far fa-plus-square"></i></h1>
                                <p><small class="text-muted">Import from your existing course content</small></p>
                                <button class="btn btn-primary" type="button" data-type="" data-chapterid=""
                                    id="importFromAsset">Import from asset
                                    library</button>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF modal -->
    <div class="modal active builder-modal createPDF" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New PDF </h5><small> </small>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form></form> <!-- put this line because bootstrap remove popup form first element -->
                <form id="pdfForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="asset_type_pdf" value="1">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input pdf-type" type="radio" name="pdf_radio" id="pdf_upload"
                                    value="pdf_upload" checked>
                                <label class="form-check-label" for="pdf_upload">Upload</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input pdf-type" type="radio" name="pdf_radio"
                                    id="pdf_public_url" value="pdf_public_url">
                                <label class="form-check-label" for="pdf_public_url">Public URL</label>
                            </div>
                        </div>
                        <!-- For pdf upload -->
                        <div class="form-group pdf-form pdf_upload">
                            <input type="file" accept="application/pdf" class="form-control-file empty-data"
                                id="upload_pdf_file" name="upload_pdf_file">
                        </div>
                        <!-- For public URL -->
                        <div class="form-group pdf-form pdf_public_url">
                            <label for="pub_url">Public URL</label><span style="color: red">*</span>
                            <input type="text" class="form-control empty-data" value="{{ old('pub_url') }}"
                                name="pub_url" id="pub_url" placeholder="Public URL">
                            <p>eg. https://pdfobject.com/pdf/sample.pdf</p>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForPDF">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div class="modal active builder-modal createVideo" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New Video </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="videoForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden"class="chapterid" name="chapterid" value="">
                    <input type="hidden" name="asset_type_video" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input video-type video_btn_hide2" checked type="radio"
                                    name="video_radio" id="upload_vimeo" value="upload_vimeo">
                                <label class="form-check-label" for="upload_vimeo">Vimeo</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input video-type video_btn_hide1" type="radio"
                                    name="video_radio" id="upload_youtube" value="upload_youtube">
                                <label class="form-check-label" for="upload_youtube">Youtube</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input video-type video_btn_hide" type="radio"
                                    name="video_radio" id="upload_video" value="upload_video">
                                <label class="form-check-label" for="upload_video">Upload</label>
                            </div>
                            {{-- <div class="form-check form-check-inline">
                                <input class="form-check-input video-type" type="radio" name="video_radio"
                                    id="upload_video_link" value="upload_video_link">
                                <label class="form-check-label" for="upload_video_link">Link</label>
                            </div> --}}
                        </div>

                        <!-- For video upload -->
                        <div class="form-group video-form upload_video">
                            {{-- <input type="file" accept="video/mp4,video/x-m4v,video/*"
                                class="form-control-file empty-data" name="upload_video_file"> --}}
                            {{-- <input type="file" id="upload_video_file" name="upload_video_file" class="form-control-file empty-data" accept="video/mp4,video/x-m4v,video/*"/> --}}
                            Click <a target="_blank" href="{{ route('media.index') }}">here </a> for uploading new video
                            then after import from asset library
                        </div>
                        {{-- <div class="form-group video-form upload_video"">
                            <button id="browseFile" type="button" class="btn btn-primary">Choose file</button>
                        </div> --}}

                        <!-- For Youtube -->
                        <div class="form-group video-form upload_youtube">
                            <label for="youtube_link">Youtube Link</label>
                            <input type="text" class="form-control empty-data" value="{{ old('youtube_link') }}"
                                name="youtube_link" id="youtube_link" placeholder="Youtube Link">
                            <small class="text-muted">eg. https://www.youtube.com/watch?v=</small>
                        </div>
                        <div class="form-group video-form upload_youtube">
                            <label for="youtube_title">Title</label>
                            <input type="text" class="form-control empty-data" value="{{ old('youtube_title') }}"
                                name="youtube_title" id="youtube_title" placeholder="Title">
                        </div>

                        <!-- For Vimeo -->
                        <div class="form-group video-form upload_vimeo">
                            <label for="vimeo_link">Vimeo Link</label>
                            <input type="text" class="form-control empty-data" value="{{ old('vimeo_link') }}"
                                name="vimeo_link" id="vimeo_link" placeholder="Vimeo Link">
                            <small class="text-muted">eg. https://vimeo.com/</small>
                        </div>
                        <div class="form-group video-form upload_vimeo">
                            <label for="vimeo_title">Title</label>
                            <input type="text" class="form-control empty-data" value="{{ old('vimeo_title') }}"
                                name="vimeo_title" id="vimeo_title" placeholder="Title">
                        </div>
                        <!-- For Link -->
                        <div class="form-group video-form upload_video_link">
                            <label for="link">Link</label>
                            <input type="text" class="form-control empty-data" value="{{ old('link') }}"
                                name="link" id="link" placeholder="Link">
                        </div>
                        <div class="form-group video-form upload_video_link">
                            <label for="link_title">Title</label>
                            <input type="text" class="form-control empty-data" value="{{ old('link_title') }}"
                                name="link_title" id="link_title" placeholder="Title">
                        </div>

                    </div>

                    <div class="modal-footer footer_hide" style="display: noned">
                        <div> <small class="text-gray">Recommended Video type ( mp4 )</small></div>
                        <button type="button" class="btn btn-secondary resetData ">Reset</button>
                        <button class="btn btn-primary" id="btnForVideos">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Audio Modal -->
    <div class="modal active builder-modal createAudio" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New Audio </h5> <small></small>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="audioForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_audio" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For audio upload -->
                        <div class="form-group upload_audio">
                            {{-- <input type="file" accept=".mp3,audio/*" class="form-control-file empty-data"
                                name="upload_audio_file" id="upload_audio_file"> --}}
                            Click <a target="_blank" href="{{ route('media.index') }}">here </a> for uploading new audio
                            then after import from asset library
                        </div>
                    </div>
                    {{-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForAudio">Upload</button>
                    </div> --}}
                </form>
            </div>
        </div>
    </div>

    <!-- File Modal -->
    <div class="modal active builder-modal createFile" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New File </h5> <small></small>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="fileForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_file" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group upload_file">
                            <input type="file" class="form-control-file empty-data" name="upload_file"
                                id="upload_file">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForFile">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal active builder-modal createImage" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="imageForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_image" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group upload_file">
                            <input type="file" class="form-control-file empty-data" name="upload_image_file"
                                id="upload_image_file" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForImage">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Heading Modal -->
    <div class="modal active builder-modal createLabel" id="createLabelModal" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New Heading </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="headingForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_heading" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group">
                            <input type="text" class="form-control empty-data" name="heading" id="heading"
                                placeholder="New heading">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForHeading">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Text Modal -->
    <div class="modal active builder-modal createArticle" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New Text </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="textForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_text" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group">
                            <input type="text" class="form-control empty-data" name="title" id="title"
                                placeholder="Title">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForTitle">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Link Modal -->
    <div class="modal active builder-modal createLink" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New Link</h5>
                    <button type="button" class="close reset_new_link" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="linkForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="chapterid" value="">
                    <input type="hidden" name="asset_type_link" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group">
                            <label for="link_title">Title</label><span style="color: red">*</span>
                            <input type="text" class="form-control empty-data" value="" name="link_title"
                                id="link_title" placeholder="Title">
                        </div>
                        <div class="form-group">
                            <label for="link_url">URL</label><span style="color: red">*</span>
                            <input type="text" class="form-control empty-data" value="" name="link_url"
                                id="link_url" placeholder="URL">
                        </div>
                        <div class="form-group">
                            <label for="image_link" accept="image/*">Image</label>
                            <input type="file" class="form-control" value="" name="image_link"
                                id="image_link">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForTitle">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Buy AND Sell Modal -->
    <div class="modal active builder-modal createBuySell" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:640px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>New BUY & SELL</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="sellBuyForm" action="{{ route('chapters.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="">
                    <input type="hidden" class="chapterid" name="chapterid" value="">
                    <input type="hidden" name="asset_type_sellbuy" value="1">
                    <input type="hidden" name="course_id"
                        value="{{ isset($course) && !empty($course) ? $course->id : null }}">
                    <div class="modal-body">
                        <!-- For file upload -->
                        <div class="form-group">
                            <label for="title" style="width: 100%;">Select Course<span
                                    style="color: red">*</span></label>
                            <select class="form-control select2" style="width: 100%;" id="course-dropdown"
                                name="title">
                                <option value="" style="width: 100%;">Select Course</option>
                                @if (isset($allCourses))
                                    @foreach ($allCourses as $key => $value)
                                        <option value="{{ $value['value'] }}" style="width: 100%;">{{ $value['text'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="plan_id">Select Plan</label><span style="color: red">*</span>
                            <select class="form-control" id="plan_dropdown" name="plan_id">
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="timer">Minutes</label><span style="color: red">*</span>
                            <input type="text" class="form-control" name="timer"
                                onkeypress="return isNumericKey(event)" oninput="restrictToMinuteDigits(event)"
                                onclick="this.select()">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary resetData">Reset</button>
                        <button type="submit" class="btn btn-primary" id="btnForTitle">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Asset modal-->
    <div class="modal active builder-modal" id="assetModal" tabindex="-1" role="dialog"
        aria-labelledby="assetModalTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document"
            style="max-width:1200px!important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="asset-filter d-flex">
                        <div class="col-2">
                            <div class="form-group">
                                {{-- <select class="course-filter mb-2 form-control" multiple="multiple"> --}}
                                {{-- </select> --}}
                                <select id="courseFilter" class="course-filter mb-2 form-control">
                                    <option value="all">All Course</option>
                                    @if (isset($allCourses))
                                        @foreach ($allCourses as $key => $value)
                                            <option value="{{ $value['value'] }}">{{ $value['text'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <select id="assetTypes" class="asset-type-filter mb-2 form-control">
                                    <option value="all">All Asset Types</option>
                                    @if (isset($assetTypes))
                                        @foreach ($assetTypes as $key => $val)
                                            <option value="{{ $val['value'] }}">{{ $val['text'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                {{-- <select class="asset-type-filter mb-2 form-control">
                                </select> --}}
                            </div>
                        </div>
                        <input type="hidden" name="type" value="">
                        <input type="hidden" name="chapterid" value="">
                        <div style="display: none;" id="blk_add_frm">
                            @includeIf('admin.layouts.partials.actions.bulk-add-data', [
                                'bulkAddURL' => url('backoffice/courses/' . $course->id . '/bulk_add'),
                            ])
                        </div>
                        <!-- Bulk Add Button -->
                        <div class="col-2">
                            <div class="form-group">
                                @includeIf('admin.layouts.partials.buttons.bulk-add')
                            </div>
                        </div>
                    </div>


                    <table id="tbl_assets" class="table table-bordered table-hove w-100 only_active">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" />
                                </th>
                                <th scope="col">TITLE</th>
                                <th scope="col">DOWNLOAD</th>
                                {{-- <th scope="col">MODIFIED AT</th> --}}
                                <th scope="col">CREATED AT</th>
                                <th scope="col">CREATED BY</th>
                                {{-- <th scope="col">ACTION</th> --}}
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Add Modal -->
    <div class="modal fade" id="bulk_add_conf" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="add_conf_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_conf_title">Add Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to add ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                    <a href="javascript:void(0)" id="bluk_add_conf_yes" type="button" class="btn btn-primary">Yes</a>
                </div>
            </div>
        </div>
    </div>



@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.min.js"></script>
    <script type="text/javascript"></script>



    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'customScript' => 1,
        'multiSelect' => 1,
        'validateJS' => 1,
        'summerNote' => 1,
        'dateRangePicker' => 1,
        'select2' => 1,
        'switch' => 1,
        'dropzone' => 0,
    ])
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="{{ asset('admin/plugins/jquery-validation/additional-methods.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#course-dropdown').select2();
            $("#desc_err").hide();

            $(".video_btn_hide").click(function() {

                $(".footer_hide").hide()
            })
            $(".video_btn_hide1").click(function() {

                $(".footer_hide").show()
            })
            $(".video_btn_hide2").click(function() {

                $(".footer_hide").show()
            })

            $(".reset_new_link").click(function() {

                $("#linkForm")[0].reset()
            })

        });
    </script>
    @if (isset($upload_type) && $upload_type == App\Models\Chapter::YOUTUBE)
        <script>
            validateYouTubeUrl();

            // Youtube Code for getting total video duration
            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            var player;

            function onYouTubeIframeAPIReady() {
                player = new YT.Player('videoObject', {
                    events: {
                        'onReady': onPlayerReady
                    }
                });
            }

            function onPlayerReady(event) {
                const totalMs = player.getDuration() * 1000;
                const result = new Date(totalMs).toISOString().slice(11, 19);
                $("#duration").val(result); // For display purpose h:i:s.
                $('#duration_in_seconds').val(player.getDuration()); // For store data in seconds.
            }
            // Youtube code end..!!

            function validateYouTubeUrl() {
                var url = $('#file_url').val();
                var cnt = 0;
                if (url != undefined || url != '') {
                    var existdata = url.includes("http");
                    if (existdata == true) {
                        cnt = (url.match(/http/g)).length;
                        if (cnt > 1) {
                            $("#videoObject").hide();
                            $("#custom_message").show();
                            $("#custom_message").html('Only One URL At a time.');
                            return false;
                        } else {
                            var regExp = /^[a-zA-Z].*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                            var match = url.match(regExp);
                            if (match && match[2].length == 11) {

                                $("#custom_message").hide();
                                $("#videoObject").show();
                                $('#videoObject').attr('src', 'https://www.youtube.com/embed/' + match[2] +
                                    '?autoplay=1&enablejsapi=1');
                            } else {
                                $("label[for=file_url].error").hide();
                                $("#custom_message").show();
                                $('#videoObject').attr('src', '');
                                $("#custom_message").html('Enter correct youtube URL.');
                                $("#videoObject").hide();
                                return false;
                            }
                        }
                    } else {
                        var regExp = /^[a-zA-Z].*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                        var match = url.match(regExp);
                        if (match && match[2].length == 11) {
                            $("#custom_message").hide();
                            $("#videoObject").show();
                            $('#videoObject').attr('src', 'https://www.youtube.com/embed/' + match[2] +
                                '?autoplay=1&enablejsapi=1');
                        } else {
                            $("label[for=youtube_url].error").hide();
                            $("#custom_message").show();
                            $('#videoObject').attr('src', '');
                            $("#custom_message").html('Enter correct youtube URL.');
                            $("#videoObject").hide();
                            return false;
                        }
                    }
                } else {
                    $("#videoObject").hide();
                }
            }
        </script>
    @endif
    @if (isset($upload_type) && $upload_type == App\Models\Chapter::VIMEO)
        <script src="https://player.vimeo.com/api/player.js"></script>
        <script>
            parseUrl();

            //Getting Vimeo video duration
            var iframe = document.getElementById('videoObject');
            var player = new Vimeo.Player(iframe);

            player.getDuration().then(function(duration) {
                const totalMs = duration * 1000;
                const result = new Date(totalMs).toISOString().slice(11, 19);
                $("#duration").val(result); // For display purpose h:i:s.
                $('#duration_in_seconds').val(duration); // For store data in seconds.
            });
            // Vimeo code ended..!!

            function parseUrl() {
                var val = document.getElementById('file_url').value;
                var vimeoRegex = /(?:vimeo)\.com.*(?:videos|video|channels|)\/([\d]+)/i;
                var parsed = val.match(vimeoRegex);
                $('#videoObject').attr('src', "//player.vimeo.com/video/" + parsed[1]);
            };
        </script>
    @endif

    <script>
        // $("input[type='checkbox']").val();

        $(document).on("click", ".resetData,.close", function(event) {

            $('form').each(function() {
                $(this).validate().resetForm();
                $(this)[0].reset();
            });
        });

        $('#myModal').modal({
            backdrop: 'static',
            keyboard: false
        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        //Validate PDF modal
        if ($("#pdfForm").length > 0) {
            $('#pdfForm').validate({
                rules: {
                    pdf_radio: {
                        required: true,
                    },
                    upload_pdf_file: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=pdf_radio]:checked").val() === 'pdf_upload'
                            }
                        },
                        // extension: "pdf",
                        accept: "application/pdf",
                        filesize: 262144000 // 250MB
                    },
                    pub_url: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=pdf_radio]:checked").val() === 'pdf_public_url'
                            }
                        },
                        extension: "pdf",
                        url: true
                    }
                },
                messages: {
                    pdf_radio: {
                        required: "Please select Upload OR Public URL",
                    },
                    upload_pdf_file: {
                        required: 'Please select file.',
                        accept: 'Only PDF file is allowed.'
                    },
                    pub_url: {
                        required: 'Please insert public url.',

                    }
                },
                submitHandler: function(form) {
                    var fd = new FormData(form);
                    var upload_pdf_file = $('input[name="upload_pdf_file"]')[0].files;
                    if (upload_pdf_file.length > 0) {
                        fd.append('file', upload_pdf_file[0]);
                    }
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: fd,
                        dataType: "json",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            console.log(response);
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            console.log(response);
                        },
                    });
                }
            });
        }

        //Validate Video modal
        if ($("#videoForm").length > 0) {
            $('#videoForm').validate({
                rules: {
                    video_radio: {
                        required: true,
                    },
                    // upload_video_file: {
                    //     required: {
                    //         depends: function(elem) {
                    //             return $("input[name=video_radio]:checked").val() === 'upload_video'
                    //         }
                    //     },
                    //     accept: "video/*"
                    // },
                    youtube_link: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_youtube'
                            }
                        },
                        youtubeURL: true,
                    },
                    youtube_title: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_youtube'
                            }
                        },
                    },
                    vimeo_link: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_vimeo'
                            }
                        },
                        vimeoURL: true,
                    },
                    vimeo_title: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_vimeo'
                            }
                        },
                    },
                    link: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_video_link'
                            }
                        },
                    },
                    link_title: {
                        required: {
                            depends: function(elem) {
                                return $("input[name=video_radio]:checked").val() === 'upload_video_link'
                            }
                        },
                    }
                },
                messages: {
                    video_radio: {
                        required: "Please select Upload OR Public URL",
                    },
                    // upload_video_file: {
                    //     required: 'Please select file.',
                    //     accept: "Only video file is allowed."
                    // },
                    youtube_link: {
                        required: 'YouTube link is required.',
                    },
                    youtube_title: {
                        required: 'Title is required.',
                    },
                    vimeo_link: {
                        required: 'Vimeo link is required.',
                    },
                    vimeo_title: {
                        required: 'Title is required.',
                    },
                    link: {
                        required: 'Link is required'
                    },
                    link_title: {
                        required: 'Title is required'
                    }
                },
                submitHandler: function(form) {
                    // alert("")
                    // return false
                    var fd = new FormData(form);
                    // var upload_video_file = $('input[name="upload_video_file"]')[0].files;
                    // if (upload_video_file.length > 0) {
                    //     return false
                    // }

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: fd,
                        dataType: "json",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                            console.log(response);
                        },
                        error: function(response) {
                            $('#loader_section').hide();
                            fail();
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate Audio modal
        if ($("#audioForm").length > 0) {
            $('#audioForm').validate({
                rules: {
                    upload_audio_file: {
                        required: true,
                        accept: 'audio/*',
                        filesize: 209715200 // 200 MB
                    },
                },
                messages: {
                    upload_audio_file: {
                        required: "Please select audio file",
                        accept: "Only audio file is allowed."
                    },
                },
                submitHandler: function(form) {
                    var fd = new FormData(form);
                    var upload_audio_file = $('input[name="upload_audio_file"]')[0].files;
                    if (upload_audio_file.length > 0) {
                        fd.append('file', upload_audio_file[0]);
                    }
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: fd,
                        dataType: "json",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            $('#loader_section').hide();
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate File modal
        if ($("#fileForm").length > 0) {
            $('#fileForm').validate({
                rules: {
                    upload_file: {
                        // required: true,
                        required: function() {
                            var $img = $("#upload_file");
                            if ($img.length && $img.is(':visible') && $img.attr('src')) {
                                return false;
                            } else {
                                return true;
                            }
                        },
                        filesize: 1073741824,
                        extension: "xlsx|csv|docx|zip|pdf|txt|xls|doc" // 1GB
                    },
                },
                messages: {
                    upload_file: {
                        required: "Please select file",
                        extension: "Please select a valid file(excel,docx,zip,pdf,txt,xls,doc)."
                    },
                },
                submitHandler: function(form) {
                    var fd = new FormData(form);
                    var upload_file = $('input[name="upload_file"]')[0].files;
                    if (upload_file.length > 0) {
                        fd.append('file', upload_file[0]);
                    }
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: fd,
                        dataType: "json",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            $('#loader_section').hide();
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate File modal
        if ($("#imageForm").length > 0) {
            $('#imageForm').validate({
                rules: {
                    upload_image_file: {
                        required: true,
                        accept: 'image/*'
                    },
                },
                messages: {
                    upload_image_file: {
                        required: "Please select file",
                        accept: 'Only image file is allowed.'
                    },
                },
                submitHandler: function(form) {
                    var fd = new FormData(form);
                    var upload_file = $('input[name="upload_image_file"]')[0].files;
                    if (upload_file.length > 0) {
                        fd.append('file', upload_file[0]);
                    }
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: fd,
                        dataType: "json",
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            $('#loader_section').hide();
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate Heading modal
        if ($("#headingForm").length > 0) {
            $('#headingForm').validate({
                rules: {
                    heading: {
                        required: true,
                        normalizer: function(value) {
                            return $.trim(value);
                        },
                    },
                },
                messages: {
                    heading: {
                        required: "Heading is required",
                    },
                },
                submitHandler: function(form) {

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate Text modal
        if ($("#textForm").length > 0) {
            $('#textForm').validate({
                rules: {
                    title: {
                        required: true,
                        normalizer: function(value) {
                            return $.trim(value);
                        },

                    },
                },
                messages: {
                    title: {
                        required: "Title is required",
                    },
                },
                submitHandler: function(form) {
                    console.log($(form).serialize());
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        //Validate Link modal
        if ($("#linkForm").length > 0) {
            // $.validator.addMethod("nowhitespace", function(value, element) {
            //     return this.optional(element) || /^\S+$/i.test(value);
            // }, "No white space please");

            $('#linkForm').validate({
                rules: {
                    link_title: {
                        required: true,
                        normalizer: function(value) {
                            return $.trim(value);
                        },
                        // nowhitespace:true
                    },
                    link_url: {
                        required: true,
                        url: true
                    },
                    image_link: {
                        accept: "image/*", // validate file type
                        filesize: 5242880

                    },
                },
                messages: {
                    link_title: {
                        required: "Title is required",
                    },
                    link_url: {
                        required: "URL is required",
                    },
                    image_link: {
                        accept: "Only image files are allowed",
                        filesize: "File size cannot exceed 5MB"
                    },
                },
                submitHandler: function(form) {
                    formData = new FormData(form);
                    // alert("")
                    // return false
                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });

            $.validator.addMethod('filesize', function(value, element, param) {
                return this.optional(element) || (element.files[0].size <= param);
            });
        }

        //Validate sell Buy modal
        if ($("#sellBuyForm").length > 0) {
            $('#sellBuyForm').validate({
                rules: {
                    title: {
                        required: true,
                    },
                    plan_id: {
                        required: true,
                    },
                    timer: {
                        required: true,
                        min: 1,
                    },
                },
                messages: {
                    Title: {
                        required: "Select Course is Required",
                    },
                    plan_id: {
                        required: "Course Plan is required",
                    },
                    timer: {
                        required: "Minutes is required",
                    },
                },
                submitHandler: function(form) {

                    $.ajax({
                        url: form.action,
                        type: form.method,
                        data: $(form).serialize(),
                        dataType: "json",
                        beforeSend: function() {
                            $('#loader_section').show();
                        },
                        success: function(response) {
                            $('#loader_section').hide();
                            if (response.url) {
                                //Pass only url/route for redirect inside this success()
                                success(response.url);
                            }
                        },
                        error: function(response) {
                            $('#loader_section').hide();
                            if (response.responseJSON.code == 1) {
                                fail();
                            }
                        },
                    });
                }
            });
        }

        var assetTable;
        $(document).ready(function() {
            // Define vaariables
            //assetTable = $('#tbl_assets'); // Datatable for listing
            // courseFilter = $('.course-filter'); //Course dropdown filter
            // assetTypeFilter = $('.asset-type-filter'); //Asset type dropdown filter

            //Main modal code
            $('input[name="itemType"]').click(function() {
                var inputValue = $(this).attr("value");
                var targetBox = $("." + inputValue);
                $(".builder-modal").not(targetBox).hide();
                $(targetBox).modal('show');
            });

            //PDF modal code
            $('input[name="pdf_radio"]').change(function() {
                if ($(this).is(':checked')) {
                    var inputValue = $(this).attr("value");
                    if (inputValue != null || inputValue != undefined) {
                        var targetBox = $("." + inputValue);
                        $(".pdf-form").not(targetBox).hide();
                        $(".pdf-form").not(targetBox).find('input').val('');
                        $(".pdf-form").not(targetBox).find('input').prop("disabled", true);
                        $(".server-error").addClass('d-none').find('label').remove();
                        $(targetBox).find('input').prop("disabled", false);
                        $(targetBox).show();
                        if (inputValue == 'pdf_public_url') {
                            $("#btnForPDF").text('Submit');
                        } else {
                            $("#btnForPDF").text('Upload');
                        }
                    }
                }
            }).trigger('change');

            //Video modal code
            $('input[name="video_radio"]').change(function() {
                if ($(this).is(':checked')) {
                    var inputValue = $(this).attr("value");
                    if (inputValue != null || inputValue != undefined) {
                        var targetBox = $("." + inputValue);
                        $(".video-form").not(targetBox).hide();
                        $(".video-form").not(targetBox).find('input').val('');
                        $(".video-form").not(targetBox).find('input').prop("disabled", true);
                        $(targetBox).find('input').prop("disabled", false);
                        $(targetBox).show();
                        if (inputValue == 'upload_video') {
                            $("#btnForVideo").text('Upload');
                        } else {
                            $("#btnForVideo").text('Submit');
                        }
                    }
                }
            }).trigger('change');

            //250 MB video file size
            $("input[name='upload_pdf_file']").on("change", function() {
                if (this.files[0].size > 250000000) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error! File too large',
                        text: 'Maximum file size should be 250MB',
                    });
                    $(this).val('');
                }
            });

            //200 MB audio file size
            $("input[name='upload_audio_file']").on("change", function() {
                if (this.files[0].size > 200000000) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error! File too large',
                        text: 'Maximum file size should be 200MB',
                    });
                    $(this).val('');
                }
            });

            //1 GB file size
            $("input[name='upload_file']").on("change", function() {
                if (this.files[0].size > 1073741824) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error! File too large',
                        text: 'Maximum file size should be 1GB',
                    });
                    $(this).val('');
                }
            });

            $("#importFromAsset").on('click', function() {



                assetTable = $('#tbl_assets').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    destroy: true,
                    // ajax: "{{ url('backoffice/get-media') }}",
                    ajax: {
                        "url": "{{ url('backoffice/get-media-chapter') }}",
                        "data": function(d) {
                            return $.extend({}, d, {
                                "assetTypes": $("#assetTypes").val().toLowerCase(),
                                "courseFilter": $("#courseFilter").val().toLowerCase()
                            });
                        }
                    },
                    columnDefs: [{
                        className: 'text-center',
                        targets: '_all'
                    }],
                    "drawCallback": function(settings) {
                        if ($("#main_checkbox").is(":checked")) {
                            $("#main_checkbox").trigger("click");
                            $("#main_checkbox").prop("checked", true);
                        } else {
                            $("#main_checkbox").prop("checked", false);
                        }
                    },
                    columns: [{
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            // render: function(data, type, row) {
                            //     return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                            //         data + ']" data-id=' + data +
                            //         ' style="cursor: pointer;"/>';
                            // },
                        },
                        {
                            data: 'title',
                            name: 'media.title'
                        },
                        {
                            data: 'image',
                            name: 'media.path'
                        },

                        {
                            data: 'created_at',
                            name: 'media.created_at'
                        },
                        {
                            data: 'created_by',
                            name: 'created_by',
                            orderable: false,
                            searchable: false,
                        },
                    ],
                    "order": [],
                });

                // Redraw the table
                assetTable.draw();

                // Redraw the table based on the custom input
                $('#assetTypes').bind("keyup change", function() {
                    assetTable.draw();
                });
                // Redraw the table based on the custom input
                $('#courseFilter').bind("keyup change", function() {
                    assetTable.draw();
                });
                $(document).on("click", ".close", function(event) {

                    $('#courseFilter option:first').prop('selected', true);
                    // $('#assetTypes option:first').prop('selected', true);
                    $('form').each(function() {
                        $(this).validate().resetForm();
                        $(this)[0].reset();
                    });
                    $("input[type='file']").val(null);
                    assetTable.draw();
                });

                var type = $('#importFromAsset').attr('data-type');
                var chapterid = $('#importFromAsset').attr('data-chapterid');
                $('input[name="type"]').val(type);
                $('input[name="chapterid"]').val(chapterid);
                $("#assetModal").modal('show');

            });

            // keywords
            $('.metaKeywords').select2({
                theme: "classic",
                selectOnClose: false,
                allowClear: true,
                minimumResultsForSearch: -1,
                tags: true,
                tokenSeparators: [',', ' ']
            });

            $(document).on("click", ".children_checkbox", function() {
                //console.log("sdfsdf");
                $("#main_checkbox").prop('checked', false);
                if ($(this).is(":checked")) {
                    $(this).prop('checked', true);
                    let data_id = $(this).data('id');
                    $("#bulk_add_frm").append('<input name="bd[' + data_id + ']"  />');
                } else {
                    $(this).prop('checked', false);
                    let data_id = $(this).data('id');
                    $('input[name="bd[' + data_id + ']"]').remove();
                }
                var type = $('input[name="type"]').val();
                var chapterid = $('input[name="chapterid"]').val();
                $("#bulk_add_frm").append('<input name="type" value="' + type + '" />');
                $("#bulk_add_frm").append('<input name="chapterid" value="' + chapterid + '" />');
            });

            $(document).on("click", "#main_checkbox", function() {
                $(".children_checkbox").prop('checked', !$(".children_checkbox").prop("checked"));
                if ($(this).is(":checked")) {
                    $(".children_checkbox").map(function(key, value) {
                        let data_id = $(value).data('id');
                        $("#bulk_add_frm").append('<input name="bd[' + data_id + ']"  />');
                    });
                    $(".children_checkbox").prop("checked", true);
                } else {
                    $("#bulk_add_frm").find('input').not(":first").remove();
                    $(".children_checkbox").prop("checked", false);
                }
                var type = $('input[name="type"]').val();
                var chapterid = $('input[name="chapterid"]').val();
                $("#bulk_add_frm").append('<input name="type" value="' + type + '" />');
                $("#bulk_add_frm").append('<input name="chapterid" value="' + chapterid + '" />');
            });

        });

        function updateType(type, chapter = null) {
            $('input:radio[name="itemType"]').prop("checked", false)
            if (type != undefined && type !== '') {

                $('#importFromAsset').attr('data-type', type);
                $('#importFromAsset').attr('data-chapterid', chapter);
                var x = $('.builder-modal').find('form');
                $(x).each(function(index, value) {
                    let length = $(value).find('input[name="type"]').length;
                    if (length > 0) {
                        $(value).find('input[name="type"]').val(type);
                        $(value).find('input[name="chapterid"]').val(chapter);
                    }
                    // .find('input[name="type"]')
                });
                // console.log($('.builder-modal').find('form'));
                // $('.builder-modal').find('form').find('input[name="type"]').val(type);
            }

            var course_ids = "{{ isset($course) && !empty($course) ? $course->id : null }}"
            var chapterid = "{{ request()->route('chapterId') }}";
            // alert(chapterid)
            var type = $('#importFromAsset').attr('data-type');
            let browseFile = $('#browseFile');
            var chapterid = $('#importFromAsset').attr('data-chapterid');
        }

        function openHeading(type, chapter = null) {

            $('input:radio[name="itemType"]').prop("checked", false)
            if (type != undefined && type !== '') {

                $('#importFromAsset').attr('data-type', type);
                $('#importFromAsset').attr('data-chapterid', chapter);
                var x = $('.builder-modal').find('form');
                $(x).each(function(index, value) {
                    let length = $(value).find('input[name="type"]').length;
                    if (length > 0) {
                        $(value).find('input[name="type"]').val(type);
                        $(value).find('input[name="chapterid"]').val(chapter);
                    }
                    // .find('input[name="type"]')
                });
            }
        }

        // function triggeronlyheading() {
        //     $("#createLabel").prop("checked", true).trigger("click");
        //     // $('#addNewChapterBtn').modal('hide');
        //     $('#addNewChapterBtn').modal('toggle');

        // }

        // $(document).on('show.bs.modal','#addNewChapterBtn', function () {
        //     // alert('hi');
        //     $("#createLabel").prop("checked", true).trigger("click");

        // });




        function courseselect(e, c) {
            e.preventDefault();
            // console.log(c.data('id'));
        }
        $('#available_till,#available_from').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
        });

        $('#enable_sharingchk').change(function() {
            var flag = $(this).is(':checked') ? 1 : 0;
            $('#enable_sharing').val(flag);
        });

        $('#enable_watermarkchk').change(function() {
            var flag = $(this).is(':checked') ? 1 : 0;
            $('#enable_watermark').val(flag);
        });
        // $('#reload').click(function() {
        //     alert("cursor: pointer;");
        //     $('#iframe').attr('src', function() {
        //         return $(this)[0].src;
        //     });
        // });

        $("input[name='availability_setting']").click(function() {
            $('#availableDate').css('display', ($(this).val() === '1') ? 'block' : 'none');
        }); //.trigger('click');
        var _URL = window.URL;
        $("#thumbnailUpload").change(function(e) {

            var fileExtension = ['jpg', 'png'];
            if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                $(".image_error").show();
                $(".image_error").html("The thumbnail must be a file of type: png, jpg.")
                return false;
            }
            $(".image_error").hide();
            var file, img;
            // if ((file = this.files[0])) {
            //     img = new Image();
            //     // alert()
            //     img.onload = function () {
            //         alert("Width:" + this.width + "   Height: " + this.height);//this will give you image width and height and you can easily validate here....
            //         // alert(this.width )
            //         if(this.width != 1920 &&  this.height != 350){
            //             $(".image_error").show();
            //             $(".image_error").html("The thumbnail has invalid image dimensions.")
            //             // alert()
            //             return false
            //          } else {
            //             $(".image_error").hide();
            //          }
            //     };
            //     img.src = _URL.createObjectURL(file);
            // }


            var id = $('#thumbnailUpload').attr('data-id');
            var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
            var files = $('#thumbnailUpload')[0].files;
            if (files.length > 0) {
                var fd = new FormData();
                // Append data
                fd.append('file', files[0]);
                const fsize = files[0].size;
                // if (fsize >= 200000) {
                //     // toastr.error("File too Big, please select a file less than 200KB");
                //     $(".image_error").html("File too Big, please select a file less than 200KB.")
                //     return false;
                // }

                fd.append('_token', CSRF_TOKEN);
                fd.append('chapterId', id);
                // AJAX request
                $.ajax({
                    url: "{{ route('course.thumbnail') }}",
                    method: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response) {
                        $('#loader_section').hide();
                        // Hide error container
                        $('#err_file').removeClass('d-block');
                        $('#err_file').addClass('d-none');

                        if (response.success == 1) { // Uploaded successfully
                            // File preview
                            $('.imagePreview').show();
                            if (response.extension == 'jpg' || response.extension ==
                                'jpeg' || response.extension == 'png') {
                                $(".imagePreview").attr("src", response.filepath);
                                location.reload();
                            }
                        }
                    },
                    error: function(response) {
                        console.log("error : " + JSON.stringify(response));
                    }
                });
            } else {
                alert("Please select a file.");
            }
        });
        //Validate all chapter type Form
        if ($("#chapterInfoForm").length > 0) {
            $('#chapterInfoForm').validate({
                rules: {
                    title: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                    file_url: {
                        required: {
                            depends: function(elem) {
                                return ($("input[name=upload_type]").val() === '1' ||
                                    $("input[name=upload_type]").val() === '0' ||
                                    $("input[name=upload_type]").val() === '2' ||
                                    $("input[name=upload_type]").val() === '4')
                            }
                        },
                        youtubeURL: {
                            depends: function(elem) {
                                return $("input[name=upload_type]").val() === '1'
                            },
                        },
                        vimeoURL: {
                            depends: function(elem) {
                                return $("input[name=upload_type]").val() === '2'
                            },
                        },
                    },
                    // available_from: {
                    //     required: {
                    //         depends: function(elem) {
                    //             return ($("input[name=availability_setting]:checked").val() === '1')
                    //         }
                    //     },
                    // },
                    // available_till: {
                    //     required: {
                    //         depends: function(elem) {
                    //             return ($("input[name=availability_setting]:checked").val() === '1')
                    //         }
                    //     },
                    // },
                },
                messages: {
                    title: {
                        required: "The title field is required.",
                    },
                    // description: {
                    //     required: "The description field is required.",
                    // },
                    file_url: {
                        required: "URL is required",
                        url: true
                    },
                    // available_from: {
                    //     required: "Available From date is required",
                    // },
                    // available_till: {
                    //     required: "Available till date is required",
                    // },
                },
                submitHandler: function(form, event) {

                    let test1 = String($('#description').val());
                    let x = test1.replaceAll(/(<([^>]+)>)/gmi, "");
                    let q = x.replaceAll(/&nbsp;/gim, '').trim(" ").length;
                    if (test1 !== "undefined" && test1 == '' && ($('#description').summernote('isEmpty') || q <=
                            0)) {
                        event.preventDefault();
                        $("#desc_err").text('Please enter description').show();
                    } else {

                        $("#desc_err").hide();
                        form.submit();
                    }
                }

            });
        }
        /*****Bulk add from here*******/
        function myFunctionForAdd() {
            if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
                var url = $('#bulk_add_frm').attr('action');
                bulk_add_confirmation(url);
            }
            return false;
        }

        function bulk_add_confirmation(path) {
            $("#bulk_add_frm").attr('action', path);
            $("#bulk_add_conf").modal('show');
        }
        $(document).on("click", "#bluk_add_conf_yes", function() {
            $("#bulk_add_frm").submit();
        });
        /*****Bulk add End from here*******/

        $('button.accordion-button').click(function() {
            $(this).find('i').toggleClass('fas fa-caret-up fas fa-caret-down');
        });

        $(document).on("keyup", "#youtube_link", function() {
            console.log("1");
            setTimeout(function() {
                let yt_url = $("#youtube_link").val();
                if (yt_url != '' && yt_url != null && yt_url != undefined) {
                    $.ajax({
                        url: "{{ url('backoffice/video-title') }}",
                        type: "POST",
                        data: {
                            url: yt_url
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}");
                        },
                        success: function(data, textStatus, xhr) {
                            if (data.status == 1) {
                                $("#youtube_title").text(data.msg).val(data.msg);
                            } else {
                                Swal.fire(data.msg);
                                $(".createVideo").modal("hide")
                            }
                        }
                    });
                }
            }, 500);
        });
        $(document).on("keyup", "#vimeo_link", function() {
            console.log("1");
            setTimeout(function() {
                let vim_url = $("#vimeo_link").val();
                if (vim_url != '' && vim_url != null && vim_url != undefined) {
                    $.ajax({
                        url: "{{ url('backoffice/video-title') }}",
                        type: "POST",
                        data: {
                            url: vim_url
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}");
                        },
                        success: function(data, textStatus, xhr) {
                            if (data.status == 1) {
                                $("#vimeo_title").text(data.msg).val(data.msg);
                            } else {
                                Swal.fire(data.msg);
                                $(".createVideo").modal("hide")
                            }
                        },
                        error: function(xhr, error, errorThrown) {
                            $("#vimeo_title").text("").val("");
                            // Swal.fire(data.msg);
                        }
                    });
                }
            }, 500);
        });

        $(document).on("change", "#course-dropdown", function() {
            var course_id = this.value;
            $(".createBuySell #plan_dropdown").html("");
            $.ajax({
                url: "{{ url('get-plan-by-course') }}",
                type: "POST",
                data: {
                    course_id: course_id,
                    _token: "{{ csrf_token() }}",
                },
                dataType: "json",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(result) {
                    console.log(result);
                    $('#loader_section').hide();
                    $(".createBuySell #plan_dropdown").append(
                        '<option value="">Select Plan</option>'
                    );
                    $.each(result.plans, function(key, value) {
                        $(".createBuySell #plan_dropdown").append(
                            '<option value="' +
                            value.id +
                            '">' +
                            value.plan_name + "( ₹" + value.final_payable_price +
                            " ) </option>"
                        );
                    });
                },
            });
        });

        function isNumericKey(event) {

            // javascript code for duration and hours
            const charCode = (event.which) ? event.which : event.keyCode;
            return !(charCode > 31 && (charCode < 48 || charCode > 57));
        }

        function restrictToTwoDigits(event) { // javascript code for duration and hours
            const inputValue = event.target.value;
            if (inputValue.length > 3) {
                event.target.value = inputValue.slice(0, 3);
            }
        }

        function restrictToMinuteDigits(event) { // javascript code for duration and hours
            const inputValue = event.target.value;
            if (inputValue.length > 3) {
                event.target.value = inputValue.slice(0, 3);
            }
        }

        $(document).ready(function() {
            $('.summernote-editor-new').summernote({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['forecolor', 'backcolor']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['picture', 'table']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        });
    </script>
@endsection
