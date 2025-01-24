@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Notification Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($notification) && !empty($notification))
                        <div class="post">
                            <div class="user-block">
                                <strong>Notification Title</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $notification->notificationTitle ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Notification Text</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $notification->notificationText ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Time</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $notification->created_at ? date('d/m/Y H:i:s', strtotime($notification->created_at)) : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Updated At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $notification->updated_at ? $notification->date : '' }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
