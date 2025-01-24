@extends('admin.layouts.app')
@section('content')
    <h3>Packages</h3>
    <div class="row justify-content-end mb-3">
        <!-- Contain Bulk Add functionalities-->
        <div style="display: none;" id="blk_add_frm">
            @includeIf('admin.layouts.partials.actions.bulk-add-data',[
                'bulkAddURL' => url('backoffice/packages/'.$courseData->id.'/package/bulk_add')
            ])
        </div>
        <div style="display: none;" id="blk_del_frm">
            @includeIf('admin.layouts.partials.actions.bulk-delete-data',[
                'bulkDelURL' => url('backoffice/packages/'.$courseData->id.'/package/bulk_delete')
            ])
        </div>
        <!-- Bulk Add Button -->
        @includeIf('admin.layouts.partials.buttons.bulk-add')

        @includeIf('admin.layouts.partials.buttons.bulk-delete')

        {{-- <select class="form-control w-25 mr-4">
            <option value="">Added Courses</option>
            <option value="1">Added Courses</option>
            <option value="1">Without Added Courses</option>
        </select> --}}

        <button class="btn btn-success mr-2 search">Submit</button>
        <button class="btn btn-success">Clear</button>
    </div>
    <br>
    <table id="tbl_course_packages" class="table table-bordered table-hove w-100 only_active mt-5">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th>
                <th scope="col">Thumbnail</th>
                <th scope="col">Title</th>
                <th scope="col">Instructor</th>
                {{-- <th scope="col">Category</th> --}}
                <th scope="col">Status</th>
                <th scope="col">Created At</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>

    <!-- Bulk Add Modal -->
<div class="modal fade" id="bulk_add_conf" data-backdrop="static" tabindex="-1" aria-labelledby="add_conf_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_conf_title">Add Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to add ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
                <a href="javascript:void(0)" id="bluk_add_conf_yes" type="button" class="btn btn-primary">Yes</a>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'dataTableJS' => 1,
        'customScript' => 1
    ])

    <script>
        var table;
        $(document).ready(function() {
            table = $('#tbl_course_packages').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('packages.package.list', ['id' => $courseData->id]) }}",
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
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="children_checkbox" name="checkbox_index[' +
                                data + ']" data-id=' + data + ' style="cursor: pointer;"/>';
                        },
                    },
                    {
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'instructor_name',
                        name: 'instructor_name'
                    },
                    // {
                    //     data: 'course_category',
                    //     name: 'course_category'
                    // },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [],
            });

            $(document).on("click", ".children_checkbox", function() {
                $("#main_checkbox").prop('checked', false);
                if ($(this).is(":checked")) {
                    $(this).prop('checked', true);
                    let data_id = $(this).data('id');
                    $("#bulk_add_frm").append('<input name="bd[' + data_id + ']"  />');
                    $("#bulk_delete_frm").append('<input name="bd[' + data_id + ']"  />');
                } else {
                    $(this).prop('checked', false);
                    let data_id = $(this).data('id');
                    $('input[name="bd[' + data_id + ']"]').remove();
                }
            });

            $(document).on("click", "#main_checkbox", function() {
                // $(".children_checkbox").prop('checked', !$(".children_checkbox").prop("checked"));
                if ($(this).is(":checked")) {
                    $(".children_checkbox").prop('checked', true);
                    $(".children_checkbox").map(function(key, value) {
                        let data_id = $(value).data('id');
                        if(data_id !== undefined){
                            $("#bulk_add_frm").append('<input name="bd[' + data_id + ']"  />');
                            $("#bulk_delete_frm").append('<input name="bd[' + data_id + ']"  />');
                        }
                    });
                } else {
                    $(".children_checkbox").prop('checked', false);
                    $("#bulk_add_frm").find('input').not(":first").remove();
                    if(data_id !== undefined){
                        $("#bulk_delete_frm").append('<input name="bd[' + data_id + ']"  />');
                    }
                }
            });
        });
    /*****Bulk add from here*******/
    function myFunctionForAdd() {
            if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
                var url = $('#bulk_add_frm').attr('action');
                bulk_add_confirmation(url);
            }
            return false;
        }
    function bulk_add_confirmation(path) {
            $("#bulk_add_frm").attr('action', path);
            $("#bulk_add_conf").modal('show');
        }
    $(document).on("click", "#bluk_add_conf_yes", function() {
            $("#bulk_add_frm").submit();
        });
    /*****Bulk add End from here*******/

    function myFunction() {
            if ($("#main_checkbox").is(":checked") || $(".children_checkbox").filter(':checked').length > 0) {
                var url = $('#bd_frm').attr('action');
                bulk_delete_confirmation(url);
            }
            return false;
        }


    $(".search").click(function(){
        alert()
    })


    </script>


@endsection
