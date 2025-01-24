<a id="restore-btn" class="btn btn-success px-2 py-1 mr-1 d-none" href="javascript:void(0)" onclick="restore_all()">
    <span class="d-flex flex-start align-items-center">
        <i class="fas fa-trash-restore"></i>
        <span class="pl-1">{{ isset($restoreBtnText) && !empty($restoreBtnText) ? $restoreBtnText : 'Bulk Restore' }}</span>
    </span>
</a>
