@extends('admin.layouts.app')
@section('styles')
<style>
.collapsing {
    -webkit-transition: none;
    transition: none;
    display: none;

}
</style>
@endsection
@section('right-section')
    {!! redirect_to_back(route('slider.index')) !!}
@endsection
@section('content')
<div class="collapse multi-collapse old slidecls" style="display: none;">
    <div class="card mb-3">
        <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
            <div>Image Slide</div>
            <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr>
        <div class="row no-gutters">
            <div class="col-md-4 d-flex justify-content-center align-items-center p-4 add-slide-section">
                <div style="height:50%;">
                    <small class="text-gray">Web Image(Resolution: 1920px * 700px)</small>
                    <a href="" class="w-100 h-100 justify-content-center" accept="image/*" style="height: 100%;" onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file]').eq(0).trigger('click');"><i class="fas fa-image fa-4x"></i></a>
                </div>
                <div class="imagePreview"></div>

                <div style="height:50%;">
                    <small class="text-gray">Mobile Image(Resolution: 480px * 256px)</small>
                    <a class="w-100 h-100 justify-content-center"
                        style="width:100%;cursor:pointer; "
                        onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file]').eq(1).trigger('click');">
                        <i class="fas fa-image fa-4x"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-8 right_section">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <!-- <div class="row">
                                <div class="col-4">
                                    <label for="">Caption 1</label>
                                    <input type="text" class="form-control" name="caption1[]">
                                </div>
                                <div class="col-4">
                                    <label for="">Caption 2</label>
                                    <input type="text" class="form-control" name="caption2[]">
                                </div>
                                <div class="col-4">
                                    <label for="">Direction</label>
                                    <select class="custom-select" name="direction[]">
                                        <option selected value="left">Left</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>
                            </div> -->
                            <!-- <div class="row">
                                <div class="col-4">
                                    <label for="">Caption 1 Text Color</label>
                                    <input type="color" class="form-control" name="caption1_text_color[]">
                                </div>
                                <div class="col-4">
                                    <label for="">Caption 2 Text Color</label>
                                    <input type="color" class="form-control" name="caption2_text_color[]">
                                </div>
                                <div class="col-4">
                                    <label for="">Call to Action Text</label>
                                    <input type="text" class="form-control" name="c2a[]">
                                </div>
                            </div> -->

                            <div class="row mt-6">

                                <div class="col-6">
                                    <label for="">Call to Action URL (Website)</label>
                                    <input type="text" class="form-control" name="c2au[]">

                                    <label for="" style="visibility: hidden">&nbsp;</label>
                                    <input type="checkbox" name="newwin[]"> <span>&nbsp;Open in new window</span>
                                </div>
                                <div class="col-6">
                                    <label for="">Call to Action URL (Mobile App)</label>
                                    <select class="custom-select"
                                        name="url_mobile[]">
                                        <option value="">Select Value</option>
                                        @if(count($courses)>0)
                                        @foreach($courses as $key2=>$value2)
                                        <option value="{{$value2->id}}">{{$value2->title}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>


                            </div>
                            <div class="row mt-6">
                                <!-- <div class="col-4">

                                </div>
                                <div class="col-4">
                                    <label for="">Button Text Color</label>
                                </div>
                                <div class="col-6">
                                    <label for="">Button Background Color</label>
                                    <div>
                                        <input type="checkbox" name="transparent[]" onchange="btn_bg_color($(this),event)" id="" />
                                        <label for="">Transparent</label>
                                    </div>
                                    <input
                                        style="'display:none'"
                                        type="color" id="btn_bg_color"
                                        class="form-control"
                                        name="button_bg_color[]">
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <input type="file" class="custom-file-input invisible" name="img[]">
                <input type="file" class="custom-file-input invisible" name="slider_image_mobile[]">
            </div>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        <form method="POST" action="{{ url('backoffice/slider/'.$slider->id) }}" enctype='multipart/form-data'>
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>Update Slider</h3>
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

            @if ($errors->any())
                <div class="text text-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mx-auto">
                <div class="col-2 d-flex flex-column card px-0"
                    style="background-color: #eee !important;max-height: 200px !important;min-height: 200px !important;">
                    <div class="d-flex flex-column">
                        <div class="w-100 bg-white p-2 text-center" style="font-size: 18px;">Slider Information</div>
                        <div class="my-4 pr-0" id="sidemenu">
                            <div class="my-1 pr-0 ml-2">
                                <a class="text-dark mx-2 " href="#multiCollapseExample1" role="button"
                                    aria-expanded="false" aria-controls="multiCollapseExample1" id="slides">Slides</a>
                            </div>
                            <div class="my-1 ml-2 pr-0">
                                <a class="text-dark mx-2 " href="#multiCollapseExample2" type="button"
                                    aria-expanded="false" aria-controls="multiCollapseExample2"
                                    id="settings">Settings</a>

                            </div>
                        </div>
                        {{-- <button class="btn btn-primary" type="button" data-toggle="collapse" data-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2">Toggle both elements</button> --}}
                    </div>

                </div>

                <div class="col-10 pl-4">
                    <div class="row">
                        <input type='hidden' name='total' value='{{$slider->slides()->pluck("id")}}' id='total' />
                        @foreach($slider->slides as $key=>$value)

                        <div class="col-12 slidediv cp1 slidecls"  id='slide_{{$value->id}}'>

                            <input type='hidden' name='slide_[{{$value->id}}]' />


                            <div class="collapse multi-collapse @if($key==0) first @endif show new_slide"
                                id="multiCollapseExample{{$key==1?$key+2:$key+1}}">
                                <div class="card mb-3">
                                    <div
                                        class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
                                        <div>Image Slide</div>
                                        <button type="button" class="close" aria-label="Close" data-id='{{$value->id}}' onclick="remove(this);"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <hr>
                                    <div class="row no-gutters">

                                        <div class="col-md-4 d-flex align-items-center p-4 "
                                            style="display:block !important">
                                            <div style="height:50%;">
                                            <div><small class="text-gray">Web Image(Resolution: 1920px * 700px) </small></div>
                                                @if($value->img)
                                                <a href='' class="w-100 h-100  justify-content-center"
                                                    data-id='{{$value->id}}'
                                                    onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image]').trigger('click');" accept="image/*">

                                                    <img class='img-fluid'
                                                        src="{{ !str_contains($value->img, 'front') ? asset(Storage::url($value->img)) : asset($value->img)}}" />

                                                        <input type="text" selector="slide-image" class="custom-file-input invisible" id='img_{{$value->id}}' name="img[{{$value->id}}]" />

                                                </a>
                                                @else
                                                <a class="border border-black p-4 d-flex justify-content-center align-items-center"
                                                    style="height: 100%;width:100%;cursor:pointer;"
                                                    onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image]').trigger('click');">
                                                    <i class="fas fa-image fa-4x"></i>

                                                </a>
                                                @endif
                                            </div>
                                            <div style="height:50%;">
                                                <div><small class="text-gray">Mobile Image(Resolution: 512px * 244px)</small></div>
                                                @if($value->slider_image_mobile)
                                                <a  href='' class="w-100 h-100 justify-content-center"
                                                    data-id='{{$value->slider_image_mobile}}'
                                                    onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image-mobile]').trigger('click');">
                                                    <img  class='img-fluid' style='height:80% !important;'
                                                        src="{{ !str_contains(asset(Storage::url($value->slider_image_mobile)), 'front') ? asset(Storage::url($value->slider_image_mobile)) : asset($value->slider_image_mobile)}}" />
                                                        <input type="text" selector="slide-image-mobile" class="custom-file-input invisible" id='slider_image_mobile_{{$value->id}}' name="slider_image_mobile[{{$value->id}}]" />
                                                </a>
                                                @else
                                                <a class="border border-black p-4 d-flex justify-content-center align-items-center"
                                                    style="width:100%;cursor:pointer;"
                                                    onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image-mobile]').trigger('click');">
                                                    <i class="fas fa-image fa-4x"></i>

                                                </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-8 right_section">
                                            <div class="card-body">

                                                <div class="row">
                                                    <div class="col-12">
                                                        <!-- <div class="row">
                                                            <div class="col-4">
                                                                <label for="">Caption 1</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{$value->caption1}}"
                                                                    name="caption1[{{$value->id}}]">
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Caption 2</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{$value->caption2}}"
                                                                    name="caption2[{{$value->id}}]">
                                                            </div>

                                                            <div class="col-4">
                                                                <label for="">Direction</label>
                                                                <select class="custom-select"
                                                                    name="direction[{{$value->id}}]">
                                                                    <option value="left" @if($value->direction==false)
                                                                        selected @endif>Left</option>
                                                                    <option value="right" @if($value->direction==true)
                                                                        selected @endif>Right</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Caption 1 Text Color</label>
                                                                <input type="color" class="form-control"
                                                                    value="{{$value->caption1_text_color ?? '#FFFFFF'}}"
                                                                    name="caption1_text_color[{{$value->id}}]">
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Caption 2 Text Color</label>
                                                                <input type="color" class="form-control"
                                                                    value="{{$value->caption2_text_color ?? '#FFFFFF'}}"
                                                                    name="caption2_text_color[{{$value->id}}]">
                                                            </div>
                                                            <div class="col-4">
                                                                <label for="">Call to Action Text</label>
                                                                <input type="text" class="form-control"
                                                                    value='{{$value->action_text}}'
                                                                    name="c2a[{{$value->id}}]">
                                                            </div>
                                                        </div> -->
                                                        <div class="row mt-4">
                                                            <div class="col-6">
                                                                <label for="">Call to Action URL (Website)</label>
                                                                <input type="text" class="form-control"
                                                                    value='{{$value->action_url == "javascript:void(0)" ? "" : $value->action_url}}'
                                                                    name="c2au[{{$value->id}}]">
                                                                    <label for=""
                                                                    style="visibility: hidden">&nbsp;</label>
                                                                <input type="checkbox" name="newwin[{{$value->id}}]"
                                                                    @if($value->new_window) checked @endif>
                                                                <span>&nbsp;Open in new window</span>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="">Call to Action URL (Mobile App)</label>
                                                                <select class="custom-select"
                                                                    name="url_mobile[{{$value->id}}]">
                                                                    <option value="">Select Value</option>
                                                                    @if(count($courses)>0)
                                                                    @foreach($courses as $key1=>$value1)
                                                                    <option value="{{$value1->id}}"
                                                                        {{!empty($value->action_url_mobile && $value->action_url_mobile == $value1->id) ? 'selected':''}}>
                                                                        {{$value1->title}}</option>
                                                                    @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>

                                                        </div>
                                                        <div class="row mt-4">
                                                        <!-- <div class="col-4">

                                                            </div>
                                                            <div class="col-4">
                                                                <input type="color" class="form-control"
                                                                    value="{{$value->button_text_color ?? '#FFFFFF'}}"
                                                                    name="button_text_color[{{$value->id}}]">
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="">Button Background Color</label>
                                                                <div>
                                                                    <input type="checkbox" id="transparent{{$value->id}}"
                                                                        name="transparent[{{$value->id}}]"
                                                                        onchange="btn_bg_color($(this),event,{{$value->id}})" {{empty($value->button_bg_color) ? "checked":''}} />
                                                                    <label
                                                                        for="{{'transparent'.$value->id}}">Transparent</label>
                                                                </div>
                                                                <input
                                                                    style="{{!empty($value->button_bg_color) ? '':'display:none'}}"
                                                                    type="color" id="btn_bg_color{{$value->id}}"
                                                                    class="form-control"
                                                                    value="{{!empty($value->button_bg_color) ? $value->button_bg_color:''}}"
                                                                    name="button_bg_color[{{$value->id}}]">
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="file" selector="slide-image" class="custom-file-input invisible"
                                                id='img_{{$value->id}}' name="img[{{$value->id}}]" value="{{$value->id}}"/>
                                            <input type="file" selector="slide-image-mobile" class="custom-file-input invisible"
                                                id='slider_image_mobile_{{$value->id}}' name="slider_image_mobile[{{$value->id}}]" value="{{$value->id}}"/>
                                        </div>

                                    </div>
                                </div>
                            </div>



                        </div>
                        @endforeach
                        <div class="col-12 cp2 ml-2">
                            <div class="collapse multi-collapse card p-4" id="multiCollapseExample2">
                                {{-- <form action="{{ url('slide/create') }}"> --}}
                                @csrf
                                <div class="row">
                                    <div class="col-2">
                                        <label for="name">Name</label><span style="color: red">*</span>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="name" class="form-control" value='{{$slider->name}}'
                                            onkeyup="x(this)"><br>
                                    </div>
                                </div>
                                <div class="row d-none">
                                    <div class="col-2">
                                        <label for="slug">Slug</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="slug" class="form-control" value='{{$slider->slug}}'
                                            readonly id='slug'><br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-2">
                                        <label for="autoplay" class="mb-0">Autoplayss</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="checkbox" name="autoplay" @if($slider->autoplay) checked @endif>
                                    </div>
                                </div>
                                <!-- <div class="row mt-3">
                          <div class="col-2">
                            <label for="name">Autoplay speed</label>
                          </div>
                          <div class="col-8">
                            <input type="number" name="speed" class="form-control" value='{{$slider->speed}}'><br>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-2">
                            <label for="autoplay" class="mb-0">Dots</label>
                          </div>
                          <div class="col-8">
                            <input type="checkbox" name="dots" @if($slider->dots) checked @endif>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-2">
                            <label for="autoplay" class="mb-0">Arrows</label>
                          </div>
                          <div class="col-8">
                            <input type="checkbox" name="arrows" @if($slider->arrows) checked @endif>
                          </div>
                        </div> -->
                            </div>
                        </div>
                    </div>
                    <i>Click on image icon to select image. Then kindly save the slide to upload the images</i>
                    <div class="col-12 pl-0">
                        <button class="px-4 ml-auto d-block mr-0 btn btn-dark" onclick="event.preventDefault();slideadd();"
                            id="addslide">Add Slide</button><br>

                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>

                </div>
            </div>
            <input type="text" name="total_slide" id="total_slide" value="{{count($slider->slides()->pluck('id'))}}" style="display: none;"/>
        </form>
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
@includeIf('admin.layouts.partials.scripts.script-list',[
'switch' => 1
])
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

