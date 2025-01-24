@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Permission Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                    <i class="fas fa-times"></i>
                </button> --}}
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="post">
                        <div class="user-block">
                            <strong>Name</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $permission->name }}</label></div>
                    </div>
                    <div class="post">
                        <div class="user-block">
                            <strong>Module Name</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>
                                {{ $permission->module_name }}
                            </label></div>
                    </div>
                    <div class="post">
                        <div class="user-block">
                            <strong>Created At</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $created_at }}</label></div>
                    </div>

                    <div class="post">
                        <div class="user-block">
                            <strong>Updated At</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $updated_at }}</label></div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection
