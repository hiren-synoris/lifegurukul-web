@extends('admin.layouts.app')
@section('content')
@section('right-section')
    {!! redirect_to_back(route('slider.index')) !!}
@endsection

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Slider</h3>
    </div>
    <form method="POST" action="{{ url('backoffice/slider') }}" enctype="multipart/form-data">
        @csrf
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
                            <div class="card mb-3 multi-collapse first">
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
                                        <input type="file" selector="slide-image" class="custom-file-input invisible" name="img[]" onchange="showPreview(this)">
                                        <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" name="slider_image_mobile[]" onchange="showPreview(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings tab -->
                        <div class="tab-pane fade cp2" id="vert-tabs-profile" role="tabpanel" aria-labelledby="vert-tabs-profile-tab">
                            <div class="row">
                                <div class="col-2"><label for="name">Name</label><span style="color: red;">*</span></div>
                                <div class="col-8"><input type="text" name="name" class="form-control" onkeyup="x(this)" /><br /></div>
                            </div>
                            <div class="row">
                                <div class="col-2"><label for="slug">Slug</label><span style="color: red;">*</span></div>
                                <div class="col-8"><input type="text" name="slug" id="slug" class="form-control" readonly /><br /></div>
                            </div>
                            <div class="row">
                                <div class="col-2"><label for="autoplay" class="mb-0">Autoplay</label></div>
                                <div class="col-8"><input type="checkbox" name="autoplay" /></div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="col-10 pl-0 mt-3">
                            <button class="px-4 btn btn-light add-slide-btn" onclick="event.preventDefault();slideadd();" id="addslide">Add Slide</button><br />
                            <button type="submit" class="btn btn-primary mt-2 px-4">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                <input type="file" selector="slide-image" class="custom-file-input invisible" name="img[]" onchange="showPreview(this)">
                <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" name="slider_image_mobile[]" onchange="showPreview(this)">
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', ['switch' => 1])

<script>
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
                previewDiv.html('<img src="' + e.target.result + '" style="max-width: 100%; max-height: 150px;">');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
