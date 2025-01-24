<form action="{{ isset($restoreAllURL) && !empty($restoreAllURL) ? $restoreAllURL : '' }}" id="restore_frm" method="POST">
    @csrf
</form>