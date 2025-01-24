@extends('admin.layouts.app')
@section('content')
<a href="{{ url("backoffice/dropdowns") }}" class="btn btn-warning float-right" style="margin-top: -47px
;">Back</a>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dropdown Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($dropdown) && !empty($dropdown))
                        @if(!empty($dropdown->name))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Dropdown Name</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $dropdown->name }}</label>
                                </div>
                            </div>
                        @endif
                        @if(!empty($dropdown->slug))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Dropdown Slug</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $dropdown->slug }}</label>
                                </div>
                            </div>
                        @endif
                        {{-- @if(!empty($dropdown->status)) --}}
                            <div class="post">
                                <div class="user-block">
                                    <strong>Status</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $dropdown->status == 1 ? 'Active' : 'In Active' }}</label>
                                </div>
                            </div>
                        {{-- @endif --}}
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
                                <strong>Dropdown</strong> <span>details not available</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection
