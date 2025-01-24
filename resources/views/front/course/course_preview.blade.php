<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name') }} @isset($title)
            | {{ 'title' }}
        @endisset
    </title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">

    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">


    {{-- <link rel="icon" type="image/x-icon"
        href="{{ !empty(config('settings.backend_favicon')) ? Storage::url('public/' . config('settings.backend_favicon')) : favicon_default() }}"> --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('front/img/favicon.png') }}">
    <!-- Custom CSS-->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/custom.css') }}?var={{ time() }}">
    <style>
        .error-message {
            /* position: absolute; */
            top: 0;
            left: 0;
            width: 100%;
            height: 850px;
            background: black;
            color: white;
            text-align: center;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        #downloadPdf {
            margin-bottom: 15px;
        }

        .test-box button {
            margin-left: 20px;
        }

        .test-box {
            display: flex;
            justify-content: end;
        }

        .test-box input {
            position: relative;
            margin: 5px 5px 12px 0;
        }
    </style>

    {{-- <!-- Pusher JS-->
    <script src="{{ asset('front/js/pusher/pusher.min.js') }}"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}'
        });

        var channel = pusher.subscribe('youtube-video-watched');
        channel.bind('youtube-video-event', function(data) {
            alert(JSON.stringify(data));
        });
    </script> --}}
    @vite(['resources/js/app.js'])

</head>

