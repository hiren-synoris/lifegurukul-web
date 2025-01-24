@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('dropdowns.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/dropdowns') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3>Add Dropdown</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name"
                        onBlur="nameConvertSlug()">
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
            {{-- <div class="col-3 swich-area">
                        <label for="status">Status</label><span style="color: red">*</span>
                        <input type="checkbox" name="status" id="status" data-on-text="Active" data-off-text="In Active" data-off-color="danger" data-on-color="success" checked data-bootstrap-switch>
                    </div> --}}
            @include('admin.layouts.partials.buttons.toggle-button', [
                'dataValue' => 1,
                'id' => 'status',
                'name' => 'status',
                'toggleBtnText' => 'Status',
            ])
        </div>
        <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
        </form>
    </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'switch' => 1,
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
    </script>
@endsection
