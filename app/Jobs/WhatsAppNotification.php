<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\UserCoin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class WhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $request;
    public $learners;
    public $media_var;
    public $manual_notify_id;

    public function __construct($request, $learners, $media_var, $manual_notify_id)
    {
        $this->request = $request;
        $this->learners = $learners;
        $this->media_var = $media_var;
        $this->manual_notify_id = $manual_notify_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $array_parameter = [];
        $variable = [];
        $media_params = [];

        $result = [];
        // dd($this->request,$this->request['input_value']);
        foreach ($this->request['campaigns'] as $key => $campaign) {
            if (array_key_exists($key, $this->request['input_value'])) {
                $result[$campaign] = $this->request['input_value'][$key];
            }
            if (array_key_exists($key, $this->request['course_wp'])) {
                $course_result[$campaign] = $this->request['course_wp'][$key];
            }

            if (array_key_exists($key, $this->request['course_plan'])) {
                $plan_result[$campaign] = $this->request['course_plan'][$key];
            }
            if (array_key_exists($key, $this->request['coupons'])) {
                $coupons_result[$campaign] = $this->request['coupons'][$key];
            }
        }

        // if (isset($this->media_var)) {
        //     foreach ($this->media_var as $val) {
        //         $array_parameter[] = url("storage/whatsapp_docs", $val);
        //     }
        // }



        if (isset($this->request['campaigns'])) {
            $variable = array_values($this->request['campaigns']);

        }

        $course_plan = CoursePlan::where("id", @$plan_result['amount'])->first();
        $pre_validity = CoursePlan::where("id", @$plan_result['pre_validity'])->first();
        // dd($this->learners);
        foreach ($this->learners as $value) {

            // dd($this->learners );

            if (isset($this->media_var)) {

                $media_params["url"] = url("storage/whatsapp_docs", $this->media_var);
                $media_params["filename"] = $this->media_var;

            }

            foreach ($variable as $index => $key) {

                if ($key === "mobile") {
                    $array_parameter[$index] = $value->mobile;
                }

                if ($key === "name") {
                    $array_parameter[$index] =isset($value->learner) ? $value->learner->name : $value->name;;
                }
                if ($key === "metting_url") {
                    $array_parameter[$index] = $result["metting_url"];
                }

                if ($key === "course_name") {
                    $course = @Course::where("id", $course_result["course_name"])->first()->title;
                    $array_parameter[$index] = $course;
                }

                if ($key === "email") {
                    $array_parameter[$index] = $value->email;
                }
                if ($key === "expired_date") {
                    $array_parameter[$index] = $value->expire_at;
                }

                if ($key === "var_link") {
                    $array_parameter[$index] = $result["var_link"];
                }
                if ($key === "event_name") {
                    $array_parameter[$index] = $result["event_name"];
                }

                if ($key === "event_time") {
                    $array_parameter[$index] = $result["event_time"];
                }
                if ($key === "event_date") {
                    $array_parameter[$index] = $result["event_date"];
                }
                if ($key === "regi_link") {
                    $array_parameter[$index] = $result["regi_link"];
                }
                if ($key === "metting_id") {
                    $array_parameter[$index] = $result["metting_id"];
                }
                if ($key === "metting_pass") {
                    $array_parameter[$index] = $result["metting_pass"];
                }
                if ($key === "metting_url") {
                    $array_parameter[$index] = $result["metting_url"];
                }
                if ($key === "time") {
                    $array_parameter[$index] = $result["time"];
                }
                if ($key === "date") {
                    $array_parameter[$index] = $result["date"];
                }
                if ($key === "coupon_code") {
                    $array_parameter[$index] = $result["coupon_code"];
                }
                if ($key === "pre_validity") {
                    if ($pre_validity) {
                        if ($pre_validity->is_fixed_date == 0) {
                            if ($pre_validity->plan_type == 1) {
                                $array_parameter[$index] = strval($pre_validity->list_price);
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                        if ($pre_validity->is_fixed_date == 1) {
                            if ($pre_validity->plan_type == 1) {
                                $array_parameter[$index] = strval($pre_validity->list_price);
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                        if ($pre_validity->is_fixed_date == 2) {
                            if ($pre_validity->plan_type == 1) {
                                $array_parameter[$index] = strval($pre_validity->list_price);
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                    }
                }

                $plus = UserCoin::where("learner_id", $value->id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2);
                        $query->Where("type", "!=", "4");
                    })->sum("coins");

                $deduct = UserCoin::where("learner_id", $value->id)
                    ->where(function ($query) {
                        $query->where("type", "=", 2)
                            ->orWhere("type", "=", 4);
                    })->sum("coins");

                $total = $plus - $deduct;

                if ($key === "coin_balance") {
                    $array_parameter[$index] = strval($total);
                }
                if ($key === "coin_redeem") {
                    $array_parameter[$index] = strval($deduct);
                }
                if ($key === "coin_earn") {
                    $array_parameter[$index] = strval($plus);
                }
                if ($key === "amount") {
                    if ($course_plan) {
                        if ($course_plan->is_fixed_date == 0) {
                            if ($course_plan->plan_type == 1) {
                                $array_parameter[$index] = $course_plan->list_price;
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                        if ($course_plan->is_fixed_date == 1) {
                            if ($course_plan->plan_type == 1) {
                                $array_parameter[$index] = $course_plan->list_price;
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                        if ($course_plan->is_fixed_date == 2) {
                            if ($course_plan->plan_type == 1) {
                                $array_parameter[$index] = $course_plan->list_price;
                            } else {
                                $array_parameter[$index] = "0.00";
                            }
                        }
                    }
                }

            }

            $data = [
                "apiKey" => config('keyConfig.api_key'),
                "campaignName" => $this->request['campaigns_name'],
                "destination" => "+".$value->country->phonecode."".$value->mobile,
                // "userName" => $this->request['assistant_name'],
                "userName" => isset($value->learner) ? $value->learner->name : $value->name,
                // "templateParams" => array_values($array_parameter),
                "templateParams" => array_values($array_parameter),
                "source" => "LifeGurukul",
                "media" => $media_params,
                // "media" => [
                //     "url" => "https://whatsapp-media-library.s3.ap-south-1.amazonaws.com/FILE/6353da2e153a147b991dd812/6932541_Evolve%203.O%20AiSensy%20%20Slides.pdf",
                //     "filename" => "sample_media",
                // ],
                "buttons" => [],
                "carouselCards" => [],
                "location" => ["23.0293504", "72.5680128"],
                // "location" => new stdClass(),
            ];

            $url = "https://backend.aisensy.com/campaign/t1/api/v2";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);

            // dd($response->json());

        }
    }
}
