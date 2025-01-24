@extends('admin.layouts.app')
@section('content')
<div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/send_notifications') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    <h3>Add</h3>
                </div>

                @includeIf('admin.layouts.partials.errors.validation-failed')

                <div class="form-group">
                    <label for="title">Notification Title</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="text">Notification Text</label>
                    <textarea class="form-control" name="text" id="text" required rows="5" cols="20"></textarea>
                </div>
                <button type="submit" id="butsave" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection