<aside class="main-sidebar sidebar-light-info elevation-4">
    <style>
        .builder-modal {
            display: none;
        }
    </style>
    @php

    //dd('test');

    @endphp



    <div class="position-relative" style="padding: .5rem 1.5rem;">
        <img class="sidebar2-preview-image courseCover rounded" src="{{ isset($courseData) && !empty($courseData) && !empty($courseData->image) && Storage::exists($courseData->image) ? Storage::url($courseData->image) : course_img_default()}}">
        <a role="button" class="cover_btn" data-toggle="modal" data-target="#exampleModalCenter"><i class="fas fa-pencil-alt bg-dark rounded-circle position-absolute" style="bottom: -5px;right: 25px;padding: 10px;"></i></a>
    </div>

    <div class="form-group text-center position-relative">


        <h5>{{ isset($courseData) && !empty($courseData->title) ? ucfirst($courseData->title) : '' }}</h5>
    </div>

    <div class="info-course text-center position-relative">
        <label for="" class="d-block"><mark class="bg-secondary">Created: {{ !empty($courseData) ? date('d/m/Y', strtotime($courseData->created_at)) : '' }}</mark></label>
        <label for="" class="d-block"><mark class="bg-secondary">Modified:
            @if(!empty($courseData))
                @if(!empty($courseData) && $courseData->type == 2)

                {{Helper::getPackageUpdatedDate($courseID,date('d/m/Y', strtotime($courseData->updated_at)))}}
                @else
                @php
                // dd()
                @endphp


                {{Helper::getCourseUpdatedDate($courseID,date('d/m/Y', strtotime($courseData->updated_at)))}}
                @endif
                @endif
            </mark></label>
    </div>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    @if(request()->routeIs('courses.*'))
                    <a href="{{ url('backoffice/courses') }}" class="nav-link">
                        <i class="nav-icon fas fa-arrow-left"></i>
                        <p>Back to Courses</p>
                    </a>
                    @endif
                    @if(request()->routeIs('packages.*'))
                    <a href="{{ route('packages.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-arrow-left"></i>
                        <p>Back to Packages</p>
                    </a>
                    @endif
                </li>

                <li class="nav-item">
                    @if(request()->routeIs('courses.*'))
                    @php $href = route('courses.edit',['course' => $courseID]); @endphp
                    @endif
                    @if(request()->routeIs('packages.*'))
                    @php $href = route('packages.edit',['package' => $courseID]); @endphp
                    @endif
                    <a href="{{ $href ?? 'javascript:void(0)' }}" class="nav-link {{ request()->routeIs('courses.edit') || request()->routeIs('packages.edit') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-info-circle"></i>
                        <p>Information</p>
                    </a>
                </li>

                {{-- <li class="nav-item course_menu" id="course_menu" style="{{($courseData->type == 1)?"display:block;":"display:none;"}}">
                <a href="{{ route('courses.builder', ['id' => $courseID]) }}" class="nav-link {{ request()->is('backoffice/courses/'.$courseID.'/builder') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tools"></i>
                    <p>Course Builder</p>
                </a>
                </li>

                <li class="nav-item package_menu" id="package_menu" style="{{($courseData->type == 2)?"display:block;":"display:none;"}}">
                    <a href="{{ route('courses.package', ['id' => $courseID]) }}" class="nav-link {{ request()->is('backoffice/courses/'.$courseID.'/package') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>Package Builder</p>
                    </a>
                </li> --}}

                @if(request()->routeIs('courses.*'))
                <li class="nav-item course_menu" id="course_menu">
                    <a href="{{ route('courses.builder', ['id' => $courseID]) }}" class="nav-link {{ request()->is('backoffice/courses/'.$courseID.'/builder') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Course Builder</p>
                    </a>
                </li>

                <li class="nav-item course_menu" id="course_menu">
                    <a target="_blank" href="{{url('course-view/' . $courseData->slug)}}" class="nav-link">
                        <i class="nav-icon fas fa-film"></i>
                        <p>Course Preview</p>
                    </a>
                </li>

                <li class="nav-item course_menu" id="course_menu">
                    <a target="_blank" href="{{url('course-details/' . $courseData->slug)}}" class="nav-link">
                        <i class="nav-icon fas fa-eye"></i>
                        <p>Landing Page</p>
                    </a>
                </li>
                @endif

                @if(request()->routeIs('packages.*'))
                <li class="nav-item package_menu" id="package_menu">
                    <a href="{{ route('packages.package', ['id' => $courseID]) }}" class="nav-link {{ request()->is('backoffice/packages/'.$courseID.'/package') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>Package Builder</p>
                    </a>
                </li>

                <li class="nav-item package_menu" id="package_menu">
                    <a target="_blank" href="{{url('course-package/' . $courseData->slug)}}" class="nav-link">
                        <i class="nav-icon fas fa-eye"></i>
                        <p>Landing Page</p>
                    </a>
                </li>
                @endif


                <li class="nav-item">
                    @if(request()->routeIs('courses.*'))
                    @php $href = route('courses.learners', ['id' => $courseID]); @endphp
                    @endif
                    @if(request()->routeIs('packages.*'))
                    @php $href = route('packages.learners', ['id' => $courseID]); @endphp
                    @endif
                    <a href="{{ $href ?? "javascript:void(0)" }}" class="nav-link {{ request()->is('backoffice/courses/'.$courseID.'/learners') || request()->is('backoffice/packages/'.$courseID.'/learners') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>Learners</p>
                    </a>
                </li>

                <li class="nav-item">

                    @php $href = url('backoffice/public-forum')
                    @endphp

                    <a href="{{ $href }}" class="nav-link {{ request()->routeIs('public-forum.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Public-Forum</p>
                    </a>


                </li>

                {{-- <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link">
                        <i class="nav-icon fas fa-film"></i>
                        <p>Subscriptions</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link">
                        <i class="nav-icon fas fa-gift"></i>
                        <p>Access Codes</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>Certificates</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link">
                        <i class="nav-icon fas fa-link"></i>
                        <p>Related Products</p>
                    </a>
                </li> --}}
            </ul>
        </nav>
    </div>
</aside>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:640px!important;">
        <form action="{{ url('backoffice/course-image') }}" method="post" enctype='multipart/form-data'>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Upload Cover</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="pb-0 text-muted">Note: Recommended Size 350px * 250px.</p>
                    <div class="form-group">
                        <input type="file" accept="image/*" class="form-control-file" name="image" id="image">
                        <input type="hidden" name="courseid" value="{{ $courseID }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="validate(event)" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    window.URL = window.URL || window.webkitURL;

    function validate(event) {
        $(".img_err").remove();
        var img = new Image();
        // var fileInput = $(this).find("input[type=file]")[0],
        var file = $("#image")[0].files[0];
        if (file == undefined) {
            $("#image").parent().append("<p class='img_err mb-0' style='color:red;'>Please select a file</p>");
            return false;
        } else {
            $(".img_err").remove();
        }
        img.src = window.URL.createObjectURL(file);
        img.onload = function() {
            var width = img.naturalWidth,
                height = img.naturalHeight;

            window.URL.revokeObjectURL(img.src);
            // if(width > 350 || height > 250){
            //     event.preventDefault();
            //     $("#image").parent().append("<p class='img_err mb-0' style='color:red;'>Image Dimention should be 350*250</p>");
            // }
            // else if(file.size > 300000){
            //     event.preventDefault();
            //     $("#image").parent().append("<p class='img_err mb-0' style='color:red;'>Image must not be more than 300kb</p>");
            // }
            //else{
            $("#exampleModalCenter").find('form').submit();
            $(".img_err").remove();
            // }
        };
        img.onerror = function() {
            $(".img_err").remove();
            $("#image").parent().append("<p class='img_err mb-0' style='color:red;'>The file must be an image.</p>");
        }
    }
</script>
