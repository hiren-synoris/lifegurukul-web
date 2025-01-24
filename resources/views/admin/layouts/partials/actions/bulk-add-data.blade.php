<form action="{{ isset($bulkAddURL) && !empty($bulkAddURL) ? $bulkAddURL : '' }}" id="bulk_add_frm" method="POST">
    @csrf
</form>
