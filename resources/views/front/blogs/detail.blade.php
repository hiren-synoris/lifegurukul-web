<?php $page="blog-details";?>
@extends('front.layout.mainlayout')
@push('meta')
    @if(isset($blog))
        <meta name="title" content="{{ isset($blog->meta_title) && !empty($blog->meta_title) ? $blog->meta_title : '' }}">
        <meta name="description" content="{{ isset($blog->meta_description) && !empty($blog->meta_description) ? $blog->meta_description : '' }}">
        <meta name="keywords" content="{{ isset($blog->meta_keywords) && !empty($blog->meta_keywords) ? $blog->meta_keywords : '' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $blog->meta_title ?: $blog->title }}">
        <meta property="og:description" content="{{ isset($blog->meta_description) && !empty($blog->meta_description) ? $blog->meta_description : '' }}">
        <meta property="og:locale" content="{{ app()->getLocale() }}">
    @endif
@endpush
@section('content')
    @component('front.components.breadcrumb')
        @slot('title') <a href="{{url('/')}}">Home</a> @endslot
        @slot('li1') <a href="{{url('/blogs')}}">Blogs</a> @endslot
        @slot('li2') {{ isset($blog) && !empty($blog->title) ? $blog->title : '' }} @endslot
    @endcomponent
<!-- Blog Details -->
<section class="course-content blog-details">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <!-- Blog Post -->
                @if(isset($blog) && !empty($blog))
                    <div class="blog aos" data-aos="fade-up">
                        <div class="blog-image">
                            <a href="{{url('blogs/'.$blog->slug)}}"><img class="img-fluid" src="{{getImageIfExists($blog->cover,blog_img_default())}}" alt="Post Image"></a>
                        </div>
                        <div class="blog-info clearfix">
                            <div class="post-left">
                                <ul>
                                    <li><img class="img-fluid" src="{{ URL::asset('front/img/icon/icon-22.svg')}}" alt="">{{$blog->date}}</li>
                                    <li><img class="img-fluid" src="{{ URL::asset('front/img/icon/icon-23.svg')}}" alt="">{{ (isset($blog->blogCategoryOptions) && isset($blog->blogCategoryOptions->name))?$blog->blogCategoryOptions->name:'' }}</li>
                                </ul>
                            </div>
                        </div>
                        <h3 class="blog-title">{{ isset($blog->title) && !empty($blog->title) ? $blog->title : ''}}</h3>
                        <div class="blog-content">
                            {!! isset($blog->content) && !empty($blog->content) ? $blog->content : '' !!}
                        </div>
                    </div>
                    <!-- /Blog Post -->
                @endif
            </div>
        </div>
    </div>
</section>
<!-- /Blog Details -->
@endsection
