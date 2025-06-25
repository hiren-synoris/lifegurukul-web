<?php

use PDF as MPDF;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppStoreController;
use App\Http\Controllers\admin\FaqController;
use App\Http\Controllers\admin\LogController;
use App\Http\Controllers\admin\BlogController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\LoginController;
use App\Http\Controllers\admin\MediaController;
use App\Http\Controllers\admin\RolesController;
use App\Http\Controllers\admin\SlideController;
use App\Http\Controllers\admin\CouponController;
use App\Http\Controllers\admin\CourseController;
use App\Http\Controllers\admin\ExportController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\SliderController;
use App\Http\Controllers\admin\ChapterController;
use App\Http\Controllers\admin\LearnerController;
use App\Http\Controllers\admin\PackageController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\DropdownController;
use App\Http\Controllers\admin\SettingsController;
use App\Http\Controllers\admin\TutorialController;
use App\Http\Controllers\admin\WishlistController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\BulkDeleteController;
use App\Http\Controllers\admin\ChapterInfoController;
use App\Http\Controllers\admin\InstructorsController;
use App\Http\Controllers\admin\PermissionsController;
use App\Http\Controllers\admin\PublicForumController;
use App\Http\Controllers\admin\StateManageController;
use App\Http\Controllers\admin\CourseOrdersController;
use App\Http\Controllers\admin\CountryManageController;
use App\Http\Controllers\admin\CoursePricingController;
use App\Http\Controllers\admin\CourseReviewsController;
use App\Http\Controllers\admin\EmailtemplateController;
use App\Http\Controllers\admin\NotificationsController;
use App\Http\Controllers\admin\DropdownOptionController;
use App\Http\Controllers\admin\ForgotPasswordController;
use App\Http\Controllers\admin\AdminNotificationsController;
use App\Http\Controllers\admin\CityManageController;

