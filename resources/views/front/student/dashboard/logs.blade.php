@extends('front.layout.mainlayout')
@section('content')
<style>
    th{
        text-align: center
    }
</style>
    <!--Dashbord Student -->
    <div class="page-content">
        <div class="container">
            <div class="row">

                @include('front.student.components.sidebar')

                <!-- Profile Details -->
                <div class="col-xl-9 col-md-8" id="learner_log">
                    <div class="settings-widget profile-details">
                        <div class="settings-inner-blk p-0">
                            <div class="profile-heading">
                                <h3>Activity Log</h3>
                            </div>
                            <div class="comman-space pb-0">
                                <div class="settings-invoice-blk table-responsive">
                                    <!-- Invoice info-->
                                    <table class="table table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th>Device</th>
                                                <th>Course/Package</th>
                                                <th>Description</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        @if (isset($logs) && count($logs) > 0)
                                        <tbody>
                                            @foreach ($logs as $key => $value)
                                            @php
                                                $label = "";
                                                if($value->type==2) {
                                                    $label = "<span class='badge badge-danger'>Logout</span></h1>";
                                                } else {
                                                    $label ="<span class='badge badge-success'>$value->description</span></h1>";
                                                }

                                            @endphp
                                            <tr class="text-center">
                                                {{-- {{dd($value)}} --}}
                                                <td>{{ $value->device_name==1 ? "Android" : ($value->device_name==2 ? "IOS" : $value->device_name.' (WEB)' )  }}</td>
                                                <td>{{ @$value->getCourse->title }}</td>
                                                <td>{!! $label !!}</td>
                                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at)->format('d-m-Y H:i:s') }}
                                                </td>
                                                <td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                            @else
                                            <tr colspan="5">No record found!</tr>
                                            @endif
                                    </table>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="pagination lms-page">
                                                {!! $logs->withQueryString()->links('pagination::bootstrap-4') !!}
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
