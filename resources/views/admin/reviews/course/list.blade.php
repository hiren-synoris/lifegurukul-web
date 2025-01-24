@extends('admin.layouts.app')
@section('styles')
@includeIf('admin.layouts.partials.styles.style', [
    'select2CSS' => 1,    
])
    <style>
        .text-truncate{
            max-width: 250px;
        }
    </style>
@endsection
@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h5>Filter</h5>

        <form action="javascript:void(0)" method="get">
            <div class="row">
                <div class="col-5">
                    {{-- <select name="users" id="users" class="custom-select" onchange="$(this)">
                        <option value="">Select User</option>

                        @foreach($users as $key => $value)
                        <option value="{{$value->id}}">{{$value->mobile}}
                            @if(!empty($value->name))
                            ({{ $value->name }})

                            @endif

                        </option>
                        @endforeach
                    </select> --}}
                    <input type="text"
                    class="form-control w-100 autocompleteInput sl-label"
                    id="users" name="users" placeholder="Search Learner" data-id="">
                </div>
                <div class="col-5">
                    {{-- <select name="courses" id="courses" class="custom-select select3">
                        <option value="">Select Course</option>
                        @foreach($courses as $key => $value)
                        <option value="{{$value->id}}">{{$value->title}}</option>
                        @endforeach
                    </select> --}}
                    <input type="text"
                    class="form-control w-100 autocompleteInputCourse sl-label-course"
                    id="courses" name="courses" placeholder="Search course" data-id="">
                </div>
                <div class="col-2"><button class="btn btn-primary" type="button" id="btn_submit">Search</button>
                <a href="" class="btn btn-primary">Clear</a>
                </div>
            </div>
        </form>
        <form action="{{url('backoffice/course_reviews')}}" style="display: none;" id="frm"></form>
    </div>
