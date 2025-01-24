@extends('front.layout.mainlayout')
@section('content')
<div class="main-wrapper">

    @component('front.components.breadcrumb')
    @slot('title') <a href="{{url('/')}}">Home</a> @endslot
    @slot('li2') Course Category @endslot
    @endcomponent

    @component('front.components.pagebanner')
        @slot('title') Course Category @endslot
    @endcomponent

    <section class="course-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    {{-- <div class="title-sec">
                        <h2>Course Categories</h2>
                    </div> --}}

                    <div class="tab-content">
                        <div class="tab-pane fade show active aos" data-aos="fade-up" id="graphics">
                            <div class="row">
                                @if (isset($categories) && !empty($categories) && count($categories) > 0)
                                    @foreach($categories as $category)
                                        <div class="col-lg-4 col-md-6">
                                            <a href="{{url('course?categorylist='.$category->id)}}">
                                                <div class="category-box">
                                                    <div class="category-title">
                                                        <div class="category-img">
                                                            <img src="{{ !str_contains($category->image, 'front') ? getImageIfExists($category->image, default_category_img())  : asset($category->image) }}"
                                                                            alt="">
                                                        </div>
                                                        <h5>{{ isset($category->name) && !empty($category->name) ? $category->name : '' }}</h5>
                                                    </div>
                                                    <div class="cat-count">
                                                        {{-- <span>{{ isset($category->total_course) && !empty($category->total_course) ? $category->total_course : 0 }}</span> --}}
                                                        <span>{{ Helper::countCategory($category->id) }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- @if(count($categories)>0)
                                @foreach($categories as $key => $value)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="category-box">
                                            <div class="category-title">
                                                <div class="category-img">
                                                    <img src="{{str_contains($value->image,'front') ? asset($value->image):Helper::asseturl($value->image, true)}}" alt="" class="">
                                                </div>
                                                <h5>{{$value->name}}</h5>
                                            </div>
                                            <div class="cat-count">
                                                <span>{{empty($value->total) ? 0 : $value->total}}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @else
                                <div class="col-lg-4 col-md-6"> No Categories Found</div>
                                @endif --}}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div style="display: none;">
        <form action="" method="GET" id="sort_frm">
            <input type="text" value="" id="sort_frm_input" name="sort" />
            <input type="submit" id="sort_frm_btn">
        </form>
    </div>
    <div style="display: none;">
        <form action="" method="GET" id="search_frm">
            <input type="text" value="" id="search_frm_input" name="search" />
            <input type="submit" id="search_frm_btn">
        </form>
    </div>
    @endsection

@section('js')
    <script>
    //     $(document).on("click",".wishlist", function(){
    //         var loggedin = "false";
    //         loggedin = "{{ Auth::guard('learner')->check() }}";
    //     if(loggedin == "false" || loggedin == false){
    //         $(".header-sign").trigger('click');
    //     }
    //     else{
    //         var temp=$(this).data('course_id');
    //         var userid = $("#user_id").val();
    //         var active = $(this).find('i').hasClass('color-active');
    //         var wishlist_id = $(this).data('id');
    //         $("#wishlist_id").val(wishlist_id);
    //         var temp1 = $(this).parents().closest('section').hasClass('trend-course');
    //             $("#wishlist_active").val(active);
    //         $("#course_id_frm").val(temp);
    //         if(temp != undefined && temp != "" && temp != null && temp != 'NULL' && userid != undefined && userid != "" && userid != null && userid != 'NULL')  $("#wishlist_btn").trigger('click');

    //     }
    // });

    $(document).on("change", "#sort", function(event){
        event.preventDefault();
        let sort_value=$("#sort").find(":selected").val();
        $("#sort_frm_input").val(sort_value);
        $("#sort_frm_btn").trigger("click");
    });

    $(document).on("click", "#search", function(event){
        event.preventDefault();
        let data=$(this).parent().find('input').val();
        $("#search_frm_input").val(data);
        $("#search_frm_btn").trigger("click");
    });
    </script>

@endsection
