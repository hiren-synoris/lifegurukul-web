@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'summerNoteCSS' => 1,
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('public-forum.index')) !!}
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/public-forum') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    <h3>Add</h3>
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="name">Subject</label>
                    <input type="text" class="form-control" name="subject" id="subject" value="{{ old('subject') }}">
                    @error('subject')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control summernote-editor" id="description" name="description">
                        {{ old('description') }}
                    </textarea>
                    @error('description')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <button type="submit" id="Supsave" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-10 bg-design">
            <h3>List of Public Forums</h3>
            <ul>
                @foreach($getPublic_forum as $forum)
                    <!-- <li>{{ $forum->created_by_name }}</li> -->
                    <li>{{ $forum->description }}</li>
                @endforeach
            </ul>
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
