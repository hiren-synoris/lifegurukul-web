@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection
@section('right-section')
    {!! redirect_to_back(route('permissions.index')) !!}
@endsection
@section('content')
  <div class="row justify-content-center">
    <div class="col-10 bg-design">
      <form method="POST" action="{{ url('backoffice/permissions/'.$permission->id) }}">
        @csrf
        @method('PUT')
        <div class="d-flex justify-content-center mb-3">
          <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit ".$permission->name }}</h3>
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
          <label for="name">Name</label>
          <input type="text" class="form-control" name="name" id="name" value="{{ $permission->name }}">
          @error('name')
        <div class="text text-danger">{{ $message }}</div>
    @enderror
        </div>

        <div class="form-group">
          <label for="module_name">Module Name</label>
          <input type="text" class="form-control" name="module_name" id="module_name" value="{{ $permission->module_name }}">
          @error('module_name')
        <div class="text text-danger">{{ $message }}</div>
    @enderror
        </div>
        {{-- <div class="form-group clearfix">
          <div class="icheck-primary d-inline">
            <input type="checkbox" id="active_checkbox" @if(is_null($permission->deleted_at)) {{ "checked" }} @endif>
            <label for="active_checkbox">Active</label>
          </div>
        </div> --}}
        <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
      </form>
    </div>
  </div>
@endsection