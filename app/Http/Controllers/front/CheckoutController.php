<?php

namespace App\Http\Controllers\front;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\EarnCoinMail;
use App\Mail\PaymentNotification;
use App\Mail\RedeemCoinMail;
use App\Models\Chapter;
use App\Models\Coupon;
use App\Models\CouponCourse;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\Learner;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserCoin;
// use Illuminate\Support\Facades\Validator;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use libphonenumber\PhoneNumberUtil;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{

    public function handleWebhook(Request $request)
    {
        // Verify Razorpay webhook signature
        $webhookSecret = "rzp_test_OaxONu6UMEZQmW";
        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        if ($this->isValidSignature($payload, $signature, $webhookSecret)) {
            $event = $request->event; // e.g., 'payment.captured', 'order.paid'

            dd($event);
            // Handle the event
            switch ($event) {
                case 'payment.captured':

                    break;
                case 'order.paid':
                    break;
            }

            return response()->json(['status' => 'success'], 200);
        } else {
            // Log invalid signatures or handle them as needed
            Log::warning('Invalid Razorpay webhook signature detected.');
            return response()->json(['status' => 'invalid signature'], 400);
        }
    }

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

    public function index($planId, $updated_new_coin, $coupon_id, Request $request)
    {
        // dd($request->all());
        $onecoinprice = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;
        $planId = Crypt::decrypt($planId);

        $course = CoursePlan::with('course')
            ->where('id', $planId)
            ->get()
            ->first();

        $price = CoursePlan::where('id', $planId)->first();
        $final_price = $price->final_payable_price;
        $actual_price = $price->final_payable_price;

        $coins = "";
        $coupon_id_new = "";
        $discount = "";

        if (($updated_new_coin !== '') && ($updated_new_coin != 0) && ($coupon_id !== 'null' && $coupon_id != "0")) {

            $coins = Crypt::decrypt($updated_new_coin);

            $coupon_id = Crypt::decrypt($request->coupon_id);

            $coupon = Coupon::where("id", $coupon_id)->first();
            $coupon_id_new = $coupon->id;
            if ($coupon->type == 1) {
                $percentage = ($price->final_payable_price * $coupon->value) / 100;
                if ($percentage > $coupon->max_amount) {
                    $discount = $price->final_payable_price - $coupon->max_amount;
                    $final_price = $discount - ($coins * $onecoinprice);
                } else {
                    $discount = $price->final_payable_price - round($percentage);
                    $final_price = $discount - ($coins * $onecoinprice);
                }
            } else {
                $discount = $price->final_payable_price - $coupon->value;

                $final_price = $discount - ($coins * $onecoinprice);
            }
        }
        // dd($coupon_id);
        if (($updated_new_coin == 0 || $updated_new_coin == "") && ($coupon_id !== 'null' && $coupon_id != "0")) {

            $coupon_id = Crypt::decrypt($request->coupon_id);

            $coupon = Coupon::where("id", $coupon_id)->first();
            $coupon_id_new = $coupon->id;
            if ($coupon->type == 1) {
                $percentage = ($price->final_payable_price * $coupon->value) / 100;
                if ($percentage > $coupon->max_amount) {
                    $discount = $price->final_payable_price - $coupon->max_amount;
                    $final_price = $discount;
                } else {
                    $discount = $price->final_payable_price - round($percentage);
                    $final_price = $discount;
                }
            } else {
                $discount = $price->final_payable_price - $coupon->value;

                $final_price = $discount;
            }
        }

        if (($updated_new_coin != 0 && $updated_new_coin != "" && $updated_new_coin != "undefined") && ($coupon_id == 'null')) {

            $coins = Crypt::decrypt($updated_new_coin);

            if ($price->final_payable_price > ($coins * $onecoinprice)) {

                $final_price = $price->final_payable_price - ($coins * $onecoinprice);
            } else {

                $final_price = ($coins * $onecoinprice) - $price->final_payable_price;
            }
        }

        $data['advancement'] = $request->advancement;
        $data['chapter_id'] = $request->chapter_id;
        $data['advancement_course_id'] = $request->advancement_course_id;

        $instamojo_status = config()->has('settings.instamojo_status') ? config('settings.instamojo_status') : null;
        if ($instamojo_status == 1) {
            if ($final_price < 10) {

                $notification['type'] = "sweet-alert";
                $notification['status'] = "warning";
                $notification['title'] = "warning";
                $notification['msg'] = "Minimum ₹10 can be purchased using instamojo";
                return redirect()->back()->with('notification', $notification);
            }
        }

        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;

        // $learner = Learner::where("id", @auth()->guard("learner")->user()->id)->with("state")->first();

        // if (@$learner->country_id==1) {
        //     if (strtolower(@$learner->state->name) == "gujarat") {
        //         $price_sgst = $final_price * ((float) $sgst / 100);
        //         $price_cgst = $final_price * ((float) $cgst  / 100);

        //         $final_price = $final_price + (ceil($price_cgst) + ceil($price_sgst));

        //         // $final_price = $final_price + $total_price;
        //     } else {
        //         $total_price = $final_price * ((float) $igst) / 100;
        //         $final_price = $final_price + $total_price;
        //     }

        //     $final_price =  number_format(ceil($final_price), 2, '.', '');

        // }
        $final_price = ceil($final_price);

        if ($request->status) {
            if (Crypt::decrypt($request->status) == "true") {
                if (view()->exists('front.checkout.new_checkout')) {

                    return view('front.checkout.new_checkout', $data, compact('course', 'planId', 'final_price', 'coins', 'coupon_id_new', 'actual_price', 'discount'));
                }
            }
        }

        if (view()->exists('front.checkout.checkout')) {
            return view('front.checkout.checkout', $data, compact('course', 'planId', 'final_price', 'coins', 'coupon_id_new', 'actual_price', 'discount'));
            // return view('test', compact('course','planId'));
        }
        abort(404);
    }

    /**
     * Save Checkout data
     */
    public function store(Request $request)
    {

        if ($request->status == "failed") {
            // $learnerData = Learner::with('country:id,phonecode')->where("id", auth()->guard('learner')->user()->id)->get()->first();
            // $subject = 'Payment failed';
            $content = @$request['error_msg']['description'];
            $payment_id = @$request['error_msg']['metadata']['payment_id'];
            // dd($payment_id);
            // // dd($content);
            // $params['common'] = [
            //     'course_id' => $request->course_id,
            //     'learner_id' => auth()->guard('learner')->user()->id,
            //     'type' => 1,
            // ];
            // $params['web']['to'] = Auth::guard('learner')->id();
            // $params['push']['to'] = auth()->guard('learner')->user()->id;
            // $res = send_notification($params, 'payment', ['push', 'web'], $subject, $content);
            // return response()->json([
            //     'success' => false,
            //     // 'url' => route('my.course'),
            //     'message' => $request->error_msg,
            // ], 201);

            if (!auth()->guard('learner')->check()) {
                // $trimmed = substr($payment['currency'], 0, 2);

                // $contact = substr($payment['contact'], 3, 10);

                // $country_id = DB::table("countries")->where("code", $trimmed)->first()->id;
                // $learner = Learner::where(["mobile" => $contact, "country_id" => $country_id])->first();

                $phoneUtil = PhoneNumberUtil::getInstance();

                $phoneNumber = $payment["contact"];
                $parsedNumber = $phoneUtil->parse($phoneNumber, null);
                $country_code = $parsedNumber->getCountryCode();

                $country_id = DB::table("countries")->where("phonecode", $country_code)->first()->id;

                $countryMapping = [
                    231 => [4, 231, 232, 38], // US // 1 phonecode
                    8 => [8, 46, 96, 162], // Antarctica // 672 phonecode
                    13 => [13, 45], // Australia // 61  phonecode
                    29 => [29, 164, 209], // Bouvet Island // 47  phonecode
                    48 => [48, 141], // Comoros / 269  phonecode
                    49 => [49, 50], // Congo // 242  phonecode
                    71 => [71, 203], // Falkland Islands // 500  phonecode
                    78 => [78, 179], // French Southern Territories // 262  phonecode
                    107 => [107, 236], // Italy // 39  phonecode
                    148 => [148, 242], // Morocco / 212  phonecode
                    157 => [157, 174], // New Zealand // 64  phonecode
                ];

                foreach ($countryMapping as $mappedId => $ids) {
                    if (in_array($country_id, $ids)) {
                        $request->merge(['country_id' => $mappedId]);
                        break;
                    } else {
                        $request->merge(['country_id' => $country_id]);
                    }
                }

                $data = $request->all();

                $learner = Learner::where(["mobile" => $parsedNumber->getNationalNumber(), "country_id" => $data['country_id']])->first();
                if (!$learner) {

                    $learner = Learner::create([
                        "name" => '',
                        "email" => $payment['email'] != "void@razorpay.com" ? $payment['email'] : '',
                        "mobile" => $parsedNumber->getNationalNumber(),
                        "country_id" => $data['country_id'],
                    ]);
                }
            }

            if (auth()->guard('learner')->user()) {
                Learner::where('id', auth()->guard('learner')->user()->id)
                    ->update(['state_id' => $input['state_id_hidden'] ?? auth()->guard('learner')->user()->state_id, 'city_id' => $input['city_id_hidden'] ?? auth()->guard('learner')->user()->city_id]);
            }

            if (auth()->guard('learner')->user()) {
                Learner::where('id', auth()->guard('learner')->user()->id)->update(['name' => auth()->guard('learner')->user()->name ?? null]);
            }
            if (auth()->guard('learner')->user()) {
                Learner::where('id', auth()->guard('learner')->user()->id)->update(['email' => auth()->guard('learner')->user()->email ?? null]);
            }
            $expiredAt = get_plan_expire($request->plan_id);
            $userCourse = UserCourse::create([
                'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
                'course_id' => $request->course_id ?? null,
                'plan_id' => $request->plan_id ?? null,
                'full_name' => auth()->guard('learner')->user()->name ?? null,
                'email' => auth()->guard('learner')->user()->email ?? null,
                'mobile' => auth()->guard('learner')->user()->mobile ?? null,
                'country_id' => auth()->guard('learner')->user()->country_id ?? null,
                'state_id' => auth()->guard('learner')->user()->state_id ?? null,
                'city_id' => auth()->guard('learner')->user()->city_id ?? null,

                'order_status' => 2,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment_id) && !empty($payment_id) ? $payment_id : null,
                'expire_at' => $expiredAt,
                'price' => $request->amount ?? 0.00,
            ]);
            $params['push']['to'] = auth()->guard('learner')->user()->id;
            // $res = send_notification($params, 'payment', ['push', 'web'], $subject, $content);
            return response()->json([
                'success' => false,
                'message' => $content,
            ], 201);
        }

        $input = $request->all();
        // dd($input);
        $api = $this->razorpayConfig();

        // $after_sucess = $api->plan->create(array('period' => 'weekly', 'interval' => 1, 'item' => array('name' => 'Test Weekly 1 plan', 'description' => 'Description for the weekly 1 plan', 'amount' => 600, 'currency' => 'INR'),'notes'=> array('key1'=> 'value3','key2'=> 'value2')));
        // dd($after_sucess);

        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        $amount = (int) ($request->input('amount') * 100);
        if ($payment->status == 'authorized') {
            $payment = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $amount));

        } else {
            return response()->json([
                'success' => false,
                'url' => route('course.list'),
                'message' => "Your Aren't authorized",
            ], 200);

        }

        if ($payment->status == "failed") {
            $userCourse = UserCourse::create([
                'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
                'course_id' => $input['courseId'] ?? null,
                'subscription_id' => request()->has('subscription_id') && request()->filled('subscription_id') ? $input['subscription_id'] : null,
                'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
                'plan_id' => $input['planId'] ?? null,
                'order_status' => 2,
                'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
                'price' => $request->actual_price,
                'full_name' => auth()->guard('learner')->user() ? auth()->guard('learner')->user()->name : @$payment['card']['name'],

                'email' => isset($payment) && !empty($payment) ? $payment->email : null,
                'mobile' => auth()->guard('learner')->user()->mobile ?? $contact,
                'country_id' => auth()->guard('learner')->user()->country_id ?? $country_id,
                'state_id' => @auth()->guard('learner')->user()->state_id,
                'city_id' => @auth()->guard('learner')->user()->city_id,
                'transaction_response' => json_encode($payment->toArray()),
            ]);

            return response()->json([
                'success' => false,
                'url' => route('course.list'),
                'message' => $payment->error_description,
            ], 200);
        }

        // dd($payment['email']);

        $course_title = request()->has('course_title') && request()->filled('course_title') ? $input['course_title'] : null;
        if (count($input) && !empty($input['razorpay_payment_id'])) {
            // try {
            // DB::beginTransaction();
            $payment_id = $input['razorpay_payment_id'];

            if (!auth()->guard('learner')->check()) {
                // $trimmed = substr($payment['currency'], 0, 2);

                // $contact = substr($payment['contact'], 3, 10);

                $phoneUtil = PhoneNumberUtil::getInstance();

                $phoneNumber = $payment["contact"];
                $parsedNumber = $phoneUtil->parse($phoneNumber, null);
                $country_code = $parsedNumber->getCountryCode();

                $country_id = DB::table("countries")->where("phonecode", $country_code)->first()->id;

                $countryMapping = [
                    231 => [4, 231, 232, 38], // US // 1 phonecode
                    8 => [8, 46, 96, 162], // Antarctica // 672 phonecode
                    13 => [13, 45], // Australia // 61  phonecode
                    29 => [29, 164, 209], // Bouvet Island // 47  phonecode
                    48 => [48, 141], // Comoros / 269  phonecode
                    49 => [49, 50], // Congo // 242  phonecode
                    71 => [71, 203], // Falkland Islands // 500  phonecode
                    78 => [78, 179], // French Southern Territories // 262  phonecode
                    107 => [107, 236], // Italy // 39  phonecode
                    148 => [148, 242], // Morocco / 212  phonecode
                    157 => [157, 174], // New Zealand // 64  phonecode
                ];

                foreach ($countryMapping as $mappedId => $ids) {
                    if (in_array($country_id, $ids)) {
                        $request->merge(['country_id' => $mappedId]);
                        break;
                    } else {
                        $request->merge(['country_id' => $country_id]);
                    }
                }

                $data = $request->all();

                $learner = Learner::where(["mobile" => $parsedNumber->getNationalNumber(), "country_id" => $data['country_id']])->first();
                if (!$learner) {

                    $learner = Learner::create([
                        "name" => '',
                        "email" => $payment['email'] != "void@razorpay.com" ? $payment['email'] : '',
                        "mobile" => $parsedNumber->getNationalNumber(),
                        "country_id" => $data['country_id'],
                    ]);

                }
            }

            if (auth()->guard('learner')->user()) {
                Learner::where('id', auth()->guard('learner')->user()->id)
                    ->update(
                        [
                            'state_id' => $input['state_id_hidden'] ?? auth()->guard('learner')->user()->state_id,
                            'city_id' => $input['city_id_hidden'] ?? auth()->guard('learner')->user()->city_id,
                            'name' => auth()->guard('learner')->user()->name ?? null,
                            'email' => auth()->guard('learner')->user()->email ?? null,

                        ]);
            }

            // if (auth()->guard('learner')->user()) {
            //     Learner::where('id', auth()->guard('learner')->user()->id)
            //     ->update(
            //         [
            //             'name' => auth()->guard('learner')->user()->name ?? null
            //         ]);
            // }
            // if (auth()->guard('learner')->user()) {
            //     Learner::where('id', auth()->guard('learner')->user()->id)
            //     ->update(
            //         [
            //             'email' => auth()->guard('learner')->user()->email ?? null
            //         ]);
            // }

            $expiredAt = get_plan_expire($input['planId']);

            //Payment record create below
            $userCourse = UserCourse::create([
                'learner_id' => auth()->guard('learner')->user()->id ?? $learner->id,
                'course_id' => $input['courseId'] ?? null,
                'subscription_id' => request()->has('subscription_id') && request()->filled('subscription_id') ? $input['subscription_id'] : null,
                'is_subscription' => isset($payment) && !empty($payment) ? $payment->order_id : null,
                'plan_id' => $input['planId'] ?? null,
                'order_status' => isset($payment) && !empty($payment) && $payment->status != 'failed' ? 1 : 2,
                'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : null,
                'payment_gateway' => 1,
                'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : null,
                'expire_at' => $expiredAt,
                // 'price' => $input['amount'] ?? 0.00,
                'price' => $request->actual_price,
                // 'full_name' => request()->has('full_name_hidden') && request()->filled('full_name_hidden') ? $input['full_name_hidden'] : auth()->guard('learner')->user()->name,

                'full_name' => auth()->guard('learner')->user() ? auth()->guard('learner')->user()->name : @$payment['card']['name'],

                'email' => isset($payment) && !empty($payment) ? $payment->email : null,
                // 'mobile' => request()->has('mobile_hidden') && request()->filled('mobile_hidden') ? $input['mobile_hidden'] : auth()->guard('learner')->user()->mobile,
                'mobile' => auth()->guard('learner')->user()->mobile ?? $parsedNumber->getNationalNumber(),
                // 'country_id' => request()->has('country_id_hidden') && request()->filled('country_id_hidden') ? $input['country_id_hidden'] : auth()->guard('learner')->user()->country_id,
                'country_id' => auth()->guard('learner')->user()->country_id ?? $data["country_id"],

                // 'state_id' => request()->has('state_id_hidden') && request()->filled('state_id_hidden') ? $input['state_id_hidden'] : auth()->guard('learner')->user()->state_id,
                'state_id' => @auth()->guard('learner')->user()->state_id,

                // 'city_id' => request()->has('city_id_hidden') && request()->filled('city_id_hidden') ? $input['city_id_hidden'] : auth()->guard('learner')->user()->city_id,
                'city_id' => @auth()->guard('learner')->user()->city_id,
                'transaction_response' => json_encode($payment->toArray()),
                'user_coin' => $request->coins,
                'per_coin_price' => config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null,
                'coupon_id' => $request->coupon_id,
                'after_deduction_price' => number_format($input["amount"], 2, '.', ''),
                'after_coupon_applied_deduction_price' => $request->discount != "" ? $request->actual_price - $request->discount : 0,
            ]);
            $user_course_id = $userCourse->id;

            createInvoice($user_course_id);
            // earn
            $course_coin = Course::where("id", $input['courseId'])->first();

            if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {
                $user_coin = new UserCoin();
                $user_coin->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
                $user_coin->coins = $course_coin->course_coin;
                $user_coin->type = 1;
                $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                $user_coin->course_id = $input['courseId'];
                $user_coin->save();

                $subject2 = "Congratulations! Success Coins Allocated to Your Account!";
                // $content = "Completing your profile earned you $coin_price coins.";
                $content2 = "you have purchased $course_coin->title course";
                $plus = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2)
                            ->Where("type", "!=", 4);
                    })->sum("coins");
                // $deduct = UserCoin::where("learner_id",$request->user_id)->where("type",2)->Orwhere("type",4)->sum("coins");

                $deduct = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
                    ->where(function ($query) {
                        $query->where("type", "=", 2)
                            ->orWhere("type", "=", 4);
                    })->sum("coins");

                $total = $plus - $deduct;

                $learner = Learner::where("id", auth()->guard("learner")->user()->id ?? $learner->id)->first();

                $inst = new EarnCoinMail($content2, $subject2, $learner, $course_coin->course_coin, $total);
                if ($learner) {
                    \Mail::to($learner->email ?? $payment['email'])->send($inst);
                }
            }

            // redeem
            if ($request->coins != "") {
                if ($request->coins) {
                    if ($request->coins > 0) {
                        $user_coin = new UserCoin();
                        $user_coin->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
                        $user_coin->coins = $request->coins;
                        $user_coin->type = 2;
                        $user_coin->comment = "You have redeemed $request->coins success coin due to you have purchased $course_coin->title course";
                        $user_coin->course_id = $input['courseId'];
                        $user_coin->save();

                        $subject3 = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                        $content3 = "you have redeemed while purchasing $course_coin->title course";
                        $plus = UserCoin::where("learner_id", $request->learner_id)
                            ->where(function ($query) {
                                $query->where("type", "!=", 2);
                                $query->Where("type", "!=", "4");
                            })->sum("coins");

                        $deduct = UserCoin::where("learner_id", $request->learner_id)
                            ->where(function ($query) {
                                $query->where("type", "=", 2)
                                    ->orWhere("type", "=", 4);
                            })->sum("coins");

                        $total = $plus - $deduct;

                        //$user_coin = UserCoin::where("learner_id", $request->learner_id)->where("type", 6)->first();

                        $learner = Learner::where("id", $request->learner_id)->first();
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
                $coupon->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
                $coupon->coupon_id = $CouponCourse->id;
                $coupon->is_used = 1;
                $coupon->course_id = $request->courseId;
                $coupon->save();
            }

            DB::commit();

            if (Auth::guard('learner')->check()) {

                $learnerData = Learner::with('country:id,phonecode')->where("id", auth()->guard('learner')->user()->id)->get()->first();
                $subject = "\u{1F91D} You are now enrolled in $course_title";

                $content = "$course_title has been purchased successfully.";
                $params['common'] = [
                    'course_id' => $input['courseId'],
                    'learner_id' => auth()->guard('learner')->user()->id,
                    'type' => 1,
                ];
                // dd($learnerData);

                $params['email']['user_course_id'] = $user_course_id;
                // $params['email']['to'] = $learnerData->email;
                $params['email']['to'] = '';
                $params['web']['to'] = Auth::guard('learner')->id();
                // need to append country code with phone number dynamically
                $params['push']['to'] = auth()->guard('learner')->user()->id;

                // need to append country code with phone number dynamically
                // $params['whatsapp']['to'] = $learnerData->country->phonecode . "" . $learnerData->mobile;
                // $params['whatsapp']['message'] = $course_title;
                // $params['whatsapp']['template'] = 'course_purchased';
                // dd($userCourse);
                if ($user_course_id) {
                    $invoiceData = $userCourse;
                }

                $inst = new PaymentNotification($content, $subject, $userCourse);

                if ($learnerData->email != '') {
                    \Mail::to($learnerData->email)->send($inst);
                    $this->notified[] = true;
                    $ids = Learner::where('email', $learnerData->email)->pluck('id')->toArray();
                    if (!empty($ids)) {
                        $this->data['email'] = $ids;
                    }
                }
                $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
                // $res = send_notification($params, 'payment', ['email', 'web'], $subject, $content);
            } else {

                $learnerData = $learner;

                $subject = "\u{1F91D} You are now enrolled in $course_title";

                $content = "$course_title has been purchased successfully.";
                $params['common'] = [
                    'course_id' => $input['courseId'],
                    'learner_id' => $learnerData->id,
                    'type' => 1,
                ];
                // dd($learnerData);

                $params['email']['user_course_id'] = $user_course_id;
                // $params['email']['to'] = $learnerData->email;
                $params['email']['to'] = '';
                $params['web']['to'] = $learnerData->id;
                // need to append country code with phone number dynamically
                $params['push']['to'] = $learnerData->id;

                // dd($userCourse);
                if ($user_course_id) {
                    $invoiceData = $userCourse;
                }
                $inst = new PaymentNotification($content, $subject, $userCourse);
                if ($learnerData->email != '') {
                    \Mail::to($learnerData->email)->send($inst);
                    $this->notified[] = true;
                    $ids = Learner::where('email', $learnerData->email)->pluck('id')->toArray();
                    if (!empty($ids)) {
                        $this->data['email'] = $ids;
                    }
                }

                $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
            }

            // DB::commit();
            //If user not logged in then new 'Learner' create and auto logged in.
            if (!auth()->guard('learner')->check()) {
                // auth()->guard('learner')->login($learner);
            }

            $this->sessionDestroy();

            $advancement_course = Course::where("id", $request->advancement_course_id)->select("slug", "id")->first();
            // dd($request->advancement_course_id);
            if ($request->advancement == 1) {
                $nextRecord = Chapter::where('id', '>', $request->chapter_id)->where("parent_id", "!=", 0)->first();
                if ($nextRecord != null) {
                    return response()->json([
                        'success' => true,
                        'url' => route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $nextRecord->id]),
                        'message' => 'Payment successfully Completed!',
                        "google_tag_status" => "success",
                    ], 200);
                } else {
                    $prevRecord = Chapter::where('id', '<', $request->chapter_id)->where("course_id", $advancement_course->id)
                        ->orderBy('id', 'desc')
                        ->first();
                    return response()->json([
                        'success' => true,
                        'url' => route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $prevRecord->id]),
                        'message' => 'Payment successfully Completed!',
                        "google_tag_status" => "success",
                    ], 200);
                }

            }

            return response()->json([
                'success' => true,
                'url' => route('my.course'),
                'message' => 'Payment successfully Completed!',
                "google_tag_status" => "success",
            ], 200);
            // } catch (\Exception $e) {
            //     DB::rollback();
            //     return response()->json([
            //         'success' => false,
            //         'url' => route('course.list'),
            //         'message' => 'Something wrong.' . $e->getMessage(),
            //     ], 200);
            // }
        }

        $this->sessionDestroy();
    }

    /**
     * Save Checkout data
     */
    public function instamojoStore(Request $request)
    {

        $input = $request->all();

        $courseId = request()->has('courseId') && request()->filled('courseId') ? $input['courseId'] : null;
        $planId = request()->has('planId') && request()->filled('planId') ? $input['planId'] : null;
        $course = Course::select('courses.id', 'courses.title', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
            ->leftJoin('course_plans as cp', 'cp.course_id', '=', 'courses.id')
        // ->where('courses.status', '1')
            ->where('cp.plan_type', '!=', 0)
            ->where('cp.id', $planId)
            ->where('courses.id', $courseId)
            ->get()
            ->first();
        $course_title = $course['title'];
        // $amount = $course['final_payable_price'];
        $amount = $request->final_price;

        $buyer_name = auth()->guard('learner')->user()->name ?? '';
        $email = auth()->guard('learner')->user()->email ?? '';
        $phone = auth()->guard('learner')->user()->mobile ?? '';

        $key = config()->has('settings.instamojo_client_id') ? config('settings.instamojo_client_id') : null;
        $secret = config()->has('settings.instamojo_client_secret') ? config('settings.instamojo_client_secret') : null;
        $is_sandbox = false;
        if (config()->has('settings.instamojo_sandbox') && config('settings.instamojo_sandbox') == 1) {
            $key = config()->has('settings.instamojo_sandbox_client_id') ? config('settings.instamojo_sandbox_client_id') : null;
            $secret = config()->has('settings.instamojo_sandbox_client_secret') ? config('settings.instamojo_sandbox_client_secret') : null;
            $is_sandbox = true;
        }
        $api = \Instamojo\Instamojo::init('app', [
            'client_id' => $key,
            'client_secret' => $secret,
        ], $is_sandbox);

        $url = route('checkout.payment.instamojo.success', [
            'planId' => $planId,
            'courseId' => $courseId, "coins" => $request->coins,
            "coupon_id" => $request->coupon_id,
            "actual_price" => $request->actual_price,
            "discount" => $request->discount,
            "advancement" => $request->advancement,
            "chapter_id" => $request->chapter_id,
            "advancement_course_id" => $request->advancement_course_id,

        ]);
        // dd($url);
        try {
            $response = $api->createPaymentRequest([
                "purpose" => $course_title,
                "amount" => $amount,
                "buyer_name" => $buyer_name,
                "send_email" => false,
                "email" => $email,
                "phone" => $phone,
                "redirect_url" => $url,
            ]);
        } catch (RequestException $e) {

            $notification['type'] = "sweet-alert";
            $notification['status'] = "warning";
            $notification['title'] = "Warning";
            $notification['msg'] = $e->getMessage();
            return redirect()->route('course.list')->with('notification', $notification);
        } catch (\Exception $e) {

            $notification['type'] = "sweet-alert";
            $notification['status'] = "warning";
            $notification['title'] = "Warning";
            $notification['msg'] = $e->getMessage();
            return redirect()->route('course.list')->with('notification', $notification);
        }
        if (count($response) && !empty($response['longurl'])) {
            return redirect($response['longurl']);
        }
    }
    // Pending : This is the default status. The email and/or sms (whichever is applicable) have not been sent out yet.
    // Sent : The email and/or sms (whichever is applicable) has been sent.
    // Failed : The email and/or sms (whichever is applicable) were not sent successfully.
    // Completed : Payment was made by a customer.
    public function instamojoSuccess(Request $request)
    {

        $key = config()->has('settings.instamojo_client_id') ? config('settings.instamojo_client_id') : null;
        $secret = config()->has('settings.instamojo_client_secret') ? config('settings.instamojo_client_secret') : null;
        $is_sandbox = false;
        if (config()->has('settings.instamojo_sandbox') && config('settings.instamojo_sandbox') == 1) {
            $key = config()->has('settings.instamojo_sandbox_client_id') ? config('settings.instamojo_sandbox_client_id') : null;
            $secret = config()->has('settings.instamojo_sandbox_client_secret') ? config('settings.instamojo_sandbox_client_secret') : null;
            $is_sandbox = true;
        }
        $api = \Instamojo\Instamojo::init('app', [
            'client_id' => $key,
            'client_secret' => $secret,
        ], $is_sandbox);

        $response = $api->getPaymentDetails($request->payment_id);

        if (isset($response) && !empty($response)) {

            if ($response['status'] == true) {
                DB::beginTransaction();
                $trimmed = substr($response['currency'], 0, 2);
                $country_id = DB::table("countries")->where("code", $trimmed)->first()->id;
                $phoneNumber = $response['phone']; // Or '9664957351'

                if (strpos($phoneNumber, '+91') === 0) {
                    $modifiedNumber = substr($phoneNumber, 3);
                } else {
                    $modifiedNumber = $phoneNumber;
                }
                if (!auth()->guard('learner')->check()) {

                    if (Learner::where(["mobile" => $response['phone'], "country_id" => $country_id])->first()) {

                        $learner = Learner::where(["mobile" => $response['phone'], "country_id" => $country_id])->first();
                    } else {

                        $learner = Learner::create([
                            "name" => $response['name'],
                            "email" => $response['email'],
                            "mobile" => $modifiedNumber,
                            "country_id" => $country_id,
                        ]);
                    }
                } else {

                    Learner::where("id", auth()->guard('learner')->user()->id)->update([
                        "name" => $response['name'],
                        "email" => $response['email'],
                        "mobile" => $modifiedNumber,
                        // substr($response['phone'], 3)
                        "country_id" => $country_id,
                    ]);

                    $learner = Helper::getLearnerData(auth()->guard('learner')->user()->id);
                }

                $transactionId = $response['id'];
                $expiredAt = get_plan_expire($request->planId);
                $userCourse = UserCourse::create([
                    'learner_id' => $learner['id'] ?? null,
                    'course_id' => $request->courseId ?? null,
                    'plan_id' => $request->planId ?? null,
                    'order_status' => strtolower($response['status']) != strtolower('Failed') ? 1 : 2,
                    'payment_order_status' => isset($response) && !empty($response) ? $response['status'] : null,
                    'payment_gateway' => UserCourse::INSTAMOJO,
                    'transaction_id' => $transactionId,
                    'expire_at' => $expiredAt,
                    'price' => get_course_plan_price($request->planId) ?? 0.00,
                    'full_name' => $learner['name'] ?? null,
                    'email' => isset($response) && !empty($response) ? $response['email'] : null,
                    'mobile' => $learner->mobile,
                    'country_id' => $learner['country_id'] ?? null,
                    'state_id' => $learner['state_id'] ?? null,
                    'city_id' => $learner['city_id'] ?? null,
                    'transaction_response' => json_encode($response),
                    'user_coin' => $request->coins,
                    'per_coin_price' => config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null,
                    'coupon_id' => $request->coupon_id,
                    'after_deduction_price' => $response['order_info']['unit_price'],
                    'after_coupon_applied_deduction_price' => $request->discount != "" ? $request->actual_price - $request->discount : 0,
                ]);
                $msg = '';
                $user_course_id = $userCourse->id;
                createInvoice($user_course_id);
                // if (Auth::guard('learner')->check()) {
                $course_title = Course::where('id', $request->courseId)->first()->title;
                $msg = "Payment successful";
                $subject = $msg;
                $content = "$course_title has been purchased successfully.";
                $params['email']['user_course_id'] = $user_course_id;
                $params['email']['to'] = isset($response) && !empty($response) ? $response['email'] : null;
                $params['web']['to'] = $learner['id'] ?? null;
                // need to append country code with phone number dynamically
                $params['push']['to'] = $learner['id'];
                // need to append country code with phone number dynamically
                $params['whatsapp']['to'] = $learner->country->phonecode . "" . $learner->mobile;
                $params['whatsapp']['message'] = $course_title;
                $params['whatsapp']['template'] = 'course_purchased';
                $params['common'] = [
                    'course_id' => $request->courseId,
                    'learner_id' => $learner['id'] ?? null,
                    'type' => 1,
                ];

                // $coins = $request->coins;

                // $onecoinprice = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;
                // if ($course->final_payable_price > ($coins * $onecoinprice)) {

                //     $final_price = $course->final_payable_price - ($coins * $onecoinprice);
                // } else {
                //     $final_price = ($coins * $onecoinprice) - $course->final_payable_price;
                // }

                // $total_coins = ( (($coins * $onecoinprice)- $course->final_payable_price) / $onecoinprice);

                // earn
                $course_coin = Course::where("id", $request->courseId)->first();
                if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {

                    $user_coin = new UserCoin();
                    $user_coin->learner_id = auth()->guard("learner")->user()->id ?? $learner->id;
                    $user_coin->coins = $course_coin->course_coin;
                    $user_coin->type = 1;
                    //$user_coin->comment = "These coins were earned from the purchase of the $course_coin->title";
                    $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
                    $user_coin->course_id = $request->courseId;
                    $user_coin->save();

                    $subject2 = "Congratulations! Success Coins Allocated to Your Account!";
                    // $content = "Completing your profile earned you $coin_price coins.";
                    $content2 = "you have purchased $course_coin->title course";
                    $plus = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2)
                                ->Where("type", "!=", 4);
                        })->sum("coins");
                    // $deduct = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)->where("type",2)->Orwhere("type",4)->sum("coins");

                    $deduct = UserCoin::where("learner_id", auth()->guard("learner")->user()->id ?? $learner->id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $learner = Learner::where("id", auth()->guard("learner")->user()->id ?? $learner->id)->first();
                    $inst = new EarnCoinMail($content2, $subject2, $learner, $course_coin->course_coin, $total);

                    if ($learner) {
                        if ($learner->email) {
                            \Mail::to($learner->email)->send($inst);
                        }
                    }
                }

                // redeem
                if ($request->coins != "") {
                    // if ($request->coin_used) {
                    //     dd("if condition");
                    // if ($request->coin_used > 0) {
                    $user_coin = new UserCoin();
                    $user_coin->learner_id = @auth()->guard("learner")->user()->id;
                    $user_coin->coins = $request->coins;
                    $user_coin->type = 2;
                    //$user_coin->comment = "Redeem coins to $course_coin->title";

                    $user_coin->comment = "You have redeemed $request->coins success coin due to you have purchased $course_coin->title course";

                    $user_coin->course_id = $request->courseId;
                    $user_coin->save();

                    $subject3 = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
                    $content3 = "you have redeemed while purchasing $course_coin->title course";
                    $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    $deduct = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    //$user_coin = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)->where("type", 6)->first();

                    $learner = Learner::where("id", @auth()->guard("learner")->user()->id)->first();
                    $inst = new RedeemCoinMail($content3, $subject3, $learner, $total, $request->coins);

                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                    // }
                    // }
                }

                if ($request->coupon_id != "") {
                    $CouponCourse = CouponCourse::where("coupon_id", $request->coupon_id)->where("course_id", $request->courseId)->first();
                    $coupon = new CouponUsage();
                    $coupon->learner_id = @auth()->guard("learner")->user()->id;
                    $coupon->coupon_id = $CouponCourse->id;
                    $coupon->is_used = 1;
                    $coupon->course_id = $request->courseId;
                    $coupon->save();
                }

                $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
                // }
                DB::commit();

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";

                $advancement_course = Course::where("id", $request->advancement_course_id)->select("slug")->first();
                if ($request->advancement == 1) {
                    $nextRecord = Chapter::where('id', '>', $request->chapter_id)->where("parent_id", "!=", 0)->first();
                    $notification['msg'] = "Payment successful";
                    return redirect()->route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $nextRecord->id])->with('notification', $notification);
                }

                if (!auth()->guard('learner')->check()) {
                    $notification['msg'] = "Payment successful";

                    return redirect()->route('home')->with('notification', $notification);
                } else {
                    $notification['msg'] = $msg;
                    return redirect()->route('my.course')->with('notification', $notification);
                }
            } else {
                // dd($response);
                try {

                    if (!auth()->guard('learner')->check()) {
                        $trimmed = substr($response['currency'], 0, 2);
                        $country_id = DB::table("countries")->where("code", $trimmed)->first()->id;

                        if (Learner::where(["mobile" => $response['phone'], "country_id" => $country_id])->first()) {
                            $learner = Learner::where(["mobile" => $response['phone'], "country_id" => $country_id])->first();
                        } else {
                            $learner = Learner::create([
                                "name" => $response['name'],
                                "email" => $response['email'],
                                "mobile" => $response['phone'],
                                "country_id" => $country_id,
                            ]);
                        }
                    } else {
                        $learner = Helper::getLearnerData(auth()->guard('learner')->user()->id);
                    }
                    $transactionId = $response['id'];
                    $expiredAt = get_plan_expire($request->planId);
                    $userCourse = UserCourse::create([
                        'learner_id' => $learner['id'] ?? null,
                        'course_id' => $request->courseId ?? null,
                        'plan_id' => $request->planId ?? null,
                        'order_status' => strtolower($response['status']) == false ? 2 : 1,
                        'payment_order_status' => isset($response) && !empty($response) ? $response['status'] : null,
                        'payment_gateway' => UserCourse::INSTAMOJO,
                        'transaction_id' => $transactionId,
                        'expire_at' => $expiredAt,
                        'price' => get_course_plan_price($request->planId) ?? 0.00,
                        'full_name' => $learner['name'] ?? null,
                        'email' => isset($response) && !empty($response) ? $response['email'] : null,
                        'mobile' => $learner->mobile,
                        'country_id' => $learner['country_id'] ?? null,
                        'state_id' => $learner['state_id'] ?? null,
                        'city_id' => $learner['city_id'] ?? null,
                        'transaction_response' => json_encode($response),
                    ]);
                    $msg = '';
                    $course_title = Course::where('id', $request->courseId)->first()->title;

                    $learner_id = auth()->guard('learner')->id();
                    $course_id = $request->courseId ?? null;
                    $course_title = Course::select('title')->find($course_id)->first()->title;
                    Notification::create([
                        'courseId' => $course_id,
                        'learnerId' => $learner_id,
                        'title' => 'Payment Failed',
                        'text' => $course_title,
                    ]);
                } catch (\Throwable $th) {
                }
                $this->sessionDestroy();
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = "Payment Failed";

                if ($request->advancement == 1) {

                    $advancement_course = Course::where("id", $request->advancement_course_id)->select("slug")->first();
                    return redirect()->route("course.preview", ["slug" => $advancement_course->slug, "chapterId" => $request->chapter_id])->with('notification', $notification);
                } else {

                    return redirect()->route('course.list')->with('notification', $notification);
                }
            }
            // } catch (\Exception $e) {
            //     $this->sessionDestroy();
            //     DB::rollback();
            //     $notification['type'] = "sweet-alert";
            //     $notification['status'] = "error";
            //     $notification['title'] = "Error";
            //     $notification['msg'] = "Payment Failed" . $e->getMessage();
            //     return redirect()->route('course.list')->with('notification', $notification);
            // }
        } else {

            $this->sessionDestroy();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Payment Failed";
            return redirect()->route('course.list')->with('notification', $notification);
        }
    }

    public static function sessionDestroy()
    {
        session()->forget('sessionAddress');
    }

    public function saveFreePlan($planId, $updated_new_coin, $coupon_id)
    {

        $planId = Crypt::decrypt($planId);

        $course = CoursePlan::with('course')
            ->where('status', 1)
            ->where('id', $planId)
            ->get()
            ->first();

        $final_price = "";
        $coins = "";
        $deduct_coins = "";
        $coupon_id_new = "";
        $discount = 0;

        $onecoinprice = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;

        if (($updated_new_coin !== '') && ($updated_new_coin != 0) && ($coupon_id !== 'null' && $coupon_id !== '0')) {

            $coins = Crypt::decrypt($updated_new_coin);

            $coupon_id = Crypt::decrypt($coupon_id);

            $coupon = Coupon::where("id", $coupon_id)->first();

            $coupon_id_new = $coupon->id;
            if ($coupon->type == 1) {

                $percentage = ($course->final_payable_price * $coupon->value) / 100;

                if ($percentage > $coupon->max_amount) {

                    $discount = $course->final_payable_price - $coupon->max_amount;

                    if (($coins * $onecoinprice) > $discount) {

                        $final_price = ($coins * $onecoinprice) - $discount;
                    } else {

                        $final_price = intval($discount) - ($coins * $onecoinprice);
                    }

                    $deduct_coins = (($coins * $onecoinprice) - $discount) / $onecoinprice;
                } else {
                    $discount = $course->final_payable_price - round($percentage);

                    $final_price = $discount - ($coins * $onecoinprice);
                    $deduct_coins = (($coins * $onecoinprice) - $discount) / $onecoinprice;
                }
            } else {
                $discount = $course->final_payable_price - $coupon->value;

                $final_price = $discount - ($coins * $onecoinprice);
                $deduct_coins = (($coins * $onecoinprice) - $discount) / $onecoinprice;
            }
        }

        if (($updated_new_coin == 0 || $updated_new_coin == "") && ($coupon_id !== 'null' && $coupon_id !== '0')) {
            $coupon_id = Crypt::decrypt($coupon_id);

            $coupon = Coupon::where("id", $coupon_id)->first();
            $coupon_id_new = $coupon->id;
            if ($coupon->type == 1) {

                $percentage = ($course->final_payable_price * $coupon->value) / 100;
                if ($percentage > $coupon->max_amount) {

                    $discount = $course->final_payable_price - $coupon->max_amount;
                    $final_price = $discount;
                    $deduct_coins = (($coins * $onecoinprice) - intval($discount)) / $onecoinprice;
                } else {
                    // $discount = $course->final_payable_price - round($percentage);
                    $discount = $course->final_payable_price;

                    $final_price = $discount;
                    $deduct_coins = $coins;
                }
            } else {

                if ($coupon->value > $course->final_payable_price) {

                    $discount = $course->final_payable_price;
                } else {

                    $discount = $course->final_payable_price - $coupon->value;

                    $deduct_coins = (($coins * $onecoinprice) - $discount) / $onecoinprice;
                }

                $final_price = $discount;
            }
        }

        if (($updated_new_coin != 0 && $updated_new_coin != "") && ($coupon_id == 'null' && $coupon_id !== '0')) {

            $coins = Crypt::decrypt($updated_new_coin);

            if ($course->final_payable_price > ($coins * $onecoinprice)) {

                $final_price = $course->final_payable_price - ($coins * $onecoinprice);

                $deduct_coins = ($final_price - ($coins * $onecoinprice) / $onecoinprice);
            } else {

                $final_price = ($coins * $onecoinprice) - $course->final_payable_price;
                // $deduct_coins = (($coins * $onecoinprice) - $final_price) / $onecoinprice;
                $deduct_coins = floor((($coins * $onecoinprice) - $course->final_payable_price) / $onecoinprice);
            }
        }

        $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
            ->where(function ($query) {
                $query->where("type", "!=", 2)
                    ->Where("type", "!=", 4);
            })->sum("coins");

        $minus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
            ->where(function ($query) {
                $query->where("type", 2)
                    ->OrWhere("type", 4);
            })->sum("coins");

        $used_coind_total = "";
        $course_coin = Course::where("id", $course->course_id)->first();
        if ($deduct_coins != "") {

            $reedem_coins = $plus - $minus;

            $used_coind_total = $reedem_coins - round(intval($deduct_coins));

            // dd($total);
            // dd( $plus,intval($deduct_coins));

            $user_coin = new UserCoin();
            $user_coin->learner_id = @auth()->guard("learner")->user()->id;
            $user_coin->coins = $used_coind_total;
            $user_coin->type = 2;
            // $user_coin->comment = "Redeem coins to $course_coin->title";
            $user_coin->comment = "You have redeemed $used_coind_total success coin due to you have purchased $course_coin->title course";
            $user_coin->course_id = $course->course_id;
            $user_coin->save();

            $subject = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
            $content = "you have redeemed while purchasing $course_coin->title course";
            $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                ->where(function ($query) {
                    $query->where("type", "!=", 2);
                    $query->Where("type", "!=", "4");
                })->sum("coins");

            $deduct = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                ->where(function ($query) {
                    $query->where("type", "=", 2)
                        ->orWhere("type", "=", 4);
                })->sum("coins");

            $totals = $plus - $deduct;

            //$user_coin = UserCoin::where("learner_id", $request->learner_id)->where("type", 6)->first();

            $learner = Learner::where("id", @auth()->guard("learner")->user()->id)->first();
            $inst = new RedeemCoinMail($content, $subject, $learner, $totals, $used_coind_total);
            if ($learner) {
                \Mail::to($learner->email)->send($inst);
            }
        }

        // earn

        if (isset($course_coin->course_coin) && $course_coin->course_coin != "" && $course_coin->course_coin > 0) {

            $user_coin = new UserCoin();
            $user_coin->learner_id = @auth()->guard("learner")->user()->id;
            $user_coin->coins = $course_coin->course_coin;
            $user_coin->type = 1;
            //$user_coin->comment = "These coins were earned from the purchase of the $course_coin->title";
            $user_coin->comment = "You have earned $course_coin->course_coin success coin due to you have purchased $course_coin->title course";
            $user_coin->course_id = $course->course_id;
            $user_coin->save();

            $subject = "Congratulations! Success Coins Allocated to Your Account!";
            // $content = "Completing your profile earned you $coin_price coins.";
            $content = "you have purchased $course_coin->title course";
            $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                ->where(function ($query) {
                    $query->where("type", "!=", 2)
                        ->Where("type", "!=", 4);
                })->sum("coins");
            // $deduct = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)->where("type",2)->Orwhere("type",4)->sum("coins");

            $deduct = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                ->where(function ($query) {
                    $query->where("type", "=", 2)
                        ->orWhere("type", "=", 4);
                })->sum("coins");

            $total = $plus - $deduct;

            $learner = Learner::where("id", @auth()->guard("learner")->user()->id)->first();
            $inst = new EarnCoinMail($content, $subject, $learner, $course_coin->course_coin, $total);
            if ($learner) {
                \Mail::to($learner->email)->send($inst);
            }
        }

        if ($coupon_id_new != "") {

            $coupon = new CouponUsage();
            $coupon->learner_id = @auth()->guard("learner")->user()->id;
            $coupon->coupon_id = $coupon_id_new;
            $coupon->is_used = 1;
            $coupon->course_id = $course->course_id;
            $coupon->save();
        }

        $expiredAt = get_plan_expire($planId);
        $after_coupon_applied_deduction_price = 0;
        if ($discount != 0) {

            if (intval($course->final_payable_price) == intval($discount)) {
                $after_coupon_applied_deduction_price = $course->final_payable_price;
            } else {
                $after_coupon_applied_deduction_price = intval($course->final_payable_price) - intval($discount);
            }
        }

        if (isset($course) && !empty($course)) {
            $userCourse = UserCourse::create([
                'learner_id' => auth()->guard('learner')->user()->id,
                'course_id' => $course->course_id ? $course->course_id : null,
                'plan_id' => $planId,
                'price' => $course->final_payable_price != "" ? $course->final_payable_price : 0.00,
                'full_name' => auth()->guard('learner')->user()->name ?? null,
                'email' => auth()->guard('learner')->user()->email ?? null,
                'mobile' => auth()->guard('learner')->user()->mobile ?? null,
                'country_id' => auth()->guard('learner')->user()->country_id ?? null,
                'state_id' => auth()->guard('learner')->user()->state_id ?? null,
                'city_id' => auth()->guard('learner')->user()->city_id ?? null,
                'order_status' => 3,
                'payment_order_status' => null,
                'payment_gateway' => null,
                'transaction_id' => null,
                'expire_at' => $expiredAt,
                'transaction_response' => null,
                'user_coin' => $used_coind_total,
                'per_coin_price' => config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null,
                'coupon_id' => $coupon_id_new,
                'after_deduction_price' => 0,
                'after_coupon_applied_deduction_price' => $after_coupon_applied_deduction_price,
            ]);
            $user_course_id = $userCourse->id;
            $course_title = Course::where('id', $course->course_id)->first(["title", "type"]);
            if (Auth::guard('learner')->check()) {
                $learnerData = Learner::with('country:id,phonecode')->where("id", auth()->guard('learner')->user()->id)->get()->first();
                // $course_title = Course::where('id', $course->course_id)->first()->title;

                if ($course_title->type == 2) {
                    $subject = 'Added free package successfully';
                    $content = "Package $course_title->title Added successfully";
                } else {

                    $subject = 'Added free course successfully';
                    $content = "Course $course_title->title Added successfully";
                }

                $params['email']['user_course_id'] = $user_course_id;
                $params['email']['to'] = $learnerData->email;
                $params['web']['to'] = Auth::guard('learner')->id();
                // need to append country code with phone number dynamically
                $params['push']['to'] = Auth::guard('learner')->id();

                // need to append country code with phone number dynamically
                // $params['whatsapp']['to'] = $learnerData->country->phonecode . "" . $learnerData->mobile;
                // $params['whatsapp']['message'] = $course_title->title;
                // $params['whatsapp']['template'] = 'course_purchased';
                $params['common'] = [
                    'course_id' => $course->course_id,
                    'learner_id' => auth()->guard('learner')->user()->id,
                    'type' => 3,
                ];
                $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);
            }
        }
        $course_name = $course_title->type == 2 ? "Package" : "Course";
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "$course_name added successfully";
        return redirect()->route('my.course')->with('notification', $notification);
    }
}
