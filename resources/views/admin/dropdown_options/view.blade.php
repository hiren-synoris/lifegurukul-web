@extends('admin.layouts.app')
@section('right-section')
{!! redirect_to_back(url('backoffice/dropdown_options/'.$dropdownOption->dropdown->id)) !!}
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dropdown Option Details</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @if (isset($dropdownOption) && !empty($dropdownOption))
                        @if(isset($dropdownOption->dropdown) && !empty($dropdownOption->dropdown))
                            @if(!empty($dropdownOption->dropdown->name))
                                <div class="post">
                                    <div class="user-block">
                                        <strong>Dropdown Name</strong>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <label>{{ $dropdownOption->dropdown->name }}</label>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if(!empty($dropdownOption->name))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Option Name</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $dropdownOption->name }}</label>
                                </div>
                            </div>
                        @endif
                        @if (isset($dropdownOption->image) && !empty($dropdownOption->image) && Storage::exists($dropdownOption->image))
                            <div class="post">
                                <div class="user-block">
                                    <strong>Image</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <img src="{{ Storage::url($dropdownOption->image) }}" alt="Image"
                                        srcset="" style="width: 120px; height:120px;">
                                </div>
                            </div>
                        @endif
                        {{-- @if(!empty($dropdownOption->status)) --}}
                            <div class="post">
                                <div class="user-block">
                                    <strong>Status</strong>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <label>{{ $dropdownOption->status == 1 ? 'Active' : 'In Active' }}</label>
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
                                <strong>Dropdown Option</strong> <span>details not available</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
@endsection
