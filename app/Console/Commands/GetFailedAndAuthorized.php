<?php

namespace App\Console\Commands;

use App\Models\Countries;
use App\Models\CoursePlan;
use App\Models\Learner;
use Illuminate\Support\Facades\Log;
use App\Models\LogError;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use libphonenumber\PhoneNumberUtil;
use Razorpay\Api\Api;

class GetFailedAndAuthorized extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-failed-and-authorized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

        // $startOfDay = strtotime('today midnight');
        // $endOfDay = strtotime('tomorrow midnight') - 1;

        $startOfDay = strtotime('-1 hour', strtotime(now()));
        $endOfDay = strtotime(now());

        // dd($startOfDay, $startOfDayTimestamp);

        // $payments = $api->payment->all([
        //     'from' => $startOfDay,
        //     'to' => $endOfDay,
        //     // 'status' => 'failed'
        // ]);

        $page = 1; // Start with the first page
        $limit = 100; // Number of records per p

        $payments = $api->payment->all([
            'from' => $startOfDay,
            'to' => $endOfDay,
            'count' => $limit, // Number of records per page
            'skip' => ($page - 1) * $limit, // Skip records for pagination
        ]);

        if (count($payments['items']) > 0) {
            $failedTransactions = array_filter($payments['items'], function ($payment) use ($api) {

                $phoneUtil = PhoneNumberUtil::getInstance();
                $phoneNumber = $payment["contact"];
                $parsedNumber = $phoneUtil->parse($phoneNumber, null);

                $country_code = $parsedNumber->getCountryCode();
                $amount = (int) ($payment['amount'] / 100);

                $amount = (int) ($payment['amount'] / 100);
                if (isset($payment['notes']["CourseId"]) && $payment['notes']["CourseId"] != "" && isset($payment['notes']["PlanId"]) && $payment['notes']["PlanId"] != "") { // only if payment receive in razorpay from lifegurukul website or android app
                    try {
                        // Your code that may throw an exception
                        $user_course = UserCourse::where("transaction_id", $payment['id'])->first();
                        if (!$user_course) {
                            Log::info("This transaction_id is not available in our database => " . $payment['id']);
                            $course_id = @$payment['notes']["CourseId"];
                            $PlanId = @$payment['notes']["PlanId"];
                            $course_plan = CoursePlan::where("course_id", $course_id)->where("id", $PlanId)->first();

                            if (isset($course_plan->id) && $course_plan->id != "") {

                                if ($payment['status'] == 'authorized') {
                                    Log::info("This transaction_id has been captured which is authorized => " . $payment['id']);
                                    $amounts = (int) ($amount * 100);
                                    $payment = $api->payment->fetch($payment['id'])->capture(array('amount' => $amounts));
                                }
                                Log::info("This transaction_id has been inserted => " . $payment['id']);
                                $created_atDate = Carbon::createFromTimestamp($payment["created_at"])->format('Y-m-d H:i:s');
                                $expiredAt = get_plan_expire($course_plan->id);
                                $coutry_code = Countries::where("phonecode", $country_code)->first();
                                $learner = Learner::where("mobile", $parsedNumber->getNationalNumber())->where("country_id", $coutry_code->id)->first();
                                $userCourse = UserCourse::create([
                                    'learner_id' => $learner->id,
                                    'course_id' => $course_id ?? null,
                                    'plan_id' => $course_plan->id ?? null,
                                    'full_name' => $learner->name ?? null,
                                    'email' => $learner->email ?? null,
                                    'mobile' => $learner->mobile ?? null,
                                    'country_id' => $coutry_code->id ?? null,
                                    'state_id' => $learner->state_id ?? null,
                                    'city_id' => $learner->city_id ?? null,
                                    'order_status' => $payment['status'] == "captured" ? 1 : 2,
                                    'payment_gateway' => 1,
                                    'transaction_id' => $payment['id'],
                                    'expire_at' => $expiredAt,
                                    'price' => $course_plan->final_payable_price ?? 0.00,
                                    'after_deduction_price' => $course_plan->final_payable_price ?? 0.00,
                                    "created_at" => $created_atDate,
                                ]);

                                if ($payment['status'] !== 'failed') {
                                    createInvoice($userCourse->id);
                                }
                            }
                        } else {

                            $amounts = (int) ($amount * 100);

                            if ($payment['status'] == 'authorized') {
                                Log::info("This transaction_id exists in our database so we have captured => " . $payment['id']);
                                $payment = $api->payment->fetch($payment['id'])->capture(array('amount' => $amounts));
                            }
                        }
                    } catch (\Exception $e) {

                        // Log::error('Error occurred: ' . $e->getMessage(), [
                        //     'file' => $e->getFile(),
                        //     'line' => $e->getLine(),
                        //     'trace' => $e->getTraceAsString(),
                        // ]);

                        LogError::create([
                            "file" => $e->getFile(),
                            "line" => $e->getLine(),
                            "trace" => $e->getTraceAsString(),
                        ]);
                    }
                }
            });
        }
        return Command::SUCCESS;
    }
}
