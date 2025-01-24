@extends('front.layout.mainlayout')
@php
use App\Models\Course;
//Here i am getting course ID from request(url)
$courseID = '';
if(request()->segment(2) != null || request()->segment(1) != ''){
$courseID = request()->segment(2);

$course = Course::where("id",$courseID)->first();

};
@endphp
@section('content')
<!--Dashbord Student -->
<div class="page-content">
	<div class="container">
		<div class="row">
			@include('front.student.components.sidebar')
			<!-- Notifications -->

			<div class="col-xl-9 col-md-8">
				<div class="block public-form-main">
					<div class="block-header">
						<div class="title">
							<!-- <h2>Discussion</h2> -->
							<!-- <div class="tag">12</div> -->
						</div>
						<div class="group-radio">
							<span class="button-radio">
								<!-- <input id="latest" name="latest" type="radio" checked>
				<label for="latest">Latest</label> -->
							</span>
							<!-- <div class="divider"></div> -->
							<span class="button-radio">
								<!-- <input id="popular" name="latest" type="radio">
				<label for="popular">Popular</label> -->
							</span>
						</div>
					</div>
					<div class="writing">
						<form method="POST" enctype="multipart/form-data" action="{{ url('public-forum-front') }}">
							@csrf
							<div class="d-flex justify-content-center mb-3">
								<h3>{{ $course->title }} : Discussion</h3>
							</div>

							{{-- @includeIf('admin.layouts.partials.errors.validation-failed') --}}

							<div class="form-group">
								<input type="hidden" name="course_id" id="course_id" value="{{ $courseID }}">

								<label for="description">Description</label><span style="color: red">*</span>
								<textarea class="form-control summernote-editor" id="description" name="description">
								{{ old('description') }}
								</textarea>
								@error('description')
								<div class="text text-danger">{{ $message }}</div>
								@enderror
							</div>
							<div class="footer">
								<div class="text-format">
									<div class="form-group">
										<label for="image">Image<small class="text-gray"> (Please upload jpg and png files no larger than 2 MB)</small></label>
										<div class="image-upload-container">
											<label class="block form-control">
												<span class="sr-only">Choose File</span>
												<input type="file" name="image" id="" accept="image/*" />

											</label>
											@error('image')
											<div class="text text-danger">{{ $message }}</div>
											@enderror
										</div>
									</div>
								</div>
								<div class="group-button">
									<button class="btn"><i class="ri-at-line"></i></button>
									<button class="btn primary">Send</button>
								</div>
							</div>
						</form>
					</div>
					<div>

						@foreach($getPublic_forum as $forum)
						<div class="main-discussion">
							<div class="comment">
								<div class="user-banner">
									<div class="user">
										<div class="avatar">
										<img src="{{ !empty($forum->created_by->profile_pic) ? $forum->created_by->profile_pic : user_img_default() }}">
											<!-- <span class="stat grey"></span> -->
										</div>
										<h5>{{ !empty($forum->created_by->name) ? $forum->created_by->name : "" }}</h5><small class="text-gray"> ({{ \Carbon\Carbon::parse($forum->created_at)->format('d-M-Y h:i A') }})</small>
									</div>
									<button class="btn dropdown"><i class="ri-more-line"></i></button>
								</div>
								<div class="content">
									<p>{!! $forum->description !!}</p>

									

									@if(auth()->guard("learner")->user()->id == $forum->created_by_learner)
									<form method="POST" action="{{ route('public.forums.delete') }}" id="descriptionDiscussion" class="descriptionDiscussion_{{ $forum->id }}">
										@csrf
										@method('PUT')
										<input name="description_id" id="description_id" type="hidden" value="{{ $forum->id }}" />
										<a type="button" class="" onclick="openConfirmationModal({{ $forum->id }})"><i class="fas fa-trash-alt"></i></a>
									</form>
									@endif


									@if($forum->image)
									<a href="{{ Storage::url($forum->image) }}" download>
										<button style="font-size:10px">
											<i class="fa fa-download"></i>
											{{ basename($forum->image) }}
										</button>
									</a>
									@endif
								</div>
								<div class="footer">

									<!-- <div class="divider"></div> -->
									<button type="button" class="btn btn-primary open-reply-modal" data-forum-id="{{ $forum->id }}">Reply</button>
									<!-- <div class="divider"></div> -->

								</div>
							</div>

							@if(isset($forum->reply) && !empty($forum->reply))
							@foreach($forum->reply as $reply)
							<div class="reply comment">
								<div class="user-banner">
									<div class="user">
										@if($reply->user)
										<div class="avatar">
											<img src="{{ getImageIfExists($reply->user->profile_picture) }}" alt="">
										</div>
										<h5>{{ $reply->user->name }}</h5><small class="text-gray"> ({{ \Carbon\Carbon::parse($reply->created_at)->format('d-M-Y h:i A') }})</small>
										@elseif($reply->learner)
										<div class="avatar">
											<img src="{{ getImageIfExists($reply->learner->profile_pic) }}" alt="">

										</div>
										<h5>{{ $reply->learner->name }}</h5><small class="text-gray"> ({{ \Carbon\Carbon::parse($reply->created_at)->format('d-M-Y h:i A') }})</small>
										@endif
									</div>
									<button class="btn dropdown"><i class="ri-more-line"></i></button>
								</div>
								<div class="content">
									<p>{!! $reply->reply !!}</p>

									@if(auth()->guard("learner")->user()->id == $reply->reply_by_learner)
									<form method="POST" action="{{ route('public.forums.reply.delete') }}" id="descriptionDiscussion" class="descriptionDiscussion_{{ $reply->id }}">
										@csrf
										@method('PUT')
										<input name="reply_id" id="reply_id" type="hidden" value="{{ $reply->id }}" />
										<a type="button" class="" onclick="openConfirmationModal({{ $reply->id }})"><i class="fas fa-trash-alt"></i></a>
									</form>
									@endif

									@if($reply->image)
									<a href="{{ Storage::url($reply->image) }}" download>
										<button style="font-size:10px">
											<i class="fa fa-paperclip"></i>
											{{ basename($reply->image) }}
										</button>
									</a>
									@endif
								</div>

							</div>
							@endforeach
						</div>
						@endif
						@endforeach
					</div>

					<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="replyModalLabel"></h5>

								</div>
								<form id="replyForm" method="POST" enctype="multipart/form-data" action="{{ url             ('reply-public-forum-front') }}">
									@csrf
									<div class="modal-body">
										<input type="hidden" name="public_forums_id" id="forumId">
										<div class="form-group">
											<label for="description">Description</label><span style="color: red">*</span>
											<textarea class="form-control summernote-editor" id="reply" name="reply" placeholder="Write your reply here">{{ old('reply') }}</textarea>
											<div class="text-danger" id="replyError"></div>
										</div>
										<div class="form-group">
											<label for="reply_image">Image<small class="text-gray"> (The maximum file size to upload is 2 MB.) Accept only jpg And png</small></label>
											<div class="image-upload-container">
												<label class="block form-control">
													<span class="sr-only">Choose File</span>
													<input type="file" name="reply_image" id="" accept="image/*" />

												</label>
											</div>
											<div class="text-danger" id="replyImageError"></div>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary-discussion" data-dismiss="modal">Close</button>
										<button type="submit" class="btn btn-primary">Reply</button>
									</div>
								</form>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
		<!-- Notifications -->

	</div>
