<div class="student-group">
    <div class="course-group ">
        <div class="course-group-img d-flex">
            @php
                $imagepath = Helper::profileImage();
               // $imagepath = ($image)? Storage::url($image) :"/front/img/user/user11.jpg";
            @endphp
            <a href="{{url('profile')}}"><img src="{{ $imagepath }}" alt=""
                    class="imagePreview img-fluid"></a>
            <div class="d-flex align-items-center">
                <div class="course-name">
                    <h4><a href="{{url('profile')}}">{{ (Auth::guard('learner')->user()->name)??' User ' }}
                            </a><span>Beginner</span></h4>
                    <p>Learner</p>
                </div>
            </div>
        </div>
        <div class="course-share ">
            <a href="{{ url('profile') }}" class="btn btn-primary">Account Settings</a>
        </div>
    </div>
</div>

<div class="my-student-list">
    <ul>
        <li><a class="{{ (request()->is('dashboard')) ? 'active' : '' }}" href="{{ url('dashboard') }}">Dashboard</a></li>
        {{-- <li><a class="{{ (request()->is('my_course')) ? 'active' : '' }}" href="{{ url('my_course') }}">Courses</a></li>
        <li><a class="{{ (request()->is('student_wishlist')) ? 'active' : '' }}" href="{{ url('student_wishlist') }}">Wishlists</a></li> --}}
        <li class="mb-0"><a class="{{ (request()->is('student_purchase_history')) ? 'active' : '' }}" href="{{ url('student_purchase_history') }}">Purchase history</a></li>
    </ul>
</div>
