<!-- Footer -->
<footer class="footer">
    <!-- Footer Top -->
    <div class="footer-top aos aos-init aos-animate" data-aos="fade-up">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <!-- Footer Widget -->
                    <div class="footer-widget footer-about">
                        <div class="footer-logo">
                                <img src="{{ !empty(config('settings.logo')) ? Storage::url(config('settings.logo')) : logo_default()}}" alt="logo">
                        </div>
                        <div class="footer-about-content">
                        <p>{{ !empty(config('settings.footer_about_content')) ? config('settings.footer_about_content') : "" }}</p>
                        </div>
                    </div>
                    <!-- /Footer Widget -->
                </div>

                <div class="col-lg-2 col-md-3">
                    <div class="footer-widget footer-menu">
                            <h2 class="footer-title">Quick Link</h2>
                            <ul>
                                <li><a href="{{ url('/') }}">Home</a></li>
                                {{-- $pagesKeyValueArray comes from 'mainlayout.blade.php file.' --}}
                                @if(is_menu_enable('about-us',$pagesKeyValueArray))
                                    <li><a href="{{ cms_menu_url('about-us', $pagesKeyValueArray) }}">About Us</a></li>
                                @endif
                                <li><a href="{{url('course')}}">Courses</a></li>
                                <li><a href="{{ url('faq') }}">FAQ</a></li>
                                @if (!auth()->guard('learner')->user())
                                    <li><a style="cursor: pointer" data-toggle="modal" class="mobileLogin"
                                    data-step="{{ session()->has('mobile') ? '2' : '1' }}">Login</a></li>
                                @else
                                    <li><a href="{{ url('profile') }}">Profile</a></li>
                                    <li><a href="{{ url('dashboard') }}"> Dashboard</a></li>
                                    <li><a href="{{ url('logout') }}">Logout</a></li>
                                @endguest


                        </ul>
                    </div>
                </div>

                <div class="col-lg-4 col-md-5">
                      <!-- Footer Widget -->
                    <div class="footer-widget footer-contact">
                        <h2 class="footer-title">Contact Us</h2>
                        <!-- <div class="news-letter">
                            <form>
                                <input type="text" class="form-control" placeholder="Enter your email address" name="email">
                            </form>
                        </div> -->
                        <div class="footer-contact-info">
                            <div class="footer-address">
                                <!-- <img src="{{ URL::asset('/front/img/icon/icon-20.svg')}}" alt="" class="img-fluid"> -->
                                @if(!empty(config('settings.footer_addr_company_name')))<h6 class="font-weight-bold">{{config('settings.footer_addr_company_name')}}</h6>@endif
                                @if(!empty(config('settings.footer_addr_company_addr')))<p class="text-justify">{{config('settings.footer_addr_company_addr')}}</p>@endif
                            </div>
                            @if(!empty(config('settings.footer_contact_email')))
                            <p>
                                <a href="mailto:info@lifegurukul.app" class="d-flex align-items-center">  <i class="fa-regular fa-envelope"></i> {{config('settings.footer_contact_email')}}</a>

                            </p>
                            @endif
                            @if(!empty(config('settings.contact_no')))
                            <p>
                                <a href="tel:+91 72111 28282" class="d-flex align-items-center"> <i class="fa-solid fa-mobile-screen-button"></i> {{config('settings.contact_no')}}</a>
                            </p>
                            @endif
                            @if(!empty(config('settings.footer_contact_insta_id')))
                            <p>
                                <a href="{{!empty(config('settings.instagram_url')) ? config('settings.instagram_url') : 'javascript:void(0)'}}" class="d-flex align-items-center"> <i class="fa-brands fa-instagram"></i> {{config('settings.footer_contact_insta_id')}}</a>
                            </p>
                            @endif
                            @if(!empty(config('settings.footer_contact_facebook_id')))
                            <p class="mb-0">
                                <a href="{{ !empty(config('settings.facebook_url')) ? config('settings.facebook_url') : 'javascript:void(0)' }}" class="d-flex align-items-center"> <i class="fa-brands fa-facebook"></i> {{config('settings.footer_contact_facebook_id')}}</a>
                            </p>
                            @endif
                        </div>
                    </div>
                    <!-- /Footer Widget -->
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-widget footer-menu">
                        <h2 class="footer-title">Opening Hours</h2>

                            <div class="footer-contact-info">
                                <div class="footer-address">
                                    <h6> Monday - Friday</h6>
                                    <p> <i class="fa-regular fa-clock"></i>  {{ config('settings.opening_hours_weekdays') ?? '' }} AM - {{ config('settings.closing_hours_weekdays') ?? '' }} PM </p>
                                </div>
                                <div class="footer-address">
                                    <h6> Saturday </h6>
                                    <p> <i class="fa-regular fa-clock"></i>  {{ config('settings.opening_hours_sat') ?? '' }} AM - {{ config('settings.closing_hours_sat') ?? '' }} PM </p>
                                </div>
                                <div class="footer-address">
                                    <h6> Sunday </h6>
                                    <p> <i class="fa-regular fa-clock"></i> Closed</p>
                                </div>
                            </div>
                    </div>
                </div>
        </div>
    </div>
</div>
<!-- /Footer Top -->

<!-- Footer Bottom -->
<div class="footer-bottom">
    <div class="container">

        {{-- <!-- Copyright -->@dd($pagesKeyValueArray) --}}
        <div class="copyright">
            <div class="row align-items-center">
                <div class="col-md-3 col-lg-4">
                    <div class="privacy-policy">
                        <ul>
                            @if(is_menu_enable('term-condition',$pagesKeyValueArray))
                                <li><a href="{{ cms_menu_url('term-condition', $pagesKeyValueArray) }}" target="_blank">Terms</a></li>
                            @endif
                            @if(is_menu_enable('privacy-policy',$pagesKeyValueArray))
                                <li><a href="{{ cms_menu_url('privacy-policy', $pagesKeyValueArray) }}" target="_blank">Privacy</a></li>
                            @endif
                            @if(is_menu_enable('refund-policy',$pagesKeyValueArray))
                                <li><a href="{{ cms_menu_url('refund-policy', $pagesKeyValueArray) }}" >Refund</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="right-reserved">
                        <p class="mb-0 text-center">&copy; {{ date('Y') }} {{env('APP_NAME')}}. All rights reserved.</p>
                    </div>
                </div>
                <div class="col-md-3 col-lg-4">
                                <div class="social-incon">
                                    <ul>
                                        <li><a target='_blank' href="{{ !empty(config('settings.facebook_url')) ? config('settings.facebook_url') : 'javascript:void(0)' }}"><i class="fa-brands fa-facebook-f"></i></a></li>
                                        <li>
                                            <a target='_blank' href="{{!empty(config('settings.instagram_url')) ? config('settings.instagram_url') : 'javascript:void(0)'}}"><i class="fa-brands fa-instagram"></i></a>
                                        </li>
                                        <li>
                                            <a target='_blank' href="{{!empty(config('settings.youtube_url')) ? config('settings.youtube_url') : 'javascript:void(0)'}}"><i class="fa-brands fa-youtube"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

            </div>
        </div>
        <!-- /Copyright -->

    </div>
</div>
<!-- /Footer Bottom -->

</footer>
<!-- /Footer -->
