<?php

namespace App\Http\Controllers\front\V2;

use App\Http\Controllers\Controller;
use App\Mail\EarnCoinMail;
use App\Mail\PaymentNotification;
use App\Mail\RedeemCoinMail;
use App\Models\Chapter;
use App\Models\CouponCourse;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\Learner;
use App\Models\User;
use App\Models\UserCoin;
// use Illuminate\Support\Facades\Validator;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use libphonenumber\PhoneNumberUtil;
use Razorpay\Api\Api;

class V2CheckoutController extends Controller
{

    public function razorpayConfig()
    {
        // rzp_test_OaxONu6UMEZQmW
        $key = config()->has('settings.razorpay_key') ? config('settings.razorpay_key') : null;
        $secret = config()->has('settings.razorpay_secret') ? config('settings.razorpay_secret') : null;
        if (env("APP_ENV") != "production") {
            if (config()->has('settings.razorpay_sandbox') && config('settings.razorpay_sandbox') == 1) {
                $key = config()->has('settings.razorpay_sandbox_key') ? config('settings.razorpay_sandbox_key') : null;
                $secret = config()->has('settings.razorpay_sandbox_secret') ? config('settings.razorpay_sandbox_secret') : null;
            }
        }
        $api = new Api($key, $secret);
        return $api;
    }

    public function store(Request $request)
    {

        $api = $this->razorpayConfig();

        $webhookSecret = "Axay";
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        try {
            $api = new \Razorpay\Api\Api("rzp_test_OaxONu6UMEZQmW", "ODDxXUQMTSLKUTuCqnPkeX0O");

            $api->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);

            $data = json_decode($payload, true);

            $eventType = $data['event'];

            $paymentId = $data['payload']['payment']['entity']['id'];
            $amountInPaise = $data['payload']['payment']['entity']['amount'];
            $status = $data['payload']['payment']['entity']['status'];
            $amountInINR = $amountInPaise / 100;

            $amount = number_format($amountInINR, 2) . ' ' . strtoupper($currency);

            switch ($eventType) {
                case 'payment.authorized':
                    $payment = $api->payment->fetch($paymentId)->capture(array('amount' => $amount));
                    break;

                case 'payment.captured':
                    Log::info("Payment {$paymentId} is captured.");
                    break;

                case 'payment.failed':
                    Log::info("Payment {$paymentId} failed.");
                    break;

                case 'payment.pending':
                    Log::info("Payment {$paymentId} is pending.");
                    break;

                default:
                    Log::info('Unhandled event type: ' . $eventType);
            }

            return response()->json(['status' => 'success', 'message' => 'Webhook handled successfully.'], 200);

        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Invalid signature or error occurred.'], 400);
        }


        // $phoneUtil = PhoneNumberUtil::getInstance();

        // $phoneNumber = $payment["contact"];
        // $parsedNumber = $phoneUtil->parse($phoneNumber, null);
        // $country_code = $parsedNumber->getCountryCode();

        // $country_id = DB::table("countries")->where("phonecode", $country_code)->first()->id;

        // $countryMapping = [
        //     231 => [4, 231, 232, 38], // US // 1 phonecode
        //     8 => [8, 46, 96, 162], // Antarctica // 672 phonecode
        //     13 => [13, 45], // Australia // 61  phonecode
        //     29 => [29, 164, 209], // Bouvet Island // 47  phonecode
        //     48 => [48, 141], // Comoros / 269  phonecode
        //     49 => [49, 50], // Congo // 242  phonecode
        //     71 => [71, 203], // Falkland Islands // 500  phonecode
        //     78 => [78, 179], // French Southern Territories // 262  phonecode
        //     107 => [107, 236], // Italy // 39  phonecode
        //     148 => [148, 242], // Morocco / 212  phonecode
        //     157 => [157, 174], // New Zealand // 64  phonecode
        // ];

        // foreach ($countryMapping as $mappedId => $ids) {
        //     if (in_array($country_id, $ids)) {
        //         $request->merge(['country_id' => $mappedId]);
        //         break;
        //     } else {
        //         $request->merge(['country_id' => $country_id]);
        //     }
        // }

        // $learner = Learner::where(["mobile" => $parsedNumber->getNationalNumber(), "country_id" => $request->country_id])->first();