Auth::routes();
/**
 * Below route is for Admin Panel
 */


 Route::get('check_invoice', function () {
 $invoiceData = get_invoice_data('693597');

        $pdfPath = storage_path('app/public/invoice');
        $fileName = 'invoice_145996.pdf';


            ini_set('max_execution_time', 180);
            //  return view('front.student.view_invoice_device', compact('invoiceData'));
            $pdf = MPDF::loadView('front.student.view_invoice_device', compact('invoiceData'));

            $pdf->save($pdfPath . '/' . $fileName);
});
Route::prefix('backoffice')->group(function () {
    Route::get('/', function () {
        return redirect('backoffice/login');
    });

    Route::get('logout', function () {
        $user = Auth::user();
        $log = new Log();
        $log->message = $user->name . " logged out";
        $user->logs()->save($log);
        Auth::logout();
        return redirect('backoffice/login');
    });

    Route::get("renewing-subscriptions",[CoursePricingController::class,'index'])->name("renewing_subscriptions");
    Route::get("ios",[AppStoreController::class,'index']);

    Route::resource('/login', LoginController::class);
    // Route::resource('/register', RegisterController::class);
    Route::resource('/forgot-password', ForgotPasswordController::class);

    Route::middleware(['web', 'admin_auth'])->group(function () {
        Route::resource('/send_notifications', AdminNotificationsController::class);
        Route::get('/send-wp-notifications', [AdminNotificationsController::class, "sendWpNotifications"])->name("send_wp_notifications");
        Route::post('/send-wp-mssage', [AdminNotificationsController::class, "SendWpMessage"])->name("send_wp_mssage");
        Route::get('/manual-notify', [AdminNotificationsController::class, "manualNotifyHistory"])->name("manual_notify_history");
        Route::get('/wp-notify', [AdminNotificationsController::class, "wpNotifyHistory"])->name("wp_notify_history");
        Route::get('/notify-learners', [AdminNotificationsController::class, "notifyLearners"])->name("notify_learners");
        Route::get('/fetch-campaigns', [AdminNotificationsController::class, "fetchCampaigns"])->name("fetch_campaigns");
        Route::get('/get-template', [AdminNotificationsController::class, "getTemplate"])->name("get_template");
        Route::get('/get-course', [AdminNotificationsController::class, "getCourse"])->name("get_course");
        Route::get('/whatsapp', [AdminNotificationsController::class, "whatsapp"])->name("whatsapp");

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search-course_dashboard', [DashboardController::class, 'searchCourseDashboard']);
        Route::get('/search-instructor_dashboard', [DashboardController::class, 'searchinstructorDashboard']);
        Route::get('/get-user-course', [DashboardController::class, 'getUserCourse'])->name("get_user_course");
        Route::get('/get-instructor', [DashboardController::class, 'getInstructor'])->name("get_instructor");
        Route::get('/get-percentage_count', [DashboardController::class, 'getPercentageCount'])->name("get_percentage_count");
        Route::get('/userWiseCourse-index', [DashboardController::class, 'userWiseCourse'])->name('userWiseCourse.index');
        Route::get('/userWiseCourse', [DashboardController::class, 'get_userWiseCourse'])->name('userWiseCourse');
        Route::post('{type}/bulk_del', [BulkDeleteController::class, 'bulk_Delete']);
        Route::get('/instructureWiseSoldCourse', [DashboardController::class, 'instructureWiseSoldCourse'])->name('instructureWiseSoldCourse');
        Route::get('/get-course-sold', [DashboardController::class, 'getCourseSold'])->name('get_course_sold');
        Route::get('/get-payment-status', [DashboardController::class, 'getPaymentStatus'])->name('get_payment_status');
        //Users Route
        Route::resource('/users', UserController::class);
        Route::controller(UserController::class)->group(function () {
            Route::get('get_users', 'get_users');
            Route::get('get_users_deleted', 'get_users_deleted');
            Route::get('/users/restore/{id}', 'restore');
            Route::post('users/restore_all', 'restore_all');
            Route::post('bulk-hard-user-delete', 'bulkHardUserDelete')->name("bulk_hard_user_delete");
        });

        //Blog Route
        Route::resource('/blogs', BlogController::class);
        Route::controller(BlogController::class)->group(function () {
            Route::get('get-blogs', 'getBlogs');
            Route::get('get-blogs-deleted', 'getBlogsDeleted');
            Route::get('/blogs/restore/{id}', 'restore');
            Route::post('blogs/restore_all', 'restore_all');
            Route::delete('blogs/delete/{id}', 'delete')->name('blogs.delete');
            Route::post('blogs/bulk_hard_del', 'bulk_Hard_Delete');
        });

        // Wishlist
        Route::resource('/wishlist', WishlistController::class);
        Route::controller(WishlistController::class)->group(function () {
            Route::get('get-wishlist/{user_id?}/{course_id?}', 'getWishlist');
            Route::post('export-wishlists', 'exportWishlists')->name('wishlist.export');
        });
        Route::get('search-learner', [WishlistController::class, 'searchLerner'])->name("search_lerner");
        Route::get('search-course', [WishlistController::class, 'searchCourse']);

        //FAQ Routes
        Route::resource('faq', FaqController::class);
        Route::controller(FaqController::class)->group(function () {
            Route::get('get_faqs', 'get_faqs');
            Route::get('get_faqs_deleted', 'get_faqs_deleted');
            Route::get('faq/restore/{id}', 'restore');
            Route::post('faq/restore_all', 'restore_all');
            Route::delete('faq/delete/{id}', 'delete')->name('faq.delete');
            Route::post('faq/bulk_hard_del', 'bulk_Hard_Delete');
            Route::post('faq/{id}/delete-media','deleteMedia')->name('faq.delete-media');
        });
        //
        //Learners Route
        Route::resource('/learners', LearnerController::class);

        Route::get('search-city', [LearnerController::class, 'searchCity'])->name("search_city");
        Route::get('get-learner-course-orders', [LearnerController::class, 'getCourseOrders'])->name("get_learner_course_orders");

        Route::controller(LearnerController::class)->group(function () {
            Route::get('get-learner', 'getLearners')->name("get_learner");
            Route::get('get-learner-history', 'getLearnerHistory')->name("get_learner_history");

            Route::delete('delete-coin-history/{id}', 'deleteCoinHistory')->name("delete_coin_history");
            Route::get('get-coin', 'getCoin')->name("get_coin");
            Route::get('get-learner-activity-log', 'getLearnerActivityLog')->name("get_learner_activity_log");
            // Route::get('get-learner-history', 'getLearnerHistory')->name("get_learner_history");

            Route::get('get-learner-deleted', 'getLearnersDeleted');
            Route::get('/learners/restore/{id}', 'restore');
            Route::post('learners/restore_all', 'restore_all');
            Route::delete('learners/delete/{id}', 'delete')->name('learners.delete');
            Route::post('learners/import/{courseId?}', 'importNew');
            Route::post('learners/import-course/{courseId?}', 'importCourse')->name("import_course");
            Route::post('learners/import-package/{packageId?}', 'importCourse')->name("import_course");
            // Route::post('learners/import/{courseId?}', 'import');
            // Route::post('learners/import/{courseId?}', 'NewImportLearners');
            Route::post('learners/importlearner', 'importlearner');
            Route::post('learners/search', 'search');
            // leraner logged device list
            Route::get('get-logged-device/{id}', 'getLoggedDevice')->name('get.logged.device');
            Route::delete('logged-device/delete/{id}', 'deviceDelete')->name('logged-device.delete');
            Route::get('logged-device/block', 'deviceBlock')->name('logged_device_block');
            Route::post('learners/bulk-hard-delete', 'bulkHardDelete')->name('bulk_hard_delete');
            Route::post('learnerEnrollCourse', 'learnerEnrollCourse')->name('learner_enroll_course');
            Route::post('learner-manual-enrollCourse', 'learnerManualEnrollCourse')->name('learner_manual_enroll_course');
            Route::post('learner-manual-plan-assign', 'learnerManualPlanAssign')->name('learner_manual_plan_assign');
            Route::post('learner-generate-url', 'LearnerGenerateUrl')->name('learner_generate_url');
            Route::get('learner-logs', 'learnerLogs')->name('learner_logs');
            Route::POST('add-coins', 'addcoin')->name('add_coins');
            Route::POST('minus-coin', 'minusCoin')->name('minus_coin');
            Route::get('user-activity-logs', 'userActivityLogs')->name('user-activity-logs');
            Route::get('user-logs-activity-export', 'userLogsActivityExport')->name('user-logs-activity-export');
            Route::get('learner_status', 'learnerStatus')->name("learner_status");
        });
        //Sub Admin Route
        Route::resource('/subadmin', UserController::class);
        Route::controller(UserController::class)->group(function () {
            Route::get('get_subadmin', 'get_users');
            Route::get('get_subadmin_deleted', 'get_users_deleted');
            Route::get('subadmin/restore/{id}', 'restore');
            Route::post('subadmin/restore_all', 'restore_all');
            Route::delete('subadmin/delete/{id}', 'delete')->name('subadmin.delete');
        });

        //Instructor Route new
        Route::resource('/instructors', InstructorsController::class);
        Route::controller(InstructorsController::class)->group(function () {
            Route::get('get_instructor', 'get_instructor');
            Route::get('get_instructor_deleted', 'get_instructor_deleted');
            Route::get('instructors/restore/{id}', 'restore');
            Route::post('instructors/restore_all', 'restore_all');
            Route::delete('instructors/delete/{id}', 'delete')->name('instructors.delete');
            Route::post('instructors/bulk-hard-del', 'bulkHardDel')->name('instructors.bulk_hard_del');
            //Route::post('instructors/bulk-del', 'bulkDel')->name('instructors.bulk_del');
        });

        /* Roles */
        Route::resource('/roles', RolesController::class);
        Route::controller(RolesController::class)->group(function () {
            Route::get('/roles/restore/{id}', 'restore');
            Route::get('get_roles', 'get_roles');
            Route::get('get_roles_deleted', 'get_roles_deleted');
            //Route::post('roles/bulk_del', 'bulk_del');
            Route::post('roles/restore_all', 'restore_all');
            Route::delete('roles/delete/{id}', 'delete')->name('roles.delete');
            Route::post('roles/bulk_hard_del', 'bulk_Hard_Delete');
        });

        /* News Letter */
        Route::resource('/news_letters', \App\Http\Controllers\admin\NewsletterController::class);
        Route::controller(\App\Http\Controllers\admin\NewsletterController::class)->group(function () {
            Route::get('/news_letters/restore/{id}', 'restore');
            Route::get('get-news-letters', 'getNewsLetters');
            Route::get('get-news-letters-deleted', 'getNewsLettersDeleted');
            Route::post('news_letters/restore_all', 'restore_all');
            Route::delete('news_letters/delete/{id}', 'delete')->name('news_letters.delete');
            Route::post('news_letters/bulk_hard_del', 'bulk_Hard_Delete');
        });

        /* Contact us */
        Route::resource('/contact', \App\Http\Controllers\admin\ContactController::class);
        Route::controller(\App\Http\Controllers\admin\ContactController::class)->group(function () {
            Route::get('get-contacts', 'getContacts');
            Route::post('contact/bulk_hard_del', 'bulk_Hard_Delete');
            Route::post('contact-reply', 'ReplyToContact')->name('contact.reply');
        });

        // Support tickit

        Route::resource('/support-ticket', \App\Http\Controllers\admin\SupportController::class);
        Route::controller(\App\Http\Controllers\admin\SupportController::class)->group(function () {
            Route::get('get-suports', 'getSupports')->name("get_support");
            Route::post('support-ticket/bulk_hard_del', 'bulk_Hard_Delete');
            Route::post('support-reply', 'supportReply')->name('support.reply');
            Route::get('read-notifications/{id}', 'readNotification')->name('read_notifications');
            Route::get('read-contacts/{id}', 'readContact')->name('read_contact');
            Route::get('read-chat-reply/{id}', 'readChatReply')->name('read_chat_reply');
            Route::get('read-all-support-ticket', 'readAllSupportTicket')->name('read_all_support_ticket');
        });

        /* Permissions */
        Route::resource('/permissions', PermissionsController::class);
        Route::controller(PermissionsController::class)->group(function () {
            Route::get('/permissions/restore/{id}', 'restore');
            Route::get('get_permissions', 'get_permissions');
            Route::get('get_permissions_deleted', 'get_permissions_deleted');
            //Route::post('permissions/bulk_del','bulk_del']);
            Route::post('permissions/restore_all', 'restore_all');
            Route::delete('permissions/delete/{id}', 'delete')->name('permissions.delete');
            Route::post('permissions/bulk_hard_del', 'bulk_Hard_Delete');
        });

        /* Settings */
        Route::resource('/settings', SettingsController::class);
        Route::controller(SettingsController::class)->group(function () {
            Route::get('/settings/restore/{id}', 'restore');
            Route::get('get_settings/{type?}', 'get_settings');
            Route::get('get_settings_deleted', 'get_settings_deleted');
            Route::post('settings/bulk_del', 'bulk_del');
            Route::post('settings/restore_all', 'restore_all');
            // Route::post('setting-update', 'update');
            Route::delete('settings/delete/{id}', 'delete')->name('settings.delete');
            Route::post('settings/bulk_hard_del', 'bulk_Hard_Delete');
        });
        /* email template */
        // Route::resource('email_templates', EmailtemplateController::class);
        Route::controller(EmailtemplateController::class)->group(function () {
            Route::get('get-email-templates', 'getEmailTemplates');
            Route::get('get-email-templates-deleted', 'getEmailTemplatesDeleted');
            Route::get('email-templates/restore/{id}', 'restore');
            Route::post('email-templates/restore_all', 'restore_all');
            Route::delete('email-templates/delete/{id}', 'delete')->name('email-templates.delete');
            Route::post('email-templates/bulk_hard_del', 'bulk_Hard_Delete');
        });
        /* logs */
        Route::resource('logs', LogController::class);
        Route::get('get_logs', [LogController::class, 'get_logs']);
        /* slider */
        Route::resource('slider', SliderController::class);
        Route::controller(SliderController::class)->group(function () {
            Route::get('get_slider', 'get_slider');
            Route::delete('backoffice/slider/delete/{id}', 'delete')->name('slider.delete');
        });

        /* Dropdown Route */
        Route::resource('/dropdowns', DropdownController::class);
        Route::controller(DropdownController::class)->group(function () {
            Route::get('get-dropdowns', 'getDropdowns');
            Route::get('get-dropdowns-deleted', 'geDropdownsDeleted');
            Route::get('dropdowns/restore/{id}', 'restore');
            Route::post('dropdowns/restore_all', 'restore_all');
            Route::delete('dropdowns/delete/{id}', 'delete')->name('dropdowns.delete');
            Route::post('dropdowns/bulk_hard_del', 'bulk_Hard_Delete');
        });

        /* Dropdown Options Route */
        Route::controller(DropdownOptionController::class)->group(function () {
            Route::get('dropdown_options/{id}', 'index');
            Route::get('dropdown_options/{id}/create', 'create');
            Route::post('dropdown_options/{id}', 'store');
            Route::get('dropdown_options/{option_id}/view', 'show');
            Route::get('dropdown_options/{option_id}/edit', 'edit');
            Route::put('dropdown_options/{option_id}', 'update');
            Route::delete('dropdown_options/{option_id}', 'destroy')->name('dropdown_options.destroy');
            Route::get('dropdown_options/restore/{option_id}', 'restore');
            Route::post('dropdown_options/{id}/restore_all', 'restore_all');
            Route::post('dropdown_options/{id}/bulk_del', 'bulk_del');
            Route::get('get_dropdown_options/{id}', 'get_dropdown_options');
            Route::get('get_dropdown_options_deleted/{id}', 'get_dropdown_options_deleted');
            Route::delete('dropdown_options/delete/{option_id}', 'delete')->name('dropdown_options.delete');
            Route::post('dropdown_options/{id}/bulk_hard_del', 'bulk_Hard_Delete');
        });
        /**Courses Route */
        Route::resource('courses', CourseController::class);
        Route::controller(CourseController::class)->group(function () {
            //In this route first parameter is "URL" & second parameter is the methodName() for this controller
            // Route::get('get-courses', 'getCourses');
            Route::get('ask-approval/{course_id}', 'askforApproval')->name('ask.approval');
            Route::get('get-courses/{user_id?}', 'getCourses');
            // Route::get('get-wishlist/{user_id?}/{course_id?}', 'getWishlist');
            Route::get('get-courses-deleted', 'getCoursesDeleted');
            Route::get('courses/restore/{id}', 'restore');
            Route::post('courses/restore_all', 'restore_all');


            Route::controller(PublicForumController::class)->group(function () {
                Route::get('get-public-forum', 'getPublicForum')->name("get_public_forum");
                Route::get('courses/{id}/public-forum', 'index')->name('courses.public-forum');
                Route::post('/public-forum', 'store');
                Route::delete('/public-forum-delete-question/{id}', 'deleteQuestion')->name("public_forum_delete_question");
                Route::delete('/public-forum-delete-reply/{id}', 'deleteReply')->name("public_forum_delete_reply");
                Route::post('reply-public-forum', 'replyPublicForum')->name("reply_public_forum");
                Route::post('get-question', 'getQuestion')->name("get_question");
            });

            // course Learner List
            Route::get('courses/{id}/learners', 'courseLearners')->name('courses.learners');
            Route::get('get_learners/{courseId}', 'get_learners');
            Route::get('get_learners_package/{courseId}', 'getLearnersPackage');

            Route::delete('courses/learners/delete/{id}', 'learner_delete')->name('courses.learners.delete');
            Route::delete('learners/courses/delete/{id}', 'learnerCourseDelete')->name('learners.courses.delete');
            Route::post('learners/courses/edit-expiration-date/{id}', 'learnerCourseEditExpirationDate')->name('learners.courses.editExpirationDate');
            Route::get('course/get_addable_learners/{courseId}', 'get_addable_learners')->name('course.get_addable_learners');
            Route::get('addable/learners/{courseId}/{learnerId}/{planId?}', 'addable_learners')->name('addable.learners');
            Route::get('delete_addable/learners/{id}', 'delete_addable_learners')->name('delete_addable.learners');
            Route::delete('restore_addable/learners/{id}', 'restore_addable_learners')->name('restore_addable.learners');

            Route::get('import-assets', 'importFromAsset')->name('import-assets');
            Route::post('update-type/{id}', 'updateType')->name('update.type');
            Route::put('course-seo-update/{id}', 'seoUpdate')->name('seo.update');

            //Edit course route for different actions
            Route::get('courses/{id}/builder/{chapterId?}', 'courseBuilder')->name('courses.builder');
            Route::get('courses/{id}/preview', 'coursePreview')->name('courses.preview');

            //Ordering
            Route::post('builder-order', 'builderOrdering')->name('builder.order');

            //Package
            Route::get('courses/{id}/package', 'coursePackage')->name('courses.package');
            Route::get('get-packages/{id}', 'getCoursesForPackages')->name('courses.package.list');
            Route::get('add-course-to-package/{course_id}/{package_id}', 'addCourseToPackage')->name('add.course.package');
            Route::get('delete-course-to-package/{course_id}/{package_id}', 'removeCourseToPackage')->name('delete.course.package');

            Route::put('courses/{id}/builder/{chapterId?}', 'courseBuilderUpdate')->name('courses.builder.update');

            Route::post('course-image', 'image');
            Route::post('course-thumbnail', 'thumbnailImage')->name("course.thumbnail");

            // add multiple package
            Route::post('courses/{id}/package/bulk_add', 'addMultipleCourseToPackage')->name('courses.package.add');
            // remove multiple package
            Route::post('courses/{id}/package/bulk_delete', 'deleteMultipleCourseToPackage')->name('courses.package.delete');

            //Delete Course
            Route::delete('courses/delete/{id}', 'delete')->name('courses.delete');
            Route::post('courses/bulk_hard_del', 'bulk_Hard_Delete');

            Route::post('video-title', 'video_title');
            Route::get('reset-learner/{courseId?}/{learnerId?}', 'resetLearner')->name('reset.learner');
            Route::get('statuscheck/{videoid}', 'statuscheck')->name('video.status');
            Route::post('intro-video', 'introVideo')->name('intro_video');
        });

        Route::resource('course-prices', CoursePricingController::class);
        Route::controller(CoursePricingController::class)->group(function () {
            Route::get('get-course-plans/{id}', 'getCoursePlans');
            Route::post('course-plan-sortable', 'coursePlanSortable');
            Route::post('get-course-plan', 'getCoursePlanForEdit')->name('course-prices.get-plan');
            Route::delete('course-prices/delete/{id}', 'delete')->name('course-prices.delete');
            Route::post('set-plan-for-course', 'setPlanForCourse')->name('set-plan.for.course');
        });

        /**Chapter Route */
        Route::resource('chapters', ChapterController::class);
        Route::controller(ChapterController::class)->group(function () {
            Route::delete('remove-chapter/{id}/{parentId?}', 'removeChapter')->name('remove.chapter');
            // add multiple Chapter from Asset Library
            Route::post('courses/{id}/bulk_add', 'addMultipleCourseFromLibrary')->name('courses.asset.add');
            Route::get('pdf_download/{id}', 'pdf_download')->name('courses.pdf_download');
            Route::get('files-upload', 'uploadLarge');
            Route::post('files-upload-large', 'uploadLargeFiles')->name('files.upload.large');
            Route::post('files-upload-larges', 'uploadLargeFiless')->name('files.upload.larges');
            // Route::middleware(['upload.throttle'])->post('files-upload-large', 'uploadLargeFiles')->name('files.upload.large');
        });

        /**Chapter Info Route */
        Route::resource('chapters-info', ChapterInfoController::class);

        //Package Route
        Route::resource('packages', PackageController::class);
        Route::controller(PackageController::class)->group(function () {
            Route::get('get-packages', 'getPackages');
            Route::get('get-packages-deleted', 'getPackagesDeleted');
            Route::get('packages/restore/{id}', 'restore');
            Route::post('packages/restore_all', 'restore_all');
            Route::put('package-seo-update/{id}', 'seoUpdate')->name('package.seo.update');
            Route::get('reset-package-courses}', 'resetPackageCourses')->name("reset_package_courses");
            //Edit course route for different actions
            Route::get('packages/{id}/learners', 'courseLearners')->name('packages.learners');
            Route::get('packages/{id}/builder/{chapterId?}', 'courseBuilder')->name('packages.builder');
            Route::get('packages/{id}/preview', 'coursePreview')->name('packages.preview');
            Route::get('packages-ask-approval/{course_id}', 'askforApprovalPackages')->name('packages.ask.approval');
            //Package Builder
            Route::get('packages/{id}/package', 'coursePackage')->name('packages.package');
            Route::get('get-packages/{id}', 'getCoursesForPackages')->name('packages.package.list');
            Route::get('add-course-to-package/{course_id}/{package_id}', 'addCourseToPackage')->name('add.packages.package');
            Route::get('delete-course-to-package/{course_id}/{package_id}', 'removeCourseToPackage')->name('delete.packages.package');
            // add multiple package
            Route::post('packages/{id}/package/bulk_add', 'addMultipleCourseToPackage')->name('packages.package.add');
            // remove multiple package
            Route::post('packages/{id}/package/bulk_delete', 'deleteMultipleCourseToPackage')->name('packages.package.delete');
            //Delete Course
            Route::delete('packages/delete/{id}', 'delete')->name('packages.delete');
            Route::post('packages/bulk_hard_del', 'bulk_Hard_Delete');

            Route::get('check_coursePackage/{courseId}', 'check_coursePackages');
            Route::get('get_addable_learners/{courseId}', 'get_addable_learners');
            Route::get('package/addable/learners/{packageId}/{learnerId}/{planId?}', 'addable_learners')->name('package.addable_learners');
            Route::get('delete_addable/learners/{id}', 'delete_addable_learners')->name('delete_addable.learners');
            Route::delete('restore_addable/learners/{id}', 'restore_addable_learners')->name('restore_addable.learners');
        });

        //Page Routes
        Route::resource('pages', PageController::class);
        Route::controller(PageController::class)->group(function () {
            Route::get('get-pages', 'getPages');
            Route::get('get-pages-deleted', 'getPagesDeleted');
            Route::get('pages/restore/{id}', 'restore');
            Route::post('pages/restore_all', 'restore_all');
            Route::delete('pages/delete/{id}', 'delete')->name('pages.delete');
            Route::post('pages/bulk_hard_del', 'bulk_Hard_Delete');
        });

        //Media Routes
        Route::resource('media', MediaController::class);
        Route::controller(MediaController::class)->group(function () {
            Route::get('get-media', 'getMedia');
            Route::get('get-media-chapter', 'getMediaChpter');
            Route::get('get-media-deleted', 'getMediaDeleted');
            Route::get('media/restore/{id}', 'restore');
            Route::post('media/restore_all', 'restore_all');
            Route::delete('media/delete/{id}', 'delete')->name('media.delete');
            Route::post('media/bulk_hard_del', 'bulk_Hard_Delete');
            Route::get('media-export', 'mediaExport')->name('media_export');
        });

        Route::get('backofice/media/deletes', [MediaController::class, "destroy"])->name("media_delete");

        //Profile route
        Route::resource('profile', ProfileController::class);

        // slide
        Route::resource('slide', SlideController::class);

        // course reviews
        Route::resource('course_reviews', CourseReviewsController::class);
        Route::controller(CourseReviewsController::class)->group(function () {
            Route::get('get_course_reviews/{user_id?}/{course_id?}/{instructor_id?}', 'course_reviews');
            Route::get('home_reviews', 'homeReviews')->name("home_reviews");
            Route::get("course-reviews-edit", "courseReviewsEdit")->name("course_reviews.edit");
            Route::post("course-reviews-update", "courseReviewsUpdate")->name("course_reviews.update");
            // Route::post('status_update', 'status_update');
        });

        Route::resource('notifications', NotificationsController::class);
        Route::controller(NotificationsController::class)->group(function () {
            Route::get('get-notifications', 'getNotifications');
            Route::get('/notifications/restore/{id}', 'restore');
            Route::get('get-notifications-deleted', 'getNotificationsDeleted');
            Route::post('notifications/restore_all', 'restore_all');
            Route::delete('notifications/delete/{id}', 'delete')->name('notifications.delete');
            Route::post('notifications/bulk_hard_del', 'bulk_Hard_Delete');
        });
        Route::resource('course_orders', CourseOrdersController::class);
        Route::controller(CourseOrdersController::class)->group(function () {
            Route::get('get-course-orders', 'getCourseOrders');
            Route::post('export-course_orders', 'exportCourseOrders')->name('course_orders.export');
        });

        Route::controller(TutorialController::class)->group(function () {
            route::get("tutorial", "index")->name("tutorial.index");
            route::get("tutorial/{id}", "show");
            route::post("tutorial-store", "tutorialStore")->name("tutorial.store");
            route::post("tutorial-update", "tutorialUpdate")->name("tutorial.update");
            route::get("tutorial-edit", "tutorialEdit")->name("tutorial.edit");
            route::get("tutorial-delete", "tutorialDelete")->name("tutorial.delete");
        });

        Route::controller(CountryManageController::class)->group(function () {
            route::get("country", "index")->name("country");
            route::post("country-store", "countryStore")->name("country.store");
            route::post("country-update", "countryUpdate")->name("country.update");
            route::get("country-edit", "countryEdit")->name("country.edit");
            route::get("country-delete", "countryDelete")->name("country.delete");
        });

        Route::controller(StateManageController::class)->group(function () {
            route::get("state", "index")->name("state");
            route::post("state-store", "stateStore")->name("state.store");
            route::post("state-update", "stateUpdate")->name("state.update");
            route::get("state-edit", "stateEdit")->name("state.edit");
            route::get("state-delete", "stateDelete")->name("state.delete");
        });

        Route::controller(CityManageController::class)->group(function () {
            route::get("city", "index")->name("city");
            route::post("city-store", "cityStore")->name("city.store");
            route::post("city-update", "cityUpdate")->name("city.update");
            route::get("city-edit", "cityEdit")->name("city.edit");
            route::get("city-delete", "cityDelete")->name("city.delete");
            route::get("search-state", "searchState");
        });

        Route::resource('coupons', CouponController::class);
        Route::controller(CouponController::class)->group(function () {
            // Route::get('/coupons/restore/{id}', 'restore');
            Route::get('get_coupons', 'get_coupons');
            // Define a route for changing the coupon status
            Route::put('/coupons/{id}/change-status', 'changeStatus');
            // Route::get('get_coupons_deleted', 'get_coupons_deleted');
            //Route::post('roles/bulk_del', 'bulk_del');
            // Route::post('coupons/restore_all', 'restore_all');
            // Route::delete('coupons/delete/{id}', 'delete')->name('coupons.delete');
            // Route::post('coupons/bulk_hard_del', 'bulk_Hard_Delete');
            Route::post('update_coupon_expiry', 'updateCouponExpiry')->name('update.coupon.expiry');
        });

        Route::controller(ExportController::class)->group(function () {
            Route::get('learner-export', 'learnerExport')->name('learner_export');
            Route::get('login-learner-export', 'loginLearnerExport')->name('login_learner_export');
            Route::get('learner-complete-report', 'learnerCompleteReport')->name('learner_complete_report');
            Route::get('sales-package-learner-report', 'salesPackageLearnerReport')->name('sales_package_learner_report');
            // Route::get('pending-course-export', 'courseExport');
            Route::get('cousre-export', 'courseExport');
            Route::get('user-signup-export', 'userSignupExport')->name('user_signup_export');
            Route::get('purchase-export', 'purchaseExport')->name('purchase_export');
        });

        Route::controller(ReportController::class)->group(function () {
            Route::get('learner-report', 'learnerReport')->name('learner_report');
            Route::get('login-learner-report', 'loginLearnerReport')->name('login_learner_report');
            Route::get('user-signup-report', 'userSignupReport')->name('user_signup_report');
            Route::get('purchase-course-report', 'purchaseCourseReport')->name('purchase_course_report');
            Route::get('user-progress-report', 'userProgressReport')->name('user_progress_report');
            Route::post('get-instructor-courses', 'getInstructorCourses')->name('get_instructor_courses');
            Route::get('learner-allCourse-progress', 'LearnerAllCourseProgress')->name('learner-_allCourse_progress');
        });
    });
});