</div>
{{-- delete model start --}}
<div class="modal fade" id="delete_conf" data-backdrop="static" tabindex="-1" aria-labelledby="delete_conf_title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="delete_conf_title">Delete Confirmation</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="hideConfirmationModal()">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
				<a href="javascript:void(0)" id="delete_conf_yes" type="button" class="btn btn-success">Yes</a>
			</div>
		</div>
	</div>
</div>
{{-- delete model end --}}

<!-- /Dashbord Student -->
@endsection
@section('js')
<script>
	function openConfirmationModal(id) {

		$('#delete_conf').modal('show');
		document.getElementById("delete_conf_yes").addEventListener("click", function() {
			submitForm(id);
		});

	}

	function hideConfirmationModal() {
		$('#delete_conf').modal('hide');
	}

	function submitForm(id) {
		var newclass = ".descriptionDiscussion_" + id;
		$(newclass).submit();

	}

	$(document).ready(function() {




		document.querySelector('#delete_conf .modal-footer .btn-danger').addEventListener('click', hideConfirmationModal);


		$(document).on('click', '.note-btn', function() {
			$(".sn-checkbox-open-in-new-window").css("display", "none");
			$(".sn-checkbox-use-protocol").css("display", "none");
		});

		$('.mobile').keyup(function() {
			this.value = this.value.replace(/[^0-9\.]/g, '');
		});
	});

	$('#replyForm .btn-secondary-discussion').click(function() {
		$('#replyModal').modal('hide');
		$('.note-editable.card-block').empty();

	});




	$('.open-reply-modal').click(function() {

		var forumId = $(this).data('forum-id');
		var URL = "{{ route('front_get_question') }}";
		$.ajax({
			type: 'POST',
			url: URL,
			data: {
				forumId: forumId
			},
			success: function(response) {
				$('#replyModalLabel').html(response.description);
				$('#forumId').val(forumId);
				$('#replyModal').modal('show');
			},
			error: function(xhr, textStatus, errorThrown) {
				if (xhr.status === 422) {
					var errors = xhr.responseJSON.errors;
					if (errors.hasOwnProperty('reply')) {
						$('#replyError').text(errors.reply[0]);
					}
					if (errors.hasOwnProperty('reply_image')) {
						$('#replyImageError').text(errors.reply_image[0]);
					}
				}
			}
		});


	});


	// $('.close-reply-modal').click(function() {
	//     $('#replyModal').modal('hide');
	// });

	$('#replyForm').submit(function(event) {
		$('#loader_section').show();
		event.preventDefault();
		var formData = new FormData(this);
		$.ajax({
			type: 'POST',
			url: $(this).attr('action'),
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				$('#replyModal').modal('hide');
				$('#loader_section').hide();
				location.reload();
			},
			error: function(xhr, textStatus, errorThrown) {
				if (xhr.status === 422) {
					$('#loader_section').hide();
					var errors = xhr.responseJSON.errors;
					if (errors.hasOwnProperty('reply')) {
						$('#replyError').text(errors.reply[0]);
					}
					if (errors.hasOwnProperty('reply_image')) {
						$('#replyImageError').text(errors.reply_image[0]);
					}
				}
			}
		});
	});
</script>
@endsection