@extends('admin.layouts.app')
@section('content')
<a href="{{ url()->previous()}}" class="btn btn-warning float-right"><i class="fas fa-arrow-circle-left pr-2"></i> Back</a><br><br>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    {{-- <i class="fas fa-minus"></i> --}}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if(isset($user) && !empty($user))
                        @if(isset($user->profile_picture) && !empty($user->profile_picture) && Storage::exists($user->profile_picture))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Profile Picture</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <img src="{{ Storage::url($user->profile_picture) }}" alt="Profile Picture" srcset="" style="width: 120px; height:120px;">
                                </div>
                            </div>
                        @endif
                        <div class="post">
                            <div class="user-block">
                                <strong>Name</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ isset($user->name) && !empty($user->name) ? $user->name : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Email</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ isset($user->email) && !empty($user->email) ? $user->email : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Roles</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ isset($roles) && !empty($roles) ? $roles : '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ isset($user->created_at) && !empty($user->created_at) ? $user->created_at : '' }}</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection
