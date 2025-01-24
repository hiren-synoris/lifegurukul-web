@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">General Information</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($page) && !empty($page))
                        @if(!empty($page->name))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Title</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->name }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($page->slug))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Slug</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->slug }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($page->body))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Body</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{!! $page->body !!}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($page->status))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Status</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->status == 1 ? 'Active' : 'In Active' }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($created_at))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Created At</strong>
                                </div>
                                <div class="col-md-12 col-sm-12"><label>{{ $created_at }}</label></div>
                            </div>
                        @endif
                    @else
                        <div class="post">
                            <div class="user-block">
                                <strong>Page</strong> <span>details not available</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>

    @if(isset($page) && isset($page->meta_title) && !empty($page->meta_title))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SEO Information</h3>

                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        @if(!empty($page->meta_title))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Meta Title</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->meta_title }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($page->meta_keywords))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Meta Keywords</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->meta_keywords }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($page->meta_description))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Meta Description</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $page->meta_description }}</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
