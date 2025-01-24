@extends('admin.layouts.app')
@section('content')
  <div class="card">
        <div class="card-header">
            <h3 class="card-title">Blog Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 post">
                    <div >
                        <div class="user-block">
                            <strong>Name</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $blog->name }}</label></div>
                    </div>
                </div>
                <div class='col-12 post'>
                    <div >
                        <div class="user-block">
                                <strong>Tags</strong>
                            </div>
                        <div class="col-md-12 col-sm-12"><label>{{$blog->tags}}</label></div>
                    <img src="{{env('APP_URL').Storage::url($blog->cover) }}" style="max-width: 100%;max-height: 100px;" />
                    </div>
                        </div>
                    <div class='col-12 post'>
                    <div >
                    <div class="user-block">
                            <strong>Content</strong>
                        </div>
                        <div class="col-md-12 col-sm-12" ><textarea id='summernote'>{{$blog->content}}</textarea></div>
                    </div>
                    </div>
                    <div class='col-12 post'>
                    <div >
                        <div class="user-block">
                            <strong>Created At</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $blog->created_at }}</label></div>
                    </div>
                    </div>
                    <div class='col-12 post'>
                   <div >
                        <div class="user-block">
                            <strong>Updated At</strong>
                        </div>
                        <div class="col-md-12 col-sm-12"><label>{{ $blog->updated_at }}</label></div>
                    </div>

                </div>
            </div>
        <!-- /.card-body -->
    </div>
@endsection