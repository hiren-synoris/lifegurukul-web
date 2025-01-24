@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(url('backoffice/dropdown_options/'.$dropdown->id)) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($dropdown) && !empty($dropdown))
            <form method="POST" action="{{ url('backoffice/dropdown_options/'.$dropdown->id) }}" enctype='multipart/form-data'>
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3>Add Dropdown Option</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="dropdown_id">Dropdown Name</label><span style="color: red">*</span>
                    <input type="hidden" name="dropdown_id" id="dropdown_id" value="{{ $dropdown->id }}">
                    <input type="text" class="form-control" value="{{ $dropdown->name ?? old('name') }}" name="dropdown_name" id="dropdown_name" readonly>
                    @error('dropdown_name')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name" onBlur="DropConvertor()">
                    @error('name')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ old('slug') }}" name="slug" id="slug" readonly >
                    @error('slug')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    @if(isset($dropdown) && !empty($dropdown->slug))
                        @if($dropdown->slug == 'course_category')
                            <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                            <input type="file" class="form-control" name="image1" id="image" accept="image/*" >
                        @elseif ($dropdown->slug == 'trusted_by')
                            <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                            <input type="file" class="form-control" name="image2" id="image" accept="image/*" >
                        @else
                        <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                        <input type="file" class="form-control" name="image3" id="image" accept="image/*" >
                        @endif
                    @endif

                    @error('image1')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                    @error('image2')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                    @error('image3')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                {{-- <div class="form-group">
                    <label for="status">Status</label><span style="color: red">*</span>
                    <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" checked data-bootstrap-switch>
                </div>
                <div class="form-group">
                    <label for="home">Home</label><span style="color: red">*</span>
                    <input type="checkbox" name="home" id="home" data-on-text="Yes" data-off-text="No" data-off-color="danger" data-on-color="success" data-bootstrap-switch>
                </div> --}}
                <div class="form-group">
                    @include('admin.layouts.partials.buttons.toggle-button',[
                        'dataValue' => 1,
                        'id' => 'status',
                        'name' => 'status',
                        'toggleBtnText' => 'Status'
                    ])
                </div>
                @if (isset($dropdown) && !empty($dropdown->slug) && $dropdown->slug == 'course_category')
                    <div class="form-group">
                        @include('admin.layouts.partials.buttons.toggle-button',[
                            'id' => 'home',
                            'name' => 'home',
                            'toggleBtnText' => 'Home'
                        ])
                    </div>
                @endif
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        @endif
    </div>
</div>
{{-- </div> --}}
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1
    ])
@endsection

<script>

        function DropConvertor() {

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

    </script>
