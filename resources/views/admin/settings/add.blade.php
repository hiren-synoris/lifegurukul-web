@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet"
    href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection
@section('right-section')
    {!! redirect_to_back(route('settings.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
  <div class="col-10 bg-design">



    <form method="POST" action="{{ url('backoffice/settings') }}" enctype="multipart/form-data" id="settingForm">
      @csrf
      <div class="d-flex justify-content-center mb-3">
        {{-- <h3>Add Settings</h3> --}}
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
        <input type="text" class="form-control" name="display_name" value="{{ old('display_name') }}" id="display_name" onBlur="nameConvertKey()">
        @error('display_name')
                <div class="text text-danger">{{ $message }}</div>
            @enderror
      </div>
      <div class="form-group">
          <label for="key">Key</label><span style="color: red">*</span>
          <input type="text" class="form-control" value="{{ old('key') }}" name="key" id="key" readonly>
          @error('key')
                <div class="text text-danger">{{ $message }}</div>
            @enderror
      </div>
      <!-- <div class="form-group">
        <label for="key">Key</label><span style="color: red">*</span>
        <input type="text" class="form-control" value="{{ old('key') }}" name="key" id="key">
      </div> -->



      <div class="form-group">
        <label for="setting_type">Type</label><span style="color: red">*</span>
        <select name="setting_type" id="setting_type" class="form-control setting_type" >
          {{-- <option value="">Select Type</option> --}}
          <option value="text" @if(old('setting_type') == 'text') selected @endif>Text box</option>
          <option value="number_coin" @if(old('setting_type') == 'number_coin') selected @endif>Number</option>
          <option value="textarea" @if(old('setting_type') == 'textarea') selected @endif>Text Area</option>
          <option value="file" @if(old('setting_type') == 'file') selected @endif>File</option>
          <option value="boolean" @if(old('setting_type') == 'boolean') selected @endif>Boolean</option>
          <option @if(old('setting_type') == 'checkbox') selected @endif value="checkbox">Checkbox</option>
        </select>
            @error('setting_type')
                <div class="text text-danger">{{ $message }}</div>
            @enderror
      </div>

      <div class="form-group">
        <label for="Value" class="" style="display:nosne">Value</label><span  class="show_didv" style="color: red;displady:none">*</span>
         <div class="setting_type_value">
             <input type="text" class="form-control text_box" name="value" value="{{ old('value')  }}" id="value">

            {{-- @if (old('setting_type')  == 'textarea')
            <textarea name='value' id='value' class='form-control'>{{ old('value')  }}</textarea>
            @elseif (old('setting_type')  == 'file')
            <input type='file' name='value' id='value' class='form-control'/> --}}
            {{-- @elseif (old('setting_type')  == 'boolean') --}}
            {{-- <input type="checkbox" name="value" id="value" data-on-text="On" data-off-text="Off" data-off-color="danger" data-on-color="success" {{ old('value') == 1 || strtolower(old('value')) == 'on' ? 'checked' : '' }} data-bootstrap-switch> --}}
            {{-- @php
              $statusMode = old('value') == 1 || strtolower(old('value')) == 'on' ? 1 : '';
            @endphp
              @include('admin.layouts.partials.buttons.toggle-button',[
                  'dataValue' => $statusMode,
                  'id' => 'value',
                  'name' => 'value',
                  'toggleBtnText' => 'Value',
              ]) --}}
            {{-- @elseif (old('setting_type') == 'checkbox')
              <div>
                  <input type="checkbox" id="website" name="platform[3]" {{old('platform') == 3 ? 'checked':''}} /><label for="website"> Website</label>
                  <input type="checkbox" id="android" name="platform[1]" {{old('platform') == 1 ? 'checked':''}} /><label for="android"> Android</label>
                  <input type="checkbox" id="ios" name="platform[2]" {{old('value') == 2 ? 'checked':''}} /><label for="ios"> Ios</label>
              </div>
            @endif --}}
            @error('value')
                <div class="text text-danger show_div">{{ $message }}</div>
            @enderror
        </div>
        <!-- <input type="text" class="form-control" name="designation" id="designation"> -->
      </div>
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
    // $(document).ready(function() {
    //  alert("")

    // $(".setting_type").change(function(){
    //     alert($(this).val())
    // })

      $(".setting_type").on('change', function() {
        // $(".show_div").show()
         $(".setting_type_value").children().last().remove();
          var value = document.getElementById("setting_type").value;
          if (value == '') {
            $(".show_div").hide()
            $(".setting_type_value").children().last().remove();

          }
          else if (value == 'number_coin') {
            $(".show_div").show()
            $(".text_box").hide()
            $(".setting_type_value").children().last().remove();
            $(".setting_type_value").append("<input type='number' name='value' id='value' class='form-control isNumericKey'/>");
          }
          else if (value == 'text') {
            $(".show_div").show()
            $(".setting_type_value").children().last().remove();
            $(".setting_type_value").append("<input type='text' name='value' id='value' class='form-control'/>");
          }

          else if (value == 'textarea'){
            $(".show_div").show()
            $(".text_box").hide()

            $(".setting_type_value").children().last().remove();
            $(".setting_type_value").append("<textarea name='value' id='value' class='form-control'></textarea>");
          }
          else if (value == 'boolean'){
            $(".setting_type_value").children().last().remove();
            $(".text_box").hide()
            $(".show_div").show()
            // $(".setting_type_value").append('<input type="checkbox" name="value" id="value" data-on-text="On" data-off-text="Off" data-off-color="danger" data-on-color="success" data-bootstrap-switch>');
            // $("[name='value']").bootstrapSwitch();
            $(".setting_type_value").append('<div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="value" id="value"><label class="custom-control-label" for="value">ON</label></div>');
          }else if (value == 'checkbox') {
            $(".setting_type_value").children().last().remove();
            $(".text_box").hide()
            $(".show_div").show()
            $(".setting_type_value").append("<div><input type='checkbox' id='website' name='platform[1]' value='1'/><label for='website'> Website</label><input type='checkbox' name='platform[2]' id='android' value='2'/><label for='android'> Android</label><input type='checkbox' id='ios' name='platform[3]' value='3'/><label for='ios'> Ios</label></div>");
            $("[name='boolean']").bootstrapSwitch();
                 $(".setting-value-image").children().last().remove();
        }
          else{
            $(".show_div").show()
            $(".text_box").hide()
            $(".setting_type_value").children().last().remove();
            $(".setting_type_value").append("<input type='file' name='value' id='value' class='form-control' onchange='return fileValidation()'/>");

          }
      });

//   });

  $(document).on(".isNumericKey","keyup",function(){
  })

  function isNumericKey(event) {

            const charCode = (event.which) ? event.which : event.keyCode;
            return !(charCode > 31 && (charCode < 48 || charCode > 57));
        }
  function nameConvertKey() {
      var name = document.getElementById("display_name");
      var nameValue = name.value;
      var Key = convertToKey(nameValue);
      var input = $("#key");
      input.val("");
      input.val(input.val() + Key);
    //   alert()
  }

  function convertToKey(Text) {
      return Text.toLowerCase()
      .replace(/[^\w ]+/g, '')
      .replace(/ +/g, '');
  }
  $("select").change(function(){
    $("#file_error").remove();
  });
//   $('#settingForm').submit(function(e){
//     e.preventDefault();
//     if($("#setting_type").find(":selected").val() == 'file'){
//       var img_size = $('form').find('input[type="file"]')[0].files[0].size;
//       if(img_size > 8000){
//           e.preventDefault();
//           $("#file_error").remove();
//           $('#value').closest('.form-group').append("<p id='file_error' style='color:red'>File size can not be greater than 8 MB</p>");
//       }
//     }else{
// $()
//     }

//   });
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
