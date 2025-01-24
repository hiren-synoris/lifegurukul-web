<?php

namespace App\Http\Controllers\Api;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Media;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Learner;
use App\Models\Settings;
use App\Models\UserCoin;
use App\Models\Wishlist;
use App\Mail\EarnCoinMail;
use App\Models\LearnerLog;
use App\Models\UserCourse;
use App\Models\Instructors;
use Illuminate\Http\Request;
use App\Models\CoursePackage;
use App\Models\DropdownOption;
use App\Models\SellCourseTimer;
use App\Models\UserCourseProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\GetUserCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\front\CommonController;
use App\Models\RatingReview;

class CourseController extends Controller
{

    public function index(Request $request)
    {

        // dD("");
        $validator = Validator::make($request->all(), [
            'categorylist' => 'nullable|string',
            'instructurelist' => 'nullable|string',
            'rating' => 'nullable|string',
            'pricelist' => 'nullable|string',
            'page' => 'nullable',
            'per_page' => 'nullable',
            'sort' => 'nullable',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $deviceType = Helper::getDeviceType($request->devicetype);
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $price_flt = [];
        $current_price_list = isset($request->pricelist) && !empty($request->pricelist) ? $request->pricelist : '';

        $prev_price_list = null;
        $orderby ="";
        $dir = "";
        
        if (isset($request->pricelist) && !empty($request->pricelist)) {
            $prev_price_list = explode(",", $request->pricelist);
            $temp = explode("_", $request->pricelist);
            $price_flt['min'] = $temp[0];
            $price_flt['max'] = $temp[1];
        }
        if (request()->has('sort') && $request->sort == "date_asc") {
            $orderby = "courses.created_at";
            $dir = "asc";
            $flag = 1;
            
        } elseif (request()->has('sort') && $request->sort == "date_desc") {
            $orderby = "courses.created_at";
            $dir = "desc";
            $flag = 1;
        } elseif (request()->has('sort') && $request->sort == "price_desc") {
            $orderby = "cp.final_payable_price";
            $dir = "desc";
            $flag = 1;
        } elseif (request()->has('sort') && $request->sort == "price_asc") {
            $orderby = "cp.final_payable_price";
            $dir = "asc";
            $flag = 1;
        } elseif (request()->has('sort') && $request->sort == "rating_desc") {
            $orderby = "rating";
            $dir = "desc";
            $flag = 1;
        }
        // else {
        //     // if (Course::sum('order') <= 0) {
        //     //     $orderby = "courses.id";
        //     //     $dir = "DESC";
        //     // } else {
        //     //     $orderby = DB::raw('ISNULL(courses.order), courses.order');
        //     //     $dir = "ASC";
        //     // }
        //     $orderby = "courses.id";
        //     $dir = "DESC";
        // }

        // print_r($orderby);
        // exit;
        $device_price_field = ($deviceType == 1 ? 'default_android_price' : ($deviceType == 2 ? 'default_iphone_price' : ''));
        $courses = Course::select('courses.id', 'courses.title', 'courses.instructor_id', "courses.image", "courses.type", "courses.lng", "courses.hours", "courses.minutes","courses.order", 'cp.id as planId', 'cp.course_id', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
            ->distinct()
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with([
                // 'categories' => function ($query) use ($request) {
                //     $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                // },
                'wishlists' => function ($query) {
                    $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                },
            ])
            ->with(['chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                },
                'rating_reviews as total_review' => function ($query) {
                    $query->where('rating', '!=', null);
                },
                'packages',
                'userCourse as total_enroll',
            ])
            ->with([
                'userCourseExpectedRelationship' => function ($query) {
                    $query->where('learner_id', request()->user_id);
                },
            ])
            ->withAvg(
                [
                    'rating_reviews as rating' => function ($query) use ($request) {
                        if (request()->has('rating') && !empty($request->rating)) {
                            $query->whereIn('rating', stringToArray($request->rating))->where('rating', '!=', null);
                        }
                    },
                ],
                'rating'
            )
            ->when(request()->has('pricelist') && !empty($request->pricelist), function ($query) use ($request, $price_flt) {

                if (count($price_flt) > 0) {
                    $temp2 = $price_flt['min'];
                    $temp3 = $price_flt['max'];
                    if ($temp2 == 0 && $temp3 == 0) {
                        $query->where('cp.plan_type', 0);
                    } else {
                        $query->whereBetween('cp.final_payable_price', [$temp2, $temp3]);
                    }
                    $query->where('cp.status', 1);
                }
            })
            ->when(request()->has('instructurelist') && !empty($request->instructurelist), function ($query) use ($request) {
                $query->whereIn('instructor_id', stringToArray($request->instructurelist));
            })
            ->when($orderby=="", function ($query) use ($request) {
                
                $query->orderByRaw('CASE WHEN `courses`.`order` IS NOT NULL THEN 0 ELSE 1 END');
                $query->orderBy('courses.id', 'DESC');
            })
            ->when($orderby!="", function ($query) use ($request,$orderby,$dir) {
                // dd($orderby, $dir);
                $query->orderBy($orderby, $dir);
                $query->orderBy('courses.id', 'DESC');
                
            })
            ->when(request()->has('categorylist') && !empty($request->categorylist), function ($query) use ($request) {
                // $query->whereIn('categcourse_categoryories', stringToArray($request->query('categorylist')));
                $categoryList = $request->categorylist;
                $query->whereHas('categories', function ($query) use ($categoryList) {
                    $query->whereIn('category_id', stringToArray($categoryList))->whereNull('course_categories.deleted_at');
                });
            })
            ->whereHas("user")
            ->leftJoin('course_plans as cp', 'cp.id', '=', $device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $device_price_field)
            ->where('courses.status', '1')
            ->whereNull('courses.deleted_at')
            ->get();

        // dd($courses->toArray());
        // dd(\DB::getQueryLog());
        // ->paginate($per_page, ['*'], 'page', $page_num);
        // $courses = cpaginate($courses, $per_page, $page_num);
        // if($orderby=="") {
        //     $courses = $courses->sortBy(function($e) {
        //         if (isset($e->order)) {
        //             return $e->order;
        //         } else {
        //             return PHP_INT_MAX;
        //         }
        //     });
        // }

        if (request()->has('rating') && !empty($request->rating)) {
            $courses = $courses->filter(function ($value, $key) {
                if ($value->rating != '') {
                    return $value;
                }
            });
        }


        $courses = $courses->filter(function ($value, $key) {
            return count($value->plans) > 0;
        })->values();

        $courses = cpaginate($courses, $per_page, $page_num);
        
        $total_cnt = $courses->count();
        if ($total_cnt > 0) {
            foreach ($courses as $i => $course) {
                $course = Helper::get_course_common_api_data($course, $request);

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
                } else {
                    $course->percentage_completed = 0;
                }
                $expireFlag = isset($course->userCourseExpectedRelationship[0]->expire_at) ? is_expired($course->userCourseExpectedRelationship[0]->expire_at) : '';
                // dd($expireFlag);

                $course->is_expire = false;
                if ($expireFlag == 0) {
                    $course->is_expire = true;
                } else if ($expireFlag == 1) {
                    $course->is_expire = false;
                } else if ($expireFlag == 2) {
                    $course->is_expire = false;
                }
                $course->course_list = new Collection([]);
                if ($course && isset($course->packages) && count($course->packages) > 0) {
                    $course->packages->map(function ($value, $key) use ($course, $request, $deviceType) {
                        $value->package = Course::select('courses.id', 'title', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
                            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                            ->where('cp.status', '1')
                            ->where('courses.id', $value->course_id)
                            ->withAvg('rating_reviews as rating', 'rating')
                            ->withCount('rating_reviews as total_review')
                            ->whereRaw("find_in_set($deviceType , course_platform)")
                            ->whereNotNull('courses.' . $request->device_price_field)->with([
                                'plans' => function ($query) {
                                    return $query->where('status', 1)->orderBy('order', 'Asc');
                                },
                            ])
                            // ->where('courses.status', '1')
                            ->first();
                        if (!empty($value->package)) {
                            $value->package->image = isset($value->package->image) && !empty($value->package->image) ? Helper::getImageUrl($value->package->image) : '';
                            unset($value->package->plans, $value->package->categories, $value->package->rating_reviews);
                            $course->course_list->push($value->package);
                        }
                    });
                    $course->packages_count = $course->course_list->count();



                }
                // $course->rating = 1;
                // $course->AverageRating = round($course->AverageRating);

                unset($course->rating_reviews, $course->instructor_id);
                unset($course->wishlists, $course->plans, $course->type, $course->userCourseExpectedRelationship);

            }

            // $courses = $courses->filter(function ($course) {
            //     return !$course->is_course_purchased;
            // })->values();

            // $data = Helper::apiResonse(1, "Success", ['data' => Helper::toStringRecursive($courses)], true);
            // $data = Helper::apiResonse(1, "Success", $courses, true);
            $is_paginated = TRUE;
            $data = Helper::apiResonse(1, "Success", $courses, $is_paginated);
            return response()->json($data, 200);

            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, 'No records found.', []);
            return response()->json($data, 200);
        }
    }

    public function getWishlist(Request $request)
    {

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $deviceType = Helper::getDeviceType($request->devicetype);
        $orderby = DB::raw('ISNULL(courses.order), courses.order');
        $dir = "ASC";
        // if ($is_featured_courses_visible) {
        DB::enableQueryLog();
        $topFeatureCourses = Course::select('courses.id', 'wishlists.id as wishlist_id', 'title', 'is_featured', 'instructor_id', 'type', 'hours', 'minutes', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
            //->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'wishlists' => function ($query) {
                    $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                },
                'plans',
            ])
            ->with('instructor.instructure')
            ->with([
                'userCourseExpectedRelationship' => function ($query) {
                    $query->where('learner_id', request()->user_id);
                },
            ])
            ->with(['chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                },
            ])
            // ->where('wishlists.learner_id', request()->user_id)
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->whereNull('courses.deleted_at')
            ->join('wishlists', 'courses.id', '=', 'wishlists.course_id')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $request->device_price_field)
            ->where('wishlists.learner_id', request()->user_id)
            ->where('courses.status', 1)
            //  ->where('type',Course::COURSE)
            ->orderBy($orderby, $dir)->get();
        $topFeatureCourses = $topFeatureCourses->filter(function ($value, $key) {
            return count($value->plans) > 0;
        })->values();
        // $topFeatureCourses = $topFeatureCourses->filter(function ($value, $key) {
        //     return count($value->wishlists) > 0;
        // })->values();

        $topFeatureCourses = cpaginate($topFeatureCourses, $per_page, $page_num);
        // dd($topFeatureCourses);
        // dd(DB::getQueryLog());