        // $learner_without_login_response_checkout_url = [
        //     "name" => '',
        //     "email" => $payment['email'] != "void@razorpay.com" ? $payment['email'] : '',
        //     "mobile" => $parsedNumber->getNationalNumber(),
        //     "country_id" => $request->country_id,
        // ];

        // $learner_login_response_checkout_url = [
        //     'state_id' => $input['state_id_hidden'] ?? $learner->state_id,
        //     'city_id' => $input['city_id_hidden'] ?? $learner->city_id,
        //     'name' => $learner->name ?? null,
        //     'email' => $learner->email ?? null,
        // ];


        // if ($request->status == "failed") {

        //     $content = @$request['error_msg']['description'];
        //     $payment_id = @$request['error_msg']['metadata']['payment_id'];

        //     if (!auth()->guard('learner')->check() && !$learner) {
        //         $learner = Learner::create($learner_without_login_response_checkout_url);
        //     }

        //     if ($learner = auth()->guard('learner')->user()) {
        //         Learner::where('id', $learner->id)->update();
        //     }

        //     $expiredAt = get_plan_expire($request->plan_id);
        //     $userCourse = UserCourse::create([
        //         'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
        //         'course_id' => $request->course_id ?? null,
        //         'plan_id' => $request->plan_id ?? null,
        //         'full_name' => auth()->guard('learner')->user()->name ?? null,
        //         'email' => auth()->guard('learner')->user()->email ?? null,
        //         'mobile' => auth()->guard('learner')->user()->mobile ?? null,
        //         'country_id' => auth()->guard('learner')->user()->country_id ?? null,
        //         'state_id' => auth()->guard('learner')->user()->state_id ?? null,
        //         'city_id' => auth()->guard('learner')->user()->city_id ?? null,
        //         'order_status' => 2,
        //         'payment_gateway' => 1,
        //         'transaction_id' => $payment_id ?? null,
        //         'expire_at' => $expiredAt,
        //         'price' => $request->amount ?? 0.00,
        //     ]);

        //     $params['push']['to'] = auth()->guard('learner')->user()->id;

        //     return response()->json([
        //         'success' => false,
        //         'message' => $content,
        //     ], 201);
        // }



        // if ($payment->status == "failed") {
        //     $userCourse = UserCourse::create([
        //         'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
        //         'course_id' => $input['courseId'] ?? null,
        //         'subscription_id' => request()->has('subscription_id') && request()->filled('subscription_id') ? $input['subscription_id'] : null,
        //         'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
        //         'plan_id' => $input['planId'] ?? null,
        //         'order_status' => 2,
        //         'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
        //         'payment_gateway' => 1,
        //         'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
        //         'price' => $request->actual_price,
        //         'full_name' => auth()->guard('learner')->user() ? auth()->guard('learner')->user()->name : @$payment['card']['name'],

        //         'email' => isset($payment) && !empty($payment) ? $payment->email : null,
        //         'mobile' => auth()->guard('learner')->user()->mobile ?? $contact,
        //         'country_id' => auth()->guard('learner')->user()->country_id ?? $country_id,
        //         'state_id' => @auth()->guard('learner')->user()->state_id,
        //         'city_id' => @auth()->guard('learner')->user()->city_id,
        //         'transaction_response' => json_encode($payment->toArray()),
        //     ]);

        //     return response()->json([
        //         'success' => false,
        //         'url' => route('course.list'),
        //         'message' => $payment->error_description,
        //     ], 200);
        // }


        // $course_title = request()->has('course_title') && request()->filled('course_title') ? $request->course_title : null;
        // if (!empty($request->razorpay_payment_id)) {
        //     // try {
        //     //     DB::beginTransaction();
        //         $payment_id = $request->razorpay_payment_id;

        //         if (!auth()->guard('learner')->check()) {

        //             if (!$learner) {
        //                 $learner = Learner::create($learner_without_login_resaponse);
        //             }
        //         }

        //         if (auth()->guard('learner')->user()) {
        //             Learner::where('id', auth()->guard('learner')->user()->id)
        //             ->update($learner_login_response_checkout_url);
        //         }

        //         $expiredAt = get_plan_expire($request->planId);

        //         //Payment record create below
        //         $userCourse = UserCourse::create([
        //             'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
        //             'course_id' => $request->courseId ?? null,
        //             'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
        //             'plan_id' => $request->planId ?? null,
        //             'order_status' => isset($payment) && !empty($payment) && $payment->status != 'failed' ? 1 : 2,
        //             'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
        //             'payment_gateway' => 1,
        //             'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
        //             'expire_at' => $expiredAt,
        //             'price' => $request->actual_price,
        //             'full_name' => auth()->guard('learner')->user() ? auth()->guard('learner')->user()->name : @$payment['card']['name'],

        //             'email' => isset($payment) && !empty($payment) ? $payment->email : null,

        //             'mobile' => auth()->guard('learner')->user()->mobile ?? $parsedNumber->getNationalNumber(),

        //             'country_id' => auth()->guard('learner')->user()->country_id ?? $request->country_id,

        //             'state_id' => @auth()->guard('learner')->user()->state_id,

        //             'city_id' => @auth()->guard('learner')->user()->city_id,
        //             'transaction_response' => json_encode($payment->toArray()),
        //             'user_coin' => $request->coins,
        //             'per_coin_price' => config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null,
        //             'coupon_id' => $request->coupon_id,
        //             'after_deduction_price' => number_format($request->amount, 2, '.', ''),
        //             'after_coupon_applied_deduction_price' => $request->discount != "" ? $request->actual_price - $request->discount : 0,
        //         ]);
        //         $user_course_id = $userCourse->id;

        //         createInvoice($user_course_id);
        //         // earn
        //         $course_coin = Course::where("id", $request->courseId)->first();

        //         if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
        //             $user_coin = new UserCoin();
        //             $user_coin->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
        //             $user_coin->coins = $course_coin->course_coin;
        //             $user_coin->type = 1;
        //             $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
        //             $user_coin->course_id = $request->courseId;
        //             $user_coin->save();

        //             $subject2 = "Congratulations! Success Coins Allocated to Your Account!";
        //             $content2 = "you have purchased $course_coin->title course";
        //             $plus = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
        //                 ->where(function ($query) {
        //                     $query->where("type", "!=", 2)
        //                         ->Where("type", "!=", 4);
        //                 })->sum("coins");

        //             $deduct = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
        //                 ->where(function ($query) {
        //                     $query->where("type", "=", 2)
        //                         ->orWhere("type", "=", 4);
        //                 })->sum("coins");

        //             $total = $plus - $deduct;

        //             $learner = Learner::where("id", auth()->guard("learner")->user()->id ?? $learner->id)->first();

        //             $inst = new EarnCoinMail($content2, $subject2, $learner, $course_coin->course_coin, $total);
        //             if ($learner) {
        //                 \Mail::to($learner->email ?? $payment['email'])->send($inst);
        //             }
        //         }

        //         // redeem
        //         if ($request->coins != "") {
        //             if ($request->coins) {
        //                 if ($request->coins > 0) {
        //                     $user_coin = new UserCoin();
        //                     $user_coin->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
        //                     $user_coin->coins = $request->coins;
        //                     $user_coin->type = 2;
        //                     $user_coin->comment = "You have redeemed $request->coins success coin due to you have purchased $course_coin->title course";
        //                     $user_coin->course_id = $input['courseId'];
        //                     $user_coin->save();

        //                     $subject3 = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
        //                     $content3 = "you have redeemed while purchasing $course_coin->title course";
        //                     $plus = UserCoin::where("learner_id", $request->learner_id)
        //                         ->where(function ($query) {
        //                             $query->where("type", "!=", 2);
        //                             $query->Where("type", "!=", "4");
        //                         })->sum("coins");

        //                     $deduct = UserCoin::where("learner_id", $request->learner_id)
        //                         ->where(function ($query) {
        //                             $query->where("type", "=", 2)
        //                                 ->orWhere("type", "=", 4);
        //                         })->sum("coins");

        //                     $total = $plus - $deduct;
        //                     $learner = Learner::where("id", $request->learner_id)->first();
        //                     $inst = new RedeemCoinMail($content3, $subject3, $learner, $total, $request->coin);
        //                     if ($learner) {
        //                         \Mail::to($learner->email)->send($inst);
        //                     }
        //                 }
        //             }
        //         }

        //         if ($request->coupon_id != "") {
        //             $CouponCourse = CouponCourse::where("coupon_id", $request->coupon_id)->where("course_id", $request->courseId)->first();
        //             $coupon = new CouponUsage();
        //             $coupon->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
        //             $coupon->coupon_id = $CouponCourse->id;
        //             $coupon->is_used = 1;
        //             $coupon->course_id = $request->courseId;
        //             $coupon->save();
        //         }

