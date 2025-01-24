@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-8">
                <div class="form-group">
                    <textarea class="summernote-editor" id="summernote" name="template" readonly>{{ $emailtemplate->body }}</textarea>
                </div>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'summerNote' => 1,
    ])

    <script>
        $(function() {
            $('.summernote-editor').summernote({
                toolbar: [],
            });
            $('.summernote-editor').summernote("disable");
        });
        </script>
@endsection
