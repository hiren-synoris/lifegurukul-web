
<table>
    <thead>
    <tr>
        <th>Start Date</th>
        <th>Plan Id</th>
        <th>Course Id</th>
        <th>Course Name</th>
        <th>Name</th>
        <th>Email</th>
        <th>Segment </th>
        <th>Country code</th>
        <th>Mobile</th>
        <th>Valid Till</th>
        <th>Assigned Through</th>
        <th>Time Spent(mins)</th>
        <th>Progress</th>
        @foreach ($data as $query_val)
            @foreach ($query_val->userChpater->where("asset_type","!=","7")->where("asset_type","!=","4")->sortBy('id') as $val)
                <th>{{ $val->title ?? '' }}</th>
            @endforeach
            @break
        @endforeach

    </tr>
    </thead>
    <tbody>
        @php
             $sum =0;
        @endphp
   `     @foreach($data as $querys)
            @foreach ($query_val->userChpater->sortBy('id') as $val)

            @php
                $sum_time = App\Models\UserCourseProgress::where("chapter_id",$val->id)->where("learner_id",$querys->learner_id)->sum("watched_time");
                $sum = $sum + $sum_time
            @endphp

             @endforeach
        <tr>

            @php
                $course = App\Models\Course::where("id",$courseId)->first();
            @endphp

            <td>{{ $querys->created_at }}</td>
            <td>{{ $querys->plan_id?? '' }}</td>
            <td>{{ $course->id?? '' }}</td>
            <td>{{ $course->title?? '' }}</td>
            <td>{{ $querys->learner->name ?? '' }}</td>
            <td>{{ $querys->learner->email ?? '' }}</td>
            <td>{{ $querys->price == '0.00' ? "Free" : "Paid" }}</td>
            {{-- <td>{{ $querys->course?->type==1 ? "No" : "Yes" }}</td> --}}
            <td>{{ @$querys->countryName->phonecode}}</td>
            <td>{{ $querys->learner->mobile ?? ''}}</td>
            <td>{{ isset($querys->expire_at) ? $querys->expire_at : "LifeTime"  }}</td>
            <td>{{ $querys->transaction_id  }}</td>
            <td>{{ Carbon\CarbonInterval::seconds($sum)->cascade()->forHumans() != '1 second' ? Carbon\CarbonInterval::seconds($sum)->cascade()->forHumans() : '' }}</td>
            <td>{{ $querys->totalProgress!="" ? $querys->totalProgress."%" : ''}}</td>

            {{-- @dd($query_val->userChpater); --}}
            @foreach ($query_val->userChpater->sortBy('id') as $val)
                @php
                    $user_progress = App\Models\UserCourseProgress::where("chapter_id",$val->id)->where("learner_id",$querys->learner_id)->first();

                    $progress =  isset($user_progress->is_completed) ? $user_progress->is_completed==1 ? 'Completed' : 'in Progress' : '';
                    $watch_time = $user_progress?->watched_time!='0.00'  ? $user_progress?->watched_time : '';
                    $pipe = '';
                    if($progress && $watch_time!='') {
                        $pipe = "|";
                    }
                @endphp
                {{-- <td>{{ isset($user_progress->is_completed) ? $user_progress->is_completed==1 ? 'Completed | '.$user_progress->watched_time  :'in Progress'  : '' }}</td> --}}
                <td>{{  $progress }}{{ $pipe }}{{ $watch_time }} </td>
            @endforeach

        </tr>
    @endforeach
    </tbody>
</table>
