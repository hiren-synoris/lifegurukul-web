<?php

use App\Models\Coupon;
use App\Models\User;
?>
@extends('admin.layouts.app')
@section('right-section')
{!! redirect_to_back(route('coupons.index')) !!}
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
        <form method="POST" action="{{ url('backoffice/coupons') }}" id="coupon_form">
            @csrf

            <div class="d-flex justify-content-center mb-3">
                {{-- <h3> @if (isset($pg_header))
                    {{ ucwords($pg_header) }}
                @endif</h3> --}}
            </div>

            {{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}
            {{--
            <div class="form-group">
                <label for="name">Name</label><span style="color: red">*</span>
                <input type="text" class="form-control" value="{{ old('name') }}" name="name" id="name"
            onBlur="nameConvertSlug()">
            @error('name')
            <div class="text text-danger">{{ $message }}</div>
            @enderror
    </div> --}}
    <div class="form-group">

        <label for="course_coin">Code</label><span style="color: red">*</span>
        <input type="text" class="form-control" value="{{ old('code') }}" name="code" id="code">
        @error('code')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group">
        <label for="type">Type</label><span style="color: red">*</span>
        <select class="form-control" name="type" id="type" style="width: 100%">
            <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Percentage</option>
            <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>Fixed Price
            </option>

        </select>
    </div>
    <div class="form-group">

        <label for="value">Value</label><span style="color: red">*</span>
        <input type="number" class="form-control" value="{{ old('value') }}" name="value" id="value">
        @error('value')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group" id="max_val">
        <label for="max_amt">Max amount</label><span style="color: red">*</span>
        <input type="number" name="max_amt" id="max_amt" class="form-control" value="{{ old('max_amt') }}">
        @error('max_amt')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>

    @if (isset($courses) && !empty($courses) && count($courses) > 0)
    <div class="form-group">
        <label for=course_id>Courses</label><span style="color: red">*</span>
        <select name="course_ids[]" @readonly(true) class="form-control courses" id="course_ids" multiple="multiple" style="width: 100%">
            <option value="">Select course</option>
            @foreach ($courses as $key => $value)
            <option value="{{ $value->id }}" {{ in_array($value->id, old('course_ids') ?: []) ? 'selected' : '' }}>
                {{ $value->title }}
            </option>
            @endforeach
        </select>
        @error('course_ids')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>
    @endif

    <div class="form-group">
        <label for="exp_date">Expiry Date</label><span style="color: red">*</span>
        <input type="text" class="form-control exp_date" name="exp_date" value="{{ old('exp_date') }}" id="exp_date">
        @error('exp_date')
        <div class="text text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        @include('admin.layouts.partials.buttons.toggle-button', [
        'dataValue' => 1,
        'id' => 'status',
        'name' => 'status',
        'toggleBtnText' => 'Status',
        ])
    </div>

    <div class="form-group">
        <label for="new_user">New User</label>
        <div class="icheck-success">
            <input type="checkbox" name="new_user" id="new_user" value="1">
            <label for="new_user"></label><br />
        </div>
    </div>


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
<script src="{{ asset('admin/plugins/datatables-rowreorder/js/dataTables.rowReorder.js') }}"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
</head>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {


        
        $(function() {
            var todayDate = new Date();
          
            $(".exp_date").datepicker({
                autoclose: true,
                format: 'dd/mm/yyyy',
                startDate: todayDate
            });

        });
        // $('.exp_date').on('paste', function(e) {
        //     e.preventDefault();
        // });

        $("#coupon_form").validate({
            rules: {
                code: {
                    required: true,
                    noWhitespace: true
                },
                value: {
                    required: true,
                    trimmedNumber: true,
                    maxPercentage: true,
                    number: true,
                    min: 1,
                },
                max_amt: {
                    required: function(element) {
                        return $('#type').val() === '1';
                    },
                    number: true,
                    min: 1,
                    trimmedNumber: true,

                },
                exp_date: {
                    required: true
                },
                'course_ids[]': {
                    required: true,
                    minlength: 1 // Ensure at least one course id is selected
                }

            },
            messages: {
                code: {
                    required: "Please enter a code"
                },
                value: {
                    maxPercentage: "The value must be maximum 100",
                    trimmedNumber: "Please enter a valid number."

                },
                max_amt: {
                    required: "Max amount field is required.",
                    number: "Please enter a valid number for max amount",
                    // min: "Max amount must be at least 1",
                    trimmedNumber: "Please enter a valid number."
                },
                exp_date: {
                    required: "Please enter a expiration date"
                },
                'course_ids[]': "Please select at least one course"

            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "course_ids[]") {
                    error.insertAfter(element.parent("div")).css('margin-bottom', '1rem');
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                // Handle form submission
                form.submit();
            }
        });

        $.validator.addMethod("maxPercentage", function(value, element) {
            if ($('#type').val() === '1') {
                return parseFloat(value) <= 100;
            }
            return true;
        }, "The value must be maximum 100.");

        $.validator.addMethod("nonNegative", function(value, element) {
            return this.optional(element) || parseFloat(value) >= 0;
        }, "Please enter a non-negative number.");

        $.validator.addMethod("minDate", function(value, element) {
            var now = new Date();
            var myDate = new Date(value);
            return this.optional(element) || myDate > now;


        });

        $.validator.addMethod("noWhitespace", function(value, element) {
            return this.optional(element) || /\S/.test(value);
        }, "Please enter a valid input (whitespace-only is not allowed).");

        $.validator.addMethod("trimmedNumber", function(value, element) {

            var trimmedValue = value.trim();
            return this.optional(element) || !/\.\d*$/.test(trimmedValue);
        }, "Please enter a valid number.");






        $('#type').change(function() {
            var selectedType = $(this).val();

            if (selectedType == '1') {
                $('#max_val').show();
            } else {
                $('#max_val').hide();
            }
        });

        var selectedType = $("#type").val();

        if (selectedType == '1') {
            $('#max_val').show();
        } else {
            $('#max_val').hide();
        }

    });

    // keywords

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

        function hideSelected(value) {
            if (value && !value.selected) {
                return $('<span>' + value.text + '</span>');
            }
        }

        $(".courses").select2({
            theme: "classic",
            selectOnClose: false,
            allowClear: true,
            minimumResultsForSearch: -1,
            tags: true,
            tokenSeparators: [',', ' '],
            multiple: true,
            templateResult: hideSelected,

        });

        $(".select3").select2({
            selectOnClose: false,
            tags: true,
            tokenSeparators: [',', ' '],
            templateResult: hideSelected,
        });

    });



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