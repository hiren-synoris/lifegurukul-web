<table>
    <thead>
        <tr>
            <th>Learner ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Country Code</th>
            <th>Mobile</th>
            <th>Device Count</th>
            <th>Created On</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $querys)

        <tr>
            <td>{{ $querys->id ?? '' }}</td>
            <td>{{ $querys->name ?? '' }}</td>
            <td>{{ $querys->email ?? '' }}</td>
            <td>{{ @$querys->country->phonecode ?? '' }}</td>
            <td>{{ $querys->mobile ?? ''}}</td>
            <td>{{ $querys->get_device_count ?? '' }}</td>
            <td>{{ $querys->created_at }}</td>
        </tr>

        @endforeach
    </tbody>
</table>
