@extends('admin.layouts.app')
@section('right-section')
{!! redirect_to_back(url('backoffice/dropdown_options/'.$dropdownOption->dropdown->id)) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($dropdownOption) && !empty($dropdownOption))
        <form method="POST" action="{{ url('backoffice/dropdown_options/'.$dropdownOption->id) }}" enctype='multipart/form-data'>
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>Edit Dropdown Option</h3>
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

            @if(isset($dropdownOption->dropdown) && !empty($dropdownOption->dropdown))
            <div class="form-group">
                <label for="dropdown_id">Dropdown Name</label><span style="color: red">*</span>
                <input type="hidden" name="dropdown_id" id="dropdown_id" value="{{ $dropdownOption->dropdown->id ?? '' }}">
                <input type="text" class="form-control" value="{{ $dropdownOption->dropdown->name ?? old('name') }}" name="dropdown_name" id="dropdown_name" readonly>
                @error('dropdown_id')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            @endif
            <div class="form-group">
                <label for="name">Name</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ $dropdownOption->name ?? old('name') }}" name="name" id="name">
                @error('name')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ $dropdownOption->slug ?? old('slug') }}" name="slug" id="slug" readonly>
                @error('slug')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                @if(isset($dropdownOption->dropdown) && !empty($dropdownOption->dropdown) && !empty($dropdownOption->dropdown->slug))
                {{-- @if($dropdownOption->dropdown->slug == 'course_category')
                                <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                            @elseif ($dropdownOption->dropdown->slug == 'trusted_by')
                                <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                            @endif --}}
                            @if (isset($dropdownOption->image) && !empty($dropdownOption->image) )
                {{-- && Storage::exists($dropdownOption->image) --}}
                <a href="{{ str_contains(asset(Storage::url($dropdownOption->image)), 'front') ? asset($dropdownOption->image) : getImageIfExists($dropdownOption->image, course_img_default()) }}" target="_blank" class="mb-2"><img src="{{ str_contains(asset(Storage::url($dropdownOption->image)), 'front') ? asset($dropdownOption->image) : getImageIfExists($dropdownOption->image, course_img_default()) }}" class="d-block mb-3" style="height: 120px;width: 120px;"></a>
                @endif
                @if($dropdownOption->dropdown->slug == 'course_category')
                <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                <input type="file" class="form-control" name="image1" id="image" accept="image/*">
                @elseif ($dropdownOption->dropdown->slug == 'trusted_by')
                <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                <input type="file" class="form-control" name="image2" id="image" accept="image/*">
                @else
                <small class="text-gray">(Please upload jpg and png files no larger than 2 MB)</small>
                <input type="file" class="form-control" name="image3" id="image" accept="image/*">
                @endif
                @endif
                
                {{-- <input type="file" class="form-control" name="image" id="image" accept="image/*"> --}}
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
                        <input type="checkbox" name="status" id="status"  data-on-text="Active" data-off-text="In Active"
                            data-off-color="danger" data-on-color="success" {{ $dropdownOption->status == 1 ? 'checked' : '' }} data-bootstrap-switch>
    </div>
    <div class="form-group">
        <label for="home">Home</label><span style="color: red">*</span>
        <input type="checkbox" name="home" id="home" data-on-text="Yes" data-off-text="No" data-off-color="danger" data-on-color="success" {{ $dropdownOption->home == 1 ? 'checked' : '' }} data-bootstrap-switch>
    </div> --}}
    <div class="form-group">
        @include('admin.layouts.partials.buttons.toggle-button',[
        'dataValue' => $dropdownOption->status,
        'id' => 'status',
        'name' => 'status',
        'toggleBtnText' => 'Status'
        ])
    </div>
    @if (isset($dropdownOption->dropdown) && !empty($dropdownOption->dropdown) && !empty($dropdownOption->dropdown->slug) && $dropdownOption->dropdown->slug == 'course_category')
    <div class="form-group">
        @include('admin.layouts.partials.buttons.toggle-button',[
        'dataValue' => $dropdownOption->home,
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
@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list',[
'switch' => 1
])
@endsection