function slideadd() {
    $(".imagePreview").each(function() {
        $(this).attr('class', 'imagePreview image_add_' + key_id);
        key_id++;
    });

    let z = parseInt($(".new_slide").length);
    // console.log(z);
    if(z == undefined || z == "" || z == null) {
        z = 0;
    }
    z += 1;
    $("#total_slide").val(z);
    t_n+=1;
    var x = $(".old").clone().removeClass('old').addClass('new_slide');
    x.find('.row:last .col-6 div:first input').attr('id',"transparent"+t_n);
    x.find('.row:last .col-6 div:first label').attr('for',"transparent"+t_n);
    var y = $('.cp1').find('.multi-collapse').length;
    if($(".slidecls").length < 2){
        if($("#total").parent().find(".col-12").length <=1){
            $('<div class="col-12"></div>').insertAfter("#total");
        }
        $("#total").parent().find('.col-12:first').append(x);
        x.show();
    }
    else{
        if (y > 0 && y != undefined) {
        $(x).insertAfter($(".cp1").find('.multi-collapse').last()).show();
        } else {
            $(".cp1").prepend(x);
            $(".cp1").children().show();
        }
    }





}

$("#sidemenu div a").click(function() {
    $("#sidemenu").children().removeClass('bg-white').removeClass('py-2');
    $(this).parent().addClass('bg-white').addClass('py-2');
});

function x(z) {
    $('#slug').val();
    var y = $(z).val().replace(/ /g, '_').toLowerCase();
    $('#slug').val(y).text(y);
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
    // console.log(test);
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
function cancel(){

}
</script>
@endsection
