@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('support-ticket.index')) !!}
@endsection
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'summerNoteCSS' => 1,
    ])
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($support) && !empty($support->id))
          <form method="POST" action="{{ route('support-ticket.update', ['support_ticket' => $support->id]) }}">
              @csrf
              @method('PUT')
              <div class="d-flex justify-content-center mb-3">
                  <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : 'Edit' }}
                  </h3>
              </div>

              {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

              <div class="form-group">
                  <label for="name">Subject</label>
                  <input type="text" class="form-control" name="subject" id="subject" value="{{ $support->subject ?? old('subject') }}">
                  @error('subject')
                  <div class="text text-danger">{{ $message }}</div>
              @enderror
                </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control summernote-editor" id="description" name="description">
                    {{ $support->description ?? old('description') }}
                </textarea>
                @error('description')
                <div class="text text-danger">{{ $message }}</div>
            @enderror
            </div>
              <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
          </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', [
    'summerNote' => 1,
    'validateJS' => 1,
])
<script>
    $(document).ready(function() {
        $('.mobile').keyup(function () {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });
    });
</script>
@endsection
