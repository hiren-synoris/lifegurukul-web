@extends('admin.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">FAQ Details</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 post">
                <div>
                    <div class="user-block">
                        <strong>Question</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><label>{{ $faq->question }}</label></div>
                </div>
            </div>

            <div class='col-12 post'>
                <div>
                    <div class="user-block">
                        <strong>Answer</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><textarea class="summernote-editor" id='summernote'>{{$faq->answer}}</textarea></div>
                </div>
            </div>
            <div class='col-12 post'>
                <div>
                    <div class="user-block">
                        <strong>Order</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><label>{{$faq->order}}</label></div>
                </div>
            </div>
            <div class='col-12 post'>
                <div>
                    <div class="user-block">
                        <strong>Created At</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><label>{{ $faq->created_at ? date('d/m/Y H:i:s', strtotime($faq->created_at)) : ''}}</label></div>
                </div>
            </div>
            <div class='col-12 post'>
                <div>
                    <div class="user-block">
                        <strong>Updated At</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><label>{{ $updated_at }}</label></div>
                </div>

            </div>
            <div class='col-12 post'>
                <div>
                    <div class="user-block">
                        <strong>Status</strong>
                    </div>
                    <div class="col-md-12 col-sm-12"><label>{{ $faq->status == 1 ? 'Active' : 'In Active' }}</label></div>
                </div>

            </div>
        </div>
        <!-- /.card-body -->
    </div>
    @endsection
    @section('scripts')
    <script src='{{asset("admin/plugins/summernote/summernote-bs4.min.js")}}'></script>
    <script>
        $('.summernote-editor').summernote('disable');
    </script>
    @endsection
