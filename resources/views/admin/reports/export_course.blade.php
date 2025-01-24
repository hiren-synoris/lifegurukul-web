<table>
    <thead>
    <tr>
        <th>Image</th>
        <th>Course id</th>
        <th>Title</th>
        <th>Instructor Name</th>
        <th>Active Plan</th>
        <th>Enroll Learner</th>
        <th>Status</th>
        <th>Created On</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $querys)
        @php
            $collection = collect($querys->userCourseCount);
        @endphp
        <tr>
            <td>{{ getImageIfExists($querys->image, course_img_default()); }}</td>
            <td>{{ $querys->id?? '' }}</td>
            <td>{{ $querys->title?? '' }}</td>
            <td>{{ isset($querys->instructor) && !empty($querys->instructor) ? $querys->instructor->name : '' }}</td>
            <td>{{ $querys->course_plan_count }}</td>
            <td>{{
                     $collection->sum('user_course_count'); }}</td>
            <td>{{ isset($querys->status) && !empty($querys->status) && $querys->status == 1 ? 'PUBLISHED' : 'UNPUBLISHED' }}</td>
            <td>{{ $querys->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
