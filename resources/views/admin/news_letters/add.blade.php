@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('news_letters.index')) !!}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-10 bg-design">
            <form method="POST" action="{{ url('backoffice/news_letters') }}">
                @csrf
                <div class="d-flex justify-content-center mb-3">
                    {{-- <h3>Add</h3> --}}
                </div>

                {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

                <div class="form-group">
                    <label for="name">Name <span style="color: red">*</span></label>
                    <input type="text" class="form-control" value="{{ old("name") }}" name="name" id="name" >
                    @error('name')
                        <div class="text text-danger">{{ $message }}</div>
                    @enderror

                </div>
                <div class="form-group">
                    <label for="email">Email <span style="color: red">*</span></label>
                    <input type="email" class="form-control" value="{{ old("email") }}" name="email"  id="email" >

                @error('email')
                    <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
                <button type="submit" id="butsave" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
