<a id="hard-delete-btn" class="btn btn-danger px-2 py-1 mr-1 d-none" href="javascript:void(0)" onclick="myHardDeleteFunction()">
    <i class="fas fa-trash-alt"></i>
    <span class="pl-1">{{ isset($bulkHardDelBtnText) && !empty($bulkHardDelBtnText) ? $bulkHardDelBtnText : 'Bulk Hard Delete' }}</span>
</a>
