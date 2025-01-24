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
    <form method="POST" action="{{ url('backoffice/roles') }}">
      @csrf
      <div class="d-flex justify-content-center mb-3">
        {{-- <h3>Add Role</h3> --}}
      </div>
      {{-- @if($errors->any())
            <div class="alert alert-danger p-1">
              <ul>
                @foreach($errors->all() as $key => $value)
                  <li class="text-white">{{ $value }}</li>
      @endforeach
      </ul>
  </div>
  @endif --}}
  <div class="form-group">
    <label for="name">Name</label><span style="color: red">*</span>
    <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name">
    @error('name')
    <div class="text text-danger">{{ $message }}</div>
    @enderror
  </div>

  <div class="form-group">
    <label for="name">Display Name</label>
    <input type="text" class="form-control" value="{{ old('display_name') }}" name="display_name" id="display_name">
  </div>
  <div class="form-group">
    <strong>Permissions:</strong><br>
  </div>
  {{-- <div class="icheck-success">
              <input type="checkbox" id="permission_select_all" name="permission_select_all">
              <label for="permission_select_all">Select all</label>
            </div> --}}

  @foreach($permissions as $key => $value)
  <div class="select-permissions d-inline-block col-12 p-0 m-0 border-bottom pb-1 mb-3">
    <div class="icheck-success col-md-12">
      <input type="checkbox" id="{{ Str::lower($key).'_box' }}" name="{{ Str::lower($key).'_box' }}" class="permission_box_header" onclick="select_role_via_per('{{ Str::lower($key).'_box' }}')">
      <label for="{{ Str::lower($key).'_box' }}">
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
        @elseif(ucwords($key) == "Forums")
        Discussion
        @else
        {{str_replace("_", " ", ucwords($key))}} 
        @endif
        : </label><br />
    </div>
    <div class="row p-0 m-0">
      @foreach($value as $key1 => $value1)
      <div class="icheck-success ml-3 mb-4">
        <input class="permission_box {{ Str::lower($key).'_box' }}" type="checkbox" id="permission_{{$value1['id']}}" name="permission[{{ $value1['id'] }}]" onclick="selectAll('{{ Str::lower($key).'_box' }}')">
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
          {{ Str::ucfirst((explode('_', $value1['name']))[0]) }}
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
</div>
@endsection
@section('scripts')
<script>
  // $(document).ready(function(){
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
  // });
  $(document).on("click", ".permission_box", function(e) {

    let checked_box = $(this).parents().closest('.select-permissions').find('input:checkbox:checked').length;
    let total = $(this).parents().closest('.select-permissions').find('input').length;

    if (checked_box < total) {
      $(this).parents().closest('.select-permissions').find('div:first input').prop('checked', false);
    }

    if (checked_box == total) {
      $(this).parents().closest('.select-permissions').find('div:first input').prop('checked', true);
    }

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