@extends('admin.layouts.app')
<?php
$path = 'instructors';
?>
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" id="form_submit" action="{{ url('backoffice/' . $path) }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3 class="m-0">@if (isset($pg_header)) {{ ucwords($pg_header) }} @endif</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="name">Name</label><span style="color: red">*</span>
                    <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name">
                    @error('name')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="name">Email</label><span style="color: red">*</span>
                    <input type="email" class="form-control" value="{{ old('email') }}" name="email" id="email">
                    @error('email')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password (one uppercase letter, one lowercase letter, one digit, and one special character.)</label><span style="color: red">*</span>
                    <input type="password" class="form-control" value="{{ old('password') }}" name="password" id="password"
                        autocomplete="new-password">
                    @error('password')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label><span style="color: red">*</span>
                    <input type="password" class="form-control" value="{{ old('password_confirmation') }}"
                        name="password_confirmation" id="password_confirmation">
                    @error('password_confirmation')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label> <small class="text-gray">(jpg, svg, jpeg,
                        png)</small>
                    <label class="block form-control">
                        <span class="sr-only">Choose File</span>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            max-length="0.005" />
                    </label>
                    <span id="image_error" class="" style="display: none;color:red"></span>
                    @error('profile_picture')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                {{-- <div class="form-group">
                    <label for="is_notify_limit">Monthly manual notification limit</label>
                    <input type="text" class="form-control" value="{{ old('is_notify_limit') }}" name="is_notify_limit"
                        id="is_notify_limit">
                    </label>
                </div> --}}
                <div class="form-group">
                    <label for="designation">Designation</label>
                    <input type="text" class="form-control" value="{{ old('designation') }}" name="designation"
                        id="designation">
                    </label>

                    {{-- @error('profile_picture')
                <div class="text text-danger">{{ $message }}</div>
            @enderror --}}
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label><br>
                    <textarea name='bio' class="form-control" rows="5" id='bio'>{{ old('bio') }}</textarea>
                    @error('bio')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="facebook_follower">Facebook Follower</label>
                    <input type="number" class="form-control" value="{{ old('facebook_follower') }}"
                        name="facebook_follower" id="facebook_follower">
                    </label>
                    @error('facebook_follower')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="instagram_follower">Instagram Follower</label>
                    <input type="number" class="form-control" value="{{ old('instagram_follower') }}"
                        name="instagram_follower" id="instagram_follower">
                    </label>

                </div>
                <div class="form-group">
                    <label for="twitter_follower">Twitter Follower</label>
                    <input type="number" class="form-control" value="{{ old('twitter_follower') }}"
                        name="twitter_follower" id="twitter_follower">
                    </label>
                </div>
                <div class="form-group">
                    <label for="youtube_follower">Youtube Follower</label>
                    <input type="number" class="form-control" value="{{ old('youtube_follower') }}"
                        name="youtube_follower" id="youtube_follower">
                    </label>
                </div>
                @if(auth()->user()->roles->first()->name=="admin")
                <div class="form-group mt-2">
                    <label for="allow_instructor">Home Free Instructor</label>
                    <div class="d-flex">
                        <input id="allow_instructor" value=1 name="allow_instructor" type="checkbox" class="form-control"
                            style="height: 26px;width: 3%;">
                    </div>
                </div>
                @endif
                @if (isset($roles) && !empty($roles) && count($roles) > 0)
                    <input type="hidden" name="roles[{{ $roles['id'] }}]" id="{{ $roles['name'] }}">
                @endif
                <input type="hidden" name="user_type" value="{{ $path }}">
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>

            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        // var value;
        // function validationForSocial(value){
        //     var val = value.replace(/[^0-9\.]/g, '');
        //     return
        //  }

        $(document).ready(function() {

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


            var flag

            $("#profile_picturse").change(function() {

                var validExtensions = ["jpg", "svg", "jpeg", "png"]
                var file = $(this).val().split('.').pop();
                if (validExtensions.indexOf(file) == -1) {
                    $("#image_error").show()
                    $("#image_error").html("Only formats are allowed : " + validExtensions.join(', '));
                    flag = false
                } else {
                    flag = true
                    $("#image_error").hide()
                }

            });

            // $("#form_submit").on('submit',(function(e) {
            // // alert(flag)
            //     return flag
            //     $("#image_error").hide()
            //     max_upload_size();
            // }))


            $(document).on("click", "#permission_select_all", function(e) {
                if ($(this).is(':checked')) {
                    $(".permission_box").prop('checked', true);
                    $(".permission_box_header").prop('checked', true);
                } else {
                    $(".permission_box").prop('checked', false);
                    $(".permission_box_header").prop('checked', false);
                }
            })
        });

        function select_role_via_per(role) {
            if ($("#" + role).is(':checked')) {
                $("." + role).prop('checked', true);
            } else {
                $("." + role).prop('checked', false);
            }
        }
        // max_upload_siz   e();
    </script>
@endsection
