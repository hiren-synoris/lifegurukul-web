<aside class="main-sidebar sidebar-light-info elevation-4 cust-design-sidebar 147">
    <style>
        /* .builder-modal {
            display: none;
        } */

        </style>
        <link rel="stylesheet" href="{{ asset('admin/plugins/sortable-draggable/styles/style.css') }}">
    @php
        //Here i am getting course ID from request(url)
        $courseID = '';
        $chapterID = '';
        if (request()->segment(3) != null || request()->segment(2) != '') {
            $courseID = request()->segment(3);
        }
        if (request()->segment(4) != null && request()->segment(5) != null) {
            $chapterID = request()->segment(5);
        }

    @endphp
    <div class="brand">
            <div class="back">
                <a href="{{ route('courses.edit', ['course' => $courseID]) }}">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
    </div>



    <div class="builder-sidebar px-2">
        <!-- <div class="brand">
            <div class="back" onclick="history.go(-1)">
                <a href="{{ route('courses.edit', ['course' => $courseID]) }}">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Courses
                </a>
            </div>
        </div> -->
        @php
            $course = App\Models\Course::find($courseID);
            $chapter_info = App\Models\Chapter::where('id',$chapterID)->first();
            // dd($chapter_info)
        @endphp
        <div class="position-relative mt-4" style="padding: .5rem 1.5rem;">
            <img class="sidebar2-preview-image courseCover rounded"
                src="{{ !empty($course) && !empty($course->image) ? Storage::url($course->image) : course_img_default()}}">
        </div>
        <div class="chapter-content mt-4">
            @if (isset($courseChapters) && !empty($courseChapters) && count($courseChapters) > 0)
                <input type="hidden" name="form-order-url" id="form-order-url" value="{{ route('builder.order') }}">
                <div id="accordion" class="accordion">
                    @foreach ($courseChapters as $chapterKey => $chapterValue)
                    {{-- @dd($courseChapters) --}}
                        @php $display = '';
                        @endphp
                        @if (Helper::setActiveCourse($chapterID) > 0)
                            @if (Helper::setActiveCourse($chapterID) == $chapterValue->parent_id)
                                @php $display = 'show';

                                @endphp
                            @endif
                        @endif
                        <div class="group builder-accordion" data-section-id="{{ $chapterValue->id }}">

                            <div class="accordion-header" id="heading{{ $chapterValue->id }}">
                            <h3>
                                    <i class="fas fa-arrows-alt drag"></i>
                            </h3>

                            <a class="sidebar-titleName "
                                href="{{ route('courses.builder', ['id' => $chapterValue->course_id, 'chapterId' => $chapterValue->id ]) }}">
                                {{ Helper::setTypeWiseIcon(trim($chapterValue->asset_type)) }}
                                {{ (isset($chapterValue->asset_type) && $chapterValue->asset_type == 7) ? Helper::getCourseTitle($chapterValue->title) : (isset($chapterValue) && !empty($chapterValue->title) ? ucfirst($chapterValue->title) : '')}}
                            </a>
                            <button class="accordion-button {{ $display?'collapsed':'' }}
                          " type="button" data-toggle="collapse" data-target="#collapse{{ $chapterValue->id }}" aria-expanded="{{ $display }}" aria-controls="collapse{{ $chapterValue->id }}">
                                <i class="float-right fa-caret-down fas"></i>
                            </button>
                            <a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)"
                            onclick="confirmDelete('{{ route('remove.chapter',['id' => $chapterValue->id, 'parentId' => $chapterValue->parent_id]) }}')">
                                <i class="fas fa-trash"></i>
                            </a>

                        </div>
                            <div class="collapse chapter-info {{ $chapter_info->parent_id == $chapterValue->id ? 'show' : '' }}" id="collapse{{ $chapterValue->id }}"  aria-labelledby="heading{{ $chapterValue->id }}" data-parent="#accordion">
                            <!-- Course chapters start -->
                            @if (count($chapterValue->childrens) > 0)
                                @php
                                    $show = '';
                                @endphp

                                    <ul id="sortable-left-{{ $chapterValue->id }}" class="connectedSortable sortable" data-list-id="{{ $chapterValue->id }}">
                                        @foreach ($chapterValue->childrens->sortBy("order") as $childKey => $childVal)
                                            @if (Helper::setActiveCourse($chapterID) > 0)
                                                @if (Helper::setActiveCourse($chapterID) == $childVal->parent_id)
                                                    @php $show = 'active'; @endphp
                                                @endif
                                            @endif
                                            <li data-item-id="{{ $childVal->id }}" class="{{ $chapterID == $childVal->id ? ' active' : '' }}">
                                                <span class="draggable">
                                                    <i class="fas fa-arrows-alt"></i>
                                                </span>

                                                <!-- <div class="message_ticker"> -->
                                                <a class="btn btn-link text-left "
                                                    href="{{ route('courses.builder', ['id' => $chapterValue->course_id, 'chapterId' => $childVal->id]) }}">
                                                    {{ Helper::setTypeWiseIcon(trim($childVal->asset_type)) }}
                                                    {{-- <div class="message_ticker {{ strlen($childVal->title) > 35 ? 'long-title' : '' }}">
    {{ (isset($childVal->asset_type) && $childVal->asset_type == 7) ? Helper::getCourseTitle($childVal->title) : $childVal->title }}
</div> --}}
                                                    <div class="message_ticker" title="{{ $childVal->title }}">
    {{ (isset($childVal->asset_type) && $childVal->asset_type == 7) ? Helper::getCourseTitle($childVal->title) : $childVal->title }}
