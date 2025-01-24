<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\Learner;
use App\Models\RatingReview;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use libphonenumber\PhoneNumberUtil;

class ZapierController extends Controller
{
    // zapair login

    public function adminZapierLogin(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return response()->json(["message" => "login successfully"], 200);
        } else {
            return response()->json(["message" => "Unauthorized"], 401);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Events

    public function zapierGetReview(Request $request)
    {

        $rating_reviews = RatingReview::select("id", "rating", "comment", "is_approve", "created_at", "learner_id", "course_id")
            ->with(["learner:id,name,mobile,email,country_id", "course:id,title"])
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
        // ->limit("1")
            ->orderBy('id', 'desc')
            ->get();
        $rating_reviews->filter(function ($val) {
            $code = @DB::table('countries')->where("id", @$val->learner->country_id)->first()->phonecode;
            $val->course_name = $val->course->title;
            $val->name = @$val->learner->name;
            $val->country_code = @$code;
            $val->mobile = @$val->learner->mobile;
            $val->email = @$val->learner->email;

            unset($val->learner_id);
            unset($val->course_id);
            unset($val->course);
            unset($val->learner);
        });

        if ($rating_reviews) {
            return response()->json($rating_reviews);
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetLearner(Request $request)
    {

        $learner = Learner::select("id", "name", "email", "mobile", "gender", "d_o_b", "country_id", "created_at")->with("country:id,phonecode")
            ->orderBy('id', 'desc')
        // ->limit(1)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->get();
        $learner->filter(function ($val) {
            $val->country_code = @$val->country->phonecode;
            $val->mobile = $val->mobile;
            $val->created_at = $val->created_at;
            $val->gender = $val->gender == 1 ? "Male" : ($val->gender == 2 ? "Female" : ($val->gender == 2 ? "Other" : ""));
            unset($val->country_id);
            unset($val->country);
        });

        if ($learner) {
            return response()->json($learner);
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetSucessfullyTransaction(Request $request)
    {

        $userCourse = UserCourse::select("id", "learner_id", "course_id", "plan_id", "order_status", "price", "created_at", "transaction_id", "expire_at", "country_id")->where("order_status", "!=", 2)->where("order_status", "!=", 4)->with("learner:id,name,email,mobile", "course:id,title", "coursePlan:id,plan_name,access_value", "countryName:id,phonecode")
            ->orderBy('id', 'desc')
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
        // ->limit(2)
            ->get();

        $userCourse->filter(function ($val) {

            if ($val->order_status == 1) {
                $val->order_status = "Success";
            } else if (($val->order_status == 2)) {
                $val->order_status = "Fail";
            } else if (($val->order_status == 3)) {
                $val->order_status = "Free";
            } else if (($val->order_status == 4)) {
                $val->order_status = "Enrol by admin";
            } else if (($val->order_status == 5)) {
                $val->order_status = "Zapier";
            }

            $val->amount = @$val->price;
            $val->course_name = @$val->course->title;
            $val->name = @$val->learner->name;
            $val->email = @$val->learner->email;
            $val->contry_code = @$val->countryName->phonecode;
            $val->mobile = @$val->learner->mobile;
            $val->plan = @$val->coursePlan->plan_name;
            $val->country_code = @$val->countryName->phonecode;
            $val->validity = "";
            if ($val->order_status != 2) {
                if ($val->expire_at) {
                    $val->validity = $val->expire_at;
                } else {
                    $val->validity = "";
                }
            }
            unset($val->learner);
            unset($val->course);
            unset($val->coursePlan);
            unset($val->countryName);
            unset($val->country_id);
        });

        if ($userCourse) {
            return response()->json($userCourse);
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetFailTransaction(Request $request)
    {

        $userCourse = UserCourse::select("id", "learner_id", "course_id", "plan_id", "order_status", "price", "created_at", "transaction_id", "expire_at", "country_id")->where("order_status", 2)->with("learner:id,name,email,mobile", "course:id,title", "coursePlan:id,plan_name,access_value", "countryName:id,phonecode")
            ->orderBy('id', 'desc')
        // ->limit("2")
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->get();
        // dd($userCourse->toArray());
        $userCourse->filter(function ($val) {

            if ($val->order_status == 1) {
                $val->order_status = "Success";
            } else if (($val->order_status == 2)) {
                $val->order_status = "Failed";
            } else if (($val->order_status == 3)) {
                $val->order_status = "Free";
            } else if (($val->order_status == 4)) {
                $val->order_status = "Enrol by admin";
            } else if (($val->order_status == 5)) {
                $val->order_status = "Zapier";
            }

            $val->amount = $val->price;
            $val->course_name = @$val->course->title;
            $val->name = @$val->learner->name;
            $val->email = $val->learner->email;
            $val->country_code = @$val->countryName->phonecode;
            $val->mobile = @$val->learner->mobile;
            $val->plan = $val->coursePlan->plan_name;
            $val->validity = "";
            if ($val->order_status != 2) {
                if ($val->expire_at) {
                    $val->validity = $val->expire_at;
                } else {
                    $val->validity = "";
                }
            }
            unset($val->learner);
            unset($val->course);
            unset($val->coursePlan);
            unset($val->after_deduction_price);
            unset($val->countryName);
            unset($val->country_id);
        });

        if ($userCourse) {
            return response()->json($userCourse);
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetEnrollTransaction(Request $request)
    {

        $userCourse = UserCourse::select("id", "learner_id", "course_id", "plan_id", "order_status", "price", "created_at", "expire_at", "country_id")->where("order_status", 4)->with("learner:id,name,email,mobile", "course:id,title", "coursePlan:id,plan_name,access_value", "countryName:id,phonecode")
            ->orderBy('id', 'desc')
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
        // ->limit("2")
            ->get();

        $userCourse->filter(function ($val) {

            if ($val->order_status == 1) {
                $val->order_status = "Success";
            } else if (($val->order_status == 2)) {
                $val->order_status = "Fail";
            } else if (($val->order_status == 3)) {
                $val->order_status = "Free";
            } else if (($val->order_status == 4)) {
                $val->order_status = "Enrol by admin";
            } else if (($val->order_status == 5)) {
                $val->order_status = "Zapier";
            }

            $val->amount = $val->price;
            $val->course_name = @$val->course->title;
            $val->name = @$val->learner->name;
            $val->email = @$val->learner->email;
            $val->country_code = @$val->countryName->phonecode;
            $val->mobile = @$val->learner->mobile;
            $val->plan = @$val->coursePlan->plan_name;
            $val->validity = "";
            if ($val->order_status != 2) {
                if ($val->expire_at) {
                    $val->validity = $val->expire_at;
                } else {
                    $val->validity = "";
                }
            }
            unset($val->learner);
            unset($val->course);
            unset($val->price);
            unset($val->coursePlan);
            unset($val->countryName);
            unset($val->country_id);
        });

        if ($userCourse) {
            return response()->json($userCourse);
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetCourseCompletion(Request $request)
    {

        $user_course = UserCourse::select("id", "learner_id", "course_id", "plan_id", "created_at")->with(['learner:id,name,mobile,email', 'course:id,title', 'userChpater:id,course_id'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))

            ->orderBy('id', 'desc')
            ->with(["coursePackages" => function ($q) {
                $q->select("package_id", DB::raw('GROUP_CONCAT(course_id) as course_id'))->groupBy('package_id');
            }])
            ->with(["userChpater" => function ($q) {
                $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->get()
            ->unique(function ($item) {
                return $item['learner_id'] . $item['course_id'] . $item['plan_id'];
            });

        $user_course->filter(function ($val) {

            if ($val->coursePackages->isNotEmpty() == true) {
                $val->course_ids = $val->coursePackages[0]->course_id;
            } else {
                $val->course_ids = $val->course_id;
            }
        });
        $mergedCourseIds = [];
        foreach ($user_course as $item) {
            $courseIdsArray = explode(',', $item['course_ids']);
            $mergedCourseIds = array_merge($mergedCourseIds, $courseIdsArray);
        }

        $mergedCourseIds = array_unique($mergedCourseIds);

        $mergedCourseIdsString = implode(',', $mergedCourseIds);
        foreach ($user_course as &$item) {
            $item['course_ids'] = $mergedCourseIdsString;
        }

        $i = 1;
        $all_data_collection = collect();
        foreach ($user_course as $key => $val) {

            foreach (explode(",", $val->course_ids) as $values) {

                $new_chapterIds = Chapter::where("course_id", $values)->where("parent_id", "!=", 0)->where("asset_type", "!=", 4)->where("asset_type", "!=", 7)->pluck("id")->toArray();

                $user_progress_records = UserCourseProgress::whereIn("chapter_id", $new_chapterIds)
                    ->where("is_completed", 1)
                    ->where("learner_id", $val->learner_id)
                    ->get();

                $user_progress_count = $user_progress_records->count();
                $learner = Learner::where("id", $val->learner_id)->first();
                // dd($learner_id);
                $code = @DB::table('countries')->where("id", $learner->country_id)->first()->phonecode;
                $percentage = round($user_progress_count) != 0 ? round(($user_progress_count * 100) /
                    count($new_chapterIds)) : "0%";
                if (100 == $percentage) {
                    $course = Course::where("id", $values)->select("title", "id")->first();

                    $all_data = [
                        "id" => $i++,
                        "name" => @$learner->name,
                        "country_code" => $code,
                        "mobile" => @$learner->mobile,
                        "email" => @$learner->email,
                        "percentage" => $percentage . "%",
                        "course_name" => $course->title,
                        "created_at" => $val->created_at,
                        // "course_id" => $course->id,
                        // "plan_id" => $plan->id,
                    ];
                    $all_data_collection->push($all_data);
                }
            }
        }

        $grouped_data = $all_data_collection->unique(function ($item) {
            return $item['mobile'] . $item['course_name'];
        });

        if ($grouped_data) {
            return response()->json($grouped_data->values());
        } else {
            return response()->json([]);
        }
    }

    public function zapierGetWishlist(Request $request)
    {

        $wishlist = Wishlist::select("id", "course_id", "learner_id")->with("course:id,title", "learner:id,name,mobile,email,country_id")
            ->orderBy('id', 'desc')
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
        // ->limit(1)
            ->get();

        $wishlist->filter(function ($val) {
            $code = @DB::table('countries')->where("id", $val->learner->country_id)->first()->phonecode;
            $val->course_name = @$val->course->title;
            $val->name = @$val->learner->name;
            $val->email = @$val->learner->email;
            $val->mobile = @$val->learner->mobile;
            $val->country_code = $code;

            unset($val->learner);
            unset($val->course);
        });

        if ($wishlist) {
            return response()->json($wishlist);
        } else {
            return response()->json([]);
        }
    }

    // Tigger

    public function zapierWithLearnerCreate(Request $request)
    {

        $validated = $request->validate([
            'mobile' => 'required',
            'country_code' => 'required',
        ]);

        $fullNumber = $request->mobile;

        $countryCode = $request->country_code;
        if ($countryCode == 1) {
            $countryCode = 231;
        }

        $country_code = @DB::table('countries')->where("phonecode", $countryCode)->first();

        $set_code = $countryCode == 231 ? 231 : $country_code->id;

        $learner_exist = @Learner::where('mobile', $fullNumber)->where("country_id", $set_code)->first();

        if ($learner_exist) {
            return response()->json(["msg" => "Learner is alreday exist"]);
        } else {
            $data = Learner::create([
                "name" => $request->name,
                "email" => $request->email,
                "mobile" => $fullNumber,
                "country_id" => $set_code,
                "d_o_b" => $request->date_of_birth,
            ]);
            $learnersCollection = collect([
                "name" => $request->name,
                "email" => $request->email,
                "mobile" => $fullNumber,
                "date_of_birth" => $request->date_of_birth,
                "country_code" => $request->country_code,
                "created_at" => $data->created_at,
            ]);

            return response()->json($learnersCollection);
        }
    }

    public function zapierWithLearnerEnroll(Request $request)
    {

        $validated = $request->validate([
            'plan_id' => 'required',
            'mobile' => 'required',
            'country_code' => 'required',
        ]);

        $fullNumber = $request->mobile;

        $countryCode = $request->country_code;
        if ($countryCode == 1) {
            $countryCode = 231;
        }

        $contry_code = DB::table('countries')->where("phonecode", $countryCode)->first();

        $plan = CoursePlan::where("id", $request->plan_id)->first();
        if (!$plan) {
            return response()->json(["Msg" => "Plan id not found"]);
        }
        $set_code = $countryCode == 231 ? 231 : $contry_code->id;

        $learner = Learner::where("mobile", $fullNumber)->where("country_id", $set_code)->first();

        if (!$learner) {
            $new_learner = Learner::create([
                "mobile" => $fullNumber,
                "name" => $request->name,
                "email" => $request->email,
                "country_id" => $set_code,
            ]);

            $userCourse = UserCourse::create([
                'learner_id' => $new_learner->id,
                'course_id' => $plan->course_id,
                'plan_id' => $plan->id,
                'order_status' => 5,
                'transaction_id' => @$request->payment_id,
                'expire_at' => get_plan_expire($plan->id),
                'price' => $plan->final_payable_price,
                'after_deduction_price' => $plan->final_payable_price,
                'full_name' => @$new_learner->name,
                'email' => @$new_learner->email,
                'mobile' => @$new_learner->mobile,
                'country_id' => @$new_learner->country_id,
            ]);
        } else {

            $userCourse = UserCourse::create([
                'learner_id' => @$learner->id,
                'course_id' => @$plan->course_id,
                'plan_id' => $plan->id,
                'order_status' => 5,
                'expire_at' => get_plan_expire($plan->id),
                'price' => @$plan->final_payable_price,
                'after_deduction_price' => @$plan->final_payable_price,
                'full_name' => @$learner->name,
                'email' => @$learner->email,
                'mobile' => @$fullNumber,
                'country_id' => @$learner->country_id,
                'state_id' => @$learner->state_id,
                'city_id' => @$learner->city_id,
            ]);
        }
        $data = UserCourse::where("id", $userCourse->id)->select("full_name", "mobile", "email", "expire_at", "price", "order_status", "course_id", "plan_id", "country_id", "transaction_id")->with("coursePlan:id,plan_name", "course:title,id")->first();

        $country_code = DB::table('countries')->where("id", $data->country_id)->first();
        $data->name = $data->full_name;
        $data->email = $data->email;
        $data->country_code = @$country_code->phonecode;
        $data->mobile = @$data->mobile;
        $data->amount = $data->price;
        $data->course_name = @$data->course->title;
        $data->plan_name = @$data->coursePlan->plan_name;
        if ($data->order_status == 1) {
            $data->order_status = "Success";
        } else if (($data->order_status == 2)) {
            $data->order_status = "Fail";
        } else if (($data->order_status == 3)) {
            $data->order_status = "Free";
        } else if (($data->order_status == 4)) {
            $data->order_status = "Enrol by admin";
        } else if (($data->order_status == 5)) {
            $data->order_status = "Zapier";
        }
        $data->validity = "";
        if ($data->order_status != 2) {
            if ($data->expire_at) {
                $data->validity = $data->expire_at;
            } else {
                $data->validity = "";
            }
        }
        $data->course_id = $data->course_id;
        $data->plan_id = $data->plan_id;
        $data->transaction_id = $data->transaction_id;
        // $data->payment_gateway = $data->payment_gateway==1 ? "RazorPay" : ($data->payment_gateway==2 ? "Instamojo":"");
        unset($data->coursePlan);
        unset($data->course);
        unset($data->full_name);
        unset($data->country_id);
        // unset($data->payment_gateway);
        unset($data->expire_at);
        unset($data->price);
        return response()->json($data);
    }

    public function zapierEnrollFromPayment(Request $request)
    {

        $validated = $request->validate([
            'plan_id' => 'required',
            'mobile' => 'required',
        ]);

        $phoneUtil = PhoneNumberUtil::getInstance();

        $phoneNumber = $request->mobile;

        $parsedNumber = $phoneUtil->parse($phoneNumber, null);
        $moNumber = $parsedNumber->getNationalNumber();

        $country_codes = $parsedNumber->getCountryCode();
        $countryCode = $country_codes;
        if ($countryCode == 1) {
            $countryCode = 231; // this is USA counry_id
        }

        $country_code = DB::table('countries')->where("phonecode", $countryCode)->first();

        $plan = CoursePlan::where("id", $request->plan_id)->first();

        if (!$plan) {
            return response()->json(["Msg" => "Plan id not found"]);
        }

        $set_code = $countryCode == 231 ? 231 : $country_code->id;

        $learner = Learner::where("mobile", $moNumber)->where("country_id", $set_code)->first();

        if (!$learner) {
            $new_learner = Learner::create([
                "mobile" => $moNumber,
                "name" => $request->name,
                "email" => $request->email,
                "country_id" => $set_code,
            ]);

            $userCourse = UserCourse::create([
                'learner_id' => $new_learner->id,
                'course_id' => $plan->course_id,
                'plan_id' => $plan->id,
                'order_status' => 5,
                'transaction_id' => @$request->payment_id,
                'expire_at' => get_plan_expire($plan->id),
                'price' => @$plan->final_payable_price,
                'after_deduction_price' => @$plan->final_payable_price,
                'full_name' => @$new_learner->name,
                'email' => @$new_learner->email,
                'mobile' => @$new_learner->mobile,
                'country_id' => $new_learner->country_id,
            ]);
        } else {

            $userCourse = UserCourse::create([
                'learner_id' => $learner->id,
                'course_id' => $plan->course_id,
                'plan_id' => $plan->id,
                'order_status' => 5,
                'expire_at' => get_plan_expire($plan->id),
                'transaction_id' => @$request->payment_id,
                'price' => @$plan->final_payable_price,
                'after_deduction_price' => @$plan->final_payable_price,
                'full_name' => @$learner->name,
                'email' => @$learner->email,
                'mobile' => @$moNumber,
                'country_id' => @$learner->country_id,
                'state_id' =>@ $learner->state_id,
                'city_id' => @$learner->city_id,
            ]);
        }
        $data = UserCourse::where("id", $userCourse->id)->select("id","full_name", "mobile", "email", "expire_at", "price", "order_status", "course_id", "plan_id", "country_id", "transaction_id")->with("coursePlan:id,plan_name", "course:title,id")->first();

        $country_code = DB::table('countries')->where("id", $data->country_id)->first();
        $data->name = $data->full_name;
        $data->email = $data->email;
        $data->country_code = @$country_code->phonecode;
        $data->mobile = @$data->mobile;
        $data->amount = $data->price;
        $data->course_name = @$data->course->title;
        $data->plan_name = @$data->coursePlan->plan_name;
        if ($data->order_status == 1) {
            $data->order_status = "Success";
        } else if (($data->order_status == 2)) {
            $data->order_status = "Fail";
        } else if (($data->order_status == 3)) {
            $data->order_status = "Free";
        } else if (($data->order_status == 4)) {
            $data->order_status = "Enrol by admin";
        } else if (($data->order_status == 5)) {
            $data->order_status = "Zapier";
        }
        $data->validity = "";
        if ($data->order_status != 2) {
            if ($data->expire_at) {
                $data->validity = $data->expire_at;
            } else {
                $data->validity = "";
            }
        }
        $data->course_id = $data->course_id;
        $data->plan_id = $data->plan_id;
        $data->transaction_id = $data->transaction_id;
        // $data->payment_gateway = $data->payment_gateway==1 ? "RazorPay" : ($data->payment_gateway==2 ? "Instamojo":"");
        unset($data->coursePlan);
        unset($data->course);
        unset($data->full_name);
        unset($data->country_id);
        // unset($data->payment_gateway);
        unset($data->expire_at);
        unset($data->price);



        createInvoice($data->id);

        $course_data = Course::where('id', $plan->course_id)->first();
        $msg = "Payment successful";
        $subject = $msg;
        $type_name = "";
        if ($course_data->type == 1) {
            $type_name = "Course";
        } else {
            $type_name = "Package";
        }
        $content = "$type_name $course_data->title purchased successfully";
        $params['email']['user_course_id'] = $data->id;
        $params['email']['to'] = isset($response) && !empty($response) ? $response['email'] : null;
        $params['web']['to'] = $learner['id'] ?? null;
        $params['push']['to'] = $learner['id'] ?? null;

        $params['common'] = [
            'course_id' => $request->courseId,
            'learner_id' => $learner['id'] ?? null,
            'type' => 1,
        ];
        $res = send_notification($params, 'payment', ['email', 'push', 'web'], $subject, $content);

        return response()->json($data);
    }

    public function zapierWithLearnerUnEnroll(Request $request)
    {

        $validated = $request->validate([
            'plan_id' => 'required',
            'mobile' => 'required',
            'course_id' => 'required',
            'country_code' => 'required',
        ]);

        $fullNumber = $request->mobile;

        $countryCode = $request->country_code;
        if ($countryCode == 1) {
            $countryCode = 231;
        }

        $country_code = DB::table('countries')->where("phonecode", $countryCode)->first();

        $plan = CoursePlan::where("id", $request->plan_id)->first();
        if (!$plan) {
            return response()->json(["Msg" => "Plan id not found"]);
        }

        $set_code = $countryCode == 231 ? 231 : $country_code->id;

        $learner = Learner::where("mobile", $fullNumber)->where("country_id", $set_code)->first();

        if (!$learner) {
            return response()->json(["Msg" => "Learner not found"]);
        }

        $update = UserCourse::where("plan_id", $request->plan_id)->where("course_id", $request->course_id)->where("mobile", $fullNumber)->where("country_id", $learner->country_id)->get();

        if ($update) {
            foreach ($update as $val) {
                UserCourse::where("id", $val->id)->delete();
            }
            return response()->json(["Msg" => "Unenoll successfully"]);
        } else {
            return response()->json(["Msg" => "Not found records"]);
        }
    }
}
