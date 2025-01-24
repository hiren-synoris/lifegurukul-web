<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Models\Learner;
use Razorpay\Api\Api;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function payment(Request $request){
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'payment_gateway' => 'required|numeric|between:1,2'
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $learner_id = request()->user_id;
        $input = $request->all();
        $key = config()->has('settings.razorpay_key') ? config('settings.razorpay_key') : NULL;
        $secret = config()->has('settings.razorpay_secret') ? config('settings.razorpay_secret') : NULL;
        if(config()->has('settings.razorpay_sandbox') && config('settings.razorpay_sandbox') == 1){
            $key = config()->has('settings.razorpay_sandbox_key') ? config('settings.razorpay_sandbox_key') : NULL;
            $secret = config()->has('settings.razorpay_sandbox_secret') ? config('settings.razorpay_sandbox_secret') : NULL;
        }
        $api = new Api($key, $secret);

        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        echo "<pre>";print_r($payment);die;

        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
                DB::beginTransaction();
                $payment_id = $input['razorpay_payment_id'];
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount']));

                Learner::where('id', auth()->guard('learner')->user()->id)->update(['state_id' => $input['state_id_hidden'] ?? auth()->guard('learner')->user()->state_id, 'city_id' => $input['city_id_hidden'] ?? auth()->guard('learner')->user()->city_id]);

                if(empty(auth()->guard('learner')->user()->name)){
                    Learner::where('id', auth()->guard('learner')->user()->id)->update(['name' => $input['full_name_hidden'] ?? NULL]);
                }
                if(empty(auth()->guard('learner')->user()->email)){
                    Learner::where('id', auth()->guard('learner')->user()->id)->update(['email' => $input['email_hidden'] ?? NULL]);
                }

                $expiredAt = get_plan_expire($input['planId']);
                //Payment record create below
                UserCourse::create([
                    'learner_id' => auth()->guard('learner')->user()->id ?? NULL,
                    'course_id' => $input['courseId']??NULL,
                    'plan_id' => $input['planId']??NULL,
                    'order_status' => isset($payment) && !empty($payment) && $payment->status == 'authorized' ? 1 : 2,
                    'payment_order_status' => isset($payment) && !empty($payment) ? $payment->status : NULL,
                    'payment_gateway' => 1,
                    'transaction_id' => isset($payment) && !empty($payment) ? $payment->id : NULL,
                    'expire_at'=>$expiredAt,
                    'price' => $input['amount']??0.00,
                    'full_name' => request()->has('full_name_hidden') && request()->filled('full_name_hidden') ? $input['full_name_hidden'] : auth()->guard('learner')->user()->name,
                    'email' => isset($payment) && !empty($payment) ? $payment->email : NULL,
                    'mobile' => request()->has('mobile_hidden') && request()->filled('mobile_hidden') ? $input['mobile_hidden'] : auth()->guard('learner')->user()->mobile,
                    'country_id' => request()->has('country_id_hidden') && request()->filled('country_id_hidden') ? $input['country_id_hidden'] : auth()->guard('learner')->user()->country_id,
                    'state_id' => request()->has('state_id_hidden') && request()->filled('state_id_hidden') ? $input['state_id_hidden'] : auth()->guard('learner')->user()->state_id,
                    'city_id' => request()->has('city_id_hidden') && request()->filled('city_id_hidden') ? $input['city_id_hidden'] : auth()->guard('learner')->user()->city_id,
                    'transaction_response' => json_encode($response->toArray()),
                ]);

                DB::commit();
                //If user not logged in then new 'Learner' create and auto logged in.
                // if(!auth()->guard('learner')->check()){
                //     auth()->guard('learner')->login($learner);
                // }
                $this->sessionDestroy();
                return response()->json([
                    'success' => true,
                    'url' => route('my.course'),
                    'message' => 'Payment successfully Completed!'
                ], 200);


            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'url' => route('course.list'),
                    'message' => 'Something wrong..'.$e->getMessage()
                ], 200);
                // $notification['type'] = "sweet-alert";
                // $notification['status'] = "error";
                // $notification['title'] = "Error";
                // $notification['msg'] = $e->getMessage();
            }
        }
        // $learner_details = Learner::findOrFail($request->learner_id);


        // if($learner_details->count() > 0){
        //     if(isset($request->name) && !empty($request->name)){
        //         $learner_details->name = $request->name;
        //     }
        //     if(isset($request->email) && !empty($request->email)){
        //         $learner_details->email = $request->email;
        //     }
        //     if(isset($request->country_id) && !empty($request->country_id)){
        //         $learner_details->country_id = $request->country_id;
        //     }
        //     if(isset($request->state_id) && !empty($request->state_id)){
        //         $learner_details->state_id = $request->state_id;
        //     }
        //     if(isset($request->city_id) && !empty($request->city_id)){
        //         $learner_details->city_id = $request->city_id;
        //     }
        //     if(isset($request->d_o_b) && !empty($request->d_o_b)){
        //         $learner_details->d_o_b = $request->d_o_b;
        //     }
        //     // if(isset($request->mobile) && !empty($request->mobile)){
        //     //     $learner_details->mobile = $request->mobile;
        //     // }
        //     if(isset($request->gender) && !empty($request->gender)){
        //         $learner_details->gender = $request->gender;
        //     }
        //     // if(isset($request->token) && !empty($request->token)){
        //     //     $learner_details->token = $request->token;
        //     // }
        //     // if(isset($request->isRegistered)){
        //     //     $learner_details->is_registered = $request->isRegistered;
        //     // }
        //     $learner_details->updated_at = now();
        //     $learner_details->save();

        //     if(isset($request->isSubscribeNewsletter)){
        //         $newsLetter = NewsLetter::where('name', $learner_details->name)->where('email', $learner_details->email)->get();
        //         if($request->isSubscribeNewsletter){
        //             $name = isset($request->name) && !empty($request->name) ? $request->name : $learner_details->name;
        //             $email = isset($request->email) && !empty($request->email) ? $request->email : $learner_details->email;
        //             $NewsLetter = NewsLetter::updateOrCreate(
        //                 ['name' => $name, 'email'=> $email]
        //             );
        //         } else {
        //             $newsLetter = NewsLetter::where('name', $learner_details->name)->where('email', $learner_details->email)->get();
        //             // echo "<pre>";print_r($newsLetter);die;
        //             if($newsLetter->count() > 0){
        //                 DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        //                 $newsLetter->each->delete();}
        //         }
        //         unset($learner_details->is_registered,$learner_details->is_used,$learner_details->deleted_by,$learner_details->created_at,$learner_details->updated_at,$learner_details->deleted_at);
        //     }

        // } else {
        //     $data = Helper::apiResonse(0, "No Records found", []);
        //     return response()->json($data, 400);
        // }


    }
    public function profilePic(Request $request){
        $learner_id = request()->user()->id;
        $validator = Validator::make($request->all(), [
            "profile_pic" => 'nullable'
		]);

		if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $profile_data = Learner::findOrFail($learner_id)->first();
        if(request()->hasFile('profile_pic')){
            if(!empty($profile_data->profile_pic)){
                Storage::delete($profile_data->profile_pic);
            }


            $file_path = Storage::putFileAs('profile', $request->profile_pic, time().'_'.$request->profile_pic->getClientOriginalName());
            if($profile_data->count() > 0){
                Learner::where('id', $learner_id)->update([
                    'profile_pic' => request()->has('profile_pic') ?  $file_path: '',
                    'updated_at' => now()
                ]);
            } else {
                $data = Helper::apiResonse(0, "No Records found", []);
                return response()->json($data, 400);
            }
        }

        $profile_details = Learner::select('id', 'name', 'mobile', 'email', 'd_o_b','gender' ,'country_id', 'state_id', 'city_id', 'profile_pic', 'is_registered')
                        ->where('id',$learner_id)
                        ->first();
        $total_cnt = $profile_details->count();
        if($total_cnt > 0){
            $query = NewsLetter::select('*');

            if(!empty($profile_details->name)) $query = $query->where('name', $profile_details->name);
            if(!empty($profile_details->email)) $query = $query->where('email', $profile_details->email);

            $newLetter = $query->get();
            if($newLetter->count() > 0){
                $profile_details->isSubscribeNewsletter  = true;
            } else {
                $profile_details->isSubscribeNewsletter  = false;
            }
            $profile_details->is_registered = (bool)$profile_details->is_registered;
            $profile_details->profile_pic = !empty($profile_details->profile_pic) ? Helper::getImageUrl($profile_details->profile_pic) : '';

            $data = Helper::apiResonse(1, "Success", $profile_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 400);
        }
    }
}
