@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('instructors.index')) !!}
@endsection
@section('content')
<?php
$is_instuctor = 0;
$path = 'instructors';

?>
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        @if(isset($user) && !empty($user))
        <form method="POST"  id="form_submit" action="{{ url('backoffice/'.$path.'/'.$user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center mb-3">
                <h3>{{ !isset($pg_header) && !empty($pg_header) ? $pg_header : "Edit ".ucwords($user->name) }}</h3>
            </div>
            @method('PUT')
            <div class="justify-content-center mb-3">
                <h3 class="text-danger">You have notification limit {{ $user->instructure->is_notify_limit > 0 ? $user->instructure->is_notify_limit: '0' }}</h3>
            </div>

            {{-- @includeIf('admin.layouts.partials.axayp.msperrors.validation-failed') --}}

            <div class="form-group">
                <label for="name">Name</label><span style="color: red">*</span>
                <input type="text" class="form-control" name="name" id="name"
                    value="{{ isset($user->name) && !empty($user->name) ? $user->name : '' }}"  >
                @error('name')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror

            </div>
            <div class="form-group">
                <label for="name">Email</label><span style="color: red">*</span>
                <input type="email" class="form-control" name="email"
                    value="{{ isset($user->email) && !empty($user->email) ? $user->email : '' }}" id="email"  >
                    @error('email')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="name">Password (one uppercase letter, one lowercase letter, one digit, and one special character.)</label><span style="color: red">*</span><br>
                <input autocomplete="new-password" type="password" class="form-control" name="password" value="" id="password">
                <small class="text-warning">Leave empty to keep same</small>
                @error('password')
                <div class="text text-danger">{{ $message }}</div>
            @enderror
            </div>
            <input type="hidden" name="old_password" value="{{ isset($user->password) && !empty($user->password) ? $user->password : '' }}">
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>  <small class="text-gray">(jpg, svg, jpeg, png)</small>
                @if(isset($user->profile_picture) && !empty($user->profile_picture) &&
                Storage::exists($user->profile_picture))
                <a href="{{ Storage::url($user->profile_picture) ?? "javascript:void(0)" }}" target="_blank"><img
                        src="{{ Storage::url($user->profile_picture) }}" class="d-block mb-3"
                        style="height: 120px;width: 120px;"></a>
                        <button class="remove-image" type="button" style="margin-bottom: 15px"><i class="fas fa-trash-alt"></i></button>
                @endif
                <label class="block form-control">
                    <span class="sr-only">Choose File</span>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <input type="hidden" name="remove_banner_image" id="remove_banner_image" value="0">

                </label>
                <span id="image_error" class="" style="display: none;color:red"></span>
                @error('profile_picture')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>

            @if(auth()->user()->roles[0]->name == "admin")
            <div class="form-group">
                <label for="is_notify_limit">Monthly manual notification limit</label>
                <input type="text" class="form-control" value="{{@$user->instructure->is_notify_limit }}" name="is_notify_limit"
                    id="is_notify_limit">
                </label>
                @error('is_notify_limit')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            @endif
            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" class="form-control" value="{{ isset($user->instructure->designation) && !empty($user->instructure->designation) ? $user->instructure->designation : '' }}"  name="designation"
                    id="designation">
                </label>
            </div>
            <div class="form-group">
                <label for="bio">Bio</label><br>
                <textarea name='bio' class="form-control" rows="5" id='bio'>{{ isset($user->instructure->bio) && !empty($user->instructure->bio) ? $user->instructure->bio : '' }}</textarea>
            </div>
            <div class="form-group">
                <label for="facebook_follower">Facebook Follower</label>
                <input type="number" oninput="validateNumericInput(this)" class="form-control" value="{{ isset($user->instructure->facebook_follower) && !empty($user->instructure->facebook_follower) ? $user->instructure->facebook_follower : '' }}"
                    name="facebook_follower" id="facebook_follower">
                </label>
            </div>
            <div class="form-group">
                <label for="instagram_follower">Instagram Follower</label>
                <input type="number" class="form-control" oninput="validateNumericInput(this)" value="{{ isset($user->instructure->instagram_follower) && !empty($user->instructure->instagram_follower) ? $user->instructure->instagram_follower : '' }}"
                    name="instagram_follower" id="instagram_follower">
                </label>
            </div>
            <div class="form-group">
                <label for="twitter_follower">Twitter Follower</label>
                <input type="number" class="form-control" oninput="validateNumericInput(this)" value="{{ isset($user->instructure->twitter_follower) && !empty($user->instructure->twitter_follower) ? $user->instructure->twitter_follower : '' }}" name="twitter_follower"
                    id="twitter_follower">
                </label>
            </div>
            <div class="form-group">
                <label for="youtube_follower">Youtube Follower</label>
                <input type="number" class="form-control"  oninput="validateNumericInput(this)" value="{{ isset($user->instructure->youtube_follower) && !empty($user->instructure->youtube_follower) ? $user->instructure->youtube_follower : '' }}" name="youtube_follower"
                    id="youtube_follower">
                </label>
            </div>
            @if(auth()->user()->roles->first()->name=="admin")
            <div class="form-group mt-2">
                <label for="allow_instructor">Home Top Instructor</label>
                <div class="d-flex">
                    <input id="allow_instructor" name="allow_instructor" {{$user->instructure->allow_instructor==1 ? "checked":''  }} value=1 type="checkbox" class="form-control"
                        style="height: 26px;width: 3%;">
                </div>
            </div>
            @endif
                @if (isset($roles) && !empty($roles) && count($roles) > 0)
                    <input type="hidden" name="roles[{{ $roles['id'] }}]" id="{{ $roles['name'] }}">
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
    jQuery(document).ready(function($) {
        $('.remove-image').click(function() {
            var container = $(this).closest('.form-group');
            container.find('img').remove();
            container.find('input[type="file"]').val('');
            $('#remove_banner_image').val('1');
            $(this).remove(); // Remove the delete icon itself
        });
    });

    var flag

        $("#profile_picturse").change(function() {

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
        function validateNumericInput(input) {
            input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
        }
        // $("#form_submit").on('submit',(function(e) {
        // // alert(flag)
        //     return flag
        //     $("#image_error").hide()

        // }))
        // max_upload_size();
        $('#facebook_follower').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });
            $('#instagram_follower').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });
            $('#twitter_follower').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });
            $('#youtube_follower').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });
</script>
@endsection
