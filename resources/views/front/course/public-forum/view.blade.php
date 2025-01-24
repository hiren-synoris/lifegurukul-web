@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Support Ticket Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($support) && !empty($support))
                        <div class="post">
                            <div class="user-block">
                                <strong>Subject</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $support->subject ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Description</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{!! $support->description ?? '' !!}</label>
                            </div>
                        </div>
                        @if(isset($support->user) && !empty($support->user) && !empty($support->user->name))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Created By</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $support->user->name }}</label>
                                </div>
                            </div>
                        @endif
                        <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $support->created_at ? date('d/m/Y H:i:s', strtotime($support->created_at)) : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Updated At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $support->updated_at ?  date('d/m/Y H:i:s', strtotime($support->updated_at)) : '' }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
