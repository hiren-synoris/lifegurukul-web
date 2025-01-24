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

        <div class="col-8">
            {{--
            @if(request()->route('courseId')) --}}

                <a href="{{url()->previous() }}" class="btn btn-success">Back</a>
            {{-- @else
                <a href="{{  url("backoffice/learners") }}" class="btn btn-success">Back</a>
            @endif
            @if(request()->route('id'))
            <a href="{{ route("courses.learners",request()->route('id') ) }}" class="btn btn-success">Back</a>
            @endif --}}
            <div class="card mt-3learners mt-3">
                <div class="card-header">Import Excel</div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    {{-- @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                        {{ $error }}
                        @endforeach
                    </div>
                    @endif --}}
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

                        @if ($failures)

                            <table class="table table-danger">
                                <tr>
                                    <th>Row</th>
                                    {{-- <th>Attribute</th> --}}
                                    <th>Errors</th>
                                    {{-- <th>Value</th> --}}
                                </tr>

                                @foreach ($failures as $validation)
                                    <tr>
                                        <td>{{ $validation->row() }}</td>
                                        {{-- <td>{{ $validation->attribute() }}</td> --}}
                                        <td>
                                            <ul>
                                                @foreach ($validation->errors() as $e)
                                                    <li>{{ $e }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        {{-- <td>
                                            {{-- {{ $validation->values()[$validation->attribute()] }}
                                        </td> --}}
                                    </tr>
                                @endforeach
                            </table>

                        @endif

                </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
