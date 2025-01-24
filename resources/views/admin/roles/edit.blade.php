@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection
@section('right-section')
{!! redirect_to_back(route('roles.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
  <div class="col-10 bg-design">
    <form method="POST" action="{{ url('backoffice/roles/'.$role->id) }}">
      @csrf
      @method('PUT')
      <div class="d-flex justify-content-center mb-3">
        <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit ".ucwords($role->display_name) }}</h3>
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
    <label for="name">Name <span style="color:red;">*</span></label>
    <input type="text" class="form-control" name="name" id="name" value="{{ $role->name }}">
    @error('name')
    <div class="text text-danger">{{ $message }}</div>
    @enderror
  </div>
  <div class="form-group">
    <label for="name">Display Name</label>
    <input type="text" class="form-control" name="display_name" value="{{ $role->display_name }}" id="display_name">
    @error('display_name')
    <div class="text text-danger">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <strong>Permissions:</strong>
    {{-- <div class="icheck-success">
            <input type="checkbox" id="permission_select_all" name="permission_select_all">
            <label for="permission_select_all">Select all</label>
          </div> --}}
  </div>

  @foreach($permissions as $key => $value)
  @php $box_val = str_replace(" ", "_", Str::lower($key).'_box'); @endphp
  <div class="select-permissions d-inline-block col-12 p-0 m-0 border-bottom pb-1 mb-3">
    <div class="icheck-success col-md-12">
      <input type="checkbox" id="{{ $box_val }}" name="{{ $box_val }}" class="permission_box_header" onclick="select_role_via_per('{{ $box_val }}')">
      <label for="{{ $box_val }}">



        @if(ucwords($key) == "Users")
        Admin
        @elseif(ucwords($key) == "Subadmin")
        Other Users
        @elseif(ucwords($key) == "Support")
        Support Ticket
        @elseif(ucwords($key) == "Browse_admin_notifications")
        Notification
        @elseif(ucwords($key) == "Learner Report")
        Report
       
        @else
        {{str_replace("_", " ", ucwords($key))}}
        @endif

        : </label><br />
    </div>
    <div class="row p-0 m-0">
      @foreach($value as $key1 => $value1)
      <div class="icheck-success ml-3 mb-4">
        <input class="permission_box {{ $box_val }}" type="checkbox" id="permission_{{$value1['id']}}" name="permission[{{ $value1['id'] }}]" @if($assigned_permissions->contains($value1['id'])) {{ ('checked') }} @endif onclick="selectAll('{{ $box_val }}')">
        <label for="permission_{{$value1['id']}}">
          @if(Str::contains($value1['name'], "_".strtolower(str_replace(" ", "_", $key))))
            @if(Str::ucfirst((explode('_', $value1['name']))[0]) == "Browse")
              List
            @elseif(Str::ucfirst((explode('_', $value1['name']))[0]) == "Read")
              View
            @else
              {{ Str::ucfirst((explode('_', $value1['name']))[0]) }}
            @endif
          @else
            {{ ucwords(str_replace('_', ' ', $value1['name'])) }}
          @endif
        </label>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach
  <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
  </form>
</div>
</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {
    let role_total = "{{ json_encode($role_total) }}";
    let temp = role_total.replace(/&quot;/g, '"');
    let role_total_formatted = JSON.parse(temp);
    let in_total = "{{ $in_total }}";
    let role_total_count = "{{ $role_total_count }}";
    let permission_count = "{{ $permission_count }}";

    //   $(document).on("click", "#permission_select_all", function(e){
    //     if ($(this).is(':checked')) {
    //       $(".permission_box").prop('checked', true);
    //       $(".permission_box_header").prop('checked', true);
    //     }
    //     else{
    //       $(".permission_box").prop('checked', false);
    //       $(".permission_box_header").prop('checked', false);
    //     }
    //   })

    $(role_total_formatted).each(function(key, value) {
      if (value.total >= 5) {
        $("#" + value.permissions + "_box").prop('checked', true);
      } else {
        $("#" + value.permissions + "_box").prop('checked', false);
        // $("."+value.permissions+"_box").prop('checked', false);
      }
    });

    /* This must be kept last in the erady function */

    if ($('.permission_box_header').length == $('.permission_box_header:checked').length) {
      $("#permission_select_all").trigger('click');
    }
    /* if ((role_total_count * 5) == in_total && role_total_formatted.length == permission_count) {
        $("#permission_select_all").trigger('click');
    } */

  });


  function select_role_via_per(role) {
    if ($("#" + role).is(':checked')) {
      $("." + role).prop('checked', true);
    } else {
      $("." + role).prop('checked', false);
    }
  }

  function selectAll(role) {
    if ($('.' + role + ':checked').length == $('.' + role).length) {
      $('#' + role).prop('checked', true);
    } else {
      $('#' + role).prop('checked', false);
    }
  }
</script>
@endsection