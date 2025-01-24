@extends('admin.layouts.app')
@section('right-section')
    {!! redirect_to_back(route('course_reviews.index')) !!}
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Course Review Details</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                @if (isset($course_review) && !empty($course_review))
                <div class="post">
                    <div class="user-block">
                        <strong>Course</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ $course_review->course->title ?? '' }}</label>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Learner</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ $course_review->learner->name ?? '' }}</label>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Comment</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <p>{!! $course_review->comment ?? '' !!}</p>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Rating</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="rating_view">
                            @for ($i = 1; $i <= 5; $i++) <i class="fas fa-star {{ $i <= ($course_review->rating ?? 0) ? 'filled' : '' }}"></i>
                                @endfor
                        </div>
                    </div>

                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Created At</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ $course_review->created_at ? date('d/m/Y H:i:s', strtotime($course_review->created_at)) : '' }}</label>
                    </div>
                </div>
                <div class="post">
                    <div class="user-block">
                        <strong>Updated At</strong>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <label>{{ $course_review->updated_at ? $course_review->date : '' }}</label>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection