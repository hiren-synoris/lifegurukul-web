@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/codemirror/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/codemirror/theme/monokai.css') }}">
@endsection
@section('right-section')
    {!! redirect_to_back(route('email_templates.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/email_templates/'.$emailtemplate->id) }}">
                @csrf
                @method('PUT')
                <div class="d-flex justify-content-center mb-3">
                    <h3>Edit template</h3>
                </div>
                @includeIf('admin.layouts.partials.errors.validation-failed')
                <div class="form-group">
                    <label for="slug">Slug</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ old('slug') && !empty(old('slug')) ? old('slug') : $emailtemplate->slug }}" name="slug" id="slug">
                </div>
                <div class="form-group">
                    <textarea class="summernote-editor" id="summernote" name="template">{{ old('template') && !empty(old('template')) ? old('template') : $emailtemplate->body }}</textarea>
                </div>
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'summerNote' => 1,
        'codeMirror' => 1
    ])

    <script>
        $(function() {
            // Summernote
            // $('#summernote').summernote({
            //     toolbar: [
            //         ['style', ['style']],
            //         ['font', ['bold', 'underline', 'clear']],
            //         ['fontname', ['fontname']],
            //         ['color', ['color']],
            //         ['para', ['ul', 'ol', 'paragraph']],
            //         ['table', ['table']],
            //         ['insert', ['link', 'picture', 'video']],
            //         ['view', ['fullscreen','help']],
            //     ],
            //     minHeight: 250,
            //     codemirror: {
            //         theme: 'monokai'
            //     }
            // });
        });
        CodeMirror.fromTextArea(document.getElementById("codeMirrorDemo"), {
            mode: "htmlmixed",
            theme: "monokai"
        });
        </script>
@endsection
