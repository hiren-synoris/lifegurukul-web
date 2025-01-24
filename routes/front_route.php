<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\front\IndexController;
use App\Http\Controllers\front\CategoryController;
use App\Http\Controllers\front\CheckoutController;
use App\Http\Controllers\front\RatingReviewController;
use App\Http\Controllers\front\V2\V2CheckoutController;
use App\Http\Controllers\front\FaqController as FaqfrontController;
use App\Http\Controllers\front\BlogController as BlogfrontController;
use App\Http\Controllers\front\PageController as FrontPageController;
use App\Http\Controllers\front\InstructorController as InstructorFront;
use App\Http\Controllers\front\WishlistController as WishlistFrontController;

/**
 * Below route is for Front website
 */

Route::controller(App\Http\Controllers\Auth\LoginController::class)->group(function () {
    Route::get('/learner', 'showLearnerLoginForm')->name('learner.login-view');
    Route::post('/learner', 'generate')->name('learner.generate');
    Route::get('/learner/verification', 'verification')->name('learner.verification');
    Route::post('/learner/login', 'loginWithOtp')->name('learner.getlogin');
    Route::post('/learner/otp-resend', 'resendOtp')->name('learner.resendOtp');
    Route::get('/logout', 'logout')->name('learner.logout');
    Route::get('/device', 'getDevice')->name('device');
    Route::get('direct-login/{status}', 'DirectLogin')->name('direct_login');
});

Route::controller(BlogfrontController::class)->group(function () {
    Route::get('blogs', 'index')->name('blogs');
    Route::get('blogs/{slug}', 'detail');
});

Route::controller(WishlistFrontController::class)->group(function () {
    Route::get('wishlist', 'index')->name('my.wishlists');
    Route::post('wishlist', 'store');
    Route::get('wishlist/{wishlist}', 'destroy')->name('front.wishlist.destory');
});

Route::controller(App\Http\Controllers\front\ContactController::class)->group(function () {
    Route::get('contact-us', 'index')->name('contact-us');
    Route::post('contact-us', 'store')->name('contact-us.store');
    Route::get('contact-us-new', 'storeNew')->name('contact-us.store-new');
});

Route::controller(FaqfrontController::class)->group(function () {
    Route::get('faq', 'index');
});
// Page route for frontend side
Route::get('page/{slug}', [FrontPageController::class, 'detail'])->name('page');

Route::resource('newsletter', App\Http\Controllers\front\NewsletterController::class);
Route::get('term-condition', [IndexController::class, 'termCondition']);
// Route::get('privacy-policy', [IndexController::class, 'privacyPolicy']);
Route::resource('categories', CategoryController::class);

Route::resource('instructor', InstructorFront::class);

//Checkout/Payment route
Route::controller(CheckoutController::class)->group(function () {
    Route::get('checkout/{planId}/{updated_new_coin}/{coupon_id}', 'index')->name('checkout.index');
    Route::get('direct-checkout/{planId}/{updated_new_coin}/{coupon_id}', 'index')->name('new_checkout.index');
    Route::post('checkout-payment', 'store')->name('checkout.payment.store');
    Route::get('checkout-free/{planId?}/{updated_new_coin}/{coupon_id}', 'saveFreePlan')->name('checkout.free.plan');
    Route::get('checkout-recurring/{planId}', 'makeRecurringPayment')->name('checkout.recurring.plan');
    Route::post('checkout-payment-instamojo', 'instamojoStore')->name('checkout.payment.instamojo.store');
    Route::get('checkout-payment-success', 'instamojoSuccess')->name('checkout.payment.instamojo.success');
});
//Checkout/Payment route

Route::controller(V2CheckoutController::class)->group(function () {
    Route::prefix('V2')->group(function () {
        // Route::post('checkout-payment', 'store')->name('checkout.payment.store');
    });
});

// CommonController for all
Route::controller(App\Http\Controllers\front\CommonController::class)->group(function () {
    Route::get('country', 'getCountry')->name('country');
    Route::post('get-states-by-country', 'getState')->name('states.by.country');
    Route::post('get-cities-by-state', 'getCity')->name('cities.by.states');
    Route::get('country-code', 'getCountryCode')->name('country.code');
    Route::get('country-flag', 'setCountryFlag')->name('country.flag');
    // global search
    Route::get('search', 'search')->name('search');
    // course wise plan
    Route::post('get-plan-by-course', 'getCoursePlan')->name('plan.by.course');
    Route::post('create-subscription', 'createSubscription')->name('create.subscription');
});

