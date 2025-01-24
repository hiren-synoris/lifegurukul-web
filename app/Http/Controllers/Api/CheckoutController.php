<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\EarnCoinMail;
use App\Mail\PaymentNotification;
use App\Mail\RedeemCoinMail;
use App\Models\CouponCourse;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\Learner;
use App\Models\LogError;
use App\Models\UserCoin;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{
    /******
     * instamojo get payment Url for web View
     * ******/
    public function getPaymentLink(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'courseId' => 'required',
            'planId' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        // // Coupon usage log
        // if($request->filled('coupon_id'))
        // {
        //     $coupon_log = new CouponUsage();
        //     $coupon_log->coupon_id = $request->coupon_id;
        //     $coupon_log->learner_id = $request->learner_id;
        //     $coupon_log->course_id = $request->courseId;
        //     $coupon_log->is_used = 1;
        //     $coupon_log->save();
        // }

        $course = Course::select('courses.id', 'courses.title', 'courses.course_coin', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
            ->leftJoin('course_plans as cp', 'cp.course_id', '=', 'courses.id')
            ->where('courses.status', '1')
        // ->where('cp.status', '1')
            ->where('cp.plan_type', '!=', 0)
            ->where('cp.id', $request->planId)
            ->where('courses.id', $request->courseId)
            ->first();

        // $user_coin = 0;
        // dD($course, $request->planId);

        // $final_price = 0;

        // if($user_coin  > $course->course_coin) {
        //     $final_price = $course['final_payable_price'];
        // } else {
        //     $final_price = $course['final_payable_price'] - $user_coin;
        // }

        // if($request->course_coin > 0) {

        //     $coin_log = new UserCoin();
        //     $coin_log->learner_id = $request->learner_id;
        //     $coin_log->coin = $request->course_coin;
        //     $coin_log->type = 3;
        //     $coin_log->course_id = $request->courseId;
        //     $coin_log->save();
        // }
        // $coin = UserCoin::where("learner_id", $request->learner_id)->get();
        // if($coin) {
        //     // $coin_redeem =$coin->where("type","2")->sum("coins");
        //     // $coin_earn =$coin->where("type","1")->sum("coins");
        //     // $total = $coin_earn - $coin_redeem;
        //     // $user_coin = $total;
        //     $plus = UserCoin::where("learner_id", $request->learner_id)
        //     ->where(function ($query) {
        //         $query->where("type", "!=", 2);
        //         $query->Where("type", "!=", "4");
        //     })->sum("coins");

        //     $deduct = UserCoin::where("learner_id", $request->learner_id)
        //         ->where(function ($query) {
        //             $query->where("type", "=", 2)
        //                 ->orWhere("type", "=", 4);
        //         })->sum("coins");

        //     $total = $plus - $deduct;
        //     $user_coin = $total;

        // }

        //     $final_price = $course['final_payable_price'] - $request->course_coin * (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0);
        // } else {

        $final_price = $request->final_payble_price;
        // }

        if (empty($course)) {
            $data = Helper::apiResonse(0, "Course Not Found", []);
            return response()->json($data, 400);
        }

        $key = config()->has('settings.instamojo_client_id') ? config('settings.instamojo_client_id') : null;
        $secret = config()->has('settings.instamojo_client_secret') ? config('settings.instamojo_client_secret') : null;
        $is_sandbox = false;
        if (config()->has('settings.instamojo_sandbox') && config('settings.instamojo_sandbox') == 1) {
            $key = config()->has('settings.instamojo_sandbox_client_id') ? config('settings.instamojo_sandbox_client_id') : null;
            $secret = config()->has('settings.instamojo_sandbox_client_secret') ? config('settings.instamojo_sandbox_client_secret') : null;
            $is_sandbox = true;
        }

        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;

        $instamojo_status = config()->has('settings.instamojo_status') ? config('settings.instamojo_status') : null;
        if ($instamojo_status == 1) {
            if ($request->final_payble_price < 10) {
                return response()->json(['message' => 'Minimum ₹10 can be purchased using instamojo.', "status" => 0], 200);
            }
        }

        // $learner = Learner::where("id", request()->user_id)->with("state")->first();
        // if ($learner->country_id == 1) {
        //     if (strtolower(@$learner->state->name) == "gujarat") {
        //         $total_price = $request->final_payble_price * (((float) $cgst) + (float) $sgst) / 100;
        //         $final_payble_price = $request->final_payble_price + $total_price;

        //     } else {
        //         $total_price = $request->final_payble_price * ((float) $igst) / 100;
        //         $final_payble_price = $request->final_payble_price + $total_price;
        //     }
        // }

        $final_payble_price = $request->final_payble_price;

        $api = \Instamojo\Instamojo::init('app', [
            'client_id' => $key,
            'client_secret' => $secret,
        ], $is_sandbox);

        $learner = Helper::getLearnerData(request()->user_id);
        if (empty($learner)) {
            $data = Helper::apiResonse(0, "Learner Not Found", []);
            return response()->json($data, 400);
        }

        $url = route('checkout.payment.instamojo.success.app', [
            'planId' => $request->planId,
            'courseId' => $request->courseId,
            'learnerId' => request()->user_id,
            'coupon_id' => $request->coupon_id,
            'coupon_amount' => $request->coupon_amount,
            'coin_used' => $request->coin_used,
            'final_payble_price' => $final_payble_price,
        ]);

        $course_title = $course['title'];
        // $amount = $course['final_payable_price'];

        $amount = $final_payble_price;
        $buyer_name = $learner->name;
        $email = $learner->email;
        $phone = $learner->mobile;

        try {
            $response = $api->createPaymentRequest(array(
                "purpose" => "$course_title",
                "amount" => $amount,
                "buyer_name" => "$buyer_name",
                "send_email" => false,
                "email" => "$email",
                "phone" => "$phone",
                "redirect_url" => $url,
            ));

            // dd($response);

        } catch (Exception $e) {
            print('Error: ' . $e->getMessage());
        }

        if (!empty($response) && !empty($response['longurl'])) {
            $res['longurl'] = $response['longurl'];

            $data = Helper::apiResonse(1, "Success", $res);
            return response()->json($data, 200);
        }
    }

    /* instamojo success save to db */
    public function getPaymentSuccess(Request $request)
    {

        $is_sandbox = false;
        $key = config()->has('settings.instamojo_client_id') ? config('settings.instamojo_client_id') : null;
        $secret = config()->has('settings.instamojo_client_secret') ? config('settings.instamojo_client_secret') : null;
        if (config()->has('settings.instamojo_sandbox') && config('settings.instamojo_sandbox') == 1) {
            $key = config()->has('settings.instamojo_sandbox_client_id') ? config('settings.instamojo_sandbox_client_id') : null;
            $secret = config()->has('settings.instamojo_sandbox_client_secret') ? config('settings.instamojo_sandbox_client_secret') : null;
            $is_sandbox = true;
        }
        $api = \Instamojo\Instamojo::init('app', [
            'client_id' => $key,
            'client_secret' => $secret,
        ], $is_sandbox);

        $response = $api->getPaymentRequestDetails(request('payment_request_id'));
        // dd(strtolower($response['status'])) ;

        if (isset($response) && !empty($response)) {
            // try {
            if ($response['status'] == "Completed") {

                $learner = Helper::getLearnerData($request->learnerId);
                if (empty($learner)) {
                    $data = Helper::apiResonse(0, "Learner Not Found", []);
                    return response()->json($data, 400);
                }
                $transactionId = $response['id'];
                $expiredAt = get_plan_expire($request->planId);
                // dd( get_course_plan_price($request->planId) ?? 0.00 ,$request->course_coin,(config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0));
                $price_new = get_course_plan_price($request->planId) ?? 0.00;
                $final_price = $price_new - $request->coin_used * (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0);
                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'course_id' => $request->courseId ?? null,
                    'plan_id' => $request->planId ?? null,
                    'order_status' => $response['status'] != 'failed' ? 1 : 2,
                    'payment_order_status' => isset($response) && !empty($response) ? $response['status'] : null,
                    'payment_gateway' => UserCourse::INSTAMOJO,
                    'transaction_id' => $transactionId,
                    'expire_at' => $expiredAt,
                    'price' => get_course_plan_price($request->planId) ?? 0.00,
                    'full_name' => $learner['name'] ?? null,
                    'email' => isset($response) && !empty($response) ? $response['email'] : null,
                    'mobile' => $response['contact'] ?? null,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'transaction_response' => json_encode($response),
                    'user_coin' => $request->coin_used,
                    'per_coin_price' => (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0),
                    'after_deduction_price' => $request->final_payble_price,
                    'coupon_id' => $request->coupon_id,
                    'after_coupon_applied_deduction_price' => $request->coupon_amount,
                ]);

                // dd(get_course_plan_price($request->planId) );

                // earn
                $course_coin = Course::where("id", $request->courseId)->first();
                if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
                    $user_coin = new UserCoin();
                    $user_coin->learner_id = $learner['id'];
                    $user_coin->coins = $course_coin->course_coin;
                    $user_coin->type = 1;
                    //$user_coin->comment = "These coins were earned from the purchase of the $course_coin->title";
                    $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                    $user_coin->course_id = $request->courseId;
                    $user_coin->save();

                    $subject = "Congratulations! Success Coins Allocated to Your Account!";
                    // $content = "Completing your profile earned you $coin_price coins.";
                    $content = "you have purchased $course_coin->title course";
                    // $plus = UserCoin::where("learner_id", $request->learnerId)->where("type", "!=", 2)->Orwhere("type", "!=", 4)->sum("coins");
                    $plus = UserCoin::where("learner_id", $request->learnerId)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    // $deduct = UserCoin::where("learner_id",$request->user_id)->where("type",2)->Orwhere("type",4)->sum("coins");

                    $deduct = UserCoin::where("learner_id", $request->learnerId)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $learner = Learner::where("id", $request->learnerId)->first();
                    $inst = new EarnCoinMail($content, $subject, $learner, $course_coin->course_coin, $total);
                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                }

                // redeem
                if (isset($request->coin_used) && $request->coin_used != "") {
                    if ($request->coin_used > 0) {

                        $user_coin = new UserCoin();
                        $user_coin->learner_id = $request->learnerId;
                        $user_coin->coins = $request->coin_used;
                        $user_coin->comment = "You have redeemed $request->coin_used success coin due to you have purchased $course_coin->title course";
                        $user_coin->type = 2;
                        $user_coin->course_id = $request->courseId;
                        $user_coin->save();
                        // }

                        $subject = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                        $content = "you have redeemed while purchasing $course_coin->title course";
                        $plus = UserCoin::where("learner_id", $request->learnerId)
                            ->where(function ($query) {
                                $query->where("type", "!=", 2);
                                $query->Where("type", "!=", "4");
                            })->sum("coins");

                        $deduct = UserCoin::where("learner_id", $request->learnerId)
                            ->where(function ($query) {
                                $query->where("type", "=", 2)
                                    ->orWhere("type", "=", 4);
                            })->sum("coins");

                        $total = $plus - $deduct;

                        //$user_coin = UserCoin::where("learner_id", $learner['id'])->where("type", 6)->first();

                        $learner = Learner::where("id", $request->learnerId)->first();

                        $inst = new RedeemCoinMail($content, $subject, $learner, $total, $request->coin_used);
                        if ($learner) {
                            \Mail::to($learner->email)->send($inst);
                        }
                    }
                }

                if ($request->coupon_id != "") {
                    $CouponCourse = CouponCourse::where("coupon_id", $request->coupon_id)->where("course_id", $request->courseId)->first();
                    $coupon = new CouponUsage();
                    $coupon->learner_id = $request->learnerId;
                    $coupon->coupon_id = @$CouponCourse->id;
                    $coupon->is_used = 1;
                    $coupon->course_id = $request->courseId;
                    $coupon->save();
                }

                $user_course_id = $userCourse->id;
                createInvoice($user_course_id);
                if (!empty($learner)) {
                    $course_data = Course::where('id', $request->courseId)->first();
                    $msg = "Payment successful";
                    $subject = $msg;
                    $type_name = "";
                    if ($course_data->type == 1) {
                        $type_name = "Course";
                    } else {
                        $type_name = "Package";
                    }
                    $content = "$type_name $course_data->title purchased successfully";
                    $params['email']['user_course_id'] = $user_course_id;
                    $params['email']['to'] = isset($response) && !empty($response) ? $response['email'] : null;
                    $params['web']['to'] = $learner['id'] ?? null;
                    // need to append country code with phone number dynamically
                    $params['push']['to'] = $learner['id'] ?? null;

                    // need to append country code with phone number dynamically
                    // $params['whatsapp']['to'] = $learner->country->phonecode . "" . $learner->mobile;
                    // $params['whatsapp']['message'] = $course_data->title;
                    // $params['whatsapp']['template'] = 'course_purchased';
                    $params['common'] = [
                        'course_id' => $request->courseId,
                        'learner_id' => $learner['id'] ?? null,
                        'type' => 1,
                    ];
                    $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
                }
                $result['courseId'] = $request->courseId;
                $result['orderId'] = "$user_course_id";
                $data = Helper::apiResonse(1, "Payment Successfully", $result);
                return response()->json($data, 200);
            } else {

                $learner = Helper::getLearnerData($request->learnerId);
                if (empty($learner)) {
                    $data = Helper::apiResonse(0, "Learner Not Found", []);
                    return response()->json($data, 400);
                }
                $transactionId = $response['id'];
                $expiredAt = get_plan_expire($request->planId);

                $price_new = get_course_plan_price($request->planId) ?? 0.00;
                $final_price = $price_new - $request->coin_used * (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0);
                $payment_status = "";
                // if ($response['status'] == "Completed") {
                //     $payment_status = 1;
                // }
                // if ($response['status'] == "Pending") {
                //     $payment_status = 2;
                // }

                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'course_id' => $request->courseId ?? null,
                    'plan_id' => $request->planId ?? null,
                    'full_name' => $learner['name'] ?? null,
                    'email' => $learner['email'] ?? null,
                    'mobile' => $learner['mobile'] ?? null,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'order_status' => 2,
                    'payment_order_status' => @$response['status'],
                    'payment_gateway' => UserCourse::INSTAMOJO,
                    'transaction_id' => $transactionId,
                    'expire_at' => $expiredAt,
                    'price' => get_course_plan_price($request->planId) ?? 0.00,
                    'transaction_response' => json_encode($response),
                ]);

                $result['courseId'] = "";
                $result['orderId'] = "";
                $data = Helper::apiResonse(0, "Payment Failed.", $result);
                return response()->json($data, 200);
            }
            // } catch (\Exception $e) {
            //     $result['courseId'] = "";
            //     $result['orderId'] = "";
            //     $data = Helper::apiResonse(0, "Payment Failed.", $result);
            //     return response()->json($data, 200);
            // }
        } else {
            $result['courseId'] = "";
            $result['orderId'] = "";
            $data = Helper::apiResonse(0, "Payment Failed.", );
            return response()->json($data, 200);
        }
    }
    public function razorpayConfig()
    {
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
    /***
     * Razor pay store success response
     *****/
    public function getPaymentSuccessStore(Request $request)
    {

        LogError::create([
            "request" => json_encode($request->all()),
        ]);

        $validator = Validator::make($request->all(), [
            'courseId' => 'required',
            'planId' => 'required',
            'transactionId' => 'nullable',
        ]);

        $api = $this->razorpayConfig();
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $learner = Helper::getLearnerData(request()->user_id);
        if (empty($learner)) {
            $data = Helper::apiResonse(0, "Learner Not Found", []);
            return response()->json($data, 400);
        }
        $transactionId = "";
        $status = "";
        $payment_gateway = "";

        $expiredAt = get_plan_expire($request->planId);
        if ($request->devicetype == 2) {
            $transactionId = $request->transactionId;
            $status = $request->status;
            $payment_gateway = 3;
        } else {
            $response = $api->payment->fetch($request->transactionId);

            // $amount = (int) ($request->input('amount') * 100);
            // if ($response->status == 'authorized') {
            //     $response = $api->payment->fetch($request->transactionId)->capture(array('amount'=>$amount));
            // } else {
            //     return response()->json([
            //         'success' => 0,
            //         'message' => "Your Aren't authorized",

            //     ], 200);

            // }

            $transactionId = $response['id'];
            $status = $response['status'];
            $payment_gateway = UserCourse::RAZOR_PAY;
        }
        //Payment record create below

        if ($status != 'failed') {

            // Coupon usage log
            if ($request->filled('coupon_id')) {
                $CouponCourse = CouponCourse::where("coupon_id", $request->coupon_id)->where("course_id", $request->courseId)->first();
                $coupon_log = new CouponUsage();
                $coupon_log->coupon_id = $CouponCourse->id;
                $coupon_log->learner_id = request()->user_id;
                $coupon_log->course_id = $request->courseId;
                $coupon_log->is_used = 1;
                $coupon_log->save();
            }
            try {
                DB::beginTransaction();
                $price_new = get_course_plan_price($request->planId) ?? 0.00;
                $final_price = $price_new - $request->coin_used * (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0);
                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'course_id' => $request->courseId ?? null,
                    'plan_id' => $request->planId ?? null,
                    // 'order_status' => strtolower($response['status']) != 'failed' ? 1 : 2,
                    'order_status' => 1,
                    // 'payment_order_status' => isset($response) && !empty($response) ? $response['status'] : null,
                    'payment_order_status' => $status,
                    'payment_gateway' => $payment_gateway,
                    'transaction_id' => $transactionId,
                    'is_subscription' => request()->has('subscriptionId') && request()->filled('subscriptionId') ? 1 : null,
                    'subscription_id' => $request->subscriptionId ?? null, // CoursePlan::PLAN_RECURRING then it will be stored
                    'expire_at' => $expiredAt,
                    'price' => get_course_plan_price($request->planId) ?? 0.00,
                    'full_name' => $learner['name'] ?? null,
                    'email' => $learner['email'] ?? null,
                    'mobile' => $learner['mobile'] ?? null,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'transaction_response' => $payment_gateway != 3 ? json_encode($response->toArray()) : json_encode([]),
                    'user_coin' => $request->coin_used,
                    'per_coin_price' => (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0),
                    'after_deduction_price' => $request->final_payble_price,
                    'coupon_id' => $request->coupon_id,
                    'after_coupon_applied_deduction_price' => $request->coupon_amount,
                ]);
                $user_course_id = $userCourse->id;
                if ($request->devicetype != 2) {
                    createInvoice($user_course_id);
                }
                if (!empty($learner)) {
                    $course_title = Course::where('id', $request->courseId)->first()->title;
                    // $msg = '<i class="fa-solid fa-handshake-simple" style="color:#FFFF00;"></i> You are now enrolled in ' . $course_title;
                    $msg = 'You are now enrolled in ' . $course_title;
                    $subject = $msg;
                    $content = "Course $course_title purchased successfully";
                    if (isset($learner['email']) && !empty($learner['email'])) {
                        $params['email']['user_course_id'] = $user_course_id;
                        $params['email']['to'] = $learner['email'] ?? null;
                    }
                    $params['web']['to'] = $learner['id'] ?? null;
                    // need to append country code with phone number dynamically
                    $params['push']['to'] = $learner['id'] ?? null;

                    // need to append country code with phone number dynamically
                    // $params['whatsapp']['to'] = $learner->country->phonecode . "" . $learner->mobile;
                    // $params['whatsapp']['message'] = $course_title;
                    // $params['whatsapp']['template'] = 'course_purchased';
                    $params['common'] = [
                        'course_id' => $request->courseId,
                        'learner_id' => $learner['id'] ?? null,
                        'type' => 1,
                        'devcetype' => $request->devicetype,
                    ];
                    send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
                }
                $result['courseId'] = $request->courseId;
                $result['orderId'] = $user_course_id;

                DB::commit();

            } catch (\Exception $e) {

                DB::rollBack();
                LogError::create([
                    "file" => $e->getFile(),
                    "flineile" => $e->getLine(),
                    "trace" => $e->getTraceAsString(),
                    "request" => json_encode($request->all()),
                ]);
                $data = Helper::apiResonse(0, "Payment Failed Try again Later..", []);
                return response()->json($data, 200);
            }

            // earn
            $course_coin = Course::where("id", $request->courseId)->first();

            if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
                $user_coin = new UserCoin();
                $user_coin->learner_id = $request->user_id;
                $user_coin->coins = $course_coin->course_coin;
                $user_coin->type = 1;
                // $user_coin->comment = "These coins were earned from the purchase of the $course_coin->title";
                $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                $user_coin->course_id = $request->courseId;
                $user_coin->save();

                $subject = "Congratulations! Success Coins Allocated to Your Account!";
                // $content = "Completing your profile earned you $coin_price coins.";
                $content = "you have purchased $course_coin->title course";
                $plus = UserCoin::where("learner_id", $request->user_id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2);
                        $query->Where("type", "!=", "4");
                    })->sum("coins");
                // $deduct = UserCoin::where("learner_id",$learner['id'])->where("type",2)->Orwhere("type",4)->sum("coins");

                $deduct = UserCoin::where("learner_id", $request->user_id)
                    ->where(function ($query) {
                        $query->where("type", "=", 2)
                            ->orWhere("type", "=", 4);
                    })->sum("coins");

                $total = $plus - $deduct;

                $learner = Learner::where("id", $learner['id'])->first();
                $inst = new EarnCoinMail($content, $subject, $learner, $course_coin->course_coin, $total);
                if ($learner) {
                    \Mail::to($learner->email)->send($inst);
                }
            }

            // redeem
            if (isset($request->coin_used) && $request->coin_used != "" && $request->coin_used > 0) {
                if ($request->coin_used > 0) {
                    $user_coin = new UserCoin();
                    $user_coin->learner_id = $request->user_id;
                    $user_coin->coins = $request->coin_used;
                    $user_coin->type = 2;
                    //$user_coin->comment = "Redeem coins to $course_coin->title";
                    $user_coin->comment = "You have redeemed $request->coin_used success coin due to you have purchased $course_coin->title course";
                    $user_coin->course_id = $request->courseId;
                    $user_coin->save();

                    $subject = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                    $content = "you have redeemed while purchasing $course_coin->title course";
                    $plus = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    //$user_coin = UserCoin::where("learner_id", $learner['id'])->where("type", 6)->first();

                    $learner = Learner::where("id", $request->user_id)->first();
                    $inst = new RedeemCoinMail($content, $subject, $learner, $total, $request->coin_used);
                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                }
            }

            $data = Helper::apiResonse(1, "Payment Successfully", $result);
            return response()->json($data, 200);
        } else { // failed entry
            try {
                DB::beginTransaction();

                $price_new = get_course_plan_price($request->planId) ?? 0.00;
                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'full_name' => $learner['name'] ?? null,
                    'email' => $learner['email'] ?? null,
                    'mobile' => $learner['mobile'] ?? null,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'course_id' => $request->courseId ?? null,
                    'plan_id' => $request->planId ?? null,
                    'order_status' => 2,
                    'payment_order_status' => $status,
                    'payment_gateway' => $payment_gateway,
                    'transaction_id' => $transactionId,
                    'expire_at' => $expiredAt,
                    'price' => $price_new,
                ]);

                DB::commit();

            } catch (\Exception $e) {

                DB::rollBack();
                LogError::create([
                    "file" => $e->getFile(),
                    "flineile" => $e->getLine(),
                    "trace" => $e->getTraceAsString(),
                    "request" => json_encode($request->all()),
                ]);
                $data = Helper::apiResonse(0, "Payment Failed Try again Later..", []);
                return response()->json($data, 200);
            }

        }
    }

    public function getPaymentFailStore(Request $request)
    {

        $api = $this->razorpayConfig();

    }

    public function testPushNotification(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'courseId' => 'required',
        ]);

        // dd($api);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $learner = Helper::getLearnerData(request()->user_id);
        if (!empty($learner)) {
            $course_title = Course::where('id', $request->courseId)->first()->title;
            $subject = "Testing push notification";
            $content = "Course $course_title purchased successfully";
            // need to append country code with phone number dynamically
            $params['push']['to'] = $learner['id'] ?? null;
            $params['common'] = [
                'course_id' => $request->courseId,
                'learner_id' => $learner['id'] ?? null,
                'type' => 1,
            ];
            send_notification($params, 'payment', ['push'], $subject, $content);
            $data = Helper::apiResonse(1, "Sent Successfully");
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "An error occurred while sending test push notification", []);
            return response()->json($data, 400);
        }
    }

    public function viewInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        } else {
            $invoiceUrl = UserCourse::where('id', $request->orderId)->value('invoice');
            $result[] = !empty($invoiceUrl) ? Helper::getImageUrl($invoiceUrl) : '';
            $data = Helper::apiResonse(1, "Payment Successfully", $result);
            return response()->json($data, 200);
        }
        $data = Helper::apiResonse(0, "Payment Failed Try again Later..", []);
        return response()->json($data, 400);
    }

    public function getSubscriptionId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'planId' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        } else {
            $course = CoursePlan::select('plan_id', 'id', 'plan_type')
                ->whereNull('deleted_at')
                ->whereNotNull('plan_id')
                ->where('plan_type', CoursePlan::PLAN_RECURRING)
                ->where('id', $request->planId)
                ->get()
                ->first();
            if (empty($course)) {
                $data = Helper::apiResonse(0, "Course Plan Not Found", []);
                return response()->json($data, 400);
            }

            try {
                $api = $this->razorpayConfig();
                $subscription = $api->subscription->create(
                    array(
                        'plan_id' => $course['plan_id'],
                        'customer_notify' => 1,
                        'quantity' => 1,
                        'total_count' => 1,
                        'notes' => array('key1' => 'Starting Recuring payment'),
                    )
                );
                $result[] = $subscription['id'];
                $data = Helper::apiResonse(1, "Subscription Id Generated Successfully", $result);
                return response()->json($data, 200);
            } catch (\Exception $e) {
                $result[] = '';
                $data = Helper::apiResonse(0, "Plan Not find for Payment Successfully", $result);
                return response()->json($data, 400);
            }
        }
    }

    public function saveFreePlan(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'planId' => 'required',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        } else {
            $course = CoursePlan::select('course_id', 'id', 'plan_type')
                ->whereNull('deleted_at')
            //->where('plan_type', CoursePlan::PLAN_FREE)
                ->where('id', $request->planId)
                ->get()
                ->first();

            if (empty($course)) {
                $data = Helper::apiResonse(0, "Course Plan Not Found", []);
                return response()->json($data, 400);
            }

            $learner = Helper::getLearnerData(request()->user_id);
            if (empty($learner)) {
                $data = Helper::apiResonse(0, "Learner Not Found", []);
                return response()->json($data, 400);
            }

            $expiredAt = get_plan_expire($request->planId);
            if (isset($course) && !empty($course)) {
                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'course_id' => $course->course_id ? $course->course_id : null,
                    'plan_id' => $request->planId,
                    'price' => get_course_plan_price($request->planId) ?? 0.00,
                    'full_name' => $learner['name'] ?? null,
                    'email' => $learner['email'] ?? null,
                    'mobile' => $learner['mobile'] ?? null,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'order_status' => 3,
                    'payment_order_status' => null,
                    'payment_gateway' => null,
                    'transaction_id' => null,
                    'expire_at' => $expiredAt,
                    'transaction_response' => null,
                    'user_coin' => $request->coin_used,
                    'per_coin_price' => (config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : 0),
                    'after_deduction_price' => $request->final_payble_price,
                    'coupon_id' => $request->coupon_id,
                    'after_coupon_applied_deduction_price' => $request->coupon_amount,

                ]);
                $result[] = $userCourse->id;
                $user_course_id = $userCourse->id;
                $learner = Helper::getLearnerData(request()->user_id);
                $course_title = Course::where('id', $course->course_id)->first(["title", "type"]);
                if ($learner) {
                    $learnerData = Learner::with('country:id,phonecode')->where("id", $learner->id)->get()->first();
                    if ($course_title->type == 2) {
                        $subject = 'Added free package successfully';
                        $content = "Package $course_title->title Added successfully";
                    } else {

                        $subject = 'Added free course successfully';
                        $content = "Course $course_title->title Added successfully";
                    }
                    // $subject = 'Added free course successfully';
                    // $content = "Course $course_title->title purchased successfully";
                    $params['email']['user_course_id'] = $user_course_id;
                    $params['email']['to'] = $learnerData->email;
                    $params['web']['to'] = $learnerData->id;
                    // need to append country code with phone number dynamically
                    $params['push']['to'] = $learnerData->id;

                    // need to append country code with phone number dynamically
                    // $params['whatsapp']['to'] = $learnerData->country->phonecode . "" . $learnerData->mobile;
                    // $params['whatsapp']['message'] = $course_title->title;
                    // $params['whatsapp']['template'] = 'course_purchased';
                    $params['common'] = [
                        'course_id' => $course->course_id,
                        'learner_id' => $learnerData->id,
                        'type' => 3,
                    ];
                    $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
                }

                // coin earn
                $course_coin = Course::where("id", $course->course_id)->first();
                // dd($course_coin);
                if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
                    $user_coin = new UserCoin();
                    $user_coin->learner_id = $request->user_id;
                    $user_coin->coins = $course_coin->course_coin;
                    //$user_coin->comment = "These coins were earned from the purchase of the $course_coin->title";
                    $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                    $user_coin->type = 1;

                    $user_coin->course_id = $course->course_id;
                    $user_coin->save();

                    $subject = "Congratulations! Success Coins Allocated to Your Account!";
                    // $content = "Completing your profile earned you $coin_price coins.";
                    $content = "you have purchased $course_coin->title course";
                    $plus = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    // $deduct = UserCoin::where("learner_id",$learner['id'])->where("type",2)->Orwhere("type",4)->sum("coins");

                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $learner = Learner::where("id", $learner['id'])->first();
                    $inst = new EarnCoinMail($content, $subject, $learner, $course_coin->course_coin, $total);
                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                }

                // redeem
                if (isset($request->coin_used) && $request->coin_used != "") {
                    if ($request->coin_used > 0) {
                        $user_coin = new UserCoin();
                        $user_coin->learner_id = $request->user_id;
                        $user_coin->coins = $request->coin_used;
                        $user_coin->type = 2;
                        // $user_coin->comment = "Redeem coins to $course_coin->title";
                        $user_coin->comment = "You have redeemed $request->coin_used success coin due to you have purchased $course_coin->title course";
                        $user_coin->course_id = $course->course_id;
                        $user_coin->save();

                        $subject = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                        $content = "you have redeemed while purchasing $course_coin->title course";
                        $plus = UserCoin::where("learner_id", $request->user_id)
                            ->where(function ($query) {
                                $query->where("type", "!=", 2);
                                $query->Where("type", "!=", "4");
                            })->sum("coins");

                        $deduct = UserCoin::where("learner_id", $request->user_id)
                            ->where(function ($query) {
                                $query->where("type", "=", 2)
                                    ->orWhere("type", "=", 4);
                            })->sum("coins");

                        $total = $plus - $deduct;

                        //$user_coin = UserCoin::where("learner_id", $learner['id'])->where("type", 6)->first();

                        $learner = Learner::where("id", $request->user_id)->first();
                        $inst = new RedeemCoinMail($content, $subject, $learner, $total, $request->coin_used);
                        if ($learner) {
                            \Mail::to($learner->email)->send($inst);
                        }
                    }
                }

                // Coupon usage log
                if ($request->filled('coupon_id')) {
                    $coupon_log = new CouponUsage();
                    $coupon_log->coupon_id = $request->coupon_id;
                    $coupon_log->learner_id = $learner['id'];
                    $coupon_log->course_id = $course->course_id;
                    $coupon_log->is_used = 1;
                    $coupon_log->save();
                }

                $course_name = $course_title->type == 2 ? "Package" : "Course";
                $data = Helper::apiResonse(1, "$course_name Added Successfully", $result);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, "Something Went Wrong.Try again Later..", []);
                return response()->json($data, 400);
            }
        }
    }

    public function getRazorpayPaymentLink(Request $request)
    {

        $url = route('send_razorpay_paymentLink', [
            "plan_id" => $request->plan_id,
            "final_price" => $request->final_price,
            "actual_price" => $request->actual_price,
            'coin_used' => $request->coin_used,
            'coupon_id' => $request->coupon_id,
            "discount" => $request->discount_price,
            "payment_status" => false,
            "user_id" => $request->user_id,
            "advisement" => $request->advisement,
            // "data"=>$data
        ]);

        return response()->json([
            'status' => '1',
            'message' => 'Success',
            'data' => [
                'longurl' => $url,
            ],
        ]);

    }

    public function sendRazorpayPaymentLink(Request $request)
    {

        $course = CoursePlan::with('course')
            ->where('id', $request->plan_id)
            ->first();
        $planId = $request->plan_id;

        $final_price = $request->final_price;
        $actual_price = $request->actual_price;
        $coin_used = $request->coin_used;
        $coupon_id = $request->coupon_id;
        $discount = $request->discount_price;

        $data['advisement'] = $request->advisement;

        $learner = Learner::where("id", $request->user_id)->first();
        // $learner = Learner::where("id", 89892)->first();

        return view('front.checkout.checkout_api', $data, compact('course', 'planId', 'final_price', 'coin_used', 'coupon_id', 'actual_price', 'discount', "learner"));
        // return redirect()->route('failed_success_redirect');
    }

    public function storePaymentApiSuccess(Request $request)
    {

        $learner = Learner::where("id", $request->user_id)->first();

        if ($request->status == "fail") {

            $content = @$request['error_msg']['description'];

            $expiredAt = get_plan_expire($request->plan_id);
            $userCourse = UserCourse::create([
                'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
                'course_id' => $request->course_id ?? null,
                'plan_id' => $request->plan_id ?? null,
                'order_status' => 2,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment_id) && !empty($payment_id) ? $payment_id : null,
                'expire_at' => $expiredAt,
                'price' => $request->amount ?? 0.00,
            ]);
            $url = route('failed_success_redirect', [
                "payment_status" => 0,
            ]);

            return response()->json([
                'success' => 0,
                'url' => $url,
            ], 200);
        }

        $input = $request->all();

        $api = $this->razorpayConfig();
        // $payment = $api->payment->fetch($input['razorpay_payment_id']);

        $amount = (int) ($request->input('amount') * 100);
        if ($payment->status == 'authorized') {
            $payment = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $amount));

        } else {
            return response()->json([
                'success' => 0,
                'message' => "Your Aren't authorized",

            ], 200);

        }

        if ($payment->status == "failed") {
            $userCourse = UserCourse::create([
                'learner_id' => @$learner->id,
                'course_id' => $input['courseId'] ?? null,
                'subscription_id' => request()->has('subscription_id') && request()->filled('subscription_id') ? $input['subscription_id'] : null,
                'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
                'plan_id' => $input['planId'] ?? null,
                'order_status' => 2,
                'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
                'price' => $request->actual_price,
                'full_name' => $learner->name ?? @$payment['card']['name'],

                'email' => isset($payment) && !empty($payment) ? $payment->email : null,
                'mobile' => $learner->mobile,
                'country_id' => $learner->country_id ?? "",
                'state_id' => @$learner->state_id,
                'city_id' => @$learner->city_id,
                'transaction_response' => json_encode($payment->toArray()),
            ]);

            // return response()->json([
            //     'success' => 0,
            //     'message' => $payment->error_description,
            // ], 200);
            $url = route('failed_success_redirect', [
                "payment_status" => 0,
            ]);

            return response()->json([
                'success' => 0,
                'url' => $url,
            ], 200);
        }

        $course_title = request()->has('course_title') && request()->filled('course_title') ? $input['course_title'] : null;
        if (count($input) && !empty($input['razorpay_payment_id'])) {
            // try {
            DB::beginTransaction();
            $payment_id = $input['razorpay_payment_id'];

            $expiredAt = get_plan_expire($input['planId']);

            $userCourse = UserCourse::create([
                'learner_id' => @$learner->id,
                'course_id' => $input['courseId'] ?? null,
                'subscription_id' => request()->has('subscription_id') && request()->filled('subscription_id') ? $input['subscription_id'] : null,
                'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
                'plan_id' => $input['planId'] ?? null,
                'order_status' => isset($payment) && !empty($payment) && $payment->status != 'failed' ? 1 : 2,
                'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
                'expire_at' => $expiredAt,
                'price' => $request->actual_price,

                'full_name' => $learner->name ?? @$payment['card']['name'],

                'email' => isset($payment) && !empty($payment) ? $payment->email : null,
                'mobile' => @$learner->mobile,
                'country_id' => @$learner->country_id,
                'state_id' => @$learner->state_id,

                'city_id' => @$learner->city_id,
                'transaction_response' => json_encode($payment->toArray()),
                'user_coin' => $request->coin_used,
                'per_coin_price' => config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null,
                'coupon_id' => $request->coupon_id,
                'after_deduction_price' => $input['amount'],
                'after_coupon_applied_deduction_price' => $request->discount != "" ? $request->actual_price - $request->discount : 0,
            ]);

            $user_course_id = $userCourse->id;

            createInvoice($user_course_id);

            // earn
            $course_coin = Course::where("id", $input['courseId'])->first();

            if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
                $user_coin = new UserCoin();
                $user_coin->learner_id = @$learner->id;
                $user_coin->coins = $course_coin->course_coin;
                $user_coin->type = 1;
                $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                $user_coin->course_id = $input['courseId'];
                $user_coin->save();

                $subject2 = "Congratulations! Success Coins Allocated to Your Account!";
                $content2 = "you have purchased $course_coin->title course";
                $plus = UserCoin::where("learner_id", @$learner->id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2)
                            ->Where("type", "!=", 4);
                    })->sum("coins");

                $total = $plus;

                $inst = new EarnCoinMail($content2, $subject2, $learner, $course_coin->course_coin, $total);
                if ($learner) {
                    \Mail::to($learner->email ?? $payment['email'])->send($inst);
                }
            }

            // redeem
            if ($request->coin_used != "") {
                if ($request->coin_used) {
                    if ($request->coin_used > 0) {
                        $user_coin = new UserCoin();
                        $user_coin->learner_id = @$learner->id;
                        $user_coin->coins = $request->coin_used;
                        $user_coin->type = 2;
                        $user_coin->comment = "You have redeemed $request->coin_used success coin due to you have purchased $course_coin->title course";
                        $user_coin->course_id = $input['courseId'];
                        $user_coin->save();

                        $subject3 = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                        $content3 = "you have redeemed while purchasing $course_coin->title course";
                        $plus = UserCoin::where("learner_id", $request->user_id)
                            ->where(function ($query) {
                                $query->where("type", "!=", 2);
                                $query->Where("type", "!=", "4");
                            })->sum("coins");

                        $deduct = UserCoin::where("learner_id", $request->user_id)
                            ->where(function ($query) {
                                $query->where("type", "=", 2)
                                    ->orWhere("type", "=", 4);
                            })->sum("coins");

                        $total = $plus - $deduct;

                        $learner = Learner::where("id", $request->user_id)->first();
                        $inst = new RedeemCoinMail($content3, $subject3, $learner, $total, $request->coin);
                        if ($learner) {
                            \Mail::to($learner->email)->send($inst);
                        }
                    }
                }
            }

            if ($request->coupon_id != "") {
                $CouponCourse = CouponCourse::where("coupon_id", $request->coupon_id)->where("course_id", $request->courseId)->first();
                $coupon = new CouponUsage();
                $coupon->learner_id = @$learner->id;
                $coupon->coupon_id = $CouponCourse->id;
                $coupon->is_used = 1;
                $coupon->course_id = $request->courseId;
                $coupon->save();
            }
            $subject = "\u{1F91D} You are now enrolled in $course_title";

            $content = "$course_title has been purchased successfully.";
            $params['common'] = [
                'course_id' => $input['courseId'],
                'learner_id' => $learner->id,
                'type' => 1,
            ];

            $params['email']['user_course_id'] = $user_course_id;
            $params['email']['to'] = '';
            $params['web']['to'] = $learner->id;
            $params['push']['to'] = $learner->id;

            if ($user_course_id) {
                $invoiceData = $userCourse;
            }

            $inst = new PaymentNotification($content, $subject, $userCourse);

            if ($learner->email != '') {
                \Mail::to($learner->email)->send($inst);
                $this->notified[] = true;
                $ids = Learner::where('email', $learner->email)->pluck('id')->toArray();
                if (!empty($ids)) {
                    $this->data['email'] = $ids;
                }
            }
            $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);

            DB::commit();

            $this->sessionDestroy();
            $course = Course::where("id", $request->courseId)->select("type")->first();
            $url = route('failed_success_redirect', [
                "plan_id" => $request->plan_id,
                "final_price" => $request->final_price,
                "actual_price" => $request->actual_price,
                'coin_used' => $request->coin_used,
                'coupon_id' => $request->coupon_id,
                "discount" => $request->discount_price,
                "payment_status" => 1,
                "user_id" => $request->user_id,
                "courseId" => $request->courseId,
                "orderId" => $userCourse->id,
                "isCombineCourse" => $course->type == 1 ? 1 : 2,
                "advisement" => $request->advisement,
            ]);

            if ($request->advisement == 1) {
                return response()->json([
                    'success' => 2,
                    'url' => $url,
                ], 200);
            }
            return response()->json([
                'success' => 1,
                'url' => $url,
            ], 200);

        }

    }

    public function failedSuccessRedirect()
    {
        return view("front.checkout.failed_success_redirect");
    }

    public static function sessionDestroy()
    {
        session()->forget('sessionAddress');
    }

}
