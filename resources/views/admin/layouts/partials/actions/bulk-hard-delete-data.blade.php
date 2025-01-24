<form action="{{ isset($bulkHardDelURL) && !empty($bulkHardDelURL) ? $bulkHardDelURL : '' }}" id="bd_hard_frm" method="POST">
    @csrf
</form>
