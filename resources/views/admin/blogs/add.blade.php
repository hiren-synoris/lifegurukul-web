@extends('admin.layouts.app')
@section('styles')
    @includeIf('admin.layouts.partials.styles.style', [
        'switchCSS' => 1,
        'select2CSS' => 1,
        'summerNoteCSS' => 1,
    ])
@endsection
@section('right-section')
    {!! redirect_to_back(route('blogs.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/blogs') }}" enctype='multipart/form-data'>
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3>Add Blog</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="create-blog-tab" data-toggle="tab" href="#create-blog" role="tab" aria-controls="create-blog" aria-selected="true">General Information</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="seo" aria-selected="false">SEO</a>
                    </li>
                </ul>
                <div class="row justify-content-center mt-3">
                    <div class="col-12">
                        <div class="tab-content" id="myTabContent">
                            <!-- General Tab -->
                            <div class="tab-pane fade show active" id="create-blog" role="tabpanel" aria-labelledby="create-blog-tab">
                                <div class="form-group">
                                    <label for="title">Name <span style='color:red;'>*</span></label>
                                    <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}"
                                        onBlur="nameConvertSlug()">
                                        @error('title')
                                        <div class="text text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="slug">Slug</label><span style="color: red">*</span>
                                    <input type="text" class="form-control" value="{{ old('slug') }}" name="slug" id="slug"
                                        readonly>
                                        @error('slug')
                                        <div class="text text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="">Blog Category</label><span style="color: red">*</span> {!! add_new_category_button('blog_category') !!}
                                    <select name="category_id" class="form-control select3" id="category_id">
                                        <option value="" selected>Select category</option>
                                        @if(isset($blogCategories) && !empty($blogCategories) && count($blogCategories) > 0)
                                        @foreach ($blogCategories as $key => $dropdownOption)
                                            <option value="{{ $dropdownOption->id }}" {{ old('category_id') == $dropdownOption->id ? 'selected' : '' }}>{{ $dropdownOption->name }}</option>.
                                        @endforeach
                                        @endif
                                    </select>
                                    @error('category_id')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>

                                <div class="form-group">
                                    <label for="cover">Cover Image <span style='color:red;'>*</span> <small class="text-gray"> Recommended Size ( 1280px * 855px )</small></label>
                                    <input type="file" class="form-control" name="cover" id="cover" accept="image/*">
                                    @error('cover')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                {{-- <div class="form-group">
                                    <label for="tags">Tags <span style='color:red;'>*</span></label>
                                    <small>Use comma separated values</small>
                                    <select class="form-control tags" name="tags[]" id="tags" multiple="multiple" style="width:100%;">
                                    </select>
                                </div> --}}
                                <div class="form-group">
                                    <label for="content">Content <span style='color:red;'>*</span></label><br>
                                    <textarea class="summernote-editor" name='content' id='content'>{{ old('content') }}</textarea>
                                    @error('content')
                                    <div class="text text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <div class="form-group">
                                    @include('admin.layouts.partials.buttons.toggle-button',[
                                        'dataValue' => 1,
                                        'id' => 'status',
                                        'name' => 'status',
                                        'toggleBtnText' => 'Status',
                                    ])
                                </div>
                                <div class="form-group">
                                    @include('admin.layouts.partials.buttons.toggle-button',[
                                        'id' => 'home',
                                        'name' => 'home',
                                        'toggleBtnText' => 'Home',
                                    ])
                                </div>

                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="form-group">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" class="form-control" value="{{ old('meta_title') }}" name="meta_title" id="meta_title">
                                </div>
                                <div class="form-group">
                                    <label for="meta_keywords">Meta Keywords</label>
                                    <select class="form-control metaKeywords" name="meta_keywords[]" id="meta_keywords" multiple="multiple" style="width:100%;">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea name="meta_description" cols="57" rows="5" id="meta_description" class="form-control">{{ old('meta_description') }}</textarea>
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
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'switch' => 1,
        'select2' => 1,
        'summerNote' => 1,
    ])

    <script>
        $(document).ready(function(){
            $('.select3').select2();
        })

        function nameConvertSlug() {
            var name = document.getElementById("title");
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

        $(document).ready(function() {
            // $('.tags').select2({
            //     theme: "classic",
            //     selectOnClose: false,
            //     allowClear: true,
            //     minimumResultsForSearch: -1,
            //     tags: true,
            //     tokenSeparators: [',', ' ']
            // });
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
