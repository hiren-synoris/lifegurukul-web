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
            <div class="col-xl-9 col-md-8" id="my_wallet">
                <div class="settings-widget profile-details">
                    <div class="settings-inner-blk p-0">
                        <div class="profile-heading">
                            <h3>My wallet - <span>Total Coins ({{ $total }})</span></h3>
                        </div>
                        <div class="comman-space pb-0">
                            <div class="settings-invoice-blk table-responsive">
                                <!-- Invoice info-->
                                <table class="table table-borderless mb-0">
                                    <thead>
                                        <tr>
                                            {{-- <th>Name</th> --}}
                                            <th>Coin</th>
                                            <th>Type</th>
                                            <th>Course Name</th>
                                            <th>chapter name</th>
                                            <th>Comment</th>
                                            <th>Created_at</th>
                                        </tr>
                                    </thead>
                                    @if (isset($wallet) && count($wallet) > 0)
                                    <tbody>
                                        @foreach ($wallet as $key => $value)
                                        <tr class="text-center">
                                            @php
                                            $label = '';

                                            if ($value->type == 1) {
                                            if ($value->getCourse->type == 1) {
                                                if(Helper::checkFree($value->course_id, $value->learner_id)) {
                                                    
                                                    $label = '<span class="badge badge-success"> Added Course Free</span></h1>';
                                                } else {
                                                    
                                                    $label = '<span class="badge badge-success">Course Purchased</span></h1>';
                                                }
                                            } else {

                                                if(Helper::checkFree($value->course_id, $value->learner_id)) {
                                                    
                                                    $label = '<span class="badge badge-success"> Added Package Free</span></h1>';
                                                } else {
                                                    
                                                    $label = '<span class="badge badge-success">Package Purchased</span></h1>';
                                                }
                                          
                                            }
                                            }

                                            if ($value->type == 2) {
                                            $label = '<span class="badge badge-warning">Redeem</span></h1>';
                                            }
                                            if ($value->type == 3) {
                                            $label = '<span class="badge badge-primary">Added by Admin</span></h1>';
                                            }
                                            if ($value->type == 4) {
                                            $label = '<span class="badge badge-warning">Deduct by Admin</span></h1>';
                                            }
                                            if ($value->type == 5) {
                                            $label = '<span class="badge badge-success">Whole video completed</span></h1>';
                                            }
                                            if ($value->type == 6) {
                                            $label = '<span class="badge badge-success">Profile completed</span></h1>';
                                            }
                                            if ($value->type == 7) {
                                            $label = '<span class="badge badge-success">Finishing certain courses</span></h1>';
                                            }
                                            if ($value->type == 8) {
                                            $label = '<span class="badge badge-success"> Finishing a course in a certain time</span></h1>';
                                            }
                                            @endphp
                                            {{-- <td>{{ @$value->getLerner->name }}</td> --}}
                                            <td>{{ $value->coins }}</td>
                                            <td>{!! $label !!}</td>
                                            <td>{{ @$value->getCourse->title }}</td>
                                            <td>{{ @$value->getChapter->title }}</td>
                                            <td style="">{{ @$value->comment }}</td>
                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y') }}</td>
                                            <td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @else

                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">No records found!</td>
                                        </tr>
                                    </tbody>
                                    @endif
                                </table>
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="pagination lms-page">
                                            {!! $wallet->withQueryString()->links('pagination::bootstrap-4') !!}
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