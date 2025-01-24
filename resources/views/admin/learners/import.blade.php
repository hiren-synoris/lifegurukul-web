@extends('admin.layouts.app')
@section('right-section')
@php
$learner = new App\Models\Learner();
@endphp

<!-- Contain Bulk Add functionalities-->
<div style="display: none;" id="blk_import_frm">
    @includeIf('admin.layouts.partials.actions.bulk-import-data',[
    'bulkImportURL' => url('backoffice/courses/'.$learner->id.'/package/bulk_add')
    ])
</div>

{{-- @includeIf('admin.layouts.partials.buttons.bulk-import-sample',[
'fileUrl' => asset("admin\sampleFiles\learner.xlsx"),
])

<!-- Bulk Add Button -->
@includeIf('admin.layouts.partials.buttons.bulk-import',[
'importUrl' => url('backoffice/learners/import'),
]) --}}

@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">

        <div>
            <a href="{{ url('/backoffice/learners') }}" class="btn btn-success">Back</a>
            <div class="card mt-3">
                <div class="card-header">Import Excel</div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                        {{ $error }}
                        @endforeach
                    </div>
                    @endif

                    @if (isset($learners_data) && !empty($learners_data))

                    <table class="table table-danger">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Country Code</th>
                            <th>Phone number</th>
                            <th>Gender</th>
                            <th>Date Of Birth</th>
                            {{-- <th>Status</th> --}}
                            <th>Error</th>
                        </tr>
                        {{-- @php
                            dd($learners_data);
                        @endphp
                         --}}
                        @foreach ($learners_data as $learner_key => $learner_val)
                        @if(( $learner_val['2']==null) && ( $learner_val['3']==null) || (isset($failure_rows[$learner_key])==true))
                        <tr>

                            <td>{{ $learner_val['0'] }}</td>
                            <td>{{ $learner_val['1'] }}</td>
                            <td>{{ $learner_val['2'] }}</td>
                            <td>{{ $learner_val['3'] }}</td>
                            <td>{{ $learner_val['4'] }}</td>
                            <td>{{ $learner_val['5'] }}</td>
                            {{-- <td>
                                 @php
                                    dump(isset($failure_rows[$learner_key]))
                                @endphp

                                 @if (isset($failure_rows) && !empty($failure_rows))
                                    @if(isset($failure_rows[$learner_key])==true)
                                        Errors
                                    @elseif(!isset($failure_rows[$learner_key])!=true)
                                        Error
                                    @else
                                        Success
                                    @endif
                                @endif
                             </td>  --}}
                            <td>

                                @if (isset($failure_rows) && !empty($failure_rows))
                                    @if(isset($failure_rows[$learner_key]))
                                        {{$failure_rows[$learner_key]}}

                                    @endif
                                @endif
                                @if( $learner_val['2']==null)
                                <span>The Country Code is required.</span><br>
                                @endif
                                @if( $learner_val['3']==null)
                                <span>The phone number is required.</span>
                                @endif
                            </td>

                        </tr>
                        @endif
                        @endforeach
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
