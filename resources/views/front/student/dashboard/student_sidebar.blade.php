<div class="my-student-list">
    <ul>
        <li><a class="{{ (request()->is('dashboard')) ? 'active' : '' }}" href="{{ url('dashboard') }}">Dashboard</a></li>
        <li><a class="{{ (request()->is('my_course')) ? 'active' : '' }}" href="{{ url('my_course') }}">Courses</a></li>
        <li><a class="{{ (request()->is('student_wishlist')) ? 'active' : '' }}" href="{{ url('student_wishlist') }}">Wishlists</a></li>
        <li class="mb-0"><a class="{{ (request()->is('student_purchase_history')) ? 'active' : '' }}" href="{{ url('student_purchase_history') }}">Purchase history</a></li>
    </ul>
</div>
