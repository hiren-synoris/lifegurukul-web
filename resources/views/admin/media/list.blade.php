@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">

    @includeIf('admin.layouts.partials.styles.style', [
        'dropzoneCSS' => 1,
        'select2CSS' => 1,
    ])
@endsection

<?php
$media = new App\Models\Media();
?>
@section('content')
    @if (Session::has('msg'))
        <h2 class="succes-msg text text-success w-50">{{ Session::get('msg') }}</h2>
    @endif
    <h5 class="text-warning">Do not close or refresh tab while file uploading. Maximum 5 files allowed.</h5>

    <!-- Dropzone form element -->
    <form action="{{ route('media.store') }}" method="post" enctype="multipart/form-data" id="image-upload"
        class="dropzone mb-3 border">
        @csrf
    </form>

    <!-- Contain Bulk delete abd restore all functionalities-->
    @if (Auth::user()->hasRole('admin'))
        <div style="display: none;" id="blk_del_frm">
            @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
                'bulkDelURL' => url('backoffice/media/bulk_del'),
            ])
            @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
                'bulkHardDelURL' => url('backoffice/media/bulk_hard_del'),
            ])
            @includeIf('admin.layouts.partials.actions.restore-all-data', [
                'restoreAllURL' => url('backoffice/media/restore_all'),
            ])
        </div>
    @endif
    <div class="post-search-panel">
        <div class="asset-filter d-flex">
            <div class="col-1">
                <div class="form-group">
                    <label>Filter</label>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <select id="courseFilter" class="course-filter mb-2 form-control select3">
                        <option value="all">All Course</option>
                        @if (isset($course))
                            @foreach ($course as $key => $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <select id="assetTypes" class="asset-type-filter mb-2 form-control">
                        <option value="all">All Asset Types</option>
                        @if (isset($assetTypes))
                            @foreach ($assetTypes as $key => $val)
                                <option value="{{ $val['value'] }}">{{ $val['text'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    <input type="text" class="form-control float-right" name="created_date" id="created_date"
                        placeholder="Select Date">
                </div>
            </div>
            <div class="col-1">
                <div class="form-group">
                    <a href="" class="btn btn-primary">Clear</a>
                </div>
            </div>
            @can('media_report')
                <div class="col-2">
                    <div class="form-group">
                        <button class='btn btn-primary text-white export_media'>Export</button>
                    </div>
                </div>
            @endcan
            <div class="media_right_btn d-flex flex-end justify-content-end align-items-center ml-auto">
                @can('restore_media')
                    <!-- Show Deleted Button -->
                    @if (Auth::user()->hasRole('admin'))
                        {{-- @includeIf('admin.layouts.partials.buttons.show-deleted') --}}
                    @endcan

                    <!-- Bulk delete Button -->
                    @can('delete_media', $media)
                        @includeIf('admin.layouts.partials.buttons.bulk-delete')
                    @endcan

                    <!-- Restore Button -->
                    @can('restore_media', $media)
                        @includeIf('admin.layouts.partials.buttons.restore')
                        <!-- Bulk Hard delete Button -->
                        @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <table id="tbl_media" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">VdoCipher Id</th>
                <th scope="col">Image</th>
                <th scope="col">File Name</th>
                <th scope="col">Created At</th>
                <th scope="col">Course Attached</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>


    <div class="modal fade" id="media_delete" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger delete_msg" data-dismiss="modal">No</button>
                    <a href="javascript:void(0)" id="" data-msg="1" type="button"
                        class="btn btn-primary confirm">Yes</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
        'select2' => 1,
        'dropzone' => 1,
        'dateRangePicker' => 1,
    ])

    <script>
        $(document).ready(function() {
            $('.select3').select2();
        })
        //Dropzone JS code
        Dropzone.autoDiscover = false;
        var dropzone = new Dropzone('#image-upload', {
            thumbnailWidth: 200,
            // maxFilesize: 1,
            maxFiles: 5,
            parallelUploads: 1,
            maxFilesize: 10240,
            chunking: true,
            forceChunking: true,
            chunkSize: 100000000,
            addRemoveLinks: true,

            init: function() {
                this.on("addedfile", function(event) {
                    while (this.files.length > this.options.maxFiles) {
                        this.removeFile(this.files[0]);
                    }
                });
                this.on('success', function(file, response) {
                    if (this.getQueuedFiles().length == 0 && this.getUploadingFiles().length == 0) {
                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                        }).then(function() {
                            if (response.status == 1) {
                                location.reload();
                            }
                        });
                    }
                });
                this.on('processing', function(file) {
                    this.options.url = "{{ route('media.store') }}?dzuuid=" + file.upload.uuid;
                });
                this.on('queuecomplete', function() {
                    console.log('All chunks uploaded successfully.');
                });
            }
        });
        //!...Dropzone JS code end

        var table;
        $(document).ready(function() {
            $('#created_date').daterangepicker({
                //timePicker: true,

                //startDate: moment().startOf('month'),
                //endDate: moment(),
                locale: {
                    format: 'DD/MM/YYYY'
                }

            });
            $("#created_date").val('');

            table = $('#tbl_media').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                // ajax: "{{ url('backoffice/get-media') }}",
                order: [
                    [4, 'desc']
                ],
                ajax: {
                    "url": "{{ url('backoffice/get-media') }}",
                    "data": function(d) {
                        return $.extend({}, d, {
                            "assetTypes": $("#assetTypes").val().toLowerCase(),
                            "courseFilter": $("#courseFilter").val().toLowerCase(),
                            "created_date": $("#created_date").val()
                        });
                    }
                },
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                "drawCallback": function(settings) {
                    if ($("#main_checkbox").is(":checked")) {
                        $("#main_checkbox").trigger("click");
                        $("#main_checkbox").prop("checked", true);
                    } else {
                        $("#main_checkbox").prop("checked", false);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        // render: function (data, type, row) {
                        // // console.log(data,row.id);
                        // return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                        // data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        //  },
                    },
                    {
                        data: 'videoId',
                        name: 'videoId',

                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'file_name',
                        name: 'file_name',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'course',
                        name: 'course.course.title',
                        orderable: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                // "order": [],
            });

            // Redraw the table
            // table.draw();

            // Redraw the table based on the custom input
            $('#assetTypes').bind("keyup change", function() {
                table.draw();
            });
            // Redraw the table based on the custom input
            $('#courseFilter').bind("keyup change", function() {
                table.draw();
            });

            $(document).on("click", ".applyBtn", function() {
                table.draw();
            });

            $(document).on("click", ".export_media", function() {
                var dateRange = $("#created_date").val();
                if (!dateRange) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Date Range',
                        text: 'Please select Date Range for export report.'
                    });
                } else {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to export Media Report?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Export'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var assetTypes = $("#assetTypes").val().toLowerCase();
                            var courseFilter = $("#courseFilter").val().toLowerCase();
                            var created_date = $("#created_date").val();
                            var url = "{{ URL::to('backoffice/media-export') }}?" + $.param({
                                "assetTypes": assetTypes,
                                "courseFilter": courseFilter,
                                "created_date": created_date
                            })
                            window.location = url;
                        }
                    });
                }
            });

            // $(".delete_msg").click(function(){
            //     alert()
            // })
            $(document).on("click", ".delete_media", function() {


                $("#media_delete").modal("show");
                var media_id = $(this).attr("data-id")
                $(".confirm").click(function() {
                    $.ajax({
                        url: "{{ route('media_delete') }}",
                        type: "get",
                        data: {
                            media_id: media_id
                        },
                        success: function(data) {
                            table.ajax.reload();
                            $("#media_delete").modal("hide");
                            Swal.fire(
                                'success',
                                'Media deleted successfully',
                                'success'
                            )
                        }
                    })
                })

                // if($(".confirm").)
                // if($(".confirm").data("msg")=="1") {
                //     $.ajax({
                //         url:"{{ route('media_delete') }}",
                //         type:"get",
                //         data:{
                //             media_id :$(this).attr("data-id")
                //         },
                //         success:function(data){
                //         table.ajax.reload();
                //         Swal.fire(
                //             'success',
                //             'Media deleted successfully',
                //             'success'
                //             )
                //         }
                //     })

                //  }

            })


        });


        // $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
        //     $("#assetTypes option").prop("selected", false).trigger("change");
        // })

        // $('#swWeather').on('switchChange.bootstrapSwitch', function(event, state) {
        //     $("#main_checkbox").prop("checked", false);
        //     $(".children_checkbox").prop("checked", false);

        //     if (state == true) {
        //         table.clear().draw();
        //         table.destroy();
        //         table = $('#tbl_media').DataTable({
        //             processing: true,
        //             serverSide: true,
        //             responsive: true,
        //             ajax: "{{ url('backoffice/get-media') }}",
        //             columnDefs: [{
        //                 className: 'text-center',
        //                 targets: '_all'
        //             }],
        //             "drawCallback": function(settings) {
        //                 if ($("#main_checkbox").is(":checked")) {
        //                     $("#main_checkbox").trigger("click");
        //                     $("#main_checkbox").prop("checked", true);
        //                 } else {
        //                     $("#main_checkbox").prop("checked", false);
        //                 }
        //             },
        //             columns: [{
        //                     data: 'id',
        //                     name: 'id',
        //                     orderable: false,
        //                     searchable: false,
        //                     render: function(data, type, row) {
        //                         return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
        //                             data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
        //                     },
        //                 },
        //                 {
        //                     data: 'image',
        //                     name: 'image',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'file_name',
        //                     name: 'file_name'
        //                 },
        //                 {
        //                     data: 'created_at',
        //                     name: 'created_at'
        //                 },
        //                 {
        //                     data: 'course',
        //                     name: 'course',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'action',
        //                     name: 'action',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //             ],
        //             "order": [],
        //         });
        //         $("#image-upload").show();
        //         $('#tbl_media').removeClass('only_deleted');
        //         $('#tbl_media').addClass('only_active');
        //     } else {
        //         $("#image-upload").hide();

        //         table.destroy();

        //         table = $('#tbl_media').DataTable({
        //             processing: true,
        //             serverSide: true,
        //             responsive: true,
        //             // ajax: "{{ url('backoffice/get-media-deleted') }}",
        //             ajax: {
        //                 url: "{{ url('backoffice/get-media-deleted') }}",
        //                 data: function(d) {
        //                     d.assetTypes = $('#assetTypes').val()
        //                     d.courseFilter = $('#courseFilter').val()

        //                 }
        //             },
        //             columnDefs: [{
        //                 className: 'text-center',
        //                 targets: '_all'
        //             }],
        //             "drawCallback": function(settings) {
        //                 if ($("#main_checkbox").is(":checked")) {
        //                     $("#main_checkbox").trigger("click");
        //                     $("#main_checkbox").prop("checked", true);
        //                 } else {
        //                     $("#main_checkbox").prop("checked", false);
        //                 }
        //             },
        //             columns: [{
        //                     data: 'id',
        //                     name: 'id',
        //                     orderable: false,
        //                     searchable: false,
        //                     render: function(data, type, row) {
        //                         return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
        //                             data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
        //                     },
        //                 },
        //                 {
        //                     data: 'image',
        //                     name: 'image',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'file_name',
        //                     name: 'file_name'
        //                 },
        //                 {
        //                     data: 'date',
        //                     name: 'date',
        //                 },
        //                 {
        //                     data: 'course',
        //                     name: 'course',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //                 {
        //                     data: 'action',
        //                     name: 'action',
        //                     orderable: false,
        //                     searchable: false
        //                 },
        //             ],
        //             "order": [],
        //         });
        //         $('#tbl_media').removeClass('only_active');
        //         $('#tbl_media').addClass('only_deleted');
        //     }

        //     $("#assetTypes").change(function() {
        //         table.draw();
        //     });
        //     $("#courseFilter").change(function() {
        //         table.draw();
        //     });
        // });
    </script>
@endsection
