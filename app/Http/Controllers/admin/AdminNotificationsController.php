<?php

namespace App\Http\Controllers\admin;

use DataTables;
use Carbon\Carbon;
use App\Models\User;
use App\Helper\Helper;
use App\Models\Cities;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\States;
use App\Models\Learner;
use App\Models\Campaign;
use App\Models\Countries;
use Illuminate\Http\File;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\Instructors;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\DropdownOption;
use App\Models\CampaignTemplate;
use App\Jobs\WhatsAppNotification;
use Illuminate\Support\Facades\DB;
use App\Jobs\ManualNotificationJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\ManualNotificationHistory;
use Illuminate\Support\Facades\Validator;

class AdminNotificationsController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard()->user();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!Auth::guard()->user()->can('browse_browse_admin_notifications')) {
            abort(403);
        }

        $pg_header = 'Manual Notification';

        $countries = Countries::select('id', 'name')->distinct()->get();
        $states = States::select('id', 'name')->distinct()->get();
        $cities = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $courses = Course::select('id', 'title', "slug")->distinct()->get();
        $instructor = Instructors::select('id', 'user_id')->with("user")->get();
        $occupations = DropdownOption::getDropdownCategories('occupation')->get();
        $marital_status = DropdownOption::getDropdownCategories('marital-status')->get();
        $educations = DropdownOption::getDropdownCategories('education')->get();
        $your_interests = DropdownOption::getDropdownCategories('your-interests')->get();


        if (view()->exists('admin.admin_notifications.list')) {
            return view('admin.admin_notifications.list', compact('pg_header', 'countries', 'states', 'cities', 'courses', 'instructor', 'occupations', 'marital_status', 'educations', 'your_interests'));
        }
        abort(404);
    }
    public function whatsapp()
    {

        if (!Auth::guard()->user()->can('browse_browse_admin_notifications')) {
            abort(403);
        }

        $pg_header = 'Whatsapp Notifications';

        $countries = Countries::select('id', 'name')->distinct()->get();
        $states = States::select('id', 'name')->distinct()->get();
        $cities = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $courses = Course::select('id', 'title', "slug")->distinct()->get();
        $instructor = Instructors::select('id', 'user_id')->with("user")->get();
        $occupations = DropdownOption::getDropdownCategories('occupation')->get();
        $marital_status = DropdownOption::getDropdownCategories('marital-status')->get();
        $educations = DropdownOption::getDropdownCategories('education')->get();
        $your_interests = DropdownOption::getDropdownCategories('your-interests')->get();

        $data = Campaign::get();


        if (view()->exists('admin.admin_notifications.list')) {
            return view('admin.admin_notifications.whatsapp_notification', compact('pg_header', 'countries', 'states', 'cities', 'courses', 'instructor', 'occupations', 'marital_status', 'educations', 'your_interests', "data"));
        }
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    public function fetchCampaigns()
    {

        $data = [
            "apiKey" => 0,
            "limit" => 0,
            "campaignType" => "API",
        ];

        $url = "https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/campaigns";

        // Use Laravel's HTTP client to send the request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            "X-AiSensy-Project-API-Pwd" => "94f5002d280336c191a48",
        ])->post($url, $data);

        $data["data"]  = json_decode($response);


        Campaign::truncate();
        CampaignTemplate::truncate();
        foreach ($data["data"]->campaigns as $val) {
            $campaign = Campaign::create([
                "name" => $val->name,
                "campaign_id" => $val->id,
            ]);
            CampaignTemplate::create([
                "campaign_id" => $val->id,
                "assistant_name" => $val->message_payload->template->assistant_name,
                "text" => $val->message_payload->template->text,
                "total_parameters" => $val->message_payload->template->total_parameters,
                "project_id" => $val->message_payload->template->project_id,
                "type" => $val->message_payload->template->type,
            ]);
        }

        $data["data"] = Campaign::get();

        $render = view("admin.admin_notifications.fetch_campaingns", $data)->render();

        return response()->json($render);
    }
    public function getTemplate(Request $request)
    {
        $camp_id = $request->camp_id;
        // dd($camp_id);
        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'X-AiSensy-Project-API-Pwd' => '94f5002d280336c191a48',
        // ])->timeout(30)->get("https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/campaign/api/$camp_id");
        // if ($response->failed()) {
        //     dd($response->body());
        // }

        // $temp = json_decode($response);

        $temp = CampaignTemplate::where("campaign_id", $camp_id)->first();


        // dd($temp);

        $current_date = date('Y-m-d');
        $courses = Course::select('id', 'title', 'slug')
            ->distinct()
            ->orderBy('title', 'asc')
            ->get();

        $coupons = Coupon::where('expiry_date', '>=', $current_date)
            ->where('status', 1)->orderBy('id', 'desc')
            ->get();


        $data = view("admin.admin_notifications.manu_list", compact('temp', "courses", "coupons"))->render();

        return response()->json(["template" => $temp, "data" => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd($request);
        $validator = Validator::make($request->all(), [
            'title' => 'required|filled',
            'content' => 'required|filled',
            'learner_ids' => 'required',
            'platform' => 'required|array|filled',
            'course' => 'required',
            // 'external_link' => 'required',
            'image' => 'image|mimes:pngm,jpeg,jpg,svg',
        ]);

        if ($validator->fails()) {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong!";
        }
        // dd($request->image);
        $image_name = "";
        if ($request->image != "undefined") {
            $image_name = $request->image->getClientOriginalName();
            $path = Storage::disk("public")->putFileAs('notification', new File($request->image), $image_name);
        }

        $my_filter = [
            'course' => $request->course != "" ? "course($request->course_name)" : '',
            'city' => $request->city != "" ? "city ($request->city_name)" : '',
            'state' => $request->state != "" ? "state ($request->state_name)" : '',
            'country' => $request->country != "" ? "country ($request->country_name)" : '',
            'email' => $request->email != "" ? "email ($request->email)" : '',
            'mobile' => $request->mobile != "" ? "mobile ($request->mobile)" : '',
            'instructor' => $request->instructor != "" ? "instructor ($request->instructor_name)" : '',
            'occupation' => $request->occupation != "" ? "occupation ($request->occupation_name)" : '',
            'marital_status' => $request->marital_status != "" ? "marital_status ($request->marital_status_name)" : '',
            'education' => $request->education != "" ? "education ($request->education_name)" : '',
            'your_interests' => $request->your_interests != "" && $request->your_interests != "null" ? "your_interests ($request->your_interests_name)" : '',
        ];
        $commaSeparatedValues = '';
        foreach ($my_filter as $key => $value) {
            if ($value != '') {
                if ($commaSeparatedValues !== '') {
                    $commaSeparatedValues .= ', ';
                }
                $commaSeparatedValues .= $value;
            }
        }

        // dd($request->platform);

        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $results = ManualNotificationHistory::where("created_by", auth()->user()->id)->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as month_count'))
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->get();

        $id = explode(",", $request->id);
        // dd($learners);
        $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile');

        if ($request->has('id') && !empty($request->id)) {
            // dd($request->id);
            $query->whereIn("id", $id);
        }
        if ($request->has('signup_date') != "undefined") {
            if ($request->has('signup_date') && !empty($request->signup_date)) {
                $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d') . "%");
            }
        }


        if ($request->has('course') && !empty($request->course) && $request->course != "null") {
            // $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
            // $query->whereIn('uc.course_id', $request->course);
            $query->whereHas("userCourses",function($query)use($request){
                $query->whereIn('course_id', explode(",", $request->course));
            });
        }
        if ($request->has('instructor') && !empty($request->instructor)) {

            $query->whereHas("user_courses.course", function ($q) use ($request) {
                $q->where('courses.instructor_id', $request->instructor);
            });
        }
        if ($request->has('email') && !empty($request->email)) {

            $query->where('learners.email', 'like', "%" . $request->email . "%");
        }

        if ($request->has('mobile') && !empty($request->mobile)) {
            $query->where('learners.mobile', 'like', "%" . $request->mobile . "%");
        }
        if ($request->has('city') && !empty($request->city)) {
            $query->where('learners.city_id', $request->city);
        }
        if ($request->has('state') && !empty($request->state)) {
            $query->where('learners.state_id', $request->state);
        }
        if ($request->has('country') && !empty($request->country)) {
            $query->where('learners.country_id', $request->country);
        }
        if ($request->has('occupation') && !empty($request->occupation)) {
            $query->where('learners.occupation', 'like', $request->occupation);
        }
        if ($request->has('marital_status') && !empty($request->marital_status)) {
            $query->where('learners.marital_status', 'like', $request->marital_status);
        }
        if ($request->has('education') && !empty($request->education)) {
            $query->where('learners.education', 'like', $request->education);
        }
        $interest = $request->your_interests;
        $interests = [];
        if (!is_array($interest)) {
            $interests[] = $interest;
        } else {
            $interests = $interest;
        }
        if ($request->has('your_interests') && !empty($request->your_interests) && $request->your_interests != 'null') {
            $query->where(function ($q) use ($interests) {
                foreach ($interests as $interest) {
                    $q->orWhere('learners.your_interests', 'like', '%' . $interest . '%');
                }
            });
        }

        $query->whereNull('learners.deleted_at');
        if ($request->order == null) {
            $query->orderBy('id', 'desc');
        }
        $learners = $query->get();
        $notification_data = [
            "admin_id" => auth()->user()->id,
            "type" => 2,
            "title" => $request->title,
            "description" => $request->content,
            "created_by" => Auth::user()->roles->pluck('name')[0] == "instructor" ? auth()->user()->id : "",
            "filters" => $commaSeparatedValues,
            "image" => $image_name,
            "Platforms" => implode(",", $request->platform),
            "target" => $request->slug != null ? $request->slug : ($request->external_link != null ? $request->external_link : 'Home'),
        ];

        $requestDataWithoutImage = $request->except('image');

        if ($results->count() > 0) {
            $is_notify_limit = Instructors::where("user_id", auth()->user()->id)->first("is_notify_limit");
            if ($results[0]->month_count < $is_notify_limit->is_notify_limit) {
                $notification_manual = ManualNotificationHistory::create($notification_data);
                ManualNotificationJob::dispatch($requestDataWithoutImage, $learners, $notification_manual->id, $image_name);
                return response()->json(["status" => "Notification has been sent successfully"]);
            } else {
                return response()->json(["status" => "Your monthly notification limit has been reached"]);
            }
        } else {
            $notification_manual = ManualNotificationHistory::create($notification_data);
            ManualNotificationJob::dispatch($requestDataWithoutImage, $learners, $notification_manual->id, $image_name);
            return response()->json(["status" => "Notification has been sent successfully"]);
        }
        // $scope = [];
        // $params = [];
        // if(in_array("1",$request->platform)){
        //     $scope[]="email";

        // }
        // if(in_array("2",$request->platform)){
        //     $scope[]="web";
        // }
        // if(in_array("3",$request->platform)){
        //     $scope[]="push";
        // }

        // $subject=$request->title;
        // $content=$request->content;
        // $course_id = Course::where("slug",$request->slug)->first()->id;

        // foreach($learners as $value){
        // $learner = Learner::withTrashed()->where('id',$value)->select('id','email')->first();
        // $params['email']['to']=$value->email;
        // $params['email']['learnerId'] = $value->id;
        // $params['common']=[
        //     'learner_id' => $value->id,
        //     'type'=> 2,
        //     "slug"=>$request->slug,
        //     "redirect_type"=>$request->type,
        //     "course_id"=>$course_id
        // ];
        // $params['web']['to'] = [$value->id];
        // $params['push']['to'] = $value->id;

        // ManualNotificationJob::dispatch($params,'manual_notification', $scope, $subject,$content);

        //->onQueue('high');
        // $notification['type'] = "sweet-alert";
        // $notification['status'] = "success";
        // $notification['title'] = "Success";
        // $notification['msg'] = "Notified successfully";

        //    }
        // return redirect()->back()->with('notification', $notification);
        // return response()->json("send successfully");

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function sendWpNotifications(Request $request)
    {

        $temp_id = $request->temp_id;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/wa_template/$temp_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "X-AiSensy-Project-API-Pwd: 94f5002d280336c191a48",
            ],
        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            dd($err);
        } else {
            $data["template"] = json_decode($response);
        }

        return response()->json($data["template"]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function SendWpMessage(Request $request)
    {


        $my_filter = [
            'course' => $request->course != "" ? "course($request->course_name)" : '',
            'city' => $request->city != "" ? "city ($request->city_name)" : '',
            'state' => $request->state != "" ? "state ($request->state_name)" : '',
            'country' => $request->country != "" ? "country ($request->country_name)" : '',
            'email' => $request->email != "" ? "email ($request->email)" : '',
            'mobile' => $request->mobile != "" ? "mobile ($request->mobile)" : '',
            'instructor' => $request->instructor != "" ? "instructor ($request->instructor_name)" : '',
            'occupation' => $request->occupation != "" ? "occupation ($request->occupation_name)" : '',
            'marital_status' => $request->marital_status != "" ? "marital_status ($request->marital_status_name)" : '',
            'education' => $request->education != "" ? "education ($request->education_name)" : '',
            'your_interests' => $request->your_interests != "" && $request->your_interests != "null" ? "your_interests ($request->your_interests_name)" : '',
        ];



        $commaSeparatedValues = '';
        foreach ($my_filter as $key => $value) {
            if ($value != '') {
                if ($commaSeparatedValues !== '') {
                    $commaSeparatedValues .= ', ';
                }
                $commaSeparatedValues .= $value;
            }
        }

        $notification_data = [
            "admin_id" => auth()->user()->id,
            "title" => $request->campaigns_name,
            "description" => $request->content,
            "created_by" => Auth::user()->roles->pluck('name')[0] == "instructor" ? auth()->user()->id : "",
            "filters" => $commaSeparatedValues,
            "type" => 1
        ];


        $manual_notify_id = "";
        $media_var = "";
        // foreach ($request->campaigns as $key => $campaign) {
        //     if ($request->hasFile("media_var.$key")) {
        //         $file = $request->file("media_var.$key");
        //         $image_name = $file->getClientOriginalName();
        //         Storage::disk("public")->putFileAs('whatsapp_docs', new File($file), $image_name);
        //         $media_var[] = $image_name;
        //     }
        // }


        if ($request->file("media_var")) {

            $file = $request->file("media_var");
            $image_name = $file->getClientOriginalName();
            Storage::disk("public")->putFileAs('whatsapp_docs', new File($file), $image_name);
            $media_var = $image_name;
        }
        $query = "";

        if ($request->days) {

            $targetDate = Carbon::now()->addDays($request->days);
            $learners = UserCourse::select("id", "learner_id", "mobile", "email", "expire_at")->with("learner:id,name")->whereDate('expire_at', '<', $targetDate->toDateString())->where("course_id", $request->course_wp)
            ->get()->unique('learner_id', 'course_id');
        } else {


            $manual_notify_id = ManualNotificationHistory::create($notification_data);
            $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile',"learners.country_id")->with("country");

            if ($request->has('id') && !empty($request->id)) {
                // dd($request->id);
                $query->whereIn("id", $id);
            }
            if ($request->has('signup_date') != "undefined") {
                if ($request->has('signup_date') && !empty($request->signup_date)) {
                    $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d') . "%");
                }
            }


            // dd($request->course);
            if ($request->has('course') && !empty($request->course) && $request->course != "null") {
                // dd("dsddsdsds");
                // $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
                $query->whereHas("userCourses",function($query)use($request){
                    $query->whereIn('course_id', explode(",", $request->course));
                });
            }
            if ($request->has('instructor') && !empty($request->instructor)) {

                $query->whereHas("user_courses.course", function ($q) use ($request) {
                    $q->where('courses.instructor_id', $request->instructor);
                });
            }
            if ($request->has('email') && !empty($request->email)) {

                $query->where('learners.email', 'like', "%" . $request->email . "%");
            }

            if ($request->has('mobile') && !empty($request->mobile)) {

                $query->where('learners.mobile', 'like', "%" . $request->mobile . "%");
            }
            if ($request->has('city') && !empty($request->city)) {
                $query->where('learners.city_id', $request->city);
            }
            if ($request->has('state') && !empty($request->state)) {
                $query->where('learners.state_id', $request->state);
            }

            if ($request->has('country') && !empty($request->country)) {
                $query->where('learners.country_id', $request->country);
            }
            if ($request->has('occupation') && !empty($request->occupation)) {
                $query->where('learners.occupation', 'like', $request->occupation);
            }
            if ($request->has('marital_status') && !empty($request->marital_status)) {
                $query->where('learners.marital_status', 'like', $request->marital_status);
            }
            if ($request->has('education') && !empty($request->education)) {
                $query->where('learners.education', 'like', $request->education);
            }

            $interest = $request->your_interests;
            $interests = [];
            if (!is_array($interest)) {
                $interests[] = $interest;
            } else {
                $interests = $interest;
            }



            if ($request->has('your_interests') && !empty($request->your_interests) && $request->your_interests != 'null') {
                $query->where(function ($q) use ($interests) {
                    foreach ($interests as $interest) {
                        $q->orWhere('learners.your_interests', 'like', '%' . $interest . '%');
                    }
                });
            }

            $query->whereNull('learners.deleted_at');
            if ($request->order == null) {
                $query->orderBy('id', 'desc');
            }

            $learners = $query->get();

        }
        // $leaners_count = Learner::count();
        // if($leaners_count==$learners->count()) {
        //     return response()->json(['status' => "A filter must be applied before continuing with this action"]);
        // } else {

            $requestDataWithoutImage = $request->except('media_var');
            WhatsAppNotification::dispatch($requestDataWithoutImage, $learners, $media_var, $manual_notify_id);
            return response()->json(['status' => "Whatsapp message sent successfully"]);
        // }

    }

    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function getCourse(Request $request)
    {
        $CoursePlan = CoursePlan::where("course_id", $request->course_id)->orderBy("id", "desc")->get(["plan_name", "id"]);

        return response()->json($CoursePlan);
    }

    public function manualNotifyHistory(Request $request)
    {

        if ($request->ajax()) {
            $data = ManualNotificationHistory::where("type", 2)->with("getAdmin:name,id")->orderBy("created_at", "desc");
            if (auth()->user()->getRoleNames()->first() == User::INSTRUCTOR) {
                $data->where("admin_id", auth()->user()->id);
            }
            $data->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = "";
                    $btn .= '<a class="mx-1 notify_learner" data-id="' . $row->id . '" data-toggle="modal" data-target="#notify_learner" title="View Learners" href=""><i class="fas fa-eye text-orange"></i></a>';
                    return $btn;
                })
                ->addColumn('name', function ($row) {
                    return @$row->getAdmin->name;
                })
                ->addColumn('image', function ($row) {
                    return $row->image != null ? '<img src="' . url("storage/notification/", $row->image) . '" width=50>' : '';
                })
                ->addColumn('target', function ($row) {
                    return $row->target;
                })
                ->addColumn('Platforms', function ($row) {
                    $array = explode(",", $row->Platforms);
                    $replacements = [
                        "1" => "Email",
                        "2" => "Web push",
                        "3" => "mobile push",
                    ];
                    $my_plateform = explode(",", $row->Platforms);

                    foreach ($array as &$value) {
                        if (isset($replacements[$value])) {
                            $value = $replacements[$value];
                        }
                    }
                    return $array;
                })
                ->addColumn('filters', function ($row) {
                    return @$row->filters;
                })

                ->addColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                    // return date('d/m/Y', strtotime($row->created_at));

                })
                ->rawColumns(['action', "name", "created_at", "image"])
                ->make(true);
        }
        return view("admin.admin_notifications.manual_notify_history");
    }
    public function wpNotifyHistory(Request $request)
    {

        if ($request->ajax()) {
            $data = ManualNotificationHistory::where("type", 1)->with("getAdmin:name,id")->orderBy("id", "desc");
            if (auth()->user()->getRoleNames()->first() == User::INSTRUCTOR) {
                $data->where("admin_id", auth()->user()->id);
            }
            $data->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = "";
                    $btn .= '<a class="mx-1 notify_learner" data-id="' . $row->id . '" data-toggle="modal" data-target="#notify_learner" title="View Learners" href=""><i class="fas fa-eye text-orange"></i></a>';
                    return $btn;
                })
                ->addColumn('name', function ($row) {
                    return @$row->getAdmin->name;
                })
                ->addColumn('image', function ($row) {
                    return $row->image != null ? '<img src="' . url("storage/notification/", $row->image) . '" width=50>' : '';
                })
                ->addColumn('target', function ($row) {
                    return $row->target;
                })
                ->addColumn('Platforms', function ($row) {
                    $array = explode(",", $row->Platforms);
                    $replacements = [
                        "1" => "Email",
                        "2" => "Web push",
                        "3" => "mobile push",
                    ];
                    $my_plateform = explode(",", $row->Platforms);

                    foreach ($array as &$value) {
                        if (isset($replacements[$value])) {
                            $value = $replacements[$value];
                        }
                    }
                    return $array;
                })
                ->addColumn('filters', function ($row) {
                    return @$row->filters;
                })

                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                    // return date('d/m/Y', strtotime($row->created_at));

                })
                ->rawColumns(['action', "name", "created_at"])
                ->make(true);
        }
        return view("admin.admin_notifications.whatsapp_notification");
    }

    public function notifyLearners(Request $request)
    {

        // dd($request->manual_notify_id);
        if ($request->ajax()) {
            $data = Notification::where("manual_notify_id", $request->manual_notify_id)->with("getLerner:name,mobile,email,id")->get();
            // dd($data);
            return Datatables::of($data)
                ->addIndexColumn()

                ->addColumn('name', function ($row) {
                    return @$row->getLerner->name;
                })
                ->addColumn('mobile', function ($row) {
                    return @$row->getLerner->mobile;
                })
                ->addColumn('email', function ($row) {
                    return @$row->getLerner->email;
                })

                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                    // return date('d/m/Y', strtotime($row->created_at));

                })
                ->rawColumns(['action', "name", "created_at"])
                ->make(true);
        }
    }
}
