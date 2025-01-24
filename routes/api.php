<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\ZapierController;
use App\Http\Controllers\Api\V2\V2AuthController;
use App\Http\Controllers\Api\V2\V2CourseController;
use App\Http\Controllers\Api\V2\V2SliderController;
use App\Http\Controllers\Api\V2\V2SettingController;
use App\Http\Controllers\Api\PublicForumReplyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|SliderController
 */
// Route::post('accessToken', 'Api\AuthController@store')->middleware('checKBasicDeviceFields');

Route::get('testingzapieraction', function (Request $request) {
    $data = $request->all();
    dd($data);
});

Route::post('/accessToken', [AuthController::class, 'storeDeviceDetails'])->middleware('checKBasicDeviceFields');

Route::post('/admin-zapier-login', [ZapierController::class, 'adminZapierLogin']);
Route::post('/zapier-learner', [ZapierController::class, 'zapierLearner']);
Route::post('/zapier-learner-create', [ZapierController::class, 'zapierLearnerCreate']);
Route::post('/zapier-get-Review', [ZapierController::class, 'zapierGetReview']);
Route::post('/zapier-get-learner', [ZapierController::class, 'zapierGetLearner']);
Route::post('/zapier-get-sucessfully-transaction', [ZapierController::class, 'zapierGetSucessfullyTransaction']);
Route::post('/zapier-get-fail-transaction', [ZapierController::class, 'zapierGetFailTransaction']);
Route::post('/zapier-get-enrol-transaction', [ZapierController::class, 'zapierGetEnrollTransaction']);
Route::post('/zapier-get-course-completion', [ZapierController::class, 'zapierGetCourseCompletion']);
Route::post('/zapier-get-wishlist', [ZapierController::class, 'zapierGetWishlist']);
Route::post('/zapier-get-payment', [ZapierController::class, 'zapierGetPayment']);
Route::post('/zapier-with-learner-create', [ZapierController::class, 'zapierWithLearnerCreate']);
Route::post('/zapier-with-learner-enroll', [ZapierController::class, 'zapierWithLearnerEnroll']);
Route::post('/zapier-enroll-fromPayment', [ZapierController::class, 'zapierEnrollFromPayment']);
Route::post('/zapier-with-learner-unenroll', [ZapierController::class, 'zapierWithLearnerUnEnroll']);

Route::controller(CheckoutController::class)->group(function () {
    Route::get('checkout-payment-success-app', 'getPaymentSuccess')->name('checkout.payment.instamojo.success.app');
    Route::get('view-invoice', 'viewInvoice')->name('view.invoice');
});

Route::group(['middleware' => ['visitor']], function () {
// Route::middleware(['auth:visitor-api', 'scopes:visitor'])->group(function () {
    // Route::middleware(['Block_device'])->group(function() {
    Route::namespace('Api')->group(function () {
        Route::any('/generateOtp', [AuthController::class, 'generateOtp']);
        // Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/resendOtp', [AuthController::class, 'resendOtp']);
        Route::post('/verifyOtp', [AuthController::class, 'verifyOtp']);
        // Route::post('/pages', [PageController::class, 'pageDetail']);
    });
    // });
});
Route::get('get-razorpay-paymentLink', [CheckoutController::class, 'getRazorpayPaymentLink'])->name("get_razorpay_paymentLink");
Route::get('send-razorpay-paymentLink', [CheckoutController::class, 'sendRazorpayPaymentLink'])->name("send_razorpay_paymentLink");
Route::post('store-payment-api-success', [CheckoutController::class, 'storePaymentApiSuccess'])->name("store_payment_api_success");
Route::get('failed-success-redirect', [CheckoutController::class, 'failedSuccessRedirect'])->name("failed_success_redirect");
// , 'scopes:learner'
Route::middleware(['auth:learner-api', 'scopes:learner'])->group(function () {
    Route::namespace('Api')->group(function () {
        // Route::post('home', [SliderController::class, 'home']);
        Route::middleware(['Block_device'])->group(function () {
            Route::post('profileDetail', [ProfileController::class, 'profileDetails']);
            Route::post('get-logs', [ProfileController::class, 'getLogs']);
            Route::post('profilePic', [ProfileController::class, 'profilePic']);
            Route::post('myCourse', [CourseController::class, 'myCourse']);

            Route::post('getDiscussionQuestion', [PublicForumReplyController::class, 'getDiscussionQuestion']);
            Route::post('getDiscussionReply', [PublicForumReplyController::class, 'getDiscussionReply']);
            Route::post('addDiscussionQuestionReply', [PublicForumReplyController::class, 'addDiscussionQuestionReply']);
            Route::post('deleteDiscussion', [PublicForumReplyController::class, 'deleteDiscussion']);

            Route::post('packageCourses', [CourseController::class, 'packageCourses']);
            Route::post('myCourseDetail', [CourseController::class, 'myCourseDetail']);
            Route::post('fetchVideoData', [CourseController::class, 'fetchVideoData']);
            Route::post('UpdateCourseProgress', [CourseController::class, 'UpdateCourseProgress']);

            Route::post('payment', [PaymentController::class, 'payment']);
            // Route::post('slider', [SliderController::class, 'sliders']);
            Route::post('getWishlist', [CourseController::class, 'getWishlist']);
            Route::post('addWishlist', [CourseController::class, 'addWishlist']);
            Route::post('deleteWishlist', [CourseController::class, 'destroy']);
            Route::post('getNotification', [CommonController::class, 'getNotification']);
            Route::post('getNotificationDetail', [CommonController::class, 'getNotificationDetail']);
            Route::post('updateNotificationRead', [CommonController::class, 'updateNotificationRead']);

            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('getPaymentLink', [CheckoutController::class, 'getPaymentLink']);

            Route::post('checkout-payment-razorpay-success', [CheckoutController::class, 'getPaymentSuccessStore'])->name('checkout.payment.razorpay.success');
            Route::post('checkout-payment-razorpay-fail', [CheckoutController::class, 'getPaymentFailStore'])->name('checkout.payment.razorpay.fail');
            Route::post('testPushNotification', [CheckoutController::class, 'testPushNotification']);

            Route::get('getSubscriptionId', [CheckoutController::class, 'getSubscriptionId']);
            Route::get('saveFreePlan', [CheckoutController::class, 'saveFreePlan']);

            // Route::post('subscriptions', [CourseController::class, 'subscriptions']);
            Route::post('mySubsciption', [CourseController::class, 'mySubsciption']);
            Route::post('cancel_subscription', [CourseController::class, 'cancel_subscription']);
            Route::post('purchase-order', [CourseController::class, 'purchaseOrder']);
            Route::post('get-user-coin', [CourseController::class, 'getUserCoin']);
            Route::post('getCoupon', [CouponController::class, 'getCourseCoupon']);
        });
    });

    // verson 2
    // Route::middleware(['Block_device'])->group(function () {
    //     Route::prefix('V2')->group(function () {
    //         Route::controller(V2CourseController::class)->group(function () {
    //             Route::post('myCourse', 'myCourse');
    //             Route::post('getWishlist', 'getWishlist');
    //             Route::post('courseDetail', 'courseDetail');
    //         });
    //     });
    // });
});
// 'auth:visitor-api,learner-api',
Route::group(['middleware' => ['visitor']], function () {
    Route::post('home', [SliderController::class, 'home']);
    Route::post('courses', [CourseController::class, 'index']);
    Route::post('globalSearch', [SliderController::class, 'globalSearch']);
    Route::post('blogs', [BlogController::class, 'index']);
    Route::post('blogDetail', [BlogController::class, 'blogDetail']);
    Route::post('courseDetail', [CourseController::class, 'courseDetail']);
    Route::post('courseFilter', [CourseController::class, 'courseFilter']);
    Route::post('courseReview', [CourseController::class, 'courseReview']);
    Route::post('get_feature_courses', [CourseController::class, 'get_feature_courses']);
    Route::post('instuctors', [InstructorController::class, 'index']);
    Route::post('instuctorDetails', [InstructorController::class, 'instuctorDetails']);
    Route::post('courseCategories', [CourseController::class, 'courseCategories']);
    Route::post('settings', [SettingController::class, 'settings']);
    Route::post('faq', [FaqController::class, 'index']);
    Route::post('countries', [CommonController::class, 'countryList']);
    Route::post('states', [CommonController::class, 'stateList']);
    Route::post('cities', [CommonController::class, 'cityList']);

    // Route::prefix('V2')->group(function () {
    //     Route::controller(V2SliderController::class)->group(function () {
    //         Route::post('home', 'home');
    //         Route::post('globalSearch', 'globalSearch');
    //     });
    //     Route::post('settings', [V2SettingController::class, 'settings']);
    //     Route::controller(V2CourseController::class)->group(function () {
    //         Route::post('get_feature_courses', 'get_feature_courses');
    //         Route::post('courseDetail', 'courseDetail');
    //     });
    // });

});
// Route::group(['middleware' => ['auth:learner-api,visitor-api', 'scopes:learner,visitor']], function () {
Route::group(['middleware' => ['auth:learner-api', 'scopes:learner']], function () {
    // uses 'auth' middleware plus all middleware from $middlewareGroups['web']
    Route::get('fetchProfile', [CommonController::class, 'fetchProfile']);
    Route::post('addReview', [RatingController::class, 'store']);
    Route::post('deleteRating', [RatingController::class, 'destroy']);
    // verson 2

    // Route::prefix('V2')->group(function () {
    //     Route::controller(V2CourseController::class)->group(function () {
    //     });
    // });

});
Route::post('add_contact_us', [CommonController::class, 'add_contact_us']);
Route::post('get-video', [CourseController::class, 'getVideo']);
Route::post('test-mail', [CourseController::class, 'testMail']);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// version - 2

Route::group(['middleware' => ['visitor']], function () {
    Route::prefix('V2')->group(function () {
        Route::controller(V2SliderController::class)->group(function () {
            Route::post('home', 'home');
            Route::post('globalSearch', 'globalSearch');
        });
        Route::post('settings', [V2SettingController::class, 'settings']);
        Route::controller(V2CourseController::class)->group(function () {
            Route::post('get_feature_courses', 'get_feature_courses');
            Route::post('courseDetail', 'courseDetail');
        });
    });

});

Route::middleware(['auth:learner-api', 'scopes:learner'])->group(function () {
    Route::middleware(['Block_device'])->group(function () {
        Route::prefix('V2')->group(function () {
            Route::controller(V2CourseController::class)->group(function () {
                Route::post('myCourse', 'myCourse');
                Route::post('getWishlist', 'getWishlist');
                //Route::post('courseDetail', 'courseDetail');
            });
        });
    });
});

