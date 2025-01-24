@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Letter Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($news_letter) && !empty($news_letter))
                        <div class="post">
                            <div class="user-block">
                                <strong>Name</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $news_letter->name ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $news_letter->created_at ? date('d/m/Y H:i:s', strtotime($news_letter->created_at)) : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Updated At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $news_letter->updated_at ? $news_letter->date : '' }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
