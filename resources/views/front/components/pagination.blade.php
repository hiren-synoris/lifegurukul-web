 <!-- pagination -->
 <div class="row">
    <div class="col-md-12">
        @if(!Route::is(['instructor-list','instructor-student-grid']))
        <ul class="pagination lms-page">
        @endif
        @if(Route::is(['instructor-list']))
        <ul class="pagination lms-page lms-pagination">
        @endif
        @if(Route::is(['instructor-list','instructor-student-grid']))
        <ul class="pagination lms-page mt-0">    
        @endif
            <li class="page-item prev">
                <a class="page-link" href="javascript:void(0);" tabindex="-1"><i class="fas fa-angle-left"></i></a>
            </li>
            <li class="page-item first-page active">
                <a class="page-link" href="javascript:void(0);">1</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);">2</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);">3</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);">4</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);">5</a>
            </li>
            <li class="page-item next">
                <a class="page-link" href="javascript:void(0);"><i class="fas fa-angle-right"></i></a>
            </li>
        </ul>
    </div>
</div>
<!-- /pagination -->
