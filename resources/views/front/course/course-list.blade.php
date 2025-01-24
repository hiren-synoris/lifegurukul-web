@extends('front.layout.mainlayout')
@section('content')
    <div class="main-wrapper">

        @component('front.components.breadcrumb')
            @slot('title')
                <a href="{{ url('/') }}">Home</a>
            @endslot
            @slot('li2')
                All Courses
            @endslot
        @endcomponent

        <section class="course-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">

                        <div class="showing-list">
                            <div class="row">
                                <div class="col-md-6 col-lg-8 d-flex align-items-center">
                                    <div class="show-result ">
                                        <h4>
                                            {{ __('Showing') }}
                                            <span class="fw-semibold">{{ $courses->firstItem() }}</span>
                                            {{ __('to') }}
                                            <span class="fw-semibold">{{ $courses->lastItem() }}</span>
                                            {{ __('of') }}
                                            <span class="fw-semibold">{{ $courses->total() }}</span>
                                            {{ __('results') }}
                                        </h4>
                                    </div>
                                </div>
                                @php
                                    $ordering = [
                                        'date_asc' => 'Oldest',
                                        'date_desc' => 'Newest',
                                        'price_asc' => 'Price Low to High',
                                        'price_desc' => 'Price High to Low',
                                      //  'rating_asc' => 'Lowest Rating',
                                        'rating_desc' => 'Most Rated',
                                    ];
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="show-filter add-course-info">
                                        <div class="row gx-2 align-items-center">
                                            <div class="col-md-12  col-item">
                                                <div class="form-group select-form mb-0">
                                                    <select class="form-select select" id="sort" name="sort">
                                                        <option value="">Sort By</option>
                                                        @foreach ($ordering as $orderKey => $orderTitle)
                                                            <option value="{{ $orderKey }}"
                                                                {{ isset($_GET['sort']) && $_GET['sort'] != '' && $_GET['sort'] == $orderKey ? 'selected' : '' }}>
                                                                <i class="fas fa-sort-amount-up-alt"></i>
                                                                {{ $orderTitle }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (isset($courses) && count($courses) > 0)
                            <div class="row" id="course-list-data">
                                @foreach ($courses as $key => $value)
                                    @php
                                        $avg_course_rating = $value->AverageRating ?? 0;
                                    @endphp
                                    {{-- //aos" data-aos="fade-up" --}}
                                    <div class="col-lg-12 col-md-12 d-flex">
                                        <div class="course-box course-design list-course d-flex">
                                            <div class="product">
                                                <div class="product-img">
                                                    @if ($value->type == 2)
                                                        <div class="course-package">
                                                            Package
                                                        </div>
                                                    @endif
                                                    <a
                                                        href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">
                                                        <img class="img-fluid" alt=""
                                                            src="{{ getImageIfExists($value->image, course_img_default()) }}">
                                                    </a>
                                                    @php
                                                        $priceArray=['plan_name'=>$value->plan_name,
                                                            'planId'=>$value->planId,
                                                            'plan_type'=>$value->plan_type,
                                                            'list_price'=>$value->list_price,
                                                            'final_payable_price'=>$value->final_payable_price,
                                                            'courseId'=>$value->id,
                                                            'slug'=>$value->slug,
                                                            'type'=> $value->type
                                                        ];
                                                    @endphp
                                                    {{ $price = min_max_price_btn($priceArray, 1) }}
                                                </div>
                                                <div class="product-content">
                                                    <div class="head-course-title">
                                                        <h3 class="title"><a
                                                                href="{{ $value->type == 2 ? url('course-package/' . $value->slug) : url('course-details/' . $value->slug) }}">{{ $value->title }}</a>
                                                        </h3>
                                                        <div class="all-btn all-category d-flex align-items-center">
                                                            {{ check_course_is_free_or_not($priceArray, 2) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="text-end"></label>
                                                    </div>
                                                    <div class="course-info border-bottom-0 pb-0 d-flex align-items-center">

                                                        <div class="rating-img d-flex align-items-center">
                                                            <img src="{{ asset('front/img/icon/icon-01.svg') }}"
                                                                alt="">
                                                            @if ($value->type == 2)
                                                                <p>{{ $value->packages_count ?? 0 }}+ Course</p>
                                                            @else
                                                                <p>{{ $value->chapters_count ?? 0 }}+ Lesson</p>
                                                            @endif

                                                        </div>
                                                        {{-- @if ($value->type == 1) --}}
                                                        <div class="course-view d-flex align-items-center">
                                                            <img src="{{ asset('front/img/icon/icon-19.svg') }}"
                                                                alt="">
                                                            <p>{{ $value->lng == 1 ? 'English' : 'Hindi' }}</p>
                                                        </div>
                                                        {{-- @endif --}}
                                                        <!-- @if ($value->type == 1)
    <div class="course-view d-flex align-items-center course-share">
                                                    <img src="{{ asset('front/img/icon/icon-23.svg') }}" alt="" />
                                                     @foreach ($value->categories as $categories_key => $categories_value)
                                                    <p>{{ isset($categories_value) && isset($categories_value->name) ? $categories_value->name : '' }}</p>
                                                    @endforeach
                                                     <p>{{ isset($value->categories) && isset($value->categories->name) ? $value->categories->name : '' }} </p>
                                                </div>
                                                        @endif -->
                                                        @if (
                                                            (isset($value->hours) && !empty($value->hours) && $value->hours != '0') ||
                                                                (isset($value->minutes) && !empty($value->minutes) && $value->minutes != '0'))
                                                            <div class="course-view d-flex align-items-center">
                                                                <img src="{{ asset('front/img/icon/icon-02.svg') }}"
                                                                    alt="" />
                                                                @if ($value->hours != '0')
                                                                    <p>{{ $value->hours }}h</p>
                                                                @endif
                                                                @if ($value->minutes != '0')
                                                                    <p>{{ $value->minutes }}m</p>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="rating">

                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <i
                                                                class="fas fa-star {{ $i <= round($avg_course_rating) ? 'filled' : '' }}"></i>
                                                        @endfor
                                                        <span class="d-inline-block average-rating">
                                                            ({{ $value->rating_reviews->count()??0 }})
                                                        </span>
                                                    </div>
                                                    <div class="course-instructor d-flex mb-0">
                                                        @if ($value->instructor && !empty($value->instructor) && isset($value->instructor->id))
                                                            {{-- @if ($value->type == 1) --}}
                                                            <div class="course-group-img d-flex">
                                                                <a
                                                                    href="{{ url('instructor/' . $value->instructor->id) }}"><img
                                                                        src="{{ getImageIfExists($value->instructor->profile_picture, user_img_default()) }}"
                                                                        alt="" class="img-fluid"></a>
                                                                <div class="course-name inst-name">
                                                                    <h4><a
                                                                            href="{{ url('instructor/' . $value->instructor->id) }}">{{ $value->instructor->name }}</a>
                                                                    </h4>
                                                                    <p>{{ $value->instructor->instructure->designation ?? '' }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            {{-- @endif --}}
                                                        @endif
                                                        <div
                                                            class="course-share d-flex align-items-center justify-content-center">
                                                            @if (!empty($wishlists) && !empty($value->wishlists) && Auth::guard('learner')->check())
                                                                <a href="javascript:void(0)" class="wishlist "
                                                                    data-course_id="{{ $value->id }}"
                                                                    data-wishlist_active=1
                                                                    data-wishlist_id="{{ !empty($value->wishlists) ? $value->wishlists->pluck('id')->first() : '' }}">
                                                                    <i
                                                                        class="fa-regular fa-heart {{ active_wishlist($value->id) }}"></i></a>
                                                            @else
                                                                <a href="javascript:void(0)" class="wishlist"
                                                                    data-course_id="{{ $value->id }}"
                                                                    data-wishlist_active=0 data-wishlist_id=0>
                                                                    <i class="fa-regular fa-heart"></i>
                                                                </a>
                                                            @endif

                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="pagination lms-page">
                                        {!! $courses->withQueryString()->links('pagination::bootstrap-4') !!}
                                    </ul>
                                </div>
                            </div>
                        @else
                            <h5>No record found!</h5>
                        @endif
                    </div>

                    <div class="col-lg-3 theiaStickySidebar">
                        <div class="filter-clear show-filter">
                            <div class="showing-list">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="course-search">
                                            <form>
                                            <div class="search-group">
                                                <input type="text"
                                                    value="{{ isset($_GET['searchTxt']) && $_GET['searchTxt'] != '' ? $_GET['searchTxt'] : '' }}"
                                                    class="form-control" id="srch" name="searchTxt"
                                                    placeholder="Search courses">
                                                    <button id="searchButton" type="submit" class="btn btn-primary"><i
                                                        class="fa-solid fa-magnifying-glass"></i></button>
                                                    </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card search-filter ">
                                <div class="accordion accordion-flush" id="accordionFlushExample">
                                    <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-Price-div">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-Price"
                                            aria-expanded="false" aria-controls="flush-Price">
                                            Price
                                        </button>
                                    </h2>
                                    <div id="flush-Price" class="accordion-collapse collapse"
                                        aria-labelledby="flush-Price-div" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            <div>
                                                <label class="custom_check">
                                                    <input type="radio" value="1" id="pricelist[0,0]"
                                                        name="pricelist" class="price price_flt"
                                                        {{ !empty($prev_price_list) && in_array('0,0', $prev_price_list) ? 'checked' : '' }}>
                                                    <span class="radiocheckmark"></span> Free
                                                </label>
                                            </div>
                                            @php
                                                $temp3 = coursePriceFilter();
                                            @endphp

                                            @if (count($temp3) > 0)
                                                @foreach ($temp3 as $key => $value)
                                                    @if ($key == 0)
                                                        <div>
                                                            <label class="custom_check">
                                                                <input type="radio" value="1"
                                                                    id="pricelist[0,500]" name="pricelist"
                                                                    class="price price_flt"
                                                                    {{ !empty($prev_price_list) && in_array('0,500', $prev_price_list) ? 'checked' : '' }}>
                                                                <span class="radiocheckmark"></span> Up To 500
                                                            </label>
                                                        </div>
                                                    @else
                                                        @if (isset($value['left']) && isset($value['right']) && $value['right'] > 0)
                                                            <div>
                                                                <label class="custom_check">
                                                                    <input type="radio" value="1"
                                                                        id="pricelist[{{ $value['left'] . ',' . $value['right'] }}]"
                                                                        name="pricelist" class="price price_flt"
                                                                        {{ !empty($prev_price_list) && in_array($value['left'] . ',' . $value['right'], $prev_price_list) ? 'checked' : '' }}>
                                                                    <span class="radiocheckmark"></span> Up to
                                                                    {{ $value['right'] }}
                                                                </label>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                    <div class="accordion-item">
                                    <h4 class="accordion-header" id="flush-category-div">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-category" aria-expanded="false"
                                            aria-controls="flush-category">
                                            Course Categories
                                        </button>
                                    </h4>
                                    <div id="flush-category" class="accordion-collapse collapse"
                                        aria-labelledby="flush-category-div" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">
                                            @if (isset($categories) && !empty($categories) && count($categories) > 0)
                                                @foreach ($categories as $category)
                                                    @if ($category->home == 1)
                                                        <div>
                                                            <label class="custom_check">
                                                                <input type="checkbox"
                                                                    {{ in_array($category->id, stringToArray(isset($_GET['categorylist']) && $_GET['categorylist'] != '' ? $_GET['categorylist'] : [])) ? 'checked' : '' }}
                                                                    value="{{ $category->id }}" name="category"
                                                                    class="category">
                                                                <span class="checkmark"></span>{{ $category->name }}
                                                            </label>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div>
                                                    <label>No Categories</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                    <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-Instructors-div">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-Instructors"
                                            aria-expanded="false" aria-controls="flush-Instructors">
                                            Instructors
                                        </button>
                                    </h2>
                                    <div id="flush-Instructors" class="accordion-collapse collapse"
                                        aria-labelledby="flush-Instructors-div" data-bs-parent="#accordionFlushExample">
                                        <div class="accordion-body">

                                            @if (isset($instructors) && count($instructors) > 0)
                                                @foreach ($instructors as $key => $value)
                                                    <div>
                                                        <label class="custom_check">
                                                            <input type="checkbox"
                                                                {{ in_array($value->id, stringToArray(isset($_GET['instructurelist']) && $_GET['instructurelist'] != '' ? $_GET['instructurelist'] : [])) ? 'checked' : '' }}
                                                                name="instructure" class="instructure"
                                                                value="{{ $value->id }}">
                                                            <span class="checkmark"></span>{{ $value->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div>
                                                    <label class="custom_check">No Instructors</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>

                                <form action="" method="GET" id="search_frm">
                                    <input type="hidden"
                                        value="{{ isset($_GET['searchTxt']) && $_GET['searchTxt'] != '' ? $_GET['searchTxt'] : '' }}"
                                        name="searchTxt" id="searchTxt">
                                    <input type="hidden"
                                        value="{{ isset($_GET['categorylist']) && $_GET['categorylist'] != '' ? $_GET['categorylist'] : '' }}"
                                        name="categorylist" id="categorylist">
                                    <input type="hidden"
                                        value="{{ isset($_GET['instructurelist']) && $_GET['instructurelist'] != '' ? $_GET['instructurelist'] : '' }}"
                                        name="instructurelist" id="instructurelist">
                                    <input type="hidden"
                                        value="{{ isset($_GET['pricelist']) && $_GET['pricelist'] != '' ? $_GET['pricelist'] : '' }}"
                                        name="pricelist" id="pricelist">
                                    <input type="hidden"
                                        {{-- value="{{ isset($_GET['sort']) && $_GET['sort'] != '' ? $_GET['sort'] : '' }}"
                                        name="sort" id="sort"> --}}
                                        <input type="hidden" value="" id="sort_frm_input" name="sort" />
                                    <div class="clear-filter d-flex align-items-center">
                                        <h4>
                                            <button type="submit" class="btn btn-primary" id="search_frm_btn">
                                                <i class="feather-filter"></i>Apply</button>
                                        </h4>
                                        <div class="clear-text">
                                            <a class="btn btn-warning" href="{{ url('course') }}"
                                                style="cursor:pointer;">RESET</a>
                                        </div>
                                    </div>
                                </form>


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
    @endsection
    @section('js')
        <script>
            $(document).on("change", "#sort", function(event) {
                event.preventDefault();
                let sort_value = $("#sort").find(":selected").val();
                $("#sort_frm_input").val(sort_value);
                // $("#sort_frm_btn").trigger("click");
                if(sort_value=='') {
                    window.location.href = window.location.origin+'/course';
                } else {
                    $("#search_frm_btn").trigger("click");
                }
            });

            $(document).on("change", ".category", function(event) {
                var category = $('input[name=category]:checked').map(function() {
                    return $(this).val();
                }).get().join(',');

                $('input[name=categorylist]').val(category);
            });

            $(document).on("change", ".price_flt", function(event) {
                event.preventDefault();
                var price_flt = $('input[id^=pricelist]:checked').map(function(key, value) {
                    return $(this).attr('id').replace("pricelist[", '').replace("]", '');
                }).get().join(':');
                $('input[id=pricelist]').val(price_flt);
            });

            $(document).on("change", ".instructure", function(event) {
                var instructure = $('input[name=instructure]:checked').map(function() {
                    return $(this).val();
                }).get().join(',');

                $('input[name=instructurelist]').val(instructure);
            });

            $(document).on("keyup", "#srch", function(event) {
                let data = $('input[name=srch]').val();
                $("#searchTxt").val(data);
            });
            // $('#srch').keypress(function(e) {
            //     if (e.which == 13) { //Enter key pressed
            //         $('#search_frm_btn').click(); //Trigger search button click event
            //     }
            // });
            $('#searchButton').click(function(e) {
                $('#search_frm_btn').click(); //Trigger search button click event
            });
        </script>
    @endsection
