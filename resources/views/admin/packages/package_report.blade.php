<table>
    <thead>
        <tr>
            <th>Package Id</th>
            <th>Plan Id</th>
            <th>Package Name</th>
            <th>Country code</th>
            <th>Mobile</th>
            <th>Name</th>
            <th>Email </th>
            <th>Enroll Date</th>
            <th>Valid Till</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $querys)
            <tr>
                <td>{{ $querys->course_id }}</td>
                <td>{{ $querys->plan_id }}</td>
                <td>{{ $querys->course->title?? '' }}</td>
                <td>{{ $querys->countryName->phonecode ?? '' }}</td>
                <td>{{ $querys->learner->mobile ?? '' }}</td>
                <td>{{ $querys->learner->name ?? '' }}</td>
                <td>{{ $querys->learner->email ?? '' }}</td>
                <td>{{ $querys->created_at }}</td>
                <td>{{ isset($querys->expire_at) ? $querys->expire_at : 'LifeTime' }}</td>

            </tr>
        @endforeach
    </tbody>
</table>
