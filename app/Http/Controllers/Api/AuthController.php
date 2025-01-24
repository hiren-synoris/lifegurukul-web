<?php

// namespace App\Http\Controllers\Auth;
namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Learner;
use App\Models\LearnerLog;
use App\Models\NewsLetter;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function storeDeviceDetails(Request $request)
    {

        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required', 'filled', 'min:1', 'max:3'],
            'devicetoken' => 'required|filled',
            'version' => 'required|filled',
        ]);
        if ($validator_header_data->fails()) {
            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        }

        $deviceTokenSaved = DeviceToken::updateOrCreate(
            [
                'device_id' => $request->header('deviceid'),
                'device_type' => $request->header('devicetype'),
                'version' => $request->header('version'),

            ],
            ['device_token' => $request->header('devicetoken')]
        );

        if ($deviceTokenSaved) {
            $response = array();
            $response['status'] = 1;
            $response['message'] = 'Success';
            $response['token'] = $deviceTokenSaved->createToken('login', ['visitor'])->accessToken;
            //$response['token'] = '';
            return response()->json($response, 200);
        } else {
            $data = Helper::apiResonse(0, "Something went wrong, please try again later", []);
            return response()->json($data, 422);
        }
    }

    //API to Generate OTP
    public function generateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            $data['status'] = 0;
            $data['message'] = "";
            $data['data'] = $validator->messages();
            return response()->json($data, 422);
        }

        $countryMapping = [
            231 => [4, 231, 232, 38],        // US // 1 phonecode
            8 => [8, 46, 96, 162],           // Antarctica // 672 phonecode
            13 => [13, 45],                  // Australia // 61  phonecode
            29 => [29, 164, 209],            // Bouvet Island // 47  phonecode
            48 => [48, 141],                 // Comoros / 269  phonecode
            49 => [49, 50],                  // Congo // 242  phonecode
            71 => [71, 203],                 // Falkland Islands // 500  phonecode
            78 => [78, 179],                 // French Southern Territories // 262  phonecode
            107 => [107, 236],               // Italy // 39  phonecode
            148 => [148, 242],               // Morocco / 212  phonecode
            157 => [157, 174]                // New Zealand // 64  phonecode
        ];


        foreach ($countryMapping as $mappedId => $ids) {
            if (in_array($request->country_id, $ids)) {
                $request->merge(['country_id' => $mappedId]);
                break;
            } else {
                $request->merge(['country_id' => $request->country_id]);
            }
        }

        $data = $request->all();


        # Generate An OTP
        $verificationCode = Learner::where('mobile', $request->mobile)->where('country_id', $data['country_id'])->whereNotNull('password')->whereNull('is_used')->take(1)->get()->makeVisible(['expire_at'])->toArray();
        $now = Carbon::now();

        //

        if ($verificationCode && $now->isBefore($verificationCode['expire_at'])) {
            return response()->json($verificationCode);
        } else {

            $verificationCode = Learner::where('mobile', $request->mobile)->where('country_id', $data['country_id'])->first();

            $get_learner_status = Learner::where('mobile', $request->mobile)->where('country_id', $data['country_id'])->where("learner_status", 0)->first();

            if ($get_learner_status) {
                return response()->json(['message' => 'Your account is inactive, contact to support.'], 401);
            }

            if ($verificationCode && !empty($verificationCode)) {
                $learner_device_token = DeviceToken::where('learner_id', $verificationCode->id)->get()->pluck('device_id')->toArray();

                $learner_device_count = count($learner_device_token);
                // $devicetoken = DeviceToken::where('learner_id', $verificationCode->id)->where('device_id', $request->header('deviceid'))->get();
                $maximum_user_device = config()->has('settings.maximum_user_device') ? config('settings.maximum_user_device') : null;
                // check maximum access
                if (($learner_device_count > 0 && (int) $maximum_user_device > 0) && ((int) $maximum_user_device <= $learner_device_count) && !in_array($request->header('deviceid'), $learner_device_token)) {
                    $data = Helper::apiResonse(0, "You can access your account only from " . $maximum_user_device . " devices. To access from new device contact Us.", []);
                    return response()->json($data, 400);
                }
            }

            $checkBlockDevice = DeviceToken::where("block_device", 0)->where("device_id", $request->header('deviceid'))->first();
            if ($checkBlockDevice) {
                return response()->json(['message' => 'Your device is blocked, contact to support.'], 401);
            }
            //Create a New OTP

            $bypassmobilenumbersforapp = config('settings.bypassmobilenumbersforapp');
            $bypassmobilenumbersforapp_arr = explode(',', $bypassmobilenumbersforapp);

            if (!empty($bypassmobilenumbersforapp_arr) && in_array($request->mobile, $bypassmobilenumbersforapp_arr)) { // by pass for store testing
                $token = '111111';
            } else {
                if (env("APP_ENV") == "production") { // remove this
                    $token = rand(123456, 999999);
                } else {
                    $token = '111111';
                }
            }

            $data_resp = [
                // 'mobile'=>$request->mobile,
                'otp' => (string) $token,
            ];
            // dd(env("APP_ENV"));
            if (!empty($bypassmobilenumbersforapp_arr) && in_array($request->mobile, $bypassmobilenumbersforapp_arr)) { // by pass for store testing

            } else {
                // Start plivo send message
                if (env("APP_ENV") == "production") { // remove this
                    // dd($request->mobile);
                    $destinatioNumber = getMobileNumber($request->mobile, $data['country_id']);
                    if ($destinatioNumber != null) {
                        if ($request->is_whatsapp == 1) { // send in whatsapp
                            sendWAsms($destinatioNumber, $token, @$verificationCode->name);
                        } else { // send in plivo otp
                            sendSms($destinatioNumber, $token);
                        }
                    }
                }
            }

            // END plivo send message

            $response_array = [];
            $response_array['status'] = 1;
            $response_array['message'] = 'Success';
            $response_array['data'] = $data_resp;
            return response()->json($response_array);
        }
    }

    //API to resent OTP
    public function resendOtp(Request $request)
    {
        $data = $this->generateOtp($request);
        return $data;
    }

    //API to Login
    public function verifyOtp(Request $request)
    {
        // return response()->json([ 'error' => "Your device is blocked, contact to support"]);
        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required', 'filled', 'min:1', 'max:3'],
            'devicetoken' => 'required|filled',
        ]);
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'country_id' => 'required|exists:countries,id',
            'otp' => 'required',
        ]);

        if ($validator_header_data->fails()) {

            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        }

        if ($validator->fails()) {

            $data['status'] = 0;
            $data['message'] = "";
            $data['data'] = $validator->messages();
            return response()->json($data, 422);
        }



        $countryMapping = [
            231 => [4, 231, 232, 38],        // US // 1 phonecode
            8 => [8, 46, 96, 162],           // Antarctica // 672 phonecode
            13 => [13, 45],                  // Australia // 61  phonecode
            29 => [29, 164, 209],            // Bouvet Island // 47  phonecode
            48 => [48, 141],                 // Comoros / 269  phonecode
            49 => [49, 50],                  // Congo // 242  phonecode
            71 => [71, 203],                 // Falkland Islands // 500  phonecode
            78 => [78, 179],                 // French Southern Territories // 262  phonecode
            107 => [107, 236],               // Italy // 39  phonecode
            148 => [148, 242],               // Morocco / 212  phonecode
            157 => [157, 174]                // New Zealand // 64  phonecode
        ];


        foreach ($countryMapping as $mappedId => $ids) {
            if (in_array($request->country_id, $ids)) {
                $request->merge(['country_id' => $mappedId]);
                break;
            } else {
                $request->merge(['country_id' => $request->country_id]);
            }
        }

        $data = $request->all();


        $is_deleted = Learner::where('mobile', $request->mobile)->where('country_id', $data['country_id'])->onlyTrashed()->first();

        if (!empty($is_deleted)) {
            $storageUrl = route('contact-us');
            $data = Helper::apiResonse(0, "You account has been deleted, kindly contact us.", []);
            return response()->json($data, 400);
        } else {

            $store = 0;
            $now = Carbon::now();

            /*if ($verificationCode && !empty($verificationCode)) {
            $learner_device_token = DeviceToken::where('learner_id', $verificationCode->id)->get()->pluck('device_id')->toArray();
            // $devicetoken = DeviceToken::where('learner_id', $verificationCode->id)->where('device_id', $request->header('deviceid'))->get();
            // $maximum_user_device = config()->has('settings.maximum_user_device') ? config('settings.maximum_user_device') : NULL;
            // // check maximum access
            // if (($learner_device_token > 0 && (int)$maximum_user_device > 0) && ((int)$maximum_user_device <= $learner_device_token)) { // && ($devicetoken->count() == 0)){
            //     // if($devicetoken->count() == 1)
            //     $data = Helper::apiResonse(0, "You can access your account only from " . $maximum_user_device . " devices. To access from new device contact Us.", []);
            //     return response()->json($data, 400);
            // }

            $learner_device_count = count($learner_device_token);
            $devicetoken = DeviceToken::where('learner_id', $verificationCode->id)->where('device_id', $request->header('deviceid'))->get();
            $maximum_user_device = config()->has('settings.maximum_user_device') ? config('settings.maximum_user_device') : NULL;
            // check maximum access
            if(($learner_device_count > 0 && (int)$maximum_user_device > 0) && ((int)$maximum_user_device <= $learner_device_count) && !in_array($request->header('deviceid'),$learner_device_token)){
            $data = Helper::apiResonse(0, "You can access your account only from ".$maximum_user_device." devices. To access from new device contact Us.", []);
            return response()->json($data, 400);
            }
            }*/
            if (empty($verificationCode)) {
                // create new learner
                $store = 1;
            } else {
                $result = Learner::find($verificationCode->id);
                $store = 2;
            }
        }

        if (isset($store) && $store > 0) {
            // Expire The OTP
            try {
                $data = [
                    'mobile' => $request->mobile,
                    'country_id' => $request->country_id,
                    'token' => $request->otp,
                    'device_id' => $request->header('deviceid'),
                    'device_type' => $request->header('devicetype'),
                    'device_token' => $request->header('devicetoken'),
                ];
                $is_Learner_exist = Learner::where(['mobile' => $data['mobile'], 'country_id' => $data['country_id']])->count();

                $learner = $this->create($data);
                // if($learner->count() > 0){

                if (Auth::guard('learner')->attempt(['mobile' => $request->mobile, 'password' => $request->otp, 'country_id' => $request->country_id])) {
                    $query = NewsLetter::select('*');

                    if (!empty($learner->name)) {
                        $query = $query->where('name', $learner->name);
                    }

                    if (!empty($learner->email)) {
                        $query = $query->where('email', $learner->email);
                    }

                    $newLetter = $query->get();
                    if ($newLetter->count() > 0) {
                        $learner->isSubscribeNewsletter = true;
                    } else {
                        $learner->isSubscribeNewsletter = false;
                    }
                    $learner->is_registered = (bool) $learner->is_registered;
                    unset($learner->deleted_by, $learner->is_used, $learner->is_registered, $learner->updated_at, $learner->created_at, $learner->deleted_at);
                    $learner->profile_pic = !empty($learner->profile_pic) ? Helper::getImageUrl($learner->profile_pic) : '';
                    $learner->d_o_b = !empty($learner->d_o_b) ? Carbon::parse($learner->d_o_b)->format('Y-m-d\TH:i:s.v\Z') : "";
                    $response_array = [];
                    $response_array['status'] = 1;
                    $response_array['data'] = Helper::nullToEmptyStringHelper($learner);
                    $response_array['data']['token'] = $learner->createToken('login', ['learner'])->accessToken;
                    $response_array['message'] = 'Sucessfully Logged In';

                    LearnerLog::create([
                        "learner_id" => $learner->id,
                        'device_id' => $request->header('deviceid'),
                        'device_token' => $request->header('devicetoken'),
                        'device_name' => $request->header('devicetype'),
                        'description' => "Logged In",
                        'type' => 1,
                    ]);
                    return response()->json($response_array);
                } /* else {
            return response()->json(['status' => 0, 'Could not register. Some error occurred.'], 400);
            }*/
            } catch (\Exception $e) {
                return response()->json(['error' => 'This account is not activated']);
            }
        }
    }
    public function create(array $data, $flag = 0)
    {

        $learner_data = Learner::updateOrCreate(
            ['mobile' => $data['mobile'], 'country_id' => $data['country_id']],
            [
                'password' => Hash::make($data['token']),
                'token' => $data['token'],
                'is_used' => 1,
            ]
        );
        if ($learner_data->wasRecentlyCreated) {
            // $request->session()->put('new_learner', 1);
            session()->put(['new_learner' => '1', 'learnerId' => $learner_data->id]);
        } else {
            // $request->session()->put('new_learner', 0);
            session()->put(['new_learner' => '1', 'learnerId' => $learner_data->id]);
        }
        // $condition = [
        //     'device_id' => $data['device_id'],
        //     'device_type' => $data['device_type'],
        //     'device_token' => $data['device_token'],
        // ];

        // $is_check_device = DeviceToken::where("device_token", $data['device_token'])->where("learner_id", $learner_data->id)->first();

        // if ($is_check_device == null) {

        //     $deviceTokenSaved = DeviceToken::updateOrCreate(
        //         ['device_id' => $data['device_id'], 'device_type' => $data['device_type']],
        //         ['learner_id' => $learner_data->id, 'device_token' => $data['device_token']]
        //     );
        //     if ($deviceTokenSaved->count() > 0) {
        //         return $learner_data;
        //     }
        // } else {
        //     return $learner_data;
        // }

        $is_check_device = DeviceToken::where("device_id", $data['device_id'])
                               ->where("learner_id", $learner_data->id)
                               ->first();

        // If the device token exists but has changed
        if ($is_check_device == null || $is_check_device->device_token != $data['device_token']) {
            
            // Insert a new record or update the device_token for the same device_id and learner_id
            $deviceTokenSaved = DeviceToken::updateOrCreate(
                ['device_id' => $data['device_id'], 'learner_id' => $learner_data->id],  // Find by these fields
                ['device_type' => $data['device_type'], 'device_token' => $data['device_token']] // Update or create with these values
            );

            if ($deviceTokenSaved) {
                return $learner_data;
            }
        } else {
            return $learner_data;  // Return if token didn't change
        }
    } 

    public function logout(Request $request)
    {
        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required', 'filled', 'min:1', 'max:3'],
            'devicetoken' => 'required|filled',
        ]);

        if ($validator_header_data->fails()) {
            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        }
        $user_id = request()->user_id;
        DeviceToken::where('learner_id', $user_id)
            ->where('device_id', $request->header('deviceid'))
            ->where('device_type', $request->header('devicetype'))
            ->where('device_token', $request->header('devicetoken'))
            ->update([
                'learner_id' => null,
            ]);
        $token = $request->user()->token();
        $token->revoke();
        LearnerLog::create([
            "learner_id" => $request->user_id,
            'device_id' => $request->header('deviceid'),
            'device_token' => $request->header('devicetoken'),
            'device_name' => $request->header('devicetype'),
            'description' => "Logged out",
            'type' => 2,
        ]);
        $data = Helper::apiResonse(1, "You have been successfully logged out!", []);
        return response()->json($data, 200);
    }
}
