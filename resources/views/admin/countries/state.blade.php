@extends('admin.layouts.app')
@section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection
<style>
     .country_id .select2 .select2-container {
        width: 468px !important;
    }
</style>
@section('right-section')
    <?php
    $role = new App\Models\Countries();
    ?>
    @can('add_state', $role)
        <button class="btn btn-success add_state" data-toggle="modal" data-target="#stateModal">Add State</button>
    @endcan
@endsection
@section('content')
    <table id="tbl_state" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Id</th>
                <th scope="col">Country</th>
                <th scope="col">State</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>

    <div class="modal" id="stateModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_state"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="submit-form">
                        @csrf
                        <div class="form-group">
                            <label for="name">Country<span class="text-danger">*</span></label><br>
                            <select class="form-control country_id w-100"  data-width="100%" name="country_id">
                                @foreach ($country as $val)
                                    <option value="{{ $val->id }}"> {{ $val->name }}
                                    <option>
                                @endforeach
                            </select>
                            <span class="error_country_id text-danger error"></span>
                        </div>
                        <div class="form-group">
                            <label for="name">State<span class="text-danger">*</span></label>
                            <input type="text" class="form-control name" placeholder="Enter state" name="name"
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
        $(document).ready(function() {
            $('.country_id').select2({dropdownAutoWidth : true});

            var table = $('#tbl_state').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('state') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [{
                        data: 'id',
                        name: 'id',
                    },
                    {
                        data: 'country_id',
                        name: 'country_id',
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

            $(".add_state").click(function() {
                $("#submit-form")[0].reset();
                $(".error").html("")
                $(".id").val("")
                $(".title_state").html("Add State")
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
                    url = "{{ route('state.update') }}";
                } else {
                    url = "{{ route('state.store') }}";
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
                            $("#stateModal").modal("hide");
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

            $(document).on("click", ".edit_state", function() {

                $(".error").html("")
                $(".sub_btn").show()
                $(".loader_btn").hide()
                $(".title_state").html("Update State")
                $.ajax({
                    url: "{{ route('state.edit') }}",
                    type: "get",
                    data: {
                        id: $(this).data("id")
                    },
                    success: function(data) {
                        $("#stateModal").modal("show");
                        $(".name").val(data.name)
                        $(".country_id").val(data.country_id).trigger('change');
                        $(".id").val(data.id)
                    }
                })
            })

            $(document).on("click", ".delete_state", function() {
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
                            url: "{{ route('state.delete') }}",
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