<body class="hold-transition sidebar-mini course-builder-sidebar">
    <div class="wrapper">
        <aside class="main-sidebar sidebar-light-info elevation-4 cust-design-sidebar front">
            <div class="brand">
                <div class="back">
                    @if (Auth::check())
                        {{-- check if admin is Intructure --}}
                        @if (
                            $course->instructor_id > 0 &&
                                auth()->user()->hasRole(App\Models\User::INSTRUCTOR) &&
                                $course->instructor_id != auth()->guard('learner')->id())
                            @php
                                $redirectUrl = 'history.go(-1)';
                            @endphp
                        @else
                            @php
                                $redirectUrl = route('courses.index');
                            @endphp
                        @endif
                    @elseif (Auth::guard('learner')->check())
                        @php
                            $redirectUrl = route('my.course');
                        @endphp
                    @else
                        @php
                            $redirectUrl = 'history.go(-1)';
                        @endphp
                    @endif
                    <a href="{{ $redirectUrl }}">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Courses
                    </a>
                </div>
            </div>
            <div class="builder-sidebar px-2">
                <div class="chapter-content mt-4"
                    style="max-height: calc(100vh - 85px) !important;height: calc(100vh - 85px) !important;">
                    @if (isset($courseChapters) && !empty($courseChapters) && count($courseChapters) > 0)
                        <div class="chapterMainDiv">
                            @php
                                $count = 1;
                                // dump($courseChapters->first())
                            @endphp
                            {{-- @dd($courseChapters->toArray()); --}}
                            @foreach ($courseChapters as $chapterKey => $chapterValue)
                                @php
                                    $displayFlag = 0;
                                    $display = 'false';
                                @endphp

                                @if (Helper::setActiveCourse($chapterID) > 0)
                                    @if (Helper::setActiveCourse($chapterID) == $chapterValue->id)
                                        @php
                                            $displayFlag = 1;
                                            $display = 'true';
                                        @endphp
                                    @endif
                                @endif
                                <div class="card ">


                                    <div class="card-header {{ Helper::setTypeWiseTextBox($chapterValue->id, 1) == 1 ? 'allow' : '' }}"
                                        id="chapter_{{ $chapterValue->id }}">
                                        <h5 class="mb-0 ">
                                            <div class="d-flex justify-content-between align-items-center collapsed chapter_class"
                                                data-toggle="collapse"
                                                data-target="#chapterCollapse_{{ $chapterValue->id }}"
                                                aria-expanded="{{ $display }}">
                                                @if (Helper::setTypeWiseTextBox($chapterValue->id, 1) == 1)
                                                    <a class="sidebar-titleName collapsed  {{ $chapterID == $chapterValue->id ? ' active' : '' }}"
                                                        href="{{ route('course.preview', ['slug' => $slug, 'chapterId' => $chapterValue->id]) }}">
                                                        @if (isset($chapterValue->userCourseProgress) &&
                                                                !empty($chapterValue->userCourseProgress) &&
                                                                $chapterValue->userCourseProgress->is_completed == 1 &&
                                                                $chapterValue->userCourseProgress->chapter_id == $chapterValue->id)
                                                            <i class="fa fa-check-circle text-successss"></i>
                                                        @endif
                                                        {{ Helper::setTypeWiseIcon(trim($chapterValue->asset_type)) }}
                                                        {{ isset($chapterValue->asset_type) && $chapterValue->asset_type == 7 ? Helper::getCourseTitle($chapterValue->title) : (isset($chapterValue) && !empty($chapterValue->title) ? ucfirst($chapterValue->title) : '') }}
                                                    </a>
                                                @else
                                                    <a class="sidebar-titleName collapsed " data-toggle="collapse"
                                                        data-target="#chapterCollapse_{{ $chapterValue->id }}"
                                                        aria-expanded="{{ $display }}">
                                                        {{ Helper::setTypeWiseIcon(trim($chapterValue->asset_type)) }}
                                                        {{ isset($chapterValue->asset_type) && $chapterValue->asset_type == 7 ? Helper::getCourseTitle($chapterValue->title) : (isset($chapterValue) && !empty($chapterValue->title) ? ucfirst($chapterValue->title) : '') }}
                                                    </a>
                                                @endif
                                                <span class="sidebar-title">
                                                    <a class="btn btn-link text-left collapsed" data-toggle="collapse"
                                                        data-target="#chapterCollapse_{{ $chapterValue->id }}"
                                                        aria-expanded="{{ $display }}">

                                                        @if ($displayFlag && $displayFlag == 1)
                                                            <i class="fas fa-caret-up  float-right "></i>
                                                        @else
                                                            <i class="fas fa-caret-down float-right "></i>
                                                        @endif
                                                    </a>
                                                </span>
                                            </div>
                                        </h5>
                                    </div>
                                    @if (count($chapterValue->childrens) > 0)
                                        @php
                                            $show = '';
                                            // dump($chapterValue->childrens)
                                        @endphp
                                        @foreach ($chapterValue->childrens as $childKey => $childVal)
                                            @if (Helper::setActiveCourse($chapterID) > 0)
                                                @if (Helper::setActiveCourse($chapterID) == $childVal->parent_id)
                                                    @php
                                                        $show = ' show';

                                                    @endphp
                                                @endif
                                            @endif


                                            <h6 class="submenu submenu_{{ $count }} mb-0 inner-title {{ $chapterID == $childVal->id ? ' active' : '' }} {{ Helper::setTypeWiseTextBox($childVal->id, 1) == 1 ? 'allow ' : '' }}collapse{{ $show }}"
                                                id="chapterCollapse_{{ $childVal->parent_id }}">
                                                <div class="d-flex justify-content-between align-items-center">

                                                    @if (Helper::setTypeWiseTextBox($childVal->id, 1) == 1)
                                                        <a class="btn btn-link text-left {{ $chapterID == $childVal->id ? ' active' : '' }}"
                                                            href="{{ route('course.preview', ['slug' => $slug, 'chapterId' => $childVal->id]) }}">
                                                            @if (isset($childVal->userCourseProgress) &&
                                                                    !empty($childVal->userCourseProgress) &&
                                                                    $childVal->userCourseProgress->is_completed == 1 &&
                                                                    $childVal->userCourseProgress->chapter_id == $childVal->id)
                                                                <i class="fa fa-check-circle text-success"></i>
                                                            @endif
                                                            {{-- <div class="message_ticker_fount {{ strlen($childVal->title) > 35 ? 'long-title' : '' }}"> --}}
                                                            <div class="" title="{{ $childVal->title }}">
                                                                {{ Helper::setTypeWiseIcon(trim($childVal->asset_type)) }}
                                                                <span class="long_text long_text_{{ $childKey }}"
                                                                    data-log={{ $childKey }}>{{ isset($childVal->asset_type) && $childVal->asset_type == 7 ? Helper::getCourseTitle($childVal->title) : $childVal->title }}</span>
                                                            </div>
                                                        </a>
                                                    @else
                                                        <a class="btn btn-link text-left">
                                                            {{ Helper::setTypeWiseIcon(trim($childVal->asset_type)) }}
                                                            {{ isset($childVal->asset_type) && $childVal->asset_type == 7 ? Helper::getCourseTitle($childVal->title) : $childVal->title }}
                                                        </a>
                                                    @endif

                                                </div>
                                            </h6>
                                            @php
                                                $count++;
                                            @endphp
                                        @endforeach
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </aside>
        <!-- Main content -->
        <div class="content-wrapper">

            @if (isset($course) && !empty($course))
                <div class="d-flex justify-content-between align-items-center preview_course_heading">
                    <h3>{{ $course->title ?? '' }}</h3>


                    <div class="discussion-info" style="display: flex; align-items: center; gap: 10px;">

                        <div class="start-leason d-flex align-items-center">
                            @if (isset($course->id) && !empty($course->id))
                                <?php $courseID = $course->id; ?>
                            @endif


                            @if (isset($course->public_forum_status) && $course->public_forum_status == 1)
                                <a target="_blank"
                                    href="{{ route('courses.public-forum-front', ['id' => $courseID]) }}"
                                    class="btn btn-primary">Discussion</a>
                            @endif

                        </div>

                        @if ($chapterData->count() != 0)
                            <div class="customePagination">

                                <button href="#" class="next btn btn-secondary px-2 py-1 mr-1 float-right"
                                    style="background: #40ab59;
                            border: #40ab59;">Next
                                    &#8250;</button>
                                <button href="#"
                                    style="background-color: #f2751f;
                            border: #f2751f;"
                                    class="previous btn btn-primary px-2 py-1 mr-1 float-right">&#8249;
                                    Previous</button>
                            </div>
                        @endif

                    </div>
                </div>
            @endif

            <div class="row d-flex justify-content-center custom_box" style="margin-right: 0;margin-left: 0;">

                @if ($chapterData->count() == 0)

                    <div class="row justify-content-center align-items-center mt-5 p-5">
                        <h3 class="text text-primary">No record found</h3>
                    </div>
                @else
                    <div class="col-10 bg-design">
                        <form method="POST" action="{{ route('all.tracker') }}">
                            @csrf

                            {{-- @php
                            dd(auth()->user())
                        @endphp --}}
                            {{-- {{ dd($chapter->userCourseProgress->is_completed) }} --}}
                            @if (auth()->user() == null)
                                @php
                                    $asset_type = Helper::chapterStatus(request()->route('chapterId'));

                                @endphp
                                {{-- @if (isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress))

                                        @if (isset($chapter->userCourseProgress->is_completed) && !empty($chapter->userCourseProgress->is_completed) && $chapter->userCourseProgress->is_completed != 1)

                                            @if ($asset_type->asset_type != 7)
                                                <button type="submit"
                                                    class="complete btn btn-primary px-2 py-1 mr-1 float-right">
                                                    Mark as Complete
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        @if ($asset_type->asset_type != 7)
                                            <button type="submit" class="complete btn btn-primary px-2 py-1 mr-1 float-right">
                                                Mark as Complete
                                            </button>
                                        @endif
                                    @endif
                                    @endif --}}
                                <div class="test-box">
                                    @if ($asset_type->asset_type == 1)
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="flexSwitchCheckChecked">
                                        <label class="form-check-label" for="flexSwitchCheckChecked">Repeatedly
                                            play</label>
                                    @endif
                                    @if (isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress))
                                        @if ($chapter->userCourseProgress->is_completed != 1)
                                            @if ($asset_type->asset_type != 7)
                                                <button type="submit"
                                                    style="background-color: #ff702f;border: #ff702f;"
                                                    class="complete btn btn-primary px-2 py-1 mr-1 float-right">
                                                    Mark as Complete
                                                </button>
                                            @endif
                                        @endif
                                    @else
                                        @if ($asset_type->asset_type != 7)
                                            <button type="submit" style="background-color: #ff702f;border: #ff702f;"
                                                class="complete btn btn-primary px-2 py-1 mr-1 float-right">
                                                Mark as Complete
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            @endif


                            {{-- <button type="submit" class="complete btn btn-primary px-2 py-1 mr-1 float-right">
                            {{ isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress) ? $chapter->userCourseProgress->is_completed==1 ? "" : "Mark as Complete" : "Mark as Complete" }}</button> --}}
                            {{-- @dd($chapter->toArray()) --}}
                            <input type="hidden" name="course_id" id="course_id_hidden"
                                value="{{ $course->id }}">
                            <input type="hidden" name="learner_id_hidden" id="learner_id_hidden"
                                value="{{ auth()->guard('learner')->id() ?? '' }}">
                            <input type="hidden" name="chapter_id_hidden" id="chapter_id_hidden"
                                value="{{ $chapter->id ?? '' }}">
                            <input type="hidden" name="chapter_coin_hidden" id="chapter_coin_hidden"
                                value="{{ $chapterData->whole_video_coin ?? '' }}">
                            <input type="hidden" name="chapters_id_hidden" id="chapters_id_hidden"
                                value="{{ $chapterData->id ?? '' }}">
                            {{-- <input type="hidden" name="is_completed" id="is_completed"
                                value="{{ isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress) ? $chapter->userCourseProgress->is_completed : 0 }}"> --}}
                            <input type="hidden" name="is_completed" id="is_completed"
                                value="{{ isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress) ? $chapter->userCourseProgress->is_completed : 0 }}">
                            <input type="hidden" name="watched_time" id="watched_time"
                                value="{{ isset($chapter->userCourseProgress) && !empty($chapter->userCourseProgress) ? $chapter->userCourseProgress->watched_time : 0 }}">
                            {{-- <input type="hidden" name="autoplay" id="autoplay"
                                value="{{ isset($chapter->chapterInfo->autoplay) && $chapter->chapterInfo->autoplay == 1 ? '1' : '0' }}"> --}}
                        </form>
                        @php
                            $type = Helper::getTypeBasedOnChapterId($chapterData->chapter_id);
                            $upload_type = $chapterData->upload_type;

                            function splitStringIntoChunks($string, $chunkSize = 5)
                            {
                                $delimiter = strpos($string, '_') !== false ? '_' : ' ';
                                $words = explode($delimiter, $string);
                                $chunkedWords = array_chunk($words, $chunkSize);
                                $finalArray = array_map(function ($chunk) use ($delimiter) {
                                    return implode($delimiter, $chunk);
                                }, $chunkedWords);
                                return $finalArray;
                            }

                            $main_title = splitStringIntoChunks(ucfirst($chapter->title), 1.5);
                        @endphp

                        {{-- class="message_ticker custom_ticker_width {{ strlen($chapter->title) > 60 ? 'long-title' : '' }}"> --}}
                        <div class="form-group">

                            <div class="msg-track">

                                <h3>

                                    {{-- <marquee>{{ isset($chapter->asset_type) && $chapter->asset_type == 7 ? Helper::getCourseTitle($chapter->title) : (isset($chapter) && !empty($chapter->title) ? ucfirst($chapter->title) : '') }}
                                    </marquee></h3><br /> --}}
                                    {{-- {{ isset($chapter->asset_type) && $chapter->asset_type == 7 ? Helper::getCourseTitle($chapter->title) : (isset($chapter) && !empty($chapter->title) ? $chapter->title : '') }} --}}

                                    @if (isset($chapter->asset_type) && $chapter->asset_type == 7)
                                        {{ Helper::getCourseTitle($chapter->title) }}
                                    @else
                                        @if (isset($chapter) && !empty($chapter->title))
                                            @foreach ($main_title as $main_titles)
                                                {{ $main_titles }}
                                            @endforeach
                                        @endif
                                    @endif

                                </h3><br />

                            </div>
                            {{-- {{ strip_tags($chapterData->description) }} --}}
                            {{-- {!! $chapterData->description !!} --}}
                            {!! htmlspecialchars_decode($chapterData->description) !!}
                        </div>
                        @if ($type == App\Models\Chapter::SELL_BUY)
                            @includeIf('front.course.sellbuy', [
                                'course' => get_course_detail_for_checkout(
                                    $chapterData->title,
                                    $chapterData->plan_id),
                                'plan_id' => $chapterData->plan_id,
                                'isShow' => 1,
                                $chapterData->timer,
                                $course,
                                $chapter->id,
                            ])
                        @endif
                        @if ($type == App\Models\Chapter::FLAG_IMAGE)
                            <img src="{{ isset($chapter->media) && !empty($chapter->media) && isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url($chapter->media->path) : '' }}"
                                style="height: 70%;width: 70%;">
                        @endif
                        @if (
                            $type == App\Models\Chapter::FLAG_LINK ||
                                $type == App\Models\Chapter::FLAG_PDF ||
                                $type == App\Models\Chapter::FLAG_VIDEO ||
                                $type == App\Models\Chapter::FLAG_AUDIO ||
                                $type == App\Models\Chapter::FLAG_FILE)
                            {{-- ******** you tube nad Vimeo File URL *********** --}}
                            @if ($type == App\Models\Chapter::FLAG_VIDEO)

                                @if ($upload_type == App\Models\Chapter::VIMEO || $upload_type == App\Models\Chapter::YOUTUBE)
                                    <div class="form-group">
                                        <input type="hidden" class="form-control"
                                            value="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : '' }}"
                                            name="file_url" id="file_url">
                                    </div>
                                @endif
                            @endif

                            {{-- ******** Uploded File URL *********** --}}
                            @if ($type == App\Models\Chapter::FLAG_FILE)

                                @if (isset($chapter->media_id) && $chapter->media->path && $chapter->media_id != null)
                                    <a href="{{ isset($chapter->media->path) && !empty($chapter->media->path) ? Storage::url($chapter->media->path) : '' }}"
                                        download><i class="fa fa-download" aria-hidden="true"></i></a>
                                @endif
                            @endif

                            {{-- ******** Uploded File LINK  *********** --}}
                            @if ($type == App\Models\Chapter::FLAG_LINK)
                                <!DOCTYPE html>
                                <html>

                                <body>
                                    <object width="100%" height="900px"
                                        data="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : '' }}"></object>
                                </body>

                                </html>
                                <a target="_blank"
                                    href="{{ isset($chapter->file_url) && !empty($chapter->file_url) ? $chapter->file_url : '' }}">
                                    @if ($chapter->image)
                                        <div class="image-wrapper" style="width: 150px;">
                                            <img src='{{ url("storage/chapter_link/$chapter->image") }}'
                                                alt="Chapter Image" width="150">
                                        </div>
                                    @else
                                        <button type="button"
                                            class="btn btn-primary">{{ $chapter->title ?? '' }}</button>
                                    @endif
                                </a>

                            @endif

                            {{-- ******** PDF SECTION *********** --}}

                            @if ($type == App\Models\Chapter::FLAG_PDF)

                                @if (isset($chapter->media_id) && $chapter->media->path && $chapter->media_id != null)
                                    @php
                                        $pdfUrl =
                                            isset($chapter->media->path) && !empty($chapter->media->path)
                                                ? Storage::url($chapter->media->path)
                                                : '';
                                    @endphp
                                @else
                                    @php
                                        $pdfUrl =
                                            isset($chapter->file_url) && !empty($chapter->file_url)
                                                ? $chapter->file_url
                                                : '';
                                    @endphp
                                @endif

                                @if ($chapterData->allow_download == 1)
                                    {{-- <form id="download_pdf" method="GET"
                                            action="{{ url('backoffice/pdf_download/' . $chapter->id) }}"
                    class="my-4 text-right"> --}}
                                    {{-- <button class="btn btn-primary text-white text-right">Download</button> --}}
                                    {{-- </form> --}}
                                    <a href="{{ url($pdfUrl) }}" download id="downloadPdf"
                                        class="btn btn-primary text-white text-right">Download</a>
                                @endif

                                <!DOCTYPE html>
                                <html>

                                <body>
                                    {{-- <div style="position: relative;"> --}}
                                    <div>
                                        <div id="pdf-viewer"></div>
                                        <div id="loader_section" style="display: none">
                                            {{-- <div id="loader" style="!important; background-color: white;"> --}}
                                            {{-- <div id="loader" style="position: fixed;
                                            width: 100%;
                                            height: 0%;!important; background-color: white;   ">
                                                <div id="spinner"></div>
                                            </div> --}}
                                        </div>

                                        {{-- <object id="pdfviewer" width="100%" height="850px" frameborder="0"
                                            allowtransparency="true"
                                            data="{{ isset($pdfUrl) ? $pdfUrl : '' }}#toolbar=0&navpanes=0&scrollbar=0"></object> --}}
                                        <object id="pdfviewer" width="100%" height="850px" type="application/pdf"
                                            data="{{ isset($pdfUrl) ? $pdfUrl : '' }}#toolbar=0&navpanes=0&scrollbar=0"></object>

                                        <div id="overlay"
                                            style="position: absolute; top: 20%; left: 0; width: 80%; height: 850px; background-color: transparent;">
                                        </div>
                                    </div>
                                </body>

                                </html>

                                {{-- ******** VIDEO SECTION *********** --}}
                            @elseif ($type == App\Models\Chapter::FLAG_VIDEO)
                                {{-- ******** YOU TUBE AND VIMEO SECTION *********** --}}
                                @if ((isset($upload_type) && $upload_type == App\Models\Chapter::YOUTUBE) || $upload_type == App\Models\Chapter::VIMEO)
                                    @if ($upload_type == App\Models\Chapter::YOUTUBE)
                                        <div id="player"></div> <!-- youtube -->
                                        <div id="videoContainer"></div><!-- youtube -->
                                    @else
                                        <!-- vimeo -->
                                        <iframe id="videoObject" type="text/html" width="100%" height="850px"
                                            frameborder="0" controlsList="nodownload"></iframe>
                                        <div id="videoContainer"></div>
                                    @endif
                                @else
                                    @if (isset($chapter->media->videoId) && $chapter->media->videoId != null)
                                        <!-- vdochiper -->
                                        @includeIf('admin.layouts.partials.video.iframe', [
                                            'videoId' => $chapter->media->videoId,
                                            'preview' => 1,
                                            'watched_time' =>
                                                isset($chapter->userCourseProgress) &&
                                                !empty($chapter->userCourseProgress)
                                                    ? $chapter->userCourseProgress->watched_time
                                                    : 0,
                                        ])
                                    @endif
                                @endif
                                {{-- ******** AUDIO SECTION *********** --}}
                            @elseif ($type == App\Models\Chapter::FLAG_AUDIO)
                                @if (isset($chapter->media->videoId) && $chapter->media->videoId != null)
                                    @includeIf('admin.layouts.partials.video.iframe', [
                                        'videoId' => $chapter->media->videoId,
                                        'preview' => 1,
                                        'user_course_id' =>
                                            isset($chapter->userCourseProgress) &&
                                            !empty($chapter->userCourseProgress)
                                                ? $chapter->userCourseProgress->id
                                                : 0,
                                        'watched_time' =>
                                            isset($chapter->userCourseProgress) &&
                                            !empty($chapter->userCourseProgress)
                                                ? $chapter->userCourseProgress->watched_time
                                                : 0,
                                    ])
                                @endif
                            @endif

                        @endif
                @endif
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            // $('.long_text').hover(
            //     function() {
            //         var id = $(this).attr("data-log");
            //         var content = $(".long_text_" + id).html();
            //         if (!content.includes('<marquee>')) {
            //             $(".long_text_" + id).html('<marquee>' + content + '</marquee>');
            //         }
            //     },
            //     function() {
            //         var id = $(this).attr("data-log");
            //         var content = $(".long_text_" + id).find('marquee').html();
            //         $(".long_text_" + id).html(content);
            //     }
            // );

            // $('.msg-track').hover(
            //     function() {

            //         var content = $(".msg-track").html();
            //         if (!content.includes('<marquee>')) {
            //             $(".msg-track").html('<marquee>' + content + '</marquee>');
            //         }
            //     },
            //     function() {
            //         var content = $(".msg-track").find('marquee').html();
            //         $(".msg-track").html(content);
            //     }
            // );

            // Select the overlay div
            var overlay = document.getElementById('overlay');

            // Add event listener for right-click
            overlay.addEventListener('contextmenu', function(e) {
                // Prevent default right-click behavior
                e.preventDefault();
            });
        });

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
    </script>
    @if (!$chapterID)
        <script>
            $(document).ready(function() {




                var active = $(".chapterMainDiv .allow");
                if (active.length > 0) {
                    var allows = $(active).closest('.allow');
                    var next = allows.first();
                    console.log(next);
                    if (next.length > 0 && next.find('a[href]').length) {
                        var URL = next.find('a').attr('href');
                        console.log(URL);
                        // alert()
                        if (URL) {
                            active.find('.active').removeClass('active');
                            next.find('a').addClass("active");
                            location.href = URL;
                        }
                    } else {
                        var a = $(allows.parent().next()).closest('.card').find('.allow a').first();
                        var URL = a.attr('href');
                        console.log(URL);
                        if (URL) {
                            active.find('.active').removeClass('active');
                            a.addClass("active");
                            location.href = URL;
                        }
                    }

                }
            });
        </script>
    @endif
    <script>
        // Disable inspect element
        // $(document).bind("contextmenu",function(e) {
        //   e.preventDefault();
        // });
        // $(document).keydown(function(e){
        //   if(e.which === 123){
        //     return false;
        // }
        // });
        var next_url_auto = "";
        $(document).ready(function() {


            if ($('.chapterMainDiv .allow').first().find('.active').length > 0) {
                $('.previous').prop('disabled', true);
            }

            // Check if last video is active
            if ($('.chapterMainDiv .allow').last().find('.active').length > 0) {
                $('.next').prop('disabled', true);
            }


            $('.next').click(function(e) {
                $('#loader_section').show();
                e.preventDefault();

                var active = $(".chapterMainDiv .allow");
                if (active.length > 0) {
                    var allows = active.find('.active').closest('.allow');
                    var next = allows.next();

                    if (next.length > 0 && next.find('a[href]').length) {
                        var URL = next.find('a').attr('href');
                        next_url_auto = URL;
                        if (URL) {
                            active.find('.active').removeClass('active');
                            next.find('a').addClass("active");
                            location.href = URL;
                        }
                    } else {
                        var a = $(allows.parent().next()).closest('.card').find('.allow a').first();
                        var URL = a.attr('href');
                        if (URL) {
                            active.find('.active').removeClass('active');
                            a.addClass("active");
                            location.href = URL;
                        } else {
                            $('#loader_section').hide();
                        }
                    }

                    // Disable next button if last video is playing
                    if (!next.find('a[href]').length) {
                        $('.next').prop('disabled', true);
                    } else {
                        $('.previous').prop('disabled', false);
                    }
                }
            });

            $('.previous').click(function(e) {
                e.preventDefault();
                var active = $(".chapterMainDiv .allow");
                if (active.length > 0) {
                    var allows = active.find('.active').closest('.allow');
                    var prev = allows.prev();

                    if (prev.length > 0 && prev.find('a[href]').length) {
                        var URL = prev.find('a').attr('href');
                        if (URL) {
                            active.find('.active').removeClass('active');
                            prev.find('a').addClass("active");
                            location.href = URL;
                        }
                    } else {
                        var a = $(allows.parent().prev()).closest('.card').find('.allow a').last();
                        var URL = a.attr('href');
                        if (URL) {
                            active.find('.active').removeClass('active');
                            a.addClass("active");
                            location.href = URL;
                        }
                    }

                    // Disable previous button if first video is playing
                    if (!prev.find('a[href]').length) {
                        $('.previous').prop('disabled', true);
                    } else {
                        $('.next').prop('disabled', false);
                    }
                }
            });

        });


        /* $('a.btn.btn-link.text-left.collapsed').click(function() {
            $(this).find('i').toggleClass('fas fa-caret-up fas fa-caret-down');
        }); */
        $('.chapter_class').click(function() {
            let caret = $(this).find('.sidebar-title > a > i');
            if ($(caret).hasClass('fa-caret-up')) {
                $(caret).removeClass('fa-caret-up');
                $(caret).addClass('fa-caret-down');
            } else {
                $(caret).addClass('fa-caret-up');
                $(caret).removeClass('fa-caret-down');
            }
        });
    </script>


    {{-- vimeo --}}

    {{-- @dd($upload_type) --}}
    @if (isset($upload_type) && $upload_type == App\Models\Chapter::VIMEO)
        <script src="https://player.vimeo.com/api/player.js"></script>

        <script>
            $(document).ready(function() {

                // Make an AJAX request to the Node server
                // var serverEndpoint =
                //     '{{ env('NODE_SERVER_URL') }}'; // Replace YOUR_PORT_NUMBER with the actual port number of your Node.js server
                // alert(serverEndpoint);
                // $.ajax({
                //     url: "{{ route('server-status') }}",
                //     method: 'GET',
                //     success: function(response) {
                //         console.log(response);
                //         if (response.status === 'online') {
                parseUrl();

                //Getting Vimeo video duration
                var seek_to_time = $("#watched_time").val();
                // console.log(seek_to_time);// get watched_time from DB
                var iframe = document.getElementById('videoObject');
                var player = new Vimeo.Player(iframe);
                console.log(iframe);


                // player.on('play', function(data) {
                //     // var totalVideoDuration = Math.floor(data.duration);
                //     // videoCurrentTime = Math.floor(data.seconds);
                //     // console.log(videoCurrentTime);
                //     // updateProgressData(videoCurrentTime);
                // });

                // player.on('pause', function(data) {
                //     // var totalVideoDuration = Math.floor(data.duration);
                //     videoCurrentTime = Math.floor(data.seconds);
                //     // console.log("pause", videoCurrentTime);
                //     // console.log("timeupdate", videoCurrentTime);
                //     updateProgressData(videoCurrentTime);
                // });

                var flag = 0;
                var isCompleted = $("#is_completed").val();
                if (isCompleted == '') {
                    isCompleted = 0;
                }
                player.on('timeupdate', function(data) {
                    var totalVideoDuration = Math.floor(data.duration);
                    var videoCurrentTime = Math.floor(data.seconds);
                    var limitDuration = (totalVideoDuration / 100) *
                        95; // Video completed 95%
                    // console.log("timeupdate", totalVideoDuration);
                    // console.log("isCompleted", isCompleted);
                    if (totalVideoDuration == videoCurrentTime) {

                        location.href = next_url_auto;
                    }
                    if (videoCurrentTime >= limitDuration) {
                        isCompleted = 1;
                        updateProgressData(totalVideoDuration, videoCurrentTime, isCompleted);
                        $("#is_completed").val(1);
                    } else {
                        updateProgressData(totalVideoDuration, videoCurrentTime);
                        // Call first time while click on 'Play' button.
                        // if(flag == 0){
                        //     updateProgressData(totalVideoDuration, videoCurrentTime, isCompleted);
                        //     flag = 1;
                        // }
                    }
                });

                player.setCurrentTime(seek_to_time);
                // Vimeo code ended..!!

                function parseUrl() {
                    var val = document.getElementById('file_url').value;
                    var vimeoRegex = /(?:vimeo)\.com.*(?:videos|video|channels|)\/([\d]+)/i;
                    var parsed = val.match(vimeoRegex);
                    $('#videoObject').attr('src', "//player.vimeo.com/video/" + parsed[1]);
                };
                // } else if (response.status === 'offline') {
                //     console.log('Node server is not running');
                //     // Server is not running, perform any additional actions or show appropriate messages
                //     var vimeoIframe = document.getElementById('videoObject');
                //     vimeoIframe.style.display = 'none'; // Hide the Vimeo iframe

                //     var errorMessage = document.createElement('div');
                //     errorMessage.className = 'error-message';
                //     errorMessage.textContent = 'Socket server not started';
                //     vimeoIframe.parentNode.insertBefore(errorMessage, vimeoIframe.nextSibling);
                // }


                // },
                // error: function() {

                // }
                // });
            });

            //                 function disableVideoPlayer() {
            //     var playerElement = document.getElementById('your-player-element'); // Replace with your player element ID
            //     playerElement.classList.add('disabled');
            //   }

            // function displayErrorMessage() {
            //     var vimeoIframe = document.getElementById('videoObject');

            //                     var player = new Vimeo.Player(vimeoIframe);

            //                     player.on('error', function(error) {
            //                     ErrorMessage('Something went wrong while loading the video.');
            //                     });
            //     // var iframe = document.getElementById('videoObject'); // Replace with your Vimeo iframe ID
            //     // iframe.style.background = 'black'; // Set the background color of the iframe to black

            //     // var errorMessage = document.createElement('div');
            //     // errorMessage.className = 'error-message';
            //     // errorMessage.textContent = message;
            //     // iframe.parentNode.insertBefore(errorMessage, iframe); // Insert the error message before the iframe
            //     // var vimeoIframe = document.getElementById('videoObject');
            //     // vimeoIframe.src = 'https://player.vimeo.com/video/INVALID_VIDEO_ID';
            //     // vimeoIframe.style.pointerEvents = 'none';
            //   }

            // function ErrorMessage(message) {
            //     var vimeoIframe = document.getElementById('videoObject');
            //     vimeoIframe.style.display = 'none'; // Hide the Vimeo iframe

            //     var errorMessage = document.createElement('div');
            //     errorMessage.className = 'error-message';
            //     errorMessage.textContent = message;
            //     vimeoIframe.parentNode.insertBefore(errorMessage, vimeoIframe);
            // }
        </script>
    @endif

    {{-- youtube --}}
    @if (isset($upload_type) && $upload_type == App\Models\Chapter::YOUTUBE)
        <script>
            function getYoutubeVideoId() {
                var url = $('#file_url').val();
                var cnt = 0;
                if (url != undefined || url != '') {
                    var regExp = /^[a-zA-Z].*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                    var match = url.match(regExp);
                    return match[2];
                }
            }
            // 2. This code loads the IFrame Player API code asynchronously.
            var tag = document.createElement('script');

            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            // 3. This function creates an <iframe> (and YouTube player)
            //    after the API code downloads.
            var player;
            var seek_to_time = $("#watched_time").val();

            function onYouTubeIframeAPIReady() {
                player = new YT.Player('player', {
                    height: '850',
                    width: '100%',
                    videoId: getYoutubeVideoId(),
                    playerVars: {
                        'playsinline': 1,
                        'start': seek_to_time, // Video will play from this second.
                        rel: 0,
                    },
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });
            }

            // 4. The API will call this function when the video player is ready.
            function onPlayerReady(event) {

                // if (document.getElementById('autoplay').value == '1') {
                //     // console.log(document.getElementById('autoplay').value);
                //     event.target.playVideo();
                // }
            }

            // 5. The API calls this function when the player's state changes.
            //    The function indicates that when playing a video (state=1),
            //    the player should play for six seconds and then stop.
            var done = false;

            function onPlayerStateChange(event) {
                commonForWebSocket(event.data);
            }

            function commonForWebSocket(playerStatus) {
                var totalVideoDuration = player.getDuration();
                var videoCurrentTime = 0;
                var isCompleted = $("#is_completed").val();;
                var flag = 0;

                if (totalVideoDuration != '') {
                    if (playerStatus == -1) {
                        // unstarted

                    } else if (playerStatus == 0) {
                        // ended
                        videoCurrentTime = totalVideoDuration;
                        isCompleted = 1;
                        updateProgressData(totalVideoDuration, videoCurrentTime, isCompleted);

                    } else if (playerStatus == 1) {
                        // playing
                        videoCurrentTime = Math.floor(player.getCurrentTime()); // Current video time in second
                        var limitDuration = (totalVideoDuration / 100) * 95; // Video completed 95%

                        var iframeWindow = player.getIframe().contentWindow;
                        var lastTimeUpdate = 0;
                        window.addEventListener("message", function(event) {
                            if (event.source === iframeWindow) {
                                var data = JSON.parse(event.data);
                                if (data.event === "infoDelivery" && data.info && data.info.currentTime) {
                                    // currentTime is emitted very frequently,
                                    var time = Math.floor(data.info.currentTime);
                                    if (time !== lastTimeUpdate && time >= limitDuration) {
                                        lastTimeUpdate = time; // Update the dom, emit an event, whatever.
                                        isCompleted = 1;
                                        updateProgressData(totalVideoDuration, time, isCompleted);
                                        $("#is_completed").val(1);
                                    } else {
                                        // Call first time while click on 'Play' button.
                                        // if(flag == 0){
                                        updateProgressData(totalVideoDuration, time);
                                        //     flag = 1;
                                        // }
                                    }
                                }
                            }
                        });

                    } else if (playerStatus == 2) {
                        // paused
                        // videoCurrentTime = Math.floor(player.getCurrentTime());
                        // updateProgressData(videoCurrentTime);

                    } else if (playerStatus == 3) {
                        // buffering

                    } else if (playerStatus == 5) {
                        // video cued
                    }
                }
            }
        </script>
    @endif

    @if (isset($upload_type) &&
            ($upload_type == App\Models\Chapter::YOUTUBE || App\Models\Chapter::VIMEO || App\Models\Chapter::UPLOAD))
        @php
            $node_server = env('NODE_SERVER_URL');
            // echo $node_server ;
            // @dd($node_server)
            // exit;
        @endphp
        <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.min.js"></script>

        <script>
            const socket_url = '<?php echo $node_server; ?>';
            // const socket = io(socket_url+':3000');
            const socket = io(socket_url);
            console.log(socket_url + "1125");
            socket.on('connect', function() {
                console.log('Socket is running');
            });
            socket.on('connect_error', function() {
                console.log('Socket is not running'); //vimeo video error msg
                var vimeoIframe = document.getElementById('videoObject');
                if (vimeoIframe) {
                    vimeoIframe.style.display = 'none'; // Hide the Vimeo iframe
                    var errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'Socket is not running';
                    errorMessage.style.height = '400px';
                    var videoContainer = document.getElementById('videoContainer');
                    if (videoContainer) {
                        videoContainer.innerHTML = '';
                        videoContainer.appendChild(errorMessage);
                    }
                }

                var youtubePlayer = document.getElementById('player'); // youtube error message
                if (youtubePlayer) //check if youtube videocontainer is exists.
                {
                    youtubePlayer.style.display = 'none'; // Hide the YouTube player

                    var errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'Socket is not running';
                    errorMessage.style.height = '400px';

                    var videoContainer = document.getElementById('videoContainer');
                    if (videoContainer) {
                        videoContainer.innerHTML = '';
                        videoContainer.appendChild(errorMessage);
                    }
                }

                // var videoCipher =  document.getElementById('videoPlayer');
                // console.log(videoCipher);
                // // if(videoCipher){
                // //     console.log('123');

                // // }
            });

            var test = 0;

            function updateProgressData(totalVideoDuration, watchedTime, isCompleted = '') {

                // var is_completed_html = parseInt($("#is_completed").val()); // Video is already completed ?

                // if (is_completed_html == 0) {
                var chapterId = parseInt($("#chapter_id_hidden").val()); // Chapter id
                var learnerId = parseInt($("#learner_id_hidden").val()); // Learner id
                var coins = parseInt($("#chapter_coin_hidden").val()); // Learner id
                var chapter_id = parseInt($("#chapters_id_hidden").val()); // Learner id
                var course_id = parseInt($("#course_id_hidden").val()); //
                var currentDateTime = "{{ date('Y-m-d H:i:s') }}"
                var data;
                if (isCompleted != '') { // if completed
                    data = {
                        learnerId,
                        chapterId,
                        watchedTime,
                        // coins,
                        isCompleted,
                        currentDateTime
                        // chapter_id,
                        // course_id
                    };

                    @if (isset($upload_type) && ($upload_type == 2 || $upload_type == 1))
                        if (watchedTime == totalVideoDuration) {
                            test++;
                            console.log(test);
                            if (test == 1) {
                                $('.next').click();
                            }
                        }
                    @endif
                } else {
                    data = {
                        learnerId,
                        chapterId,
                        watchedTime,
                        currentDateTime
                        //     coins,
                        //     chapter_id,
                        //     course_id
                    };
                }
                // console.log("senddata", data);

                // console.log('learnerId', learnerId);
                socket.emit('updateData', data);
                // }
            }
            // console.log(data);

            // Custom ajax call
            // var chapterId = $("#chapter_id_hidden").val(); // Chapter id
            // var is_completed_html = $("#is_completed").val(); // Video is already completed ?

            // function updateProgressData(totalVideoDuration = 0, watchedTime = '', isCompleted = 0){
            //     // if(is_completed_html == 0){
            //         if(chapterId != '' && totalVideoDuration != 0){
            //             $.ajax({
            //                 url: "{{ route('video.tracker') }}",
            //                 method: "POST",z
            //                 data: {
            //                     chapterId: chapterId,
            //                     watchedTime: watchedTime,
            //                     isCompleted: isCompleted,
            //                 },
            //                 success: function(response) {
            //                     console.log('Success from AJAX');
            //                 },
            //                 error: function(jqxhr, error, errorThrown){
            //                     console.log('Error from AJAX');
            //                 },
            //             });
            //         }
            //     // }
            // }
        </script>
    @endif

    </div>
    {{-- <script type="text/javascript">
        $(document).ready(function() {
            Echo.channel('youtube-video')
                .listen('YoutubeVideo', (e) =>
                    console.log('Data'+ JSON.stringify(e))
                );
        });
    </script> --}}
    {{-- $(".caret").click(function(){

    }); --}}
    <script></script>
