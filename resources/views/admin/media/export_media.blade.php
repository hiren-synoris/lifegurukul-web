<table>
    <thead>
    <tr>
        <th>VdoCipher Id</th>
        <th>Image</th>
        <th>File Name</th>
        <th>Created At</th>
        <th>Course Attached</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $querys)

        <tr>
            <td>{{ $querys->asset_type==0 || $querys->asset_type==1 ? $querys->videoId : '-' }}</td>
            <td>
                @php
                    if (Illuminate\Support\Facades\Storage::exists($querys->path) && !empty($querys->path)) {
                        echo url('/').Illuminate\Support\Facades\Storage::url($querys->path);
                    } else {
                        echo 'No Image';
                    }
                    // if (Illuminate\Support\Facades\Storage::exists($querys->path) && !empty($querys->path)) {
                    //     $fileUrl = Illuminate\Support\Facades\Storage::url($querys->path);
                    //     $fullUrl = url('/') . $fileUrl;
                    //     $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));

                    //     if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                    //         echo '<a href="' . $fullUrl . '" target="_blank"><img src="' . $fullUrl . '" style="height: 60px; width: 60px;"></a>';
                    //     } else {
                    //         echo '<a href="' . $fullUrl . '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    //     }
                    // } else {
                    //     echo 'No Image';
                    // }
                @endphp
            </td>
            <td>{{ $querys->file_name ?? '' }}</td>
            <td>{{ $querys->created_at }}</td>
            <td>{{ Helper::getMediaCourse($querys->id) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
