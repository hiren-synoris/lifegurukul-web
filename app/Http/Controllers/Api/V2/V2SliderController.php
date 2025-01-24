<?php

namespace App\Http\Controllers\Api\V2;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Course;
use App\Models\Dropdown;
use App\Models\DropdownOption;
use App\Models\Instructors;
use App\Models\Notification;
use App\Models\Settings;
use App\Models\Slider;
use App\Models\User;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class V2SliderController extends Controller
{
    public function home(Request $request)
    {

        $header_data = $request->header();
        // dd($request  ->header('devicetype'));
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required', 'filled', 'min:1', 'max:3'],
            'devicetoken' => 'required|filled',
        ]);

        if ($validator_header_data->fails()) {
            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        }
        // dd($header_data-.);
        $validator = Validator::make($request->all(), [
            // 'deviceId' => 'required|filled|string',
            // 'deviceType' => ['required','filled',Rule::in(['1','2','3'])],
            // 'deviceToken' => 'required|filled'
        ]);
        if ($validator->fails()) {
            $data['status'] = 0;
            $data['message'] = "";
            $data['data'] = $validator->messages();
            return response()->json($data, 422);
        }
        // echo $Login_data->id;die;
        $scope = $request->scope;
        $has_wishlist = ($scope == 'learner') ? true : false;
        $deviceType = Helper::getDeviceType($request->header('devicetype'));
        $settings = Settings::select('key', 'value')->where('key', 'LIKE', '%visibility%')->get()->keyBy('key');
        $home_slider_visibility = isset($settings['home_slider_visibility']->value) ? stringToArray($settings['home_slider_visibility']->value) : [];
        $home_featured_courses_visibility = isset($settings['home_featured_courses_visibility']->value) ? stringToArray($settings['home_featured_courses_visibility']->value) : [];
        $home_top_free_visibility = isset($settings['home_top_free_visibility']->value) ? stringToArray($settings['home_top_free_visibility']->value) : [];
        $home_course_category_visibility = isset($settings['home_course_category_visibility']->value) ? stringToArray($settings['home_course_category_visibility']->value) : [];
        $home_trusted_by_visibility = isset($settings['home_trusted_by_visibility']->value) ? stringToArray($settings['home_trusted_by_visibility']->value) : [];
        $home_top_blogs_visibility = isset($settings['home_top_blogs_visibility']->value) ? stringToArray($settings['home_top_blogs_visibility']->value) : [];
        $is_home_slider_visible = in_array($deviceType, $home_slider_visibility);
        $is_featured_courses_visible = in_array($deviceType, $home_featured_courses_visibility);
        $is_top_free_visible = in_array($deviceType, $home_top_free_visibility);

        $is_course_category_visible = in_array($deviceType, $home_course_category_visibility);
        $is_trusted_by_visible = in_array($deviceType, $home_trusted_by_visibility);
        $is_top_blogs_visible = in_array($deviceType, $home_top_blogs_visibility);

        $slider_details = Slider::where('slug', 'home')->with(['slides.course'])->select('id', 'name', 'slug', 'autoplay')->first();
        // dd($slider_details);

        if ( /*$slider_details->slides->count() == 1 &&*/!$slider_details->autoplay) {
            $slider_details->isBanner = true;
        } else {
            $slider_details->isBanner = false;
        }

        $notification = Notification::where('learnerId', $request->user_id)->where("isRead", 0)->count();

        if ($notification > 0) {
            $slider_details->isShowNotificationBadge = true;

        } else {
            $slider_details->isShowNotificationBadge = false;
        }

        unset($slider_details->autoplay, $slider_details->slug, $slider_details->name, $slider_details->id, $slider_details->caption1);

        $trusted_by = Dropdown::where('slug', 'trusted_by')->with(['dropdownOptions'])->first();

        if ($is_course_category_visible) {
            $slider_details->categories = DropdownOption::getDropdownCategories('course_category')
                ->select('id', 'image', 'name')
            // ->withCount('courses')
                ->where('status', true)
                ->where('home', true)
                ->orderBy("id", "desc")
                ->get();
        } else {
            unset($slider_details->slides);
            $slider_details->categories = new stdClass([]);
        }
        // $slider_details->categories = \DB::select("select dropdown_options.*, count(dropdown_options.id) as total from dropdown_options inner join dropdowns on dropdowns.id = dropdown_options.dropdown_id where dropdowns.slug='course_category' and dropdown_options.deleted_at is null and dropdowns.deleted_at is null and dropdown_options.home = true  group by dropdown_options.name");
        if ($is_top_blogs_visible) {
            $slider_details->blogs = Blog::select('id', 'category_id', 'title', 'created_at', 'cover')
                ->with('blogCategoryOptions')
                ->where('home', true)
                ->where('status', true)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            unset($slider_details->slides);
            $slider_details->blogs = new stdClass([]);
        }

        $slider_details->instructors = Instructors::where("allow_instructor", 1)->select(['id', 'user_id'])->with(['user' => function ($query) {
            $query->select('id', 'name', 'profile_picture', 'email');
        }])->orderBy("id", "desc")->get();

        $orderby = DB::raw('ISNULL(courses.order), courses.order');
        $dir = "DESC";
        $device_price_field = ($deviceType == 1 ? 'default_android_price' : ($deviceType == 2 ? 'default_iphone_price' : ''));
        // $course = Cache::get('cached_courses');
        $user_id = $request->user_id;

        $course = Course::query()
        ->select('courses.id', 'courses.order', 'title', 'type', 'instructor_id', 'image', 'cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price', 'courses.is_featured', 'courses.is_free')
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->whereHas("user")
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
                $query->whereNull('deleted_at');
            }, 'wishlists' => function ($query) {
                $query->where('learner_id', request()->user_id)->select('course_id', 'id', 'learner_id');
            }, 'plans' => function ($query) {
                $query->where('status', 1)->select('id', 'status');
            }])

            ->withcount("getPackages")->with("instructor:id,name")
            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            }, 'rating_reviews as reviews'])
            ->withCount(['rating_reviews as reviews'])
            ->withCount(['userCourse as total_enroll', 'rating_reviews as total_review'])
            ->withAvg('rating_reviews as rating', 'rating')
            ->whereRelation('categories', 'status', true)
            ->whereNull('courses.deleted_at')
            ->leftJoin('course_plans as cp', 'cp.id', '=', $device_price_field)
            ->where('cp.status', '1')
            ->whereNotNull('courses.' . $device_price_field)

            ->where('courses.status', 1)
            ->orderByRaw('CASE WHEN `courses`.`order` IS NOT NULL THEN 0 ELSE 1 END')
            ->when(function ($query) {
                $query->where(function ($query) {
                    $query->where('is_featured', 1)
                        ->orWhere('is_free', 1);
                });
            })
            ->groupBy("courses.id");

        $course = $course->orderBy("id", "DESC")->limit(15)->get();
        // $course = $course->sortBy(function ($e) {
        //     if (isset($e->order)) {
        //         return $e->order;
        //     } else {
        //         return PHP_INT_MAX;
        //     }
        // });

        $course->filter(function ($slider_detail) use ($request,$deviceType) {

            $request->course_id = $slider_detail->id;
            $main_user_course = $slider_detail->newLearnerCourse($request)->first();

            $scope = $request->scope;
            $has_wishlist = ($scope == 'learner') ? true : false;
            $slider_detail->is_wishlisted = isset($slider_detail->wishlists) && $slider_detail->wishlists->count() > 0 && $has_wishlist ? true : false;

            $slider_detail->is_free = $slider_detail->is_free == 1 ? true : false;
            $slider_detail->is_featured = $slider_detail->is_featured == 1 ? true : false;
            $slider_detail->instructor_name = $slider_detail?->instructor?->name;
            $slider_detail->packages_count = Helper::get_package_course_count($slider_detail->id, $deviceType);

            $slider_detail->image = !empty($slider_detail->image) ? Helper::getImageUrl($slider_detail->image) : '';

            $main_user_course = $slider_detail->HomeLearnerCourse($request, $slider_detail->id)->first();

            $slider_detail->is_course_purchased = false;
            $slider_detail->course_purchase_id = '';
            $slider_detail->isCombineCourse = false;

            if ($slider_detail->type == 1) {

                if ($slider_detail->getPackages()->exists()) {

                    foreach ($slider_detail->getPackages as $package) {

                        $user_cousre = UserCourse::where("learner_id", $request->user_id)->where('order_status', "!=", 2)->orderBy("id","desc")->where("course_id", $package->package_id)->first();

                        if ($user_cousre != null) {
                            $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre?->expire_at);
                            $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre?->expire_at) == true ? $user_cousre?->id : '';
                            $slider_detail->isCombineCourse = false;

                        } else {
                            $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->course_id)->first();
                            if ($user_cousre != null) {
                                $slider_detail->is_course_purchased = isUserCourseExpired($user_cousre?->expire_at);
                                $slider_detail->course_purchase_id = isUserCourseExpired($user_cousre?->expire_at) == true ? $user_cousre?->id : '';
                                $slider_detail->isCombineCourse = false;
                            }

                        }
                    }
                } else if ($main_user_course) {

                    $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);;
                    $slider_detail->course_purchase_id = $slider_detail->course_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                    $slider_detail->isCombineCourse = false;
                }

            } else if ($slider_detail->type == 2) {
                if ($main_user_course) {
                    $slider_detail->is_course_purchased = isUserCourseExpired($main_user_course->expire_at);;
                    $slider_detail->course_purchase_id = $slider_detail->course_id = isUserCourseExpired($main_user_course->expire_at) == true ? $main_user_course->id : '';
                    $slider_detail->isCombineCourse = true;
                } else {
                    $slider_detail->is_course_purchased = false;
                    $slider_detail->course_purchase_id = $slider_detail->course_id = '';
                    $slider_detail->isCombineCourse = true;
                }
                $slider_detail->is_course_purchased = false;
                $slider_detail->course_purchase_id = $slider_detail->course_id = '';
                $slider_detail->isCombineCourse = true;
            }

            unset($slider_detail->getPackages);
        });

        if ($is_top_free_visible) {

            $slider_details->topFreeCourse = $course->where('is_free', 1)->values();

            // array_values($slider_details->topFreeCourse);
        } else {

            unset($slider_details->slides);
            $slider_details->topFreeCourse = new stdClass([]);
        }

        if ($is_featured_courses_visible) {

            $slider_details->topFeatureCourse = $course->where("is_featured", 1)->values();
            // array_values($slider_details->topFeatureCourse);
        } else {
            unset($slider_details->slides);
            $slider_details->topFeatureCourse = new stdClass([]);
        }

        $total_cnt = $slider_details->count();
        if ($total_cnt > 0) {

            if (isset($slider_details->categories) && !empty($slider_details->categories)) {
                foreach ($slider_details->categories as $slider_detail) {
                    $slider_detail->image = !empty($slider_detail->image) ? Helper::getImageUrl($slider_detail->image) : '';
                    unset($slider_detail->dropdown);
                }
            }
            if (isset($slider_details->blogs) && !empty($slider_details->blogs)) {
                foreach ($slider_details->blogs as $slider_detail) {
                    unset($slider_detail->tags);
                    if ($slider_detail->blogCategoryOptions?->count() > 0) {
                        $slider_detail->category_name = $slider_detail->blogCategoryOptions->name;
                    }
                    unset($slider_detail->blogCategoryOptions);
                    unset($slider_detail->category_id);
                    $slider_detail->cover = !empty($slider_detail->cover) ? Helper::getImageUrl($slider_detail->cover) : '';
                }
            }

            if ($is_home_slider_visible) {
                if (isset($slider_details->slides) && !empty($slider_details->slides)) {
                    $slide_cnt = 0;

                    foreach ($slider_details->slides as $k => $slider_detail) {
                        // dd($slider_detail->toArray());
                        $slider_detail->img = !empty($slider_detail->slider_image_mobile) ? Helper::getImageUrl($slider_detail->slider_image_mobile) : '';
                        // $slider_detail->is_caption_left = $slider_detail->direction == 0 ? true : '0';

                        if (!empty($slider_detail->course) && isset($slider_detail->course->id)) {
                            $slider_detail->isCombineCourse = $slider_detail->course->type == '1' ? false : true;

                            $slider_detail->course_id = $slider_detail->course != "" ? $slider_detail->course->id : "";

                            // $slider_detail->course_id = $slider_detail->course->id;

                            $expireFlag = is_expired($slider_detail?->course?->userCourse?->where([["learner_id", $request->user_id], ["course_id", $slider_detail->course->id]])->first()->expire_at ?? '');
                            // dd($slider_detail->course->userCourse->where("learner_id",auth()->user()->id)->first()->expire_at);

                            $slider_detail->is_expire = false;
                            // $slider_detail->course_purchase_id = $slider_detail->course->userCourse->id ?? '';
                            if ($expireFlag == 0) {
                                $slider_detail->is_expire = true;

                            } else if ($expireFlag == 1) {
                                $slider_detail->is_expire = false;
                            } else if ($expireFlag == 2) {
                                $slider_detail->is_expire = false;

                            }

                            $slider_detail->is_course_purchased = false; // Set 'false' to default value
                            $slider_detail->course_purchase_id = '1';
                            if ($slider_detail->course->userCourseExpectedRelationship->count() > 0) {

                                foreach ($slider_detail->course->userCourseExpectedRelationship as $userCourse) {
                                    $data = UserCourse::where("course_id", $userCourse->course_id)->where('order_status', "!=", 2)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();

                                    if ($data) {
                                        if (isUserCourseExpired($data->expire_at) == true) {
                                            $slider_detail->is_course_purchased = isUserCourseExpired($data->expire_at);
                                            $slider_detail->course_purchase_id = isUserCourseExpired($data->expire_at) == true ? $data->id : '';

                                        } else {
                                            $slider_detail->course_id = "";
                                            $slider_detail->is_course_purchased = false;
                                            $slider_detail->course_purchase_id = '';
                                        }
                                    }
                                }
                            } else {

                                foreach ($slider_detail->course->getPackages as $package) {
                                    $package_user = UserCourse::where("course_id", $package->package_id)->where('order_status', "!=", 2)->orderBy("id", "desc")->where("learner_id", request()->user_id)->first();
                                    if ($package_user) {

                                        if (isUserCourseExpired($package_user->expire_at) == true) {
                                            $slider_detail->is_course_purchased = isUserCourseExpired($package_user->expire_at);
                                            $slider_detail->course_purchase_id = isUserCourseExpired($package_user->expire_at) == true ?
                                            $package_user->id : '';
                                            break;
                                        } else {
                                            $slider_detail->course_id = "";
                                            $slider_detail->is_course_purchased = false;
                                            $slider_detail->course_purchase_id = '';
                                        }
                                    } else {
                                        $slider_detail->course_id = "";
                                        $slider_detail->is_course_purchased = false;
                                        $slider_detail->course_purchase_id = '';
                                    }
                                }
                            }

                        } else {
                            $slider_detail->isCombineCourse = false;
                            $slider_detail->course_id = "";
                            $slider_detail->is_course_purchased = false;
                            $slider_detail->course_purchase_id = '';
                            $slider_detail->is_expire = false;
                        }

                        unset($slider_detail->slider_id, $slider_detail->caption1, $slider_detail->caption2, $slider_detail->direction, $slider_detail->action_url_mobile, $slider_detail->action_url, $slider_detail->action_text, $slider_detail->new_window, $slider_detail->course, $slider_detail->caption1_text_color, $slider_detail->caption2_text_color, $slider_detail->slider_image_mobile, $slider_detail->button_text_color, $slider_detail->button_bg_color, $slider_detail->is_caption_left);
                        // r($slide_cnt);
                        if ($slider_details->isBanner && $slide_cnt >= 1) { // if slider autoplay is off then only return first 1 slide
                            // $slider_detail->count = $slide_cnt;
                            unset($slider_details->slides[$k]);
                            // break;
                        }

                        $slide_cnt++;
                    }
                }
            } else {
                unset($slider_details->slides);
                $slider_details->slides = new stdClass([]);
            }

            // $slider_details->topFeatureCourse = $slider_details->topFeatureCourse->filter(function ($course) {
            //     // Assuming is_course_purchased has been set in previous processing steps
            //     return !$course->is_course_purchased;
            // })->values();

            // $slider_details->topFreeCourse = $slider_details->topFeatureCourse->filter(function ($course) {
            //     // Assuming is_course_purchased has been set in previous processing steps
            //     return !$course->is_course_purchased;
            // })->values();

            if (isset($slider_details->instructors) && !empty($slider_details->instructors)) {
                foreach ($slider_details->instructors as $slider_detail) {

                    $slider_detail->profile_pic = !empty($slider_detail->user->profile_picture) ? Helper::getImageUrl($slider_detail->user->profile_picture) : '';
                    $slider_detail->name = $slider_detail->user->name ?? '';
                    $slider_detail->email = $slider_detail->user->email ?? '';
                    $slider_detail->id = $slider_detail->user_id;
                    unset($slider_detail->user);
                    unset($slider_detail->user_id);
                }

            } else {
                $slider_detail->instructors = new \stdClass();
            }

            $data = Helper::apiResonse(1, "Success", $slider_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 400);
        }
    }


    public function globalSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'searchTerm' => 'required|min:3',
            'page' => 'nullable',
            'per_page' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $page_num = $request->page ?: 1;
        $per_page = $request->per_page ?: 10;
        $search = '';

        if ($request->filled('searchTerm')) {
            $search = trim(strtolower($request->searchTerm));

            if (strlen($search) >= 3) {
                $langFlag = 0;
                $english = 'english';
                $hindi = 'hindi';
                if (preg_match_all("/" . $search . "/", $english)) {
                    $langFlag = 1;
                }
                if (preg_match_all("/" . $search . "/", $hindi)) {
                    $langFlag = 2;
                }
                $deviceType = Helper::getDeviceType($request->devicetype);
                // $course = Cache::get('cached_course_search');

                // if ($course === null) {
                $course = Course::whereHas("user")->
                    with([
                    'instructor',
                    'categories' => function ($query) {
                        $query->whereNull('course_categories.deleted_at');
                    },
                    'plans' => function ($query) {
                        return $query->where('status', 1)->orderBy('order', 'Asc');
                    },
                    'chapters' => function ($query) {
                        $query->where('parent_id', 0);
                    },
                ])

                    ->where('courses.status', '1')
                    ->where('cp.status', '1')
                // ->whereIn('course_platform', [Course::COURSE_ALL, $deviceType])
                    ->whereRaw("find_in_set($deviceType , course_platform)")
                    ->leftJoin('course_categories', 'courses.id', '=', 'course_categories.course_id')
                    ->leftJoin('dropdown_options', 'course_categories.category_id', '=', 'dropdown_options.id')
                    ->Join('course_plans as cp', 'cp.id', '=', $request->device_price_field)
                    ->whereRaw('LOWER(`courses`.`title`) LIKE ?', "%{$search}%")
                    ->orWhereRaw('LOWER(`courses`.`tags`) LIKE ?', "%{$search}%");
                // ->orWhereRaw('LOWER(`title`) LIKE ?', "%{$search}%")

                if ($langFlag > 0) {
                    $course->orWhere('lng', $langFlag);
                }

                $course
                    ->orderBy('courses.order', 'ASC')
                    ->groupBy('id')

                    // ->where('courses.status', '=', 1)
                    ->select(DB::raw('"a" as flag'), 'title', 'courses.id', 'courses.image as image', 'courses.slug', 'lng', 'tags', 'courses.created_at', DB::raw('"content" as content'), 'instructor_id', 'type', 'courses.updated_at', DB::raw('"category_id" as category_id'), 'hours', 'minutes', 'cp.plan_type', 'cp.list_price', 'cp.final_payable_price')
                    // ->orderBy('courses.order', 'ASC')->groupBy('id')
                    ->with(['chapters' => function ($query) {
                        $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                    }]);
                $course->whereRaw("find_in_set($deviceType , course_platform)");

                $courses = $course->withAvg('rating_reviews as rating', 'rating')->get();
                // Cache::put('cached_course_search', $courses, 2);
                // }

                $courses->filter(function ($title) {
                    return $title->title = ucwords($title->title);
                });

                if (!empty($courses)) {
                    foreach ($courses as $key => $course) {
                        $course->course_id = $course->id;

                        if (count($course->plans) > 0) {

                            $course->image = !empty($course->image) ? Helper::getImageUrl($course->image) : '';
                            $course->lng = $course->lng == 2 ? 'Hindi' : 'English';
                            $course->show_learner_cnt = $course->show_learner_cnt == 0 ? false : true;
                            $course->rating = $course->rating ?? 0;

                            $main_user_course = $course->HomeLearnerCourse($request, $course->id)->first();
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

                            $course->is_course_purchased = false;
                            $course->course_purchase_id = '';
                            $course->isCombineCourse = false;

                            if ($course->type == 1) {
                                // $user_cousre = UserCourse::where("learner_id",$request->user_id)->where("course_id",$course?->getNewPackages?->package_id)->first();

                                if ($course->getPackages()->exists()) {
                                    foreach ($course->getPackages as $package) {

                                        $user_cousre = UserCourse::where("learner_id", $request->user_id)->orderBy("id","desc")->where('order_status', "!=", 2)->where("course_id", $package->package_id)->first();
                                        if ($user_cousre != null) {
                                            $course->is_course_purchased = isUserCourseExpired($user_cousre->expire_at);
                                            $course->course_purchase_id = isUserCourseExpired($user_cousre->expire_at) == true ? $user_cousre->id : '';
                                            $course->isCombineCourse = false;
                                        } else {
                                            $user_cousre = UserCourse::where("learner_id", $request->user_id)->where('order_status', "!=", 2)->orderBy("id","desc")->where("course_id", $package->course_id)->first();

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
                                // $new_course = $course->newLearnerCourse($request)->first();
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
                            $course->instructor_name = $course->user->name ?? '';
                            $course->isCombineCourse = $course->type == '1' ? false : true;

                            unset($course->instructor_id, $course->course_category, $course->type, $course->category_id, $course->instructor, $course->categories, $course->plans, $course->chapters, $course->userCourseExpectedRelationship, $course->packages, $course->wishlists, $course->rating_reviews);
                        }
                    }
                }

                $blog = Blog::whereRaw('LOWER(`title`) LIKE ?', "%{$search}%")
                    ->orWhereRaw('LOWER(`dropdown_options`.`name`) LIKE ?', "%{$search}%")
                    ->leftJoin('dropdown_options', 'category_id', '=', 'dropdown_options.id')
                    ->select(DB::raw('"c" as flag'), 'title', 'blogs.id', 'cover as image', 'blogs.slug', DB::raw('"lng" as lng'), 'dropdown_options.name as tags', 'blogs.created_at', 'content', DB::raw('"instructor_id" as instructor_id'), DB::raw('"type" as type'), 'blogs.updated_at', 'category_id', DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'))->orderBy('title', 'ASC');
                $b = $blog->get();

                if (!empty($b)) {
                    foreach ($b as $key => $value) {
                        $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                        unset($value->categories, $value->plans, $value->chapters, $value->packages, $value->instructor_id, $value->course_category, $value->type, $value->category_id, $value->instructor, $value->lng, $value->hours, $value->minutes, $value->plan_type, $value->list_price, $value->final_payable_price, $value->user_course_expected_relationship);
                    }
                }

                $instructure = Instructors::whereRaw('LOWER(`name`) LIKE ?', "%{$search}%")
                    ->join('users', 'instructors.user_id', '=', 'users.id')
                    ->select(DB::raw('"b" as flag'), 'users.name as title', 'users.id as id', 'users.profile_picture as image', DB::raw('"slug" as slug'), DB::raw('"lng" as lng'), 'instructors.designation as tags', 'instructors.created_at as created_at', DB::raw('"content" as content'), DB::raw('"instructor_id" as instructor_id'), DB::raw('"type" as type'), 'instructors.updated_at as updated_at', DB::raw('"category_id" as category_id'), DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'))->orderBy('name', 'ASC');
                $i = $instructure->get();

                if (!empty($i)) {
                    foreach ($i as $key => $value) {
                        $instructor = Instructors::where('user_id', $value->id)->first();
                        $value->designation = $instructor->designation ?? '';
                        $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                        unset($value->categories, $value->plans, $value->chapters, $value->packages, $value->instructor_id, $value->course_category, $value->type, $value->category_id, $value->instructor, $value->lng, $value->hours, $value->minutes, $value->plan_type, $value->list_price, $value->final_payable_price);
                    }
                }

                $categories = DropdownOption::with('dropdown')
                    ->where('status', true)
                    ->whereRaw('LOWER(`name`) LIKE ?', "%{$search}%")
                    ->select(DB::raw('"d" as flag'), 'name as title', 'id', 'image as image', DB::raw('"slug" as slug'), DB::raw('"lng" as lng'), 'name as tags', 'created_at', DB::raw('"content" as content'), DB::raw('"instructor_id" as instructor_id'), DB::raw('"type" as type'), 'updated_at', DB::raw('"category_id" as category_id'), DB::raw('"hours" as hours'), DB::raw('"minutes" as minutes'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'), DB::raw('"plan_type" as plan_type'))->orderBy('name', 'ASC');
                $c = $categories->get();

                if (!empty($c)) {
                    foreach ($c as $key => $value) {
                        $value->image = !empty($value->image) ? Helper::getImageUrl($value->image) : '';
                        unset($value->categories, $value->plans, $value->chapters, $value->packages, $value->instructor_id, $value->course_category, $value->type, $value->category_id, $value->instructor, $value->lng, $value->hours, $value->minutes, $value->plan_type, $value->list_price, $value->final_payable_price, $value->userCourseExpectedRelationship, $value->dropdown);
                    }
                }

                $course_per_page = $inst_page_number = $blog_page_number = $cat_page_number = $page_num;
                if (!empty($request->flag)) {
                    if ($request->flag == 'a') {
                        $inst_page_number = $blog_page_number = $cat_page_number = 1;
                    } else if ($request->flag == 'b') {
                        $course_per_page = $blog_page_number = $cat_page_number = 1;
                    } else if ($request->flag == 'c') {
                        $course_per_page = $inst_page_number = $cat_page_number = 1;
                    } else if ($request->flag == 'd') {
                        $course_per_page = $inst_page_number = $blog_page_number = 1;
                    }
                }

                $course_Data = cpaginate($courses, $per_page, $course_per_page);
                $catagoryData = cpaginate($c, $per_page, $cat_page_number);
                $instructorData = cpaginate($i, $per_page, $inst_page_number);
                $blogData = cpaginate($b, $per_page, $blog_page_number);

                $searchData['data'] = [
                    'courses' => $courses->isEmpty() ? [] : [Helper::nullToEmptyStringHelper($course_Data)],
                    'instructure' => $i->isEmpty() ? [] : [Helper::nullToEmptyStringHelper($instructorData)],
                    'categories' => $c->isEmpty() ? [] : [Helper::nullToEmptyStringHelper($catagoryData)],
                    'blog' => $b->isEmpty() ? [] : [Helper::nullToEmptyStringHelper($blogData)],
                ];

                $data = Helper::apiResonse(1, "Success", $searchData, true);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(1, "Search term must be at least 3 characters long", []);
                return response()->json($data, 200);
            }
        } else {
            $data = Helper::apiResonse(1, "Search term is required", []);
            return response()->json($data, 200);
        }
    }

}
