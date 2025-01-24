@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Settings Details</h3>

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
                    <!-- <div class="post">
                        <div class="user-block">
                            <strong>key</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $settings->key }}</label></div>
                    </div> -->
                    <div class="post">
                        <div class="user-block">
                            <strong>Display name</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>
                                {{ $settings->display_name }}
                            </label></div>
                    </div>
                    <div class="post">
                        <div class="user-block">
                            <strong>Key</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $settings->key }}</label></div>
                    </div>
                    <div class="post">
                        <div class="user-block">
                            <strong>Value</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>
                            @if($settings->setting_type == "boolean")
                                {{ $settings->value == "1" ? "Yes":"No" }}
                            @elseif($settings->setting_type == 'file')
                               <img width="100px" src='{{ asset(Storage::url($settings->value)) }}'>
                            @else
                                {{ $settings->value }}
                            @endif

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
