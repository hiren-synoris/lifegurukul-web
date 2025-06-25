@extends('admin.layouts.app')
@section('content')
@section('right-section')
    {!! redirect_to_back(route('slider.index')) !!}
@endsection

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Slider</h3>
    </div>
     <form method="POST" action="{{ url('backoffice/slider/'.$slider->id) }}" enctype='multipart/form-data'>
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="text text-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">
            <div class="row">
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="vert-tabs-home" aria-selected="true">Slides</a>
                        <a class="nav-link" id="vert-tabs-profile-tab" data-toggle="pill" href="#vert-tabs-profile" role="tab" aria-controls="vert-tabs-profile" aria-selected="false">Settings</a>
                    </div>
                </div>
                <div class="col-7 col-sm-9">
                    <div class="tab-content" id="vert-tabs-tabContent">
                        <!-- Slides tab -->
                        <div class="tab-pane text-left fade show active cp1" id="vert-tabs-home" role="tabpanel" aria-labelledby="vert-tabs-home-tab">
                            @foreach($slider->slides as $key => $value)
                                <div class="card mb-3 multi-collapse first show new_slide" id="slide_{{$value->id}}">
                                    <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
                                        <div>Image Slide</div>
                                        <button type="button" class="close" aria-label="Close" data-id='{{$value->id}}' onclick="remove(this);">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <hr>
                                    <div class="row no-gutters">
                                        <!-- IMAGE UPLOAD -->
                                        <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4">
                                            <!-- Desktop Image -->
                                            <div class="mb-4 w-100">
                                                <div><small class="text-gray">Desktop Image (Resolution: 1920px * 670px)</small></div>
                                                <a href="#" class="w-100 justify-content-center">
                                                    <div class="border border-black p-4 d-flex justify-content-center align-items-center"
                                                        onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image]').trigger('click');">
                                                        @php
                                                            $imgPath = !str_contains($value->img, 'front') ? asset(Storage::url($value->img)) : asset($value->img);
                                                            $ext = pathinfo($value->img, PATHINFO_EXTENSION);
                                                        @endphp

                                                        @if(in_array(strtolower($ext), ['mp4','webm','ogg','mov']))
                                                            <video controls class="w-100" style="max-height: 120px;">
                                                                <source src="{{ $imgPath }}" type="video/{{ $ext }}">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @elseif($value->img)
                                                            <img class='img-fluid' src="{{ $imgPath }}" style="max-height: 120px;" />
                                                        @else
                                                            <i class="fas fa-image fa-4x"></i>
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                            <!-- Mobile Image -->
                                            <div class="w-100">
                                                <div><small class="text-gray">Mobile Image (Resolution: 512px * 244px)</small></div>
                                                <a href="#" class="w-100 justify-content-center">
                                                    <div class="border border-black p-4 d-flex justify-content-center align-items-center"
                                                        onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image-mobile]').trigger('click');">
                                                        @php
                                                            $mobileMedia = $value->slider_image_mobile;
                                                            $mobilePath = !str_contains($mobileMedia, 'front') ? asset(Storage::url($mobileMedia)) : asset($mobileMedia);
                                                            $mobileExt = strtolower(pathinfo($mobileMedia, PATHINFO_EXTENSION));
                                                        @endphp

                                                        @if(in_array($mobileExt, ['mp4','webm','ogg','mov']))
                                                            <video controls class="w-100" style="max-height: 120px;">
                                                                <source src="{{ $mobilePath }}" type="video/{{ $mobileExt }}">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        @elseif(in_array($mobileExt, ['jpg','jpeg','png','gif','svg','webp']))
                                                            <img class='img-fluid' src="{{ $mobilePath }}" style="max-height: 120px;" />
                                                        @else
                                                            <i class="fas fa-image fa-4x"></i>
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- RIGHT SECTION -->
                                        <div class="col-md-8 right_section">
                                            <div class="card-body">
                                                <!-- Captions -->
                                                <div class="row">
                                                    <div class="col-4 mb-3">
                                                        <label>Caption 1</label>
                                                        <input type="text" class="form-control" value="{{$value->caption1}}" name="caption1[{{$value->id}}]">
                                                    </div>
                                                    <div class="col-4 mb-3">
                                                        <label>Caption 2</label>
                                                        <input type="text" class="form-control" value="{{$value->caption2}}" name="caption2[{{$value->id}}]">
                                                    </div>
                                                    <div class="col-4 mb-3">
                                                        <label>Direction</label>
                                                        <select class="custom-select" name="direction[{{$value->id}}]">
                                                            <option value="left" @if(!$value->direction) selected @endif>Left</option>
                                                            <option value="right" @if($value->direction) selected @endif>Right</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- Call to Action -->
                                                <div class="row mt-4">
                                                    <div class="col-6 mb-3">
                                                        <label>Call to Action URL (Website)</label>
                                                        <input type="text" class="form-control" value="{{ $value->action_url == 'javascript:void(0)' ? '' : $value->action_url }}" name="c2au[{{$value->id}}]">
                                                        <label style="visibility: hidden;">&nbsp;</label>
                                                        <input type="checkbox" name="newwin[{{$value->id}}]" @if($value->new_window) checked @endif>
                                                        <span>&nbsp;Open in new window</span>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <label>Call to Action URL (Mobile App)</label>
                                                        <select class="custom-select" name="url_mobile[{{$value->id}}]">
                                                            <option value="">Select Value</option>
                                                            @foreach($courses as $course)
                                                                <option value="{{$course->id}}" @if($value->action_url_mobile == $course->id) selected @endif>{{$course->title}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- HIDDEN INPUTS FOR FILES -->
                                            <input type="file" selector="slide-image" class="custom-file-input invisible" id='img_{{$value->id}}' name="img[{{$value->id}}]" value="{{$value->id}}" onchange="showPreview(this)">
                                            <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" id='slider_image_mobile_{{$value->id}}' name="slider_image_mobile[{{$value->id}}]" value="{{$value->id}}" onchange="showPreview(this)">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- Settings tab -->
                        <div class="tab-pane fade cp2" id="vert-tabs-profile" role="tabpanel" aria-labelledby="vert-tabs-profile-tab">
                            <div class="row">
                                <div class="col-2"><label for="name">Name</label><span style="color: red;">*</span></div>
                                <div class="col-8"><input type="text" name="name" class="form-control" onkeyup="x(this)"   value='{{$slider->name}}'/><br /></div>
                            </div>
                            <div class="row">
                                <div class="col-2"><label for="slug">Slug</label><span style="color: red;">*</span></div>
                                <div class="col-8"><input type="text" name="slug" id="slug" class="form-control"  value='{{$slider->slug}}' readonly /><br /></div>
                            </div>
                            <div class="row">
                                <div class="col-2"><label for="autoplay" class="mb-0">Autoplay</label></div>
                                <div class="col-8"> <input type="checkbox" name="autoplay" @if($slider->autoplay) checked @endif></div>
                            </div>
                        </div>
                        <!-- Buttons -->
                        <div class="col-10 pl-0 mt-3">
                            <button class="px-4 btn btn-light  add-slide-btn" onclick="event.preventDefault();slideadd();" id="addslide">Add Slide</button><br />
                            <button type="submit" class="btn btn-primary mt-2 px-4">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="text" name="total_slide" id="total_slide" value="{{count($slider->slides()->pluck('id'))}}" style="display: none;"/>
    </form>
</div>
<!-- Hidden .old block for cloning -->
<div class="collapse multi-collapse old" style="display: none;">
    <div class="card mb-3">
        <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
            <div>Image Slide</div>
            <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr />
        <div class="row no-gutters">
            <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4">
                <div class="mb-4 w-100">
                    <div><small class="text-gray">Desktop Image (Resolution: 1920px * 670px)</small></div>
                    <a href="#" class="w-100 justify-content-center">
                        <div
                            class="border border-black p-4 d-flex justify-content-center align-items-center"
                            onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image]').trigger('click');"
                            selector="slide-image-preview" class="mt-2"
                            >
                            <i class="fas fa-image fa-4x"></i>
                        </div>
                    </a>
                    {{-- <div selector="slide-image-preview" class="mt-2"></div> --}}
                </div>
                <div class="w-100">
                    <div><small class="text-gray">Mobile Image (Resolution: 512px * 244px)</small></div>
                    <a href="#" class="w-100 justify-content-center">
                        <div
                            class="border border-black p-4 d-flex justify-content-center align-items-center"
                            onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image-mobile]').trigger('click');"
                            selector="slide-image-mobile-preview" class="mt-2"
                            >
                            <i class="fas fa-image fa-4x"></i>
                        </div>
                    </a>
                    {{-- <div selector="slide-image-mobile-preview" class="mt-2"></div> --}}
                </div>
            </div>
            <div class="col-md-8 right_section">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <label>Caption 1</label>
                            <input type="text" class="form-control" name="caption1[]" />
                        </div>
                        <div class="col-4">
                            <label>Caption 2</label>
                            <input type="text" class="form-control" name="caption2[]" />
                        </div>
                        <div class="col-4">
                            <label>Direction</label>
                            <select class="custom-select" name="direction[]">
                                <option selected value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-4">
                            <label>Call to Action Text</label>
                            <input type="text" class="form-control" name="c2a[]" />
                        </div>
                        <div class="col-4">
                            <label>Call to Action URL</label>
                            <input type="text" class="form-control" name="c2au[]" />
                        </div>
                        <div class="col-4">
                            <label style="visibility: hidden;">&nbsp;</label><br />
                            <input type="checkbox" name="newwin[]" /> <span>&nbsp;Open in new window</span>
                        </div>
                    </div>
                </div>
              <input type="file" selector="slide-image" class="custom-file-input invisible" name="img[]" accept="image/*,video/mp4,video/webm,video/ogg" onchange="showPreview(this)">
              <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" name="slider_image_mobile[]" accept="image/*,video/mp4,video/webm,video/ogg" onchange="showPreview(this)">
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal -->
    <div class="modal fade" id="delete_conf" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="cancel();">No</button>
                    <a href="javascript:void(0)" id="delete_conf_slider_yes" type="button" class="btn btn-primary">Yes</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="media_delete" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                    <a href="javascript:void(0)" id="bluk_delete_conf_yes" type="button" class="btn btn-primary">Yes</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', ['switch' => 1])
<script>
    var t_n = 0;
    var id = null;
    var current = null;
    var key_id = 0
    $(document).ready(function() {
        $(document).on("change",'.custom-file-input',function() {
            var fileName = $(this).val().split('\\').pop(); // Get the file name
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {

                    $('.imagePreview').append('<img src="' + e.target.result + '" width="200"/>');

                    // $('.imagePreview').append('<div class="image_add_'+key_id+'"><img src="' + e.target.result + '" width="200"/></div>');
                    // key_id++
                }
                reader.readAsDataURL(file);
            } else {

                $('#imagePreview').html('');
            }
            // You can do further processing with the file name here
        });

        $("#multiCollapseExample2").collapse('hide');
        $(".first").collapse('show');
        $("#slides").click(function() {
            $("#addslide").show();
            $("#multiCollapseExample2").collapse('hide');
            $(".cp1").show();
        });
        $("#settings").click(function() {
            $(".cp1").hide();
            $("#addslide").hide();
            $("#multiCollapseExample2").collapse('show');
        });

        $("#sidemenu").children().removeClass('bg-white').removeClass('py-2');
        $("#sidemenu div:first").addClass('bg-white').addClass('py-2');
    });

    $(document).ready(function () {
        $(".first").show();
        $("#vert-tabs-home-tab").click(function () {
            $('.add-slide-btn').show();
            $(".cp1").show();
        });
        $("#vert-tabs-profile-tab").click(function () {
            $('.add-slide-btn').hide();
            $(".cp1").hide();
        });
    });
    function slideadd() {
        var x = $(".old").clone().removeClass("old");
        var y = $("#vert-tabs-home").find(".multi-collapse").length;
        if (y > 0 && y != undefined) {
            $(x).insertAfter($("#vert-tabs-home").find(".multi-collapse").last()).show();
        } else {
            $("#vert-tabs-home").prepend(x);
            $("#vert-tabs-home").children().show();
        }
    }
    function x(z) {
        var y = $(z).val().replace(/ /g, "_").toLowerCase();
        $("#slug").val(y).text(y);
    }
    function showPreview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var targetSelector = $(input).attr('selector');
                var previewDiv = $(input).closest('.right_section').siblings().find('[selector="'+targetSelector+'-preview"]');
                previewDiv.html('<img src="' + e.target.result + '" style="max-width: 100%; max-height: 120px;">');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

function btn_bg_color(afs, event, id) {
    event.preventDefault();
    if ($(afs).is(':checked')) {
        $("#btn_bg_color" + id).hide();
    } else {
        $("#btn_bg_color" + id).show();
    }
}
function btn_bg_color(afs, event) {
    event.preventDefault();
    if ($(afs).is(':checked')) {
        console.log($(afs).parent().eq(1).find("input"));
        $(afs).parents().eq(1).find("input:last").hide();
    } else {
        $(afs).parents().eq(1).find("input:last").show();
    }
}

function remove(test){
    // console.log(test);
    let temp_id = $(test).data('id');
    $('#delete_conf').modal('show');
    id = temp_id;

}
$("#delete_conf_slider_yes").click(function(event){
    event.preventDefault();
    // console.log(id);
    if(id != null && id != undefined){
        $.ajax({
            headers: {"X-CSRF-TOKEN": "{{csrf_token()}}"},
            url: "{{env('APP_URL')}}"+'/backoffice/slide/'+id,
            type: 'DELETE',
            beforeSend: function() {
                $('#loader_section').show();
            },
            success: function(data){
                $('#loader_section').hide();
                if(data){
                    // location.reload();
                    Swal.fire(
                        'Success',
                        'Slide deleted successfully',
                        'success'
                    );
                    $("#slide_"+id).remove();
                    $("#total").val($("#total").val().replace(id,'').replace(',,','').replace(',]',']').replace('[,]','').replace('[,,]',''));
                        let tmp1 = parseInt($("#total_slide").val());
                        let tmp2 = tmp1-1;
                    $("#total_slide").val(tmp2);

                }
            },
            error: function(jqxhr, error, errorThrown){
                if(jqxhr.status == 403){
                    Swal.fire(
                        'Unauthorized',
                        '',
                        'error'
                    );
                }
            },
            complete: function(){
                $('#delete_conf').modal('hide');
                id=null;
            }
        });
    }
});

</script>
@endsection
