<a id="delete-btn" class="btn btn-danger px-2 py-1 mr-1 d-none" href="javascript:void(0)" onclick="bulk_delete()">
    <i class="fas fa-trash-alt"></i>
    <span class="pl-1">{{ isset($bulkDelBtnText) && !empty($bulkDelBtnText) ? $bulkDelBtnText : 'Bulk Delete' }}</span>
</a>
