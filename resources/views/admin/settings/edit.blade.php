@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection
@section('right-section')
    {!! redirect_to_back(route('settings.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        <form method="POST" action="{{ url('backoffice/settings/'.$settings->id) }}" enctype="multipart/form-data" id="settingForm">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit ".$settings->key }}</h3>
            </div>
            {{-- @if($errors->any())
            <div class="alert alert-danger p-3">
                <ul>
                    @foreach($errors->all() as $key => $value)
                    <li class="text-white">{{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            @endif --}}
            <div class="form-group">
                <label for="display_name">Display Name</label><span style="color: red">*</span>
                <input type="text" class="form-control" name="display_name" value="{{ $settings->display_name }}"
                    id="display_name">
                    @error('display_name')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
            </div>
            {{-- <div class="form-group">
                <label for="slug">Slug</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ $settings->slug }}" name="slug" id="slug" readonly>
            </div> --}}
            <div class="form-group">
                <label for="key">Key</label><span style="color: red">*</span>
                <input type="text" class="form-control" name="key" value="{{ $settings->key }}" id="key" style="pointer-events: none;">
                @error('key')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
            </div>


            <div class="form-group">
                <label for="setting_type">Type</label><span style="color: red">*</span>
                <select id="setting_type" class="form-control setting_type" disabled>
                    <option value="">Select Type</option>
                    <option @if($settings->setting_type == 'text') selected @endif value="text">Text box
                    </option>
                    <option value="number_coin" @if($settings->setting_type == 'number_coin') selected @endif >Text box coin</option>
                    <option @if($settings->setting_type == 'textarea') selected @endif value="textarea">Text Area
                    </option>
                    <option @if($settings->setting_type == 'file') selected @endif value="file">File</option>
                    <option @if($settings->setting_type == 'boolean') selected @endif value="boolean">Boolean</option>
                    <option @if($settings->setting_type == 'checkbox') selected @endif value="checkbox">Checkbox</option>
                </select>
            </div>
            <input type="hidden" name="setting_type" value="{{$settings->setting_type}}" />
            @error('setting_type')
            <div class="text text-danger">{{ $message }}</div>
        @enderror

        <div class="form-group">
            <label for="Value">Value</label><span style="color: red">*</span>
            <div class="setting_type_value">
                @if($settings->setting_type == 'number_coin')
                    <input type="number" class="form-control isNumericKey" name="value" value="{{ $settings->value }}" id="value">
                @elseif($settings->setting_type == 'text')
                    <input type="text" class="form-control" name="value" value="{{ $settings->value }}" id="value">
                @elseif ($settings->setting_type == 'textarea')
                    <textarea name='value' id='value' class='form-control'>{{ $settings->value }}</textarea>
                @elseif ($settings->setting_type == 'file')
                    <input type='file' name='value' id='value' class='form-control' required/>
                @elseif ($settings->setting_type == 'boolean')
                    @php
                        $statusMode = $settings->value == 1 || strtolower($settings->value) == 'on' ? 1 : 0;
                    @endphp
                    @include('admin.layouts.partials.buttons.toggle-button',[
                        'dataValue' => $statusMode,
                        'id' => 'value',
                        'name' => 'value',
                        'toggleBtnText' => 'Value',
                    ])
                @elseif ($settings->setting_type == 'checkbox')
                    <input type="checkbox" id="website" name="platform[3]" @if(!empty($settings->value)){{in_array('3',stringToArray($settings->value)) ? 'checked':''}}@endif value="3"/><label for="website"> Website</label>
                    <input type="checkbox" id="android" name="platform[1]" @if(!empty($settings->value)){{in_array('1',stringToArray($settings->value)) ? 'checked':''}}@endif value="1"/><label for="android"> Android</label>
                    <input type="checkbox" id="ios" name="platform[2]" @if(!empty($settings->value)){{in_array('2',stringToArray($settings->value)) ? 'checked':''}}@endif value="2"/><label for="ios"> Ios</label>
                @endif
            </div>
            @error('value')
            <div class="text text-danger show_div">{{ $message }}</div>
            @enderror
        </div>

            @if($settings->setting_type == 'file')
              <div class="form-group">
                <input class="form-control old-settings-value" type="hidden" name="old_value" id="old_value" value="{{ $settings->value }}">
                  <div class="setting-value-image">
                    <img src='{{ asset(Storage::url($settings->value)) }}'>
                  </div>
              </div>
            @endif
            <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list',[
    'switch' => 1,
    'select2' => 1
])
<script>
$(document).ready(function() {
    $("#setting_type").on('change', function() {
        $(".setting_type_value").children().last().remove();
        var value = document.getElementById("setting_type").value;
        console.log(value);
        if (value == '') {

            $(".setting_type_value").children().last().remove();
             $(".setting-value-image").children().last().remove();
        } else if (value == 'text') {
            $(".setting_type_value").append(
                "<input type='text' name='value' id='value' class='form-control'/>");
                 $(".setting-value-image").children().last().remove();
        } else if (value == 'textarea') {
            $(".setting_type_value").append(
                "<textarea name='value' id='value' class='form-control'></textarea>");
                 $(".setting-value-image").children().last().remove();
        } else if (value == 'boolean') {
            // $(".setting_type_value").append("<input type='checkbox' data-on-text='On' data-off-text='Off' name='boolean' >");
            // $("[name='boolean']").bootstrapSwitch();
            //      $(".setting-value-image").children().last().remove();

            $(".setting_type_value").append('<div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="value" id="value"><label class="custom-control-label" for="value">ON</label></div>');
            $(".setting-value-image").children().last().remove();

        } else if (value == 'checkbox') {
            $(".setting_type_value").append("<div><input type='checkbox' id='website' name='platform[1]' {{in_array('1',(array)($settings->value)) ? 'checked':''}} value='1'/><label for='website'> Website</label><input type='checkbox' name='platform[2]' id='android' {{in_array('2',(array)($settings->value)) ? 'checked':''}} value='2'/><label for='android'> Android</label><input type='checkbox' id='ios' name='platform[3]' {{in_array('3',(array)($settings->value)) ? 'checked':''}} value='3'/><label for='ios'> Ios</label></div>");
            $("[name='boolean']").bootstrapSwitch();
                 $(".setting-value-image").children().last().remove();
        } else {
            // $(".setting_type_value").append(
            //     "<input type='file' name='value' id='value' class='form-control'/>");
            //      $(".setting-value-image").children().last().remove();

            $(".setting_type_value").append("<input type='file' name='value' id='value' class='form-control' onchange='return fileValidation()'/>");
            $(".setting-value-image").children().last().remove();
        }
    });
});
$("select").change(function(){
    $("#file_error").remove();
  });
// $('form').submit(function(e){
//     e.preventDefault();
//     if($("#setting_type").find(":selected").val() == 'file'){
//       var img_size = $('form').find('input[type="file"]')[0].files[0].size;
//       if(img_size > 8000){
//           e.preventDefault();
//           $("#file_error").remove();
//           $('#value').closest('.form-group').append("<p id='file_error' style='color:red'>File size can not be greater than 8 MB</p>");
//       }
//     }

//   });

$(document).on(".isNumericKey","keyup",function(){
    alert()
  })

  function isNumericKey(event) {

            const charCode = (event.which) ? event.which : event.keyCode;
            return !(charCode > 31 && (charCode < 48 || charCode > 57));
        }
function fileValidation() {
          var fileInput = document.getElementById('value');
          var filePath = fileInput.value;
          if (fileInput.files[0].size > 8000000){
            $("#file_error").remove();
            $('#value').closest('.form-group').append("<p id='file_error' style='color:red'>File size can not be greater than 8 MB</p>");
          }else{
            $("#file_error").empty();
          }
      }
</script>
@endsection
