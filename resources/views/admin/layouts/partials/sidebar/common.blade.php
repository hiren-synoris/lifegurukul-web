<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/') ?? 'javascript:void(0)' }}" class="brand-link" target="_blank">
        <img src="{{ !empty(config('settings.backend_favicon')) ? Storage::url('public/' . config('settings.backend_favicon')) : favicon_default() }}"
            alt="LifeGurukul Logo" class="brand-image elevation-3" style="opacity: .8">
        <span
            class="brand-text font-weight-light">{{ (!empty(config('settings.backend_favicon')) ? Storage::url('public/' . config('settings.backend_favicon')) : favicon_default()) ? ' ' : config('app.name') }}</span>
    </a>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <a href="{{ (auth()->user()->profile_picture)?Storage::url(auth()->user()->profile_picture): asset('front/img/icon/user-icon.svg')}}"
                    target="_blank" title="View Profile Picture"><img
                        src="{{ (auth()->user()->profile_picture)?Storage::url(auth()->user()->profile_picture): asset('front/img/icon/user-icon.svg') }}"
                        class="img-circle elevation-2" alt="Profile Picture"></a>
            </div>
            <div class="info">
                <a href="{{ route('profile.edit', ['profile' => auth()->id()]) ?? 'javascript:void(0)' }}"
                    class="d-block" title="Edit Profile">{{ auth()->user()->name ?? '' }}</a>
            </div>
        </div>
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        @php
            $user = new App\Models\User();
            $dropdown = new App\Models\Dropdown();
            $role = new App\Models\Role();
            $permission = new App\Models\Permission();
            $log = new App\Models\Log();
            $course = new App\Models\Course();
            $setting = new App\Models\Settings();
            $emailTemplate = new App\Models\Emailtemplate();
            $page = new App\Models\Page();
            $slider = new App\Models\Slider();
            $media = new App\Models\Media();
            $news_letter = new App\Models\NewsLetter();
            $blog = new App\Models\Blog();
            $faq = new App\Models\Faq();
            $wishlist = new App\Models\Wishlist();
            $course_orders = new App\Models\UserCourse();
            $public_forum = new App\Models\PublicForum();
            $notification = new App\Models\Notification();
        @endphp

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Dashboard Menu -->
                <li class="nav-item">
                    <a href="{{ url('backoffice/dashboard') }}"
                        class="nav-link {{ request()->is('backoffice/dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @can("browse_reports")
                @canany(['sales_report', 'login_learner_report', 'user_log_activity', 'user_signup_report','purchase_vs_course_complete_report'])

                {{-- @if(Auth::user()->hasRole('admin')) --}}
                    <li
                        class="nav-item {{ request()->is('backoffice/learner-report') || request()->is('backoffice/login-learner-report') || request()->is('backoffice/user-activity-logs') || request()->is('backoffice/user-signup-report') ? 'menu-is-opening menu-open' : '' }}">

                        <a href="javascript:void(0)"
                            class="nav-link {{ request()->is('backoffice/learner-report') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Reports <i class="right fas fa-angle-left"></i></p>
                        </a>

                        <ul class="nav nav-treeview child-sidebar-menu">
                            <!-- Admin Menu -->
                            @can("sales_report")
                            <li class="nav-item">
                                <a href="{{ url('backoffice/learner-report') }}"
                                    class="nav-link {{ request()->is('backoffice/learner-report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>Sales report</p>
                                </a>
                            </li>
                            @endcan
                            @can("login_learner_report")
                            <li class="nav-item">
                                <a href="{{ url('backoffice/login-learner-report') }}"
                                    class="nav-link {{ request()->is('backoffice/login-learner-report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>Login learner report</p>
                                </a>
                            </li>
                            @endcan
                            @can("user_log_activity")
                            <li class="nav-item">
                                <a href="{{ url('backoffice/user-activity-logs') }}"
                                    class="nav-link {{ request()->is('backoffice/user-activity-logs') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>User LogActivity</p>
                                </a>
                            </li>
                            @endcan
                            @can("user_signup_report")
                            <li class="nav-item">
                                <a href="{{ url('backoffice/user-signup-report') }}"
                                    class="nav-link {{ request()->is('backoffice/user-signup-report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>User Signup report</p>
                                </a>
                            </li>
                            @endcan
                            @can("purchase_vs_course_complete_report")
                            {{-- <li class="nav-item">
                                <a href="{{ url('backoffice/purchase-course-report') }}"
                                    class="nav-link {{ request()->is('backoffice/purchase-course-report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>Purchase v/s course complete report</p>
                                </a>
                            </li> --}}
                            @endcan
                            {{-- @can("purchase_vs_course_complete_report")
                            <li class="nav-item">
                                <a href="{{ url('backoffice/user-progress-report') }}"
                                    class="nav-link {{ request()->is('backoffice/user-progress-report') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sharp fa-regular fa-book"></i>
                                    <p>User Progress Report</p>
                                </a>
                            </li>
                            @endcan --}}
                        </ul>

                    </li>

                    {{-- @endif --}}
                @endcan
                @endcan


                @canany(['browse_users', 'browse_learners', 'browse_instructors', 'browse_subadmin','browse_user_coin'])
                    <!-- Users Menu Start-->
                    <li
                        class="nav-item {{ request()->routeIs('users.*') || request()->routeIs('logs.*') || request()->routeIs('learners.*') || request()->routeIs('instructors.*') ||  request()->routeIs('get_learner_history') || request()->routeIs('subadmin.*') ? 'menu-is-opening menu-open' : '' }}">

                        <a href="javascript:void(0)"
                            class="nav-link {{ request()->routeIs('users.*') || request()->routeIs('learners.*') ||request()->routeIs('get_learner_history') || request()->routeIs('instructors.*') || request()->routeIs('subadmin.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users <i class="right fas fa-angle-left"></i></p>
                        </a>

                        <ul class="nav nav-treeview child-sidebar-menu">
                            @can('browse_users', $user)
                                @if(Auth::user()->hasRole('admin'))
                                    <!-- Admin Menu -->
                                    <li class="nav-item">
                                        <a href="{{ url('backoffice/users') }}"
                                            class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                            <i class="nav-icon fas fa-user-tie"></i>
                                            <p>Admin Users</p>
                                        </a>
                                    </li>
                                @endif
                            @endcan

                            @can('browse_subadmin', $user)
                                <!-- Sub Admin Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/subadmin') }}"
                                        class="nav-link {{ request()->routeIs('subadmin.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-friends"></i>
                                        <p>Other Users</p>
                                    </a>
                                </li>
                            @endcan

                            @can('browse_instructors', $user)
                                <!-- Instructor Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/instructors') }}"
                                        class="nav-link {{ request()->routeIs('instructors.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-friends"></i>
                                        <p>Instructors</p>
                                    </a>
                                </li>
                            @endcan

                            @can('browse_learners', $user)
                                <!-- Learners Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/learners') }}"
                                        class="nav-link {{ request()->routeIs('learners.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                        <p>Learners</p>
                                    </a>
                                </li>
                            @endcan
                            {{-- @can('browse_user_coin', $user)
                                <!-- Learners Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/get-learner-history') }}"
                                        class="nav-link {{ request()->routeIs('get_learner_history') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                                        <p>Coins History</p>
                                    </a>
                                </li>
                            @endcan --}}
                            @can('browse_logs', $log)
                            <!-- Logs Menu -->
                            <li class="nav-item">
                                <a href="{{ url('backoffice/logs') }}"
                                    class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-regular fa-history"></i>
                                    <p>Admin Logs</p>
                                </a>
                            </li>
                        @endcan

                        </ul>
                    </li>
                    <!--/!! Users Menu End-->
                @endcan

                @canany(['browse_courses', 'browse_packages', 'browse_media'])
                    <!-- Content Menu Dropdown Start-->
                    <li
                        class="nav-item {{ request()->routeIs('courses.*') || request()->routeIs('course_orders.*') || request()->routeIs('wishlist.*')  || request()->routeIs('media.*') || request()->routeIs('course_reviews.*') || request()->routeIs('packages.*')  ? 'menu-is-opening menu-open' : '' }}">
                        <a href="javascript:void(0)"
                            class="nav-link {{ request()->routeIs('courses.*') || request()->routeIs('media.*') || request()->routeIs('packages.*')  ? 'active' : '' }}">
                            <i class="nav-icon fas fa-box"></i>
                            <p>Content <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview child-sidebar-menu">
                            @can('browse_courses', $course)
                                <!-- Course Menu -->
                                <li class="nav-item menu-left-side-margin">
                                    <a href="{{ url('backoffice/courses') }}"
                                        class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-chalkboard"></i>
                                        <p>Courses</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_packages')
                                <!-- Package Menu -->
                                <li class="nav-item menu-left-side-margin">
                                    <a href="{{ url('backoffice/packages') }}"
                                        class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-box-open"></i>
                                        <p>Packages</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_media', $media)
                                <!-- Media Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/media') }}"
                                        class="nav-link {{ request()->routeIs('media.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-camera"></i>
                                        <p>Media</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_course_orders', $course_orders)
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/course_orders') }}"
                                        class="nav-link {{ request()->routeIs('course_orders.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                        <p>Course/Package Orders</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_course_review', $news_letter)
                            <!-- new_letters Menu -->
                            <li class="nav-item">
                                <a href="{{ url('backoffice/course_reviews') }}"
                                    class="nav-link {{ request()->routeIs('course_reviews.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-regular fa-star"></i>
                                    <p>Course Reviews</p>
                                </a>
                            </li>
                            @endcan
                            @can('browse_wishlist', $wishlist)
                            <!-- FAQ Menu -->
                            <li class="nav-item">
                                <a href="{{ url('backoffice/wishlist') }}"
                                    class="nav-link {{ request()->routeIs('wishlist.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-heart"></i>
                                    <p>Wishlist</p>
                                </a>
                            </li>
                        @endcan

                        </ul>
                    </li>
                    <!--/!! Content Menu Dropdown End-->
                @endcan


            @can('browse_browse_admin_notifications')
            <!-- Permissions Menu -->
            <li class="nav-item menu-left-side-margin">
                <a href="{{ url('backoffice/send_notifications') }}"
                    class="nav-link {{ request()->routeIs('send_notifications.*') ? 'active' : '' }} mr-2">
                    <i class="nav-icon fa fa-envelope text-white " aria-hidden="true"></i>
                    <p>App Notification</p>
                </a>
            </li>
             @endcan
            @can('browse_wp_admin_notifications')
            <!-- Permissions Menu -->
            {{-- @dd(request()->routeIs('whatsapp') ) --}}
            <li class="nav-item menu-left-side-margin">
                <a href="{{ url('backoffice/whatsapp') }}"
                    class="nav-link {{ request()->routeIs('whatsapp') ? 'active' : '' }} mr-2">
                    <i class="nav-icon fa fa-envelope text-white " aria-hidden="true"></i>
                    <p>WhatsApp Notification</p>
                </a>
            </li>
             @endcan

             @can('browse_coupon')
             <li class="nav-item menu-left-side-margin">
                 <a href="{{ url('backoffice/coupons') }}"
                     class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}">
                     <i class="nav-icon fas fa-ticket-alt text-white" aria-hidden="true"></i>
                     <p>Coupon Codes</p>
                 </a>
             </li>
             @endcan


                <!-- @can('browse_admin_notifications', $notification)
                    <li class="nav-item">
                        <a href="{{ url('backoffice/send_notifications') }}"
                            class="nav-link {{ request()->routeIs('send_notifications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-regular fa-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan -->
                @canany(['browse_pages', 'browse_blog', 'browse_news_letters','browse_faq'])


                <li
                class="nav-item {{ request()->routeIs('pages.*') || request()->routeIs('blogs.*') || request()->routeIs('faq.*') || request()->routeIs('news_letters.*')  ? 'menu-is-opening menu-open' : '' }}">
                <a href="javascript:void(0)"
                    class="nav-link {{ request()->routeIs('pages.*') || request()->routeIs('blogs.*') || request()->routeIs('faq.*') || request()->routeIs('news_letters.*')  ? 'active' : '' }}">
                    <i class="nav-icon fas fa-box"></i>
                    <p>Others <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview child-sidebar-menu">



                @can('browse_pages', $page)
                <!-- Page Menu -->
                <li class="nav-item">
                    <a href="{{ url('backoffice/pages') }}"
                        class="nav-link {{ request()->routeIs('pages.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Pages</p>
                    </a>
                </li>
                @endcan

                @can('browse_blog', $blog)
                <!-- new_letters Menu -->
                <li class="nav-item {{ url('backoffice/blogs') }}">
                    <a href="{{ url('backoffice/blogs') }}"
                        class="nav-link {{ request()->routeIs('blogs.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-regular fa-envelope"></i>
                        <p>Blogs</p>
                    </a>
                </li>
                @endcan



            @can('browse_news_letters', $news_letter)
                <!-- new_letters Menu -->
                <li class="nav-item">
                    <a href="{{ url('backoffice/news_letters') }}"
                        class="nav-link {{ request()->routeIs('news_letters.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-regular fa-newspaper"></i>
                        <p>News Letters</p>
                    </a>
                </li>
            @endcan
            @can('browse_faq', $faq)
                <!-- FAQ Menu -->
                <li class="nav-item">
                    <a href="{{ url('backoffice/faq') }}"
                        class="nav-link {{ request()->routeIs('faq.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa fa-question-circle"></i>
                        <p>FAQ</p>
                    </a>
                </li>
            @endcan

                </ul>
            </li>
            @endcan
            @canany(['browse_tutorial', 'browse_support', 'browse_contact'])


            <li
            class="nav-item {{  request()->routeIs('contact.*') || request()->routeIs('support-ticket.*')  || request()->routeIs('tutorial.*')  ? 'menu-is-opening menu-open' : '' }}">
            <a href="javascript:void(0)"
                class="nav-link {{  request()->routeIs('contact.*') || request()->routeIs('support-ticket.*') || request()->routeIs('tutorial.*')  ? 'active' : '' }}">

                <i class="fas fa-hands-helping"></i>
                <p>Help <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview child-sidebar-menu">

                @can('browse_tutorial', $setting)
                    <li class="nav-item">
                        <a href="{{ url('backoffice/tutorial') }}"
                            class="nav-link {{ request()->routeIs('tutorial.*') ? 'active' : '' }}">
                            <i class="nav-icon  fa fa-video" aria-hidden="true"></i>
                            <p>Instructor Tutorial</p>
                        </a>
                    </li>
                 @endcan
                 @can('browse_support')
                 <!-- Contact Us Menu -->
                 <li class="nav-item">
                     <a href="{{ url('backoffice/support-ticket') }}"
                         class="nav-link {{ request()->routeIs('support-ticket.*') ? 'active' : '' }}">
                         <i class="nav-icon fas fa-ticket-alt"></i>
                         <p>Support-Ticket</p>
                     </a>
                 </li>
                 @endcan
                 @can('browse_contact')
                 <!-- Contact Us Menu -->
                 <li class="nav-item">
                     <a href="{{ url('backoffice/contact') }}"
                         class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}">
                         <i class="nav-icon fas fa-id-badge"></i>
                         <p>Contact Us</p>
                     </a>
                 </li>
                 @endcan


            </ul>
        </li>
        @endcan


                @canany(['browse_dropdowns', 'browse_slider', 'browse_settings',"browse_country","browse_state","browse_city"])
                    <!-- Configuration Menu Start-->
                    <li
                        class="nav-item {{ request()->routeIs('dropdowns.*') || request()->routeIs('roles.*') ||request()->routeIs('slider.*') || request()->routeIs('settings.*') || request()->routeIs('country') || request()->routeIs('state') || request()->routeIs('city') || request()->routeIs('permissions.*') ? 'menu-is-opening menu-open' : '' }}">

                        <a href="javascript:void(0)"
                            class="nav-link {{ request()->routeIs('dropdowns.*') || request()->routeIs('slider.*') || request()->routeIs('settings.*') || request()->routeIs('permissions.*') || request()->routeIs('country')  || request()->routeIs('state') || request()->routeIs('city') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Configurations <i class="right fas fa-angle-left"></i></p>
                        </a>

                        <ul class="nav nav-treeview child-sidebar-menu">

                            @can('browse_dropdowns', $dropdown)
                                <!-- Dropdown Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/dropdowns') }}"
                                        class="nav-link {{ request()->routeIs('dropdowns.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list-ul" ></i>
                                        <p>Dropdowns</p>
                                    </a>
                                </li>
                            @endcan

                            {{-- @can('browse_email_templates', $emailTemplate)
                                <!-- Email Templates Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/email_templates') }}"
                                        class="nav-link {{ request()->routeIs('email_templates.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-regular fa-envelope"></i>
                                        <p>Email Templates</p>
                                    </a>
                                </li>
                            @endcan --}}

                            @can('browse_slider', $slider)
                                <!-- slider Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/slider') }}"
                                        class="nav-link {{ request()->routeIs('slider') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-regular fa-stream"></i>
                                        <p>Sliders</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_country', $slider)
                                <!-- slider Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/country') }}"
                                        class="nav-link {{ request()->routeIs('country') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-city" ></i>
                                        <p>Country</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_state', $slider)
                                <!-- slider Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/state') }}"
                                        class="nav-link {{ request()->routeIs('state') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-city" ></i>
                                        <p>State</p>
                                    </a>
                                </li>
                            @endcan
                            @can('browse_city', $slider)
                                <!-- slider Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/city') }}"
                                        class="nav-link {{ request()->routeIs('city') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-city" ></i>
                                        <p>City</p>
                                    </a>
                                </li>
                            @endcan
                            @if(Auth::user()->hasRole('admin'))
                                <!-- Roles Menu -->
                                <li class="nav-item menu-left-side-margin">
                                    <a href="{{ url('backoffice/roles') }}"
                                        class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-tag"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                                <!-- Permission Menu -->
                                <li class="nav-item menu-left-side-margin">
                                    <a href="{{ url('backoffice/permissions') }}"
                                        class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-tag"></i>
                                        <p>Permissions</p>
                                    </a>
                                </li>
                            @endif
                            @can('browse_settings', $setting)
                                <!-- Settings Menu -->
                                <li class="nav-item">
                                    <a href="{{ url('backoffice/settings') }}"
                                        class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-cog"></i>
                                        <p>Settings</p>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>
                    <!--/!! Configuration Menu End-->
                @endcan

                {{-- @can('browse_notifications', $notification)
                    <!-- new_letters Menu -->
                    <li class="nav-item">
                        <a href="{{ url('backoffice/notifications') }}"
                            class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-regular fa-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @endcan --}}
            </ul>
        </nav>
    </div>
</aside>
