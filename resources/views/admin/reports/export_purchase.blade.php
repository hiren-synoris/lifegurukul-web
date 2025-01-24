<table>
    <thead>
    <tr>
        <th>Created On</th>
        <th>Course Name</th>
        <th>Name</th>
        <th>Email</th>
        <th>Segment</th>
        <th>Country Code</th>
        <th>Mobile</th>
        <th>Actual Price</th>
        <th>Selling Price</th>
        <th>Coupon Disount Price</th>
        <th>Coupon name</th>
        <th>Used Coins</th>
        <th>Progress</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $querys)

        <tr>
            <td>{{ $querys->created_at }}</td>
            <td>{{ $querys->course->title?? '' }}</td>
            <td>{{ $querys->learner->name ?? '' }}</td>
            <td>{{ $querys->learner->email ?? '' }}</td>
            <td>{{ $querys->price > 0 ? "Free" : "paid" }}</td>
            {{-- <td>{{ $querys->course?->type==1 ? "No" : "Yes" }}</td> --}}
            <td>{{ @$querys->countryName->phonecode ?? ''}}</td>
            <td>{{ $querys->learner->mobile ?? ''}}</td>
            <td>{{ $querys->price}}</td>
            <td>{{ isset($querys->price) && !empty($querys->after_deduction_price) ? $querys->after_deduction_price : 0 }}</td>
            <td>{{ $querys->after_coupon_applied_deduction_price }}</td>
            <td>{{ isset($querys->getCoupon->code) && !empty($querys->getCoupon->code) ? $querys->getCoupon->code : '' }}</td>
            <td>{{ isset($querys->user_coin) && !empty($querys->user_coin) ? ($coin_price != 0 ? $querys->user_coin."*".$coin_price : $querys->user_coin) : 0 }}</td>
            <td>{{ $querys->totalProgress!="" ? $querys->totalProgress."%" : ''}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
