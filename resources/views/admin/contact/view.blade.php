@extends('admin.layouts.app')
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Contact Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($contact) && !empty($contact))
                        <div class="post">
                            <div class="user-block">
                                <strong>Name</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->name ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Email</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->email ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Mobile</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->mobile ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Description</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->description ?? '' }}</label>
                            </div>
                        </div>
                        <div class="post">
                            <div class="user-block">
                                <strong>Created At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->created_at ? date('d/m/Y H:i:s', strtotime($contact->created_at)) : '' }}</label>
                            </div>
                        </div>
                        <!-- <div class="post">
                            <div class="user-block">
                                <strong>Updated At</strong>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label>{{ $contact->updated_at ?  date('d/m/Y H:i:s', strtotime($contact->updated_at)) : '' }}</label>
                            </div>
                        </div> -->
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
