<?php

namespace App\Http\Controllers\Api;

use App\Models\Blog;
use App\Models\Page;
use App\Helper\Helper;
use App\Models\Settings;
use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings()
    {
        // dd('12');
        $keys = [ 'contact_no', 'whatsapp_contact_no','whatsappquerymsg',"igst","sgst","cgst", "hidewelcomescreen"];
        $settings = array();
        array_map(function($item) use (&$settings) {
            $settings[$item['key']] = $item['value'];
        }, Settings::select('key', 'value')->whereIn('key', $keys)->get()->toArray());

        $pages = array();
        array_map(function($item) use (&$pages) {
            $slug = str_replace("-", "_", $item['slug']);
            $pages[$slug] = env('APP_URL') . '/page/' . $item['slug'] . '?app&isWebside=1';
        }, Page::select('slug')->whereIn('slug',['term-condition', 'privacy-policy', 'about-us', 'refund-policy','how-to-earn-coin'])->get()->toArray());
        $payment_gateway = '';
        if(config()->has('settings.razorpay_status') && config('settings.razorpay_status') == 1){
            $payment_gateway = 'razorpay';
        } else if(config()->has('settings.instamojo_status') && config('settings.instamojo_status') == 1) {
            $payment_gateway = 'instamojo';
        }

        $settings['payment_gateway'] = $payment_gateway;

        $rkey = config()->has('settings.razorpay_key') ? config('settings.razorpay_key') : null;
        $rsecret = config()->has('settings.razorpay_secret') ? config('settings.razorpay_secret') : null;
        if (config()->has('settings.razorpay_sandbox') && config('settings.razorpay_sandbox') == 1) {
            $rkey = config()->has('settings.razorpay_sandbox_key') ? config('settings.razorpay_sandbox_key') : null;
            $rsecret = config()->has('settings.razorpay_sandbox_secret') ? config('settings.razorpay_sandbox_secret') : null;
        }
        $settings['razorpay_id'] = $rkey;
        $settings['razorpay_secret'] = $rsecret;

        $setting_details =  array_merge($settings, $pages);

        $setting_details['occupations'] = DropdownOption::GetDropdownCategoriesForApi('occupation')->get();
        $setting_details['marital_status'] = DropdownOption::GetDropdownCategoriesForApi('marital-status')->get();
        $setting_details['educations'] = DropdownOption::GetDropdownCategoriesForApi('education')->get();
        $setting_details['your_interests'] = DropdownOption::GetDropdownCategoriesForApi('your-interests')->get();

        $total_cnt = count($setting_details);
        if($total_cnt > 0){
            $data = Helper::apiResonse(1, "Success", $setting_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 400);
        }
    }
}
