@php
    use App\Models\DropdownOption;
@endphp
<table>
    <thead>
        <tr>
            <th>Learner ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Gender</th>
            <th>Country Code</th>
            <th>Mobile</th>
            <th>Date of Birth</th>
            <th>Occupation</th>
            <th>Marital Status</th>
            <th>Education</th>
            <th>Your Interests</th>
            <th>Device Count</th>
            <th>Last Login</th>
            <th>Created On</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $querys)
            @php
                $your_interests = DropdownOption::whereIn('id', @explode(',', $querys->your_interests))
                    ->pluck('name')
                    ->toArray();
            @endphp
            <tr>
                <td>{{ $querys->id ?? '' }}</td>
                <td>{{ $querys->name ?? '' }}</td>
                <td>{{ $querys->email ?? '' }}</td>
                <td>
                    @if ($querys->gender == 1)
                        {{ 'Male' }}
                    @elseif($querys->gender == 2)
                        {{ 'Female' }}
                    @elseif($querys->gender == 3)
                        {{ 'Other' }}
                    @endif
                </td>
                <td>{{ @$querys->country->phonecode ?? '' }}</td>
                <td>{{ $querys->mobile ?? '' }}</td>
                <td>{{ isset($querys->d_o_b) ? $querys->d_o_b: '' }}</td>
                <td>{{ @$querys->getOccupation->name ?? '' }}</td>
                <td>{{ @$querys->maritalStatus->name ?? '' }}</td>
                <td>{{ @$querys->getEducation->name ?? '' }}</td>
                <td>{{ @implode(',', $your_interests) }}</td>
                <td>{{ $querys->get_device_count ?? '' }}</td>
                <td>{{ !empty($querys->userLastLogin) && !empty($querys->userLastLogin->updated_at) ? date('d/m/Y H:i:s', strtotime($querys->userLastLogin->updated_at)) : '' }}
                </td>
                <td>{{ $querys->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
