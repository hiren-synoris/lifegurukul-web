@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('notifications.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($notification) && !empty($notification->id))
          <form method="POST" action="{{ route('notifications.update', ['notification' => $notification->id]) }}">
              @csrf
              @method('PUT')
              <div class="d-flex justify-content-center mb-3">
                  <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : 'Edit ' . ucwords($notification->name) }}
                  </h3>
              </div>

              @includeIf('admin.layouts.partials.errors.validation-failed')

              <div class="form-group">
                  <label for="title">Notification Title</label>
                  <input type="text" class="form-control" name="title" id="title" value="{{ $notification->notificationTitle ?? old('title') }}">
              </div>
              <div class="form-group">
                  <label for="text">Notification Text</label>
                  <textarea class="form-control" name="text" id="text" rows="5" cols="20" required>{{$notification->notificationText ?? old('text') }}</textarea>
              </div>
              <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
          </form>
        @endif
    </div>
</div>
@endsection

