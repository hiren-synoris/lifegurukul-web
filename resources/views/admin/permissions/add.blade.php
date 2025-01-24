@extends('admin.layouts.app')
@section('scripts')
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection
@section('right-section')
    {!! redirect_to_back(route('permissions.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
  <div class="col-10 bg-design">
    <form method="POST" action="{{ url('backoffice/permissions') }}">
      @csrf
      <div class="d-flex justify-content-center mb-3">
        {{-- <h3>Add Permission</h3> --}}
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

      <div class="form-group checkbox-area">
        <input type="checkbox" class="" name="permission" id="permission">
        <label for="permission">Module Permissions</label>
      </div>
      <div class="form-group">
        <label for="name">Name</label><span style="color: red">*</span>
        <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name">
        @error('name')
        <div class="text text-danger">{{ $message }}</div>
    @enderror
      </div>
      {{-- <div class="form-group clearfix">
          <div class="icheck-primary d-inline">
            <input type="checkbox" id="active_checkbox" checked>
            <label for="active_checkbox">Active</label>
          </div>
        </div> --}}
      <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
    </form>
  </div>
</div>
@endsection