</div>
    <table id="tbl_course_reviews" class="table table-bordered table-hove w-100 only_active">
        <thead class="thead-light">
            <tr class="text-center">
                <th scope="col">Course</th>
                <th scope="col">Learner</th>
                <th scope="col">Rating</th>
                <th scope="col">Comment</th>
                <th scope="col">Created Date</th>
                <th scope="col">Approved Status</th>
                <th scope="col">Home Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
    </table>
    <div class="modal" id="courseReviewModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title title_course_review"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form method="POST" id="edit_review_frm">
                        @csrf
                        
                        <div class="form-group">
                            <label for=comment>Comment<span class="text-danger">*</span></label>
                            <textarea class="form-control" id="comment" placeholder="Enter Comment" name="comment" pattern=["/^\s+$/g"] required>
                            </textarea>
                            <span class="error_title text-danger error"></span>
                        </div>
                        
                        <div class="form-group">
                        <label for="rating">Rating<span class="text-danger">*</span></label>
                        <div class="rating"></div>
                        <input type="hidden" id="rating_val" name="rating_val">                        
                        </div>

                        <input type="hidden" name="id" id="id" class="id">
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
    @includeIf('admin.layouts.partials.scripts.script-list',[
        'dataTableJS' => 1,
        'switch' => 1,
        'select2' => 1,
        'ratingjs'=> 1
    ])

    <script>
        var table;
        $(document).ready(function() {

            $(".autocompleteInput").autocomplete({
                source: '/backoffice/search-learner',
                focus: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label").val(ui.item.label);
                    $(".sl-label").attr("data-id",ui.item.value);
                    return false;
                }

            });

        $(".autocompleteInputCourse").autocomplete({
                source: '/backoffice/search-course',
                focus: function(event, ui) {
                    $(".sl-label-course").val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    $(".sl-label-course").val(ui.item.label);
                    $(".sl-label-course").attr("data-id",ui.item.value);
                    return false;
                }

            });



            var instructor_id = $("#instructor_id").val();
            table = $('#tbl_course_reviews').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,

                // ajax: "{{ url('backoffice/get_course_reviews') }}",
                ajax: {
                    url: "{{ url('backoffice/get_course_reviews')  }}",
                    data: function (d) {
                        d.user_id = $('#users').attr("data-id"),
                        d.courses = $('#courses').attr("data-id")
                    }
                    },
                columnDefs: [{
                    className: 'text-center',
                    targets: '_all'
                }],
                columns: [
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'rating',
                        name: 'rating'
                    },
                    {
                        data: 'comment',
                        name: 'comment'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'approved_status',
                        name: 'approved_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'approved_home_status',
                        name: 'approved_home_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                "order": [[4,'desc']],
            });

            $(document).on("change", ".approved_cls", function(){
                var status = $(this).is(":checked");
                var id = $(this).data('id');
                var token = "{{csrf_token()}}";
                var data = {status: status};
                var origin = "{{env('APP_URL')}}"+'/backoffice/course_reviews/'+id;

                $.ajax({
                    url: origin,
                    type: "PUT",
                    data: data,
                    headers: {"X-CSRF-TOKEN": token},
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response){
                        $('#loader_section').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'success',
                            text: 'Review updated successfully',
                        });
                    },
                    error: function(jqxhr, error, errorThrown){
                        if(jqxhr.status == 403){
                            Swal.fire(
                                'Unauthorized',
                                '',
                                'error'
                            );
                        }
                    },
                })
            });


            $(document).on("change", ".home_status_cls", function(){

                // alert($(this).is(":checked"))
                // return false;
                var origin = "{{route("home_reviews")}}"

                $.ajax({
                    url: origin,
                    type: "get",
                    data: {
                        status: $(this).is(":checked")==true ? 1 : 0,
                        id : $(this).data('id')
                    },
                    beforeSend: function() {
                        $('#loader_section').show();
                    },
                    success: function(response){
                        $('#loader_section').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'success',
                            text: 'Review updated successfully',
                        });
                    },
                    error: function(jqxhr, error, errorThrown){
                        if(jqxhr.status == 403){
                            Swal.fire(
                                'Unauthorized',
                                '',
                                'error'
                            );
                        }
                    },
                })
            });

        });

    // $('#btn_submit').click(function (event) {
    //     event.preventDefault();
    //     table.destroy();
    //     var user_id = $("#users").attr("data-id");
    //     var courses = $("#courses").attr("data-id");

    //     table = $('#tbl_course_reviews').DataTable({
    //         processing: true,
    //         serverSide: true,
    //         responsive: true,
    //         ajax: "{{ url('backoffice/get_course_reviews') }}"+'?user_id='+user_id+'&courses='+courses,
    //         columnDefs: [{
    //             className: 'text-center',
    //             targets: '_all'
    //         }],
    //         columns: [
    //                 {
    //                     data: 'course.title',
    //                     name: 'course.title'
    //                 },
    //                 {
    //                     data: 'learner.name',
    //                     name: 'learner.name'
    //                 },
    //                 {
    //                     data: 'rating',
    //                     name: 'rating'
    //                 },
    //                 {
    //                     data: 'comment',
    //                     name: 'comment'
    //                 },
    //                 {
    //                     data: 'created_date',
    //                     name: 'created_date'
    //                 },
    //                 {
    //                     data: 'approved_status',
    //                     name: 'approved_status',
    //                     orderable: false,
    //                     searchable: false
    //                 },
    //                 {
    //                     data: 'action',
    //                     name: 'action',
    //                     orderable: false,
    //                     searchable: false
    //                 }
    //             ],
    //         "order": []
    //     });
    // });
        $(document).on("click", "#btn_submit", function(event){
            event.preventDefault();
            var test = $("#frm").attr('action');
            $(frm).attr('action', test+'?');
            table.draw();

        })

        $("#edit_review_frm").on("submit", function(e) { 
            
            e.preventDefault(); 

            let isValid = true;
            const comment = $("#comment").val().trim();
            const rating = $("#rating_val").val();
            const id = $("#id").val();

            if (comment === "") {
                isValid = false;
                $(".error").html("Comment is required.");
            }

            if (!rating || rating === "0") {
                isValid = false;
                $(".error").html("Rating is required.");
            }

            if (isValid) {

                $(".sub_btn").hide();
                $(".loader_btn").show();

                $.ajax({
        url: "{{ route('course_reviews.update') }}",
        type: "POST",
        data: $(this).serialize(), 
        success: function(data) {
            if (data.status == "1") {
                                $("#courseReviewModal").modal("hide");
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
            }
        });    

             $(document).on("click", ".edit_course_review", function() {
                        $(".error").html("");
                        $(".sub_btn").show();
                        $(".loader_btn").hide();
                        $(".title_course_review").html("Update Course Review");

                    $.ajax({
                        url: "{{ route('course_reviews.edit') }}",
                        type: "get",
                        data: {
                        id: $(this).data("id")
                        },
                        success: function(data) {


                        $("#courseReviewModal").modal("show");
                        $("#comment").val(data.comment);
                        $("#rating_val").val(data.rating);
                        $("#id").val(data.id);
                        
                        $(".rating").empty();


                        for (let i = 1; i <= 5; i++) {
                            const starClass = i <= data.rating ? 'fas fa-star filled' : 'fas fa-star';
                            $(".rating").append(`<i class="${starClass}" data-star="${i}"></i>`);
                        }


                        $(".rating i").hover(
                            function() {
                            const hoverRating = $(this).data("star");
                            $(".rating i").each(function(index) {
                                if (index < hoverRating) {
                                $(this).addClass("filled");
                                } else {
                                $(this).removeClass("filled");
                                }
                            });
                            },
                            function() {

                            const currentRating = $("#rating_val").val();
                            $(".rating i").each(function(index) {
                                if (index < currentRating) {
                                $(this).addClass("filled");
                                } else {
                                $(this).removeClass("filled");
                                }
                            });
                            }
                        );


                        $(".rating i").on("click", function() {
                            const selectedRating = $(this).data("star");
                            $("#rating_val").val(selectedRating);

                            // Update the star display based on the new rating
                            $(".rating i").each(function(index) {
                            if (index < selectedRating) {
                                $(this).addClass("filled");
                            } else {
                                $(this).removeClass("filled");
                            }
                            });
                        });
                    }
                });
            });


                $("#rating").rating({
                    "stars": 5,
                    "click": function(e) {
                        $('#rating_val').val(e.stars);
                    }
                });

        $(document).ready(function(){
            $('#users').select2({
                allowClear: true,
                placeholder: "Select User"
            });
        })
        $(document).ready(function(){
            $('.select3').select2({
                allowClear: true,
                placeholder: "Select Course"
            });
        })

    </script>
@endsection
