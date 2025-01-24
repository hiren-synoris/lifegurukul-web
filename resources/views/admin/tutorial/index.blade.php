@extends('admin.layouts.app')
@section('right-section')
    <?php
    $tutorial = new App\Models\Tutorial();
    ?>

    @can('add_tutorial')
        <button class="btn btn-success add_tutorial" data-toggle="modal" data-target="#tutorialModal">Create tutorial</button>
    @endcan

    @if (Auth::user()->hasRole('admin'))
        <!-- Bulk delete Button -->
        {{-- @can('delete_tutorial', $tutorial)
            @includeIf('admin.layouts.partials.buttons.bulk-delete')
        @endcan

        <!-- Restore Button -->
        @can('restore_tutorial', $tutorial)
            <!-- Bulk Hard delete Button -->
            @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
        @endcan --}}
    @endif
@endsection
@section('content')
    <div style="display: none;" id="blk_del_frm">
        @includeIf('admin.layouts.partials.actions.bulk-delete-data', [
            'bulkDelURL' => url('backoffice/blogs/bulk_del'),
        ])
        @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data', [
            'bulkHardDelURL' => url('backoffice/blogs/bulk_hard_del'),
        ])
        @includeIf('admin.layouts.partials.actions.restore-all-data', [
            'restoreAllURL' => url('backoffice/blogs/restore_all'),
        ])
    </div>
    <table id="tbl_tutorial" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                {{-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> --}}
                <th scope="col">Title</th>
                <th scope="col">Description</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
    <div class="modal" id="tutorialModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_tutrial"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="submit-form">
                        @csrf
                        <div class="form-group">
                            <label for="titie">Title<span class="text-danger">*</span></label>
                            <input type="titie" class="form-control title" placeholder="Enter titie" name="title"
                                id="titie">
                            <span class="error_title text-danger error"></span>
                        </div>

                        <div class="form-group">
                            <label for=description>Description</label>
                            <textarea class="form-control" id="description" placeholder="Enter description" name="description">
                            </textarea>
                        </div>

                        {{-- <div class="form-group">
                            <label for="">YouTube Link:</label>
                            <input type="file" accept="video/mp4,video/x-m4v,video/*" class="form-control" id="link"
                                name="link">
                            <span class="error_link text-danger error"></span>
                            <div class="video" style="display: none">
                                <video id="v1" width="240" height="240" controls="controls">

                                </video>
                            </div> --}}
                        <div class="form-group">
                            <label for="">YouTube URL<span class="text-danger">*</span></label>
                            <input type="text" class="form-control link" value="" name="link" id="link"
                                placeholder="link">
                            <span class="error_link text-danger error"></span>
                        </div>
                        <input type="hidden" name="id" class="id">
                        <button type="submit" class="btn btn-primary sub_btn">Submit</button>
                        <button class="btn btn-primary loader_btn" disabled style="display: none">
                            <span class="spinner-border spinner-border-sm"></span>
                            Loading..
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
    ])
    <script>
        $(function() {


            var table = $('#tbl_tutorial').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tutorial.index') }}",
                columns: [{
                        data: 'title',
                        name: 'title',
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width:"17%"
                    },
                ],
                "order": []
            });



            $(document).ready(function(e) {

                $(".add_tutorial").click(function() {
                    $("#submit-form")[0].reset();
                    $(".error").html("")
                    $(".video").hide();
                    $(".id").val("")
                    $(".title_tutrial").html("Add tutorial")
                    $(".sub_btn").show()
                    $(".loader_btn").hide()
                })


                $("#submit-form").on('submit', (function(e) {
                    e.preventDefault();
                    $(".error").html("")
                    $(".loader_btn").show()
                    $(".sub_btn").hide()
                    var url = "";
                    if ($(".id").val()) {
                        url = "{{ route('tutorial.update') }}";
                    } else {
                        url = "{{ route('tutorial.store') }}";
                    }

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(data) {
                            if (data.status == "1") {
                                $("#tutorialModal").modal("hide");
                                table.ajax.reload();
                                // Swal.fire(data.msg)
                                Swal.fire(
                                    'Success',
                                    data.msg,
                                    'success'
                                )
                            } else {
                                $(".loader_btn").hide()
                                $(".sub_btn").show()
                                $.each(data.error, function(k, v) {
                                    $(".error_" + k).html(v)
                                })
                            }

                        },
                    });
                }));

            });

            

            $(document).on("click", ".edit_tutorial", function() {
                
                $(".error").html("")
                // $("#v1").attr('src','')
                $(".sub_btn").show()
                $(".loader_btn").hide()
                $(".title_tutrial").html("Update tutorial")
                $.ajax({
                    url: "{{ route('tutorial.edit') }}",
                    type: "get",
                    data: {
                        id: $(this).data("id")
                    },
                    success: function(data) {
                        $("#tutorialModal").modal("show");
                        $(".title").val(data.title)
                        $(".link").val(data.link)
                        $(".id").val(data.id)
                        $("#description").val(data.description)
                        // $(".video").show();

                        // var storage = "{{ url('storage/tutorial/') }}"
                        // $("#v1").attr('src',storage + "/" + data.link)
                        // $("#v1").html('<source src=' + storage + "/" + data.link +
                        //     ' type="video/mp4"></source>');
                    }
                })
            })
            $(document).on("click", ".delete_tutorial", function() {
                $(".error").html("")


                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('tutorial.delete') }}",
                            type: "get",
                            data: {
                                id: $(this).data("id")
                            },
                            success: function(data) {
                                table.ajax.reload();
                            }
                        })
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        )
                    }
                })

            })
        });
    </script>

    <script type="text/javascript">
        var config = {
            phone: "919664957351",
            call: "Message Us",
            position: "ww-right",
            size: "ww-normal",
            text: "",
            type: "ww-standard",
            brand: "",
            subtitle: "",
            welcome: "Hi, I have a query regarding your learning community."
        };
        var proto = document.location.protocol,
            host = "cloudfront.net",
            url = proto + "//d3kzab8jj16n2f." + host;
        // alert(url)
        var s = document.createElement("script");
        s.type = "text/javascript";
        s.async = true;
        s.src = url + "/v2/main.js";

        s.onload = function() {
            tmWidgetInit(config)
        };
        var x = document.getElementsByTagName("script")[0];
        x.parentNode.insertBefore(s, x);
    </script>
@endsection
