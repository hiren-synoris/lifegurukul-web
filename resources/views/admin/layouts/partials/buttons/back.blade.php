@if(isset($back) && !empty($back))
    <a class="btn btn-warning px-2 py-1" href="{{ $back ?? 'javascript:void(0)' }}"><i class="fas fa-arrow-circle-left pr-2"></i> Back</a>
@endif