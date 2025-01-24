<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Mail\DeleteLearnerMail;
use App\Models\Blog;
use App\Models\Countries;
use App\Models\Course;
use App\Models\Dropdown;
use App\Models\DropdownOption;
use App\Models\Learner;
use App\Models\RatingReview;
use App\Models\Slider;
use App\Models\States;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $deviceType = Course::COURSE_WEBSITE;
        $slider = Slider::whereNull('deleted_at')->where('slug', 'home')->first();
        $categories = DropdownOption::getDropdownCategories('course_category')
            ->select('dropdown_options.id', 'dropdown_options.image', 'dropdown_options.name', 'dropdown_options.home', DB::raw('COUNT(c.id) as total_course'), DB::raw('GROUP_CONCAT(c.id) as total_course_list'))
        // ->with(['courses']['courses.courseCategory'])
        // ->with(['courses' => function ($query) {
        //     $query->leftJoin('course_plans as cp','cp.id','=','default_web_price');
        //     $query->where('courses.status', '1');
        //     $query->where('cp.status','1');
        //     // $query->whereNotNull('courses.default_web_price');
        //     // $query->whereNull('courses.deleted_at');
        //     // $query->groupBy('courses.id');
        //     return $query;
        // }])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->leftJoin('course_categories as cc', 'cc.category_id', '=', 'dropdown_options.id')
            ->leftJoin('courses as c', 'c.id', 'cc.course_id')
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'c.default_web_price')
            ->whereNotNull('c.default_web_price')
            ->where('cp.status', '1')
            ->where('c.status', '1')
            ->whereNull('c.deleted_at')
            ->where('dropdown_options.status', true)
            ->where('dropdown_options.home', true)
            ->groupBy('dropdown_options.id')
            ->get();

        // $categories = $categories->filter(function ($value, $key) {
        //     if(count($value->plans) > 0){
        //         return true;
        //     }
        // });
        // $affected = DB::table('course_plans')
        // ->where('status', 1)->where('is_fixed_date', 1)->whereDate('access_value', '<', Carbon::now())->update(['status' => 0]);
        // dd($affected);
        // ->update(['status1' => 0]);
        // if($categories->count() > 0) {
        //     foreach
        // }
        // $categories = $categories->filter(function ($value, $key) {
        //         if (count($value->plans) <= 0) {
        //             return false;
        //         } else {
        //             return true;
        //         }
        // });
        // dd($categories);
        // if (Course::sum('order') <= 0) {
        //     $orderby = "courses.id";
        //     $dir = "DESC";
        // } else {
        //     $orderby = DB::raw('ISNULL(courses.order), courses.order');
        //     $dir = "ASC";
        // }
        $blogs = Blog::whereRelation('blogCategoryOptions', 'status', true)
            ->where('home', true)
            ->where('status', true)
            ->orderBy('created_at', 'DESC')
            ->get();
        $blogs = $blogs->transform(function ($value, $key) {
            $value->date = date('M d, Y', strtotime($value->created_at));
            return $value;
        });
        $deviceType = Course::COURSE_WEBSITE;
        // $topFreeCourse = Course::
        //     //whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
        //     whereRaw("find_in_set($deviceType , course_platform)")
        //     ->with(['instructor' => function ($query) {
        //         $query->select('name', 'email', 'id', 'profile_picture');
        //     }, 'categories' => function ($query) {
        //         $query->whereNull('course_categories.deleted_at');
        //     }, 'wishlists'])
        //     ->with('instructor.instructure')
        //     ->with("coursePlan")
        //     ->whereHas('coursePlan', function ($query) {
        //         return $query->where('status', 1)->where('plan_type', 0);
        //     })
        //     ->withCount([
        //         'chapters' => function ($query) {
        //             $query->where('parent_id', 0);
        //         },

        //         'packages',
        //         'rating_reviews as total_review'
        //     ])
        //     ->leftJoin('course_categories', 'courses.id', '=', 'course_categories.course_id')
        //     ->withAvg('rating_reviews as AverageRating', 'rating')
        //     ->whereRelation('categories', 'status', true)
        //     // ->leftJoin('rating_reviews as rv','rv.course_id','=','courses.id')
        //     ->whereNull('courses.deleted_at')
        //     ->where('is_free', 1)
        //     ->where('courses.status', 1)
        //     // ->whereNull('rv.deleted_at')
        //     // ->orWhere('rv.is_approve','1')
        //     ->groupBy('courses.id')
        //     // ->latest()
        //     ->orderBy($orderby, $dir)
        //     ->get();
        //     // dd($topFreeCourse->toArray());
        // // $topFreeCourse = $topFreeCourse->sortBy(fn($e) => $e->order ?: PHP_INT_MAX);
        // $topFreeCourse = $topFreeCourse->filter(function ($value, $key) {
        //     if (count($value->plans) <= 0) {
        //         return false;
        //     } else {
        //         return true;
        //     }
        // });
        // \DB::enableQueryLog();

        $topFreeCourse = Course::select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'categories' => function ($query) {
                $query->whereNull('course_categories.deleted_at');
            }, 'wishlists', 'plans' => function ($query) {
                return $query->where('status', 1);
            }])
            ->with('instructor.instructure')
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0);
                },
                'rating_reviews as total_review',
                'packages',
            ])->whereHas("user")
            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
        //->leftJoin('rating_reviews as rv','rv.course_id','=','courses.id')
            ->where('courses.is_free', 1)
            ->where('courses.status', '1')
            ->where('cp.status', '1')
            ->whereNull('courses.deleted_at')
            ->orderByRaw('CASE WHEN `courses`.`order` IS NOT NULL THEN 0 ELSE 1 END')
        //->whereNull('rv.deleted_at')
        // ->orWhere('rv.is_approve','1')
        // ->orderBy('courses.order', 'ASC')
        // ->orderBy($orderby, $dir)
        ->groupBy('courses.id')
        ->orderBy('courses.id', 'DESC')
            ->get();
        // $topFreeCourse = $topFeatureCourse->sortBy(fn($e) => $e->order ?: PHP_INT_MAX);
        $topFreeCourse = $topFreeCourse->filter(function ($value, $key) {
            if (count($value->plans) <= 0) {
                return false;
            } else {
                return true;
            }
        });

        $deviceType = Course::COURSE_WEBSITE;
        $topFeatureCourse = Course::select('courses.*', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
        // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'categories' => function ($query) {
                $query->whereNull('course_categories.deleted_at');
            }, 'wishlists', 'plans' => function ($query) {
                return $query->where('status', 1);
            }])
            ->whereHas("user")
            ->with('instructor.instructure')
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0);
                },
                'rating_reviews as total_review',
                'packages',
            ])
            ->withAvg('rating_reviews as AverageRating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->leftJoin('course_plans as cp', 'cp.id', '=', 'courses.default_web_price')
        //->leftJoin('rating_reviews as rv','rv.course_id','=','courses.id')
            ->where('courses.is_featured', 1)
            ->where('courses.status', '1')
            ->where('cp.status', '1')
            ->whereNull('courses.deleted_at')
        //->whereNull('rv.deleted_at')
        // ->orWhere('rv.is_approve','1')
        // ->orderBy('courses.order', 'ASC')
        // ->orderBy($orderby, $dir)
        ->orderByRaw('CASE WHEN `courses`.`order` IS NOT NULL THEN 0 ELSE 1 END')
        ->groupBy('courses.id')
        ->orderBy('courses.id', 'DESC')
            ->get();
        // $topFeatureCourse = $topFeatureCourse->sortBy(fn($e) => $e->order ?: PHP_INT_MAX);
        $topFeatureCourse = $topFeatureCourse->filter(function ($value, $key) {
            if (count($value->plans) <= 0) {
                return false;
            } else {
                return true;
            }
        });
        // echo "<pre>";print_r($topFeatureCourse);die;
        // dd(\DB::getQueryLog());
        $wishlist = [];
        if (isLearnerLoggedIn()) {
            $wishlists = Wishlist::where('learner_id', authLearnerID())->whereNull('deleted_at')->get();
        }
        $trusted_by = Dropdown::where('slug', 'trusted_by')->with(['dropdownOptions' => function ($query) {
            $query->where('status', true);
        }])->where('status', true)->first();
        if (!$slider->autoplay) {
            $slider->load(['slides' => function ($query) {
                return $query->first();
            }]);
        } else {
            $slider->load(['slides']);
        }
        $review = RatingReview::where("home_status", 1)->with("learner:id,name,profile_pic", "course:id,title")->get();
        // $this->sessionDestroy();
        // $notification['type'] = "sweet-alert";
        // $notification['status'] = "error";
        // $notification['title'] = "Error";
        // $notification['msg'] = "Payment Failed";

        if (view()->exists('front.index')) {
            return view('front.index', compact('slider', 'categories', 'blogs', 'wishlist', 'topFreeCourse', 'topFeatureCourse', 'trusted_by', "review"));
            //->with('notification', $notification);;
        }

        abort(404);
    }

    public static function sessionDestroy()
    {
        session()->forget('sessionAddress');
    }

    public function DeleteLearner(Request $request)
    {

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        $clientIp = $request->ip();
        $browser = $agent->is('AndroidOS') || $agent->is('iOS') ? 'App' : $agent->browser();
        $platform = $agent->platform();
        $currentDate = Carbon::now()->toDateTimeString();

            Log::info('Delete learner access:', [
                'Date' => $currentDate,
                'IP Address' => $clientIp,
                'Browser/App' => $agent->is('AndroidOS'),
                'Platform' => $platform,
                'Country Name' => $request->input('country_name'),
                'Mobile' => $request->input('mobile'),
            ]);

        return response()->json("Your account will be deleted after 15 days");

    }
    public function mailDeleteLearner(Request $request)
    {

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        $clientIp = $request->ip();
        $browser = $agent->is('AndroidOS') || $agent->is('iOS') ? 'App' : $agent->browser();
        $platform = $agent->platform();
        $currentDate = Carbon::now()->toDateTimeString();

        // if ($agent->is('iOS')) {

        //     Log::info('Delete learner access:', [
        //         'Date' => $currentDate,
        //         'IP Address' => $clientIp,
        //         'Browser/App' => $browser,
        //         'Platform' => $platform,
        //         'Country Name' => $request->input('country_name'),
        //         'Mobile' => $request->input('mobile'),
        //     ]);
        // }

        return response()->json("Your account will be deleted after 15 days");
        // return view('front/test');
        $get_learner = Learner::with('country:id,phonecode')->where('mobile', $request->learner_number)->first();

        if ($get_learner) {
            $users = User::role('admin')->select(['id', 'name', 'email'])->get();
            $subject = "You have a learner account deletion request to process";
            $sender = env('MAIL_FROM_ADDRESS') ? env('MAIL_FROM_ADDRESS') : "noreply@lifegurukul.com";

            $url = route('learners.index', ['mobile_no' => $get_learner->mobile]);

            if (isset($users) && !empty($users)) {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(new DeleteLearnerMail($user, $sender, $get_learner, $url, $subject));
                }
            }
        }
    }

    public function countryCodeUpdate()
    {
        $statesWithNoCities = States::where('country_id', 1)->whereDoesntHave('cities')->delete();

        // $states = [];
        // foreach ($statesWithNoCities as $state) {
        //     $states[] =  $state->name;
        // }

        // dd($states);

        $cArray = ["8" => "672", "29" => "47", "78" => "262", "96" => "672", "174" => "64", "203" => "500"];

        foreach ($cArray as $key => $value) {
            $country = Countries::findOrFail($key);
            if (!empty($country)) {
                $country->phonecode = $value;
                $country->save();
            }
        }
        dd('Done');
    }
}
