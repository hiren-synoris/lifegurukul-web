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
    @can('add_country', $role)
        <button class="btn btn-success add_country" data-toggle="modal" data-target="#countryModal">Add Country</button>
    @endcan
@endsection
@section('content')
    <table id="tbl_countries" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Id</th>
                <th scope="col">Name</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>

    <div class="modal" id="countryModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_country"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="submit-form">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control name" placeholder="Enter name" name="name"
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
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'dataTableJS' => 1,
        'switch' => 1,
    ])
    <script>
        var table;
        $(document).ready(function() {

            var table = $('#tbl_countries').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('country') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'id',
                        name: 'id',
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

            $(".add_country").click(function() {
                    $("#submit-form")[0].reset();
                    $(".error").html("")
                    $(".id").val("")
                    $(".title_country").html("Add Country")
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
                    url = "{{ route('country.update') }}";
                } else {
                    url = "{{ route('country.store') }}";
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
                            $("#countryModal").modal("hide");
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

            $(document).on("click", ".edit_country", function() {

                $(".error").html("")
                $(".sub_btn").show()
                $(".loader_btn").hide()
                $(".title_country").html("Update Country")
                $.ajax({
                    url: "{{ route('country.edit') }}",
                    type: "get",
                    data: {
                        id: $(this).data("id")
                    },
                    success: function(data) {
                        $("#countryModal").modal("show");
                        $(".name").val(data.name)
                        $(".id").val(data.id)
                    }
                })
            })

            $(document).on("click", ".delete_country", function() {
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
                            url: "{{ route('country.delete') }}",
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
