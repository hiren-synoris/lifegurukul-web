<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\Learner;
use App\Models\LearnerLog;
use App\Models\LoginUrl;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Jenssegers\Agent\Facades\Agent;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
        $this->middleware('guest:learner')->except(['logout', 'perform']);
    }

    public function showLoginForm()
    {
        return abort(404);
    }
    public function getDevice()
    {
        // $macAddr = exec('getmac');
        // echo $ip = $request->getClientIp();
        // if (Agent::isMobile()) {
        //     $result = 'Yes, This is Mobile.';
        // }else if (Agent::isDesktop()) {
        //     $result = 'Yes, This is Desktop.';
        // }else if (Agent::isTablet()) {
        //     $result = 'Yes, This is Desktop.';
        // }else if (Agent::isPhone()) {
        //     $result = 'Yes, This is Phone.';
        // }
        //return ($result);
        // Agent::setUserAgent('Mozilla/5.0 (Linux; Android 10; Model) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36');

        $browserName = Agent::browser(); // Chrome
        $browserVersion = Agent::version($browserName); //109.0.0.0
        $platform = Agent::platform(); // Windows // LINUX
        return $browserName . " " . $browserVersion . " " . $platform;

    }
    //  create user login data
    public function create(array $data, $flag = 0)
    {

        $learner_data = Learner::where(['mobile' => $data['mobile'], 'country_id' => $data['country_id']])->first();
        $learner = Learner::updateOrCreate(
            ['mobile' => $data['mobile'], 'country_id' => $data['country_id']],
            //['mobile' => $data['mobile']],
            ['password' => Hash::make($data['token']),
                // 'country_id'=> $data['country_id'],
                'country_id' => $data['country_id'],
                'token' => $data['token'],
                'expire_at' => $data['expire_at'],
                'is_used' => 1,
            ]);
        if (!empty($learner_data)) {
            // $learner_email = $learner

        }

        return true;

    }
    // Generate OTP
    public function generate(Request $request)
    {

        $validator = $request->validate([
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'country_id' => 'required|min:1',
        ]);

        // $us_ids = [4, 231, 232, 38]; // 1 phonecode

        // $antarctica_ids = [8, 46, 96, 162]; // 672 phonecode
        // $australia_ids = [13, 45]; // 61  phonecode
        // $bouvet_island_ids = [29, 164, 209]; // 47  phonecode
        // $comoros_ids = [48, 141]; // 269  phonecode
        // $congo_ids = [49, 50]; // 242  phonecode
        // $falkland_Islands = [71, 203]; // 500  phonecode
        // $french_Southern_Territories = [78, 179]; // 262  phonecode
        // $italy = [107, 236]; // 39  phonecode
        // $morocco = [148, 242]; // 212  phonecode
        // $new_Zealand = [157, 174]; // 64  phonecode

        // if (in_array($request->country_id, $us_ids)) {
        //     $country_id = 231;
        // } else if (in_array($request->country_id, $antarctica_ids)) {
        //     $country_id = 8;
        // } else if (in_array($request->country_id, $australia_ids)) {
        //     $country_id = 13;
        // } else if (in_array($request->country_id, $bouvet_island_ids)) {
        //     $country_id = 29;
        // } else if (in_array($request->country_id, $comoros_ids)) {
        //     $country_id = 48;
        // } else if (in_array($request->country_id, $congo_ids)) {
        //     $country_id = 49;
        // } else if (in_array($request->country_id, $falkland_Islands)) {
        //     $country_id = 71;
        // } else if (in_array($request->country_id, $french_Southern_Territories)) {
        //     $country_id = 78;
        // } else if (in_array($request->country_id, $italy)) {
        //     $country_id = 107;
        // } else if (in_array($request->country_id, $morocco)) {
        //     $country_id = 148;
        // } else if (in_array($request->country_id, $new_Zealand)) {
        //     $country_id = 157;
        // } else {
        //     $country_id = $request->country_id;
        // }


        $countryMapping = [
            231 => [4, 231, 232, 38],        // US
            8 => [8, 46, 96, 162],           // Antarctica
            13 => [13, 45],                  // Australia
            29 => [29, 164, 209],            // Bouvet Island
            48 => [48, 141],                 // Comoros
            49 => [49, 50],                  // Congo
            71 => [71, 203],                 // Falkland Islands
            78 => [78, 179],                 // French Southern Territories
            107 => [107, 236],               // Italy
            148 => [148, 242],               // Morocco
            157 => [157, 174]                // New Zealand
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


        $data["mobile"] = (int) $data["mobile"];
        $data["mobile"] = (string) $data["mobile"];

        // $getCountryId = Countries::where('code', trim(strtoupper($request->country_id)))->first();

        // $getCountryId = Countries::where('phonecode', $request->country_id)->first();
        // $getCountryId = Countries::where('id', $request->country_id)->first();

        // if ($getCountryId) {

        //     $data['country_id'] = $getCountryId->id;
        // }

        $this->storeSession($data);
        \Session::put('device_id', $data['deviceId']);
        \Session::put('device_id_log', $data['deviceId']);

        if (!session()->has('mobile')) {
            return response()->json(['error' => "Mobile is Required"]);
            //return redirect()->route('learner')->with('error', 'Mobile is Required');
        }

        $LearnerData = Learner::where('mobile', $request->mobile)->where('country_id', $request->country_id)->first();

        if ($LearnerData) {

            $get_learner_status = Learner::where('mobile', $request->mobile)->where('country_id', $request->country_id)->where("learner_status", 0)->first();

            if ($get_learner_status) {
                return response()->json(['error' => "Your account is inactive, contact to support"]);
            }

        }

        $checkBlockDevice = DeviceToken::where('learner_id', @$LearnerData->id)->where("block_device", 0)->where("device_id", $data['deviceId'])->first();
        if ($checkBlockDevice) {
            return response()->json(['error' => "Your device is blocked, contact to support"]);
        }

        $is_deleted = Learner::where('mobile', $request->mobile)->where('country_id', $request->country_id)->onlyTrashed()->first();

        if (!empty($is_deleted)) {
            $storageUrl = route('contact-us');
            return response()->json(['error' => "You account has been deleted, kindly <a href=" . $storageUrl . " target='_blank' style='color:blue'>contact us</a>"]);
        } else {

            if ($LearnerData && !empty($LearnerData)) {
                $learner_device_token = DeviceToken::where('learner_id', $LearnerData->id)->get()->pluck('device_id')->toArray();
                $learner_device_count = count($learner_device_token);
                // $devicetoken = DeviceToken::where('learner_id', $LearnerData->id)->where('device_id', $request->deviceId)->get();
                $maximum_user_device = config()->has('settings.maximum_user_device') ? config('settings.maximum_user_device') : null;
                // check maximum access
                if (($learner_device_count > 0 && (int) $maximum_user_device > 0) && ((int) $maximum_user_device <= $learner_device_count) && !in_array($request->deviceId, $learner_device_token)) {
                    $storageUrl = route('contact-us');
                    return response()->json(['error' => "You can access your account only from " . $maximum_user_device . " devices. To access from a new device, <a href=" . $storageUrl . " target='_blank' style='color:blue'>contact us</a>.",
                    ]);
                }
            }
        }

        # Generate An OTP
        $verificationCode = $this->generateOtp();

        if (isset($verificationCode)) {
            //dd($request->is_whatsapp);
            // Start plivo send message
            $bypassmobilenumbersforapp = config('settings.bypassmobilenumbersforapp');
            $bypassmobilenumbersforapp_arr = explode(',', $bypassmobilenumbersforapp);
            if (!empty($bypassmobilenumbersforapp_arr) && in_array($request->mobile, $bypassmobilenumbersforapp_arr)) { // by pass for store testing
            } else {
                if (env("APP_ENV") == "production") {
                    $destinatioNumber = getMobileNumber($request->mobile, $request->country_id);
                    if ($destinatioNumber != null) {
                        if ($request->is_whatsapp == 1) { // send in whatsapp
                            sendWAsms($destinatioNumber, $verificationCode['token'], @$LearnerData->name);
                        } else { // send in plivo otp
                            sendSms($destinatioNumber, $verificationCode['token']);
                        }
                    }
                }
            }

            $message = "OTP Sent";
            // END plivo send message

            if (session()->has('expire_at')) {
                $expire_at = session()->get('expire_at');
                $mobile = session()->get('mobile');
                $country_id = session()->get('country_id');

                return response()->json(['success' => $message, 'expire_at' => $expire_at, 'mobile' => $mobile, 'country_id' => $country_id]);
            }
            // return dd($message);
            // return '123565';
            return response()->json(['success' => $message]);
        } else {
            return response()->json(['error' => "Please Resend OTP"]);
        }
    }

    public function generateOtp()
    {

        if (session()->has('mobile')) {
            $mobile = session()->get('mobile');
        } else {
            return response()->json(['error' => "Mobile is Required"]);
            // return redirect()->back()->with('mobileModal', 'Mobile is Required');
        }
        if (session()->has('country_id')) {
            $country_id = session()->get('country_id');

        } else {
            return response()->json(['error' => "Country Code is Required"]);
            // return redirect()->back()->with('mobileModal', 'Mobile is Required');
        }

        # User Does not Have Any Existing OTP
        $verificationCode = Learner::where('mobile', $mobile)->where('country_id', "1")->whereNotNull('password')->whereNull('is_used')->take(1)->get()->toArray();

        $now = Carbon::now();
        $bypassmobilenumbersforapp = config('settings.bypassmobilenumbersforapp');
        $bypassmobilenumbersforapp_arr = explode(',', $bypassmobilenumbersforapp);

        if ($verificationCode && $now->isBefore($verificationCode['expire_at'])) {
            return $verificationCode;
        } else {
            //Create a New OTP

            if (!empty($bypassmobilenumbersforapp_arr) && in_array($mobile, $bypassmobilenumbersforapp_arr)) {

                $token = 111111;

            } else {
                if (env("APP_ENV") == "production") {
                    $token = rand(123456, 999999);
                } else {
                    $token = 111111;
                }
            }

            $data = ['mobile' => $mobile,
                'country_id' => $country_id,
                'token' => $token,
                'expire_at' => Carbon::now()->addMinutes(2)];
            //   'expire_at' => Carbon::now()->addSeconds(5)];
            // store in session
            // dd($data);
            $this->storeSession($data);
            return $data;
        }
    }

    public function mobile()
    {
        return 'mobile';
    }

    public function loginWithOtp(Request $request)
    {
        #Validation

        $validator = $request->validate([
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'country_id' => 'required',
            'password' => 'required',
        ]);

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

        $store = 0;

        $verificationCode = Learner::where('mobile', $request->mobile)->where('country_id', $data['country_id'])->whereNull('is_used')->first();

        $now = Carbon::now();
        if (session()->has('token')) {
            $token = session()->get('token');
        }
        if (session()->has('expire_at')) {
            $expire_at = session()->get('expire_at');
        }
        if (session()->has('deviceId')) {
            $deviceId = session()->get('deviceId');
        }
        if (empty($verificationCode)) {

            if ($request->password != $token) {

                if (isset($expire_at) && $now->isAfter($expire_at)) {
                    session()->put('showBtnflag', 1);
                }
                return response()->json(['error' => "Your OTP is not correct"]);
                //return redirect()->back()->with('otpmodelerror',  "Your OTP is not correct");
            } else if (isset($expire_at) && $now->isAfter($expire_at)) {
                session()->put('showBtnflag', 1);
                return response()->json(['error' => "Your OTP has been expired"]);
                //return redirect()->route('home')->with('otpmodelerror',  "Your OTP has been expired");
            }
            // create new learner
            $store = 1;
            session(['new_learner' => true]);
        } else {

            $result = Learner::find($verificationCode->id);
            if ($result && $result->password == null) {
                if ($request->password != $token) {
                    return response()->json(['error' => "Your OTP is not correct"]);
                }
            } else if (!Hash::check($request->password, $result->password)) {
                return response()->json(['error' => "Your OTP is not correct"]);
                //return redirect()->route('home')->with('otpmodelerror', 'Your OTP is not correct 123');
            } else if ($verificationCode && $now->isAfter($verificationCode->expire_at)) {
                session()->put('showBtnflag', 1);
                return response()->json(['error' => "Your OTP has been expired"]);
                //return redirect()->route('home')->with('mobileModal', 'Your OTP has been expired');
            }
            // update in db only
            $store = 2;
            session(['new_learner' => false]);
        }

        if (isset($store) && $store > 0) {
            // Expire The OTP
            $data = $this->getSession();

            $this->create($data);

            if (Auth::guard('learner')->attempt(['mobile' => $request->mobile, 'password' => $request->password, 'country_id' => $data['country_id']])) {

                // Device Token Store
                $devideInfo = $this->getDevice();
                $deviceId = !isset($deviceId) && empty($deviceId) ? Str::random(10) : $deviceId;

                $deviceTokenSaved = DeviceToken::updateOrCreate(
                    ['device_id' => $deviceId, 'learner_id' => Auth::guard('learner')->user()->id, 'device_type' => 3],
                    ['learner_id' => Auth::guard('learner')->user()->id,
                        'device_token' => null,
                        'device_name' => $devideInfo,
                        'device_type' => 3]);

                LearnerLog::create([
                    "learner_id" => Auth::guard('learner')->user()->id,
                    'device_id' => $deviceId,
                    'device_token' => null,
                    'device_name' => $devideInfo,
                    'description' => "Logged In",
                    'type' => 1,
                ]);
                // dd("");
                $this->removeSession();
                $country_id = 1;
                if (Auth::guard('learner')->user()) {
                    $country_id = Auth::guard('learner')->user()->country_id ?? 1;
                    $id = Auth::guard('learner')->user()->id ?? 0;
                    if ($id > 0) {
                        $learner = Learner::findOrFail($id);
                        if ($learner->email) {
                            return response()->json(['success' => 1]);
                        } else {
                            return response()->json(['success' => 0, 'country_id' => $country_id]);
                        }
                    } else {
                        return response()->json(['success' => 0, 'country_id' => $country_id]);
                    }
                } else {
                    return response()->json(['success' => 0, 'country_id' => $country_id]);
                }
                // return response()->json(['success'=>1]);
                //return redirect()->back()->with('loginsuccess', 'logged successfully');
            } else {
                $this->incrementLoginAttempts($request);
                return response()->json(['error' => 'This account is not activated']);
                //return redirect()->route('home')->with('mobileModal', 'This account is not activated');
            }
        }
    }
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resendOtp(Request $request)
    {


        $countryMapping = [
            231 => [4, 231, 232, 38],        // US
            8 => [8, 46, 96, 162],           // Antarctica
            13 => [13, 45],                  // Australia
            29 => [29, 164, 209],            // Bouvet Island
            48 => [48, 141],                 // Comoros
            49 => [49, 50],                  // Congo
            71 => [71, 203],                 // Falkland Islands
            78 => [78, 179],                 // French Southern Territories
            107 => [107, 236],               // Italy
            148 => [148, 242],               // Morocco
            157 => [157, 174]                // New Zealand
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
            return response()->json(['error' => "You account has been deleted, kindly<a href=" . $storageUrl . " target='_blank'  style='color:blue'>contact us</a>"]);
        }

        # Generate An OTP
        $verificationCode = $this->generateOtp();
        // dd($request->is_whatsapp);
        if (isset($verificationCode)) {

            $bypassmobilenumbersforapp = config('settings.bypassmobilenumbersforapp');
            $bypassmobilenumbersforapp_arr = explode(',', $bypassmobilenumbersforapp);
            if (!empty($bypassmobilenumbersforapp_arr) && in_array($request->mobile, $bypassmobilenumbersforapp_arr)) { // by pass for store testing

            } else {

                if (env("APP_ENV") == "production") {
                    $destinatioNumber = getMobileNumber($request->mobile, $verificationCode['country_id']);
                    if ($destinatioNumber != null) {
                        if ($request->is_whatsapp == 1) { // send in whatsapp
                            $LearnerData = Learner::where('mobile', $request->mobile)->where('country_id', $verificationCode['country_id'])->first();
                            sendWAsms($destinatioNumber, $verificationCode['token'], @$LearnerData->name);
                        } else { // send in plivo otp
                            sendSms($destinatioNumber, $verificationCode['token']);
                        }
                    }
                }
            }
            $message = "OTP sent";
            # Return With OTP
            session()->put('showBtnflag', 0);
            if (session()->has('expire_at')) {
                $expire_at = session()->get('expire_at');
                $mobile = session()->get('mobile');
                $country_id = session()->get('country_id');
                return response()->json(['success' => $message, 'expire_at' => $expire_at, 'mobile' => $mobile, 'country_id' => $country_id]);
            }
            return response()->json(['success' => $message]);
            //return redirect()->back()->with('otpmodel',  $message);
        } else {
            return response()->json(['error' => "Please Resend OTP"]);
            //return redirect()->back()->with('otpmodelerror',  "Please Resend Otp");
        }
    }

    public function logout(Request $request)
    {

        $devideInfo = $this->getDevice();
        session()->has('checkoutSessionData') ? session()->forget('checkoutSessionData') : '';
        // dd(session()->get('deviceId'));

        if (Auth::guard('learner')->check()) {
            LearnerLog::create([
                "learner_id" => Auth::guard('learner')->user()->id,
                'device_id' => session()->get('device_id_log'),
                'device_token' => null,
                'device_name' => $devideInfo,
                'description' => "Logout",
                'type' => 2,
            ]);
            Auth::guard('learner')->logout();
            return redirect()->route('home');
        }

        // $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
        ? new JsonResponse([], 204)
        : redirect('/');
    }
    public function storeSession($data)
    {
        if (isset($data['mobile'])) {
            session()->put('mobile', $data['mobile']);
        }
        if (isset($data['token'])) {
            session()->put('token', $data['token']);
        }
        if (isset($data['expire_at'])) {
            session()->put('expire_at', $data['expire_at']);
        }
        if (isset($data['country_id'])) {
            session()->put('country_id', $data['country_id']);
        }
        if (isset($data['deviceId'])) {
            session()->put('deviceId', $data['deviceId']);
        }

    }
    public function getSession()
    {
        return session()->all();
    }
    public function removeSession()
    {
        session()->forget(['mobile', 'token', 'expire_at', 'showBtnflag', 'country_id', 'deviceId']);
    }

    public function DirectLogin($mobile, Request $request)
    {

        // Auth::guard('learner')->logout();
        $mobile = Crypt::decrypt($mobile);
        $country_id = $request->c_id;
        $loginUrl = LoginUrl::where("mobile", $mobile)->where("country_id", $country_id)->first();
        // dd(now()->diffInMinutes($loginUrl->updated_at));
        if ($loginUrl) {
            if (now()->diffInMinutes($loginUrl->updated_at) > 30) {
                abort(403);
            }
            $learner = Learner::where("mobile", $mobile)->where("country_id", $country_id)->first();
            if (Auth::guard('learner')->attempt(['mobile' => $mobile, "password" => $learner->token, "country_id" => $learner->country_id])) {
                $devideInfo = $this->getDevice();
                $deviceId = !isset($deviceId) && empty($deviceId) ? Str::random(10) : $deviceId;
                $deviceTokenSaved = DeviceToken::updateOrCreate(
                    ['device_id' => $deviceId, 'learner_id' => Auth::guard('learner')->user()->id, 'device_type' => 3],
                    ['learner_id' => Auth::guard('learner')->user()->id,
                        'device_token' => null,
                        'device_name' => $devideInfo,
                        'device_type' => 3]);

                return redirect("/");

            }
        } else {
            abort(403);
        }
    }

}
