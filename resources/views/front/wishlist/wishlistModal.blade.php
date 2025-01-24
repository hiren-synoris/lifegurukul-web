<div style="display: none;">
    <form  id="wishlist_form" method="POST">
        @csrf
        <input type="user_id"
            value="{{ Auth::guard('learner')->check() ? Auth::guard('learner')->id() : null }}"
            name="user_id" id="user_id">
        <input type="course_id" value="" name="course_id" id="course_id_frm">
        <input type="text" value="" name="wishlist_active" id="wishlist_active_frm">
        <input type="text" value="" name="wishlist_id" id="wishlist_id_frm">
        <input type="submit" id="wishlist_btn">
    </form>
</div>
