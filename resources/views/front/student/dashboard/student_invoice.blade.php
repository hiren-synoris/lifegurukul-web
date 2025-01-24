@php
use App\Models\States;
@endphp
@extends('front.layout.mainlayout')
@section('content')
    <style>
        th {
            text-align: center
        }
    </style>
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')

                <!-- Profile Details -->
                <div class="col-xl-9 col-md-8" id="my_order">
                    <div class="settings-widget profile-details">
                        <div class="settings-inner-blk p-0">
                            <div class="profile-heading">
                                <h3>Purchase History</h3>
                                <!-- <p>Order Dashboard is a quick overview of all current orders.</p> -->
                            </div>
                            <div class="comman-space pb-0">
                                <div class="settings-invoice-blk table-responsive">
                                    <!-- Invoice info-->
                                    @php
                                        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
                                        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
                                        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;
                                    @endphp
                                    <table class="table table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th>order id</th>
                                                <th>Title</th>
                                                <th>date & Time</th>
                                                <th>Actual Price</th>
                                                {{-- <th>GST Amount</th> --}}
                                                <th>Coupon Discount</th>
                                                <th>Coin Applied</th>
                                                <th>Paid Amount</th>
                                                <!-- <th>Per Coin Price</th>                                                       -->
                                                <th>Transaction id</th>
                                                <th>Payment Gateway</th>
                                                <th>Status</th>
                                                <th>Download Invoice</th>
                                            </tr>
                                        </thead>
                                        @if (isset($courses) && count($courses) > 0)
                                            <tbody>
                                                @foreach ($courses as $key => $value)
                                                    {{-- @php
                                                        $sgst_price = 0.0;
                                                        $cgst_price = 0.0;
                                                        $igst_price = 0.0;
                                                        $text = 0.0;

                                                        if ($value->country_id == 1) {
                                                            $data = collect([$value->price,$value->per_coin_price,$value->after_coupon_applied_deduction_price]);
                                                            $stateName = States::where("id",$value->state_id)->first();
                                                            if (strtolower(@$stateName->name) == 'gujarat') {
                                                                $text = gstCal($value, $sgst) + gstCal($value, $cgst);
                                                            } else {
                                                                $text = gstCal($value, $igst);
                                                            }
                                                        } else {
                                                            $text = '';
                                                        }

                                                    @endphp --}}
                                                    <tr>

                                                        <td>
                                                            {{-- <a href="{{ url('view-invoice/'.Crypt::encrypt($value->user_courses_id)) }}" class="invoice-no">#{{$value->user_courses_id}}</a> --}}
                                                            #{{ $value->user_courses_id }}

                                                        </td>
                                                        <td><a
                                                                href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">{{ $value->title }}</a>
                                                        </td>
                                                        <td>{{ $value->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>{{ $value->price }}</td>

                                                        <td>{{ $value->after_coupon_applied_deduction_price }}</td>
                                                        {{-- <td>{{ $value->user_coin!="" ? }})</td> --}}
                                                        <td>
                                                            @if ($value->user_coin != '')
                                                                {{ $value->user_coin * $value->per_coin_price }}({{ $value->user_coin }}
                                                                * {{ $value->per_coin_price }})
                                                            @endif


                                                        </td>
                                                        <td>{{ $value->after_deduction_price }}</td>
                                                        <!-- <td>{{ $value->per_coin_price }}</td>                                                                                                                                                 -->
                                                        <td>{{ $value->transaction_id }}</td>
                                                        @php
                                                            $status = '';
                                                            // if( $value->order_status!=2) {
                                                            //     $status = $value->payment_gateway==1 ? "Razorpay" : ($value->payment_gateway==2 ? "Instamojo" : "FREE");
                                                            // }
                                                            if ($value->payment_gateway == 1) {
                                                                $status = 'Razorpay';
                                                            } elseif ($value->payment_gateway == 2) {
                                                                $status = 'Instamojo';
                                                            } elseif ($value->payment_gateway == 3) {
                                                                $status = 'In-app purchase';
                                                            } else {
                                                                $status = '';
                                                            }
                                                        @endphp
                                                        <td>{{ $status }}</td>

                                                        <td>
                                                            @if ($value->order_status == 1)
                                                                <span class="badge status-completed">completed</span>
                                                            @elseif($value->order_status == 2)
                                                                <span class="badge status-due">Failed</span>
                                                            @elseif($value->order_status == 3)
                                                                <span class="badge status-success">Free</span>
                                                            @elseif($value->order_status == 4)
                                                                <span class="badge status-success">Enrol by Admin</span>
                                                            @elseif($value->order_status == 5)
                                                                <span class="badge status-due"></span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($value->price > 0 && $value->invoice)
                                                                <a download
                                                                    href="{{ getImageIfExists($value->invoice, course_img_default()) }}"
                                                                    class="btn-style" title="Download Invoice"><i
                                                                        class="fa fa-download"></i></a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        @else
                                            <tbody>
                                                <tr>
                                                    <td colspan="12" class="text-center">No records found!</td>
                                                </tr>
                                            </tbody>
                                        @endif
                                    </table>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="pagination lms-page">
                                                {!! $courses->withQueryString()->links('pagination::bootstrap-4') !!}
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="mt-5"></div>
                                    <!-- /Invoice info-->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Profile Details -->
            </div>
        </div>
    </div>
    <!-- /Dashbord Student -->
@endsection
