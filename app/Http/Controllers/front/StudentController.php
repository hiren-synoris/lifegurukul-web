<?php

namespace App\Http\Controllers\front;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\EarnCoinMail;
use App\Models\Chapter;
use App\Models\Cities;
use App\Models\Course;
use App\Models\DropdownOption;
use App\Models\Learner;
use App\Models\LearnerLog;
use App\Models\NewsLetter;
use App\Models\Notification;
use App\Models\Support;
use App\Models\UserCoin;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
// use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Facades\Agent;

class StudentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth:learner');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // return view('course-list');
    }

    public function getCity(Request $request)
    {
        $data = Cities::where("state_id", $request->state_id)->get();
        return response()->json($data);
    }

    public function learnerLog(Request $request)
    {

        $data["logs"] = LearnerLog::with("getCourse:id,title")->orderBy("id", "desc")->where("learner_id", auth()->user()->id)->paginate(10);

        if (view()->exists('front/student/dashboard/logs')) {
            return view('front/student/dashboard/logs', $data);
        }
    }

    public function myWallet(Request $request)
    {

        $data["wallet"] = UserCoin::with("getCourse:id,title,type")->with("getChapter:id,title")->where("learner_id", auth()->guard("learner")->user()->id)->orderBy("id", "desc")->paginate(10);

        $user_count = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
            ->where(function ($query) {
                $query->where("type", "!=", "2");
                $query->Where("type", "!=", "4");
            })->sum("coins");

        $minus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
            ->where(function ($query) {
                $query->where("type", "=", 2)
                    ->orWhere("type", "=", 4);
            })->sum("coins");

        $total = $user_count - $minus;

        if (view()->exists('front/student/dashboard/my_wallet')) {
            return view('front/student/dashboard/my_wallet', $data, compact("total"));
        }
    }

    /**
     * Show the student deposite dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function student_dashboard(Request $request)
    {
        if (Course::sum('order') <= 0) {
            $orderby = "courses.id";
            $dir = "DESC";
        } else {
            $orderby = DB::raw('ISNULL(courses.order), courses.order');
            $dir = "ASC";
        }
        $purchasedCourses = UserCourse::where('learner_id', Auth::guard('learner')->id())->distinct('course_id')
            ->groupBy('learner_id')
            ->count();
        $totalTransaction = UserCourse::where('learner_id', Auth::guard('learner')->id())
            ->whereNotNull('transaction_id')
            ->groupBy('learner_id')
            ->count();
        $totalSubscription = UserCourse::where('learner_id', Auth::guard('learner')->id())
            ->with(['coursePlan' => function ($query) {
                return $query->where('plan_type', 2);
            }, 'course' => function ($query) {
                //return $query->where('status', 1);
            }])
            ->whereNotNull('transaction_id')
            ->whereNotNull('subscription_id')
            ->whereNotNull('user_courses.is_subscription')
            ->groupBy('learner_id')
            ->count();

        // dd($totalSubscription);

        // $courses = Course::distinct()->select('title', 'slug', 'instructor_id', 'instructor_name', 'description', 'tags', 'banner_image', 'intro_video', 'tagline', 'how_to_use', 'lng', 'image', 'meta_title', 'meta_keywords', 'meta_description', 'type', 'status', 'course_platform', 'order', 'is_featured', 'is_free', 'courses.id', 'user_courses.id as user_courses_id')
        //     ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
        //     ->withCount(['chapters' => function ($query) {
        //         $query->where('parent_id', 0);
        //     }])
        //     ->withCount(['packages'])
        //     ->with(['instructor' => function ($query) {
        //         $query->select('name', 'email', 'id', 'profile_picture');
        //     }, 'categories'])
        //     ->with('instructor.instructure')
        //     ->with(['wishlists' => function ($query) {
        //         $query->select('course_id', 'id', 'learner_id');
        //     }])
        //     ->with(['plans'])
        //     ->when(request()->has('searchTxt') && !empty($request->query('searchTxt')), function ($query) use ($request) {
        //         $query->where('title', 'LIKE', "%" . $request->query('searchTxt') . "%");
        //     })
        //     ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
        //     ->where('courses.status', '1')
        //     ->where('user_courses.learner_id', Auth::guard('learner')->id())
        //     ->whereNull('courses.deleted_at')
        //     ->groupBy('courses.id')
        //     ->orderBy($orderby, $dir)->paginate(10);

        if (view()->exists('front/student/dashboard/student_dashboard')) {
            return view('front/student/dashboard/student_dashboard', compact('purchasedCourses', 'totalTransaction', 'totalSubscription'));
        }
        abort(404);
    }

    /**
     * Show the Student Course.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function my_course(Request $request)
    {
        // dd(\Session::get('deviceId'));
        // dd("");
        // $courseId = 29;
        // $chapterIds=Chapter::where("course_id",$courseId)->selectRaw('group_concat(id) as ids')->pluck('ids')->first();
        // if(!empty($chapterIds)) {
        //     $chapterIds = explode(",",$chapterIds);
        //     $totalDuration = ChapterInfo::selectRaw('sum(duration) as durations')->whereIn('chapter_id',$chapterIds);
        //     $delete = UserCourseProgress::where('learner_id',$learnerId)->whereIn('chapter_id',$chapterIds)->delete();
        // }
        // dd($courseIds);
        $deviceType = Course::COURSE_WEBSITE;

        $courses =

        Course::distinct()->select('title', 'slug', 'instructor_id', 'instructor_name', 'description', 'tags', 'banner_image', 'intro_video', 'tagline', 'how_to_use', 'lng', 'image', 'meta_title', 'meta_keywords', 'meta_description', 'type', 'status', 'course_platform', 'order', 'is_featured', 'is_free', 'courses.id', 'user_courses.id as user_courses_id', 'user_courses.expire_at')
        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE]).
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0);
                },
                'packages',
                'rating_reviews as total_review',
            ])
            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->with(['chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id')->where('asset_type', '!=', 4)->where('asset_type', '!=', 7);
            }])
        // ->wherehas("chapters.chapterInfo")

            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                }, 'categories',
                'instructor.instructure',
                'wishlists' => function ($query) {
                    $query->select('course_id', 'id', 'learner_id');
                },
                'plans' => function ($query) {
                    //  $query->where('status',1);
                },
                // 'userCourse',
            ])
            ->with([
                'userCourse' => function ($query) {
                    $query->where('learner_id', Auth::guard('learner')->id());
                },
            ])
            ->when(request()->has('searchTxt') && !empty($request->query('searchTxt')), function ($query) use ($request) {
                $query->where('title', 'LIKE', "%" . $request->query('searchTxt') . "%");
            })
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
            ->where('user_courses.learner_id', Auth::guard('learner')->id())
            ->where('user_courses.order_status', "!=", 2)
            ->whereNull('courses.deleted_at')
            ->groupBy(['courses.id', 'user_courses.learner_id'])
            ->orderByDesc('user_courses.id')
            ->paginate(12);

        $courses->each(function ($value) {
            // if($value->id == 40) {
            //     dd($value);
            // }
            // $value->chapter_data = Chapter::getCourseChapters($value->id)->get();
            $sum = 0;
            $value->getChapters->filter(function ($v) use ($value, &$sum) {
                $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')
                    ->where('asset_type', '!=', 4)
                    ->where('asset_type', '!=', 7)
                    ->where('parent_id', $v->id)->count();
                $sum += $v->chpter_count;
            });
            //  $value->getChapters->filter(function($val) use(&$sum) {
            //     return $sum += $val->chpter_count;
            // });
            $value->tot_chapterCount = $sum;
        });

        $collection = $courses->getCollection();
        // $collection = $collection->filter(function ($value, $key) {
        //     return $value->plans->count() > 0;
        // });
        // dd($collection->toArray());
        // course wise Total duration of chapter
        $collection1 = $collection->map(function ($value, $key) {
            if (!empty($value->chapters->first()) && !empty($value->chapters->first())) {
                $chapterIds = explode(",", $value->chapters->first()->chapterIds);
                $totalDuration = count($chapterIds);

                $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')
                    ->whereIn('chapter_id', $chapterIds)
                    ->where('learner_id', Auth::guard('learner')->id())
                    ->where('is_completed', 1)
                    ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                    ->where('chapters.asset_type', '!=', 4)
                    ->where('chapters.asset_type', '!=', 7)
                    ->pluck('watched_times')
                    ->first();

                // dump( $totalCompletedChapter);
                $value->totalCompletedDuration = round($totalCompletedChapter);
                $value->totalProgress = ($totalCompletedChapter > 0 && $totalDuration > 0)
                ? round(($totalCompletedChapter * 100) / $value->tot_chapterCount)
                : 0;

                return $value;
            }
        });

        $collection1 = $collection->map(function ($value, $key) {
            $value->rr = Course::where('slug', $value->slug)->with(['rating_reviews' => function ($query) {
                $query->where('is_approve', true)->where('learner_id', Auth::guard('learner')->user()->id);
            }])->first();
            return $value;
        });

        $courses->setCollection($collection);

        if (view()->exists('front/student/dashboard/my_course')) {
            return view('front/student/dashboard/my_course', compact('courses'));
        }
        abort(404);
    }
    /**
     * Show the Student Course.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function my_package_course($userCourseId = null, Request $request)
    {
        // $courses = CoursePackage::distinct()->select('title', 'slug', 'instructor_id', 'instructor_name', 'description', 'tags', 'banner_image', 'intro_video', 'tagline', 'how_to_use', 'lng', 'image', 'meta_title', 'meta_keywords', 'meta_description', 'type', 'status', 'course_platform', 'order', 'is_featured', 'is_free','courses.id','user_courses.id as user_courses_id','user_courses.expire_at')
        //                    ->whereIn('course_platform',[Course::COURSE_ALL,Course::COURSE_WEBSITE])
        //                     ->when(request()->has('searchTxt') && !empty($request->query('searchTxt')), function($query) use ($request){
        //                         $query->where('title', 'LIKE',"%".$request->query('searchTxt')."%");
        //                     })
        //                     ->leftJoin('user_courses','course_packages.package_id','=','user_courses.course_id')
        //                     ->leftJoin('courses','course_packages.course_id','=','courses.id')
        //                    // ->where('courses.status','1')
        //                     ->where('user_courses.learner_id',Auth::guard('learner')->id())
        //                     ->where('user_courses.id',$userCourseId)
        //                     ->whereNull('courses.deleted_at')
        //                     ->groupBy('course_packages.course_id')
        //                     ->orderBy('user_courses.created_at','DESC')->paginate(10);

        //     $collection = $courses->getCollection();
        //     $collection1= $collection->map(function($value, $key){
        //         $value->rr = Course::where('slug',$value->slug)->with(['rating_reviews' => function($query){
        //             $query->where('is_approve', true)->where('learner_id', Auth::guard('learner')->user()->id);
        //         }])->first();
        //         return $value;
        //     });
        //     $courses->setCollection($collection);
        // if(view()->exists('front/student/dashboard/my_package_course')){
        //     return view('front/student/dashboard/my_package_course', compact('courses'));
        // }abort(404);
        // dd($userCourseId);
        $deviceType = Course::COURSE_WEBSITE;

        $userCourse = UserCourse::with([
            "newCourse:title,id",
            //"course_packages" table relationship below
            'coursePackages:id,package_id,course_id' => [
                //"courses" table relationship below
                'original_course' => function ($query) use ($request, $deviceType) {
                    $query->select('id', 'title', 'slug', 'instructor_id', 'instructor_name', 'description', 'tags', 'banner_image', 'intro_video', 'tagline', 'how_to_use', 'lng', 'image', 'meta_title', 'meta_keywords', 'meta_description', 'type', 'status', 'course_platform', 'order', 'is_featured', 'is_free')
                    // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
                        ->whereRaw("find_in_set($deviceType , course_platform)")
                        ->whereNull('deleted_at');

                    $query->when(request()->has('searchTxt') && !empty($request->query('searchTxt')), function ($query) use ($request) {
                        $query->where('title', 'LIKE', "%" . $request->query('searchTxt') . "%");
                    });

                    // 'duration' column sum (time based)
                    // $query->withSum(['chapterInfo as total_duration' => function($query){
                    //         $query->whereNull('chapter_info.deleted_at');
                    // }], 'duration');

                    //Total chapters count
                    // $query->withCount('chapterInfo as total_chapter');
                    $query->withCount(['chapters as total_chapter' => function ($query) {
                        $query->where('chapters.asset_type', '!=', '4');
                        $query->where('chapters.asset_type', '!=', '7');
                    }]);

                    // 'watched_time' column count (time based)
                    // $query->withCount(['userCourseProgress as total_completed_duration' => function($query){
                    //         $query->where('learner_id', Auth::guard('learner')->id())
                    //             ->where('is_completed', 1);
                    // }], 'watched_time');

                    //Total is_completed count
                    $query->withCount(['userCourseProgress as total_is_completed' => function ($query) {
                        $query->where('learner_id', Auth::guard('learner')->id())
                            ->where('is_completed', 1);
                    }]);

                    // 'rating_review' table data below
                    // 'total_review' count
                    $query->withCount(['rating_reviews as total_review' => function ($query) {
                        $query->isApproved()->isLearner();
                    }]);

                    // 'rating' column average
                    $query->withAvg(['rating_reviews as rating' => function ($query) {
                        $query->isApproved()->isLearner();
                    }], 'rating');

                    // 'id' column maximum id value
                    $query->withMax(['rating_reviews as max_review_id' => function ($query) {
                        $query->isApproved()->isLearner();
                    }], 'id');
                },
            ],
        ])
            ->select('id', 'learner_id', 'course_id', 'plan_id', 'expire_at')
            ->where('learner_id', Auth::guard('learner')->user()->id)
            ->find($userCourseId);
        // dd( Auth::guard('learner')->user()->id);
        // $userCourse->load(['coursePackages.original_course.chapterInfo' => function ($q) {
        //     $q->where('asset_type', '!=', 4);
        //   }]);
        // dd($userCourse->toArray());

        if (isset($userCourse) && !empty($userCourse) && isset($userCourse->coursePackages) && !empty($userCourse->coursePackages)) {
            $userCourse->coursePackages->map(function ($item, $key) {
                if (isset($item->original_course) && !empty($item->original_course)) {
                    $item->original_course['total_video_completed_percentage'] = 0;

                    // Time based
                    // if(isset($item->original_course->total_duration) && !empty($item->original_course->total_duration) && isset($item->original_course->total_completed_duration) && !empty($item->original_course->total_completed_duration)){
                    //     $item->original_course['total_video_completed_percentage'] = round(($item->original_course->total_completed_duration * 100) / $item->original_course->total_duration);
                    //     $item->original_course->rating = round($item->original_course->rating);
                    // }

                    // Chapter based
                    // dump( $item->original_course->total_chapter);
                    if (isset($item->original_course->total_chapter) && !empty($item->original_course->total_chapter) && isset($item->original_course->total_is_completed) && !empty($item->original_course->total_is_completed)) {
                        $item->original_course['total_video_completed_percentage'] = round(($item->original_course->total_is_completed * 100) / $item->original_course->total_chapter);
                        $item->original_course->rating = round($item->original_course->rating);
                    }
                }
            });
        }

        $browserName = Agent::browser(); // Chrome
        $browserVersion = Agent::version($browserName); //109.0.0.0
        $platform = Agent::platform(); // Windows // LINUX
        $divice_name = $browserName . " " . $browserVersion . " " . $platform;
        // if(!$chapterID) {
        LearnerLog::create([
            "learner_id" => @Auth::guard('learner')->user()->id,
            'device_name' => $divice_name,
            'description' => "Package viewed",
            'course_id' => $userCourse->course_id,
            // 'chapter_id' => $chapterID,
            'type' => 3,
        ]);
        // }

        // dd($userCourse->toArray());

        if (view()->exists('front/student/dashboard/my_package_course')) {
            return view('front/student/dashboard/my_package_course', ['userCourse' => $userCourse]);
        }
        abort(404);
    }
    /**
     * Show the Course Wishlist.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function student_wishlist()
    {
        return view('front/student/student_wishlist');
    }
    /**
     * Show the Purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function student_purchase_history(Request $request)
    {
        $deviceType = Course::COURSE_WEBSITE;

        $orderby = $dir = "";
        $courses = Course::distinct()->select('title', 'slug', 'instructor_id', 'instructor_name', 'description', 'tags', 'banner_image', 'intro_video', 'tagline', 'how_to_use', 'lng', 'image', 'meta_title', 'meta_keywords', 'meta_description', 'type', 'status', 'course_platform', 'order', 'is_featured', 'is_free', 'courses.id', 'user_courses.id as user_courses_id', 'user_courses.price', "user_courses.payment_gateway")
        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0);
                },
                'packages',
                'rating_reviews as total_review',
            ])
            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->with([
                'wishlists' => function ($query) {
                    $query->select('course_id', 'id', 'learner_id');
                },
                'plans',
                'instructor.instructure',
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'categories',
            ])
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
            ->where('courses.status', '1')
            ->where('user_courses.learner_id', Auth::guard('learner')->id())
            ->whereNull('courses.deleted_at')
            ->whereNotNull('transaction_id')
        //->groupBy('courses.id')
            ->orderBy('user_courses.id', "DESC")->paginate(10);

        $wishlists = [];
        if (isLearnerLoggedIn()) {
            $wishlists = Wishlist::where('learner_id', authLearnerID())->whereNull('deleted_at')->get();
        }
        //$instructors = Instructors::all();
        $instructors = Helper::getInstructure();
        $categories = DropdownOption::whereHas('dropdown', function ($query) {
            $query->where('slug', 'course_category');
        })->get();

        if (view()->exists('front/student/student_purchase_history')) {
            return view('front/student/student_purchase_history', compact('courses', 'categories', 'instructors', 'wishlists'));
        }
        abort(404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function profileSet(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            // 'd_o_b' => 'before:today',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        if (Auth::guard('learner')->user()) {
            $id = Auth::guard('learner')->user()->id ?? 0;
            if ($id > 0) {

                // dd($request->all());
                // dd($id);
                $validator = $validator->validated();
                $learner = Learner::findOrFail($id);
                $learner->email = $validator['email'];
                $learner->name = request()->has('name') ? $request->name : null;
                $learner->gender = request()->has('gender') ? $request->gender : null;
                $learner->d_o_b = $request['d_o_b'] ? $request['d_o_b'] : $learner->d_o_b;
                $learner->country_id = $request['country_id'] ? $request['country_id'] : $learner->country_id;
                $learner->state_id = $request['state_id'] ? $request['state_id'] : $learner->state_id;
                $learner->city_id = $request['city_id'] ? $request['city_id'] : $learner->city_id;
                $learner->save();

                // insert newsletter by default
                NewsLetter::updateOrCreate(
                    ['email' => $learner->email],
                    ['name' => $learner->name]
                );
                if (!empty($learner) && !empty(session('new_learner') && session('new_learner'))) {
                    $params['email']['to'] = $learner->email;
                    $params['email']['learnerId'] = $learner->id;
                    $params['push']['to'] = $learner->mobile;
                    $params['web']['to'] = $id;

                    $params['common'] = [
                        'course_id' => null,
                        'learner_id' => $learner->id,
                        'type' => 1,
                    ];
                    $content = "You have successfully registered";
                    // $subject = "Registered successfully";
                    $subject = "Welcome " . $learner->name . " to lifegurukul";
                    $res = send_notification($params, 'registration', ['email'], $subject, $content);
                }
                session('new_learner', false);
                if ($request->ajax()) {

                    return response()->json(['success' => 1]);
                }
                return redirect()->back();
            } else {
                return response()->json(['error' => "Please Login in Again.."]);
            }
        } else {
            return response()->json(['error' => "You are not logged.."]);
        }
    }
    public function edit(Request $request)
    {
        $learner = [];
        if (isLearnerLoggedIn()) {
            $learner = Learner::findOrFail(authLearnerID());
        }
        $newsletter_data = NewsLetter::where('email', $learner->email)->first();
        $is_subscribed = false;
        if (isset($newsletter_data->email) && $newsletter_data->email != '') {
            $is_subscribed = true;
        }
        $occupations = DropdownOption::getDropdownCategories('occupation')->get();
        $marital_status = DropdownOption::getDropdownCategories('marital-status')->get();
        $educations = DropdownOption::getDropdownCategories('education')->get();
        $your_interests = DropdownOption::getDropdownCategories('your-interests')->get();
        $user_interests = explode(',', $learner->your_interests);
        // dd($newsletter_data);
        if (view()->exists('front.student.student_profile')) {
            return view('front.student.student_profile', compact('learner', 'is_subscribed', 'occupations', 'marital_status', 'educations', 'your_interests', 'user_interests'));
        }
        abort(404);
    }
    public function update(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|max:255',
            // 'gender' => 'required|in:' . implode(",", Learner::GENDER),
            // 'country_id' => 'required',
            'state_id' => 'required',
            // 'mobile' => ['required', 'filled', Rule::unique('learners', 'mobile')->ignore(authLearnerID(), 'id')],
            'email' => 'required|filled|regex:/(.+)@(.+)\.(.+)/i|email',
            //'email' => ['required', 'filled', Rule::unique('learners', 'email')->ignore($learner->id, 'id')],
            // 'd_o_b' => 'required|before:today',
        ]);

        // try {

        if (isset($request['promotional_email']) && $request['promotional_email'] == 1) {
            // insert newsletter by default
            NewsLetter::updateOrCreate(
                ['email' => $request['email'],
                    'name' => $request['name'],
                    'learner_id' => auth()->guard("learner")->user()->id]
            );
        } else {
            NewsLetter::where('email', $request['email'])->delete();
        }
        $your_interests = '';
        if (!empty($request->input('your_interests'))) {
            $your_interests = $request->input('your_interests');
            $your_interests = implode(',', $your_interests);
        }

        $notification = [];
        Learner::findOrFail(authLearnerID())->update([
            'name' => $request['name'],
            // 'country_code' => $request['country_code'],
            // 'mobile' => $request['mobile'],
            'email' => $request['email'],
            'gender' => request()->has('gender') ? $request['gender'] : null,
            'd_o_b' => $request['d_o_b'] ?? null,
            // 'country_id' => $request['country_id'] ?? null,
            'state_id' => $request['state_id'] ?? null,
            'city_id' => $request['city_id'] ?? null,
            'occupation' => $request['occupation'] ?? null,
            'marital_status' => $request['marital_status'] ?? null,
            'education' => $request['education'] ?? null,
            'your_interests' => $your_interests ?? null,
            'updated_at' => now(),
        ]);

        // dd(auth()->guard("learner")->user()->id);
        $emptyFields = Learner::where("id", auth()->guard("learner")->user()->id)
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
                    ->orWhereNull('your_interests')
                    ->orWhereNull('updated_at');
            })
            ->get();
        $coin_price = config()->has('settings.completedprofilecoin') ? config('settings.completedprofilecoin') : null;
        if ($emptyFields->isEmpty()) {

            $check_usercoin = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)->where("type", 6)->first();

            if (!$check_usercoin) {

                $user_coin = UserCoin::create([
                    "learner_id" => @auth()->guard("learner")->user()->id,
                    "coins" => $coin_price,
                    "type" => 6,
                    //"comment" => "Completing your profile earned you $coin_price coins",
                    "comment" => "You have earned $coin_price success coins due to you have completed your profile",
                ]);

                $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                    ->where(function ($query) {
                        $query->where("type", "!=", 2);
                        $query->Where("type", "!=", "4");
                    })->sum("coins");

                // $deduct = UserCoin::where("type",2)->where("type",4)->sum("coins");
                $deduct = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
                    ->where(function ($query) {
                        $query->where("type", "=", 2)
                            ->orWhere("type", "=", 4);
                    })->sum("coins");

                $total = $plus - $deduct;

                $subject = "Congratulations! Success Coins Allocated to Your Account!";
                $content = "you have completed profile.";

                //$learner = Learner::where("id", auth()->guard("learner")->user()->id)->first();
                $inst = new EarnCoinMail($content, $subject, @Auth::guard('learner')->user(), $coin_price, $total);
                if (Auth::guard('learner')->user()) {
                    \Mail::to(@Auth::guard('learner')->user()->email)->send($inst);
                }
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner Profile updated successfully";

        // } catch (\Exception$e) {
        //     DB::rollback();
        //     $notification['type'] = "sweet-alert";
        //     $notification['status'] = "error";
        //     $notification['title'] = "Error";
        //     $notification['msg'] = "Something went wrong.";
        // }
        return redirect('profile')->with([
            'notification' => $notification,
        ]);
    }
    public function profilePicture(Request $request)
    {
        $data = array();
        $id = Auth::guard('learner')->user()->id;
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpg,jpeg,csv,txt,pdf|max:2048',
        ]);

        if ($validator->fails()) {

            $data['success'] = 0;
            $data['error'] = $validator->errors()->first('file'); // Error response

        } else {
            if ($request->file('file')) {
                //remove Old file
                if (!empty(Auth::guard('learner')->user()->profile_pic)) {
                    Storage::delete(Auth::guard('learner')->user()->profile_pic);
                }
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();

                // File extension
                $extension = $file->getClientOriginalExtension();

                // File upload location
                $location = $file->storeAs('profile', $filename);

                // File path
                $path = Storage::url($location); //storage_path('app/public/' . $location);

                // Update Image
                $cust = Learner::findOrFail($id);
                $cust->profile_pic = $location;
                $cust->save();

                // Response
                $data['success'] = 1;
                $data['message'] = 'Uploaded Successfully!';
                $data['filepath'] = $path;
                $data['extension'] = $extension;
            } else {
                // Response
                $data['success'] = 2;
                $data['message'] = 'File not uploaded.';
            }
        }
        return response()->json($data);
    }
    public function profilePictureRemove(Request $request)
    {
        // dd($request->all());
        $notification = [];
        //remove Old file
        if (!empty(Auth::guard('learner')->user()->profile_pic)) {
            Storage::delete(Auth::guard('learner')->user()->profile_pic);
        }
        $learner = Learner::findOrFail(authLearnerID());
        $learner->profile_pic = null;
        $learner->updated_at = now();
        $learner->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Leraner Profile image removed successfully";
        return redirect('profile')->with([
            'notification' => $notification,
        ]);
    }

    /**
     * Show the student deposite dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function studentPayment()
    {
        return view('front/student/dashboard/student_payment');
    }

    /**
     * Show the student deposite dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function studentInvoice()
    {

        $deviceType = Course::COURSE_WEBSITE;

        $courses = Course::distinct()->select('title', 'learner_id', 'type', 'slug', 'user_courses.id as user_courses_id', 'user_courses.price', 'user_courses.order_status', 'user_courses.created_at', 'user_courses.invoice', 'user_courses.after_deduction_price', "user_courses.transaction_id", 'user_courses.after_deduction_price', 'user_courses.per_coin_price', 'user_courses.after_deduction_price', 'user_courses.user_coin', 'user_courses.country_id', 'user_courses.state_id', 'user_courses.after_coupon_applied_deduction_price', "user_courses.payment_gateway", "user_courses.per_coin_price", "user_courses.price")
        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            }])
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'categories'])
            ->with('instructor.instructure')
            ->with(['wishlists' => function ($query) {
                $query->select('course_id', 'id', 'learner_id');
            }])
            ->with(['plans'])
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
        // ->where('courses.status', '1')
            ->where('user_courses.learner_id', Auth::guard('learner')->id())
            ->whereNull('courses.deleted_at')
        //->whereNotNull('transaction_id')
        //->groupBy('courses.id')
            ->orderBy('user_courses.id', "DESC")->paginate(10);

        if (view()->exists('front/student/dashboard/student_invoice')) {
            return view('front/student/dashboard/student_invoice', compact('courses'));
        }
    }

    /**
     * Show the student deposite dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function viewInvoice($id)
    {

        ini_set('max_execution_time', 180);
        // $id = Crypt::decrypt($id);
        // $invoiceData = collect([]);

        $invoiceData = get_invoice_dataV3($id);
        if (!empty($invoiceData)) {
            if (view()->exists('front/student/view_invoice_device')) {
                return view('front/student/view_invoice_device', compact('invoiceData'));
            }
            abort(404);
        }
        // Storage::disk('local')->makeDirectory('/invoice');
        // pdf download from here
        // $pdf = PDF::setOptions([
        //     'isHtml5ParserEnabled' => true,
        //     'isRemoteEnabled' => true
        // ])->loadView('front.student.view_invoice_device', compact('invoiceData'));
        // $path = storage_path('app/public/invoice');//  public_path('invoice/');
        // $fileName =  'invoice_' . $id . '.pdf' ;
        // $pdf->save($path . '/' . $fileName);
        // return $pdf->download($fileName);
    }
    public function notificationsDetails($id = 0)
    {
        $learner_id = Auth::guard('learner')->id();
        $notification = Notification::with('course')->where('learnerId', $learner_id)->findOrFail($id);
        if ($id > 0) {
            $read = Notification::where('id', $id)->first();
            $read->update([
                'isRead' => 1,
            ]);
        }
        $notification->date = Helper::date_format($notification->updated_at);
        $pg_header = "View Notification";
        return view('front/student/dashboard/notification_detail', compact('notification', 'pg_header'));
    }

    public function notifications($id = 0)
    {
        // return view('setting-student-notification');
        $learner_id = Auth::guard('learner')->id();
        $notifications = Notification::with('course')->orderBy('id', 'DESC')->where('learnerId', $learner_id)->get();

        // if($id > 0) {
        $read = Notification::where('id', $id)->first();
        // $read->update([
        //     'isRead' => 1,
        // ]);

        // }
        // $notifications=$notifications->filter(function($val,$k) use($learner_id){
        //     return in_array($learner_id, explode(',',$val->learner_ids));
        // });
        // $notify_ids = $notifications->pluck('id')->map(function($value, $key){
        //     $read = Notification::where('id',$value)->first();
        //     if(empty(explode(',',$read->readby_learner_ids))){
        //         $read->update([
        //             'readby_learner_ids' => Auth::guard('learner')->id()
        //         ]);
        //     }
        //     else {
        //         $read->readby_learner_ids .= ",".Auth::guard('learner')->id();
        //         $read->readby_learner_ids = trim($read->readby_learner_ids,",");
        //         $read->update([
        //             'readby_learner_ids' => Auth::guard('learner')->id()
        //         ]);
        //     }
        // });
        $notifications = cpaginate($notifications);
        return view('front/student/dashboard/notification', compact('notifications'));
    }

    public function subscriptions()
    {
        $learnerId = Auth::guard('learner')->check() ? Auth::guard('learner')->id() : null;
        if (isset($learnerId) && $learnerId != null) {
            $deviceType = Course::COURSE_WEBSITE;
            $subscriptionData = Course::distinct()->select('courses.title', 'courses.slug', 'courses.id', 'user_courses.id as user_courses_id', 'user_courses.price', 'user_courses.created_at', 'course_plans.calendar', 'user_courses.subscription_id')
            // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
                ->whereRaw("find_in_set($deviceType , course_platform)")
                ->withCount(['chapters' => function ($query) {
                    $query->where('parent_id', 0);
                }])
                ->with(['instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                }, 'categories'])
                ->with('instructor.instructure')
                ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
                ->leftJoin('course_plans', 'user_courses.plan_id', '=', 'course_plans.id')
                ->where('courses.status', '1')
                ->where('user_courses.learner_id', $learnerId)
                ->whereNotNull('user_courses.is_subscription')
                ->whereNotNull('user_courses.subscription_id')
                ->where('courses.status', '1')
                ->whereNull('courses.deleted_at')
                ->whereNotNull('transaction_id')
                ->orderBy('user_courses.id', "DESC")->get();
            if (view()->exists('front/student/dashboard/mysubscriptions')) {
                return view('front/student/dashboard/mysubscriptions', compact('subscriptionData'));
            }
            abort(404);
        }
    }

    public function readNotification()
    {
        $user = Auth::guard("learner")->user()->id;
        $notify = Notification::where("learnerId", $user)->update(["isRead" => 1]);
        return redirect()->back();
    }

    public function testEmailTemplate(Request $request)
    {

        $data = [
            "to" => "919898222366",
            "type" => "template",
            "template" => [
                "language" => [
                    "code" => "English",
                ],
                "name" => "lifegurukulotp",
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => "123654",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $url = "https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/messages";

        // Use Laravel's HTTP client to send the request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            "X-AiSensy-Project-API-Pwd" => "94f5002d280336c191a48",
        ])->post($url, $data);

        $resp = json_decode($response);
        dd($resp);

        // $subject = "Congratulations! Success Coins Allocated to Your Account!";
        //     // $content = "Completing your profile earned you $coin_price coins.";
        //     $content = "you have purchased 10 course";
        //     $plus = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)->where("type", "!=", 2)->Orwhere("type", "!=", 2)->sum("coins");
        //     // $deduct = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)->where("type",2)->Orwhere("type",4)->sum("coins");

        //     $deduct = UserCoin::where("learner_id", @auth()->guard("learner")->user()->id)
        //         ->where(function ($query) {
        //             $query->where("type", "=", 2)
        //                 ->orWhere("type", "=", 4);
        //         })->sum("coins");

        //     $total = $plus - $deduct;

        //     $learner = Learner::where("id", @auth()->guard("learner")->user()->id)->first();
        //     $inst = new EarnCoinMail($content, $subject, $learner, 10, 20);
        //     if ($learner) {
        //         \Mail::to('saifmsp7@gmail.com')->send($inst);
        //     }
        //     echo "done";
        //     exit;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "X-AiSensy-Project-API-Pwd: 94f5002d280336c191a48",
            ],
        ]);

        $response = curl_exec($curl);
        dd($response);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }

        // $curl = curl_init();
        // curl_setopt_array($curl, [
        // CURLOPT_URL => "https://apis.aisensy.com/project-apis/v1/project/66150ee46838040c0b531ab5/wa_template/",
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => "",
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 30,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => "GET",
        // CURLOPT_HTTPHEADER => [
        //     "Accept: application/json",
        //     "X-AiSensy-Partner-API-Key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY2MTUwZWU0NjgzODA0MGMwYjUzMWFiNSIsIm5hbWUiOiJUaW1lbGVzcyBFZHVjYXRpb24gTExQIiwiYXBwTmFtZSI6IkFpU2Vuc3kiLCJjbGllbnRJZCI6IjY2MTUwZWU0NjgzODA0MGMwYjUzMWFhNSIsImFjdGl2ZVBsYW4iOiJQUk9fTU9OVEhMWSIsImlhdCI6MTcxNTU5MjQ2Mn0.ucKHPFLZCjrkW2YK0VVlL_7sXKFSV8GAlJVZFY1u-b0"
        // ],
        // ]);

        // $response = curl_exec($curl);
        // dd($response);
        // $err = curl_error($curl);

    }

}
