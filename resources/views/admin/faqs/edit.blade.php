@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style',[
            'select2CSS' => 1,
            'summerNoteCSS' => 1
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('faq.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
      <form method="POST" action="{{ url('backoffice/faq/'.$faq->id)}}" enctype='multipart/form-data'>
        @csrf
    @method('PUT')
    <div class="d-flex justify-content-center mb-3">
                    <h3>Edit FAQ</h3>
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}
        <div class="form-group">
          <label for="question">Question <span style='color:red;'>*</span></label>
          <input type="text" class="form-control" name="question" id="question" value="{{$faq->question}}">
          @error('question')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group">
          <label for="summernote">Answer <span style='color:red;'>*</span></label><br>
          <textarea class="summernote-editor" name='answer' id='summernote'>{{ $faq->answer }}</textarea>
          @error('answer')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group">
          <label for="order">Order</label>
          <input type="number" class="form-control" name="order" pattern="[0-9]*"  value="{{$faq->order}}">
          @error('order')
          <div class="text text-danger">{{ $message }}</div>
      @enderror
        </div>
        <div class="form-group row">
        {{-- <div class="col-3 swich-area">
            <label for="status">Status</label>
            <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" {{ $faq->status == 1 ? 'checked' : '' }} data-bootstrap-switch>
        </div> --}}
            @include('admin.layouts.partials.buttons.toggle-button',[
                'dataValue' => $faq->status,
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
        'select2' => 1,
        'summerNote' => 1
    ])
@endsection