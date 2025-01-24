@extends('front.layout.mainlayout')
@section('content')
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')
                <!-- Profile Details -->
                <div class="col-xl-9 col-md-8">
                    <div class="settings-widget profile-details">
                        <div class="settings-inner-blk p-0">
                            <div class="profile-heading">
                                <h3>Payment Methods</h3>
                                <p>Primary payment method is used by default</p>
                            </div>
                            <div class="comman-space">
                                <div class="settings-btn-grp hvr-sweep-to-right">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#addpaymentMethod">Add Payment
                                        Method</a>
                                </div>
                                <div class="payment-method-blk">
                                    <!-- Payment Method -->
                                    <div class="payment-list d-flex align-items-center">
                                        <div class="me-auto">
                                            <h5>Visa ending in 1234</h5>
                                            <p>Expires in 10/2021</p>
                                        </div>
                                        <div>
                                            <ul class="grp-action-style inline-inline">
                                                <li class="list-inline-item">
                                                    <a href="javascript:;"
                                                        class="btn btn-action-style btn-action-primary">primary</a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Edit" class="btn btn-action-style btn-action-dark"><i
                                                            class="fa-regular fa-pen-to-square"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Make it Primary"
                                                        class="btn btn-action-style btn-action-method"><i
                                                            class="fa-regular fa-credit-card"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Remove" class="btn btn-action-style btn-action-delete"><i
                                                            class="fa-regular fa-trash-can"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- /Payment Method -->

                                    <!-- Payment Method -->
                                    <div class="payment-list d-flex align-items-center">
                                        <div class="me-auto">
                                            <h5>Mastercard ending in 1234</h5>
                                            <p>Expires in 10/2021</p>
                                        </div>
                                        <div>
                                            <ul class="grp-action-style inline-inline">
                                                <li class="list-inline-item">
                                                    <a href="javascript:;"
                                                        class="btn btn-action-style btn-action-primary">primary</a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Edit" class="btn btn-action-style btn-action-dark"><i
                                                            class="fa-regular fa-pen-to-square"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Make it Primary"
                                                        class="btn btn-action-style btn-action-method"><i
                                                            class="fa-regular fa-credit-card"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Remove" class="btn btn-action-style btn-action-delete"><i
                                                            class="fa-regular fa-trash-can"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- /Payment Method -->

                                    <!-- Payment Method -->
                                    <div class="payment-list d-flex align-items-center">
                                        <div class="me-auto">
                                            <h5>Discover ending in 1234</h5>
                                            <p>Expires in 10/2021</p>
                                        </div>
                                        <div>
                                            <ul class="grp-action-style inline-inline">
                                                <li class="list-inline-item">
                                                    <a href="javascript:;"
                                                        class="btn btn-action-style btn-action-primary">primary</a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Edit" class="btn btn-action-style btn-action-dark"><i
                                                            class="fa-regular fa-pen-to-square"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Make it Primary"
                                                        class="btn btn-action-style btn-action-method"><i
                                                            class="fa-regular fa-credit-card"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Remove" class="btn btn-action-style btn-action-delete"><i
                                                            class="fa-regular fa-trash-can"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- /Payment Method -->

                                    <!-- Payment Method -->
                                    <div class="payment-list d-flex align-items-center">
                                        <div class="me-auto">
                                            <h5>American Express ending in 1234</h5>
                                            <p>Expires in 10/2021</p>
                                        </div>
                                        <div>
                                            <ul class="grp-action-style inline-inline">
                                                <li class="list-inline-item">
                                                    <a href="javascript:;"
                                                        class="btn btn-action-style btn-action-primary">primary</a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit"
                                                        class="btn btn-action-style btn-action-dark"><i
                                                            class="fa-regular fa-pen-to-square"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Make it Primary"
                                                        class="btn btn-action-style btn-action-method"><i
                                                            class="fa-regular fa-credit-card"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Remove"
                                                        class="btn btn-action-style btn-action-delete"><i
                                                            class="fa-regular fa-trash-can"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- /Payment Method -->

                                    <!-- Payment Method -->
                                    <div class="payment-list d-flex align-items-center">
                                        <div class="me-auto">
                                            <h5>Paypal Express ending in 1234</h5>
                                            <p>Expires in 10/2021</p>
                                        </div>
                                        <div>
                                            <ul class="grp-action-style inline-inline">
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit"
                                                        class="btn btn-action-style btn-action-dark"><i
                                                            class="fa-regular fa-pen-to-square"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Make it Primary"
                                                        class="btn btn-action-style btn-action-method"><i
                                                            class="fa-regular fa-credit-card"></i></a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:;" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Remove"
                                                        class="btn btn-action-style btn-action-delete"><i
                                                            class="fa-regular fa-trash-can"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <!-- /Payment Method -->

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