        //         if (Auth::guard('learner')->check()) {

        //             $learnerData = Learner::with('country:id,phonecode')->where("id", auth()->guard('learner')->user()->id)->get()->first();
        //             $subject = "\u{1F91D} You are now enrolled in $course_title";

        //             $content = "$course_title has been purchased successfully.";
        //             $params['common'] = [
        //                 'course_id' => $request->courseId,
        //                 'learner_id' => auth()->guard('learner')->user()->id,
        //                 'type' => 1,
        //             ];

        //             $params['email']['user_course_id'] = $user_course_id;
        //             $params['email']['to'] = '';
        //             $params['web']['to'] = Auth::guard('learner')->id();
        //             $params['push']['to'] = auth()->guard('learner')->user()->id;

        //             if ($user_course_id) {
        //                 $invoiceData = $userCourse;
        //             }

        //             $inst = new PaymentNotification($content, $subject, $userCourse);

        //             if ($learnerData->email != '') {
        //                 \Mail::to($learnerData->email)->send($inst);
        //                 $this->notified[] = true;
        //                 $ids = Learner::where('email', $learnerData->email)->pluck('id')->toArray();
        //                 if (!empty($ids)) {
        //                     $this->data['email'] = $ids;
        //                 }
        //             }
        //             $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
        //         } else {

        //             $learnerData = $learner;

        //             $subject = "\u{1F91D} You are now enrolled in $course_title";

        //             $content = "$course_title has been purchased successfully.";
        //             $params['common'] = [
        //                 'course_id' => $request->courseId,
        //                 'learner_id' => $learnerData->id,
        //                 'type' => 1,
        //             ];

        //             $params['email']['user_course_id'] = $user_course_id;
        //             $params['email']['to'] = '';
        //             $params['web']['to'] = $learnerData->id;
        //             $params['push']['to'] = $learnerData->id;

        //             // dd($userCourse);
        //             if ($user_course_id) {
        //                 $invoiceData = $userCourse;
        //             }
        //             $inst = new PaymentNotification($content, $subject, $userCourse);
        //             if ($learnerData->email != '') {
        //                 \Mail::to($learnerData->email)->send($inst);
        //                 $this->notified[] = true;
        //                 $ids = Learner::where('email', $learnerData->email)->pluck('id')->toArray();
        //                 if (!empty($ids)) {
        //                     $this->data['email'] = $ids;
        //                 }
        //             }

        //             $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
        //         }

        //         $this->sessionDestroy();
        //         DB::commit();
        //         $advancement_course = Course::where("id", $request->advancement_course_id)->select("slug", "id")->first();

        //         if ($request->advancement == 1) {
        //             $nextRecord = Chapter::where('id', '>', $request->chapter_id)->where("parent_id", "!=", 0)->first();
        //             if ($nextRecord != null) {
        //                 return response()->json([
        //                     'success' => true,
        //                     'url' => route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $nextRecord->id]),
        //                     'message' => 'Payment successfully Completed!',
        //                     "google_tag_status" => "success",
        //                 ], 200);
        //             } else {
        //                 $prevRecord = Chapter::where('id', '<', $request->chapter_id)->where("course_id", $advancement_course->id)
        //                     ->orderBy('id', 'desc')
        //                     ->first();
        //                 return response()->json([
        //                     'success' => true,
        //                     'url' => route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $prevRecord->id]),
        //                     'message' => 'Payment successfully Completed!',
        //                     "google_tag_status" => "success",
        //                 ], 200);
        //             }

        //         }

        //         return response()->json([
        //             'success' => true,
        //             'url' => route('my.course'),
        //             'message' => 'Payment successfully Completed!',
        //             "google_tag_status" => "success",
        //         ], 200);
        //     // } catch (\Exception $e) {
        //     //     DB::rollback();
        //     //     return response()->json([
        //     //         'success' => false,
        //     //         'url' => route('course.list'),
        //     //         'message' => 'Something wrong.' . $e->getMessage(),
        //     //     ], 200);
        //     // }
        // }

    }


    public static function sessionDestroy()
    {
        session()->forget('sessionAddress');
    }

}
