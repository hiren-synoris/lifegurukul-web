@extends('admin.layouts.app')

@section('styles')
<style>
    .collapsing {
        -webkit-transition: none;
        transition: none;
        display: none;
    }
    .image-preview img, .image-preview-mobile img {
        max-height: 150px;
    }
</style>
@endsection

@section('right-section')
    {!! redirect_to_back(route('slider.index')) !!}
@endsection

@section('content')
<div class="collapse multi-collapse old" style="display: none;">
    <div class="card mb-3 slide-item">
        <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
            <div>Image Slide</div>
            <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr>
        <div class="row no-gutters">
            <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4">
                <div class="mb-4 w-100 image-preview" onclick="event.preventDefault();$(this).closest('.no-gutters').find('input[selector=slide-image]').trigger('click');" style="cursor:pointer;">
                    <small class="text-gray">Desktop Image (1920px * 670px)</small>
                    <div class="border border-black p-4 d-flex justify-content-center align-items-center">
                        <img src="" class="img-fluid d-none" />
                        <i class="fas fa-image fa-4x"></i>
                    </div>
                </div>
                <div class="w-100 image-preview-mobile" onclick="event.preventDefault();$(this).closest('.no-gutters').find('input[selector=slide-image-mobile]').trigger('click');" style="cursor:pointer;">
                    <small class="text-gray">Mobile Image (512px * 244px)</small>
                    <div class="border border-black p-4 d-flex justify-content-center align-items-center">
                        <img src="" class="img-fluid d-none" />
                        <i class="fas fa-image fa-4x"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-8 right_section">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <label>Caption 1</label>
                            <input type="text" class="form-control" name="caption1[]">
                        </div>
                        <div class="col-4">
                            <label>Caption 2</label>
                            <input type="text" class="form-control" name="caption2[]">
                        </div>
                        <div class="col-4">
                            <label>Direction</label>
                            <select class="custom-select" name="direction[]">
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-4">
                            <label>Call to Action Text</label>
                            <input type="text" class="form-control" name="c2a[]">
                        </div>
                        <div class="col-4">
                            <label>Call to Action URL</label>
                            <input type="text" class="form-control" name="c2au[]">
                        </div>
                        <div class="col-4">
                            <label style="visibility: hidden;">&nbsp;</label><br>
                            <input type="checkbox" name="newwin[]"> <span>Open in new window</span>
                        </div>
                    </div>
                </div>
                <input type="file" selector="slide-image" class="custom-file-input invisible" name="img[]" onchange="showPreview(this)">
                <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" name="slider_image_mobile[]" onchange="showPreviewMobile(this)">
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12">
        <form method="POST" action="{{ url('backoffice/slider') }}" enctype='multipart/form-data'>
            @csrf

            <div class="d-flex justify-content-center mb-3">
                <h3>Create Slider</h3>
            </div>

            @includeIf('admin.layouts.partials.errors.validation-failed')

            <div class="row mx-auto">
                <div class="col-2 d-flex flex-column card px-0" style="background-color: #eee !important;max-height: 200px;min-height: 200px;">
                    <div class="d-flex flex-column">
                        <div class="w-100 bg-white p-2 text-center" style="font-size: 18px;">Slider Information</div>
                        <div class="my-4 pr-0" id="sidemenu">
                            <div class="my-1 pr-0 ml-2">
                                <a class="text-dark mx-2" href="#multiCollapseExample1" id="slides">Slides</a>
                            </div>
                            <div class="my-1 ml-2 pr-0">
                                <a class="text-dark mx-2" href="#multiCollapseExample2" id="settings">Settings</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-10">
                    <div class="row">
                        <div class="col-12 cp1">
                            <div class="collapse multi-collapse first" id="multiCollapseExample1">
                                <div class="card mb-3 slide-item">
                                    <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
                                        <div>Image Slide</div>
                                        <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <hr>
                                    <div class="row no-gutters">
                                        <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4">
                                            <div class="mb-4 w-100 image-preview" onclick="event.preventDefault();$(this).closest('.no-gutters').find('input[selector=slide-image]').trigger('click');" style="cursor:pointer;">
                                                <small class="text-gray">Desktop Image (1920px * 670px)</small>
                                                <div class="border border-black p-4 d-flex justify-content-center align-items-center">
                                                    <img src="" class="img-fluid d-none" />
                                                    <i class="fas fa-image fa-4x"></i>
                                                </div>
                                            </div>
                                            <div class="w-100 image-preview-mobile" onclick="event.preventDefault();$(this).closest('.no-gutters').find('input[selector=slide-image-mobile]').trigger('click');" style="cursor:pointer;">
                                                <small class="text-gray">Mobile Image (512px * 244px)</small>
                                                <div class="border border-black p-4 d-flex justify-content-center align-items-center">
                                                    <img src="" class="img-fluid d-none" />
                                                    <i class="fas fa-image fa-4x"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 right_section">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <label>Caption 1</label>
                                                        <input type="text" class="form-control" name="caption1[]">
                                                    </div>
                                                    <div class="col-4">
                                                        <label>Caption 2</label>
                                                        <input type="text" class="form-control" name="caption2[]">
                                                    </div>
                                                    <div class="col-4">
                                                        <label>Direction</label>
                                                        <select class="custom-select" name="direction[]">
                                                            <option value="left">Left</option>
                                                            <option value="right">Right</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-4">
                                                        <label>Call to Action Text</label>
                                                        <input type="text" class="form-control" name="c2a[]">
                                                    </div>
                                                    <div class="col-4">
                                                        <label>Call to Action URL</label>
                                                        <input type="text" class="form-control" name="c2au[]">
                                                    </div>
                                                    <div class="col-4">
                                                        <label style="visibility: hidden;">&nbsp;</label><br>
                                                        <input type="checkbox" name="newwin[]"> <span>Open in new window</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="file" selector="slide-image" class="custom-file-input invisible" name="img[]" onchange="showPreview(this)">
                                            <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" name="slider_image_mobile[]" onchange="showPreviewMobile(this)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 cp2 ml-2">
                            <div class="collapse multi-collapse card p-4" id="multiCollapseExample2">
                                @csrf
                                <div class="row">
                                    <div class="col-2">
                                        <label for="name">Name</label><span style="color: red">*</span>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="name" class="form-control" onkeyup="x(this)">
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-2">
                                        <label for="slug">Slug</label><span style="color: red">*</span>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="slug" id="slug" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-2">
                                        <label for="autoplay" class="mb-0">Autoplay</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="checkbox" name="autoplay">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-10 pl-0 mt-3">
                            <button class="px-4 btn btn-light" onclick="event.preventDefault();slideadd();" id="addslide">Add Slide</button>
                            <button type="submit" class="btn btn-primary mt-2 px-4">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', ['switch' => 1])
<script>
    $(document).ready(function() {
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

        $("#sidemenu div a").click(function() {
            $("#sidemenu").children().removeClass('bg-white').removeClass('py-2');
            $(this).parent().addClass('bg-white').addClass('py-2');
        });
    });

    function slideadd() {
        var x = $(".old").clone().removeClass('old');
        $(x).insertAfter($(".cp1").find('.multi-collapse').last()).show();
    }

    function showPreview(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var container = $(input).closest('.multi-collapse').find('.image-preview');
                container.find('img').attr('src', e.target.result).removeClass('d-none');
                container.find('i').hide();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showPreviewMobile(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var container = $(input).closest('.multi-collapse').find('.image-preview-mobile');
                container.find('img').attr('src', e.target.result).removeClass('d-none');
                container.find('i').hide();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function x(z) {
        var y = $(z).val().replace(/ /g, '_').toLowerCase();
        $('#slug').val(y).text(y);
    }
</script>
@endsection
