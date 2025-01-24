<!-- Breadcrumb -->
{{-- <div class="page-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                @if(!Route::is(['help-center']))
                <h1 class="mb-0">{{ $title }}</h1>
                @endif
                @if(Route::is(['help-center']))
                <h1>{{ $title }}</h1>
                <p>{{ $li1 }}</p>
                @endif
            </div>
        </div>
    </div>
</div> --}}
<!-- /Breadcrumb -->

<!-- Breadcrumb -->
<div class="page-banner instructor-bg-blk">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                    @if(isset($image)  && isset($title))
                        <div class="profile-info-blk">
                            @if(isset($image))
                            <a href="javascript:;" class="profile-info-img">
                                <img src="{{ isset($image) ? $image:'' }}" alt="" class="img-fluid">
                            </a>
                            @endif
                            <h4><a href="javascript:;">{{ $title }}</a></h4>
                            <p>{{ $li1??''}}</p>

                        </div>
                    @else
                        <h1 class="mb-0">{{ $title }}</h1>
                    @endif
                    @if(isset($li2))
                        @php
                            $j = $li2;
                        @endphp
                        @dd((int)$li2);
                        <div class="rating mb-0">
                            @for($i=1;$i<=5;$i++)
                                @if((int)$i <= (int)$j)
                                <i class="fas fa-star filled"></i>
                                @else
                                <i class="fas fa-star"></i>
                                @endif

                            @endfor
                            <span class="d-inline-block average-rating"> ({{$li2??0}})</span>
                        </div>

                    @endif


            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->

<!-- Breadcrumb -->
{{-- <div class="page-banner student-bg-blk">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="profile-info-blk">
                    <a href="javascript:;" class="profile-info-img">
                        <img src="{{ URL::asset('/assets/img/students/profile-avatar.png')}}" alt="Profile Avatar" class="img-fluid">
                    </a>
                    <h4><a href="javascript:;">{{ $title }}</a><span>Beginner</span></h4>
                    <p>{{ $li1 }}</p>
                    <ul class="list-unstyled inline-inline profile-info-social">
                        <li class="list-inline-item">
                            <a href="javascript:;">
                                <i class="fa-brands fa-facebook"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:;">
                                <i class="fa-brands fa-twitter"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:;">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="javascript:;">
                                <i class="fa-brands fa-linkedin"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> --}}
<!-- /Breadcrumb -->

