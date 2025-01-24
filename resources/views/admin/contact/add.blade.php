@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'summerNoteCSS' => 1,
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('support-ticket.index')) !!}
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/contact') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    <h3>Add</h3>
                </div>

                @includeIf('admin.layouts.partials.errors.validation-failed')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" name="email" id="email" value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile</label>
                    <input type="text" maxlength="10" class="form-control mobile" name="mobile" id="mobile" value="{{ old('mobile') }}">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control summernote-editor" id="description" name="description">
                        {{ old('description') }}
                    </textarea>
                </div>
                <button type="submit" id="butsave" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
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
