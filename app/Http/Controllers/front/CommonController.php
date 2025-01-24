<?php
namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Countries;
use App\Models\Cities;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\DropdownOption;
use App\Models\Instructors;
use App\Models\States;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
class CommonController extends Controller
{
    public function getCountry()
    {
        $data['countries'] = Countries::get(["name","id"]);
        return response()->json($data);
    }
    public function getState(Request $request)
    {

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
        $country_ids = [];
        foreach ($countryMapping as $mappedId => $ids) {
            if (in_array($request->country_id, $ids)) {
                $request->merge(['country_id' => $mappedId]);
                $country_ids =  $ids;
                break;
            }
        }


        if (isset($country_ids) && !empty($country_ids)) {
            $data['states'] = States::wherein('country_id', $country_ids)->get(["name","id"]);;
        } else {

        $data['states'] = States::where("country_id",$request->country_id)
        ->get(["name","id"]);
        }


        return response()->json($data);
    }
    public function getCity(Request $request)
    {
        $data['cities'] = Cities::where("state_id",$request->state_id)
                    ->get(["name","id"]);
        return response()->json($data);
    }
    public function getCountryCode()
    {
        $data['countries'] = Countries::get(["name","id","flag"]);
        return response()->json($data);
    }
    public function getCoursePlan(Request $request)
    {
        $data['plans'] = CoursePlan::where("course_id",$request->course_id)
                                ->get(["plan_name","final_payable_price","id"]);
        return response()->json($data);
    }
    public function setCountryFlag()
    {
        $path =  public_path()."/json/flag.json";
        $json = json_decode(file_get_contents($path), true);
        if($json){
            foreach($json as $key => $value)
            {

                $countryCode = $value["alpha2Code"];
                $fileUrl = $value["flags"]["png"];
                    // update code from here
                    $UpdateDetails = Countries::where('code', '=', $countryCode)->where('flag','LIKE','% %')->first();
                    if($UpdateDetails)
                    {
                        $imageName = str_replace(' ', '_', strtolower(trim($UpdateDetails->name)));
                        $upload = $this->downloadFile($fileUrl,$imageName);
                        if($upload){
                            $UpdateDetails->flag = "flags/".$imageName.".png";
                            $UpdateDetails->save();
                            echo "<br/>Countries".$imageName;
                        }
                    }
            }
        }
    }

    function downloadFile($file_name,$countryName){
        $file = file_get_contents($file_name);
        $path = base_path().'/public/front/img/flags/'.$countryName.'.png';
        return File::put($path,$file);
    }

