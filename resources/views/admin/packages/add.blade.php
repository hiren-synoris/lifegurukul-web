<?php

use App\Models\Course;
use App\Models\User;
?>
@extends('admin.layouts.app')
@section('right-section')
{!! redirect_to_back(route('packages.index')) !!}
@endsection
@section('content')
@section('styles')
@includeIf('admin.layouts.partials.styles.style', [
'select2CSS' => 1,
'summerNoteCSS' => 1,
])
<link rel="stylesheet" href="{{ asset('admin/plugins/datatables-rowreorder/css/rowReorder.bootstrap4.min.css') }}">
<style>
    #showAdvancedOptions>[aria-expanded="true"] i {
        -webkit-transform: rotate(180deg);
        -moz-transform: rotate(180deg);
        transform: rotate(180deg);
    }
</style>
@endsection
<div class="row justify-content-center">
    <div class="col-10 bg-design">
        <form method="POST" action="{{ url('backoffice/packages') }}">
            @csrf

            <div class="d-flex justify-content-center mb-3">
                {{-- <h3>Create Package</h3> --}}
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

            <div class="form-group">
                <label for="title">Package Title</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ old('title') }}" name="title" id="title" onBlur="nameConvertSlug()">
                @error('title')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ old('slug') }}" name="slug" id="slug" readonly>
                @error('slug')
                <div class="text text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- @if (auth()->user()->roles[0]->name == 'admin')

            <div class="coin_section">
                <h3>Success Coin Settings</h3>
                <div class="form-group">

                    <label for="course_coin">Give coin when this Package purchase (enter coin)</label>
                    <input type="text" class="form-control" onkeypress="return isNumericKey(event)" value="{{ old('course_coin') }}" name="course_coin" id="course_coin">
                    @error('course_coin')
                    <div class="text text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group row">

                <div class="col-md-6">
                    <label for="course_finished"> Give coin when learner finished this course (enter coin)</label>
                    <input type="text" class="form-control" onkeypress="return isNumericKey(event)" value="{{ old('course_finished') }}" name="course_finished" id="course_finished">
                </div>
             </div>
              <div class="form-group row">
                <div class="col-md-6">
                <p>Extra coin</p>
                    <label for="course_finished_day">When learner finished this course in specific time (enter coin)</label>
                    <input type="text" class="form-control" onkeypress="return isNumericKey(event)" value="{{ old('course_finished_day') }}" name="course_finished_day" id=" course_finished_day">

                </div>
                <div class="col-md-6 extra-grap">
                    <label for="days">When course finished within specific days (enter days)</label>
                    <input type="text" class="form-control" onkeypress="return isNumericKey(event)" value="{{ old('days') }}" name="days" id="days">
                </div>
               </div>
            </div>

            @endif --}}

    <div class="form-group">
        <label for=instructor_id>Instructor</label><span style="color: red">*</span>
        <select name="instructor_id" class="form-control select3" id="instructor_id">
            <option value="">Select Instructor</option>

            @if (isset($instructors) && !empty($instructors))
            @foreach ($instructors as $key => $value)
            @if (auth()->user()->hasRole(User::INSTRUCTOR) && $value->id != Auth::id())
            @php continue; @endphp
            @endif
            @if (old('instructor_id') == $value->id)
            <option value="{{ $value->id }}" selected>{{ $value->name }}</option>.
            @else
            <option value="{{ $value->id }}">{{ $value->name }}</option>
            @endif
            @endforeach
            @endif
        </select>
        @error('instructor_id')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>


    @if(isset($courseCategories) && !empty($courseCategories) && count($courseCategories) > 0)
    <div class="form-group">
        <label for=category_id>Package Categories</label><span style="color: red">*</span>
        {!! add_new_category_button('course_category') !!}
        <select name="category_id[]" class="form-control course-categories" id="category_id" multiple="multiple" style="width: 100%">
            <option value=""></option>
            @foreach($courseCategories as $key => $value)
            <option value="{{ $value->id }}" {{in_array($value->id, old("category_id") ?: []) ? "selected": ""}}>
                {{ $value->name }}
            </option>
            @endforeach
        </select>
        @error('category_id')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>
    @endif

    <input class="form-check-input select-type" type="hidden" name="type" id="packageType" value="{{ Course::PACKAGE }}" checked>


    <div class="form-group">
        <label for="lng">Language</label>
        <select class="form-control" name="lng" id="lng" style="width: 100%">

            <option value="1">English
            </option>
            <option value="2">Hindi</option>
            <option value="3">Hindi and English</option>

        </select>
    </div>
    <div class="form-group">
        <label for="status">Display Priority</label>
        <input type="text" name="order" id="order" class="form-control" onkeypress="return isNumericKey(event)" oninput="restrictToTwoDigits(event)" onclick="this.select()">
    </div>
    @if (auth()->user()->hasRole('admin'))
    <div class="form-group row switch_section">
        <div class="col-3 form-group">
            @include('admin.layouts.partials.buttons.toggle-button', [
            'id' => 'featured',
            'name' => 'featured',
            'toggleBtnText' => 'Featured',
            ])
        </div>
        <div class="col-3 form-group">
            @include('admin.layouts.partials.buttons.toggle-button', [
            'id' => 'is_free',
            'name' => 'is_free',
            'toggleBtnText' => 'Home',
            ])
        </div>
        <div class="col-3 form-group">
            @include('admin.layouts.partials.buttons.toggle-button', [
            'id' => 'status',
            'name' => 'status',
            'toggleBtnText' => 'Published',
            ])
        </div>
    </div>
    @endif
    <button type="submit" class="btn btn-inline-block submit-btn btn-primary">Submit</button>
    </form>
