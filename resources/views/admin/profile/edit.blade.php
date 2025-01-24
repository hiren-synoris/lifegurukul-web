@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('dashboard')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($user) && !empty($user))
            <form method="POST" action="{{ route('profile.update',['profile' => $user->id]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="d-flex justify-content-center mb-3">
                    <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit Profile" }}</h3>
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control" name="name" id="name" value="{{ isset($user->name) && !empty($user->name) ? $user->name : '' }}">
                    @error('name')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="name">Email</label><span style="color: red">*</span>
                    <input type="email" class="form-control" name="email" value="{{ isset($user->email) && !empty($user->email) ? $user->email : '' }}" id="email" >
                    @error('email')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label> <small class="text-gray"> Maximum File Size ( 500KB )</small>
                    @if(isset($user->profile_picture) && !empty($user->profile_picture) && Storage::exists($user->profile_picture))
                        <a href="{{ Storage::url($user->profile_picture) ?? "javascript:void(0)" }}" target="_blank"><img src="{{ Storage::url($user->profile_picture) }}" class="d-block mb-3" style="height: 120px;width: 120px;"></a>
                    @endif
                    <label class="block form-control">
                        <span class="sr-only">Choose File</span>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    </label>
                    @error('profile_picture')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="name">Password</label><span style="color: red">*</span><br>
                    <input type="password" class="form-control" name="password" value="" id="password">
                    <small class="text-warning">Leave empty to keep same</small>
                    @error('password')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <input type="hidden" name="old_password" value="{{ isset($user->password) && !empty($user->password) ? $user->password : '' }}">
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        @endif
    </div>
</div>
@endsection
