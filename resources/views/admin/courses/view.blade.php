
@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Course Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($course) && !empty($course))
                        @if(isset($course->title) && !empty($course->title))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Title</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $course->title }}</label>
                                </div>
                            </div>
                        @endif
                        @if(isset($course->user) && !empty($course->user))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Instructor Name</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ isset($course->user->name) && !empty($course->user->name) ? $course->user->name : '' }}</label>
                                </div>
                            </div>
                        @endif
                        @if(isset($course->status) && !empty($course->status))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Status</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $course->status == 1 ? 'Active' : 'In Active' }}</label>
                                </div>
                            </div>
                        @endif
                        @if(isset($created_at) && !empty($created_at))
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
                                <strong>Course</strong> <span>details not available</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection
