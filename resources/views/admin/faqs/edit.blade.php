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
        <div class="form-group">
            <label for="question">Media</label>
            <input type="file" name="media[]" multiple>
            @error('question')
            <div class="text text-danger">{{ $video }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label>Current Media:</label>
            @if ($faq->media)
                @php
                    $mediaFiles = json_decode($faq->media, true);
                @endphp

                @if(is_array($mediaFiles) && count($mediaFiles) > 0)
                    <div class="row">
                        @foreach($mediaFiles as $index => $file)
                            @php
                                $extension = pathinfo($file, PATHINFO_EXTENSION);
                            @endphp
                            <div class="col-md-3 mb-3" id="media-item-{{ $index }}">
                                @if(in_array($extension, ['jpg','jpeg','png','gif','svg']))
                                    <img src="{{ asset('storage/' . $file) }}" alt="Media Image" class="img-fluid rounded border" style="max-height: 200px;">
                                @elseif(in_array($extension, ['mp4','webm','mov','ogg']))
                                    <video controls width="100%" height="auto">
                                        <source src="{{ asset('storage/' . $file) }}" type="video/{{ $extension }}">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <p>Unsupported file type: {{ $extension }}</p>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-danger btn-block mt-2 delete-media-btn"
                                        data-id="{{ $faq->id }}"
                                        data-path="{{ $file }}"
                                        data-index="{{ $index }}">
                                    Delete
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://common.olemiss.edu/_js/sweet-alert/sweet-alert.css">
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1,
        'select2' => 1,
        'summerNote' => 1
    ])
    <script>
    $(document).ready(function () {
        $(".delete-media-btn").click(function () {
            Swal.fire({
                title: "Are you sure Want to delete it?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    var faqId = $(this).data("id");
                    var mediaPath = $(this).data("path");
                    var index = $(this).data("index");
                    $.ajax({
                        url: "{{ url('backoffice/faq/') }}/" + faqId + "/delete-media",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            media_path: mediaPath,
                            faqId: faqId,
                            index: index,
                        },
                        success: function (response) {
                           if (response.status === "success") {
                                Swal.fire('Deleted!', response.msg, 'success');
                                $("#media-item-" + index).fadeOut(500, function () {
                                    $(this).remove();
                                });
                            } else {
                                Swal.fire('Error!', response.msg, 'error');
                            }
                        },
                        error: function (xhr) {
                            console.log(xhr.responseText);
                            alert("An error occurred!");
                        },
                    });
                }
            });
        });
    });
</script>
@endsection
