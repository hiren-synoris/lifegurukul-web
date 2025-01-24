@extends('admin.layouts.app')
<?php
// $url = $_SERVER['PHP_SELF'];
$url = Request::url();
if (str_contains($url, 'learners')) {
    $path = 'learners';
} elseif (str_contains($url, 'subadmin')) {
    $path = 'subadmin';
} else {
    $path = 'users';
}
?>
@section('right-section')
    {!! redirect_to_back(route($path . '.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/' . $path) }}" id="form_submit" enctype="multipart/form-data">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    <h3 class="m-0">
                        {{-- @if (isset($pg_header))
                            {{ ucwords($pg_header) }}
                        @endif --}}
                    </h3>
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
                    <label for="profile_picture">Profile Picture</label><small class="text-gray"> (jpeg,png,jpg,svg)
                        </small>
                    <label class="block form-control">
                        <span class="sr-only">Choose File</span>
                        <input type="file" name="profile_picture" id="profile_picture"
                            accept="image/jpeg, image/png,image/jpg,image/svg"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            max-length="0.005" /><br>
                        </label>
                        <span id="image_error" class="" style="display: none;color:red"></span>
                        @error('profile_picture')
                            <div class="text text-danger">{{ $message }}</div>
                        @enderror
                </div>
                <div class="form-group">
                    <label for="name">Password</label><span style="color: red">*</span></br>
                    <small class="text-gray"> (The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character.)
                        </small>
                    <input type="password" class="form-control" value="{{ old('password') }}" name="password"
                        id="password">
                    @error('password')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="name">Confirm Password</label><span style="color: red">*</span>
                    <input type="password" class="form-control" value="{{ old('password_confirmation') }}"
                        name="password_confirmation" id="password">
                        @error('password_confirmation')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                @if (!empty($path) && $path != 'users')
                    <div class="form-group">
                        <label>Roles:</label><span style="color: red">*</span>
                        <div class="ml-0">
                            @if (isset($roles) && !empty($roles) && count($roles) > 0)
                                @foreach ($roles as $key => $value)
                                    <div class="icheck-success">
                                        <input type="checkbox" name="roles[{{ $value->id }}]" id="{{ $value->name }}">
                                        <label for="{{ $value->name }}">{{ ucwords($value->name) }} </label><br />
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @error('roles')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror
                    </div>
                @endif
                <input type="hidden" name="user_type" value="{{ $path }}">
                <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>


        $('#name').keypress(function (e) {
            var name  = $(this).val()
            $(this).val(trim(name))
        });
        $(document).ready(function() {



            var flag

            $("#profile_pictusres").change(function() {

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
            //    // alert(flag)
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
        // max_upload_size();

    </script>

@endsection
