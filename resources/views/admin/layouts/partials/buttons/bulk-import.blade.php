@php
 $button = '';
 $button .= '<a  id="bulk-import-btn" class="btn btn-warning px-2 py-1 mr-1 float-right" href="javascript:void(0)"onclick=import_learner("' . $importUrl . '")><i class="fas fa-plus"></i><span class="pl-1"> Bulk Enroll Learners</span></a>';
 echo $button;
@endphp

