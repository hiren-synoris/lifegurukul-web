@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style',[
            'select2CSS' => 1,
            'summerNoteCSS' => 1
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('pages.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($page) && !empty($page))
            <form method="POST" action="{{ url('backoffice/pages/'.$page->id) }}">
                @csrf
                @method('PUT')
                <div class="d-flex justify-content-center mb-3">
                    <h3>Edit Page</h3>
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <ul class="nav nav-tabs" id="myTab" role="tablist">
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
                                    <input type="text" class="form-control" value="{{ isset($page->name) && !empty($page->name) ? $page->name : old('name') }}" name="name" id="name">
                                    @error('name')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">Slug</label><span style="color: red">*</span>
                                    <input type="text" class="form-control" value="{{ isset($page->slug) && !empty($page->slug) ? $page->slug : old('slug') }}" name="slug" id="slug" readonly>
                                    @error('slug')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    <label for="body">Body</label><span style="color: red">*</span>
                                    <textarea class="form-control summernote-editor" id="body" name="body">{{ isset($page->body) && !empty($page->body) ? $page->body : old('body') }}</textarea>
                                    @error('body')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    {{-- <div class="col-3 swich-area">
                                        <label for="status">Status</label><span style="color: red">*</span>
                                        <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" {{ $page->status == 1 ? 'checked' : '' }} data-bootstrap-switch>
                                    </div> --}}
                                    @include('admin.layouts.partials.buttons.toggle-button',[
                                        'dataValue' => $page->status,
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
                                    <input type="text" class="form-control" value="{{ isset($page->meta_title) && !empty($page->meta_title) ? $page->meta_title : old('meta_title') }}" name="meta_title" id="meta_title">
                                </div>
                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <select class="form-control metaKeywords" name="meta_keywords[]" id="meta_keywords" multiple="multiple" style="width:100%;">
                                        @forelse($keywords as $key => $value)
                                            <option value="{{ $value ?? old('meta_keywords') }}" selected>{{ $value }}</option>
                                        @empty

                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea name="meta_description" cols="57" rows="5" id="meta_description" class="form-control">{{ isset($page->meta_description) && !empty($page->meta_description) ? $page->meta_description : old('meta_description') }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1,
        'select2' => 1,
        'summerNote' => 1
    ])
    <script>

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
