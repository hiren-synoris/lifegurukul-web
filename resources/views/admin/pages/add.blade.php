@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style',[
            'select2CSS' => 1,
            'summerNoteCSS' => 1,
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('pages.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/pages') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3>Add Page</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <ul class="nav nav-tabs mx-auto p-0" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="create-page-tab" data-toggle="tab" href="#create-page" role="tab" aria-controls="create-page" aria-selected="true">General Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="seo" aria-selected="false">SEO</a>
                    </li>
                </ul>
                <div class="row justify-content-center mt-3">
                    <div class="col-12">
                        <div class="tab-content" id="myTabContent">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="create-page" role="tabpanel" aria-labelledby="create-page-tab">
                                <div class="form-group">
                                    <label for="name">Title</label><span style="color: red">*</span>
                                    <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name" onBlur="nameConvertSlug()">
                                    @error('name')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">Slug</label><span style="color: red">*</span>
                                    <input type="text" class="form-control" value="{{ old('slug') }}" name="slug" id="slug" readonly>
                                    @error('slug')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="body">Body</label><span style="color: red">*</span>
                                    <textarea class="form-control summernote-editor" id="body" name="body">{{ old('body') }}</textarea>
                                    @error('body')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    {{-- <div class="col-3 swich-area">
                                        <label for="status">Status</label>
                                        <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" checked data-bootstrap-switch>
                                    </div> --}}
                                    @include('admin.layouts.partials.buttons.toggle-button',[
                                        'dataValue' => 1,
                                        'id' => 'status',
                                        'name' => 'status',
                                        'toggleBtnText' => 'Status',
                                    ])
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="form-group">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" class="form-control" value="{{ old('meta_title') }}" name="meta_title" id="meta_title">
                                    @error('meta_title')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <select class="form-control metaKeywords" name="meta_keywords[]" id="meta_keywords" multiple="multiple" style="width:100%;">
                                    </select>
                                    @error('meta_keywords')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea name="meta_description" cols="57" rows="5" id="meta_description" class="form-control">{{ old('meta_description') }}</textarea>
                                    @error('meta_description')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1,
        'select2' => 1,
        'summerNote' => 1,
    ])
    <script>

        function nameConvertSlug() {
            var name = document.getElementById("name");
            var nameValue = name.value;
            var Slug = convertToSlug(nameValue);
            var input = $("#slug");
            input.val("");
            input.val(input.val() + Slug);
        }

        function convertToSlug(Text) {
            return Text.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        }

        $(document).ready(function(){
            $('.metaKeywords').select2({
                theme: "classic",
                selectOnClose: false,
                allowClear: true,
                minimumResultsForSearch: -1,
                tags: true,
                tokenSeparators: [',', ' ']
            });
        });

    </script>
@endsection
