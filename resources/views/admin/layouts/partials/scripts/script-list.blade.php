<script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.js') }}"></script>

@if(isset($dataTableJS) && $dataTableJS == 1)
<!-- Datatable JS -->
<script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
@endif

<!-- Include Axios for HTTP requests -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>


@if(isset($switch) && $switch == 1)
<!-- Bootstrap switch JS -->
<script src="{{ asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<script>
    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
</script>
@endif

@if(isset($dateTime) && $dateTime == 1)
<!-- InputMask -->
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
@endif

@if(isset($ckEditor) && $ckEditor == 1)
<!-- CKEditor js -->
<script src="{{ asset('admin/plugins/ckeditor/ckeditor.js') }}"></script>
@endif

@if(isset($summerNote) && $summerNote == 1)
<!-- Summernote -->
<script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css" rel="stylesheet">


<script>
    $('.summernote-editor').summernote({
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['insert', ['link', 'picture', 'video', 'table', 'hr']],
            ['view', ['fullscreen', 'codeview', 'undo', 'redo', 'help']],
        ],
        minHeight: 200,
    });
</script>
@endif

@if(isset($summerNoteDiscussion) && $summerNoteDiscussion == 1)
<!-- Summernote -->
<script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>

<script>
        $(document).ready(function() {
            // Initialize Summernote
            $('.summernote-editor').summernote({
                toolbar: [
                    ['font', ['bold', 'underline']],
                    ['insert', ['link']],
                ],
                minHeight: 200
            });
        });
    </script>
@endif

@if(isset($summerNoteSupportTicket) && $summerNoteSupportTicket == 1)
<!-- Summernote -->
<script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
    $('.summernote-editor').summernote({
        toolbar: [
            ['font', ['bold', 'underline']],
            ['para', ['ul', 'ol']],
            ['insert', ['picture']]
        ],
        minHeight: 200,
    });
</script>
@endif

@if(isset($codeMirror) && $codeMirror == 1)
<!-- Code Mirror -->
<script src="{{ asset('admin/plugins/codemirror/codemirror.js') }}"></script>
<script src="{{ asset('admin/plugins/codemirror/mode/css/css.js') }}"></script>
<script src="{{ asset('admin/plugins/codemirror/mode/xml/xml.js') }}"></script>
<script src="{{ asset('admin/plugins/codemirror/mode/htmlmixed/htmlmixed.js') }}"></script>
@endif

@if(isset($select2) && $select2 == 1)
<!-- Select 2 -->
<script src="{{ asset('admin/plugins/select2/js/select2.min.js') }}"></script>
@endif

@if(isset($dropzone) && $dropzone == 1)
<!-- Dropzone JS -->
<script src="{{ asset('admin/plugins/dropzone/min/dropzone.min.js') }}"></script>
@endif

{{-- @if(isset($customScript) && $customScript == 1) --}}
<!-- Custom Script file -->
<script src="{{ asset('admin/dist/js/custom.js') }}?var={{time()}}"></script>
{{-- @endif --}}


@if(isset($dashboard) && $dashboard == 1)
<!-- Custom Script file -->
{{-- <script src="{{ asset('admin/dist/js/pages/dashboard3.js') }}?var={{time()}}"></script> --}}
@endif

@if(isset($demo) && $demo == 1)
<!-- Custom Script file -->
<script src="{{ asset('admin/dist/js/demo.js') }}?var={{time()}}"></script>
@endif
@if(isset($chart) && $chart == 1)
<!-- Custom Script file -->
<script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}?var={{time()}}"></script>
@endif

@if(isset($multiSelect) && $multiSelect == 1)
<!-- Multi Select -->
<script src="{{ asset('admin/plugins/multi-select/js/multiple-select.min.js') }}"></script>
@endif

@if(isset($validateJS) && $validateJS == 1)
<!-- Custom Script file -->
<script src="{{ asset('admin/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('admin/dist/js/custom-validation-rules.js') }}"></script>
@endif

@if(isset($summerNote) && $summerNote == 1)
<!-- SummerNote JS -->
<script src='{{asset("admin/plugins/summernote/summernote-bs4.min.js")}}'></script>
@endif

@if(isset($ratingjs) && $ratingjs == 1)
<!-- Rating JS -->
<script src='{{asset("admin/dist/js/rating.js")}}'></script>
@endif

@if(isset($dateRangePicker) && $dateRangePicker == 1)
<!-- Date Range Picker JS -->
<script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
@endif
@if(isset($searchpanes) && $searchpanes == 1)
<!-- Date Range Picker JS -->
<script src="{{ asset('admin/plugins/datatables-searchpanes/js/searchPanes.bootstrap4.min.js') }}"></script>
@endif