</div>

                                                </a>
                                                <!-- </div> -->
                                                <!--Delete btn-->
                                                <a class="mx-1" title="Delete" type="button" href="javascript:void(0)" onclick=confirmDelete('{{route('chapters-info.destroy', ["chapters_info" => $childVal->id])}}')> <i class="fas fa-trash"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>

                            @endif
                            <!-- Course chapters end -->
                            </div>
                            <button class="btn btn-light btn-sm border px-1 py-0 text-secondary" data-toggle="modal" data-target="#addNewChapterBtn" onclick="updateType(0, {{ $chapterValue->id }})" style="display:block !important;">
                                <span class="text-secondary"><i class="fas fa-plus"></i>  Add Chapter Item</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="add-btn position-absolute fixed-bottom addnew-chapter">
            <button class="btn btn-lg btn-link text-white" type="button" data-toggle="modal"
                data-target="#createLabelModal" onclick="openHeading(1)">
                <i class="fas fa-plus-circle"></i> <span class="icon-text">Add New Chapter</span>
            </button>
        </div>
    </div>
</aside>
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:640px!important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Upload Cover</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="pb-0 text-muted">Note:Recommended Size 350px * 250px.</p>
                <form action="{{ url('backoffice/course-image/') }}" method="post" class="form_reset" enctype='multipart/form-data'>
                    @csrf
                    <div class="form-group">
                        <input type="file" class="form-control-file" id="exampleFormControlFile1" name="image" accept="image/*" >
                        <input type="hidden" name="courseid" value="{{ $courseID }}" />
                        <input type="submit" id="submit" style="display:none;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="validate(event)">Upload</button>
            </div>
        </div>
    </div>
</div>
<script>
    window.URL = window.URL || window.webkitURL;
    function validate(event){
        $(".img_err").remove();
        var img = new Image();
        // var fileInput = $(this).find("input[type=file]")[0],

        if($("#exampleFormControlFile1")[0].files[0] == undefined){
            $("#exampleFormControlFile1").parent().append("<p class='img_err mb-0' style='color:red;'>Please select a Image</p>");
           return false;
        }
        var file = $("#exampleFormControlFile1")[0].files[0];
        var x = file['type'];
        var validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
        // console.log(x);
        if(!validImageTypes.includes(x)){
                event.preventDefault();
                $("#exampleFormControlFile1").parent().append("<p class='img_err mb-0' style='color:red;'>Please Add Only Image</p>");

         }
        else{
            $(".img_err").remove();
        }
        img.src = window.URL.createObjectURL( file );
        img.onload = function() {
            var width = img.naturalWidth,
                height = img.naturalHeight;

            window.URL.revokeObjectURL( img.src );
            if(width > 1280 || height > 855){
                event.preventDefault();
                $("#exampleFormControlFile1").parent().append("<p class='img_err mb-0' style='color:red;'>Image Dimention should be 1280*855</p>");
            }else if(file.size > 300000){
                event.preventDefault();
                $("#exampleFormControlFile1").parent().append("<p class='img_err mb-0' style='color:red;'>Image must not be more than 300kb</p>");
            }
            else{
                $("#submit").trigger('click');
                $(".img_err").remove();
            }
        };
    }

</script>
