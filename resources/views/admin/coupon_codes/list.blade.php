    @extends('admin.layouts.app')
    @section('styles')
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    @endsection
    @section('right-section')
    <?php
    $coupon = new App\Models\Coupon;
    ?>
    @can('add_coupon', $coupon)
    <a href="{{ url('backoffice/coupons/create') }}" class="btn btn-success px-2 py-1 mr-1" title="Add Coupon"><i class="fas fa-plus"></i><span class="pl-1">Add New</span></a>

    @endcan
    {{-- @if(Auth::user()->hasRole('admin')) --}}
    {{-- @can('restore_coupon', $coupon)
<div class="mr-1">
    <input type="checkbox" name="my-checkbox" id="swWeather" data-size="normal" data-handle-width="120"
        data-label-width="1" data-on-text="<i class='fas fa-trash-restore'></i> Show Deleted"
        data-off-text="<i class='fas fa-trash-alt'></i> Hide Deleted" checked data-bootstrap-switch>
</div>
@endcan --}}
    {{-- @can('delete_coupon', $coupon) --}}
    {{-- <a id="delete-btn" class="btn btn-danger px-2 py-1 mr-1" href="javascript:void(0)" onclick="myFunction()">
    <i class="fas fa-trash-alt"></i>
    <span class="pl-1">Bulk Delete</span>
</a> --}}
    {{-- @includeIf('admin.layouts.partials.buttons.bulk-delete') --}}
    {{-- @endcan --}}
    {{-- @can('restore_coupon', $coupon)
<a id="restore-btn" class="btn btn-success px-2 py-1 mr-1" href="javascript:void(0)" onclick="restore_all()"
    style="display: none;">
    <span class="d-flex flex-start align-items-center">
        <i class="fas fa-trash-restore"></i>
        <span class="pl-1">Restore</span>
    </span>
</a>
@endif --}}
    <!-- Bulk Hard delete Button -->
    {{-- @includeIf('admin.layouts.partials.buttons.bulk-hard-delete')
@endcan --}}
    @endsection
    @section('content')
    {{-- <div style="display: none;" id="blk_del_frm">
    <form action="{{ url('backoffice/coupons/bulk_del') }}" id="bd_frm" method="POST">
    @csrf
    </form>
    @includeIf('admin.layouts.partials.actions.bulk-hard-delete-data',[
    'bulkHardDelURL' => url('backoffice/coupons/bulk_hard_del')
    ])
    <form action="{{ url('backoffice/coupons/restore_all') }}" id="restore_frm" method="POST">
        @csrf
    </form>
    </div> --}}
    <table id="tbl_coupon_codes" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <!-- <th scope="col"><input type="checkbox" id="main_checkbox" style="cursor: pointer;" /></th> -->
                <th scope="col">Code</th>
                <th scope="col">Type</th>
                <th scope="col">Value</th>
                <th scope="col">Courses</th>
                <th scope="col">Max amount</th>
                <th scope="col">Expiry Date</th>
                <th scope="col">Created at</th>
                {{-- <th scope="col">Status</th> --}}
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
    <!-- Modal -->
    <div class="modal fade" id="expiryDateModal" tabindex="-1" role="dialog" aria-labelledby="expiryDateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="expiryDateModalLabel">Update Expiry Date</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateExpiryDateForm" id="coupon_form">
                    @csrf
                        <div class="form-group">
                            <label for="exp_date">Expiry Date</label><span style="color: red">*</span>
                            <input type="text" class="form-control exp_date" readonly id="exp_date" name="exp_date">
                            <input type="hidden" id="coupon_id" name="coupon_id">
                            <div class="text text-danger" id="expiry_date_error" style="display: none;">Please enter an expiration date.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveExpiryDate">Save</button>
                </div>
            </div>
        </div>
    </div>

    @endsection
    @section('scripts')
    @includeIf('admin.layouts.partials.scripts.script-list',[
    'dataTableJS' => 1,
    'switch' => 1
    ])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" rel="stylesheet"
        type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
    <script>
        function changeStatus(couponId, checked) {
            // alert('sss');
            $.ajax({
                type: "PUT",
                url: "{{ url('backoffice/coupons') }}/" + couponId + "/change-status",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    status: checked ? 1 : 0
                },
                success: function(response) {
                    // Handle success response
                    console.log(response.status);
                    if (response.status === 'success') { // Check if status is success
                        Swal.fire({
                            icon: response.status,
                            title: response.title,
                            text: response.msg
                        });
                    }
                    table.ajax.reload();

                    // Optionally update UI to reflect the changed status
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error(xhr.responseText);
                    // Reset the switch state in case of error
                    $("#statusToggle" + couponId).prop('checked', !checked);
                }
            });
        }

        var table;
        $(document).ready(function() {

            table = $('#tbl_coupon_codes').DataTable({
                processing: true,
                // serverSide: true,
                responsive: true,
                ajax: "{{ url('backoffice/get_coupons') }}",
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],

                columns: [

                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'value',
                        name: 'value'
                        // createdCell: function (td, cellData, rowData, row, col) {
                        //     $(td).attr('style', '  text-overflow: ellipsis;overflow: hidden;white-space: nowrap;max-width: 15px;}');
                        // }
                    },
                    {
                        data: 'courses',
                        name: 'courses'
                    },
                    {
                        data: 'max_amount',
                        name: 'max_amount'
                    },
                    {
                        data: 'expiry_date',
                        name: 'expiry_date',
                        render: function(data, type, row) {
                            return `<span>${data}</span> <i class="fas fa-pencil-alt edit-expiry-date" data-id="${row.id}" style="cursor:pointer; margin-left: 10px;"></i>`;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    // {
                    //     data: 'status',
                    //     name: 'status'
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "order": [
                    // [4, "desc"]
                ]
            });


            $('#saveExpiryDate').on('click', function() {

                var couponId = $('#coupon_id').val();
                var newDate = $('#exp_date').val();


                // Validate the date
                if (!newDate) {
                    $('#expiry_date_error').show();
                    return;
                }

                $('#expiry_date_error').hide();


                $.ajax({
                    url: "{{ route('update.coupon.expiry') }}",
                    type: "POST",
                    data: {
                        id: couponId,
                    expiry_date: newDate,
                    _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                            if (data.status == "1") {

                                console.log(data.status);

                                $('#expiryDateModal').modal('hide');
                                table.ajax.reload();
                                // Swal.fire(data.msg)
                                Swal.fire(
                                    'Success',
                                    data.msg,
                                    'success'
                                )
                            }

                        }
                })

            });
            $('#tbl_coupon_codes').on('click', '.edit-expiry-date', function() {

                var couponId = $(this).data('id');
                var currentDate = $(this).siblings('span').text();

                // Set current date and coupon ID in the modal
                $('#exp_date').val(currentDate);
                $('#coupon_id').val(couponId);

                // Show the modal
                $('#expiryDateModal').modal('show');
            });

            var todayDate = new Date();

            $('.exp_date').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                startDate: todayDate
            });

        });
    </script>

    @endsection
