@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('contact.index')) !!}
@endsection
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'summerNoteCSS' => 1,
    ])
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($contact) && !empty($contact->id))
          <form method="POST" action="{{ route('contact.update', ['contact' => $contact->id]) }}">
              @csrf
              @method('PUT')
              <div class="d-flex justify-content-center mb-3">
                  <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : 'Edit ' . ucwords($contact->name) }}
                  </h3>
              </div>

              @includeIf('admin.layouts.partials.errors.validation-failed')

              <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" name="name" id="name" value="{{ $contact->name ?? old('name') }}">
              </div>
              <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email" value="{{ $contact->email ?? old('email') }}">
              </div>
              <div class="form-group">
                <label for="mobile">Mobile</label>
                <input type="text" maxlength="10" class="form-control mobile" name="mobile" id="mobile" value="{{ $contact->mobile ?? old('mobile') }}">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control summernote-editor" id="description" name="description">
                    {{ $contact->description ?? old('description') }}
                </textarea>
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
