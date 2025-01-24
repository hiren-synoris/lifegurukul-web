<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Course Name</th>
            <th>Chapter name</th>
            <th>Device name</th>
            <th>Description</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $querys)
            <tr>
                <td>{{ @$querys->getLerner->name ?? '' }}</td>
                <td>{{ @$querys->getCourse->title ?? '' }}</td>
                <td>{{ @$querys->getChapter->title ?? '' }}</td>
                <td>{{ $querys->device_name == 1 ? 'Android' : ($querys->device_name == 2 ? 'IOS' : $querys->device_name . ' (WEB)') }}
                </td>
                <td>{{ $querys->description }}</td>
                <td>{{ $querys->created_at }}</td>

            </tr>
        @endforeach
    </tbody>
</table>