</div>
</div>
@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', [
'dataTableJS' => 1,
'switch' => 1,
'select2' => 1,
'summerNote' => 1,
'dateRangePicker' => 1,
'validateJS' => 1,
'customScript' => 1,
])
<script src="{{ asset('admin/plugins/datatables-rowreorder/js/dataTables.rowReorder.js') }}">

</script>


<script>
    function nameConvertSlug() {
        var name = document.getElementById("title");
        var nameValue = name.value;
        var Slug = convertToSlug(nameValue);
        var input = $("#slug");
        input.val("");
        input.val(input.val() + Slug);
    }

    function convertToSlug(Text) {
        return Text.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.metaKeywords').select2({
            theme: "classic",
            selectOnClose: false,
            allowClear: true,
            minimumResultsForSearch: -1,
            tags: true,
            tokenSeparators: [',', ' ']
        });
    });
    $('.summernote').summernote();
    // keywords
    $('.tags').select2({
        theme: "classic",
        selectOnClose: false,
        allowClear: true,
        minimumResultsForSearch: -1,
        tags: true,
        tokenSeparators: [',', ' ']
    });

    var table;
    $(document).ready(function() {
        // List course for sale
        $(".list-course-checkbox").on('click', function() {
            if ($(this).is(':checked') && $(this).val() == '4') {
                $('.list-course-checkbox').not(this).prop('checked', false);
                $(this).prop('checked', true);
            } else {
                $("#all").prop('checked', false);
            }
        });


        $(".course-categories").select2({
            theme: "classic",
            selectOnClose: false,
            allowClear: true,
            minimumResultsForSearch: -1,
            tags: true,
            tokenSeparators: [',', ' ']
        });


        function sendOrderToServer(order) {
            var token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: "{{ url('backoffice/course-plan-sortable') }}",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                type: "POST",
                data: {
                    order: order,
                    _token: token
                },
                dataType: "json",
                beforeSend: function() {
                    $('#loader_section').show();
                },
                success: function(response) {
                    $('#loader_section').hide();
                    console.log('Success');
                }
            });
        }
    });
    $(document).ready(function() {
        function hideSelected(value) {
            if (value && !value.selected) {
                return $('<span>' + value.text + '</span>');
            }
        }
        $(".select3").select2({
            selectOnClose: false,
            tags: true,
            tokenSeparators: [',', ' '],
            templateResult: hideSelected,
        });
    })

    function isNumericKey(event) { // javascript code for duration and hours
        const charCode = (event.which) ? event.which : event.keyCode;
        return !(charCode > 31 && (charCode < 48 || charCode > 57));
    }

    function restrictToTwoDigits(event) { // javascript code for duration and hours
        const inputValue = event.target.value;
        if (inputValue.length > 2) {
            event.target.value = inputValue.slice(0, 2);
        }
    }
</script>
@endsection
