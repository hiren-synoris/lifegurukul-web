<?php
namespace App\Http\Controllers\front;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\front\CommonController;
use App\Mail\EarnCoinMail;
use App\Models\Chapter;
use App\Models\ChapterInfo;
use App\Models\Course;
use App\Models\DropdownOption;
use App\Models\LearnerLog;
use App\Models\User;
use App\Models\UserCoin;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use App\Models\Wishlist;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Facades\Agent;
use PDF;

class CoursesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:learner');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        // dd($request->all());

        $orderby = $dir = "";
        $categories = $price_flt = [];
        $prev_price_list = null;
        $current_price_list = $request->pricelist;
        $page = null;
        if ($request->has('page')) {
            $page = $request->page;
        }
        if (!empty($request->pricelist)) {
            $prev_price_list = explode(":", $request->pricelist);
            if (count($prev_price_list) > 0) {
                // foreach ($prev_price_list as $key => $value) {
                $temp = explode(",", $request->pricelist);
                $price_flt['left'] = $temp[0];
                $price_flt['right'] = $temp[1];
                // }
            }
        }
        // if(request()->has('sort') && $request->query('sort') == "name_asc"){
        //     $orderby="courses.title";
        //     $dir="asc";
        // }
        // elseif(request()->has('sort') && $request->query('sort') == "name_desc"){
        //     $orderby="courses.title";
        //     $dir="desc";
        // }
        // else
        if (request()->has('sort') && $request->query('sort') == "date_asc") {
            $orderby = "courses.created_at";
            $dir = "asc";

        } elseif (request()->has('sort') && $request->query('sort') == "date_desc") {
            $orderby = "courses.created_at";
            $dir = "desc";
        } elseif (request()->has('sort') && $request->query('sort') == "price_desc") {
            $orderby = "cp.final_payable_price";
            $dir = "desc";
        } elseif (request()->has('sort') && $request->query('sort') == "price_asc") {
            $orderby = "cp.final_payable_price";
            $dir = "asc";
        } elseif (request()->has('sort') && $request->query('sort') == "rating_desc") {
            $orderby = "AverageRating";
            $dir = "desc";
        }
        //  else {
        //     // if (Course::sum('order') <= 0) {
        //     //     $orderby = "courses.id";
        //     //     $dir = "DESC";
        //     // } else {

        //     //     $orderby = DB::raw('ISNULL(courses.order), courses.order');
        //     //     $dir = "ASC";
        //     // }
        // }
        // DB::raw('AVG(rv.rating) as AverageRating'),
        $deviceType = Course::COURSE_WEBSITE;
        // $coursess = Course::

        // when(request()->has('categorylist') && !empty($request->query('categorylist')), function($query) use ($request){
        //     $categoryList = $request->query('categorylist');
        //     $query->whereHas('categories', function($query)   use($categoryList) {
        //         $query->whereIn('category_id', stringToArray($categoryList))->whereNull('course_categories.deleted_at');
        //     });
        // }) ->whereRaw("find_in_set($deviceType , course_platform)")->get();
        // dd($coursess);
        $courses = Course::distinct()->select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')

        //    ->whereIn('course_platform',[Course::COURSE_ALL,Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            },
                'rating_reviews as total_review',

                'userCourse as total_enroll'])
            ->withAvg('rating_reviews as AverageRating', 'rating')

            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'categories' => function ($query) use ($request) {
                $query->whereNull('course_categories.deleted_at');
            },
                'packages.original_course',
                'plans' => function ($query) {
                    return $query->orderBy('order', 'Asc');
                }])
            ->with("packages")
            ->whereHas("user")
            ->with('instructor.instructure')  
            ->with(['wishlists' => function ($query) {
                $query->select('course_id', 'id', 'learner_id');
            }, 'rating_reviews' => function ($query) {
                $query->where('is_approve', true);
                $query->whereNull('deleted_at');
            }])
            ->when(request()->has('searchTxt') && !empty($request->query('searchTxt')), function ($query) use ($request) {
                $query->where('title', 'LIKE', "%" . $request->query('searchTxt') . "%");
            })
            ->when(request()->has('instructurelist') && !empty($request->query('instructurelist')), function ($query) use ($request) {
                $query->whereIn('instructor_id', stringToArray($request->query('instructurelist')));
            })
            ->when(request()->has('categorylist') && !empty($request->query('categorylist')), function ($query) use ($request) {
                $categoryList = $request->query('categorylist');
                $query->whereHas('categories', function ($query) use ($categoryList) {
                    $query->whereIn('category_id', stringToArray($categoryList))->whereNull('course_categories.deleted_at');
                });
            })
            ->when(request()->has('pricelist') && !empty($request->query('pricelist')), function ($query) use ($request, $price_flt) {
                if (count($price_flt) > 0) {
                    $temp2 = $price_flt['left'];
                    $temp3 = $price_flt['right'];
                    if ($temp2 == 0 && $temp3 == 0) {
                        $query->where('cp.plan_type', 0);
                    } else {
                        $query->whereBetween('cp.final_payable_price', [$temp2, $temp3]);
                    }
                    $query->where('cp.status', 1);
                }
            })
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
            ->where('courses.status', '1')
            ->where('cp.status', '1')
            ->whereNotNull('courses.default_web_price')
            ->whereNull('courses.deleted_at')

            ->when($orderby == "", function($q) {
                $q->orderByRaw('CASE WHEN `courses`.`order` IS NOT NULL THEN 0 ELSE 1 END');
                $q->orderBy("courses.id", "DESC");
            
            })
            ->when($orderby!="",function($q)use($orderby,$dir){ 
                $q->orderBy($orderby, $dir);
                $q->orderBy('courses.id', 'desc');
            })
            ->groupBy('courses.id')
           ->get();

        // dd($courses->toArray());

        $courses = $courses->filter(function ($value, $key) use ($courses, $deviceType) {
            $sum = 0;
            $value->packages->filter(function ($val, $k) use (&$sum, $deviceType) {
                $course_count = Course::where("id", $val->course_id)->whereRaw("find_in_set($deviceType , course_platform)")->first();
                if ($course_count) {
                    return $sum = $sum += 1;
                }
            });
            $value->packages_count = $sum;
            if (count($value->plans) <= 0) {
                return false;
            } else {
                return true;
            }
        });
        // dd($courses->toArray());

        $courses = cpaginate($courses);
        // dd(DB::getQueryLog());
        $wishlists = [];
        if (isLearnerLoggedIn()) {
            $wishlists = Wishlist::where('learner_id', authLearnerID())->whereNull('deleted_at')->get();
        }
        //$instructors = Instructors::all();
        $instructors = Helper::getInstructure();
        $categories = DropdownOption::whereHas('dropdown', function ($query) {$query->where('slug', 'course_category');})->get();
        // dd( $categories->count());.


        if (view()->exists('front/course/course-list')) {
            return view('front/course/course-list', compact('courses', 'categories', 'instructors', 'wishlists', 'prev_price_list', 'current_price_list', 'price_flt'));
        }abort(404);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function course_detail(Request $request, $slug)
    {
        // dd("");
        $currentPage = $request->input('page', 1);
        $perPage = 10;
        $wishlists = [];
        $deviceType = Course::COURSE_WEBSITE;
        $course = Course::select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price', 'cp.course_limit', 'cp.is_fixed_date', 'cp.access_value')
            ->where('slug', $slug)->with(['chapters' => function ($query) {
            $query->where('parent_id', 0)->where('asset_type', '<>', 7);
        },
            'categories',
            'instructor',
            'instructor.instructure',
            'wishlists',
            'packages',
            'userCourseExpectedRelationship' => function ($query) {
                return $query->where('learner_id', Auth::guard('learner')->id())->where('order_status', 1);
            },
            'plans' => function ($query) {
                return $query->orderBy('order', 'Asc');
            }])
            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0)->where('asset_type', '<>', 7);
            },
                'rating_reviews as total_review',
                'packages'])
            ->withCount(['userCourse as total_enroll',
                'rating_reviews as total_review'])
            ->with(['userCourseCount' => function ($query) {
                $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                    ->groupBy('course_id');
            }])

            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
        // ->where('courses.status','1')
        // ->where('cp.status','1')
            ->firstOrFail();

        $course->chapters = $course->chapters->sortBy('order');
        // rating review pagination start
        $sortOption = $request->input('sort_option', 'mostrecent');
        $sortOptions = ['mostrecent', 'higheststar', 'loweststar'];
        if (!in_array($sortOption, $sortOptions)) {
            $sortOption = 'mostrecent'; // Set default if an invalid option is selected
        }
        $course->setRelation('rating_reviews', $course->rating_reviews()
                ->with(['learner', 'instructor'])
                ->where('is_approve', 1)
                ->when($sortOption === 'higheststar', function ($query) {
                    return $query->orderBy('rating', 'DESC');
                })
                ->when($sortOption === 'loweststar', function ($query) {
                    return $query->orderBy('rating', 'asc');
                }, function ($query) {
                    return $query->orderBy('created_at', 'DESC'); // 'mostrecent' option or invalid option
                })
                ->paginate($perPage, ['*'], 'page', $currentPage)
        );
        // rating review pagination end.........

        if ($course && isset($course->packages) && count($course->packages) > 0) {
            $course->packages->map(function ($value, $key) {
                $value->package = Course::where('id', $value->course_id)->with(['instructor'])->first();
                return $value;
            });
        }
        if ($course && isset($course->chapters) && count($course->chapters) > 0) {
            $course->chapters->map(function ($value) {
                $value->chapter_items = Chapter::with(['media', 'chapterInfo' => function ($query) {
                }])->where('parent_id', $value->id)->get();
                $value->total_lectures = count($value->chapter_items);
            });
            $course->lectures = $course->chapters->pluck('total_lectures')->sum();
        } else {
            $course->chapters->chapter_items = [];
            $course->chapters->lectures = 0;
        }
        $course->inst_total_course = 0;
        $wishlists = [];
        if (isLearnerLoggedIn()) {
            $wishlists = Wishlist::where('learner_id', authLearnerID())->whereNull('deleted_at')->get();
        }

        // $course->rr_count = Course::where('slug',$slug)->withCount(['rating_reviews' => function($query){
        //     $query->where('is_approve', 1);
        // }])->first()->rating_reviews_count;
        $total_rating_based_on_course = Course::where('slug', $slug)->withCount(['rating_reviews' => function ($query) {
            $query->where('is_approve', 1);
        }])->first()->rating_reviews_count;

        // dd($course->rr_count);
        $course->is_purchased = course_purchased($course->id);
        if ($course && isset($course->instructor)) {
            $course->inst_rating = avg_inst_rating($course->instructor->id);
        }
        $course->inst_total_course = Course::where('instructor_id', $course->instructor_id)->whereNull('deleted_at')->where('status', 1)
        // ->whereIn('course_platform',[Course::COURSE_ALL,Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->select('id')->get();
        // $course->inst_total_course = $course->inst_total_course->filter(function ($value, $key) {
        //     if(count($value->plans) <= 0){
        //         return false;
        //     }
        //     else{
        //         return true;
        //     }
        // });
        $course->inst_total_course = $course->inst_total_course->count();
        $course->rr = Course::where('slug', $slug)->with(['rating_reviews' => function ($query) {
            $query->where('is_approve', 1);
        }])->first();

        // dd($course);

        return view('front/course/course_detail', compact('course', 'wishlists', 'total_rating_based_on_course', 'sortOption'));
    }

    public function course_package(Request $request, $slug)
    {
        $currentPage = $request->input('page', 1);
        $perPage = 10;
        $wishlists = [];
        $deviceType = Course::COURSE_WEBSITE;
        $learnerId = Auth::guard('learner')->check() ? Auth::guard('learner')->id() : '';
        $course = Course::select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.course_limit', 'cp.is_fixed_date', 'cp.access_value', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
            ->where('slug', $slug)
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            },
                'categories',
                'instructor',
                'wishlists',
                'rating_reviews' => function ($query) {
                    $query->where('is_approve', true)->orderBy('id', "Desc")->get();
                },

                'packages.original_course',
                'userCourseExpectedRelationship' => function ($query) {
                    return $query->where('learner_id', Auth::guard('learner')->id())->where('order_status', 1);
                },
                'plans' => function ($query) {
                    return $query->orderBy('order', 'Asc');
                }])
            ->with('instructor.instructure')
            ->with(['userCourseCount' => function ($query) {
                $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                    ->groupBy('course_id');
            }])

            ->withCount([
                'rating_reviews as total_review'])
            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
        // ->where('courses.status','1')
        // ->where('cp.status','1')
            ->firstOrFail();
        $sortOption = $request->input('sort_option', 'mostrecent');
        $sortOptions = ['mostrecent', 'higheststar', 'loweststar'];
        if (!in_array($sortOption, $sortOptions)) {
            $sortOption = 'mostrecent'; // Set default if an invalid option is selected
        }
        $course->setRelation('rating_reviews', $course->rating_reviews()
                ->with(['learner', 'instructor'])
                ->where('is_approve', 1)
                ->when($sortOption === 'higheststar', function ($query) {
                    return $query->orderBy('rating', 'DESC');
                })
                ->when($sortOption === 'loweststar', function ($query) {
                    return $query->orderBy('rating', 'asc');
                }, function ($query) {
                    return $query->orderBy('created_at', 'DESC'); // 'mostrecent' option or invalid option
                })
                ->paginate($perPage, ['*'], 'page', $currentPage)
        );
        if ($course && isset($course->packages) && count($course->packages) > 0) {
            $course->packages->map(function ($value, $key) use ($deviceType) {
                // join('course_plans as cp','cp.course_id','=','courses.id')->
                $value->package = Course::select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
                    ->where('courses.id', $value->course_id)
                    ->with(['instructor',
                        'categories',
                        'wishlists' => function ($query) use ($value) {
                            $learnerId = Auth::guard('learner')->check() ? Auth::guard('learner')->id() : '';
                            if ($learnerId) {
                                $query->where('learner_id', $learnerId);
                                $query->where('course_id', $value->course_id);
                            }
                        },
                        'rating_reviews' => function ($query) {
                            $query->where('is_approve', true);
                            $query->whereNull('deleted_at');
                        }])
                    ->whereRaw("find_in_set($deviceType , course_platform)")
                    ->with('instructor.instructure')
                    ->withCount(['rating_reviews as total_review'])
                    ->withAvg('rating_reviews as AverageRating', 'rating')
                    ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
                    ->where('courses.status', '1')
                    ->where('cp.status', '1')
                    ->first();
            });

        }
        if ($course && isset($course->chapters) && count($course->chapters) > 0) {
            $course->chapters->map(function ($value) {
                $value->chapter_items = Chapter::with('media')->where('parent_id', $value->id)->get();
                $value->total_lectures = count($value->chapter_items);
            });
            $course->lectures = $course->chapters->pluck('total_lectures')->sum();
        }

        // dd($course,count($course->plans)>0);
        // $course1 = $course->packages->filter(function($value, $key) use($course){
        //     if($value->original_course->is_free == 0 && count($course->plans) <= 0){
        //         return false;
        //     }
        //     else{
        //         return true;
        //     }
        // });
        // // if($course1->isNotEmpty()) {
        //     $course->packages = $course1->all();
        // // }
        if ($learnerId) {
            $wishlists = Wishlist::where('learner_id', $learnerId)->whereNull('deleted_at')->get();
        }
        $course->inst_total_course = 0;
        $course->rr_count = Course::where('slug', $slug)->withCount(['rating_reviews' => function ($query) {
            $query->where('is_approve', true);
        }])->first()->rating_reviews_count;
        $course->is_purchased = course_purchased($course->id);
        if ($course && isset($course->instructor)) {
            $course->inst_rating = avg_inst_rating($course->instructor->id);
        }
        $course->inst_total_course = Course::where('instructor_id', $course->instructor_id)->whereNull('deleted_at')->where('status', 1)
        // ->whereIn('course_platform',[Course::COURSE_ALL,Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->select('id')->get();
        // $course->inst_total_course = $course->inst_total_course->filter(function ($value, $key) {
        //     if(count($value->plans) <= 0){
        //         return false;
        //     }
        //     else{
        //         return true;
        //     }
        // });
        $course->inst_total_course = $course->inst_total_course->count();

        $course->rr = Course::where('slug', $slug)->with(['rating_reviews' => function ($query) {
            $query->where('is_approve', true);
        }])->first();
        // dd($course->toArray());
        $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);
        return view('front/course/course_package', compact('course', 'wishlists', 'sortOption'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function course_preview(Request $request, $slug, $chapterID = null)
    {

        // dd($request->all(),$chapterID);

        $course = Course::where('slug', $slug)->firstOrFail();
        if (Auth::check()) {
            if ($course->instructor_id > 0 && auth()->user()->hasRole(User::INSTRUCTOR) && $course->instructor_id != Auth::id()) {
                abort(403);
            }
        } elseif (Auth::guard('learner')->check()) {
            // if course purchase
            // dd("");
            $id = Auth::guard('learner')->user()->id;
            if (isset($id) && !empty($id)) {
                // For package
                $allUserCourses = UserCourse::with(['coursePackages'])
                    ->whereHas('course', function ($query) {
                        $query->where('type', 2);
                    })
                    ->where('learner_id', auth()->guard('learner')->user()->id ?? null)
                    ->get();

                $data = [];
                if (isset($allUserCourses) && !empty($allUserCourses)) {
                    $data = $allUserCourses->map(function ($item, $key) use ($data) {
                        $data['expire_at'] = $item->expire_at;
                        $data['course_ids'] = $item->coursePackages->pluck('course_id')->toArray();
                        return $data;
                    })->toArray();
                }

                $purchasedCourses = UserCourse::where('learner_id', $id)
                    ->where('course_id', $course->id)
                    // ->groupBy('learner_id')
                    ->orderBy("id","desc")
                    ->first();

                $arr = \Arr::flatten($data);
                if (isset($data) && !empty($data)) {
                    foreach ($data as $k => $value) {
                        if (!in_array($course->id, $arr) && empty($purchasedCourses)) {

                            abort(403);
                        }
                    }
                } else {
                    if (!empty($purchasedCourses)) {
                        $expireFlag = is_expired($purchasedCourses->expire_at);
                        if ($expireFlag == 0) {
                            abort(403);
                        }
                    } else {
                        abort(403);
                    }
                }
            }
        } else {
            abort(403);
        }


        $wishlists = [];
        $chapterData = collect();
        // dd($chapterData);
        $chapter = [];
        // dd($course->id);
        $courseChapters = Chapter::with(['userCourseProgress:id,chapter_id,is_completed,watched_time'])->getCourseChapters($course->id)->get();
        // dd(auth()->guard("learner")->user()->id);
        $my_array = [];
        $courseChapters->map(function ($value, $key) {
            $value->childrens = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')->where('parent_id', $value->id)
                ->with(['userCourseProgress' => function (Builder $query)use($value) {
                    $query->select('id', 'chapter_id', 'is_completed', 'watched_time');
                    $query->where('learner_id', Auth::guard('learner')->id());
                }])
                ->whereDoesntHave("getUserCourse",function($q)use($value){
                    $q->where("course_id","!=",$value->title);
                    $q->where("learner_id",@auth()->guard("learner")->user()->id);
                })
                ->whereNull('deleted_at')->orderBy('order', 'asc')->get();

        });




        $autoplay = ChapterInfo::select('autoplay')->get()->toArray();
        // dd($autoplay);
        if ($chapterID > 0) {
            // dd($chapterID);

            $chapterData = ChapterInfo::where('chapter_id', $chapterID)->whereNull('deleted_at')->first();
            // $data['firstData'] = ChapterInfo::where('chapter_id', $chapterID)->whereNull('deleted_at')->first();
            // $data['lastData'] = $chapterData->chapter_id;

            // dd(collect($chapterData)->latest);
            // dd($chapterData->chapter_id);
            // $chapter = Chapter::with('media:id,path,videoId')->with(['userCourseProgress:id,learner_id,chapter_id,is_completed,watched_time'])->where('id', $chapterID)->whereNull('deleted_at')->get();
            if (Auth::guard('learner')->check()) {
                $id = Auth::guard('learner')->user()->id;
                $chapter = Chapter::with([
                    'media:id,path,videoId',
                    'userCourseProgress' => function ($query) use ($id) {
                        $query->select('id', 'learner_id', 'chapter_id', 'is_completed', 'watched_time')
                            ->where('learner_id', $id); // Add your condition here
                    },
                ])
                    ->where('id', $chapterID)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
            } else {
                $chapter = Chapter::with([
                    'media:id,path,videoId',
                ])
                    ->where('id', $chapterID)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
            }
            // dd($courseChapters->toArrau)
        }

        /*if (Auth::guard('learner')->user()) {
            // dD($course->course_finished);
            if ($course->course_finished != null) {
                $chapter_watch_whole = Chapter::where("course_id", $course->id)
                    ->where("parent_id", "!=", 0)
                    ->where("asset_type", "!=", 7)
                    ->where("asset_type", "!=", 4)
                    ->pluck('id');

                $usercousreprogress = UserCourseProgress::whereIn("chapter_id", $chapter_watch_whole)->where("learner_id", @Auth::guard('learner')->user()->id)->where("is_completed", 1)->get();

                if ($usercousreprogress->count() == $chapter_watch_whole->count()) {

                    if ($usercousreprogress) {

                        $check_user = UserCoin::where("type", 7)->where("course_id", $course->id)->get();
                        if ($check_user->count() == 0) {
                            $user_coin = UserCoin::create([
                                "learner_id" => @Auth::guard('learner')->user()->id,
                                "course_id" => $course->id,
                                "coins" => $course->course_finished,
                                "type" => 7,
                                "comment" => "Congratulations, You have got $course->course_finished coins to successfully completed to $course->title course",
                            ]);
                            // $plus = UserCoin::where("type","!=",2)->where("type","!=",2)->sum("coins");

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
                            $content = "Congratulations, You have got $course->course_finished_day coins to successfully.";

                            $inst = new EarnCoinMail($content, $subject, @Auth::guard('learner')->user(), $user_coin, $total,
                            $course->course_finished_day);
                            if (Auth::guard('learner')->user()) {

                                \Mail::to(@Auth::guard('learner')->user()->email)->send($inst);
                            }
                        }
                    }
                }
            }



            if ($course->course_finished_day != null && $course->days != null) {
                $course_days = $course->userCourseExpectedRelationship[0]->created_at;

                $currentDate = Carbon::now();
                $courseDate = Carbon::parse($course_days);

                if ($courseDate->diffInDays($currentDate) + 1 <= 2) {
                    $chapter_watch_whole = Chapter::where("course_id", $course->id)
                        ->where("parent_id", "!=", 0)
                        ->where("asset_type", "!=", 7)
                        ->where("asset_type", "!=", 4)
                        ->pluck('id');

                    $usercousreprogress = UserCourseProgress::whereIn("chapter_id", $chapter_watch_whole)->where("learner_id", @Auth::guard('learner')->user()->id)->where("is_completed", 1)->get();

                    if ($usercousreprogress->count() == $chapter_watch_whole->count()) {


                        if ($usercousreprogress) {
                            $check_user = UserCoin::where("type", 8)->where("course_id", $course->id)->get();
                            if ($check_user->count() == 0) {
                                $user_coin = UserCoin::create([
                                    "learner_id" => @Auth::guard('learner')->user()->id,
                                    "course_id" => $course->id,
                                    "coins" => $course->course_finished_day,
                                    "type" => 8,
                                    "comment" => "Congratulations, You have got $course->course_finished_day coins to successfully. completed to $course->title course",
                                ]);

                                // $plus = UserCoin::where("type","!=",2)->where("type","!=",2)->sum("coins");
                                // $deduct = UserCoin::where("type",2)->Orwhere("type",4)->sum("coins");

                                $plus = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)
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
                                $content = "Congratulations, You have got $course->course_finished_day coins to successfully.";

                                $inst = new EarnCoinMail($content, $subject, Auth::guard('learner')->user(), $user_coin, $total, $course->course_finished_day);
                                if (Auth::guard('learner')->user()) {
                                    \Mail::to(@Auth::guard('learner')->user()->email)->send($inst);
                                }
                            }

                        }
                    }
                }

            }*/

        // }
        $browserName = Agent::browser(); // Chrome
        $browserVersion = Agent::version($browserName); //109.0.0.0
        $platform = Agent::platform(); // Windows // LINUX
        $divice_name = $browserName . " " . $browserVersion . " " . $platform;
        if(!$chapterID) {
            LearnerLog::create([
                "learner_id" => @Auth::guard('learner')->user()->id,
                'device_name' => $divice_name,
                'description' => "Course viewed",
                'course_id' => $course->id,
                'chapter_id' => $chapterID,
                'type' => 3,
            ]);
        }



        return view('front/course/course_preview', compact('course', 'courseChapters', 'chapter', 'chapterData', 'slug', 'chapterID', 'autoplay'));
    }

    public function course_preview_slug_chapter(Request $request, $slug, $chapterID = null)
    {

        // dd($request->all(),$chapterID);

        $course = Course::where('slug', $slug)->firstOrFail();
        if (Auth::check()) {
            if ($course->instructor_id > 0 && auth()->user()->hasRole(User::INSTRUCTOR) && $course->instructor_id != Auth::id()) {
                abort(403);
            }
        } elseif (Auth::guard('learner')->check()) {
            // if course purchase
            // dd("");
            $id = Auth::guard('learner')->user()->id;
            if (isset($id) && !empty($id)) {
                // For package
                $allUserCourses = UserCourse::with(['coursePackages'])
                    ->whereHas('course', function ($query) {
                        $query->where('type', 2);
                    })
                    ->where('learner_id', auth()->guard('learner')->user()->id ?? null)
                    ->get();

                $data = [];
                if (isset($allUserCourses) && !empty($allUserCourses)) {
                    $data = $allUserCourses->map(function ($item, $key) use ($data) {
                        $data['expire_at'] = $item->expire_at;
                        $data['course_ids'] = $item->coursePackages->pluck('course_id')->toArray();
                        return $data;
                    })->toArray();
                }

                $purchasedCourses = UserCourse::where('learner_id', $id)
                    ->where('course_id', $course->id)
                    // ->groupBy('learner_id')
                    ->orderBy("id","desc")
                    ->first();

                $arr = \Arr::flatten($data);
                if (isset($data) && !empty($data)) {
                    foreach ($data as $k => $value) {
                        if (!in_array($course->id, $arr) && empty($purchasedCourses)) {

                            abort(403);
                        }
                    }
                } else {
                    if (!empty($purchasedCourses)) {
                        $expireFlag = is_expired($purchasedCourses->expire_at);
                        if ($expireFlag == 0) {
                            abort(403);
                        }
                    } else {
                        abort(403);
                    }
                }
            }
        } else {
            abort(403);
        }


        $wishlists = [];
        $chapterData = collect();
        // dd($chapterData);
        $chapter = [];
        // dd($course->id);
        $courseChapters = Chapter::with(['userCourseProgress:id,chapter_id,is_completed,watched_time'])->getCourseChapters($course->id)->get();

        $my_array = [];
        $courseChapters->map(function ($value, $key) {
            $value->childrens = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')->where('parent_id', $value->id)
                ->with(['userCourseProgress' => function (Builder $query) {
                    $query->select('id', 'chapter_id', 'is_completed', 'watched_time');
                    $query->where('learner_id', Auth::guard('learner')->id());
                }])->whereNull('deleted_at')->orderBy('order', 'asc')
                ->whereDoesntHave("getUserCourse",function($q)use($value){
                    $q->where("course_id","!=",$value->title);
                    $q->where("learner_id",@auth()->guard("learner")->user()->id);
                })
                ->get();
            // $my_array[] = $value->childrens->id;

        });

        // dd($courseChapters->toArray());
        $autoplay = ChapterInfo::select('autoplay')->get()->toArray();
        // dd($autoplay);
        if ($chapterID > 0) {
            // dd($chapterID);

            $chapterData = ChapterInfo::where('chapter_id', $chapterID)->whereNull('deleted_at')->first();
            // $data['firstData'] = ChapterInfo::where('chapter_id', $chapterID)->whereNull('deleted_at')->first();
            // $data['lastData'] = $chapterData->chapter_id;

            // dd(collect($chapterData)->latest);
            // dd($chapterData->chapter_id);
            // $chapter = Chapter::with('media:id,path,videoId')->with(['userCourseProgress:id,learner_id,chapter_id,is_completed,watched_time'])->where('id', $chapterID)->whereNull('deleted_at')->get();
            if (Auth::guard('learner')->check()) {
                $id = Auth::guard('learner')->user()->id;
                $chapter = Chapter::with([
                    'media:id,path,videoId',
                    'userCourseProgress' => function ($query) use ($id) {
                        $query->select('id', 'learner_id', 'chapter_id', 'is_completed', 'watched_time')
                            ->where('learner_id', $id); // Add your condition here
                    },
                ])
                    ->where('id', $chapterID)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
            } else {
                $chapter = Chapter::with([
                    'media:id,path,videoId',
                ])
                    ->where('id', $chapterID)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
            }
            // dd($courseChapters->toArrau)
        }

        /*if (Auth::guard('learner')->user()) {
            // dD($course->course_finished);
            if ($course->course_finished != null) {
                $chapter_watch_whole = Chapter::where("course_id", $course->id)
                    ->where("parent_id", "!=", 0)
                    ->where("asset_type", "!=", 7)
                    ->where("asset_type", "!=", 4)
                    ->pluck('id');

                $usercousreprogress = UserCourseProgress::whereIn("chapter_id", $chapter_watch_whole)->where("learner_id", @Auth::guard('learner')->user()->id)->where("is_completed", 1)->get();

                if ($usercousreprogress->count() == $chapter_watch_whole->count()) {

                    if ($usercousreprogress) {

                        $check_user = UserCoin::where("type", 7)->where("course_id", $course->id)->get();
                        if ($check_user->count() == 0) {
                            $user_coin = UserCoin::create([
                                "learner_id" => @Auth::guard('learner')->user()->id,
                                "course_id" => $course->id,
                                "coins" => $course->course_finished,
                                "type" => 7,
                                "comment" => "Congratulations, You have got $course->course_finished coins to successfully completed to $course->title course",
                            ]);
                            // $plus = UserCoin::where("type","!=",2)->where("type","!=",2)->sum("coins");

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
                            $content = "Congratulations, You have got $course->course_finished_day coins to successfully.";

                            $inst = new EarnCoinMail($content, $subject, @Auth::guard('learner')->user(), $user_coin, $total,
                            $course->course_finished_day);
                            if (Auth::guard('learner')->user()) {

                                \Mail::to(@Auth::guard('learner')->user()->email)->send($inst);
                            }
                        }
                    }
                }
            }



            if ($course->course_finished_day != null && $course->days != null) {
                $course_days = $course->userCourseExpectedRelationship[0]->created_at;

                $currentDate = Carbon::now();
                $courseDate = Carbon::parse($course_days);

                if ($courseDate->diffInDays($currentDate) + 1 <= 2) {
                    $chapter_watch_whole = Chapter::where("course_id", $course->id)
                        ->where("parent_id", "!=", 0)
                        ->where("asset_type", "!=", 7)
                        ->where("asset_type", "!=", 4)
                        ->pluck('id');

                    $usercousreprogress = UserCourseProgress::whereIn("chapter_id", $chapter_watch_whole)->where("learner_id", @Auth::guard('learner')->user()->id)->where("is_completed", 1)->get();

                    if ($usercousreprogress->count() == $chapter_watch_whole->count()) {


                        if ($usercousreprogress) {
                            $check_user = UserCoin::where("type", 8)->where("course_id", $course->id)->get();
                            if ($check_user->count() == 0) {
                                $user_coin = UserCoin::create([
                                    "learner_id" => @Auth::guard('learner')->user()->id,
                                    "course_id" => $course->id,
                                    "coins" => $course->course_finished_day,
                                    "type" => 8,
                                    "comment" => "Congratulations, You have got $course->course_finished_day coins to successfully. completed to $course->title course",
                                ]);

                                // $plus = UserCoin::where("type","!=",2)->where("type","!=",2)->sum("coins");
                                // $deduct = UserCoin::where("type",2)->Orwhere("type",4)->sum("coins");

                                $plus = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)
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
                                $content = "Congratulations, You have got $course->course_finished_day coins to successfully.";

                                $inst = new EarnCoinMail($content, $subject, Auth::guard('learner')->user(), $user_coin, $total, $course->course_finished_day);
                                if (Auth::guard('learner')->user()) {
                                    \Mail::to(@Auth::guard('learner')->user()->email)->send($inst);
                                }
                            }

                        }
                    }
                }

            }*/

        // }
        $browserName = Agent::browser(); // Chrome
        $browserVersion = Agent::version($browserName); //109.0.0.0
        $platform = Agent::platform(); // Windows // LINUX
        $divice_name = $browserName . " " . $browserVersion . " " . $platform;

        // LearnerLog::create([
        //     "learner_id" => @Auth::guard('learner')->user()->id,
        //     'device_id' => "",
        //     'device_token' => null,
        //     'device_name' => $divice_name,
        //     'description' => "Course viewed",
        //     'course_id' => $course->id,
        // ]);

        // dd($courseChapters);
        return view('front/course/course_preview', compact('course', 'courseChapters', 'chapter', 'chapterData', 'slug', 'chapterID', 'autoplay'));
    }

    public function getCoursePlanListForPopup($courseId)
    {

        // $course = CoursePlan::with('course:id,title')->where("status",1)
        //                         ->where('course_id', $courseId)
        //                         // ->where('is_fixed_date', 1)->whereDate('access_value', '=<', Carbon::now())
        //                         // ->orWhereNull('access_value')->orWhere('access_value', '')

        //                         return $query->where(function ($query) {
        //                             $query->where('is_fixed_date', 1)->whereDate('access_value', '>=', Carbon::now());
        //                         })->orWhere(function ($query) {
        //                             $query->where('is_fixed_date', 2)->where(function ($query) {
        //                                 $query->whereDate('access_value', '>=', Carbon::now())
        //                                     ->orWhere(function ($query) {
        //                                         // This will fetch plans where access_value is an integer (specific days)
        //                                         $query->whereNotNull('access_value')->where('access_value', '>', 0);
        //                                     });
        //                             });
        //                         })->orWhereNull('access_value')->orWhere('access_value', '')

        //                         ->select('id', 'course_id', 'plan_type', 'plan_name', 'order', 'list_price','final_payable_price','course_limit','is_fixed_date','access_value','bill_learner_every','calendar','setup_fee','is_trial_fee_included', 'status')
        //                         ->groupBy('id')
        //                         ->get();

        $course = Course::select('courses.id', 'courses.title')
            ->where('courses.id', $courseId)
            ->with([
                'plans' => function ($query) {
                    return $query->where(function ($query) {
                        $query->where('status', 1);
                        $query->where(function ($query) {
                            $query->where('course_limit', 1)->where('is_fixed_date', 1)->whereDate('access_value', '>=', Carbon::now());
                        })->orWhere(function ($query) {
                            $query->where('course_limit', 1)->where('is_fixed_date', 2)->where(function ($query) {
                                $query->whereDate('access_value', '>=', Carbon::now())
                                    ->orWhere(function ($query) {
                                        // This will fetch plans where access_value is an integer (specific days)
                                        $query->whereNotNull('access_value')->where('access_value', '>', 0);
                                    });
                            });
                        })->orWhereNull('access_value')->orWhere('access_value', '');
                    })->orderBy('order', 'Asc')->where('status', 1);
                },
            ])
            ->firstOrFail();

        // $plus = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)->where("type","!=",2)->where("type","!=",4)
        // // ->get();
        // ->sum("coins");
        // $deduct = UserCoin::where("learner_id",@auth()->guard("learner")->user()->id)->where("type",2)->Orwhere("type",4)->sum("coins");

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

        // $current_date = date('Y-m-d');
        // $coupons = Coupon::whereJsonContains('courses', $courseId)
        // ->where('expiry_date', '>=', $current_date)
        // ->where('status', 1)
        // ->get();

        $coupons = get_coupon_by_learner(@auth()->guard("learner")->user()->id, $courseId);


        $content = view('front.course.course_plan', compact('course', 'total', 'coupons'))->render();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'content' => $content,
        ], 200);
    }
    /**
     * all chapter Save and Continue
     */
    public function completeAndcontinue(Request $request)
    {
        if (!auth()->guard('learner')->check()) {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "You are not logged..";
            return redirect()->back()->with('notification', $notification);
        }
        if (request()->has('chapter_id_hidden')) {
            $userCourseProgress = UserCourseProgress::where('learner_id', auth()->guard('learner')->id())->where('chapter_id', $request['chapter_id_hidden'])->first();
            if (isset($userCourseProgress) && !empty($userCourseProgress)) {
                if ($userCourseProgress->is_completed == 1) {
                    $userCourseProgress->watched_time = $request['watched_time'];
                } else {
                    $userCourseProgress->watched_time = $request['watched_time'];
                    $userCourseProgress->is_completed = 1;
                }
                $userCourseProgress->save();
            } else {
                $userCourseProgress = UserCourseProgress::updateOrCreate(
                    [
                        'learner_id' => auth()->guard('learner')->id(),
                        'chapter_id' => $request['chapter_id_hidden'],
                    ],
                    [
                        'watched_time' => $request['watched_time'],
                        'is_completed' => 1,
                        'updated_at' => now()
                    ]
                );
            }

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Successfully Completed";
            return redirect()->back()->with('notification', $notification);
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "error";
        $notification['title'] = "Error";
        $notification['msg'] = "Already watched";
        return redirect()->back()->with('notification', $notification);

    }

    /**
     * Youtube/Vimeo Video tracker
     */
    public function videoEventTracker(Request $request)
    {
        if (!auth()->guard('learner')->check()) {
            return false;
        }
        if (request()->has('chapterId') && request()->has('watchedTime')) {
            $userCourseProgress = UserCourseProgress::where('learner_id', auth()->guard('learner')->id())->where('chapter_id', $request['chapterId'])->first();
            if (isset($userCourseProgress) && !empty($userCourseProgress)) {
                if ($userCourseProgress->is_completed == 1) {
                    $userCourseProgress->watched_time = $request['watchedTime'];
                } else {
                    $userCourseProgress->watched_time = $request['watchedTime'];
                    $userCourseProgress->is_completed = $request['isCompleted'];
                }
                $userCourseProgress->save();
            } else {
                $userCourseProgress = UserCourseProgress::updateOrCreate(
                    [
                        'learner_id' => auth()->guard('learner')->id(),
                        'chapter_id' => $request['chapterId'],
                    ],
                    [
                        'watched_time' => $request['watchedTime'],
                        'is_completed' => $request['isCompleted'],
                        'updated_at' => now()
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Success',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Already watched',
        ], 400);

    }

    public function cancel_subscription($id = null)
    {
        if (!empty($id)) {
            $commonController = new CommonController();
            $commonController->cancelSubscription($id);

            return redirect()->back();
        }
    }

    public function downloadPdf(Request $request)
    {
        return Response::download($request->pdf);
        // dd($request->pdf);
        // $pdf = PDF::loadView($request->pdf); // Load the PDF view using dompdf
        // return response()->download($pdf);
        // $headers = ['Content-Type: application/pdf'];
        // $newName = 'itsolutionstuff-pdf-file-'.time().'.pdf';
        // return response()->download($request->pdf, $newName, $headers);
        // return Response::download($request->pdf);
        // $path = public_path('pdf/');
        // $fileName =  time().'.'. 'pdf' ;
        // $pdf->save($path . '/' . $fileName);

        // $pdf = public_path('pdf/'.$fileName);
        // return response()->download($pdf);
        // return $pdf->download('document.pdf');

        // $pdf = PDF::loadView($request->pdf);
    }
    public function userCourseUpdate(Request $request)
    {

        UserCourseProgress::where('id', $request->user_course_id)->update(['is_completed' => 1]);
        return "sucesss";
    }
}
