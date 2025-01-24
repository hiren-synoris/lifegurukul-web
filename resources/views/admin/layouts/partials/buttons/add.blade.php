<a href="{{ isset($addUrl) && !empty($addUrl) ? $addUrl : 'javascript:void(0)' }}" class="btn btn-success px-2 py-1 mr-1" title="{{ isset($addTitleText) && !empty($addTitleText) ? $addTitleText : 'Add New' }}">
    <i class="fas fa-plus"></i>
    <span class="pl-1">{{ isset($addBtnText) && !empty($addBtnText) ? $addBtnText : 'Add New' }}</span>
</a>