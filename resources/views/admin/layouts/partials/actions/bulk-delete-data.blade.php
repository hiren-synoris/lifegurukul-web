<form action="{{ isset($bulkDelURL) && !empty($bulkDelURL) ? $bulkDelURL : '' }}" id="bd_frm" method="POST">
    @csrf
</form>