</body>

</html>


{{-- <script>
    var url = "{{ isset($pdfUrl) ? url($pdfUrl) : '' }}";


if(url) {
    $('#loader_section').show();
}

// Load PDF
var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {
    // Get the total number of pages
    var totalPages = pdf.numPages;

    // Render each page sequentially
    var renderPageSequentially = function(pageNum) {
        if (pageNum > totalPages) {
            // Hide the loader when all pages are rendered
            $('#loader_section').hide();
            return; // Stop when all pages are rendered
        }
        pdf.getPage(pageNum).then(function(page) {
            var scale = 1.5;
            var viewport = page.getViewport({ scale: scale });

            // Prepare canvas using PDF page dimensions
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Append the canvas to the viewer
            var pageContainer = document.createElement('div');
            pageContainer.className = 'page-container';
            pageContainer.appendChild(canvas);
            document.getElementById('pdf-viewer').appendChild(pageContainer);

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);
            renderTask.promise.then(function() {
                console.log('Page ' + pageNum + ' rendered');
                // Render the next page
                renderPageSequentially(pageNum + 1);
            });
        });
    };

    // Start rendering from the first page
    renderPageSequentially(1);
}, function(reason) {
    console.error(reason);
    // Hide the loader if there's an error
    $('#loader_section').hide();
});

</script> --}}
