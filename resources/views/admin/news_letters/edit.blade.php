@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('news_letters.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($news_letter) && !empty($news_letter->id))
          <form method="POST" action="{{ route('news_letters.update', ['news_letter' => $news_letter->id]) }}">
              @csrf
              @method('PUT')
              <div class="d-flex justify-content-center mb-3">
                  <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : 'Edit ' . ucwords($news_letter->name) }}
                  </h3>
              </div>

              {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

              <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" name="name" id="name" value="{{ $news_letter->name ?? old('name') }}">
                  @error('name')
                  <div class="text text-danger">{{ $message }}</div>
              @enderror
              </div>
              <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="name" value="{{ $news_letter->email ?? old('email') }}">
                  @error('email')
                  <div class="text text-danger">{{ $message }}</div>
              @enderror
              </div>
              <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
          </form>
        @endif
    </div>
</div>
@endsection

