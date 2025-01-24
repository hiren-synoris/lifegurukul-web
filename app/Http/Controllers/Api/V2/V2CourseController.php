<?php

namespace App\Http\Controllers\Api\V2;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Instructors;
use App\Models\Learner;
use App\Models\Media;
use App\Models\RatingReview;
use App\Models\User;
use App\Models\UserCoin;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class V2CourseController extends Controller
{
    public function myCourse(Request $request)
    {

        $learner_id = request()->user_id;
        $deviceType = Helper::getDeviceType($request->devicetype);
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;

        //$courses = Cache::get('my_courses');

        // if ($courses === null) {
        // $courses = Cache::remember('cached_courses', 3, function () use ($deviceType, $request, $learner_id) {
        $courses = Course::distinct()
            ->select('courses.id', 'title', 'instructor_id', 'image', 'type', 'user_courses.id as user_courses_id', 'hours', 'minutes', 'cp.list_price', 'cp.final_payable_price', 'user_courses.id as course_purchase_id', 'user_courses.created_at', 'cp.plan_type')
        // ->where("id",55555)
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
            ->with([
                'userCourse' => function ($query) use ($learner_id) {
                    $query->where('learner_id', $learner_id);
                },
            ])
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
        // ->where('courses.status', '1')
            ->where('user_courses.learner_id', $learner_id)
            ->where('user_courses.order_status', "!=", 2)
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->whereNull('courses.deleted_at')
            ->groupBy('courses.id')
            ->orderBy('user_courses.id', 'DESC')
            ->get();
        // });

        // Cache::put('my_courses', $courses, 0.1);
        // }

        $courses = cpaginate($courses, $per_page, $page_num);

        $collection = $courses->getCollection();
        // $collection = $collection->filter(function ($value, $key) {
        //     return $value->plans->count() > 0;
        // });

        $courses->setCollection($collection);
        if ($courses->count() > 0) {
            foreach ($courses as $course) {

                $sum = 0;
                $course->getChapters->filter(function ($v) use ($course, &$sum) {
                    $v->chpter_count = Chapter::whereHas("chapterInfo")
                        ->where('asset_type', '!=', 4)
                        ->where('asset_type', '!=', 7)
                        ->with('media:id,path,videoId')->where('parent_id', $v->id)->count();
                    $sum += $v->chpter_count;
                });

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

                if (isset($course->categories) && !empty($course->categories)) {
                    for ($i = 0; $i < $course->categories->count(); $i++) {
                        unset($course->categories[$i]->pivot);
                    }
                }
                if (!empty($course->chapters->first()) && !empty($course->chapters->first()->chapterIds)) {
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

    public function myCourseDetail(Request $request)
    {
        // dd($request->all());
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
            $course = Course::select('courses.id', 'courses.course_platform', 'courses.id as course_id', 'courses.title', 'courses.image', 'courses.instructor_id', "courses.description", "courses.how_to_use", "courses.type", "courses.lng", "courses.hours", "courses.minutes", "courses.type", 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type', 'courses.show_learner_cnt')
                ->where('courses.id', $request->course_id)
                ->whereRaw("find_in_set($deviceType , course_platform)")
                ->with(['chaptersCounts' => function ($query) {
                    $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                    $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                }])
                ->with("LearnerCourse:id,course_id,learner_id,plan_id,expire_at")
                ->with("user:id,name")
                ->with(['getPackages' => function ($query) {
                    $query->select('course_id', 'package_id', DB::raw('GROUP_CONCAT(package_id) as package_ids'))->groupBy('course_id');
                }])
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
                        $query->select("id", "course_id", "asset_type", "title");
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

                    'chapters.chapterInfo:id,title,chapter_id,asset_type,upload_type,allow_download,allow_sleep,pdf_page_count',

                ])->whereHas("plansV2")
                ->withAvg('rating_reviews as rating', 'rating')

                ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
                ->withCount(['rating_reviews as total_new_review'])
                ->withCount("userCourseCount")

                ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                ->first();

            // dd($course->toArray());

            if ($course) {
                $perPage = $request->input('per_page', 10);
                $page = $request->input('page', 1);
                $star = $request->input('star');
                $ratingReviewsQuery = $course->rating_reviews()->select('id', 'learner_id', 'course_id', 'comment', 'rating', 'learner_id', 'created_at')
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

                $course->lng = $course->lng == 2 ? 'Hindi' : 'English';
                $course->show_learner_cnt = $course->show_learner_cnt == 0 ? false : true;
                $course->rating = $course->rating ?? 0;
                $course->tot_chapterCount = $course->chaptersCounts->count() > 0 ? count(explode(',', $course->chaptersCounts[0]->chapterIds)) : 0;

                $main_user_course = $course->newLearnerCourse($request)->first();
                $expireFlag = is_expired($main_user_course?->expire_at);

                $course->expire_at = $main_user_course?->expire_at;
                if ($expireFlag == 0) {
                    $course->is_expire = true;
                    $course->validity = 'Expired';
                } else if ($expireFlag == 1) {
                    $course->is_expire = false;
                    $course->validity = dateFormate($main_user_course?->expire_at) . ' Days';
                } else if ($expireFlag == 2) {
                    $course->is_expire = false;
                    $course->validity = 'Lifetime';
                }
                if ($course->type == 1) {
                    $user_cousre = UserCourse::where("learner_id", $request->user_id)->where('order_status', "!=", 2)->where("course_id", $course?->getNewPackages?->package_id)->first();
                    // $new_course = $course->newLearnerCourse($request)->first();
                    if ($user_cousre != null) {
                        $course->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $course->isCombineCourse = false;
                    } else if ($main_user_course) {
                        $course->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $new_course->id : '';
                        $course->isCombineCourse = false;
                    } else {
                        $course->is_course_purchased = false;
                        $course->course_purchase_id = '';
                        $course->isCombineCourse = false;
                    }
                } else if ($course->type == 2) {
                    if ($main_user_course) {
                        $course->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $course->isCombineCourse = true;
                    } else {
                        $course->is_course_purchased = false;
                        $course->course_purchase_id = '';
                        $course->isCombineCourse = true;
                    }
                }

                $course->chaptersCounts->filter(function ($fl) use ($course) {
                    return $course->new_chapterIds = $fl->chapterIds;
                });

                // dd($course->toArray());

                $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $course->new_chapterIds))->where("learner_id", $request->user_id)->where("is_completed", 1)->count();

                $course->percentage_completed = round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $course->new_chapterIds))) . "" : "0";
                $scope = $request->scope;
                $has_wishlist = ($scope == 'learner') ? true : false;
                $course->is_wishlisted = isset($course->wishlists) && $course->wishlists->count() > 0 && $has_wishlist ? true : false;
                $course->instructor_name = $course->user->name ?? '';
                $course->isCombineCourse = $course->type == '1' ? false : true;
                $course->coin_price = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : "";
                $course->course_coin = isset($course->course_coin) ? $course->course_coin : "";
                $course->learner_count = $course->user_course_count_count;
                $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);

                // dd($course->packages_count);
                // $coin = UserCoin::where("learner_id", @auth()->user()->id)->get();
                // if ($coin) {
                //     $coin_redeem = $coin->where("type", "2")->sum("coins");
                //     $coin_earn = $coin->where("type", "1")->sum("coins");
                //     $total = $coin_earn - $coin_redeem;
                //     $course->user_coin = $total;
                // } else {
                //     $course->user_coin = 0;
                // }
                $coin = UserCoin::where("learner_id", $request->user_id)->get();

                if ($coin) {
                    $plus = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $course->user_coin = $total;
                } else {
                    $course->user_coin = 0;
                }
                // dd($course->chapters);
                $course->lessons = new Collection([]);

                $course->lessons = $course->chapters;
                $course->lessons->filter(function ($val) {
                    $val->chapter_id = $val->chapterInfo->id;
                    $val->asset_type = "Heading";
                    $val->upload_type = $val->chapterInfo->upload_type;
                    $val->allow_download = $val->chapterInfo->allow_download == 1 ? true : false;
                    $val->allow_sleep = $val->chapterInfo->allow_sleep == 1 ? true : false;
                    $val->playback_info = collect([
                        'isCompleted' => isset($value->userCourseProgress) && !empty($value->userCourseProgress) && $value->userCourseProgress->is_completed == 1 ? true : false,
                        'watchedTime' => isset($value->userCourseProgress) && !empty($value->userCourseProgress) ? $value->userCourseProgress->watched_time : '',
                    ]);
                });

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

    public function courseDetail(Request $request)
    {
        // dd("");
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

            // $course = Cache::get('courseDetail');

            // if ($course === null) {packages_count

            $course = Course::select('courses.id', 'courses.course_coin', 'courses.title', 'courses.image', 'courses.instructor_id', "courses.description", "courses.how_to_use", "courses.type", "courses.lng", "courses.hours", "courses.minutes", 'courses.image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type', 'courses.show_learner_cnt')
                ->where('courses.id', $request->course_id)
            // ->with("LearnerCourse:id,course_id,learner_id,plan_id,expire_at")
                ->with([
                    'lessons' => function ($query) {
                        $query->where('parent_id', 0);
                        $query->select("course_id", "id", "title", "asset_type");
                    },
                    'instructor:id,name',
                    'wishlists' => function ($query) {
                        $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
                    },

                    'instructor:id,name',
                    // 'chapters.chapterInfo:id,title,chapter_id,asset_type',
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
            // ->with(['getNewPackages' => function ($query) {
            //     $query->select('course_id','package_id', DB::raw('GROUP_CONCAT(package_id) as package_ids'))->groupBy('course_id');
            // }])
                ->with(['userCourseCount' => function ($query) {
                    $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                        ->groupBy('course_id');
                }])
                ->with('getNewPackages')
                ->withcount("packages")
                ->with(['chaptersCounts' => function ($query) {
                    $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                    $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                }])

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
            // ->where('courses.status', '1')
            // ->where('cp.status', '1')

                ->first();
            // dd(auth()->guard("learner")->user()->id);
            // dd($course->toArray());
            //     Cache::put('courseDetail', $course, 1);
            // }

            if (!empty($course)) {
                $course->lessons = $course->lessons->sortBy('order');

                $rating_check_once = RatingReview::where("learner_id", request()->user_id)->where("course_id", $request->course_id)->first();

                if ($rating_check_once) {
                    $course->rating_status = true;
                } else {
                    $course->rating_status = false;
                }

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

                // $course->packages_count = $course->packages_count;
                $course->rating_reviews = $ratingReviewsPaginated;
                if (isset($course->instructor->id)) {
                    $course->instructor->profile_picture = !empty($course->user->profile_picture) ? Helper::getImageUrl($course->user->profile_picture) : '';
                    $course->instructor->name = $course->user->name;
                    $course->instructor->email = $course->user->email;
                    $designation = Instructors::select('designation')->where('user_id', $course->instructor->id)->first();
                    $course->instructor->designation = $designation->designation ?? '';
                    // $course->instructor->rating = '';
                }

                $course->lng = $course->lng == 2 ? 'Hindi' : 'English';
                $course->show_learner_cnt = $course->show_learner_cnt == 0 ? false : true;
                $course->rating = $course->rating ?? 0;
                $course->tot_chapterCount = $course->chaptersCounts->count() > 0 ? count(explode(',', $course->chaptersCounts[0]->chapterIds)) : 0;
                // $expire_at = $course->newLearnerCourse($request)?->where([["course_id", $course->id]])->orderBy("id","desc")->first()->expire_at ?? '';
                $main_user_course = $course->newLearnerCourse($request)->first();

                $expireFlag = is_expired($main_user_course?->expire_at);

                $course->expire_at = $main_user_course?->expire_at;
                if ($expireFlag == 0) {
                    $course->is_expire = true;
                    $course->validity = 'Expired';
                } else if ($expireFlag == 1) {
                    $course->is_expire = false;
                    $course->validity = dateFormate($main_user_course?->expire_at) . ' Days';
                } else if ($expireFlag == 2) {
                    $course->is_expire = false;
                    $course->validity = 'Lifetime';
                }

                $course->plans->filter(function ($plan_date) {
                    if ($plan_date->course_limit == 1) {
                        if (isset($plan_date->is_fixed_date) && $plan_date->access_value != null) {
                            if ($plan_date->is_fixed_date == 2) { // add number of days

                                $valid_till = $plan_date->access_value;
                            }
                            if ($plan_date->is_fixed_date == 1) {
                                $valid_till = dateFormate($plan_date->access_value);
                            }
                            $valid_till = $valid_till . ' Days';
                        }
                    } else {
                        $valid_till = "Lifetime";
                    }

                    $plan_date->validity = $valid_till;

                    $plan_date->productId = $plan_date->renewing_subscriptions_id;
                });

                $course->is_course_purchased = false;
                $course->course_purchase_id = '';
                $course->isCombineCourse = false;

                if ($course->type == 1) {

                    if ($course->getPackages()->exists()) {

                        foreach ($course->getPackages as $package) {
                            $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->package_id)->first();

                            if ($user_cousre != null) {
                                $course->is_course_purchased = isUserCourseExpired($user_cousre->expire_at);
                                $course->course_purchase_id = isUserCourseExpired($user_cousre->expire_at) == true ? $user_cousre->id : '';
                                $course->isCombineCourse = false;
                            } else {

                                $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->course_id)->first();
                                if ($user_cousre != null) {
                                    $course->is_course_purchased = isUserCourseExpired($user_cousre?->expire_at);
                                    $course->course_purchase_id = isUserCourseExpired($user_cousre?->expire_at) == true ? $user_cousre?->id : '';
                                    $course->isCombineCourse = false;
                                }
                                // else {
                                //     $course->is_course_purchased = false;
                                //     $course->course_purchase_id = '';
                                //     $course->isCombineCourse = false;
                                // }
                            }
                        }
                    } else if ($main_user_course) {
                        $course->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $course->isCombineCourse = false;
                    } else {

                        $course->is_course_purchased = false;
                        $course->course_purchase_id = '';
                        $course->isCombineCourse = false;
                    }
                } else if ($course->type == 2) {
                    // $new_course = $course->newLearnerCourse($request)->first();packages_count

                    if ($main_user_course) {
                        $course->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $course->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $course->isCombineCourse = true;
                    } else {
                        $course->is_course_purchased = false;
                        $course->course_purchase_id = '';
                        $course->isCombineCourse = true;
                    }
                }

                $scope = $request->scope;
                $has_wishlist = ($scope == 'learner') ? true : false;
                $course->is_wishlisted = isset($course->wishlists) && $course->wishlists->count() > 0 && $has_wishlist ? true : false;
                $course->instructor_name = $course->user->name ?? '';
                $course->isCombineCourse = $course->type == '1' ? false : true;
                $course->image = isset($course?->image) ? Helper::getImageUrl($course?->image) : "";
                $course->coin_price = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : "";
                $course->course_coin = isset($course->course_coin) ? $course->course_coin : "";
                // $course->learner_count = $course->user_course_count_count;
                $collection_sum = collect($course->userCourseCount);
                $course->learner_count = $collection_sum->sum('user_course_count');

                // $course->packages_count = Helper::get_package_course_count($course->id, $deviceType);
                $course->packages_count = "";

                // $coin = UserCoin::where("learner_id", auth()->user()->id)->get();
                // if ($coin) {
                //     $coin_redeem = $coin->where("type", "2")->sum("coins");
                //     $coin_earn = $coin->where("type", "1")->sum("coins");
                //     $total = $coin_earn - $coin_redeem;
                //     $course->user_coin = $total;
                // } else {
                //     $course->user_coin = 0;
                // }packages_count
                $coin = UserCoin::where("learner_id", $request->user_id)->get();

                if ($coin) {
                    $plus = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "!=", 2);
                            $query->Where("type", "!=", "4");
                        })->sum("coins");

                    $deduct = UserCoin::where("learner_id", $request->user_id)
                        ->where(function ($query) {
                            $query->where("type", "=", 2)
                                ->orWhere("type", "=", 4);
                        })->sum("coins");

                    $total = $plus - $deduct;

                    $course->user_coin = $total;
                } else {
                    $course->user_coin = 0;
                }

                $course->lessons->filter(function ($val) {
                    $val->asset_type = "Heading";
                    $val->lesson_item = Chapter::select("id", "course_id", "title", "asset_type", "file_url", "media_id", "upload_type")->with("media:id,path")->with(["chapterInfo:id,pdf_page_count,chapter_id"])->where("parent_id", $val->id)->where("asset_type", "!=", 7)->get();
                    $val->lesson_item->filter(function ($value) {
                        $value->path = $value->media_id != '' ? url($value->media->path) : $value->file_url;
                        $value->pdf_count = isset($value->chapterInfo) ? $value->chapterInfo->pdf_page_count : 0;
                        $value->upload_type = $value->upload_type;
                        $value->asset_type = Helper::getChapterAssetType($value->asset_type ?? '');
                        $value->chapter_id = $value?->chapterInfo?->id;
                    });
                });

                if ($course->isCombineCourse) {

                    $course->course_list = new Collection([]);

                    $course->course_list = CoursePackage::select("id", "course_id", 'package_id')
                        ->whereHas(
                            "courses",
                            function ($q) use ($deviceType) {
                                $q->whereRaw("find_in_set(?, course_platform)", [$deviceType]);
                                $q->select("id", "title", "image", "course_platform", "type", "hours", "minutes");
                                $q->whereNull('deleted_at');
                                // $q->where("status",1);
                            },
                        )

                        ->with([
                            "userCourseV2" => function ($q) {
                                $q->select("id", "created_at");
                            }, "courseV2" => function ($q) {
                                $q->select("course_id", "learner_id", "id", "expire_at");
                            },
                        ])
                        ->with(['getPackagesV2' => function ($query) {
                            $query->groupBy('user_courses.learner_id');
                            $query->select("course_id", "learner_id", "id", "expire_at");
                        }])

                        ->with(['chaptersCountsV2' => function ($query) {
                            $query->select('course_id', 'asset_type', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                            $query->where("asset_type", "<>", "4")->where("asset_type", "<>", "7");
                        }])
                        ->withcount('rating_reviews as total_review')
                        ->whereHas("getPlan",function($q){
                            $q->where("status",1);
                        })
                        ->with("getPlan:course_id,list_price,final_payable_price,plan_type")
                        ->withAvg('ratingReviewsV2 as rating', 'rating')->withCount(['ratingReviewsV2 as total_review'])
                        ->where("package_id", $request->course_id)->get();

                        $course->packages_count = $course->course_list->count();

                    $course->course_list->filter(function ($value) use ($request) {

                        $value->chaptersCountsV2->filter(function ($fl) use ($value) {
                            return $value->new_chapterIds = $fl->chapterIds;
                        });

                        $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $value->new_chapterIds))->where("learner_id", $request->user_id)->where("is_completed", 1)->count();

                        $value->percentage_completed = round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $value->new_chapterIds))) . "" : "0";

                        $value->course_id = $value?->courses?->id;
                        $value->title = $value?->courses?->title;
                        $value->image = isset($value?->courses) ? Helper::getImageUrl($value?->courses?->image) : "";
                        $value->list_price = $value?->getPlan?->list_price;
                        $value->final_payable_price = $value?->getPlan?->final_payable_price;
                        $value->plan_type = $value?->getPlan?->plan_type;
                        $value->rating = $value->rating ?? 0;
                        $value->type = $value?->course?->type;
                        $value->hours = $value?->course?->hours;
                        $value->minutes = $value?->course?->minutes;
                        $value->total_enroll = $value?->getPackagesV2->count();
                        $value->created_at = $value?->userCourseV2?->created_at;
                        $value->tot_chapterCount = $value?->chaptersCountsV2->count() > 0 ? count(explode(',', $value?->chaptersCountsV2[0]->chapterIds)) : 0;

                        // $main_user_course1 = $value->newLearnerCoursePackage($request, $value->course_id)->first();
                        $main_user_course1 = $value->newLearnerCoursePackage($request, $request->course_id)->first();
                        $expireFlag = is_expired($main_user_course1?->expire_at);

                        $value->expire_at = $main_user_course1?->expire_at;
                        if ($expireFlag == 0) {
                            $value->is_expire = true;
                            $value->validity = 'Expired';
                        } else if ($expireFlag == 1) {
                            $value->is_expire = false;
                            $value->validity = dateFormate($main_user_course1?->expire_at) . ' Days';
                        } else if ($expireFlag == 2) {
                            $value->is_expire = false;
                            $value->validity = 'Lifetime';
                        }
                        $value->is_course_purchased = false;
                        $value->course_purchase_id = '';
                        $value->isCombineCourse = false;

                        if ($value->courses->type == 1) {
                            // $user_cousre_pck = UserCourse::where("learner_id", $request->user_id)->where("course_id", $value->package_id)->first();

                            if ($value->courses->getPackages()->exists()) {

                                foreach ($value->courses->getPackages as $package) {
                                    $user_cousre_pck = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->package_id)->first();

                                    if ($user_cousre_pck != null) {
                                        $value->is_course_purchased = isUserCourseExpired($user_cousre_pck->expire_at);
                                        $value->course_purchase_id = isUserCourseExpired($user_cousre_pck->expire_at) == true ? $user_cousre_pck->id : '';
                                        $value->isCombineCourse = false;
                                    } else {

                                        $user_cousre_pck = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->course_id)->first();
                                        if ($user_cousre_pck != null) {
                                            $value->is_course_purchased = isUserCourseExpired($user_cousre_pck?->expire_at);
                                            $value->course_purchase_id = isUserCourseExpired($user_cousre_pck?->expire_at) == true ? $user_cousre_pck?->id : '';
                                            $value->isCombineCourse = false;
                                        }
                                        // else {
                                        //     $value->is_course_purchased = false;
                                        //     $value->course_purchase_id = '';
                                        //     $value->isCombineCourse = false;
                                        // }
                                    }
                                }
                            } else if ($main_user_course1) {

                                // $new_course = $value->newLearnerCoursePackage($request,$value->course_id)->first();
                                $value->is_course_purchased = isUserCourseExpired($main_user_course1->expire_at);
                                $value->course_purchase_id = isUserCourseExpired($main_user_course1->expire_at) == true ? $main_user_course1->id : '';
                                $value->isCombineCourse = false;
                            } else {
                                $value->is_course_purchased = false;
                                $value->course_purchase_id = "";
                                $value->isCombineCourse = false;
                            }
                        }

                        unset($value->getPackagesV2, $value->new_chapterIds, $value->userCourseV2, $value->courseV2, $value->chaptersCountsV2, $value->getPlan,$value->courses);
                    });
                    //   $course->packages_count = count($course->course_list);
                }

                $coupons = get_coupon_by_learner(request()->user_id, $request->course_id);
                if ($coupons->count() > 0) {

                    $course->is_coupon_available = true;
                } else {

                    $course->is_coupon_available = false;
                }

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

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $deviceType = Helper::getDeviceType($request->devicetype);

        DB::enableQueryLog();
        // $course = Cache::get('course_fea');

        // if ($course === null) {

        $course = Course::select('courses.id', 'courses.order', 'title', 'type', 'instructor_id', 'image', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price', 'courses.is_featured', 'courses.is_free')
            ->whereRaw("find_in_set($deviceType , course_platform)")
        // ->with("LearnerCourse:id,learner_id,course_id,plan_id")
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'wishlists' => function ($query) {
                $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
            }, 'plans' => function ($query) {
                return $query->where('status', 1)->select('id', 'status');; //->where('plan_type', 0);
            }])
            ->with(['wishlists' => function ($query) {
                $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
            }])
            ->whereHas("user")
            ->withcount("getPackages")
            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            }, 'rating_reviews as reviews'])
            ->withCount(['rating_reviews as reviews'])
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->whereNull('courses.deleted_at')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $request->device_price_field)
            ->where('courses.status', 1)
            ->where("is_featured", 1)
            ->orderBy("courses.id", "desc")
            ->groupBy("courses.id")->get();
        $course = $course->sortBy(function ($e) {
            if (isset($e->order)) {
                return $e->order;
            } else {
                return PHP_INT_MAX;
            }
        });
        $course = cpaginate($course, $per_page, $page_num);
        //     Cache::put('course_fea', $course, 1);
        // }

        if ($course) {

            if (isset($course) && !empty($course)) {
                $course->filter(function ($slider_detail) use ($request, $deviceType) {
                    $scope = $request->scope;
                    $has_wishlist = ($scope == 'learner') ? true : false;
                    $slider_detail->is_wishlisted = isset($slider_detail->wishlists) && $slider_detail->wishlists->count() > 0 && $has_wishlist ? true : false;

                    $slider_detail->is_free = $slider_detail->is_free == 1 ? true : false;
                    $slider_detail->is_featured = $slider_detail->is_featured == 1 ? true : false;
                    $slider_detail->packages_count = Helper::get_package_course_count($slider_detail->id, $deviceType);
                    $slider_detail->image = !empty($slider_detail->image) ? Helper::getImageUrl($slider_detail->image) : '';

                    $main_user_course = $slider_detail->HomeLearnerCourse($request, $slider_detail->id)->first();
                    $slider_detail->is_course_purchased = false;
                    $slider_detail->course_purchase_id = '';
                    $slider_detail->isCombineCourse = false;
                    if ($slider_detail->type == 1) {
                        // $user_cousre = UserCourse::where("learner_id", $request->user_id)->where("course_id", $slider_detail?->getNewPackages?->package_id)->first();

                        if ($slider_detail->getPackages()->exists()) {

                            foreach ($slider_detail->getPackages as $package) {
                                $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->package_id)->first();
                                if ($user_cousre != null) {
                                    $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre->expire_at);
                                    $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre->expire_at) == true ? $user_cousre->id : '';
                                    $slider_detail->isCombineCourse = false;
                                } else {

                                    $user_cousre = UserCourse::where("learner_id", $request->user_id)->where('order_status', "!=", 2)->where("course_id", $package->course_id)->orderBy("id","desc")->first();
                                    if ($user_cousre != null) {
                                        $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre?->expire_at);
                                        $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre?->expire_at) == true ? $user_cousre?->id : '';
                                        $slider_detail->isCombineCourse = false;
                                    }
                                    //  else {
                                    //     $slider_detail->is_course_purchased = false;
                                    //     $slider_detail->course_purchase_id = '';
                                    //     $slider_detail->isCombineCourse = false;
                                    // }
                                }
                            }
                        } else if ($main_user_course) {
                            $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);;
                            $slider_detail->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                            $slider_detail->isCombineCourse = false;
                        } else {

                            $slider_detail->is_course_purchased = false;
                            $slider_detail->course_purchase_id = '';
                            $slider_detail->isCombineCourse = false;
                        }
                    } else if ($slider_detail->type == 2) {
                        if ($main_user_course) {
                            $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);;
                            $slider_detail->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                            $slider_detail->isCombineCourse = true;
                        } else {
                            $slider_detail->is_course_purchased = false;
                            $slider_detail->course_purchase_id = '';
                            $slider_detail->isCombineCourse = true;
                        }
                    }
                });
            }

            $data = Helper::apiResonse(1, "Success", $course, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 400);
        }
    }

    public function getWishlist(Request $request)
    {
        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $deviceType = Helper::getDeviceType($request->devicetype);

        $course = Course::select('courses.id', 'wishlists.id as wishlist_id', 'title', 'is_featured', 'instructor_id', 'type', 'hours', 'minutes', 'image', 'cp.list_price', 'cp.final_payable_price', 'cp.plan_type')
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
            ->join('wishlists', 'courses.id', '=', 'wishlists.course_id')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $request->device_price_field)
            ->where('cp.status', '1')
            ->where('wishlists.learner_id', request()->user_id)
            ->where('courses.status', 1)
            ->whereNull('wishlists.deleted_at')
            ->orderBy("wishlists.id", "desc")
            ->get();

        // $course = cpaginate($course, $per_page, $page_num);

        if ($course) {
            $course->filter(function ($slider_detail) use ($request, $deviceType) {
                $scope = $request->scope;
                $has_wishlist = ($scope == 'learner') ? true : false;
                $slider_detail->is_wishlisted = isset($slider_detail->wishlists) && $slider_detail->wishlists->count() > 0 && $has_wishlist ? true : false;
                $slider_detail->course_id = $slider_detail->id;
                $slider_detail->isCombineCourse = $slider_detail->type == 1 ? false : true;
                $slider_detail->instructor_name = @$slider_detail->instructor->name;
                $slider_detail->is_free = $slider_detail->is_free == 1 ? true : false;
                $slider_detail->is_featured = $slider_detail->is_featured == 1 ? true : false;
                $slider_detail->packages_count = Helper::get_package_course_count($slider_detail->id, $deviceType);
                $slider_detail->image = !empty($slider_detail->image) ? Helper::getImageUrl($slider_detail->image) : '';
                $main_user_course = $slider_detail->HomeLearnerCourse($request, $slider_detail->id)->first();
                $slider_detail->is_course_purchased = false;
                $slider_detail->course_purchase_id = '';
                $slider_detail->isCombineCourse = false;
                if ($slider_detail->type == 1) {
                    // $user_cousre = UserCourse::where("learner_id", $request->user_id)->where("course_id", $slider_detail?->getNewPackages?->package_id)->first();

                    if ($slider_detail->getPackages()->exists()) {

                        foreach ($slider_detail->getPackages as $package) {
                            $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->package_id)->first();
                            if ($user_cousre != null) {
                                $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre->expire_at);
                                $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre->expire_at) == true ? $user_cousre->id : '';
                                $slider_detail->isCombineCourse = false;
                            } else {

                                $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->course_id)->first();
                                    if ($user_cousre != null) {
                                        $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre?->expire_at);
                                        $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre?->expire_at) == true ? $user_cousre?->id : '';
                                        $slider_detail->isCombineCourse = false;
                                    }
                                    //  else {
                                    //     $slider_detail->is_course_purchased = false;
                                    //     $slider_detail->course_purchase_id = '';
                                    //     $slider_detail->isCombineCourse = false;
                                    // }
                            }
                        }
                    } else if ($main_user_course) {
                        $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $slider_detail->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $slider_detail->isCombineCourse = false;
                    } else {
                        $slider_detail->is_course_purchased = false;
                        $slider_detail->course_purchase_id = '';
                        $slider_detail->isCombineCourse = false;
                    }
                } else if ($slider_detail->type == 2) {
                    if ($main_user_course) {
                        $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);
                        $slider_detail->course_purchase_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                        $slider_detail->isCombineCourse = true;
                    } else {
                        $slider_detail->is_course_purchased = false;
                        $slider_detail->course_purchase_id = '';
                        $slider_detail->isCombineCourse = true;
                    }
                }

                return !$slider_detail->is_course_purchased;
            });

            // dd($filteredCourse);
            // $filteredCourse = $filteredCourse->values();

            $paginatedCourse = cpaginate($course, $per_page, $page_num);

            $data = Helper::apiResonse(1, "Success", $paginatedCourse, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 200);
        }
    }
}
