@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection
@section('right-section')
    <?php
    $role = new App\Models\Countries();
    ?>
    @can('add_city', $role)
        <button class="btn btn-success add_city" data-toggle="modal" data-target="#cityModal">Add City</button>
    @endcan
@endsection
@section('content')
    <table id="tbl_city" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Id</th>
                <th scope="col">State</th>
                <th scope="col">City</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>

    <div class="modal" id="cityModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_city"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="submit-form">
                        @csrf
                        <div class="form-group">
                            <label for="name">State<span class="text-danger">*</span></label><br>
                            <input type="text" class="form-control w-100 search_state autocompleteInput sl-label"
                                   id="state-label" name="state" placeholder="Search State">
                            <input type="hidden" class="form-control w-100 search_state state_value sl-id"
                                   id="state-id" name="state_id">
                                   <span class="error_state_id error_state text-danger error"></span>
                        </div>

                        <div class="form-group">
                            <label for="name">City<span class="text-danger">*</span></label>
                            <input type="text" class="form-control name" placeholder="Enter City" name="name"
                                id="name">
                            <span class="error_name text-danger error"></span>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
    ])
    <script>
        var table;

        // $('.state_id').select2();
        $(document).ready(function() {


            $('#cityModal').on('shown.bs.modal', function() {
                $(".autocompleteInput").autocomplete({
                    source: '/backoffice/search-state',
                    focus: function(event, ui) {
                        $(".sl-label").val(ui.item.label);
                        return false;
                    },
                    select: function(event, ui) {
                        $(".sl-label").val(ui.item.label);
                        $(".sl-id").val(ui.item.value);
                        return false;
                    }
                }).autocomplete("widget").css({
                    "z-index": 1051
                });
                $(".autocompleteInput").focus();
            });


            $('.state_id').select2();

            var table = $('#tbl_city').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('city') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },

                    {
                        data: 'state_id',
                        name: 'state_id',
                    },
                    {
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: "17%"
                    },
                ],
                "order": []
            });

            $(".add_city").click(function() {
                $("#submit-form")[0].reset();
                $(".error").html("")
                $(".id").val("")
                $(".title_city").html("Add City")
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
                    url = "{{ route('city.update') }}";
                } else {
                    url = "{{ route('city.store') }}";
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
                            $("#cityModal").modal("hide");
                            table.ajax.reload();
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

            $(document).on("click", ".edit_city", function() {

                $(".error").html("")
                $(".sub_btn").show()
                $(".loader_btn").hide()
                $(".title_city").html("Update City")
                $.ajax({
                    url: "{{ route('city.edit') }}",
                    type: "get",
                    data: {
                        id: $(this).data("id")
                    },
                    success: function(data) {
                        $("#cityModal").modal("show");
                        $(".name").val(data.data.name)
                        $(".search_state").val(data.state.name)
                        $(".state_value").val(data.state.id)
                        $(".id").val(data.data.id)
                    }
                })
            })

            $(document).on("click", ".delete_city", function() {
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
                            url: "{{ route('city.delete') }}",
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
                            'Your data has been deleted.',
                            'success'
                        )
                    }
                })

            })


        });
    </script>
@endsection
