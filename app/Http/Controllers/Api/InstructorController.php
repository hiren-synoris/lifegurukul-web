<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Models\Course;
use App\Models\Instructors;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    public function index(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'page' => 'nullable',
            'per_page' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        // DB::enableQueryLog();
        $instructors = Instructors::join('users', 'instructors.user_id', '=', 'users.id')
        ->distinct()
        // ->whereNotNull("deleted_at")
        ->select('users.id', 'users.name', 'users.profile_picture', 'instructors.designation')->orderBy("instructors.id","desc")
        ->get();
        //->paginate($per_page, ['*'], 'page', $page_num);
        $instructors = cpaginate($instructors, $per_page, $page_num);
        // dd(DB::getQueryLog());
        foreach ($instructors as $inst) {
            //  echo $inst->user->name; die;
            // $inst->name = $inst->name ?? ' ';
            $inst->profile_picture = !empty($inst->profile_picture) ? Helper::getImageUrl($inst->profile_picture) : '';
            unset($inst->user);
        }

        $is_paginated = TRUE;
        // dd($instructors);die;
        $data = Helper::apiResonse(1, "Success", $instructors, $is_paginated);
        return response()->json($data, 200);
    }

    public function instuctorDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instructor_id' => 'required|exists:instructors,user_id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        // dd($request->all());
        $deviceType = Helper::getDeviceType($request->devicetype);
        $price_flt = [];
        $current_price_list = $request->pricelist;
        $prev_price_list = NULL;
        if (!empty($request->pricelist)) {
            $prev_price_list = explode(",", $request->pricelist);
            if (count($prev_price_list) > 0) {
                foreach ($prev_price_list as $key => $value) {
                    $temp = explode("_", $value);
                    $price_flt[$key]['min'] = $temp[0];
                    $price_flt[$key]['max'] = $temp[1];
                }
            }
        }
        $instructor = Instructors::select('id', 'user_id', 'designation', 'instagram_follower', 'facebook_follower', 'twitter_follower', 'youtube_follower', 'bio as about')
            ->with(['course_reviews' => function ($query) {
                $query->select('id', 'course_id', 'rating')->where('is_approve', true);
            }])
            ->withCount('ratingReviews as total_review')
            ->withAvg('ratingReviews as rating','rating')
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'profile_picture');
            }])
            ->where('user_id', $request->instructor_id)
            ->first();

        $course = array();
        $total_cnt = $instructor->count();

        if ($total_cnt > 0) {
            // foreach($instructors as $instructor){
            $instructor->id = $instructor->user_id;
            unset($instructor->user_id);
            $instructor->name = $instructor->user->name ?? '';
            $instructor->profile_picture = !empty($instructor->user->profile_picture) ? Helper::getImageUrl($instructor->user->profile_picture) : '';
            $instructor->total_review = isset($instructor->total_review) && !empty($instructor->total_review) && $instructor->total_review > 0 ? $instructor->total_review : 0;
            $instructor->rating = isset($instructor->rating) && !empty($instructor->rating) && $instructor->rating > 0 ? $instructor->rating : 0;

            unset($instructor->user, $instructor->course_reviews);
            $course = collect([]);

            if (Course::sum('order') <= 0) {
                $orderby = "courses.id";
                $dir = "DESC";
            } else {
                $orderby = DB::raw('ISNULL(courses.order), courses.order');
                $dir = "ASC";
            }

            // $courses =
            //  Course::
            // whereRaw("find_in_set($deviceType ,course_platform)")->
            // distinct()
            // ->select('courses.id', 'title', 'instructor_id', 'image', 'type', 'user_courses.id as user_courses_id', 'user_courses.expire_at', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'user_courses.id as course_purchase_id', 'user_courses.created_at','cp.plan_type')
            // // ->whereIn('course_platform', [$deviceType,4])

            //     ->with(['categories' => function ($query) {
            //         $query->select('dropdown_options.id', 'dropdown_options.name');
            //     }])
            //     ->with(['plans' => function ($query) {
            //         // return $query->where('status', 1);
            //     }])
            //     ->with(['userCourseExpectedRelationship' => function($query){
            //         $query->where('learner_id', request()->user_id);
            //     }])
            //     ->with(['chapters' => function ($query) {
            //         $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            //     }])
            //     ->withCount(['chapters' => function ($query) {
            //         $query->where('parent_id', 0);
            //     }])
            //     ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            //     ->where('courses.status', 1)
            //     ->withAvg('rating_reviews as rating', 'rating')
            //     ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
            //     ->whereNull('courses.deleted_at')
            //     ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            //     ->where('cp.status', '1')
            //     ->whereNotNull('courses.' . $request->device_price_field)
            //     ->where('instructor_id', $request->instructor_id)
            //     ->groupBy('user_courses.course_id')
            //     ->orderBy($orderby,$dir)
            //     ->get();
            $courses =
             Course::
            whereRaw("find_in_set($deviceType ,course_platform)")->
            distinct()
            ->select('courses.id', 'title', 'instructor_id', 'image', 'type', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')


                ->with(['categories' => function ($query) {
                    $query->select('dropdown_options.id', 'dropdown_options.name');
                }])
                ->with(['plans' => function ($query) {
                    // return $query->where('status', 1);
                }])
                ->with(['userCourseExpectedRelationship' => function($query){
                    $query->where('learner_id', request()->user_id);
                }])
                ->with(['chapters' => function ($query) {
                    $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                }])
                ->withCount(['chapters' => function ($query) {
                    $query->where('parent_id', 0);
                }])
                ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                ->where('courses.status', 1)
                ->withAvg('rating_reviews as rating', 'rating')
                // ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
                ->whereNull('courses.deleted_at')
                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                ->where('cp.status', '1')
                ->whereNotNull('courses.' . $request->device_price_field)
                ->where('instructor_id', $request->instructor_id)
                // ->groupBy('user_courses.course_id')
                ->orderBy($orderby,$dir)
                ->get();

                // dd($courses->toArray());
                $courses = $courses->filter(function ($value, $key) {
                return  count($value->plans) > 0;
            })->values();
            $course_total_cnt = $courses->count();
            $instructor->course_count = $courses->count();
            if (!empty($course_total_cnt)) {
                foreach ($courses as $course) {
                    // dd($course);
                    if (!empty($course->chapters->first()) && !empty($course->chapters->first()->chapterIds)) {
                        $chapterIds = explode(",", $course->chapters->first()->chapterIds);
                        $totalChapters = count($chapterIds);
                        $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                            ->where("learner_id", $request->user_id)
                            ->where("is_completed", 1)
                            ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                            ->where('chapters.asset_type', '!=', 4)
                            ->where('chapters.asset_type', '!=', 7)
                            ->pluck('watched_times')
                            ->first();
                        $course->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                    }else{
                        $course->percentage_completed = 0;
                    }
                    $expireFlag = isset($course->userCourseExpectedRelationship[0]->expire_at) ? is_expired($course->userCourseExpectedRelationship[0]->expire_at) : '';
                    // dd($expireFlag);

                    $course->is_expire = false;
                    if($expireFlag == 0) {
                        $course->is_expire = true;
                    } else if($expireFlag == 1) {
                        $course->is_expire = false;
                    } else if($expireFlag == 2) {
                        $course->is_expire = false;
                    }
                    $course->course_id = $course->id;
                    $course = Helper::get_course_common_api_data($course, $request);
                    $course->isCombineCourse = $course->type == '1' ? FALSE : true;
                    unset($course->plans, $course->categories, $course->course_category, $course->rating_reviews, $course->wishlists, $course->userCourseExpectedRelationship);
                }
            }
            $instructor->course = $courses;
            // $instructor->profile_pic = !empty($instructor->profile_pic) ? Helper::getImageUrl($instructor->profile_pic) : '';
            // }
            $data = Helper::apiResonse(1, "Success",  $instructor);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
}
