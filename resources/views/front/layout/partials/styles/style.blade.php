<!-- Summernote CSS -->
<link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
<!-- Tempusdominus Bootstrap 4 CSS -->
<link rel="stylesheet" href="{{ asset('admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">

@if(isset($dataTableCSS) && $dataTableCSS == 1)
    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endif

@if(isset($switchCSS) && $switchCSS == 1)
    <!-- Bootstrap switch CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endif

@if(isset($summerNoteCSS) && $summerNoteCSS == 1)
    <!-- Summernote CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
@endif

@if(isset($codeMirrorCSS) && $codeMirrorCSS == 1)
    <!-- Code Mirror CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/codemirror/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/codemirror/theme/monokai.css') }}">
@endif

@if(isset($select2CSS) && $select2CSS == 1)
    <!-- select2 CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
@endif

@if(isset($dropzoneCSS) && $dropzoneCSS == 1)
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/dropzone/min/dropzone.min.css') }}">
@endif

@if(isset($multiSelectCSS) && $multiSelectCSS == 1)
    <!-- Multi select CSS -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/multi-select/css/multiple-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/multi-select/css/multiple-select-bootstrap.min.css') }}">
@endif