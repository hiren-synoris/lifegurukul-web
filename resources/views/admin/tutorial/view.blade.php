@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('tutorial.index')) !!}
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tutorial Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($data) && !empty($data))
                        <div class="post">
                            <div class="user-block">
                                <strong>Title</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $data->title ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Description</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{!! $data->description ?? '' !!}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>YouTube URL</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{!! $data->link ?? '' !!}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $data->created_at ? date('d/m/Y H:i:s', strtotime($data->created_at)) : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Updated At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $data->updated_at ?  date('d/m/Y H:i:s', strtotime($data->updated_at)) : '' }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
