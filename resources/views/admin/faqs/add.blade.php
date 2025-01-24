@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style',[
            'select2CSS' => 1,
            'summerNoteCSS' => 1,
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('faq.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
      <form method="POST" action="{{ url('backoffice/faq')}}" enctype='multipart/form-data'>
        @csrf

        <div class="d-flex justify-content-center mb-3">
          <h3>
          {{-- Add --}}
          </h3>
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
          <label for="question">Question <span style='color:red;'>*</span></label>
          <input type="text" class="form-control" name="question" id="question" value="{{old('question')}}">
          @error('question')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group">
          <label for="summernote">Answer <span style='color:red;'>*</span></label><br>
          <textarea class="summernote-editor" name='answer' id='summernote'>{{old('answer')}}</textarea>
          @error('answer')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group">
          <label for="order">Order</label>
          <input type="number" class="form-control" name="order" pattern="[0-9]*" value="{{old('order')}}">
          @error('order')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group">
          {{-- <div class="col-3 swich-area">
              <label for="status">Status</label>
              <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" checked data-bootstrap-switch>
          </div> --}}
            @include('admin.layouts.partials.buttons.toggle-button',[
                'dataValue' => 1,
                'id' => 'status',
                'name' => 'status',
                'toggleBtnText' => 'Status',
            ])
        </div>
        <button type="submit" id="butsave"  class="btn btn-inline-block submit-btn btn-primary">Submit</button>
      </form>
    </div>
  </div>


@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1,
        'select2' => 0,
        'summerNote' => 1,
    ])
@endsection
