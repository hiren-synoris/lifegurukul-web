@extends('admin.layouts.app')
@section('styles')
@includeIf('admin.layouts.partials.styles.style', [
'summerNoteCSS' => 1,
])
@endsection

@php
//Here i am getting course ID from request(url)
$courseID = '';
if(request()->segment(3) != null || request()->segment(2) != ''){
$courseID = request()->segment(3);

}


@endphp
@section('content')
<div class="row justify-content-center">
	<div class="block public-form-main">
		<div class="block-header">
			<div class="title">
				<h2>Discussion</h2>
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
			<form method="POST" action="{{ url('backoffice/public-forum') }}" enctype="multipart/form-data">
				@csrf
				<div class="d-flex justify-content-center mb-3">
					<!-- <h3>Discussion</h3> -->
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
									<input type="file" name="image" id="image" accept="image/*" />

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
						<div class="comment-image">
							<p>{!! $forum->description !!}</p>
							@if($forum->image)
							<a href="{{ Storage::url($forum->image) }}" download>
								<button style="font-size:10px">
									<i class="fa fa-download"></i>
									{{ basename($forum->image) }}
								</button>
							</a>
							@endif
						</div>
						<div class="delete-icon">
							<a href="javascript:void(0)" class="mx-1 text-danger" title="Delete" type="button" onclick="permanent_delete_confirmation('{{ url('backoffice/public-forum-delete-question/') }}/{{ $forum->id }}')">
								<i class="fas fa-trash-alt"></i>
							</a>
						</div>
					</div>
					<div class="footer">

						<!-- <div class="divider"></div> -->
						<button type="button" class="btn btn-primary open-reply-modal" data-forum-id="{{ $forum->id }}">Reply</button>
						<!-- <div class="divider"></div> -->

					</div>
				</div>

				@if(isset($forum->reply) && !empty($forum->reply))
				@foreach($forum->reply as $reply)
				<div class="comment reply_to_set">
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

					<div class="comment-image reply">
						<p>{!! $reply->reply !!}</p>
						@if($reply->image)
						<a href="{{ Storage::url($reply->image) }}" download>
							<button style="font-size:10px">
								<i class="fa fa-paperclip"></i>
								{{ basename($reply->image) }}
							</button>
						</a>
						@endif
					</div>
					<div class="content reply">
						<a href="javascript:void(0)" class="mx-1 text-danger" title="Delete" type="button" onclick="permanent_delete_confirmation('{{ url('backoffice/public-forum-delete-reply/') }}/{{ $reply->id }}')">
							<i class="fas fa-trash-alt"></i>
						</a>

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
						<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button> -->
					</div>
					<form id="replyForm" method="POST" action="{{ url('backoffice/reply-public-forum') }}" enctype="multipart/form-data">
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


@endsection
@section('scripts')
@includeIf('admin.layouts.partials.scripts.script-list', [
'summerNoteDiscussion' => 1,
'validateJS' => 1,
])

<script>
	$(document).ready(function() {

		$('.note-link-btn').click(function() {

			var $linkDialog = $('.note-link-dialog');

			console.log($linkDialog);

			var url = $('.note-link-url').val();

			var urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/;

			if (!urlPattern.test(url)) {
				alert('Please enter a valid URL.');
				('.note-link-url').val('');
				event.stopPropagation();
				event.preventDefault();
				return false;
			}
			
			return true;
		});


		$(document).on('click', '.note-btn', function() {
			$(".sn-checkbox-open-in-new-window").css("display", "none");
			$(".sn-checkbox-use-protocol").css("display", "none");
		});

		$('.mobile').keyup(function() {
			this.value = this.value.replace(/[^0-9\.]/g, '');
		});
	});

	$('.open-reply-modal').click(function() {

		var forumId = $(this).data('forum-id');
		// alert(forumId);
		var URL = "{{ route('get_question') }}";
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

	$('#replyForm .btn-secondary-discussion').click(function() {
		$('#replyModal').modal('hide');
		$('.note-editable.card-block').empty();

	});

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
				// You may perform any further actions like updating the UI here
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