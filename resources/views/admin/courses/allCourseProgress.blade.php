@php
use App\Models\UserCourseProgress;
@endphp
<table>
    <thead>
        <tr>
            <th>Mobile</th>
            <th>Name</th>
            <th>Email</th>
            <th>Expried Date</th>
            <th>Enroll Date</th>
            <th>Course Progress</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $querys)

        {{-- @dd($querys) --}}
            @php
            $status = '';

            if ($querys->order_status == 1) {
                $status = 'Completed';
            } elseif ($querys->order_status == 2) {
                $status = 'Failure';
            } elseif ($querys->order_status == 3) {
                $status = 'Free';
            } elseif ($querys->order_status == 4) {
                $status = 'Enrol by Admin';
            }

            $querys->userChpater->filter(function ($fl) use ($querys) {
                return $querys->new_chapterIds = $fl->chapterIds;
            });
            $user_progress = UserCourseProgress::whereIn('chapter_id', explode(',', $querys->new_chapterIds))
                ->where('learner_id', $querys->learner_id)
                ->where('is_completed', 1)
                ->count();

            $percentage =
                round($user_progress) != 0
                    ? round(($user_progress * 100) / count(explode(',', $querys->new_chapterIds))) . '%'
                    : '0%';



@endphp
            <tr>
                <td>{{ @$querys->learner->mobile ?? '' }}</td>
                <td>{{ @$querys->learner->name ?? '' }}</td>
                <td>{{ @$querys->learner->email ?? '' }}</td>
                <td>{{ $querys->expried_at }}
                </td>
                <td>{{ $percentage }}</td>
                <td>{{ $status }}</td>

            </tr>
        @endforeach
    </tbody>
</table>
