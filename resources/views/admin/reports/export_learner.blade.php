@php
    $igst = config()->has('settings.igst') ? config('settings.igst') : null;
    $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
    $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;
    use App\Models\Countries;
    use App\Models\Role;


@endphp

<table>
    <thead>
        <tr>
            <th>Enroll Date</th>
            <th>Plan Id</th>
            <th>Course Id</th>
            <th>Course Name</th>
            <th>Name</th>
            <th>Email</th>
            {{-- <th>Segment</th> --}}

            <th>Coutry code</th>
            <th>Mobile</th>
            <th>Expire Date</th>
            <th>Actual Price</th>
            {{-- <th>GST Amount</th> --}}
            <th>Selling Price</th>
            <th>Coupon Disount Price</th>
            <th>Coupon name</th>
            <th>Used Coins</th>
            <th>Last Login Date</th>
            <th>Invoice No</th>
            <th>Order Status</th>
            <th>Payment Gateway</th>
            <th>Transaction id</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $querys)
            @php
                $country_code = Countries::where('id', @$querys->learner->country_id)->first();
                $role_name = Role::where('id', @$row->createdBy->id)->first();
            @endphp
            <tr>
                <td>{{ $querys->created_at }}</td>
                <td>{{ $querys->plan_id }}</td>
                <td>{{ $querys->course_id }}</td>
                <td>{{ $querys->course->title ?? '' }}</td>
                <td>{{ $querys->learner->name ?? '' }}</td>
                <td>{{ $querys->email ?? '' }}</td>
                @php
                    $status = '';
                    // if( $querys->order_status!=2) {
                    //     $status = $querys->payment_gateway==1 ? "Razorpay" : ($querys->payment_gateway==2 ? "Instamojo" : "FREE");
                    // } else {
                    //     $status ="Failed";
                    // }

                    if ($querys->payment_gateway == 1) {
                        $status = 'Razorpay';
                    } elseif ($querys->payment_gateway == 2) {
                        $status = 'Instamojo';
                    } elseif ($querys->payment_gateway == 3) {
                        $status = 'In-app purchase';
                    }

                    // $sgst_price = 0.0;
                    // $cgst_price = 0.0;
                    // $igst_price = 0.0;
                    // $text = 0.00;

                    // if ($querys->country_id == 1) {
                    //     if (strtolower(@$querys->stateName->name) == 'gujarat') {
                    //         $text = gstCal($querys, $sgst) + gstCal($querys, $cgst);
                    //     } else {
                    //         $text =  gstCal($querys, $igst);
                    //     }
                    // } else {
                    //     $text =  '';
                    // }

                @endphp


                <td>{{ $querys->countryName->phonecode ?? "" }}</td>
                <td>{{ @$querys->learner->mobile ?? '' }}</td>
                {{-- <td>{{ @$querys->expire_at ?? '' }}</td> --}}
                <td>{{ !empty($querys->expire_at) ?  Helper::only_date_format($querys->expire_at) : 'LifeTime' }}</td>

                <td>{{ $querys->price }}</td>

                <td>{{ isset($querys->price) && !empty($querys->after_deduction_price) ? $querys->after_deduction_price : 0 }}


                <td>{{ $querys->after_coupon_applied_deduction_price }}</td>

                <td>{{ isset($querys->getCoupon->code) && !empty($querys->getCoupon->code) ? $querys->getCoupon->code : '' }}
                </td>

                <td>{{ isset($querys->user_coin) ? $querys->user_coin . '*' . $querys->per_coin_price : '' }}
                </td>


                <td>
                    @if (!empty($querys->learnerLastLogin) && $querys->learnerLastLogin->type == 1)
                        {{ date('d/m/Y H:i:s', strtotime($querys->learnerLastLogin->updated_at)) }}
                    @endif

                </td>

                <td>{{ $querys->id }}</td>

                <td>
                    @if ($querys->order_status == 1)
                        <span>completed</span>
                    @elseif($querys->order_status == 2)
                        <span>Failed</span>
                    @elseif($querys->order_status == 3)
                        <span>Free</span>
                    @elseif($querys->order_status == 4)
                        @if ($role_name)
                            <span>{{ $role_name?->display_name . ' ' . $row->createdBy->name }}</span>
                        @else
                            <span>Enroll by admin</span>
                        @endif
                    @elseif($querys->order_status == 5)
                        <span>Zapier</span>
                    @else
                        <span></span>
                    @endif
                </td>
                <td>{{ $status }}</td>

                <td>{{ $querys->transaction_id }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
