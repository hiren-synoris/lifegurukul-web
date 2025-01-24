@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('dropdowns.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        <form method="POST" action="{{ url('backoffice/dropdowns/'.$dropdown->id) }}">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>Edit Dropdown</h3>
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

            @if(isset($dropdown) && !empty($dropdown))
                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ $dropdown->name ?? old('name') }}" name="name" id="name">
                    @error('name')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ $dropdown->slug ?? old('slug') }}" name="slug" id="slug" readonly>
                    @error('slug')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                </div>
                <div class="form-group">
                    {{-- <div class="col-3 swich-area">
                        <label for="status">Status</label><span style="color: red">*</span>
                        <input type="checkbox" name="status" id="status"  data-on-text="Active" data-off-text="In Active"
                            data-off-color="danger" data-on-color="success" {{ $dropdown->status == 1 ? 'checked' : '' }} data-bootstrap-switch>
                    </div> --}}
                    @include('admin.layouts.partials.buttons.toggle-button',[
                        'dataValue' => $dropdown->status,
                        'id' => 'status',
                        'name' => 'status',
                        'toggleBtnText' => 'Status'
                    ])
                </div>
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            @endif
        </form>
    </div>
</div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'switch' => 1
    ])
@endsection