    function search(Request $request){
        // dd("ok");
       $search = '';
        if(request()->has('s') && !empty($request->query('s'))){
            $search=trim(strtolower($request->query('s')));
            //if(strlen($search) >= 3){
                $langFlag = 0;
               try {
                    $rows_number = 10;
                    $english = 'english';
                    $hindi = 'hindi';
                    if(preg_match_all("/".$search."/", $english)){
                        $langFlag = 1;
                    }
                    if(preg_match_all("/".$search."/", $hindi)){
                        $langFlag = 2;
                    }
                    $notification = [];
                    $deviceType = Course::COURSE_WEBSITE;
                    $course = Course::with(['instructor','categories'=> function($query){
                                            $query->whereNull('course_categories.deleted_at');
                                        },
                                        'chapters' => function ($query) {
                                            $query->where('parent_id',0);
                                        },
                                        'packages','rating_reviews'])
                                        ->with('instructor.instructure')
                                        ->where('courses.status','1')
                                        ->where('cp.status','1')
                                        ->whereHas("coursePlan",function($course){
                                            $course->where("status",'1');
                                        })
                                        ->whereHas("user")
                                        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
                                        ->whereRaw("find_in_set($deviceType , course_platform)")
                        ->leftJoin('course_categories','courses.id','=','course_categories.course_id')
                        ->leftJoin('dropdown_options','course_categories.category_id','=','dropdown_options.id')
                        ->Join('course_plans as cp','cp.id','=','courses.default_web_price')
                        ->whereRaw('LOWER(`courses`.`title`) LIKE ?', "%{$search}%")
                        ->orWhereRaw('LOWER(`courses`.`tags`) LIKE ?', "%{$search}%");
                        // ->orWhereRaw('LOWER(`title`) LIKE ?', "%{$search}%")
                        if($langFlag > 0){
                            $course->orWhere('lng', $langFlag);
                        }
                        $course->get();
                       $course
                        ->where('courses.status', '=', 1)
                        ->orderBy('courses.order', 'ASC')
                        ->groupBy('id')

                        ->select(DB::raw('"a" as flag'), 'courses.title', 'courses.id','courses.image as path','courses.slug','lng','tags','courses.created_at',DB::raw('"content" as content'),'instructor_id','type','courses.updated_at','is_free',DB::raw('"category_id" as category_id'), 'hours', 'minutes',
                        'cp.plan_type', 'cp.list_price', 'cp.final_payable_price','cp.id as planId');

                    $blog = Blog::whereRaw('LOWER(`title`) LIKE ?',"%{$search}%")
                        ->orWhereRaw('LOWER(`dropdown_options`.`name`) LIKE ?', "%{$search}%")
                        ->leftJoin('dropdown_options','category_id','=','dropdown_options.id')
                        ->select(DB::raw('"c" as flag'),'title','blogs.id','cover as path','blogs.slug',DB::raw('"lng" as lng'),'dropdown_options.name as tags','blogs.created_at','content',DB::raw('"instructor_id" as instructor_id'),DB::raw('"type" as type'),'blogs.updated_at',DB::raw('"is_free" as is_free'),'category_id', DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'), DB::raw('"plan_type" as plan_type'),DB::raw('"list_price" as list_price'),DB::raw('"final_payable_price" as final_payable_price'),DB::raw('"planId" as planId'))->orderBy('title', 'ASC');

                     $instructure = Instructors::whereRaw('LOWER(`name`) LIKE ?',"%{$search}%")
                                ->join('users','instructors.user_id','=','users.id')
                                ->select(DB::raw('"b" as flag'),'users.name as title','users.id as id','users.profile_picture as path',DB::raw('"slug" as slug'),DB::raw('"lng" as lng'),'instructors.designation as tags','instructors.created_at as created_at',DB::raw('"content" as content'),DB::raw('"instructor_id" as instructor_id'),DB::raw('NULL as type'),'instructors.updated_at as updated_at',DB::raw('"is_free" as is_free'),DB::raw('"category_id" as category_id'), DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'), DB::raw('"plan_type" as plan_type'),DB::raw('"list_price" as list_price'),DB::raw('"final_payable_price" as final_payable_price'),DB::raw('"planId" as planId'))->orderBy('name', 'ASC');

                    $categories = DropdownOption::with('dropdown')
                        ->where('status',true)
                        ->whereRaw('LOWER(`name`) LIKE ?', "%{$search}%")
                        ->select(DB::raw('"d" as flag'),'name as title','id','image as path',DB::raw('"slug" as slug'),DB::raw('"lng" as lng'),'name as tags','created_at',DB::raw('"content" as content'),DB::raw('"instructor_id" as instructor_id'),DB::raw('NULL as type'),'updated_at',DB::raw('"is_free" as is_free'),DB::raw('"category_id" as category_id'), DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'),DB::raw('"plan_type" as plan_type'),DB::raw('"list_price" as list_price'),DB::raw('"final_payable_price" as final_payable_price'),DB::raw('"planId" as planId'))->orderBy('name', 'ASC');
                        // ->withCount('courses');
                        $searchData = $course
                        ->union($blog)
                        ->union($instructure)
                        ->union($categories)
                        ->orderBy('flag','asc')
                        ->get();
                        // dd($searchData);
                        //->paginate($rows_number);
                        $searchData = $searchData->filter(function ($value, $key) {
                            if($value == 'a'){
                                if(count($value->plans) > 0){
                                    return true;
                                }
                            }
                            else return true;
                        });
                        $searchData = cpaginate($searchData, $rows_number);

                    if (view()->exists('front.search.search-list')) {
                        return view('front.search.search-list', compact('searchData'));
                    }

                } catch (\Exception $e) {
                   // dd($e->getMessage());
                   DB::rollback();
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "error";
                    $notification['title'] = "Error";
                    $notification['msg'] = "Something went wrong.";
                    return redirect()->back()->with('notification', $notification);
                }


            // }else{
            //         $notification['type'] = "sweet-alert";
            //         $notification['status'] = "error";
            //         $notification['title'] = "Error";
            //         $notification['msg'] = "Something went wrong.";
            //         return redirect()->back()->with('notification', $notification);
            // }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "error";
        $notification['title'] = "Error";
        $notification['msg'] = "No data search.";
        return redirect()->back()->with('notification', $notification);
    }

    public function razorpayConfig()
    {
        $key = config()->has('settings.razorpay_key') ? config('settings.razorpay_key') : NULL;
        $secret = config()->has('settings.razorpay_secret') ? config('settings.razorpay_secret') : NULL;
        if(config()->has('settings.razorpay_sandbox') && config('settings.razorpay_sandbox') == 1){
            $key = config()->has('settings.razorpay_sandbox_key') ? config('settings.razorpay_sandbox_key') : NULL;
            $secret = config()->has('settings.razorpay_sandbox_secret') ? config('settings.razorpay_sandbox_secret') : NULL;
        }
        $api = new Api($key, $secret);
        return $api;
    }

    public function createPlan(Request $request)
    {
        try {
            $api = $this->razorpayConfig();
            // if(request()->has('edit_plan_id')){
            //     $plan = $api->plan->create(
            //         array('period' => getBillFrequency($request['calendar']),
            //                 'interval' => $request['bill_learner_every'],
            //                 'item' =>
            //                 array('name' => $request['plan_name'],
            //                         'description' => $request['plan_name'],
            //                         'amount' => $request['edit_price'] * 100,
            //                         'currency' => 'INR')
            //             ));
            // }else{
                $plan = $api->plan->create(
                    array('period' => getBillFrequency($request['calendar']),
                            'interval' => $request['bill_learner_every'],
                            'item' =>
                            array('name' => $request['plan_name'],
                                    'description' => $request['plan_name'],
                                    'amount' => $request['price'] * 100,
                                    'currency' => 'INR')
                        ));
                        // dd($plan);
            // }
            // dump($plan);
            // $subscriptionId = $this->makeRecurringPayment($request,$plan->id);
            return (isset($plan->id) && $plan->id != '') ? $plan->id : '';
            //$data=["planId" => $plan->id];
            //,"subscriptionId" => $subscriptionId
        } catch (\Exception $e) {
            //dd($e);
            return '';
        }
    }

    public function createSubscription(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'plan_id' => 'required'
        ]);

        if(!isset($request['plan_id'])){
            return response()->json([
                'success' => false,
                'data' => 'server-error',
                'errors' => 'planId not found'
            ], 400);
        }

            try{
                $api = $this->razorpayConfig();
                $subscription =  $api->subscription->create(
                    array('plan_id' => $request['plan_id'],
                            'customer_notify' => 1,
                            'quantity'=>1,
                            'total_count' => 1,
                            'notes'=> array('key1'=> 'Starting Recuring payment'))
                );
                // Save the subscription ID in your database
                return response()->json([
                    'success' => true,
                    'subscription_id' => $subscription['id'],
                    'message' => 'Subscription added successfully'
                ], 200);
            }
            catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'data' => 'server-error',
                    'errors' => "Plan is not Exists..",
                ], 200);
            }

    }

    public function cancelSubscription($subscription_id)
    {
        try{
            $api = $this->razorpayConfig();

            $options = ['subscriptionId' => $subscription_id, 'cancel_at_cycle_end' => 1 ];
            $plan = $api->subscription->fetch($subscription_id)->cancel($options);
            dd($plan);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'data' => 'server-error',
                'errors' => "Plan is not Exists..",
            ], 200);
        }

        // $ch = curl_init('https://api.razorpay.com/v1/subscriptions/'.$subscription_id.'/cancel');
        //     $key = config('settings.razorpay_key');
        //     $secret = config('settings.razorpay_secret');
        //     $headers = [
        //         "Content-Type"=>"application/json",
        //         "Accept"=>"application/json",
        //     ];
        //     $data = json_encode([
        //         "cancel_at_cycle_end" => 1
        //     ]);

        //     curl_setopt($ch, CURLOPT_USERPWD, "$key:$secret");
        //     curl_setopt($ch, CURLOPT_POST, true);
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_exec($ch);
    }

}
