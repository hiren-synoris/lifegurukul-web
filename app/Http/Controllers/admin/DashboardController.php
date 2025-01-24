<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Learner;
use App\Models\UserCourse;
use App\Models\DeviceToken;
use App\Models\Instructors;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function index1()
    {
        //   dd("true");

        // $d = DeviceToken::groupBy("device_token")->where("device_type",1)->get();
        // dd($d);
        $pg_header = "Dashboard";

        $user = Auth::user();

        // Check if it's the user's first login
        if ($user->hasRole(User::INSTRUCTOR) && $user->first_login) {
            $firstLogin = true;
            $user->first_login = false;
            $user->save();
        } else {
            $firstLogin = false;
        }

        $query = UserCourse::leftJoin("courses", "courses.id", "=", "user_courses.course_id")
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('courses.instructor_id', auth()->id());
            })
            ->groupBy('learner_id');

        $totalPaidUser = $query->clone()->paidPlan()->get()->count();
        $totalFreeUser = $query->clone()->freePlan()->get()->count();

        $totalLearner = Learner::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->count();
        // dd($totalLearner);
        $totalCourses = Course::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('instructor_id', auth()->id());
            })->where('type', 1)
            ->count();
        // dd($totalCourses);
        $totalPackages = Course::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('instructor_id', auth()->id());
            })->where('type', 2)
            ->count();
        $instructors = Instructors::whereNull('deleted_by')->whereNull('deleted_at')->count();
        // $courseWiseUser = $this->instructureWiseSoldCourse();
        $courseWiseUser = $this->instructureWiseSoldCourse();
        // dd($courseWiseUser);
        // $course_sold = UserCourse::select("users.id",
        //                                     "users.name",
        //                                     "users.profile_picture",
        //                                     "users.email",
        //                                     DB::raw("(GROUP_CONCAT(DISTINCT(courses.id) SEPARATOR ',')) as `courses`"))
        //                             ->leftjoin("courses","courses.id","=","user_courses.course_id")
        //                             ->leftjoin("users","users.id","=","courses.instructor_id")
        //                             ->groupBy("courses.instructor_id")
        //                             ->orderBy('courses','DESC')
        //                             ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function($query){
        //                                 $query->where('courses.instructor_id', auth()->id());
        //                             })
        //                             // whereRaw("FIND_IN_SET('all',coursescity)")->get();
        //                             ->pluck("courses")->toArray();

        // dd($course_sold);
        $learnerDevice = $this->learnerWiseDevice();



        $data["paid_learner"] = UserCourse::where("expire_at", "!=", "")->distinct()->count();
        $data["free_learner"] = UserCourse::where("expire_at", null)->distinct()->count();
        $data["daily"] = UserCourse::where('created_at', '>=', now()->subDays(1))->distinct()->count();
        $data["complate_payment"] = UserCourse::where('transaction_id', '<>', null )->distinct()->count();

        if (view()->exists('admin.dashboard.dashboard')) {
            return view('admin.dashboard.dashboard', compact('pg_header', 'firstLogin', 'totalPackages', 'totalPaidUser', 'totalFreeUser', 'courseWiseUser', 'learnerDevice', 'totalLearner', 'totalCourses', 'instructors'), $data);
        }abort(404);
    }
    public function index()
    {

        $user = Auth::user();

        if ($user->hasRole(User::INSTRUCTOR) && $user->first_login) {
            $firstLogin = true;
            $user->first_login = false;
            $user->save();
        } else {
            $firstLogin = false;
        }
        $pg_header = "Dashboard";

        $totalLearner = Learner::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->count();

            $totalCourses = Course::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('instructor_id', auth()->id());
            })->where("type",1)->count();
            $totalPackages = Course::whereNull('deleted_by')
            ->whereNull('deleted_at')
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('instructor_id', auth()->id());
            })->where("type",2)->count();

        $instructors = Instructors::whereNull('deleted_by')->whereNull('deleted_at')->count();
        $total = DeviceToken::count();
        // $android = DeviceToken::selectRaw('COUNT(DISTINCT device_token) as count')->where("device_type",1)->first()->count;
        // $ios = DeviceToken::selectRaw('COUNT(DISTINCT device_token) as count')->where("device_type",2)->first()->count;
        // $web = DeviceToken::whereNotNull('device_name')->count();

        // $android_count = [
        //     "device_type"=>"Android",
        //     "android_count"=>$android,
        // ];
        // $ios_count = [
        //     "device_type"=>"IOS",
        //     "ios_count"=>$ios,
        // ];
        // $web_count = [
        //     "device_type"=>"WEB",
        //     "web_count"=>$web,
        // ];


        // $data["complate_payment"] = UserCourse::where('transaction_id', '<>', null )->distinct()->count();

        if (view()->exists('admin.dashboard.dashboard')) {
            // return view('admin.dashboard.dashboard2', compact('pg_header', 'firstLogin', 'totalPackages', 'android_count', 'ios_count','web_count','totalLearner', 'totalCourses', 'instructors','total'), $data);
            return view('admin.dashboard.dashboard2', compact('pg_header', 'firstLogin', 'totalPackages','totalLearner', 'totalCourses', 'instructors','total'));
        }abort(404);
    }

    public function userWiseCourse()
    {
        $pg_header = "User Course List";
        if (view()->exists('admin.dashboard.user_wise_course_list')) {
            return view('admin.dashboard.user_wise_course_list', compact('pg_header'));
        }abort(404);
    }
    public function searchCourseDashboard(Request $request)
    {
        $coures = Course::whereRaw("concat(title) like '%" . $request->term . "%' ")->paginate(50);
        $usersArray = [];
        foreach ($coures as $val) {
            $usersArray[] = array(
                "label" => $val->title,
                "value" => $val->id,
            );
        }
        return response()->json($usersArray);
    }

    public function searchinstructorDashboard(Request $request)
    {
        $instructor = Instructors::whereHas("user",function($q)use($request){
            $q->whereRaw("concat(name) like '%" . $request->term . "%' ");
        })->paginate(50);



        $usersArray = [];
        foreach ($instructor as $val) {
            $usersArray[] = array(
                "label" => @$val->user->name,
                "value" => $val->id,
            );
        }
        return response()->json($usersArray);
    }

    public function get_userWiseCourse()
    {
        $courseWiseUser = Learner::select("learners.id",
            "learners.name",
            "learners.email",
            "learners.mobile",
            "countries.phonecode",
            DB::raw("(GROUP_CONCAT(DISTINCT(courses.id) SEPARATOR ',')) as `courses`"))
            ->leftjoin("user_courses", "user_courses.learner_id", "=", "learners.id")
            ->leftjoin("courses", "courses.id", "=", "user_courses.course_id")
            ->leftjoin("countries", "countries.id", "=", "learners.country_id")
        // ->withCount('user_courses as course_count')
            ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function ($query) {
                $query->where('courses.instructor_id', auth()->id());
            })
            ->groupBy('learners.id')
            ->whereNull('learners.deleted_by')
            ->whereNull('learners.deleted_at')
        // ->orderBy('course_count','DESC')
            ->limit(10)->get();

        $courseWiseUser = $courseWiseUser->map(function ($item, $key) {
            $courseIds = stringToArray($item->courses);
            if (isset($courseIds) && !empty($courseIds)) {
                $courses = Course::whereIn('id', $courseIds)->select('id', 'title', 'slug', 'type')->get();
                if (isset($courses) && !empty($courses)) {
                    $all_course_btn = [];
                    foreach ($courses as $course) {
                        $url = $course->type == 2
                        ? route('course.packages', ['slug' => $course->slug])
                        : route('course.details', ['slug' => $course->slug]);

                        $all_course_btn[$course->id] = '<a class="badge course-tag" href="' . $url . '" target="_blank">' . $course->title . '</a>';
                    }
                    $item['buttonArray'] = $all_course_btn;
                }
            }
            return $item;
        });

        return DataTables::of($courseWiseUser)
            ->addColumn('action', function ($row) {
                $button = "";
                $button .= '<a class="mx-1" target="_blank" title="Landing Page" href="#"><i class="fas fa-eye text-orange"></i></a>';
            })
            ->editColumn('courses', function ($row) {
                $courseList = '';
                if (isset($row->buttonArray) && !empty($row->buttonArray)) {
                    foreach ($row->buttonArray as $value) {
                        $courseList .= $value;
                    }
                }
                return $courseList;
            })
            ->editColumn('course_count', function ($row) {
                $sum = 0;
                if (isset($row->buttonArray) && !empty($row->buttonArray)) {
                    foreach ($row->buttonArray as $value) {
                        $sum = $sum + 1;
                    }
                }
                return $sum;
            })
            ->rawColumns(['courses', "course_count"])
            ->addIndexColumn()
            ->toJson();
    }

    public function instructureWiseSoldCourse()
    {
        // $courseWiseUser = UserCourse::select("users.id",
        //                                     "users.name",
        //                                     "users.profile_picture",
        //                                     "users.email",
        //                                     DB::raw("(GROUP_CONCAT(DISTINCT(courses.id) SEPARATOR ',')) as `courses`"))
        //                             ->leftjoin("courses","courses.id","=","user_courses.course_id")
        //                             ->leftjoin("users","users.id","=","courses.instructor_id")
        //                             ->groupBy("courses.instructor_id")
        //                             ->orderBy('courses','DESC')
        //                             ->when(auth()->user()->hasRole(User::INSTRUCTOR) == true, function($query){
        //                                 $query->where('courses.instructor_id', auth()->id());
        //                             })
        //                             ->limit(10)->get();

        //                             dd($courseWiseUser);
        // $courseWiseUser = $courseWiseUser->filter(function ($value, $key) use($courseWiseUser){
        //     $CourseIds = stringToArray($value->courses);
        //     return (isset($CourseIds) && $CourseIds != null && count($CourseIds) > 0) ? true :false;
        // });

        $courseWiseUser = Instructors::with(["courses" => function ($q) {
            $q->select('instructor_id', \DB::raw("GROUP_CONCAT(id SEPARATOR ', ') as instructor_course_id"))
                ->groupBy('instructor_id');
        }])->
            limit("12")->get();
        $courseWiseUser->each(function ($instru) use ($courseWiseUser) {
            $instru->courses->filter(function ($schedule) use ($instru) {
                $instru->usercourseCount = UserCourse::whereIn("course_id", explode(",", $schedule->instructor_course_id))->count();
            });
        });

        return $courseWiseUser;
    }

    public function learnerWiseDevice()
    {
        $learnerDevice = DB::table('device_tokens')
            ->select('device_tokens.device_type',
                DB::raw('count(device_tokens.id) as device_count')
            )
            ->groupBy('device_type')
            ->get()
            ->toArray();
        // dd();
        // $learnerDevice = Instructors::with("DeviseCourses.userCourseManage")->where("user_id",auth()->user()->id)->first();

        // $android = 0;
        // $ios = 0;
        // $website = 0;
        // $learnerDevice->DeviseCourses->each(function($value)use($learnerDevice,&$android){
        //     $value->userCourseManage->filter(function($val)use($value,$learnerDevice,&$android){
        //         $val->device = DB::table('device_tokens')
        //         ->select('device_tokens.device_type',
        //                 DB::raw('count(device_tokens.id) as device_count')
        //                 )->where("learner_id",$val->learner_id)
        //         ->groupBy('device_type')
        //         ->get();

        //         // dump($val->device);
        //         $val->device->filter(function($v)use(&$android,$learnerDevice){
        //             if($v->device_type==2) {
        //                 $android = $android+$v->device_count;
        //             }
        //             if($v->device_type==1) {
        //                 $sum = $sum+$v->device_count;
        //             }
        //             if($v->device_type==3) {
        //                 $sum = $sum+$v->device_count;
        //             }

        //         });

        //     });
        // });
        // $learnerDevice->device_count = $sum;

        // dd($learnerDevice);
        // dd("");

        return $learnerDevice;
    }

    public function getInstructor(Request $request)
    {

        $courses = Course::
                    where("instructor_id",$request->id)
                    ->pluck("id")->toArray();


        $query =  UserCourse::selectRaw('DATE(created_at) as date,
            SUM(CASE WHEN order_status = "1" THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN order_status = "2" THEN 1 ELSE 0 END) as fail_count,
            SUM(CASE WHEN order_status = "3" THEN 1 ELSE 0 END) as free_count,
            SUM(CASE WHEN order_status = "4" THEN 1 ELSE 0 END) as enrol_by_admin'
        )
        ->groupBy('date');


        if($request->days==7) {
            $start = now()->subDays(8);
            $end = now();
            $query->whereBetween('created_at', [$start, $end]);
        }

        if($request->days==30) {
            $start = now()->subDays(31);
            $end = now();
            $query->whereBetween('created_at', [$start, $end]);
        }

        if($request->start_date_course) {
            $start = $request->start_date_course;
            $end = $request->end_date_course  ;
            $query->whereBetween('created_at', [$start, $end]);
        }

        $instructor_wise_courses = $query->get();

        return response()->json(["instructor_wise_courses"=>$instructor_wise_courses]);



    }

    public function getUserCourse(Request $request)
    {


        $query = UserCourse::where("order_status", "!=", "2")
                    ->where("course_id",$request->id)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date');


        if($request->days==7) {
            $start = now()->subDays(8);
            $end = now();
            $query->whereBetween('created_at', [$start, $end]);
        }

        if($request->days==30) {
            $start = now()->subDays(31);
            $end = now();
            $query->whereBetween('created_at', [$start, $end]);
        }

        if($request->start_date_course) {
            $start = $request->start_date_course;
            $end = $request->end_date_course  ;
            $query->whereBetween('created_at', [$start, $end]);
        }

        $sold_courses = $query->get();


        $course = Chapter::where("course_id",$request->id)->pluck("id")->toArray();

        $user_course_completed = UserCourseProgress::whereIn("chapter_id",$course)
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')->where("is_completed",1)->get();

        return response()->json(["sold_courses"=>$sold_courses,"coures"=>"course","user_course_completed"=>$user_course_completed]);

    }

    public function getCourseSold(Request $request)
    {


        $interval = $request->days;
        switch ($interval) {
            case 7:
                $start = now()->subDays(8);
                $end = now();
                break;
            case 30:
                $start = now()->subDays(31);
                $end = now();
                break;

            default:
            $start = $request->start_date;
            $end = $request->end_date;
            break;

        }



        $userCoursesQuery = UserCourse::whereBetween('created_at', [$start, $end]);

        // Free and Paid Courses query
        $freePaidCourses = $userCoursesQuery->clone()
            ->selectRaw('DATE(created_at) as date,
                        SUM(CASE WHEN order_status = "1" THEN 1 ELSE 0 END) as paid_count,
                        SUM(CASE WHEN order_status = "2" THEN 1 ELSE 0 END) as fail_count,
                        SUM(CASE WHEN order_status = "3" THEN 1 ELSE 0 END) as free_count,
                        SUM(CASE WHEN order_status = "4" THEN 1 ELSE 0 END) as enrol_by_admin')
            ->groupBy('date')
            ->get();

        // Unique Enrollments query
        $newEnrol = $userCoursesQuery->clone()
            ->where('order_status', '!=', '2')
            ->whereNull('transaction_id')
            ->get()
            ->unique('learner_id', 'course_id', "plan_id");

        // Course Counts query
        $courseCounts = $userCoursesQuery->clone()
            ->with('course:id,title')
            ->select('course_id', DB::raw('count(*) as count'))
            ->groupBy('course_id')
            ->orderBy('count', 'DESC')
            ->get();

        // Most Frequent Course
        $mostFrequentCourse = $courseCounts->first();




        // $freePaidcourses = UserCourse::whereBetween('created_at', [$start, $end])
        // ->selectRaw('DATE(created_at) as date,
        //              SUM(CASE WHEN order_status = "1" THEN 1 ELSE 0 END) as paid_count,
        //              SUM(CASE WHEN order_status = "2" THEN 1 ELSE 0 END) as fail_count,
        //              SUM(CASE WHEN order_status = "3" THEN 1 ELSE 0 END) as free_count,
        //              SUM(CASE WHEN order_status = "4" THEN 1 ELSE 0 END) as enrol_by_admin'

        //              )
        // ->groupBy('date')
        // ->get();


        $new_learner = Learner::whereBetween('created_at', [$start, $end])->count();

        // $new_enrol =UserCourse::where("order_status", "!=", "2")->whereNull("transaction_id")
        // ->whereBetween('created_at', [$start, $end])
        // ->get()->unique('learner_id', 'course_id', "plan_id");


        // $courseCounts = UserCourse::with("course:id,title")->whereBetween('created_at', [$start, $end])->select('course_id', DB::raw('count(*) as count'))
        // ->groupBy('course_id')
        // ->orderBy('count', 'DESC')
        // ->get();

        // $mostFrequentCourse = $courseCounts->first();

        $user_courses = UserCourseProgress:: whereBetween('created_at', [$start, $end])
        ->select('chapter_id', DB::raw('count(*) as count'))
        ->groupBy('chapter_id')->orderBy('count', 'DESC')
        ->get();

        $chapter = $user_courses->first();


        $mostFrequentView = Chapter::with("course:id,title")->where("id",@$chapter->chapter_id)->first();


        return response()->json([
            "freePaidcourses"=>$freePaidCourses,
            "courses"=>"Paid and Free courses",
            "new_enrol"=>$newEnrol->count(),
            "new_learner"=>$new_learner,
            "mostFrequentBuy"=>@$mostFrequentCourse->course->title?? "",
            "mostFrequentView"=>$mostFrequentView->course->title??"",

        ]);

    }

    public function getPaymentStatus(Request $request) {


        switch ($request->payment_status) {
            case 1:
                $status = 1;
                break;
            case 2:
                $status = 2;
                break;
            case 3:
                $status = 3;
                break;
            case 4:
                $status = 4;
                break;
            default:
                return response()->json(['error' => 'someting went worng'], 400);
        }

        $count_payment_status = UserCourse::where('order_status',$status)->count();

        return response()->json(
            $count_payment_status
        );
    }
    public function getPercentageCount(Request $request) {


        $chapter = Chapter::selectRaw('COUNT(*) as total_count, GROUP_CONCAT(id) as ids')
        ->where('course_id', $request->course_id)
        ->where('parent_id', '!=', 0)
        ->whereNotIn('asset_type', [4, 7])
        ->groupBy('course_id')
        ->first();

        $percentage = $request->percentage_count;

        $requiredCompletedChapters = ceil(($percentage / 100) * @$chapter->total_count);


        $userCourseCompleted = UserCourseProgress::selectRaw('COUNT(*) as completed_count, GROUP_CONCAT(learner_id) as learner_ids')
        ->whereIn('chapter_id', explode(',', @$chapter->ids))
        ->where('is_completed', 1)
        ->first();


        $learnerIds = [];
        if ($userCourseCompleted && $userCourseCompleted->completed_count >= $requiredCompletedChapters) {
            $learnerIds = array_slice(explode(',', $userCourseCompleted->learner_ids), 0, $requiredCompletedChapters);
        }

        $learner_count = Learner::whereIn("id",$learnerIds)->count();

        return response()->json(
            $learner_count,
        );
    }

}
