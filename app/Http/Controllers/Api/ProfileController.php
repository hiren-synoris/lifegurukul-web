<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\EarnCoinMail;
use App\Models\Learner;
use App\Models\LearnerLog;
use App\Models\NewsLetter;
use App\Models\UserCoin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function profileDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "learner_id" => "required|string|exists:learners,id",
            "email" => "nullable",
            "name" => "nullable",
            "gender" => "nullable",
            "country_id" => "nullable|exists:countries,id",
            "state_id" => "nullable|exists:states,id",
            "city_id" => "nullable|exists:cities,id",
            "d_o_b" => "nullable|string",
            // "mobile" => "nullable|string",
            // "token" => "nullable|string",
            // "isRegistered" => "nullable",
            "gender" => 'nullable', 'string', 'min:1', 'max:3',
            "isSubscribeNewsletter" => "nullable",
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $learner_details = Learner::findOrFail($request->learner_id);

        if ($learner_details->count() > 0) {
            if (isset($request->name) && !empty($request->name)) {
                $learner_details->name = $request->name;
            }
            if (isset($request->email) && !empty($request->email)) {
                $learner_details->email = $request->email;
            }
            if (isset($request->country_id) && !empty($request->country_id)) {
                $learner_details->country_id = $request->country_id;
            }
            if (isset($request->state_id) && !empty($request->state_id)) {
                $learner_details->state_id = $request->state_id;
            }
            if (isset($request->city_id) && !empty($request->city_id)) {
                $learner_details->city_id = $request->city_id;
            }
            if (isset($request->occupations) && !empty($request->occupations)) {
                $learner_details->occupation = $request->occupations;
            }
            if (isset($request->marital_status) && !empty($request->marital_status)) {
                $learner_details->marital_status = $request->marital_status;
            }
            if (isset($request->educations) && !empty($request->educations)) {
                $learner_details->education = $request->educations;
            }
            if ($request->has('your_interests')) {
                $learner_details->your_interests = $request->your_interests;
            }
            if (isset($request->d_o_b) && !empty($request->d_o_b)) {
                $learner_details->d_o_b = date('Y-m-d', strtotime($request->d_o_b));
            }
            // if(isset($request->mobile) && !empty($request->mobile)){
            //     $learner_details->mobile = $request->mobile;
            // }
            if (isset($request->gender) && !empty($request->gender)) {
                $learner_details->gender = $request->gender;
            }
            // if(isset($request->token) && !empty($request->token)){
            //     $learner_details->token = $request->token;
            // }
            // if(isset($request->isRegistered)){
            //     $learner_details->is_registered = $request->isRegistered;
            // }
            $learner_details->updated_at = now();
            $learner_details->save();

            $emptyFields = Learner::where("id", $request->user_id)
                ->where(function ($query) {
                    $query->whereNull('name')
                        ->orWhereNull('email')
                        ->orWhereNull('mobile')
                        ->orWhereNull('gender')
                        ->orWhereNull('d_o_b')
                        ->orWhereNull('country_id')
                        ->orWhereNull('state_id')
                        ->orWhereNull('city_id')
                        ->orWhereNull('occupation')
                        ->orWhereNull('marital_status')
                        ->orWhereNull('education')
                        ->orWhereNull('your_interests');
                })
                ->get();
            $coin_price = config()->has('settings.completedprofilecoin') ? config('settings.completedprofilecoin') : null;
            if ($emptyFields->isEmpty()) {

                $user_coin = UserCoin::where("learner_id", $request->user_id)->where("type", 6)->first();
                if (empty($user_coin)) {

                    $coin_price = config()->has('settings.completedprofilecoin') ? config('settings.completedprofilecoin') : null;
                    // $coins = $coin_price * config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;

                    $user_coin = UserCoin::create([
                        "learner_id" => $request->user_id,
                        "coins" => $coin_price,
                        "type" => 6,
                        // "comment" => "You have got $coin_price coins to successfully completed your profile",
                        "comment" => "You have earned $coin_price success coins due to you have completed your profile",
                    ]);

                    $subject = "Congratulations! Success Coins Allocated to Your Account!";
                    $content = "you have completed profile.";
                    $plus = UserCoin::where("learner_id", $request->user_id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2);
                        $query->Where("type", "!=", "4");
                    })->sum("coins");

                    // $deduct = UserCoin::where("type",2)->where("type",4)->sum("coins");
                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $learner = Learner::where("id", $request->user_id)->first();
                    $inst = new EarnCoinMail($content, $subject, $learner, $coin_price, $total);
                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                }
            }

            // dd(DB::getQueryLog());
            if (isset($request->isSubscribeNewsletter)) {
                $newsLetter = NewsLetter::where('name', $learner_details->name)->where('email', $learner_details->email)->get();
                if ($request->isSubscribeNewsletter) {
                    $name = isset($request->name) && !empty($request->name) ? $request->name : $learner_details->name;
                    $email = isset($request->email) && !empty($request->email) ? $request->email : $learner_details->email;
                    $NewsLetter = NewsLetter::updateOrCreate(
                        ['name' => $name, 'email' => $email,"learner_id"=>$learner_details->id]
                    );
                } else {
                    $newsLetter = NewsLetter::where('name', $learner_details->name)->where('email', $learner_details->email)->get();
                    // echo "<pre>";print_r($newsLetter);die;
                    if ($newsLetter->count() > 0) {
                        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                        $newsLetter->each->delete();
                    }
                }
                unset($learner_details->is_registered, $learner_details->is_used, $learner_details->deleted_by, $learner_details->created_at, $learner_details->updated_at, $learner_details->deleted_at);
            }



            $params['email'] = [
                'learnerId' => $request->user_id,
                'to' => $learner_details->email,
            ];
            if($request->register_status==1) {
                $subject = "Register successfully";

                send_notification($params, 'registration', ['email'], $subject,"A");
            }


        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 400);
        }

        if (session()->has('new_learner') && session()->get('new_learner') == '1' &&
            session()->has('learnerId') && session()->get('learnerId') == $learner_details->id && !empty($learner_details->email)) {
            $params['common'] = [
                'course_id' => null,
                'learner_id' => $learner_details->id,
                'type' => 1,
            ];
            $params['email']['to'] = $learner_details->email;
            $params['email']['learnerId'] = $learner_details->id;
            $params['push']['to'] = $learner_details->mobile;
            $content = "You have successfully registered";
            $subject = "Registered successfully";
            $res = send_notification($params, 'registration', ['email'], $subject, $content);
            session()->put(['new_learner' => '0', 'learnerId' => ""]);
        }
        $total_cnt = $learner_details->count();
        if ($total_cnt > 0) {
            $learner_details->profile_pic = !empty($learner_details->profile_pic) ? Helper::getImageUrl($learner_details->profile_pic) : '';
            $learner_details->d_o_b = !empty($learner_details->d_o_b) ? Carbon::parse($learner_details->d_o_b)->format('Y-m-d\TH:i:s.v\Z') : "";
            $query = NewsLetter::select('*');

            if (!empty($learner_details->name)) {
                $query = $query->where('name', $learner_details->name);
            }

            if (!empty($learner_details->email)) {
                $query = $query->where('email', $learner_details->email);
            }

            $newLetter = $query->get();
            if ($newLetter->count() > 0) {
                $learner_details->isSubscribeNewsletter = true;
            } else {
                $learner_details->isSubscribeNewsletter = false;
            }
            $data = Helper::apiResonse(1, "Success", $learner_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 400);
        }
    }
    public function profilePic(Request $request)
    {
        $learner_id = request()->user()->id;

        $validator = Validator::make($request->all(), [
            "profile_pic" => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $profile_data = Learner::findOrFail($learner_id)->first();
        if (request()->hasFile('profile_pic')) {
            if (!empty($profile_data->profile_pic)) {
                Storage::delete($profile_data->profile_pic);
            }

            $file_path = Storage::putFileAs('profile', $request->profile_pic, time() . '_' . $request->profile_pic->getClientOriginalName());
            if ($profile_data->count() > 0) {
                Learner::where('id', $learner_id)->update([
                    'profile_pic' => request()->has('profile_pic') ? $file_path : '',
                    'updated_at' => now(),
                ]);

                $emptyFields = Learner::where("id", $request->user_id)
                ->where(function ($query) {
                    $query->whereNull('name')
                        ->orWhereNull('email')
                        ->orWhereNull('mobile')
                        ->orWhereNull('gender')
                        ->orWhereNull('d_o_b')
                        ->orWhereNull('country_id')
                        ->orWhereNull('state_id')
                        ->orWhereNull('city_id')
                        ->orWhereNull('occupation')
                        ->orWhereNull('marital_status')
                        ->orWhereNull('education')
                        ->orWhereNull('your_interests');
                })
                ->get();
            $coin_price = config()->has('settings.completedprofilecoin') ? config('settings.completedprofilecoin') : null;
            if ($emptyFields->isEmpty()) {

                $user_coin = UserCoin::where("learner_id", $request->user_id)->where("type", 6)->first();
                if (empty($user_coin)) {

                    $coin_price = config()->has('settings.completedprofilecoin') ? config('settings.completedprofilecoin') : null;
                    // $coins = $coin_price * config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : null;

                    $user_coin = UserCoin::create([
                        "learner_id" => $request->user_id,
                        "coins" => $coin_price,
                        "type" => 6,
                        // "comment" => "You have got $coin_price coins to successfully completed your profile",
                        // "comment" => "Completing your profile earned you $coin_price coins.",
                        "comment" => "You have earned $coin_price success coins due to you have completed your profile"
                    ]);

                    $subject = "Congratulations! Success Coins Allocated to Your Account!";
                    // $content = "Completing your profile earned you $coin_price coins.";
                    $content = "you have completed your profile.";
                    $plus = UserCoin::where("learner_id", $request->user_id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2);
                        $query->Where("type", "!=", "4");
                    })->sum("coins");

                    // $deduct = UserCoin::where("type",2)->where("type",4)->sum("coins");
                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $learner = Learner::where("id", $request->user_id)->first();
                    $inst = new EarnCoinMail($content, $subject, $learner, $coin_price,$total);
                    if ($learner) {
                        \Mail::to($learner->email)->send($inst);
                    }
                }
            }


            } else {
                $data = Helper::apiResonse(0, "No Records found", []);
                return response()->json($data, 400);
            }
        }

        $profile_details = Learner::select('id', 'name', 'mobile', 'email', 'd_o_b', 'gender', 'country_id', 'state_id', 'city_id', 'profile_pic', 'is_registered', 'occupation', 'marital_status', 'education', 'your_interests')
            ->where('id', $learner_id)
            ->first();
        $total_cnt = $profile_details->count();
        if ($total_cnt > 0) {
            $query = NewsLetter::select('*');

            if (!empty($profile_details->name)) {
                $query = $query->where('name', $profile_details->name);
            }

            if (!empty($profile_details->email)) {
                $query = $query->where('email', $profile_details->email);
            }

            $newLetter = $query->get();
            if ($newLetter->count() > 0) {
                $profile_details->isSubscribeNewsletter = true;
            } else {
                $profile_details->isSubscribeNewsletter = false;
            }
            $profile_details->is_registered = (bool) $profile_details->is_registered;
            $profile_details->profile_pic = !empty($profile_details->profile_pic) ? Helper::getImageUrl($profile_details->profile_pic) : '';

            $data = Helper::apiResonse(1, "Success", $profile_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 400);
        }
    }

    public function getLogs(Request $request)
    {
        $learner_log = LearnerLog::with("getCourse:id,title")
            ->select("device_name", "description", "created_at", "course_id", "type")
            ->where("learner_id", $request->user_id)
            ->orderBy("id", "desc")
            ->get();

        if ($learner_log->count() > 0) {
            $learner_log->map(function ($log) {
                $log->course_name = @$log->getCourse->title;
                $log->device_name = $log->device_name==1 ? "Android" : ($log->device_name==2 ? "Ios" :$log->device_name);
            });

            $data = Helper::apiResonse(1, "Success", $learner_log);
            return response()->json($data, 200);
        } else {
            $learner_log = Helper::apiResonse(0, "No Records found", []);
            return response()->json($learner_log, 400);
        }
    }
}
