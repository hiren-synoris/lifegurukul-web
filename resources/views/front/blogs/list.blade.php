@extends('front.layout.mainlayout')
@section('content')
    @component('front.components.breadcrumb')
        @slot('title') <a href="{{url('/')}}">Home</a> @endslot
        {{-- @slot('li1') <a href="{{url('/blogs')}}">All Blogs </a> @endslot --}}
        @slot('li2') Blogs @endslot
    @endcomponent
    <!-- Blog Grid -->
    <section class="course-content">
        <div class="container">
            @if (isset($blogs) && $blogs->isNotEmpty())
                <div class="row masonry-blog-blk">
                    @foreach ($blogs as $blog)
                        <div class="col-lg-4 col-md-6">
                            <!-- Blog Post -->
                            <div class="blog grid-blog aos" data-aos="fade-up">
                                <div class="blog-image">
                                    <a href="{{ url('blogs/' . $blog->slug) }}"><img class="img-fluid" src="{{getImageIfExists($blog->cover,blog_img_default())}}" alt="Post Image"></a>
                                </div>
                                <div class="blog-grid-box masonry-box">
                                    <div class="blog-info clearfix">
                                        <div class="post-left">
                                            <ul>
                                                <li><img class="img-fluid" src="{{ URL::asset('/front/img/icon/icon-22.svg') }}" alt="">{{ date('F d, Y', strtotime($blog->created_at)) }}</li>
                                                <li><img class="img-fluid" src="{{ URL::asset('/front/img/icon/icon-23.svg') }}" alt="">{{ (isset($blog->blogCategoryOptions) && isset($blog->blogCategoryOptions->name))?$blog->blogCategoryOptions->name:'' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <h3 class="blog-title"><a href="{{ url('blogs/' . $blog->slug) }}">
                                            {{ isset($blog->title) && !empty($blog->title) ? $blog->title : '' }}
                                        </a>
                                    </h3>
                                    <div class="blog-content blog-read">
                                        @if(isset($blog->content) && !empty($blog->content))
                                            {!! substr(strip_tags($blog->content,'...'), 0, 150).'...' !!}
                                        @endif
                                        <a href="{{ url('blogs/' . $blog->slug) }}" class="read-more btn btn-primary">Read
                                            More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Load button -->
                <div class="load-more text-center">
                    <div class="d-flex w-100 justify-content-center">
                        {!! $blogs->links() !!}
                    </div>
                </div>
                <!-- /Load button -->
            @else
                <p>No Blogs found</p>
            @endif
        </div>
    </section>
    <!-- /Blog Grid -->
@endsection
