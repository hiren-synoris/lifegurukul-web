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
    <div class="collapse multi-collapse old" style="display: none;">
      <div class="card mb-3">
        <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4">
            <div>Image Slide</div>
            <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
        </div>
        <hr>
        <div class="row no-gutters">
          {{-- <form action=""> --}}
          <div class="col-md-4 d-flex justify-content-center align-items-center p-4 ">
            <a href="" class="w-100 h-100">
              {{-- $('#customFile').trigger(); --}}
              <div class="border border-black p-4 d-flex justify-content-center align-items-center" style="height: 100%;" onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file]').trigger('click');">
                {{-- $('#customFile').trigger('click'); --}}
                <i class="fas fa-image fa-4x"></i>
              </div>
            </a>
          </div>
          <div class="col-md-8 right_section">
            <div class="card-body">
              {{-- <h5 class="card-title"></h5> --}}
              <div class="row">
                <div class="col-12">
                  <div class="row">
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
                  </div>
                  <div class="row mt-4">
                    <div class="col-4">
                      <label for="">Call to Action Text</label>
                      <input type="text" class="form-control" name="c2a[]">
                    </div>
                    <div class="col-4">
                      <label for="">Call to Action URL</label>
                      <input type="text" class="form-control" name="c2au[]">
                    </div>
                    <div class="col-4">
                      <label for="" style="visibility: hidden">&nbsp;</label><br>
                      <input type="checkbox" name="newwin[]"> <span>&nbsp;Open in new window</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <input type="file" class="custom-file-input invisible" id="customFile" name="img[]">
          </div>
        {{-- </form> --}}
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
              <div class="col-2 d-flex flex-column card px-0" style="background-color: #eee !important;max-height: 200px !important;min-height: 200px !important;">
                <div class="d-flex flex-column">
                  <div class="w-100 bg-white p-2 text-center" style="font-size: 18px;">Slider Information</div>
                  <div class="my-4 pr-0" id="sidemenu">
                    <div class="my-1 pr-0 ml-2">
                      <a class="text-dark mx-2 " href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" id="slides">Slides</a>
                    </div>
                    <div class="my-1 ml-2 pr-0">
                      <a class="text-dark mx-2 " href="#multiCollapseExample2" type="button" aria-expanded="false" aria-controls="multiCollapseExample2" id="settings">Settings</a>

                    </div>
                  </div>
                  {{-- <button class="btn btn-primary" type="button" data-toggle="collapse" data-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2">Toggle both elements</button> --}}
                </div>

              </div>
              <div class="col-10">
                <div class="row">
                  <div class="col-12 cp1">
                    <div class="collapse multi-collapse first" id="multiCollapseExample1">
                      <div class="card mb-3">
                        <div class="row no-gutters px-4 d-flex justify-content-between align-items-center pt-4" style="height: 100%;">
                            <div>Image Slide</div>
                            <button type="button" class="close" aria-label="Close" onclick="event.preventDefault();$(this).closest('.multi-collapse').remove();"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <hr>
                        <div class="row no-gutters">
                          {{-- <form action=""> --}}
                          <div class="col-md-4 d-flex justify-content-center align-items-center p-4 " style="display:block !important">
                          <div style="height:50%;">
                          <div><small class="text-gray">Desktop Image(Resolution: 1920px * 670px)</small></div>
                            <a href="" class="w-100 h-100 justify-content-center">
                              <div class="border border-black p-4 d-flex justify-content-center align-items-center"  onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file]').trigger('click');">
                                <i class="fas fa-image fa-4x"></i>
                              </div>
                            </a>
                          </div>
                          <div style="height:50%;">
                          <div><small class="text-gray">Mobile Image(Resolution: 512px * 244px)</small></div>
                            <a href="" class="w-100 h-100 justify-content-center">
                              <div class="border border-black p-4 d-flex justify-content-center align-items-center" style="width:100%;cursor:pointer;" onclick="event.preventDefault();$(this).parents().closest('.no-gutters').find('.right_section').find('input[type=file][selector=slide-image-mobile]').trigger('click');">
                                <i class="fas fa-image fa-4x"></i>
                              </div>
                            </a>
                          </div>
                          </div>
                          <div class="col-md-8 right_section">
                            <div class="card-body">
                              {{-- <h5 class="card-title"></h5> --}}
                              <div class="row">
                                <div class="col-12">
                                  <div class="row">
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
                                  </div>
                                  <div class="row mt-4">
                                    <div class="col-4">
                                      <label for="">Call to Action Text</label>
                                      <input type="text" class="form-control" name="c2a[]">
                                    </div>
                                    <div class="col-4">
                                      <label for="">Call to Action URL</label>
                                      <input type="text" class="form-control" name="c2au[]">
                                    </div>
                                    <div class="col-4">
                                      <label for="" style="visibility: hidden">&nbsp;</label><br>
                                      <input type="checkbox" name="newwin[]"> <span>&nbsp;Open in new window</span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <input type="file" selector="slide-image" class="custom-file-input invisible" id="customFile" name="img[]">
                            <input type="file" selector="slide-image-mobile" class="custom-file-input invisible" id="customFile" name="slider_image_mobile[]">
                          </div>
                        {{-- </form> --}}
                        </div>
                      </div>
                    </div>
                    {{-- <button type="submit" class="btn btn-primary mt-2">Save</button> --}}
                  </div>


                  <div class="col-12 cp2 ml-2">
                    <div class="collapse multi-collapse card p-4" id="multiCollapseExample2">
                      {{-- <form action="{{ url('slide/create') }}"> --}}
                        @csrf
                        <div class="row">
                          <div class="col-2">
                            <label for="name">Name</label><span style="color: red">*</span>
                          </div>
                          <div class="col-8">
                            <input type="text" name="name" class="form-control" onkeyup="x(this)"><br>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-2">
                            <label for="slug">Slug</label><span style="color: red">*</span>
                          </div>
                          <div class="col-8">
                            <input type="text" name="slug" id="slug" class="form-control" readonly><br>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-2">
                            <label for="autoplay" class="mb-0">Autoplay</label>
                          </div>
                          <div class="col-8">
                            <input type="checkbox" name="autoplay">
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="col-10 pl-0">
                    <button class="px-4 btn btn-light" onclick="event.preventDefault();slideadd();" id="addslide">Add Slide</button><br>
                    <button type="submit" class="btn btn-primary mt-2 px-4">Save</button>
                  </div>
                </div>
              </div>
          </form>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1
    ])
    <script>
      $(document).ready(function(){
        $("#multiCollapseExample2").collapse('hide');
        $(".first").collapse('show');
        $("#slides").click(function(){
          $("#addslide").show();
          $("#multiCollapseExample2").collapse('hide');
          $(".cp1").show();
        });
        $("#settings").click(function(){
          $(".cp1").hide();
          $("#addslide").hide();
          $("#multiCollapseExample2").collapse('show');
        });

        $("#sidemenu").children().removeClass('bg-white').removeClass('py-2');
        $("#sidemenu div:first").addClass('bg-white').addClass('py-2');
      });

      function slideadd(){
        var x = $(".old").clone().removeClass('old');
        var y = $('.cp1').find('.multi-collapse').length;
        if(y > 0 && y != undefined){
          $(x).insertAfter($(".cp1").find('.multi-collapse').last()).show();
        }
        else{
          $(".cp1").prepend(x);
          $(".cp1").children().show();
        }
      }

      $("#sidemenu div a").click(function(){
        $("#sidemenu").children().removeClass('bg-white').removeClass('py-2');
        $(this).parent().addClass('bg-white').addClass('py-2');
      });

      function x(z){$('#slug').val();var y = $(z).val().replace(/ /g, '_').toLowerCase();$('#slug').val(y).text(y);}


    </script>
@endsection
