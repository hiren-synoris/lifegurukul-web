@extends('admin.layouts.app')
<?php
// $url = $_SERVER['PHP_SELF'];
$url = Request::url();
if (str_contains($url, 'learners')) {
    $path = 'learners';
} else if (str_contains($url, 'subadmin')){
    $path = 'subadmin';
} else {
    $path = 'users';
}
?>
@section('right-section')
{!! redirect_to_back(route($path.'.index')) !!}
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($user) && !empty($user))
        <form method="POST" id="form_submit" action="{{ url('backoffice/'.$path.'/'.$user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit ".ucwords($user->name) }}</h3>
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

            <div class="form-group">
                <label for="name">Name</label><span style="color: red">*</span>
                <input type="text" class="form-control" name="name" id="name"
                    value="{{ isset($user->name) && !empty($user->name) ? preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $user->name) : '' }}" >
                    @error('name')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
            </div>
            <div class="form-group">
                <label for="name">Email</label><span style="color: red">*</span>
                <input type="email" class="form-control" name="email"
                    value="{{ isset($user->email) && !empty($user->email) ? $user->email : '' }}" id="email" >
                    @error('email')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label><small class="text-gray">(jpg, svg, jpeg, png)</small>
                @if(isset($user->profile_picture) && !empty($user->profile_picture) &&
                Storage::exists($user->profile_picture))
                <a href="{{ Storage::url($user->profile_picture) ?? "javascript:void(0)" }}" target="_blank"><img
                        src="{{ Storage::url($user->profile_picture) }}" class="d-block mb-3"
                        style="height: 120px;width: 120px;"></a>
                @endif
                <label class="block form-control">
                    <span class="sr-only">Choose File</span>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                </label>
                <span id="image_error" class="" style="display: none;color:red"></span>
                @error('profile_picture')
                        <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="name">Password (one uppercase letter, one lowercase letter, one digit, and one special character.)</label><span style="color: red">*</span><br>
                <input type="password" class="form-control" name="password" value="" id="password">
                <small class="text-warning">Leave empty to keep same</small>
                @error('password')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <input type="hidden" name="old_password"
                value="{{ isset($user->password) && !empty($user->password) ? $user->password : '' }}">
                @if(!empty($path) && $path != "users")
                    <div class="form-group">
                        <label>Roles:</label><span style="color: red">*</span>
                        <div class="ml-5">
                            @if(isset($roles) && !empty($roles) && count($roles) > 0)
                            @foreach($roles as $key => $value)
                            <div class="icheck-success mr-5" style="margin-left: -46px;">
                                <input type="checkbox" name="roles[{{ $value->id }}]" id="{{ $value->name }}"
                                    @if($assigned_roles->contains($value->name)) {{ "checked" }} @endif>
                                <label for="{{ $value->name }}">{{ucwords($value->name)}} </label><br />
                            </div>
                            @endforeach
                            @endif
                        </div>
                        @error('roles')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
                    </div>
                @endif
                <input type="hidden" name="user_type" value="{{$path}}">
            <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
        </form>
        @endif
    </div>
</div>
@endsection
@section('scripts')
<script>
    // max_upload_size();
    var flag
    $("#profile_pictusre").change(function() {

        var validExtensions = ["jpg", "svg", "jpeg", "png"]
        var file = $(this).val().split('.').pop();
        if (validExtensions.indexOf(file) == -1) {
            $("#image_error").show()
            $("#image_error").html("Only formats are allowed : " + validExtensions.join(', '));
            flag = false
        } else{
            flag = true
            $("#image_error").hide()
        }

        });


        // $("#form_submit").on('submit',(function(e) {
        //        // alert(flag)
        //         return flag
        //         $("#image_error").hide()
        //         max_upload_size();
        //     }))
        // max_upload_size();
</script>
@endsection
