@extends('front.layout.mainlayout')

@section('content')
    <div class="main-wrapper">

        <!--Dashbord Student -->
        <div class="page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <!-- Profile Details -->
                    <div class="col-xl-9 col-md-8">
                        <div class="settings-widget profile-details">
                            <div class="settings-menu invoice-list-blk p-0 ">
                                <div class="card pro-post border-0 mb-0">
                                    <div class="card-body">
                                        <div class="invoice-item">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="invoice-logo">
                                                        <img src="{{ !empty(config('settings.logo')) ? Storage::url(config('settings.logo')) : logo_default() }}"
                                                            alt="logo">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="invoice-details">
                                                        <strong>Order:</strong> #{{ $invoiceData->id }} <br>
                                                        <strong>Issued:</strong> {{ dateFormate($invoiceData->created_at) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Invoice Item -->
                                        <div class="invoice-item">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="invoice-info">
                                                        <strong class="customer-text">Invoice From</strong>
                                                        <p class="invoice-details invoice-details-two">
                                                            {{config('settings.invoice_from')}} <br>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="invoice-info invoice-info2">
                                                        <strong class="customer-text">Invoice To</strong>
                                                        <p class="invoice-details">
                                                            {{ $invoiceData->learner->name ?? '' }} <br>
                                                            @if(!empty($invoiceData->learner->mobile)){{ $invoiceData->learner->mobile ?? '' }}, <br>@endif
                                                            @if(!empty($invoiceData->learner->city->name)){{ $invoiceData->learner->city->name ?? '' }}, @endif
                                                            @if(!empty($invoiceData->learner->state->name)){{ $invoiceData->learner->state->name ?? '' }},@endif
                                                            {{ $invoiceData->learner->country->name ?? '' }} <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Invoice Item -->

                                        <!-- Invoice Item -->
                                        <div class="invoice-item">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="invoice-info">
                                                        <strong class="customer-text">Payment Method: </strong>
                                                        <p class="invoice-details invoice-details-two">
                                                            @if(isset($invoiceData->payment_gateway) && !empty($invoiceData->payment_gateway))
                                                                @if ($invoiceData->payment_gateway == \App\Models\UserCourse::RAZOR_PAY)
                                                                    {{ \App\Models\UserCourse::RAZOR_PAY_LABEL }}
                                                                @endif
                                                                @if ($invoiceData->payment_gateway == \App\Models\UserCourse::INSTAMOJO)
                                                                    {{ \App\Models\UserCourse::INSTAMOJO_LABEL }}
                                                                @endif
                                                            @endif
                                                            <br>
                                                            @if(isset($invoiceData->order_status) && !empty($invoiceData->order_status))
                                                                @php
                                                                    echo Helper::checkTransactionType($invoiceData->order_status);
                                                                @endphp
                                                            @endif
                                                        </p>
                                                        {{-- <p class="invoice-details invoice-details-two">
                                                            Debit Card <br>
                                                            XXXXXXXXXXXX-2541 <br>
                                                            HDFC Bank<br>
                                                        </p> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Invoice Item -->

                                        <!-- Invoice Item -->
                                        <div class="invoice-item invoice-table-wrap">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="table-responsive">
                                                        <table class="invoice-table table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Course/Package</th>
                                                                    <th>Description</th>
                                                                    <th class="text-center">Quantity</th>
                                                                    <th class="text-end">Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if (isset($invoiceData->course) && !empty($invoiceData->course))
                                                                    <tr>
                                                                        <td><a
                                                                                href="{{ $invoiceData->course->type == 2 ? url('course-package/' . $invoiceData->course->slug) : url('course-details/' . $invoiceData->course->slug) }}">
                                                                                <img class="img-fluid" alt=""
                                                                                    src="{{ getImageIfExists($invoiceData->course->image, course_img_default()) }}">
                                                                            </a></td>
                                                                        <td><a
                                                                                href="{{ $invoiceData->course->type == 2 ? url('course-package/' . $invoiceData->course->slug) : url('course-details/' . $invoiceData->course->slug) }}">{{ $invoiceData->course->title ?? '' }}</a>
                                                                        </td>
                                                                        <td class="text-center">1</td>
                                                                        {{-- <td class="text-end">$100</td> --}}
                                                                        <td class="text-end">{{ isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan) && ($invoiceData->coursePlan->plan_type != 0 ) ? $invoiceData->coursePlan->final_payable_price : 'FREE' }}</td>
                                                                    </tr>
                                                                @endif


                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-xl-4 ms-auto">
                                                    <div class="table-responsive">
                                                        <table class="invoice-table-two table table-borderless">
                                                            <tbody>
                                                                {{-- <tr>
                                                                    <th>Subtotal:</th>
                                                                    <td><span>$350</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Discount:</th>
                                                                    <td><span>-10%</span></td>
                                                                </tr> --}}
                                                                <tr>
                                                                    <th>Total Amount:</th>
                                                                    <td><span>{{ (isset($invoiceData->coursePlan) && !empty($invoiceData->coursePlan) &&$invoiceData->coursePlan->final_payable_price > 0) ? $invoiceData->coursePlan->final_payable_price : 'FREE' }}</span></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /Invoice Item -->

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



    </div>
@endsection