Route::middleware(['browser_suport'])->group(function () {
    Route::middleware(['device_validation'])->group(function () {
        Route::get('/', [App\Http\Controllers\front\IndexController::class, 'index'])->name('home');
        Route::get('/countryCodeUpdate', [App\Http\Controllers\front\IndexController::class, 'countryCodeUpdate'])->name('countryCodeUpdate');
        Route::controller(App\Http\Controllers\front\CoursesController::class)->group(function () {
            Route::get('/course', 'index')->name('course.list');
            Route::get('/course-details/{slug}', 'course_detail')->name('course.details');
            Route::get('/course-package/{slug}', 'course_package')->name('course.packages');
            // seprated routes because of twice entry in activity log of coure view
            Route::get('/course-view/{slug}', 'course_preview')->name('course.preview.slug');
            Route::get('/course-view/{slug}/{chapterId?}', 'course_preview_slug_chapter')->name('course.preview');
            //

            Route::get('get-course-all-plan/{id?}', 'getCoursePlanListForPopup')->name('course.all.plan');

            Route::post('video-tracker', 'videoEventTracker')->name('video.tracker');
            Route::post('all-tracker', 'completeAndcontinue')->name('all.tracker');
            Route::get('cancel_subscription/{id}', 'cancel_subscription')->name('subscription.cancel');
            Route::post('pdf/download', 'downloadPdf')->name('pdf.download');
            Route::get('user-course-update', 'userCourseUpdate')->name('user_course_update');
        });
        // we have to access Route after login in learner
        Route::middleware(['web', 'is_learner'])->group(function () {
            // Route::middleware(['web_auth'])->group(function () {
            Route::controller(App\Http\Controllers\front\StudentController::class)->group(function () {
                Route::get('/dashboard', 'student_dashboard')->name('student.dashboard');
                Route::get('/my_course', 'my_course')->name('my.course');
                Route::get('/test-email-template', 'testEmailTemplate')->name('test_email_template');
                Route::get('/my_package_course/{userCourseId?}', 'my_package_course')->name('my.package.course');
                Route::get('/my-order', 'studentInvoice')->name('my.order');
                Route::get('/learner-log', 'learnerLog')->name('learner_log');
                Route::get('/my-wallet', 'myWallet')->name('my_wallet');
                Route::get('/student_wishlist', 'student_wishlist')->name('student_wishlist');
                Route::get('/student_purchase_history', 'student_purchase_history')->name('student_purchase_history');
                Route::post('/student-profile', 'profileSet')->name('student.profile');
                Route::get('/profile', 'edit')->name('student.profile.edit');
                Route::get('/get-city', 'getCity')->name('student.profile.get_city');
                Route::put('/student-profile-update', 'update')->name('student.profile.update');
                Route::post('/student-profile-picture', 'profilePicture')->name('profile.picture.store');
                Route::put('/student-profile-delete', 'profilePictureRemove')->name('student.profile.delete');
                Route::get('/student-payment', 'studentPayment')->name('student.payment');
                Route::get('/my-notification/{id?}', 'notifications')->name('my.notification');
                Route::get('/my-notification-detail/{id}', 'notificationsDetails')->name('my.details');
                Route::get('/my-subscription', 'subscriptions')->name('subscriptions');
                Route::get('/view-invoice/{id}', 'viewInvoice')->name('view.invoice');
                Route::get('/read-notification', 'readNotification')->name('read_notification');
            });
        });

        // Front Public Forum
        Route::controller(App\Http\Controllers\front\PublicForumController::class)->group(function () {
            Route::get('get-public-forum-front', 'getPublicForumFront')->name("get_public_forum_front");
            Route::get('/public-forum-front/{id}', 'index')->name('courses.public-forum-front');
            Route::post('/public-forum-front', 'store');
            Route::put('/public-forum-delete', 'publicForumDelete')->name('public.forums.delete');
            Route::put('/public-forum-reply-delete', 'publicForumReplyDelete')->name('public.forums.reply.delete');
            Route::post('get-question', 'getQuestion')->name("front_get_question");
            Route::post('reply-public-forum-front', 'replyPublicForumFront')->name("reply_public_forum_front");
        });

        // rating review
        Route::resource('rating_review', RatingReviewController::class);
        Route::controller(RatingReviewController::class)->group(function () {
            Route::get('load_reviews/{id}', 'load_reviews');
        });
    });
});

Route::get('delete-account', function () {
    return view('front.delete-account-ios');
});

Route::get('delete-learner', function () {
    return view('front.delete-account-android');
});

Route::get('mail-delete-learner', [IndexController::class, 'mailDeleteLearner'])->name('mail.delete.learner');
Route::get('delete-user', [IndexController::class, 'DeleteLearner'])->name('delete.users');
