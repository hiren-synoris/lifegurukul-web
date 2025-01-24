
<div class="modal-dialog modal-dialog-centered" style="max-width:640px!important;">
        <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Logged Devices</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @if(isset($devices) && !empty($devices))
                        @forelse ($devices as $device)

                        <div class="col-md-6 info-box_info-list" id="deviceUID_{{ $device->id }}">
                            <div class="info-box">
                            <div class="info-box_info">

<span class="info-box-icon bg-info">
    @if($device->device_type == 3)
        <i class="fa fa-desktop"></i>
    @elseif($device->device_type == 1)
        <i class="fa fa-mobile"></i>
    @elseif($device->device_type == 2)
        <i class="fa fa-mobile"></i>
    @endif

</span>
<div class="info-box-content">
  <span class="info-box-text">
    {{ $device->device_name??''}}
  </span>
  <span class="info-box-number">{{$device->device_id??''}}</span>
</div>

<div class="close-card mt-2">
    <a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=confirmDeleteNoReload('{{route('logged-device.delete', ['id' => $device->id])}}')> <i class="fas fa-trash"></i>
    </a><br>
    {{-- <a class="mx-1 text-danger block_device" title="Delete" data-id="{{ $device->id }}" type="button" href="javascript:void(0)"><i class="fa fa-ban" aria-hidden="true"></i>
    </a> --}}
   
</div>
 
</div>
                            </div>
                            @if($device->device_type != 3)
                            <div class="custome_button">
                              <div class="custom-control custom-switch">
                                        <input type="checkbox" data-id="{{ $device->id }}"  data-lerner_id="{{ $device->learner_id }}" {{ $device->block_device==1 ? "checked" : "" }} class="custom-control-input block_device" name="home_status" id="toggle_Switch_{{ $device->id }}" >
                                        Block
                                        
                                        <label class="custom-control-label button-btn" for="toggle_Switch_{{ $device->id }}"></label>
                                        
                                        Active
                                    </div>
                              </div>
                              @endif
                              
                        </div>
                        @empty
                        <div class="col-md-12">
                            No Device Logged Yet
                        </div>
                        @endforelse


                        @endif
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
        </div>
    </div>
    @includeIf('admin.layouts.partials.scripts.script-list', [
        'validateJS' => 1
    ])
<script>
// $(document).ready(function() {

//     $(document).on("change",".block_device",function(){
//         $.ajax({
//             type:"get",
//             url:'{{ route("logged_device_block") }}',
//             data:{
//                 id:$(this).attr("data-id"),
//                 lerner_id:$(this).attr("data-lerner_id"),
//                 status:$(this).is(":checked")

//             },
//             success:function(data){
//                 Swal.fire({
//                 title: "Success",
//                 text:  data.content,
//                 icon: "success"
//                 });
//             }
//         })
//     })

// })
// </script>