        // } else {
        //     $data = Helper::apiResonse(0, "No permission to view feature courses. Please contact Admininistor to enable the permission.", []);
        //     return response()->json($data, 400);
        // }
        $total_cnt = $topFeatureCourses->count();
        if ($total_cnt > 0) {

            if (isset($topFeatureCourses) && !empty($topFeatureCourses)) {
                foreach ($topFeatureCourses as $topFeatureCourse) {

                    if (!empty($topFeatureCourse->chapters) && !empty($topFeatureCourse->chapters->first()) && !empty($topFeatureCourse->chapters->first()->chapterIds)) {
                        $chapterIds = explode(",", $topFeatureCourse->chapters->first()->chapterIds);
                        $totalChapters = count($chapterIds);
                        $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                            ->where("learner_id", $request->user_id)
                            ->where("is_completed", 1)
                            ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                            ->where('chapters.asset_type', '!=', 4)
                            ->where('chapters.asset_type', '!=', 7)
                            ->pluck('watched_times')
                            ->first();
                        $topFeatureCourse->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                    } else {
                        $topFeatureCourse->percentage_completed = 0;
                    }
                    $expireFlag = isset($topFeatureCourse->userCourseExpectedRelationship[0]->expire_at) ? is_expired($topFeatureCourse->userCourseExpectedRelationship[0]->expire_at) : '';
                    // dd($expireFlag);

                    $topFeatureCourse->is_expire = false;
                    if ($expireFlag == 0) {
                        $topFeatureCourse->is_expire = true;
                    } else if ($expireFlag == 1) {
                        $topFeatureCourse->is_expire = false;
                    } else if ($expireFlag == 2) {
                        $topFeatureCourse->is_expire = false;
                    }

                    $topFeatureCourse = Helper::get_course_common_api_data($topFeatureCourse, $request);
                    $wishlist_id = $topFeatureCourse->wishlist_id;
                    $course_id = $topFeatureCourse->id;
                    $topFeatureCourse->id = $wishlist_id;
                    $topFeatureCourse->course_id = $course_id;
                    if (isset($topFeatureCourse->instructor) && !empty($topFeatureCourse->instructor)) {
                        $topFeatureCourse->instructor->profile_picture = !empty($topFeatureCourse->instructor->profile_picture) ? Helper::getImageUrl($topFeatureCourse->instructor->profile_picture) : '';
                    }
                    $topFeatureCourse->is_featured = $topFeatureCourse->is_featured == "1" ? true : false;
                    unset($topFeatureCourse->instructor_id, $topFeatureCourse->user);
                    unset($topFeatureCourse->plans, $topFeatureCourse->wishlists, $topFeatureCourse->userCourseExpectedRelationship, $topFeatureCourse->chapters, $topFeatureCourse->rating_reviews, $topFeatureCourse->learners_data, $topFeatureCourse->instructor, $topFeatureCourse->type, $topFeatureCourse->plans);
                }
            }
            $data = Helper::apiResonse(1, "Success", $topFeatureCourses, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 200);
        }
    }

    public function addWishlist(Request $request)
    {

        $learners = request()->user();
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            // 'isWishList' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $wishlist = Wishlist::where('course_id', $request->course_id)->where('learner_id', $learners->id)->get();
        // if($request->isWishList){
        if ($wishlist->count() < 1) {
            Wishlist::updateOrCreate(
                ['course_id' => $request->course_id, 'learner_id' => $learners->id],
                ['created_by' => $learners->id]
            );
            $data = Helper::apiResonse(1, "Successfully wishlisted", []);
            return response()->json($data, 200);
        } else {

            $data = Helper::apiResonse(1, "Wishlist already exist", []);
            return response()->json($data, 200);
        }
        // } else {
        //     //dd($wishlist);
        //     if($wishlist->count() > 0){
        //         $wishlist->each->delete();
        //         $data = Helper::apiResonse(1, "Wishlist removed Successfully", []);
        //         return response()->json($data, 200);
        //     }
        //     $data = Helper::apiResonse(1, "Wishlist removed Successfully", []);
        //     return response()->json($data, 200);

        // }
    }

    public function destroy(Request $request)
    {
        $learner_id = request()->user()->id;
        // echo $learner_id;die;

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $wishlist = DB::table('wishlists')->where('learner_id', $learner_id)->where('course_id', $request->course_id)
            ->delete();
        if ($wishlist) {
            $data = Helper::apiResonse(1, "Wishlist deleted successfully", []);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }

    public function courseCategories(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:dropdown_options,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $course_categories = DropdownOption::whereHas('dropdown', function ($query) {
            $query->where('slug', 'course_category');
        })
            ->select('id', 'image', 'name')
            ->where('status', true)
            ->orderBy("id", "desc")
            ->get();

        // DropdownOption::select('id', 'name', 'image')->distinct()->groupBy('name')->get();
        $total_cnt = count($course_categories);
        if ($total_cnt > 0) {
            foreach ($course_categories as $course_category) {
                $course_category->image = !empty($course_category->image) ? Helper::getImageUrl($course_category->image) : '';
                // $course_category['courses']['image'] = !empty($course_category['courses']['image']) ? Helper::getImageUrl($course_category['courses']['image']) : ' ';
            }
            $data = Helper::apiResonse(1, "Success", $course_categories);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
    public function courseDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'page' => 'nullable',
            'per_page' => 'nullable',
            'star' => 'nullable',
            'sorting' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        if ($request->has('course_id') && !empty($request->course_id) && $request->course_id !== "") {
            DB::enableQueryLog();
            $deviceType = Helper::getDeviceType($request->devicetype);
            $course = Course::select('courses.id', 'courses.course_coin', 'courses.title', 'courses.image', 'courses.instructor_id', "courses.description", "courses.how_to_use", "courses.type", "courses.lng", "courses.hours", "courses.minutes", 'courses.image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type', 'courses.show_learner_cnt')
                ->where('courses.id', $request->course_id)
                ->with("getPackages.getPackageBasedCourse")
                ->with("userCourseExpectedRelationship")
                ->with([
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                    },
                    'instructor:id,name',
                    'wishlists' => function ($query) {
                        $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                    },

                    'instructor:id,name',
                    'chapters.chapterInfo:id,title,chapter_id,asset_type',
                    'plans' => function ($query) {
                        return $query->where(function ($query) {
                            $query->where('status', 1);

                            $query->where(function ($query) {
                                $query->where('course_limit', 1)->where('is_fixed_date', 1)->whereDate('access_value', '>=', Carbon::now());
                            })
                                ->orWhere(function ($query) {
                                    $query->where('course_limit', 1)->where('is_fixed_date', 2)->where(function ($query) {
                                        $query->whereDate('access_value', '>=', Carbon::now())
                                            ->orWhere(function ($query) {
                                                // This will fetch plans where access_value is an integer (specific days)
                                                $query->whereNotNull('access_value')->where('access_value', '>', 0);
                                            });
                                    });
                                })
                                ->orWhereNull('access_value')->orWhere('access_value', '');
                        })->orderBy('order', 'Asc')
                            ->where('status', 1);
                    },

                ])
                ->with([
                    'userCourse' => function ($query) {
                        $query->where('learner_id', $request->user_id);
                    },
                ])
                // ->withCount([
                //     'userCourse as total_enroll',
                //     'rating_reviews as total_review'
                // ])
                ->withAvg('rating_reviews as rating', 'rating')
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'name', 'profile_picture', 'email');
                    },
                ])
                ->withCount([
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                    },
                ])
                ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                ->withCount(['rating_reviews as total_new_review'])

                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                ->where('courses.status', '1')
                // ->where('cp.status', '1')

                ->first();
            // dd($course->toArray());
            if (!empty($course)) {
                $course->chapters = $course->chapters->sortBy('order');

                $course = Helper::get_course_common_api_data($course, $request);
                // dd("");
                unset($course->rating_reviews);

                $perPage = $request->input('per_page', 10);
                $page = $request->input('page', 1);
                $ratingReviews = $course->rating_reviews();
                $ratingReviews->select('id', 'learner_id', 'rating', 'comment', 'created_at'); // Add the columns you need
                if ($request->has('star') && !empty($request->star)) {
                    $star = (int) $request->star;
                    $ratingReviews->where('rating', $star);
                }
                if ($request->has('sorting')) {
                    if ($request->sorting == '1') {
                        $ratingReviews->orderBy('created_at', 'desc');
                    } else if ($request->sorting == '2') {
                        $ratingReviews->orderBy('rating', 'desc');
                    } else if ($request->sorting == '3') {
                        $ratingReviews->orderBy('rating', 'asc');
                    } else {
                        $ratingReviews->orderBy('created_at', 'desc');
                    }
                } else {
                    $ratingReviews->orderBy('created_at', 'desc');
                }
                $ratingReviewsPaginated = $ratingReviews->paginate($perPage, ['*'], 'page', $page);
                // Transform the paginated rating_reviews data as needed
                $ratingReviewsPaginated->getCollection()->transform(function ($item) use ($request) {
                    $my_id = $item->learner_id;
                    $item->rating = empty($item->rating) ? '0' : $item->rating;
                    $item->name = $item->learner->name ?? '';
                    $item->profile_pic = !empty($item->learner->profile_pic) ? Helper::getImageUrl($item->learner->profile_pic) : '';
                    $item->review_date = Carbon::parse($item->created_at)->format("Y-m-d H:i:s"); // Assuming Carbon is used
                    $item->is_my_review = (request()->user_id == $my_id) ? true : false;
                    unset($item->course_id, $item->learner);
                    return $item;
                });

                $course->rating_reviews = $ratingReviewsPaginated;
                if (isset($course->instructor->id)) {
                    $course->instructor->profile_picture = !empty($course->user->profile_picture) ? Helper::getImageUrl($course->user->profile_picture) : '';
                    $course->instructor->name = $course->user->name;
                    $course->instructor->email = $course->user->email;
                    $designation = Instructors::select('designation')->where('user_id', $course->instructor->id)->first();
                    $course->instructor->designation = $designation->designation ?? '';
                    // $course->instructor->rating = '';
                }
                // dd($courses);
                if ($course->isCombineCourse) {
                    $course->course_list = new Collection([]);
                    if ($course && isset($course->packages) && count($course->packages) > 0) {

                        LearnerLog::create([
                            "learner_id" => $request->user_id,
                            'device_id' => $request->header('deviceid'),
                            'device_token' => $request->header('devicetoken'),
                            'device_name' => $request->header('devicetype'),
                            'description' => $course->type==1 ? "Course viewed" : "Package viewed",
                            'course_id' => $request->course_id,
                            'type' => 3,
                        ]);
                        $course->packages->map(function ($value, $key) use ($course, $request, $deviceType) {
                            // $value->package = Course::select('courses.id', 'title', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
                            //     ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                            //     ->where('cp.status', '1')
                            //     ->where('courses.id', $value->course_id)
                            //     ->withAvg('rating_reviews as rating', 'rating')
                            //     ->withCount('rating_reviews as total_review')
                            //     ->whereNotNull('courses.' . $request->device_price_field)->with([
                            //     'plans' => function ($query) {
                            //         return $query->where('status', 1)->orderBy('order', 'Asc');
                            //     },
                            // ])
                            //     ->where('courses.status', '1')
                            //     ->first();

                            $value->package = Course::distinct()
                                ->select('courses.id', 'courses.id as course_id', 'title', 'instructor_id', 'image', 'type', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'user_courses.created_at', 'cp.plan_type')
                                ->where('courses.id', $value->course_id)
                                ->whereRaw("find_in_set($deviceType , course_platform)")
                                ->with("getChapters:id,course_id")
                                // ->with("userCourseExpectedRelationship")
                                ->with([
                                    'instructor' => function ($query) {
                                        $query->select('name', 'email', 'id', 'profile_picture');
                                    },
                                    'categories' => function ($query) use ($request) {
                                        $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                                    },
                                ])

                                ->with(['chapters' => function ($query) {
                                    $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                                    $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                                }])

                                ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                                ->withAvg('rating_reviews as rating', 'rating')
                                ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
                                ->where('courses.status', '1')

                                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                                ->whereNull('courses.deleted_at')
                                ->groupBy('courses.id')
                                ->orderBy('user_courses.id', 'DESC')
                                ->first();

                            if ($value->package) {
                                // if($value->package->getChapters->count() > 0) {
                                $sum = 0;
                                $value->package->getChapters->filter(function ($v) use (&$sum) {
                                    $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')
                                        ->where('asset_type', '!=', 4)
                                        ->where('asset_type', '!=', 7)
                                        ->where('parent_id', $v->id)->count();
                                    $sum += $v->chpter_count;
                                });
                                // $value->package->getChapters->filter(function ($val) use (&$sum) {
                                //     return $sum += $val->chpter_count;

                                // });
                                $value->package->tot_chapterCount = $sum;
                                $value->package->is_course_purchased = false;
                                $value->package->course_purchase_id = '';
                                if ($value->package->userCourseExpectedRelationship->count() > 0) {
                                    foreach ($value->package->userCourseExpectedRelationship as $userCourse) {
                                        $data = UserCourse::where("course_id", $userCourse->course_id)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();
                                        if ($data) {
                                            if (isUserCourseExpired($data->expire_at) == true) {
                                                $value->package->is_course_purchased = isUserCourseExpired($data->expire_at);
                                                $value->package->course_purchase_id = isUserCourseExpired($data->expire_at) == true ? $data->id : '';
                                            } else {
                                                $value->package->is_course_purchased = false;
                                                $value->package->course_purchase_id = '';
                                            }
                                        }
                                    }
                                } else {
                                    foreach ($value->package->getPackages as $package) {
                                        // dump($package->package_id);
                                        $package_user = UserCourse::where("course_id", $package->package_id)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();
                                        if ($package_user) {

                                            if (isUserCourseExpired($package_user->expire_at) == true) {
                                                $value->package->is_course_purchased = isUserCourseExpired($package_user->expire_at);
                                                $value->package->course_purchase_id = isUserCourseExpired($package_user->expire_at) == true ? $package_user->id : '';
                                                break;
                                            }
                                        } else {

                                            $value->package->is_course_purchased = false;
                                            $value->package->course_purchase_id = '';
                                        }
                                    }
                                }

                                $expireFlag = is_expired($course->userCourse->expire_at ?? '');
                                $value->package->is_expire = false;
                                $value->package->validity = '';
                                if ($expireFlag == 0) {
                                    $value->package->is_expire = true;

                                    $value->package->validity = 'Expired';
                                } else if ($expireFlag == 1) {
                                    // dd($course->userCourse->toArray());
                                    $value->package->is_expire = false;
                                    $value->package->validity = dateFormate($course->userCourse->expire_at ?? '') . ' Days';
                                } else if ($expireFlag == 2) {
                                    $value->package->is_expire = false;
                                    $value->package->validity = 'Lifetime';
                                }

                                $value->package->instructor_name = $course->instructor->name;
                                $value->package->isCombineCourse = $course->isCombineCourse;
                                $value->package->packages_count = $course->packages_count;
                                $value->package->expire_at = $course->userCourse->expire_at ?? '';

                                if (!empty($value->package->chapters->first()) && !empty($value->package->chapters->first()->chapterIds)) {
                                    $chapterIds = explode(",", $value->package->chapters->first()->chapterIds);

                                    $totalChapters = count($chapterIds);
                                    $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                                        ->where("learner_id", $request->user_id)
                                        ->where("is_completed", 1)
                                        ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                                        ->where('chapters.asset_type', '!=', 4)
                                        ->where('chapters.asset_type', '!=', 7)
                                        ->pluck('watched_times')
                                        ->first();

                                    $value->package->percentage_completed = ($value->package->tot_chapterCount > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $value->package->tot_chapterCount) * 100) : 0;
                                } else {
                                    $value->package->percentage_completed = 0;
                                }
                            }

                            // if (!empty($value->package)) {
                            //     $package_data = Helper::get_course_detail_for_api($value->package->id);

                            //     if (isset($package_data->id)) {
                            //         // "course_purchase_id": "700",
                            //         // validity
                            //         // progressbar
                            //         // $package_data->ima = isset($package_data->image) && !empty($package_data->image) ? Helper::getImageUrl($package_data->image) : '';
                            //         unset($package_data->plans, $package_data->categories, $package_data->rating_reviews);
                            //         // dump($value->package);
                            //         $course->course_list->push($package_data);
                            //     }
                            // }

                            if (!empty($value->package)) {
                                $value->package->image = isset($value->package->image) && !empty($value->package->image) ? Helper::getImageUrl($value->package->image) : '';
                                unset($value->package->plans, $value->package->categories, $value->package->rating_reviews);
                                $course->course_list->push($value->package);
                            }
                        });
                    }
                    // $course->course_list = $course->course_list->sortByDesc("created_at")->filter(function($item)
                    // {
                    //     return $item->id;
                    // });

                    $course->packages_count = $course->course_list->count();

                    unset($course->chapters, $course->lng, $course->user, $course->chapters_count, $course->user_course, $course->learners_data);
                } else {
                    $course->lessons = collect([]);

                    $course->chapters->map(function ($value) {
                        $value->chapter_items = Chapter::with(['media', 'chapterInfo:id,title,chapter_id,asset_type,duration,upload_type,pdf_page_count as pdf_count'])->where('parent_id', $value->id)->where('asset_type', '<>', 7)
                            ->get();

                        $value->chapterInfo->asset_type = Helper::getChapterAssetType($value->chapterInfo->asset_type ?? '');
                    });
                    if ($course && isset($course->chapters) && count($course->chapters) > 0) {
                        foreach ($course->chapters as $key => $value) {
                            $value->chapterInfo->chapter_name = $value->title;
                            $value->chapterInfo->path = !empty($value->media->path) ? Helper::getImageUrl($value->media->path) : '';
                            if ($value->asset_type == 7 && $value->chapterInfo->chapter_id == $value->id) {
                                $value->chapterInfo->course_data = collect([]);
                                $course_data = Helper::get_course_detail_for_api($value->title, $value->plan_id, $request->devicetype);
                                $course_data->isCombineCourse = $course_data->type == '1' ? false : true;
                                $course_data->lng = Helper::getCourseLanguage($course_data->lng);
                                $value->chapterInfo->course_data->push($course_data);
                            }
                            $value->chapterInfo->asset_type = Helper::getChapterAssetType($value->asset_type);
                            $value->chapterInfo->lesson_item = collect([]);
                            unset($value->chapterInfo->chapter_id);
                            $course->lessons->push($value->chapterInfo);
                            foreach ($value->chapter_items as $key1 => $value1) {
                                if ($value1->chapterInfo) {
                                    $value1->chapterInfo->asset_type = Helper::getChapterAssetType($value1->asset_type);
                                    $value1->chapterInfo->path = !empty($value1->media->path) ? Helper::getImageUrl($value1->media->path) : '';
                                    if ($value1->asset_type == 7 && $value1->chapterInfo->chapter_id == $value1->id) {
                                        $value1->chapterInfo->course_data = collect([]);
                                        $course_data = Helper::get_course_detail_for_api($value1->title, $value1->plan_id, $request->devicetype);
                                        $course_data->isCombineCourse = $course_data->type == '1' ? false : true;
                                        $course_data->lng = Helper::getCourseLanguage($course_data->lng);
                                        $value1->chapterInfo->course_data->push($course_data);
                                    }
                                    if ($value1->asset_type == 0 || $value1->asset_type == 1 || $value1->asset_type == 2) {
                                        // $value1->chapterInfo->autoplay = $value1->chapterInfo->autoplay == 1 ? true : false;
                                        $value1->chapterInfo->duration = $value1->asset_type != 2 ? $value1->chapterInfo->duration : $value1->chapterInfo->pdf_count . ' Pages';
                                    } else {
                                        // $value1->chapterInfo->autoplay = false;
                                        $value1->chapterInfo->duration = '';
                                    }
                                    $value->chapterInfo->lesson_item->push($value1->chapterInfo);
                                }
                            }
                        }
                    }
                    unset($course->chapters, $course->user, $course->packages_count, $course->user_course);
                }

                if ($course && isset($course->plans) && count($course->plans) > 0) {
                    foreach ($course->plans as $key => $value) {

                        // $expiredAt = get_plan_expire($value->id);
                        // // $expireFlag = is_expired($expiredAt);
                        // // dd($value['calendar']);
                        // if($value->calendar == '1'){
                        //     $calender = 'week';
                        // }else if($value->calendar == '2'){
                        //     $calender = 'month';
                        // }else if($value->calendar == '3'){
                        //     $calender = 'year';
                        // }

                        // $value->validity = $value->plan_type != '2' ? get_course_validity($expireFlag, $course->expire_at) : 'Every ' . $value['bill_learner_every'] . ' ' . $calender . ' ' ;

                        if ($value->course_limit == 1) {
                            if (isset($value->is_fixed_date) && $value->access_value != null) {
                                if ($value->is_fixed_date == 2) { // add number of days

                                    $valid_till = $value->access_value;
                                }
                                if ($value->is_fixed_date == 1) { // add expiredate
                                    // $date->modify('+'.$value->access_value.' days');
                                    // $expiredAt = $date->format('Y-m-d');

                                    // $your_date = strtotime($value->access_value);
                                    // $valid_till = $your_date - time();
                                    // $valid_till = round($valid_till / (60 * 60 * 24));
                                    $valid_till = dateFormate($value->access_value);
                                }
                                $valid_till = $valid_till . ' Days';
                            }
                        } else {
                            $valid_till = "Lifetime";
                        }

                        $value->validity = $valid_till;
                    }
                }

                $coupons = get_coupon_by_learner(request()->user_id, $request->course_id);
                if ($coupons->isEmpty()) {
                    $course->is_coupon_available = false;
                } else {
                    $course->is_coupon_available = true;
                }

                // dd($course);
                unset($course->wishlists, $course->type, $course->instructor_id, $course->packages, $course->total_enroll, $course->user_course);

                $data = Helper::apiResonse(1, "Success", $course);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, 'No records found.', []);
                return response()->json($data, 200);
            }
        } else {
            $data = Helper::apiResonse(0, 'Course Id is required.', []);
            return response()->json($data, 400);
        }
    }
    public function courseFilter(Request $request)
    {
        $filterData['sorting_list'] = ['date_asc', 'date_desc', 'price_asc', 'price_desc', 'rating_desc'];
        $filterData['categorylist'] = DropdownOption::join('dropdowns', 'dropdowns.id', '=', 'dropdown_options.dropdown_id')->where('dropdowns.slug', 'course_category')->get(['dropdown_options.id', 'dropdown_options.name'])->toArray();
        // echo "<pre>";print_r($dropdown);die;
        $filterData['instructurelist'] = Instructors::select(['id', 'user_id'])
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'name', 'profile_picture', 'email');
                },
            ])
            ->get();
        //   dd( $filterData['instructurelist']);
        foreach ($filterData['instructurelist'] as $fil) {
            $fil->name = $fil->user->name ?? '';
            $fil->email = $fil->user->email ?? '';
            $fil->id = $fil->user_id;
            unset($fil->user_id);
            $fil->profile_picture = !empty($fil->user->profile_picture) ? Helper::getImageUrl($fil->user->profile_picture) : '';

            unset($fil->user);
        }
        // $filterData['sorting_list'] = ["Date Ascending" => 'date_asc', 'Date Descending'=>'date_desc', "Price Asc"=>'price_asc', "Price Desc"=>'price_desc', "User Rating"=>'rating_desc'];

        $max_price = maxCoursePrice();
        // dd($max_price);
        $temp3 = [];

        if (!empty($max_price)) {
            $temp = 1;
            $temp += round($max_price / 2500, 0);
            // dd($temp);
            $next = 0;
            if ($max_price > 500 && $max_price < 1250) {
                $temp += 1;
            }
            for ($i = 0; $i < $temp; $i++) {
                if ($i == 0) {
                    $temp3[$i]['min'] = 0;
                    $temp3[$i]['max'] = 0;
                    $next = $temp3[$i]['max'];
                } else
                if ($i == 1) {
                    $temp3[$i]['min'] = 0;
                    $temp3[$i]['max'] = 500;
                    $next = $temp3[$i]['max'];
                } else {

                    if ($next >= 10000) {
                        $temp -= 1;
                        if (!isset($temp3[$i]['max'])) {
                            break;
                        } else {
                            $temp3[$i]['min'] = 0;
                            $temp3[$i]['max'] = $temp3[$i]['max'] + 5000;
                        }
                    } else {
                        $temp3[$i]['min'] = 0;
                        $next = ((500 * 2) * $i);
                        $temp3[$i]['max'] = $next;
                    }
                }
                $temp3[$i]['range'] = $temp3[$i]['min'] . '_' . $temp3[$i]['max'];
            }

            $filterData['pricelist'] = $temp3;
            $filterData['ratings'] = [1, 2, 3, 4, 5];
        }
        $total_cnt = count($filterData);
        if ($total_cnt > 0) {
            $data = Helper::apiResonse(1, "Success", $filterData);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
    public function courseReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        if ($request->has('course_id') && !empty($request->course_id) && $request->course_id !== "") {
            DB::enableQueryLog();

            $course = Course::select('courses.id')
                ->where('courses.id', $request->course_id)

                ->with([
                    'rating_reviews' => function ($query) {
                        $query->select('learner_id', 'course_id', 'comment', 'rating', 'learner_id', 'created_at')->where('is_approve', '1')->where('learner_id', request()->user_id)->with([
                            'learner' => function ($query) {
                                $query->select('id', 'name', 'profile_pic');
                            },
                        ]);
                    },
                ])
                ->first();

            // dd(DB::getqueryLog());

            // echo "<pre>";print_r($course );die;
            if ($course) {
                if (!empty(avg_course_rating($course->id))) {
                    $course->rating = avg_course_rating($course->id) ?? '';
                } else {
                    $course->rating = '0';
                }

                if (!empty($course->rating_reviews->count())) {
                    $course->reviews = $course->rating_reviews->count();
                } else {
                    $course->reviews = '0';
                }
                $course->reviewers = collect([]);
                if ($course && isset($course->rating_reviews) && count($course->rating_reviews) > 0) {
                    foreach ($course->rating_reviews as $key => $value) {
                        //  $value->chapterInfo->rating = '';
                        $value->name = $value->learner->name;
                        $value->profile_pic = !empty($value->learner->profile_pic) ? Helper::getImageUrl($value->learner->profile_pic) : '';
                        unset($value->learner_id, $value->course_id, $value->learner);
                        $course->reviewers->push($value);
                    }
                }
                unset($course->rating_reviews);
                $data = Helper::apiResonse(1, "Success", $course);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, 'No records found.', []);
                return response()->json($data, 200);
            }
        } else {
            $data = Helper::apiResonse(0, 'Course Id is required.', []);
            return response()->json($data, 400);
        }
    }
    public function myCourse(Request $request)
    {
        // dd(date('Y-m-d H:i:s'));
        // $expiredAt = get_plan_expire(205);
        // dd($expiredAt);

        $learner_id = request()->user_id;
        $deviceType = Helper::getDeviceType($request->devicetype);
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $courses = Course::distinct()->select('courses.id', 'title', 'instructor_id', 'image', 'type', 'user_courses.id as user_courses_id', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'user_courses.id as course_purchase_id', 'user_courses.created_at', 'cp.plan_type')
            // ->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'categories' => function ($query) use ($request) {
                    $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                },
            ])
            ->with(['chapters' => function ($query) {
                $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
            }])
            // ->with('userCourse:id,course_id,expire_at')
            ->with([
                'userCourse' => function ($query) use ($learner_id) {
                    $query->where('learner_id', $learner_id);
                },
            ])
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
            ->where('courses.status', '1')
            ->where('user_courses.learner_id', $learner_id)
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            // ->where('cp.status', '1')
            // ->whereNotNull('courses.' . $request->device_price_field)
            ->whereNull('courses.deleted_at')
            ->groupBy('courses.id')
            ->orderBy('user_courses.id', 'DESC')->get();
        // ->paginate($per_page, ['*'], 'page', $page_num);

        $courses = cpaginate($courses, $per_page, $page_num);

        $collection = $courses->getCollection();
        $collection = $collection->filter(function ($value, $key) {
            return $value->plans->count() > 0;
        });

        // $courses->each(function($value){
        // $value->chapter_data = Chapter::getCourseChapters($value->id)->get();
        //  $value->getChapters->filter(function($v) use($value){
        //     $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')->where('parent_id', $v->id)->count();
        // });

        //     $sum = 0;
        //      $value->getChapters->filter(function($val) use(&$sum) {
        //         return $sum += $val->chpter_count;

        //     });
        //     $value->tot_chapterCount = $sum;
        // });

        // dd($courses->toArray());
        $courses->setCollection($collection);
        if ($courses->count() > 0) {
            foreach ($courses as $course) {

                //$course->chapter_data = Chapter::getCourseChapters($course->id)->get();
                $sum = 0;
                $course->getChapters->filter(function ($v) use ($course, &$sum) {
                    $v->chpter_count = Chapter::whereHas("chapterInfo")
                        ->where('asset_type', '!=', 4)
                        ->where('asset_type', '!=', 7)
                        ->with('media:id,path,videoId')->where('parent_id', $v->id)->count();
                    $sum += $v->chpter_count;
                });

                // $course->getChapters->filter(function ($val) use (&$sum) {
                //     return $sum += $val->chpter_count;
                // });
                $course->tot_chapterCount = $sum;
                $expireFlag = is_expired($course->userCourse->expire_at ?? '');

                $course->is_expire = false;
                $course->validity = '';
                $course->course_purchase_id = $course->userCourse->id ?? '';
                if ($expireFlag == 0) {
                    $course->is_expire = true;
                    $course->validity = 'Expired';
                } else if ($expireFlag == 1) {
                    $course->is_expire = false;
                    // $course->validity = dateFormate($course->expire_at) . ' Days';
                    $course->validity = dateFormate($course?->userCourse?->expire_at) > 1 ? dateFormate($course?->userCourse?->expire_at) . ' Days' : dateFormate($course?->userCourse?->expire_at) . ' Day';
                } else if ($expireFlag == 2) {
                    $course->is_expire = false;
                    $course->validity = 'Lifetime';
                }

                // $now = Carbon::now();
                // $current_date = Carbon::parse($now)->toDateString();

                // if($course->is_expire) {

                // }

                // if(isset($course->userCourse->expire_at) && $course->userCourse->expire_at != null)
                // {
                //     $course->is_expire = $course->userCourse->expire_at > $current_date ? FALSE : true;
                // }else{

                // }
                $course->course_id = $course->id;
                $course->expire_at = $course->userCourse->expire_at ?? '';
                $course->rating = isset($course->rating) && !empty($course->rating) ? $course->rating : 0;
                $course->instructor_name = $course->instructor->name ?? '';
                $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';
                $course->isCombineCourse = $course->type == '1' ? false : true;
                $course->packages_count = 0;
                if ($course->isCombineCourse) {
                    $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);
                }
                // $course->is_combime
                if (isset($course->categories) && !empty($course->categories)) {
                    for ($i = 0; $i < $course->categories->count(); $i++) {
                        unset($course->categories[$i]->pivot);
                    }
                }
                if (!empty($course->chapters->first()) && !empty($course->chapters->first()->chapterIds)) {
                    // dd($course->chapters);
                    $chapterIds = explode(",", $course->chapters->first()->chapterIds);
                    // dd($chapterIds);
                    $totalChapters = count($chapterIds);
                    $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                        ->where("learner_id", $learner_id)
                        ->where("is_completed", 1)
                        ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                        ->where('chapters.asset_type', '!=', 4)
                        ->where('chapters.asset_type', '!=', 7)
                        ->pluck('watched_times')
                        ->first();
                    // dd($totalChapters);
                    // $course->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                    $course->percentage_completed = ($course->tot_chapterCount > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $course->tot_chapterCount) * 100) : 0;
                } else {
                    $course->percentage_completed = 0;
                }
                $course->instructor->profile_picture = Helper::getImageUrl($course->instructor->profile_picture);

                unset($course->instructor_id, $course->userCourse, $course->user_courses_id, $course->plans);
            }
            $data = Helper::apiResonse(1, "Success", $courses, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, 'No records found.', []);
            return response()->json($data, 200);
        }
    }

    public function packageCourses(Request $request)
    {
        //  dd(auth()->user()-id);
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:courses,id',
        ]);
        $learner_id = request()->user_id;
        $deviceType = Helper::getDeviceType($request->devicetype);
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $courses = CoursePackage::distinct()->select('courses.id', 'instructor_id', 'courses.title', 'type', 'user_courses.id as user_courses_id', 'user_courses.expire_at', 'is_free', 'courses.id', 'user_courses.id as user_courses_id', 'user_courses.expire_at', 'courses.type', 'courses.hours', 'courses.minutes', 'cp.list_price', 'cp.final_payable_price', 'courses.instructor_id', 'courses.image', 'cp.plan_type')
            ->whereRaw("find_in_set($deviceType , course_platform)")
            // ->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
            ->with('userCourse:id,course_id,expire_at')
            ->withAvg('rating_reviews as rating', 'rating')
            ->withCount(['UserCourse as total_enroll', 'rating_reviews as total_review'])
            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'chapters' => function ($query) {
                    $query->where("asset_type", "<>", 4)->select("chapters.asset_type", 'course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                },
                'categories' => function ($query) use ($request) {
                    $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                },
            ])
            ->leftJoin('user_courses', 'course_packages.package_id', '=', 'user_courses.course_id')
            ->leftJoin('courses', 'course_packages.course_id', '=', 'courses.id')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            // ->where('courses.status','1')
            ->where('user_courses.learner_id', $learner_id)
            ->where('user_courses.id', $request->id)
            ->whereNull('courses.deleted_at')
            ->groupBy('course_packages.course_id')
            ->orderBy('user_courses.created_at', 'DESC')
            ->get();

        //  dd($courses->toArray());
        $courses = cpaginate($courses, $per_page, $page_num);
        $collection = $courses->getCollection();

        $courses->setCollection($collection);
        if ($courses->count() > 0) {
            foreach ($courses as $course) {
                $course->course_id = $course->id;
                $course->rating = empty($course->rating) ? '0' : $course->rating;
                $course->instructor_name = $course->instructor->name ?? '';
                $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';
                $course->is_free = $course->is_free > '0' ? true : false;
                $course->is_purchased = $course->total_enroll > '0' ? true : false;
                $course->isCombineCourse = $course->type == '1' ? false : true;
                $course->packages_count = 0;
                if ($course->isCombineCourse) {
                    $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);
                }
                $course->percentage_completed = 0;
                if (isset($course->categories) && !empty($course->categories)) {
                    if (!empty($course->chapters->first()) && !empty($course->chapters->first()->chapterIds)) {
                        //   dd($course->chapters);
                        $chapterIds = explode(",", $course->chapters->first()->chapterIds);
                        $totalChapters = count($chapterIds);
                        $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                            ->where("learner_id", $learner_id)
                            ->where("is_completed", 1)
                            ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                            ->where('chapters.asset_type', '!=', 4)
                            ->where('chapters.asset_type', '!=', 7)
                            ->pluck('watched_times')
                            ->first();
                        // dd($totalCompletedChapter);
                        $course->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                    }
                    if (isset($course->categories) && !empty($course->categories)) {
                        for ($i = 0; $i < $course->categories->count(); $i++) {
                            unset($course->categories[$i]->pivot);
                        }
                    }
                }
                $course->instructor->profile_picture = Helper::getImageUrl($course->instructor->profile_picture);
                $expireFlag = is_expired($course->expire_at);
                $course->validity = get_course_validity($expireFlag, $course->expire_at);
                unset($course->instructor_id, $course->userCourse, $course->user_courses_id, $course->plans);
                // , $course->user_courses_id
            }
            $data = Helper::apiResonse(1, "Success", $courses, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, 'No records found.', []);
            return response()->json($data, 200);
        }
    }

    public function myCourseDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'page' => 'nullable',
            'per_page' => 'nullable',
            'star' => 'nullable',
            'sorting' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $deviceType = Helper::getDeviceType($request->devicetype);
        $learner_id = request()->user_id;
        if ($request->has('course_id') && !empty($request->course_id) && $request->course_id !== "") {
            DB::enableQueryLog();
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $course = Course::select('courses.id', 'courses.id as course_id', 'courses.title', 'courses.image', 'courses.instructor_id', "courses.description", "courses.how_to_use", "courses.type", "courses.lng", "courses.hours", "courses.minutes", "courses.type", 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type', 'courses.show_learner_cnt', "courses.public_forum_status", "courses.course_finished_day", "courses.course_finished", "courses.completely_watch", "courses.days")
                ->where('courses.id', $request->course_id)
                ->with(["userCourseExpectedRelationship" => function ($q)use($request) {
                    $q->where("learner_id", $request->user_id);
                }, 'getPackages'])
                ->with([
                    'rating_reviews' => function ($query) use ($page, $perPage) {
                        $query->select('learner_id', 'course_id', 'comment', 'rating', 'learner_id', 'created_at')
                            ->where('is_approve', '1')
                            ->with([
                                'learner' => function ($query) {
                                    $query->select('id', 'name', 'profile_pic');
                                },
                            ]);
                    },
                ])
                ->with([
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0)->with([
                            'media' => function ($query) {
                                $query->select('id', 'path', 'videoId');
                            },
                        ])->orderBy('order', 'Asc');
                    },
                    'instructor:id,name,profile_picture,email',
                    'wishlists' => function ($query) {
                        $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                    },
                    'instructor:id,name',
                    'chapters.chapterInfo:id,title,chapter_id,asset_type,upload_type,allow_download,allow_sleep,pdf_page_count',
                    'plans' => function ($query) {
                        return $query->where('status', 1)->orderBy('order', 'Asc');
                    },
                    'packages.original_course',
                    'chapters.userCourseProgress',
                ])
                ->withAvg('rating_reviews as rating', 'rating')
                // ->with(['user' => function ($query) {
                //     $query->select('id', 'name', 'profile_picture', 'email');
                ->withCount([
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                    },
                ])
                // }])
                ->with(['chaptersCounts' => function ($query) {
                    $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                    $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                    // $query->where('parent_id', 0);

                }])
                ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                ->withCount(['rating_reviews as total_new_review'])

                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                // ->where('cp.status', '1')
                // ->whereNotNull('courses.' . $request->device_price_field)
                ->orderBy("id", "desc")
                ->first();


            if ($course) {

                LearnerLog::create([
                    "learner_id" => $request->user_id,
                    'device_id' => $request->header('deviceid'),
                    'device_token' => $request->header('devicetoken'),
                    'device_name' => $request->header('devicetype'),
                    'description' => $course->type == 1 ? "Course viewed" : "Package viewed",
                    'course_id' => $request->course_id,
                    'type' => 3,
                ]);


                $perPage = $request->input('per_page', 10);
                $page = $request->input('page', 1);
                $star = $request->input('star');
                $ratingReviewsQuery = $course->rating_reviews()->select('id', 'learner_id', 'course_id', 'comment', 'rating', 'learner_id', 'created_at')
                    // ->getRawOriginal('created_at')
                    ->where('is_approve', '1')
                    ->with([
                        'learner' => function ($query) {
                            $query->select('id', 'name', 'profile_pic');
                        },
                    ]);

                if (!empty($star)) {

                    $ratingReviewsQuery->where('rating', $star);
                }
                if (!empty($request->sorting)) {
                    if ($request->sorting == '1') {
                        $ratingReviewsQuery->orderBy('created_at', 'desc');
                    } else if ($request->sorting == '2') { //high star
                        $ratingReviewsQuery->orderBy('rating', 'desc');
                    } else if ($request->sorting == '3') { //low star
                        $ratingReviewsQuery->orderBy('rating', 'asc');
                    } else {
                        $ratingReviewsQuery->orderBy('created_at', 'desc');
                    }
                } else {
                    $ratingReviewsQuery->orderBy('created_at', 'desc');
                }
                $ratingReviewsPaginated = $ratingReviewsQuery->paginate($perPage, ['*'], 'page', $page);
                $course->setRelation('rating_reviews', $ratingReviewsPaginated);
            }

            if ($course) {
                $rating_check_once = RatingReview::where("learner_id", $request->user_id)->where("course_id", $request->course_id)->first();

                if ($rating_check_once) {
                    $course->rating_status = true;
                } else {
                    $course->rating_status = false;
                }

                $course->public_forum_status = @$course->public_forum_status == 1 ? true : false;

                $chapter_watch_whole = Chapter::where("course_id", $course->id)
                    ->where("parent_id", "!=", 0)
                    ->pluck('id');



                // $course->packages_count = Helper::get_package_course_count($course->id, $request->devicetype);
                $course = Helper::get_course_common_api_data($course, $request);
                // dd($course->packages_count);




                // $learners_data = UserCourse::select('id')->where('course_id', $request->course_id)->get();
                // $course->learner_count = empty(count($learners_data)) ? '0' : count($learners_data);

                $course->learner_count = $course->user_course_count_count;

                foreach ($course->rating_reviews as $ratings) {
                    // dd($ratings->created_at->getRawOriginal());
                    $user_id = $request->user_id;
                    $my_id = $ratings->learner_id;
                    $ratings->name = $ratings->learner->name ?? '';
                    $ratings->profile_pic = !empty($ratings->learner->profile_pic) ? Helper::getImageUrl($ratings->learner->profile_pic) : '';
                    $ratings->review_date = Carbon::parse($ratings->created_at)->format("Y-m-d H:i:s"); // Assuming Carbon is used
                    // dd($created_at);
                    // $ratings->created_at_date = $created_at->format("Y-m-d\TH:i:s\Z");
                    // dd($ratings->created_at);
                    // $ratings->created_at = $ratings->getRawOriginal('created_at') != '' ? date("d/m/Y H:i:s", strtotime($ratings->getRawOriginal('created_at'))) : '';
                    // $created_at
                    // $ratings->created_at = !empty($ratings->created_at) ? Helper::date_format($ratings->getRawOriginal('created_at')) : '';
                    $ratings->is_my_review = ($user_id == $my_id) ? true : false;
                    unset($ratings->learner, $ratings->course_id);
                }

                if (isset($course->instructor->id)) {
                    $course->instructor->profile_picture = !empty($course->user->profile_picture) ? Helper::getImageUrl($course->user->profile_picture) : '';
                    $course->instructor->name = $course->user->name;
                    $course->instructor->email = $course->user->email;
                    $designation = Instructors::select('designation')->where('user_id', $course->instructor->id)->first();
                    $course->instructor->designation = $designation->designation ?? '';
                }

                //dd($course->instructor,$course->user);
                if ($course->isCombineCourse) {

                    $course->course_list = new Collection([]);
                    if ($course && isset($course->packages) && count($course->packages) > 0) {
                        $course->packages->map(function ($value, $key) use ($course, $request, $deviceType, $learner_id) {
                            $value->package =
                                Course::select('courses.id', 'title', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type', "cp.renewing_subscriptions_id as productId" )
                                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                                ->where('cp.status', '1')
                                ->where('courses.status', '1')
                                ->where('courses.id', $value->course_id)
                                ->withAvg('rating_reviews as rating', 'rating')
                                ->with("rating_reviews")
                                ->withCount('rating_reviews as total_review')
                                ->withCount(['rating_reviews as new_rating' => function ($query) use ($request) {
                                    $query->where('learner_id', $request->user_id);
                                }])
                                ->whereNotNull('courses.' . $request->device_price_field)->with([
                                    'plans' => function ($query) {
                                        return $query->where('status', 1)->orderBy('order', 'Asc');
                                    },
                                ])
                                ->first();

                            // $value->package->new_rating = @$value->package->new_rating > 0 ? true:  false;

                            // dd( $value->package);
                            // Course::distinct()
                            // ->select('courses.id' , 'courses.id as course_id', 'title', 'instructor_id', 'image', 'type','hours', 'minutes', 'cp.list_price', 'cp.final_payable_price',  'user_courses.created_at', 'cp.plan_type')
                            //         ->where('courses.id', $value->course_id)
                            //     ->whereRaw("find_in_set($deviceType , course_platform)")
                            //     ->with("getChapters:id,course_id")
                            //     // ->with("userCourseExpectedRelationship")
                            //     ->with([
                            //         'instructor' => function ($query) {
                            //             $query->select('name', 'email', 'id', 'profile_picture');
                            //         },
                            //         'categories' => function ($query) use ($request) {
                            //             $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                            //         },
                            //     ])
                            //     ->with(['chapters' => function ($query) {
                            //         $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                            //         $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                            //     }])

                            //     ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                            //     ->withAvg('rating_reviews as rating', 'rating')
                            //     ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
                            //     ->where('courses.status', '1')

                            //     ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                            //     ->whereNull('courses.deleted_at')
                            //     ->groupBy('courses.id')
                            //     ->orderBy('user_courses.id', 'DESC')
                            //     ->first();
                            //     dD($value->package->toArray());
                            //     if($value->package) {
                            //     // if($value->package->getChapters->count() > 0) {
                            //         $value->package->getChapters->filter(function ($v) {
                            //             $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')->where('parent_id', $v->id)->count();
                            //         });
                            //         $sum = 0;
                            //         $value->package->getChapters->filter(function ($val) use (&$sum) {
                            //             return $sum += $val->chpter_count;

                            //         });
                            //     $value->package->tot_chapterCount = $sum;
                            //     // }
                            //         // dd($value->package->tot_chapterCount);
                            //     $expireFlag = is_expired($course->userCourse->expire_at);
                            //     $value->package->is_expire = false;
                            //     $value->package->validity = '';
                            //     $value->package->course_purchase_id = $course->userCourse->id;
                            //     if ($expireFlag == 0) {
                            //         $value->package->is_expire = true;
                            //         $value->package->validity = 'Expired';
                            //     } else if ($expireFlag == 1) {
                            //         $value->package->is_expire = false;
                            //         $value->package->validity = dateFormate($course->userCourse) . ' Days';
                            //     } else if ($expireFlag == 2) {
                            //         $value->package->is_expire = false;
                            //         $value->package->validity = 'Lifetime';
                            //     }

                            //     $value->package->instructor_name = $course->instructor->name;
                            //     $value->package->isCombineCourse = $course->isCombineCourse;
                            //     $value->package->packages_count = $course->packages_count;
                            //     $value->package->expire_at = $course->userCourse->expire_at;

                            //     if (!empty( $value->package->chapters->first()) && !empty( $value->package->chapters->first()->chapterIds)) {
                            //         $chapterIds = explode(",",  $value->package->chapters->first()->chapterIds);

                            //         $totalChapters = count($chapterIds);
                            //         $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                            //             ->where("learner_id", $learner_id)
                            //             ->where("is_completed", 1)
                            //             ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                            //             ->where('chapters.asset_type', '!=', 4)
                            //             ->where('chapters.asset_type', '!=', 7)
                            //             ->pluck('watched_times')
                            //             ->first();

                            //             $value->package->percentage_completed = ( $value->package->tot_chapterCount > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter /  $value->package->tot_chapterCount) * 100) : 0;
                            //     } else {
                            //          $value->package->percentage_completed = 0;
                            //     }

                            if (!empty($value->package)) {
                                // $value->package->public_forum_status =  @$value->package->public_forum_status==1  ? true : false;
                                $value->package->rating_status = @$value->package->new_rating > 0 ? true : false;
                                $value->package->image = isset($value->package->image) && !empty($value->package->image) ? Helper::getImageUrl($value->package->image) : '';
                                unset($value->package->plans, $value->package->categories, $value->package->rating_reviews);
                                $course->course_list->push($value->package);
                            }
                        });
                    }
                    unset($course->chapters, $course->user, $course->chapters_count, $course->plans, $course->learners_data);
                } else {
                    $course->lessons = collect([]);

                    $course->chapters->map(function ($value) {
                        $value->chapter_items = Chapter::with(['media', 'chapterInfo:id,title,chapter_id,asset_type,description,duration,upload_type,allow_download,allow_sleep,pdf_page_count as pdf_count,timer,whole_video_coin'])
                            ->with([
                                'userCourseProgress' => function ($q) {
                                    $q->where('learner_id', request()->user_id)->select('learner_id', 'chapter_id', 'watched_time', 'is_completed');
                                },
                            ])
                            ->where('parent_id', $value->id)
                            ->whereDoesntHave("getUserCourse",function($q)use($value){
                                $q->where("course_id","!=",$value->title);
                                $q->where("learner_id",request()->user_id);

                            })
                            ->orderBy('order', 'asc')->get();
                        // dd($value->chapter_items);
                        $value->chapterInfo->asset_type = Helper::getChapterAssetType($value->chapterInfo->asset_type);


                        // dd($value->chapterInfo->asset_type == 1);
                        // if($value->chapterInfo->asset_type == 0){
                        //     dd('123');
                        // }
                    });

                    if ($course && isset($course->chapters) && count($course->chapters) > 0) {

                        foreach ($course->chapters as $key => $value) {

                            $value->chapterInfo->playback_info = collect([
                                'isCompleted' => isset($value->userCourseProgress) && !empty($value->userCourseProgress) && $value->userCourseProgress->is_completed == 1 ? true : false,
                                'watchedTime' => isset($value->userCourseProgress) && !empty($value->userCourseProgress) ? $value->userCourseProgress->watched_time : '',
                            ]);

                            /**
                             * video_type
                             */
                            $value->chapterInfo->video_type = checkVideoType($value);
                            // dd(checkVideoType($value));
                            // $value->chapterInfo->videoId = $value->chapterInfo->otp = $value->chapterInfo->offlineotp = $value->chapterInfo->playbackInfo = '';
                            /*if (($value->asset_type == 0 || $value->asset_type == 1) && $value->upload_type == 0 && isset($value->media) && !empty($value->media->videoId)) {
                            $responseObj = getVideoTokenData($value->media->videoId);
                            $value->chapterInfo->videoId = $value->media->videoId;
                            $value->chapterInfo->otp = isset($responseObj->otp) ? $responseObj->otp : '';
                            $value->chapterInfo->playbackInfo = isset($responseObj->playbackInfo) ? $responseObj->playbackInfo : '';

                            // offline otp
                            $responseObjOffline = getVideoOfflineTokenData($value->media->videoId, $expiryDuration);
                            // $value->chapterInfo->offline->videoId = $value->media->videoId;
                            $value->chapterInfo->offlineotp = isset($responseObjOffline->otp) ? $responseObjOffline->otp : '';
                            // $value->chapterInfo->offline->playbackInfo = isset($responseObjOffline->playbackInfo) ? $responseObjOffline->playbackInfo : '';
                            }*/

                            // dd($value->upload_type);

                            // dd($value->asset_type == 1);
                            // $value->chapterInfo->playback_info = collect();
                            $value->chapterInfo->chapter_name = $value->title;
                            $value->chapterInfo->path = !empty($value->media->path) ? Helper::getImageUrl($value->media->path) : '';
                            //check if asset type is video , return video url from database.
                            if (($value->asset_type == 0 && ($value->upload_type == 1 || $value->upload_type == 2)) || ($value->asset_type == 2 && $value->upload_type == 3) || $value->asset_type == 6) {
                                $value->chapterInfo->path = $value->file_url;
                            }

                            if ($value->asset_type == 7 && $value->chapterInfo->chapter_id == $value->id) {
                                $value->chapterInfo->course_data = collect([]);
                                $course_data = Helper::get_course_detail_for_api($value->title, $value->plan_id, $request->devicetype);
                                $course_data->isCombineCourse = $course_data->type == '1' ? false : true;
                                $course_data->lng = Helper::getCourseLanguage($course_data->lng);
                                $value->chapterInfo->course_data->push($course_data);
                            }
                            $value->chapterInfo->asset_type = Helper::getChapterAssetType($value->asset_type);
                            $value->chapterInfo->lesson_item = collect([]);
                            $value->chapterInfo->allow_download = false;
                            $value->chapterInfo->allow_sleep = false;

                            $course->lessons->push($value->chapterInfo);
                            foreach ($value->chapter_items as $key1 => $value1) {

                                if ($value1->chapterInfo) {



                                    $value1->chapterInfo->asset_type = Helper::getChapterAssetType($value1->asset_type);
                                    $value1->chapterInfo->path = !empty($value1->media->path) ? Helper::getImageUrl($value1->media->path) : '';
                                    $value1->chapterInfo->playback_info = collect([
                                        'isCompleted' => isset($value1->userCourseProgress) && !empty($value1->userCourseProgress) && $value1->userCourseProgress->is_completed == 1 ? true : false,
                                        'watchedTime' => isset($value1->userCourseProgress) && !empty($value1->userCourseProgress) ? $value1->userCourseProgress->watched_time : '',
                                    ]);
                                    if ($value1->asset_type == 7 && $value1->chapterInfo->chapter_id == $value1->id) {
                                        $value1->chapterInfo->course_data = collect([]);

                                        $course_data = Helper::get_course_detail_for_api($value1->title, $value1->plan_id, $request->devicetype);

                                        if (isset($course_data->id)) {

                                            $SellCourseTimer = SellCourseTimer::where("course_id", @$course_data->id)->where("learner_id", $request->user_id)->where("chapter_id", $value1->chapterInfo->chapter_id)->first();

                                            // $SellCourseTimer = SellCourseTimer::where("course_id",@$course_data->id)->where("learner_id",$request->user_id)->where("chapter_id",$value1->chapterInfo->chapter_id)->first();

                                            // $value1->chapterInfo->timer = $SellCourseTimer!=null ? $SellCourseTimer->timer : ($value1->chapterInfo->timer!="" ? $value1->chapterInfo->timer.':00' : '00:00');


                                            $course_data->isCombineCourse = $course_data->type == '1' ? false : true;
                                            $course_data->plan_id = $course_data->type == '1' ? false : true;

                                            // $course_data->timer = $SellCourseTimer!=null ? $SellCourseTimer->timer : $value1->chapterInfo->timer.':00';

                                            $course_data->timer =  $SellCourseTimer != null ? $SellCourseTimer->timer : ($value1->chapterInfo->timer != "" ? $value1->chapterInfo->timer . ':00' : '00:00');


                                            $course_data->plan_id = $value1->plan_id;
                                            $course_data->chapter_id = $value1->chapterInfo->chapter_id;


                                            $course_data->image = !empty($course_data->image) ? Helper::getImageUrl($course_data->image) : '';

                                            $course_data->lng = Helper::getCourseLanguage($course_data->lng);

                                            // get bysell list price & final payable price
                                            // $course_discount_price = CoursePlan::select('list_price', 'final_payable_price')->where('id',$value1->plan_id)->first();
                                            // // dd($value1);
                                            // $course_data->bysell_list_price = $course_discount_price->list_price;
                                            // $course_data->bysell_final_payable_price = $course_discount_price->final_payable_price;
                                            $value1->chapterInfo->course_data->push($course_data);
                                        }
                                    }
                                    $value1->chapterInfo->video_type = checkVideoType($value1);

                                    $value1->chapterInfo->youtube_video_id = '';

                                    //check if asset type is video , return video url from database.
                                    if (($value1->asset_type == 0 && ($value1->upload_type == 1 || $value1->upload_type == 2)) || ($value1->asset_type == 2 && $value1->upload_type == 3) || $value1->asset_type == 6) {
                                        $value1->chapterInfo->path = $value1->file_url;

                                        $value1->chapterInfo->image = $value1->image != null ? url("storage/chapter_link/$value1->image") : '';

                                        if ($value1->asset_type == 0 && $value1->upload_type == 1) {
                                            $video_id = explode("?v=", $value1->file_url);
                                            $video_id = isset($video_id[1]) ? $video_id[1] : '';
                                            $value1->chapterInfo->youtube_video_id = $video_id;
                                        }
                                    }

                                    if ($value1->asset_type == 5) {
                                        // dd($value1->chapterInfo->description);
                                        $value1->chapterInfo->text_field_content = htmlspecialchars_decode($value1->chapterInfo->description);

                                        // dd($value1->chapterInfo->text_field_content);

                                    } else {
                                        $value1->chapterInfo->text_field_content = '';
                                    }


                                    if ($value1->asset_type == 7) {
                                        $value1->chapterInfo->title = Course::where('id',$value1->title)
                                        ->pluck('title')
                                        ->first();
                                    } else {
                                        $value1->chapterInfo->title = $value1->title;
                                    }

                                    if ($value1->asset_type == 0 || $value1->asset_type == 1 || $value1->asset_type == 2) {
                                        // $value1->chapterInfo->autoplay = $value1->chapterInfo->autoplay == 1 ? true : false;
                                        $value1->chapterInfo->duration = $value1->asset_type != 2 ? $value1->chapterInfo->duration : $value1->chapterInfo->pdf_count . ' Pages';
                                    } else {
                                        // $value1->chapterInfo->autoplay = false;
                                        $value1->chapterInfo->duration = '';
                                    }
                                    if ($value1->chapterInfo->allow_download == 1 && (($value1->asset_type == 0 && $value1->upload_type != 1) || $value1->asset_type == 1 || $value1->asset_type == 2)) { // for pdf, video and audio
                                        $value1->chapterInfo->allow_download = true;

                                        //unset($value1->chapterInfo->allow_download);
                                    } else {
                                        $value1->chapterInfo->allow_download = false;
                                    }

                                    if ($value1->chapterInfo->allow_sleep == "true" && $value1->asset_type == 1) { // for pdf, video and audio
                                        $value1->chapterInfo->allow_sleep = true;
                                    } else {
                                        $value1->chapterInfo->allow_sleep = false;
                                    }


                                    // dd($value1->toArray());

                                    $value->chapterInfo->lesson_item->push($value1->chapterInfo);
                                }
                            }
                        }
                    }


                    // dd( $course->lessons);
                    /* end buy sell structurise */
                    unset($course->chapters, $course->user, $course->packages_count, $course->learners_data);
                }
                unset($course->wishlists, $course->type, $course->instructor_id, $course->packages, $course->plans);

                $data = Helper::apiResonse(1, "Success", $course);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, 'No records found.', []);
                return response()->json($data, 200);
            }
        } else {
            $data = Helper::apiResonse(0, 'Course Id is required.', []);
            return response()->json($data, 400);
        }
    }

    /* Used for fetching vimeo or vdochiper video details*/
    public function fetchVideoData(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:chapters,id',
            'course_id' => 'required|exists:courses,id',
            'flag' => 'required', //1 = vdochiper (66), 2 = vimeo (150)
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        if (isset($request->flag) && $request->flag != '') {

            $chapter = Chapter::select('file_url', 'media_id')->find($request->id);

            if ($request->flag == 1) { // vdochiper

                $media = Media::select('videoId')->find($chapter->media_id);
                // dd($media);

                // $value1->chapterInfo->videoId = $value1->chapterInfo->otp = $value1->chapterInfo->offlineotp = $value1->chapterInfo->playbackInfo = '';
                if (isset($media->videoId) && $media->videoId != '') {

                    $expiry_date = UserCourse::select('expire_at')->where('learner_id', $request->user_id)
                        ->where('course_id', $request->course_id)
                        ->first();

                    if (isset($expiry_date->expire_at) && $expiry_date->expire_at != '') {
                        $expiryDate = Carbon::parse($expiry_date->expire_at);
                        $currentDate = Carbon::now();
                        $expiryDuration = $currentDate->diffInDays($expiryDate);
                    } else {
                        $expiryDuration = '';
                    }

                    //Code for VdoCipher
                    $responseObj = getVideoTokenData($media->videoId);
                    $response['videoId'] = $media->videoId;
                    $response['otp'] = isset($responseObj->otp) ? $responseObj->otp : "";
                    $response['playbackInfo'] = isset($responseObj->playbackInfo) ? $responseObj->playbackInfo : ' ';

                    // offline otp
                    $responseObjOffline = getVideoOfflineTokenData($media->videoId, $expiryDuration);
                    $response['offlineotp'] = isset($responseObjOffline->otp) ? $responseObjOffline->otp : "";
                    $response['vimeo_files'] = [];
                    $response['audio_files'] = '';
                    // dd($media->videoId);
                    if ($request->is_audio == 1) {

                        $get_id = getVideo($media->videoId);
                        $is_id = "";
                        foreach ($get_id as $values) {
                            if ($values->encryption_type == "original") {
                                $is_id = $values->id;
                                break;
                            }
                        }
                        $response['audio_files'] = getVideoUrl($is_id, $media->videoId)->redirect;
                    }

                    $data = Helper::apiResonse(1, "Success", $response);
                    return response()->json($data, 200);
                } else {
                    $data = Helper::apiResonse(0, 'Invalid vdochiper id', []);
                    return response()->json($data, 400);
                }
            } else if ($request->flag == 2) { // vimeo

                if (isset($chapter->file_url) && $chapter->file_url != '') {
                    // dd($value1);
                    $arr = explode("/", $chapter->file_url);
                    // dd($arr);
                    $vid = end($arr);
                    $vim_api = "https://api.vimeo.com/videos/" . $vid;
                    $VIMEO_ACCESS_KEY = config()->has('settings.vimeo_access') ? config('settings.vimeo_access') : null;
                    $response = Http::withHeaders([
                        'Authorization' => 'bearer ' . $VIMEO_ACCESS_KEY,
                        'Accept' => 'application/vnd.vimeo.*+json;version=3.4',
                    ])->get($vim_api, [
                        'fields' => ['files', 'privacy'],
                    ]);
                    if ($response->status() == 200) {
                        $response = $response->json();

                        if (isset($response['download']) && isset($response['privacy']['download']) && $response['privacy']['download']) {

                            $all_type = [];
                            if (!empty($response['download'])) {
                                foreach ($response['download'] as $k => $v) {
                                    $v['md5'] = '';
                                    $v['width'] = (string) $v['width'];
                                    $v['height'] = (string) $v['height'];
                                    $v['fps'] = (string) $v['fps'];
                                    $v['size'] = (string) $v['size'];

                                    if (strlen($v['rendition']) == 5) { // 1080p and more
                                        $leng = substr($v['rendition'], 0, 4);
                                    } else { // 240p to 720p
                                        $leng = substr($v['rendition'], 0, 3);
                                    }
                                    $all_type[$leng] = $v;
                                }
                            }
                            ksort($all_type);
                            // dd($all_type);

                            $res = [];
                            $res['videoId'] = "";
                            $res['otp'] = "";
                            $res['playbackInfo'] = "";
                            $res['offlineotp'] = "";
                            $res['vimeo_files'] = array_values($all_type);
                            $data = Helper::apiResonse(1, "Success", $res);
                            return response()->json($data, 200);
                        } else {
                            $data = Helper::apiResonse(0, "This video is not downloadable from vimeo privacy", []);
                            return response()->json($data, 400);
                        }
                    } else {
                        $data = Helper::apiResonse(0, "An error occurred while fetching vimeo download files", []);
                        return response()->json($data, 400);
                    }
                } else {
                    $data = Helper::apiResonse(0, 'Chapter Id is required.', []);
                    return response()->json($data, 400);
                }
            } else {
                $data = Helper::apiResonse(0, 'Flag will be either 1 or 2', []);
                return response()->json($data, 400);
            }
        } else {
            $data = Helper::apiResonse(0, 'Flag will be either 1 or 2', []);
            return response()->json($data, 400);
        }
    }
    public function get_feature_courses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'nullable',
            'per_page' => 'nullable',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        // echo $Login_data->id;die;
        $scope = $request->scope;
        $has_wishlist = ($scope == 'learner') ? true : false;

        $settings = Settings::select('key', 'value')->where('key', 'LIKE', '%visibility%')->get()->keyBy('key');
        // $home_featured_courses_visibility = isset($settings['home_featured_courses_visibility']->value) ? stringToArray($settings['home_featured_courses_visibility']->value) : [];
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        // $is_featured_courses_visible = in_array($deviceType, $home_featured_courses_visibility);
        $deviceType = Helper::getDeviceType($request->devicetype);
        $orderby = DB::raw('ISNULL(courses.order), courses.order');
        $dir = "ASC";
        // if ($is_featured_courses_visible) {
        DB::enableQueryLog();
        $topFeatureCourses = Course::select('courses.id', "courses.id as course_id", 'title', 'is_featured', 'instructor_id', 'type', 'hours', 'minutes', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
            // ->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'wishlists' => function ($query) {
                    $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                },
                'plans',
            ])
            ->with("getPackages")
            ->with('instructor.instructure')
            ->with([
                'userCourseExpectedRelationship' => function ($query) {
                    $query->where('learner_id', request()->user_id);
                },
            ])
            ->with(['chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->withCount([
                'chapters' => function ($query) {
                    $query->where('parent_id', 0)->where('asset_type', '<>', 7);
                },
            ])
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->whereNull('courses.deleted_at')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $request->device_price_field)
            ->where('is_featured', 1)
            ->where('courses.status', 1)
            //  ->where('type',Course::COURSE)
            ->orderBy($orderby, $dir)->get();

        $topFeatureCourses = $topFeatureCourses->filter(function ($value, $key) {
            return count($value->plans) > 0;
        })->values();
        $topFeatureCourses = cpaginate($topFeatureCourses, $per_page, $page_num);
        // dd(DB::getQueryLog());

        // } else {
        //     $data = Helper::apiResonse(0, "No permission to view feature courses. Please contact Admininistor to enable the permission.", []);
        //     return response()->json($data, 400);
        // }
        $total_cnt = $topFeatureCourses->count();
        if ($total_cnt > 0) {

            if (isset($topFeatureCourses) && !empty($topFeatureCourses)) {
                foreach ($topFeatureCourses as $topFeatureCourse) {

                    if (!empty($topFeatureCourse->chapters) && !empty($topFeatureCourse->chapters->first()) && !empty($topFeatureCourse->chapters->first()->chapterIds)) {
                        $chapterIds = explode(",", $topFeatureCourse->chapters->first()->chapterIds);
                        $totalChapters = count($chapterIds);
                        $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                            ->where("learner_id", $request->user_id)
                            ->where("is_completed", 1)
                            ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                            ->where('chapters.asset_type', '!=', 4)
                            ->where('chapters.asset_type', '!=', 7)
                            ->pluck('watched_times')
                            ->first();
                        $topFeatureCourse->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                    } else {
                        $topFeatureCourse->percentage_completed = 0;
                    }
                    $expireFlag = isset($topFeatureCourse->userCourseExpectedRelationship[0]->expire_at) ? is_expired($topFeatureCourse->userCourseExpectedRelationship[0]->expire_at) : '';
                    // dd($expireFlag);

                    $topFeatureCourse->is_expire = false;
                    if ($expireFlag == 0) {
                        $topFeatureCourse->is_expire = true;
                    } else if ($expireFlag == 1) {
                        $topFeatureCourse->is_expire = false;
                    } else if ($expireFlag == 2) {
                        $topFeatureCourse->is_expire = false;
                    }
                    // dd(request()->user_id);
                    unset($topFeatureCourse->instructor_id, $topFeatureCourse->user, $topFeatureCourse->userCourseExpectedRelationship);
                    // dump($topFeatureCourse->getPackages->toArray());
                    $topFeatureCourse = Helper::get_course_common_api_data($topFeatureCourse, $request);
                    if (isset($topFeatureCourse->instructor) && !empty($topFeatureCourse->instructor)) {
                        $topFeatureCourse->instructor->profile_picture = !empty($topFeatureCourse->instructor->profile_picture) ? Helper::getImageUrl($topFeatureCourse->instructor->profile_picture) : '';
                    }
                    $topFeatureCourse->is_featured = $topFeatureCourse->is_featured == "1" ? true : '0';
                    unset($topFeatureCourse->plans, $topFeatureCourse->instructor, $topFeatureCourse->type, $topFeatureCourse->plans);

                    if (!empty($topFeatureCourse->wishlists) && $topFeatureCourse->wishlists->count() > 0 && $has_wishlist) {
                        $topFeatureCourse->is_wishlisted = true;
                        unset($topFeatureCourse->wishlists);
                    } else {
                        $topFeatureCourse->is_wishlisted = false;
                        unset($topFeatureCourse->wishlists);
                    }
                }
            }
            $data = Helper::apiResonse(1, "Success", $topFeatureCourses, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 400);
        }
    }

    public function UpdateCourseProgress(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'update_data' => 'required',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $res = [];
        $multipleUpdate = $request->input('update_data');
        //   dd($multipleUpdate);
        if (!empty($multipleUpdate)) {
            foreach ($multipleUpdate as $key) {
                if (isset($key['chapter_id']) && !empty($key['chapter_id']) && isset($key['is_completed']) && $key['is_completed'] != '') {
                    $userCourseUpdate = UserCourseProgress::where('learner_id', $request->user_id)->where('chapter_id', $request['chapter_id'])->first();

                    if (isset($userCourseUpdate) && !empty($userCourseUpdate)) { //check users not empty
                        $res[] = $userCourseUpdate;
                        if ($userCourseUpdate->is_completed == 1) {
                            $userCourseUpdate->watched_time = $key['watched_time'];
                        } else {
                            $userCourseUpdate->is_completed = $key['is_completed'];
                        }
                        $userCourseUpdate->save();
                    } else {
                        $userCourseUpdate = UserCourseProgress::updateOrCreate(
                            [
                                'learner_id' => $request->user_id,
                                'chapter_id' => $key['chapter_id'],
                            ],
                            [
                                'watched_time' => $key['watched_time'],
                                'is_completed' => $key['is_completed'],
                                'updated_at' => now()
                            ]
                        );
                        $res[] = $userCourseUpdate;
                    }
                }
            }
            $data = Helper::apiResonse(1, "Success", $res);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Please pass user progress data array", $res);
            return response()->json($data, 400);
        }
    }

    public function mySubsciption(Request $request)
    {
        $learner_id = request()->user_id;
        $deviceType = Helper::getDeviceType($request->devicetype);
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;

        $courses = Course::distinct()->select('courses.id', 'title', 'instructor_id', 'image', 'type', 'user_courses.id as user_courses_id', 'user_courses.expire_at', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'user_courses.id as course_purchase_id', 'user_courses.created_at', 'user_courses.subscription_id', 'cp.plan_type')
            // ->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->with([
                'instructor' => function ($query) {
                    $query->select('name', 'email', 'id', 'profile_picture');
                },
                'categories' => function ($query) use ($request) {
                    $query->select('dropdown_options.id', 'dropdown_options.name')->whereNull('course_categories.deleted_at');
                },
            ])
            ->with(['chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->with('userCourse:id,course_id,expire_at')
            ->withCount(['userCourse as total_enroll'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
            ->where('courses.status', '1')
            ->where('user_courses.learner_id', $learner_id)
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $request->device_price_field)
            ->whereNotNull('user_courses.is_subscription')
            ->whereNotNull('transaction_id')
            ->whereNull('courses.deleted_at')
            ->groupBy('user_courses.course_id')
            ->orderBy('user_courses.created_at', 'DESC')->get();
        // ->paginate($per_page, ['*'], 'page', $page_num);
        $courses = cpaginate($courses, $per_page, $page_num);
        // dd($courses);
        $collection = $courses->getCollection();
        $collection = $collection->filter(function ($value, $key) {
            return $value->plans->count() > 0;
        });
        $courses->setCollection($collection);
        if ($courses->count() > 0) {
            foreach ($courses as $course) {
                $now = Carbon::now();
                $current_date = Carbon::parse($now)->toDateString();
                if (isset($course->userCourse->expire_at) && $course->userCourse->expire_at != null) {
                    $course->is_expire = $course->userCourse->expire_at > $current_date ? false : true;
                } else {
                    $course->is_expire = false;
                }
                $course->course_id = $course->id;
                // $course->rating = isset($course->rating) && !empty($course->rating) ? $course->rating : 0;
                $course->instructor_name = $course->instructor->name ?? '';
                $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';
                $course->isCombineCourse = $course->type == '1' ? false : true;
                $course->packages_count = 0;
                if ($course->isCombineCourse) {
                    $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);
                }
                $course->next_billing = '-';
                // $course->is_combime
                if (isset($course->categories) && !empty($course->categories)) {
                    for ($i = 0; $i < $course->categories->count(); $i++) {
                        unset($course->categories[$i]->pivot);
                    }
                }
                if (!empty($course->chapters->first()) && !empty($course->chapters->first()->chapterIds)) {
                    // dd($course->chapters);
                    $chapterIds = explode(",", $course->chapters->first()->chapterIds);
                    // dd($chapterIds);
                    $totalChapters = count($chapterIds);
                    $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')->whereIn('chapter_id', $chapterIds)
                        ->where("learner_id", $learner_id)
                        ->where("is_completed", 1)
                        ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                        ->where('chapters.asset_type', '!=', 4)
                        ->where('chapters.asset_type', '!=', 7)
                        ->pluck('watched_times')
                        ->first();
                    $course->percentage_completed = ($totalChapters > 0 && $totalCompletedChapter > 0) ? round(($totalCompletedChapter / $totalChapters) * 100) : 0;
                } else {
                    $course->percentage_completed = 0;
                }
                $course->instructor->profile_picture = Helper::getImageUrl($course->instructor->profile_picture);
                $expireFlag = isset($course->userCourseExpectedRelationship[0]->expire_at) ? is_expired($course->userCourseExpectedRelationship[0]->expire_at) : '';
                // dd($expireFlag);

                $course->is_expire = false;
                if ($expireFlag == 0) {
                    $course->is_expire = true;
                } else if ($expireFlag == 1) {
                    $course->is_expire = false;
                } else if ($expireFlag == 2) {
                    $course->is_expire = false;
                }

                unset($course->instructor_id, $course->userCourse, $course->user_courses_id, $course->plans, $course->userCourseExpectedRelationship);
            }
            $data = Helper::apiResonse(1, "Success", $courses, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, 'No records found.', []);
            return response()->json($data, 200);
        }
    }

    // public function subscriptions(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|filled|numeric|exists:user_courses,learner_id',
    //     ]);

    //     if ($validator->fails()) {
    //         $data = Helper::apiResonse(0, $validator->messages(), []);
    //         return response()->json($data, 400);
    //     }

    //     $subscriptions = UserCourse::where('learner_id', $request->id)->get();
    //     return response()->json($subscriptions, 200);
    // }

    public function cancel_subscription(Request $request)
    {
        $learner_id = request()->user_id;
        $validator = Validator::make($request->all(), [
            // 'id' => 'required|filled|numeric|exists:user_courses,learner_id',
            'subscription_id' => 'required|filled|exists:user_courses,subscription_id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $course = UserCourse::where('subscription_id', $request->id)->where('learner_id', $learner_id)->first();
        if (!empty($course)) {

            $commonController = new CommonController();
            $commonController->cancelSubscription($request->id);
        }

        $data = Helper::apiResonse(1, "Success", []);
        return response()->json($data, 200);
    }

    public function purchaseOrder(Request $request)
    {

        // $userCourse = UserCourse::where("learner_id", auth()->user()->id)->orderBy('id',"desc")
        // ->select(DB::raw('DATE_FORMAT(user_courses.created_at, "%d-%b-%Y") as formatted_dob'),DB::raw("GROUP_CONCAT(id) AS user_course_id"))
        // ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d-%m')"))
        // ->get();
        $userCourse = UserCourse::where("learner_id",$request->user_id)
            ->select(DB::raw('YEAR(created_at) year, monthName(created_at) month'), DB::raw("GROUP_CONCAT(id) AS user_course_id"))
            ->groupBy('year', 'month')
            ->orderBy("created_at", "desc")
            ->get();

            $igst = config()->has('settings.igst') ? config('settings.igst') : null;
            $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
            $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;


        $order_data = collect([]);
        $userCourse->map(function ($value, $key)use($igst,$sgst,$cgst) {
            $value->order_data = UserCourse::select("id as order_id", "learner_id","coupon_id", "course_id", "transaction_id", "payment_gateway", "order_status", "payment_gateway", "invoice", "created_at", "after_deduction_price as after_deduction_price", "price", "per_coin_price", "user_coin","after_coupon_applied_deduction_price","country_id","state_id")
                ->with(['newCourse' => function ($q) {
                    $q->select("id", "title");
                }])->with(["getCoupon:code,id","stateName:id,name"])
                ->whereIn("id", explode(",", $value->user_course_id))->orderBy('created_at', "desc")->get();

            $value->order_data->filter(function ($value, $key)use($igst,$sgst,$cgst) {
                $value->order_status = $value->order_status;
                // $value->payment_gateway = isset($value->payment_gateway) ? $value->payment_gateway == 1 ? "razorpay" : "instamojo" : '';
                if($value->payment_gateway==1) {
                    $value->payment_gateway =  "Razorpay";
                }
                elseif($value->payment_gateway==2) {
                    $value->payment_gateway =  "Instamojo";
                }
                elseif($value->payment_gateway==3) {
                    $value->payment_gateway= "In-app purchase";
                } else {
                    $value->payment_gateway =  "";
                }
                $value->invoice = isset($value->invoice) ? url("storage/" . $value->invoice) : '';
                $value->after_deduction_price = $value->after_deduction_price;
                $value->price = $value->price;
                // $value->per_coin_price = $value->user_coin * $value->per_coin_price;
                $value->per_coin_price = $value->per_coin_price;
                // $value->created_at_new = \Carbon\Carbon::parse($value->created_at)->format('y-m-d H:i:s');
                $value->created_at_new = $value->created_at->format('Y-m-d H:i:s');
                $value->coupon_name = isset($value->getCoupon) ? $value->getCoupon->code :'';
                $value->coupon_amount = $value->after_coupon_applied_deduction_price ;


                // $value->text = "";
                // $sgst_price = 0.0;
                // $cgst_price = 0.0;
                // $igst_price = 0.0;

                // if ($value->country_id == 1) {
                //     if (strtolower(@$value->stateName->name) == 'gujarat') {
                //         $value->gst_amount = gstCal($value, $sgst) +  gstCal($value, $cgst);

                //     } else {
                //         $value->gst_amount = gstCal($value, $igst);
                //     }
                // }
            });
        });
        if ($userCourse->count() > 0) {

            $data = Helper::apiResonse(1, "Success", $userCourse);
            return response()->json($data, 200);
        } else {

            $data = Helper::apiResonse(1, "Success", []);
            return response()->json($data, 200);
        }
    }

    public function getUserCoin(Request $request)
    {
        $coin = UserCoin::select("id", "learner_id", "course_id", "coins", "type", "created_at", "comment", "chapter_id")->with("getCourse:id,title")->where("learner_id", $request->user_id)->with("getChapter:id,title")->get();


        // $plus = UserCoin::where("learner_id",$request->user_id)->where("type","!=",2)->Orwhere("type","!=",2)->sum("coins");
        $plus = UserCoin::where("learner_id", $request->user_id)->where(function ($q) {
            $q->where("type", "!=", 2)->where("type", "!=", 4);
        })->sum("coins");


        // $deduct = UserCoin::where("learner_id", $request->user_id)->where("type", 2)->Orwhere("type", 4)->sum("coins");
        $deduct = UserCoin::where("learner_id", $request->user_id)
                ->where(function ($query) {
                    $query->where("type", "=", 2)
                        ->orWhere("type", "=", 4);
                })->sum("coins");

        $total  = $plus - $deduct;



        $result['total_coin'] = $total;
        $result['coin_history'] = $coin;

        $result['coin_history']->map(function ($val, $key) {
            // $val->created_new_at = \Carbon\Carbon::parse($val->created_at)->format('y-m-d H:i:s');
            $val->chapter_id = $val->chapter_id != null ? $val->chapter_id : '';
            $val->chapter_name = $val->getChapter != null ? $val->getChapter->title : '';
            $val->title = $val->getCourse != null ? $val->getCourse->title : '';
            $val->course_id = $val->course_id != null ? $val->course_id : '';
            $val->comment = $val->comment != null ? $val->comment : '';
        });
        $data = Helper::apiResonse(1, "Success", $result);
        return response()->json($data, 200);
    }


    // public function getUserCoin()
    // {
    //     $coin = UserCoin::select("id", "learner_id", "course_id", "coins", "type", "created_at")
    //         ->with("getCourse:id,title")
    //         ->where("learner_id", 665)
    //         ->get();

    //     $coin_redeem = UserCoin::where("type", "2")
    //         ->where("learner_id", auth()->user()->id)
    //         ->sum("coins");

    //     $coin_earn = UserCoin::where("type", "1")
    //         ->where("learner_id", auth()->user()->id)
    //         ->sum("coins");

    //     $total = $coin_earn - $coin_redeem;

    //     $result['coin_history'] = $coin;

    //     $result['coin_history']->map(function ($val, $key) {
    //         $val->created_new_at = \Carbon\Carbon::parse($val->created_at)->format('Y-m-d H:i:s');
    //     });

    //     $data = new GetUserCollection($result['coin_history']);
    //     // dd($data->resource);
    //     $datas = Helper::apiResonse(1, "Success", $data->resource);
    //     return response()->json($datas, 200);
    // }



    public function getVideo(Request $request)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dev.vdocipher.com/api/videos/797ab37b8cf54b549bbf0726e1fc9742",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: Apisecret XgCDaoAfDXPFcBdHvO5I6AyOAuVx87Nb9gXRQaAcCw3oYplPkvkqL4d36gvYFEgE",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        // if ($err) {
        // echo "cURL Error #:" . $err;
        // } else {
        // echo $response;
        // }

        // dd($response);
        $dataArray = json_decode($response, true);

        return response()->json($dataArray);
    }
}
